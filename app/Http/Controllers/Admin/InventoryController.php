<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display the warehouse inventory dashboard with stock levels, alerts, and audit trail.
     * GET /admin/inventory
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));

        $query = Product::query()->with(['variations', 'primaryImage']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('variations', function ($vq) use ($search) {
                      $vq->where('name', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($status === 'low_stock') {
            $query->where(function ($q) {
                $q->whereRaw('qty <= low_stock_threshold AND qty > 0')
                  ->orWhereHas('variations', function ($vq) {
                      $vq->whereRaw('qty <= low_stock_threshold AND qty > 0');
                  });
            });
        } elseif ($status === 'out_of_stock') {
            $query->where(function ($q) {
                $q->where('stock_status', 'out_of_stock')
                  ->orWhere('qty', '<=', 0)
                  ->orWhereHas('variations', function ($vq) {
                      $vq->where('stock_status', 'out_of_stock')->orWhere('qty', '<=', 0);
                  });
            });
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        // High-level KPI metrics
        $totalProducts = Product::count();
        $lowStockCount = Product::whereRaw('qty <= low_stock_threshold AND qty > 0')->count() +
                         ProductVariation::whereRaw('qty <= low_stock_threshold AND qty > 0')->count();
        $outOfStockCount = Product::where('stock_status', 'out_of_stock')->orWhere('qty', '<=', 0)->count();

        // Recent Stock Movements (Audit Trail)
        $recentMovements = StockMovement::with(['product', 'variation', 'user'])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('admin.inventory.index', compact(
            'products',
            'status',
            'search',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'recentMovements'
        ));
    }

    /**
     * Handle manual inventory restock or count adjustment.
     * POST /admin/inventory/adjust
     */
    public function adjustStock(Request $request)
    {
        $data = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'new_qty'      => 'required|integer|min:0',
            'type'         => 'required|in:RESTOCK,ADJUSTMENT',
            'reason'       => 'nullable|string|max:255',
        ]);

        $this->inventoryService->adjustStock(
            (int) $data['product_id'],
            $data['variation_id'] ? (int) $data['variation_id'] : null,
            (int) $data['new_qty'],
            $data['type'],
            $data['reason'] ?? '',
            Auth::id()
        );

        return back()->with('success', 'Stock level adjusted and recorded to inventory ledger.');
    }
}
