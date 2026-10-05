<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomepageSectionController extends Controller
{
    /**
     * Display the Homepage Product Sections manager under Appearance
     */
    public function index()
    {
        $sections = StoreSetting::getHomepageSectionsConfig();

        // Sample / current products for each section so admin can see what is displayed
        $sectionPreviews = [];

        // Best seller IDs calculation
        $bestSellerIds = Cache::remember('home_bestseller_ids', 1800, function () {
            $ids = OrderItem::select('product_id')
                ->selectRaw('SUM(quantity) as total_sold')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(20)
                ->pluck('product_id')
                ->all();

            if (empty($ids)) {
                $ids = Product::active()
                    ->orderByDesc('id')
                    ->limit(20)
                    ->pluck('id')
                    ->all();
            }

            return $ids;
        });

        foreach ($sections as $key => $sec) {
            $limit = max(1, (int)($sec['limit'] ?? 10));
            $mode = $sec['mode'] ?? 'auto';
            $manualIds = $sec['product_ids'] ?? [];

            if ($mode === 'manual' && !empty($manualIds)) {
                $cases = [];
                $bindings = [];
                foreach ($manualIds as $idx => $mid) {
                    $cases[] = "WHEN id = ? THEN ?";
                    $bindings[] = (int)$mid;
                    $bindings[] = (int)$idx;
                }
                $caseSql = "CASE " . implode(' ', $cases) . " ELSE 999999 END";

                $prods = Product::active()
                    ->whereIn('id', $manualIds)
                    ->with(['primaryImage', 'categories'])
                    ->orderByRaw($caseSql, $bindings)
                    ->get();
            } else {
                switch ($key) {
                    case 'featured':
                        $prods = Product::active()
                            ->where('is_featured', true)
                            ->with(['primaryImage', 'categories'])
                            ->orderByDesc('id')
                            ->limit($limit)
                            ->get();
                        break;

                    case 'deals':
                        $prods = Product::active()
                            ->whereNotNull('special_price')
                            ->with(['primaryImage', 'categories'])
                            ->orderByDesc('id')
                            ->limit($limit)
                            ->get();
                        break;

                    case 'bestsellers':
                        $prods = Product::active()
                            ->whereIn('id', array_slice($bestSellerIds, 0, $limit))
                            ->with(['primaryImage', 'categories'])
                            ->get();
                        break;

                    case 'latest':
                    default:
                        $prods = Product::active()
                            ->with(['primaryImage', 'categories'])
                            ->orderByDesc('id')
                            ->limit($limit)
                            ->get();
                        break;
                }
            }

            // Also load full Product objects for manual IDs in case mode is manual or admin switches to manual
            $manualProducts = !empty($manualIds)
                ? Product::whereIn('id', $manualIds)->with('primaryImage')->get()->keyBy('id')
                : collect();

            $sectionPreviews[$key] = [
                'preview_products' => $prods,
                'manual_products'  => $manualProducts,
            ];
        }

        // Stats summary
        $stats = [
            'total_active'    => Product::active()->count(),
            'total_featured'  => Product::active()->where('is_featured', true)->count(),
            'total_deals'     => Product::active()->whereNotNull('special_price')->count(),
            'total_bestsellers' => count($bestSellerIds),
        ];

        return view('admin.appearance.sections', compact('sections', 'sectionPreviews', 'stats'));
    }

    /**
     * Update all homepage sections settings
     */
    public function update(Request $request)
    {
        $current = StoreSetting::getHomepageSectionsConfig();
        $inputSections = $request->input('sections', []);

        $newConfig = [];
        $order = 1;

        // Valid section keys
        $validKeys = ['featured', 'deals', 'bestsellers', 'latest'];

        // If an explicit order array is sent
        $orderedKeys = $request->input('section_order', $validKeys);
        if (!is_array($orderedKeys)) {
            $orderedKeys = $validKeys;
        }

        foreach ($orderedKeys as $key) {
            if (!in_array($key, $validKeys, true)) {
                continue;
            }

            $input = $inputSections[$key] ?? [];
            $cur = $current[$key] ?? [];

            // Manual product IDs can come as array or comma-separated string
            $rawProductIds = $input['product_ids'] ?? ($cur['product_ids'] ?? []);
            if (is_string($rawProductIds)) {
                $productIds = array_filter(array_map('intval', explode(',', $rawProductIds)));
            } elseif (is_array($rawProductIds)) {
                $productIds = array_filter(array_map('intval', $rawProductIds));
            } else {
                $productIds = [];
            }

            $newConfig[$key] = [
                'key'          => $key,
                'title'        => !empty($input['title']) ? trim($input['title']) : ($cur['title'] ?? ucfirst($key)),
                'subtitle'     => isset($input['subtitle']) ? trim($input['subtitle']) : ($cur['subtitle'] ?? ''),
                'icon'         => !empty($input['icon']) ? trim($input['icon']) : ($cur['icon'] ?? 'las la-star'),
                'icon_color'   => !empty($input['icon_color']) ? trim($input['icon_color']) : ($cur['icon_color'] ?? 'var(--color-primary)'),
                'enabled'      => isset($input['enabled']) ? (bool)$input['enabled'] : false,
                'limit'        => max(1, min(50, (int)($input['limit'] ?? ($cur['limit'] ?? 10)))),
                'mode'         => in_array($input['mode'] ?? 'auto', ['auto', 'manual'], true) ? $input['mode'] : 'auto',
                'product_ids'  => array_values(array_unique($productIds)),
                'view_all_url' => isset($input['view_all_url']) ? trim($input['view_all_url']) : ($cur['view_all_url'] ?? '/products'),
                'view_all_text'=> isset($input['view_all_text']) ? trim($input['view_all_text']) : ($cur['view_all_text'] ?? 'View All'),
                'sort_order'   => isset($input['sort_order']) ? (int)$input['sort_order'] : $order++,
            ];
        }

        // Add any missing keys
        foreach ($validKeys as $key) {
            if (!isset($newConfig[$key]) && isset($current[$key])) {
                $newConfig[$key] = $current[$key];
                $newConfig[$key]['sort_order'] = $order++;
            }
        }

        uasort($newConfig, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        StoreSetting::saveHomepageSectionsConfig($newConfig);
        Cache::forget('home_bestseller_ids');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Homepage sections updated successfully.',
                'sections' => $newConfig,
            ]);
        }

        return redirect()->route('admin.appearance.sections.index')
            ->with('success', 'Homepage sections updated successfully.');
    }

    /**
     * Quick toggle active status for a specific section via AJAX
     */
    public function toggle(Request $request, string $key): JsonResponse
    {
        $config = StoreSetting::getHomepageSectionsConfig();

        if (!isset($config[$key])) {
            return response()->json(['success' => false, 'message' => 'Section not found.'], 404);
        }

        $config[$key]['enabled'] = !$config[$key]['enabled'];
        StoreSetting::saveHomepageSectionsConfig($config);

        return response()->json([
            'success' => true,
            'enabled' => $config[$key]['enabled'],
            'message' => "Section '{$config[$key]['title']}' " . ($config[$key]['enabled'] ? 'enabled' : 'disabled') . '.',
        ]);
    }

    /**
     * Quick toggle product is_featured attribute via AJAX
     */
    public function toggleFeatured(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->is_featured = !$product->is_featured;
        $product->save();

        return response()->json([
            'success'     => true,
            'is_featured' => (bool)$product->is_featured,
            'message'     => $product->is_featured
                ? "'{$product->name}' marked as Featured."
                : "'{$product->name}' removed from Featured.",
        ]);
    }

    /**
     * AJAX Product Search for adding to manual sections
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query);

        $products = Product::active()
            ->when($escaped !== '', function ($q) use ($escaped) {
                $q->where(function ($sub) use ($escaped) {
                    $sub->where('name', 'like', "%{$escaped}%")
                        ->orWhere('code', 'like', "%{$escaped}%")
                        ->orWhere('sku', 'like', "%{$escaped}%");
                });
            })
            ->with('primaryImage')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $formatted = $products->map(function ($prod) {
            $img = $prod->primaryImage?->image ?: $prod->image;
            $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : asset(str_starts_with($img, 'storage/') || str_starts_with($img, 'product/') ? $img : 'storage/' . $img)) : asset('images/placeholder.png');

            return [
                'id'            => $prod->id,
                'name'          => $prod->name,
                'code'          => $prod->code ?: $prod->sku,
                'price'         => number_format((float)$prod->price, 2),
                'special_price' => $prod->special_price ? number_format((float)$prod->special_price, 2) : null,
                'is_featured'   => (bool)$prod->is_featured,
                'image'         => $imgUrl,
            ];
        });

        return response()->json([
            'success'  => true,
            'products' => $formatted,
        ]);
    }
}
