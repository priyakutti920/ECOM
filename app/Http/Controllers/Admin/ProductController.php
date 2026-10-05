<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;
use App\Models\Category;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['primaryImage', 'categories']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            elseif ($request->status === 'inactive') $query->where('is_active', false);
        }

        $products = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = Category::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $allProducts = Product::active()->orderBy('name')->get(['id', 'name']);
        $providers = Provider::orderBy('name')->get(['id', 'name', 'city']);
        return view('admin.products.create', compact('categories', 'allProducts', 'providers'));
    }

    public function store(Request $request)
    {
        // Preprocess categories
        if ($request->has('categories') && is_string($request->categories)) {
            $request->merge(['categories' => array_filter(explode(',', $request->categories))]);
        }

        // Preprocess providers
        if ($request->has('providers') && is_string($request->providers)) {
            $request->merge(['providers' => array_filter(explode(',', $request->providers))]);
        }

        // Preprocess related products
        if ($request->has('related_products') && is_string($request->related_products)) {
            $request->merge(['related_products' => array_filter(explode(',', $request->related_products))]);
        }

        // Preprocess variations
        if ($request->has('variations') && is_string($request->variations)) {
            $decoded = json_decode($request->variations, true);
            $request->merge(['variations' => is_array($decoded) ? $decoded : []]);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:100|unique:products,code',
            'description' => 'nullable|string',
            'categories'  => 'nullable|array',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp,gif,svg,avif|max:20480',
            'url'         => 'nullable|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'price'              => 'nullable|numeric|min:0',
            'special_price'     => 'nullable|numeric|min:0',
            'special_price_start' => 'nullable|date',
            'special_price_end'    => 'nullable|date|after_or_equal:special_price_start',
            'manage_inventory' => 'boolean',
            'stock_status'     => 'nullable|string|in:in_stock,out_of_stock',
            'qty'              => 'nullable|integer|min:0',
            'is_featured'      => 'boolean',
            'is_active'        => 'boolean',
            'is_returnable'    => 'boolean',
            'related_products' => 'nullable|array',
            'variations'       => 'nullable|array',
            'variations.*.name'       => 'required_with:variations|string|max:255',
            'variations.*.sku'        => 'nullable|string|max:100',
            'variations.*.price'      => 'required_with:variations|numeric|min:0',
            'variations.*.special_price' => 'nullable|numeric|min:0',
            'variations.*.special_price_start' => 'nullable|date',
            'variations.*.special_price_end'   => 'nullable|date|after_or_equal:special_price_start',
            'variations.*.manage_inventory' => 'boolean',
            'variations.*.qty'          => 'nullable|integer|min:0',
            'variations.*.stock_status' => 'nullable|string|in:in_stock,out_of_stock',
            'variations.*.images'       => 'nullable|array',
        ], [
            'variations.*.name.required_with' => 'Variation name is required.',
            'variations.*.price.required_with' => 'Variation price is required.',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Generate slug
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $product = Product::create([
                'name'              => $validated['name'],
                'code'              => $validated['code'] ?? null,
                'description'       => $validated['description'] ?? null,
                'seo_url'          => $validated['url'] ?? null,
                'slug'              => $slug,
                'meta_title'        => $validated['meta_title'] ?? null,
                'meta_description'  => $validated['meta_description'] ?? null,
                'price'            => $validated['price'] ?? 0,
                'special_price'     => $validated['special_price'] ?? null,
                'special_price_start' => $validated['special_price_start'] ?? null,
                'special_price_end'   => $validated['special_price_end'] ?? null,
                'manage_inventory' => $request->boolean('manage_inventory'),
                'stock_status'      => $validated['stock_status'] ?? 'in_stock',
                'qty'              => $validated['qty'] ?? 0,
                'is_featured'      => $request->boolean('is_featured'),
                'is_active'        => $request->boolean('is_active', true),
                'is_returnable'    => $request->boolean('is_returnable'),
                'status'            => 1,
            ]);

            // Save product images (from temporary uploads or image_order string)
            if ($request->has('image_order')) {
                $orders = is_string($request->image_order) ? json_decode($request->image_order, true) : $request->image_order;
                if (is_array($orders)) {
                    foreach ($orders as $i => $imgVal) {
                        if (!empty($imgVal)) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image'      => $imgVal,
                                'sort_order' => $i,
                                'is_primary' => ($i === 0),
                            ]);
                        }
                    }
                }
            } else {
                $this->saveProductImages($product, $request, 'images', null);
            }

            // Categories
            if (!empty($validated['categories'])) {
                $product->categories()->attach($validated['categories']);
            }

            // Providers
            if (!empty($request->providers)) {
                $product->providers()->attach($request->providers);
            }

            // Related products
            $relatedIds = is_string($validated['related_products'] ?? '')
                ? array_filter(explode(',', $validated['related_products'] ?? ''))
                : ($validated['related_products'] ?? []);
            if (!empty($relatedIds)) {
                $product->relatedProducts()->attach(array_map('intval', $relatedIds));
            }

            // Variations
            if (!empty($validated['variations'])) {
                foreach ($validated['variations'] as $varData) {
                    $varImages = $varData['images'] ?? [];
                    unset($varData['images']);

                    // Sanitize empty strings to database compatible defaults
                    $qty = (!isset($varData['qty']) || $varData['qty'] === '') ? 0 : intval($varData['qty']);
                    $sku = (isset($varData['sku']) && $varData['sku'] !== '') ? $varData['sku'] : null;
                    $specialPrice = (isset($varData['special_price']) && $varData['special_price'] !== '') ? $varData['special_price'] : null;
                    $specialPriceStart = (isset($varData['special_price_start']) && $varData['special_price_start'] !== '') ? $varData['special_price_start'] : null;
                    $specialPriceEnd = (isset($varData['special_price_end']) && $varData['special_price_end'] !== '') ? $varData['special_price_end'] : null;

                    $var = $product->variations()->create([
                        'name'                  => $varData['name'],
                        'sku'                   => $sku,
                        'price'                 => $varData['price'],
                        'special_price'         => $specialPrice,
                        'special_price_start'   => $specialPriceStart,
                        'special_price_end'     => $specialPriceEnd,
                        'manage_inventory'      => !empty($varData['manage_inventory']),
                        'qty'                   => $qty,
                        'stock_status'          => $varData['stock_status'] ?? 'in_stock',
                    ]);

                    // Save variation images
                    if (!empty($varImages)) {
                        foreach ($varImages as $vi => $vimg) {
                            if (!empty($vimg)) {
                                ProductVariationImage::create([
                                    'product_variation_id' => $var->id,
                                    'image'                => $vimg,
                                    'sort_order'           => $vi,
                                    'is_primary'           => ($vi === 0),
                                ]);
                            }
                        }
                    }
                    unset($var);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created.',
                'product_id' => $product->id,
            ]);
        });
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'categories', 'variations.images', 'relatedProducts', 'providers']);
        $categories = Category::active()->orderBy('name')->get();
        $allProducts = Product::active()->where('id', '!=', $product->id)->orderBy('name')->get(['id', 'name']);
        $providers = Provider::orderBy('name')->get(['id', 'name', 'city']);
        return view('admin.products.edit', compact('product', 'categories', 'allProducts', 'providers'));
    }

    public function update(Request $request, Product $product)
    {
        // Preprocess categories
        if ($request->has('categories') && is_string($request->categories)) {
            $request->merge(['categories' => array_filter(explode(',', $request->categories))]);
        }

        // Preprocess providers
        if ($request->has('providers') && is_string($request->providers)) {
            $request->merge(['providers' => array_filter(explode(',', $request->providers))]);
        }

        // Preprocess related products
        if ($request->has('related_products') && is_string($request->related_products)) {
            $request->merge(['related_products' => array_filter(explode(',', $request->related_products))]);
        }

        // Preprocess variations
        if ($request->has('variations') && is_string($request->variations)) {
            $decoded = json_decode($request->variations, true);
            $request->merge(['variations' => is_array($decoded) ? $decoded : []]);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:100|unique:products,code,' . $product->id,
            'description' => 'nullable|string',
            'categories'  => 'nullable|array',
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpg,jpeg,png,webp,gif,svg,avif|max:20480',
            'url'         => 'nullable|string|max:255',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'price'              => 'nullable|numeric|min:0',
            'special_price'     => 'nullable|numeric|min:0',
            'special_price_start' => 'nullable|date',
            'special_price_end'    => 'nullable|date|after_or_equal:special_price_start',
            'manage_inventory' => 'boolean',
            'stock_status'     => 'nullable|string|in:in_stock,out_of_stock',
            'qty'              => 'nullable|integer|min:0',
            'is_featured'      => 'boolean',
            'is_active'        => 'boolean',
            'is_returnable'    => 'boolean',
            'related_products' => 'nullable|array',
            'deleted_images'   => 'nullable|array',
            'variations'      => 'nullable|array',
            'variations.*.id'  => 'nullable|integer|exists:product_variations,id',
            'variations.*.name'       => 'required_with:variations|string|max:255',
            'variations.*.sku'        => 'nullable|string|max:100',
            'variations.*.price'      => 'required_with:variations|numeric|min:0',
            'variations.*.special_price' => 'nullable|numeric|min:0',
            'variations.*.special_price_start' => 'nullable|date',
            'variations.*.special_price_end'   => 'nullable|date|after_or_equal:special_price_start',
            'variations.*.manage_inventory' => 'boolean',
            'variations.*.qty'          => 'nullable|integer|min:0',
            'variations.*.stock_status' => 'nullable|string|in:in_stock,out_of_stock',
        ], [
            'variations.*.name.required_with' => 'Variation name is required.',
            'variations.*.price.required_with' => 'Variation price is required.',
        ]);

        return DB::transaction(function () use ($request, $validated, $product) {
            $product->update([
                'name'              => $validated['name'],
                'code'              => $validated['code'] ?? null,
                'description'       => $validated['description'] ?? null,
                'seo_url'          => $validated['url'] ?? null,
                'meta_title'        => $validated['meta_title'] ?? null,
                'meta_description'  => $validated['meta_description'] ?? null,
                'price'             => $validated['price'] ?? 0,
                'special_price'     => $validated['special_price'] ?? null,
                'special_price_start' => $validated['special_price_start'] ?? null,
                'special_price_end'   => $validated['special_price_end'] ?? null,
                'manage_inventory' => $request->boolean('manage_inventory'),
                'stock_status'      => $validated['stock_status'] ?? 'in_stock',
                'qty'              => $validated['qty'] ?? 0,
                'is_featured'      => $request->boolean('is_featured'),
                'is_active'        => $request->boolean('is_active', true),
                'is_returnable'    => $request->boolean('is_returnable'),
            ]);

            // Handle deleted images
            if (!empty($validated['deleted_images'])) {
                $imgsToDelete = ProductImage::whereIn('id', $validated['deleted_images'])
                    ->where('product_id', $product->id)->get();
                foreach ($imgsToDelete as $img) {
                    if (str_starts_with($img->image, 'product/')) {
                        $fullPath = public_path($img->image);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    } else {
                        Storage::disk('public')->delete($img->image);
                    }
                    $img->delete();
                }
            }

            // Save and reorder product images
            if ($request->has('image_order')) {
                $orders = is_string($request->image_order) ? json_decode($request->image_order, true) : $request->image_order;
                if (is_array($orders)) {
                    // Find and delete any existing product images that are NOT in the image_order array
                    $dbImageIds = $product->images()->pluck('id')->toArray();
                    $keptImageIds = [];
                    foreach ($orders as $imgId) {
                        if (is_numeric($imgId)) {
                            $keptImageIds[] = intval($imgId);
                        }
                    }
                    $toDeleteIds = array_diff($dbImageIds, $keptImageIds);
                    if (!empty($toDeleteIds)) {
                        $imgsToDelete = ProductImage::whereIn('id', $toDeleteIds)->get();
                        foreach ($imgsToDelete as $img) {
                            if (str_starts_with($img->image, 'product/')) {
                                $fullPath = public_path($img->image);
                                if (file_exists($fullPath)) {
                                    @unlink($fullPath);
                                }
                            } else {
                                Storage::disk('public')->delete($img->image);
                            }
                            $img->delete();
                        }
                    }

                    foreach ($orders as $i => $imgId) {
                        $isPrimary = ($i === 0);
                        if (is_numeric($imgId)) {
                            ProductImage::where('id', $imgId)->update([
                                'sort_order' => $i,
                                'is_primary' => $isPrimary,
                            ]);
                        } else {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image'      => $imgId,
                                'sort_order' => $i,
                                'is_primary' => $isPrimary,
                            ]);
                        }
                    }
                }
            }

            // Categories
            $product->categories()->sync($validated['categories'] ?? []);

            // Providers
            $product->providers()->sync($request->providers ?? []);

            // Related products
            $relatedIds = is_string($validated['related_products'] ?? '')
                ? array_filter(explode(',', $validated['related_products'] ?? ''))
                : ($validated['related_products'] ?? []);
            $product->relatedProducts()->sync(array_map('intval', $relatedIds));

            // Variations (upsert)
            if (!empty($validated['variations'])) {
                foreach ($validated['variations'] as $varData) {
                    $existingId = $varData['id'] ?? null;
                    unset($varData['id']);

                    $varData['product_id'] = $product->id;
                    $varData['manage_inventory'] = !empty($varData['manage_inventory']);

                    // Separate custom keys before database update/create
                    $varImages = $varData['images'] ?? [];
                    unset($varData['images']);

                    $deletedVarImages = $varData['_deleted_images'] ?? '';
                    unset($varData['_deleted_images']);

                    // Sanitize empty strings to database compatible defaults
                    $varData['qty'] = (!isset($varData['qty']) || $varData['qty'] === '') ? 0 : intval($varData['qty']);
                    $varData['sku'] = (isset($varData['sku']) && $varData['sku'] !== '') ? $varData['sku'] : null;
                    $varData['special_price'] = (isset($varData['special_price']) && $varData['special_price'] !== '') ? $varData['special_price'] : null;
                    $varData['special_price_start'] = (isset($varData['special_price_start']) && $varData['special_price_start'] !== '') ? $varData['special_price_start'] : null;
                    $varData['special_price_end'] = (isset($varData['special_price_end']) && $varData['special_price_end'] !== '') ? $varData['special_price_end'] : null;

                    if ($existingId) {
                        ProductVariation::where('id', $existingId)
                            ->where('product_id', $product->id)
                            ->update($varData);
                        $varId = $existingId;
                    } else {
                        $var = $product->variations()->create($varData);
                        $varId = $var->id;
                    }

                    // Save and reorder variation images
                    if (!empty($varImages)) {
                        foreach ($varImages as $vi => $vimg) {
                            if (!empty($vimg)) {
                                $isPrimary = ($vi === 0);
                                if (is_numeric($vimg)) {
                                    ProductVariationImage::where('id', $vimg)
                                        ->where('product_variation_id', $varId)
                                        ->update([
                                            'sort_order' => $vi,
                                            'is_primary' => $isPrimary,
                                        ]);
                                } else {
                                    ProductVariationImage::create([
                                        'product_variation_id' => $varId,
                                        'image'                => $vimg,
                                        'sort_order'           => $vi,
                                        'is_primary'           => $isPrimary,
                                    ]);
                                }
                            }
                        }
                    }

                    // Handle deleted variation images
                    if (!empty($deletedVarImages)) {
                        $dels = is_array($deletedVarImages)
                            ? $deletedVarImages
                            : explode(',', $deletedVarImages);
                        $imgsToDelete = ProductVariationImage::whereIn('id', $dels)
                            ->where('product_variation_id', $varId)->get();
                        foreach ($imgsToDelete as $img) {
                            if (str_starts_with($img->image, 'product/')) {
                                $fullPath = public_path($img->image);
                                if (file_exists($fullPath)) {
                                    @unlink($fullPath);
                                }
                            } else {
                                Storage::disk('public')->delete($img->image);
                            }
                            $img->delete();
                        }
                    }
                }
            }

            // Handle removed variations
            if ($request->has('deleted_variations')) {
                $delIds = is_array($request->deleted_variations)
                    ? $request->deleted_variations
                    : explode(',', $request->deleted_variations);
                $varsToDelete = ProductVariation::whereIn('id', $delIds)
                    ->where('product_id', $product->id)->with('images')->get();
                foreach ($varsToDelete as $var) {
                    foreach ($var->images as $img) {
                        if (str_starts_with($img->image, 'product/')) {
                            $fullPath = public_path($img->image);
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        } else {
                            Storage::disk('public')->delete($img->image);
                        }
                    }
                    $var->images()->delete();
                    $var->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated.',
            ]);
        });
    }

    public function destroy(Request $request, Product $product)
    {
        if ($request->boolean('force')) {
            $product->forceDelete();
            return response()->json(['success' => true, 'message' => 'Product permanently deleted.']);
        }

        // Soft delete: keep images and relationships intact so product can be restored
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted.']);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return response()->json(['success' => true, 'message' => 'Product restored successfully.']);
    }

    // ── Image upload endpoint ─────────────────────────────

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image'   => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:20480',
            'product_id'  => 'nullable|integer|exists:products,id',
            'variation_id' => 'nullable|integer|exists:product_variations,id',
        ]);

        $file = $request->file('image');
        $path = $file->store('product', 'public');

        // Also index in MediaFile manager
        try {
            \App\Models\MediaFile::create([
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'filename' => basename($path),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                'size' => $file->getSize() ?: 0,
                'folder' => 'product',
            ]);
        } catch (\Throwable $e) {
            // Silently continue if duplicate
        }

        if ($request->filled('product_id')) {
            $existing = ProductImage::where('product_id', $request->product_id)->count();
            $isPrimary = $existing === 0;
            $img = ProductImage::create([
                'product_id'  => $request->product_id,
                'image'       => $path,
                'sort_order'  => $existing,
                'is_primary'  => $isPrimary,
            ]);
        } elseif ($request->filled('variation_id')) {
            $existing = ProductVariationImage::where('product_variation_id', $request->variation_id)->count();
            $isPrimary = $existing === 0;
            $img = ProductVariationImage::create([
                'product_variation_id' => $request->variation_id,
                'image'          => $path,
                'sort_order'     => $existing,
                'is_primary'     => $isPrimary,
            ]);
        } else {
            // New product or variation creation: store the image and return the path
            return response()->json([
                'success'    => true,
                'image_id'   => $path,
                'image_url'  => \App\Models\Product::resolveMediaUrl($path),
                'is_primary' => false,
            ]);
        }

        return response()->json([
            'success'    => true,
            'image_id'   => $img->id,
            'image_url'  => $img->url,
            'is_primary' => $img->is_primary,
        ]);
    }

    // ── Product search (for related products) ─────────────

    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $exclude = $request->get('exclude', '');

        $products = Product::active()
            ->when($q, fn($p) => $p->where('name', 'like', "%{$q}%"))
            ->when($exclude, fn($p) => $p->where('id', '!=', $exclude))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'code']);

        return response()->json(['success' => true, 'data' => $products]);
    }

    // ── Private helpers ───────────────────────────────────

    private function saveProductImages($product, Request $request, string $key, $variationId = null)
    {
        if (!$request->hasFile($key)) return;

        $existingCount = $variationId
            ? ProductVariationImage::where('product_variation_id', $variationId)->count()
            : ProductImage::where('product_id', $product->id)->count();

        foreach ($request->file($key) as $i => $file) {
            $path = $file->store('product', 'public');
            if ($variationId) {
                ProductVariationImage::create([
                    'product_variation_id' => $variationId,
                    'image'          => $path,
                    'sort_order'     => $existingCount + $i,
                    'is_primary'    => ($existingCount + $i === 0),
                ]);
            } else {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $existingCount + $i,
                    'is_primary' => ($existingCount + $i === 0),
                ]);
            }
        }
    }

    /**
     * Perform bulk actions on selected products.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:activate,deactivate,in_stock,out_of_stock,delete,change_category',
            'category_id' => 'nullable|required_if:action,change_category|exists:categories,id',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $affected = 0;

        if ($action === 'activate') {
            $affected = Product::whereIn('id', $ids)->update(['is_active' => true]);
            $msg = "{$affected} product(s) marked as Active.";
        } elseif ($action === 'deactivate') {
            $affected = Product::whereIn('id', $ids)->update(['is_active' => false]);
            $msg = "{$affected} product(s) marked as Inactive.";
        } elseif ($action === 'in_stock') {
            $affected = Product::whereIn('id', $ids)->update(['stock_status' => 'in_stock']);
            $msg = "{$affected} product(s) marked In Stock.";
        } elseif ($action === 'out_of_stock') {
            $affected = Product::whereIn('id', $ids)->update(['stock_status' => 'out_of_stock']);
            $msg = "{$affected} product(s) marked Out of Stock.";
        } elseif ($action === 'delete') {
            $products = Product::whereIn('id', $ids)->get();
            foreach ($products as $p) {
                $p->delete();
                $affected++;
            }
            $msg = "{$affected} product(s) moved to Trash.";
        } elseif ($action === 'change_category') {
            $catId = (int) $request->input('category_id');
            $category = Category::findOrFail($catId);
            $products = Product::whereIn('id', $ids)->get();
            foreach ($products as $p) {
                $p->update(['category_id' => $catId]);
                $p->categories()->sync([$catId]);
                $affected++;
            }
            $msg = "{$affected} product(s) assigned to '{$category->name}'.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'affected' => $affected,
            ]);
        }

        return back()->with('success', $msg);
    }
}