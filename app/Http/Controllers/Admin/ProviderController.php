<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = Provider::with(['products' => function ($q) {
            $q->select('products.id', 'products.name')->orderBy('name');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('mobile', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%')
                  ->orWhereHas('products', function ($pq) use ($search) {
                      $pq->where('products.name', 'like', '%' . $search . '%');
                  });
            });
        }

        $providers = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $allProducts = Product::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.providers', compact('providers', 'allProducts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'mobile'   => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'city'     => 'nullable|string|max:100',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        $provider = Provider::create([
            'name'    => $data['name'],
            'mobile'  => $data['mobile'] ?? null,
            'address' => $data['address'] ?? null,
            'city'    => $data['city'] ?? null,
        ]);

        if (!empty($data['products'])) {
            $provider->products()->attach($data['products']);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Provider added successfully.']);
        }

        return back()->with('success', 'Provider added successfully.');
    }

    public function update(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'mobile'   => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'city'     => 'nullable|string|max:100',
            'products' => 'nullable|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        $provider->update([
            'name'    => $data['name'],
            'mobile'  => $data['mobile'] ?? null,
            'address' => $data['address'] ?? null,
            'city'    => $data['city'] ?? null,
        ]);

        $provider->products()->sync($data['products'] ?? []);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Provider updated successfully.']);
        }

        return back()->with('success', 'Provider updated successfully.');
    }

    public function destroy(Request $request, Provider $provider)
    {
        $provider->products()->detach();
        $provider->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Provider deleted.']);
        }

        return back()->with('success', 'Provider deleted.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->get('q', '');

        $products = Product::active()
            ->when($q, fn($p) => $p->where('name', 'like', '%' . $q . '%'))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json(['success' => true, 'data' => $products]);
    }
}