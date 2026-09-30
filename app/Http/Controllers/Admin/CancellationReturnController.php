<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CancellationReturnController extends Controller
{
    /**
     * GET /admin/cancellations-returns
     * Unified list: cancelled orders + return requests, with tabs/filters.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'all'); // all | cancellations | returns

        $cancellations = Order::query()
            ->where('status', 'cancelled')
            ->when($request->q, function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(function ($w) use ($term) {
                    $w->where('order_code', 'like', "%{$term}%")
                      ->orWhere('contact_name', 'like', "%{$term}%")
                      ->orWhere('contact_mobile', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('cancelled_at')
            ->paginate(20, ['*'], 'c_page')
            ->withQueryString();

        $returns = OrderReturn::query()
            ->with(['order', 'product', 'customer'])
            ->when($request->q, function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(function ($w) use ($term) {
                    $w->where('reason', 'like', "%{$term}%")
                      ->orWhereHas('order', function ($o) use ($term) {
                          $o->where('order_code', 'like', "%{$term}%");
                      })
                      ->orWhereHas('customer', function ($c) use ($term) {
                          $c->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
                      });
                });
            })
            ->when(in_array($request->status, array_keys(OrderReturn::STATUSES), true), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByRaw("FIELD(status, 'requested','accepted','pickup_scheduled','picked_up','completed','rejected')")
            ->orderByDesc('requested_at')
            ->paginate(20, ['*'], 'r_page')
            ->withQueryString();

        $stats = [
            'cancellations' => Order::where('status', 'cancelled')->count(),
            'returns_open'  => OrderReturn::whereIn('status', ['requested', 'accepted', 'pickup_scheduled', 'picked_up'])->count(),
            'returns_done'  => OrderReturn::whereIn('status', ['completed', 'rejected'])->count(),
        ];

        return view('admin.cancellations-returns', [
            'tab'           => $tab,
            'cancellations' => $cancellations,
            'returns'       => $returns,
            'stats'         => $stats,
            'filters'       => $request->only(['q', 'status']),
        ]);
    }

    /**
     * POST /admin/returns/{return}/status
     */
    public function updateReturnStatus(Request $request, OrderReturn $return)
    {
        $data = $request->validate([
            'status'     => 'required|string|in:requested,accepted,pickup_scheduled,picked_up,completed,rejected',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $prevStatus = $return->status;
        $return->status      = $data['status'];
        $return->admin_note  = $data['admin_note'] ?? $return->admin_note;
        $return->resolved_at = in_array($data['status'], ['completed', 'rejected'], true) ? now() : null;
        $return->save();

        // Restock returned item when return is completed (exactly once via InventoryService)
        $restocked = false;
        if ($data['status'] === 'completed') {
            $restocked = app(\App\Services\Inventory\InventoryService::class)->restoreForReturn(
                $return,
                null,
                auth()->id(),
                auth()->user()?->name
            );
        }

        return back()->with('success', 'Return status updated to ' . $return->status_label . ($restocked ? ' and inventory restocked.' : '.'));
    }

    /**
     * POST /admin/returns/{return}/note
     */
    public function updateReturnNote(Request $request, OrderReturn $return)
    {
        $data = $request->validate(['admin_note' => 'required|string|max:1000']);
        $return->admin_note = $data['admin_note'];
        $return->save();
        return back()->with('success', 'Note saved.');
    }
}
