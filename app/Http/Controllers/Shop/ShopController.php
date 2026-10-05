<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Bonus;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    /**
     * Shop page — single canonical store front.
     * URL: /shop
     *  - default -> all products
     *  - ?category=<id> -> only products in that category
     *  - ?sort=latest|price_low|price_high|name
     */
    public function shop(Request $request)
    {
        $storeName = StoreSetting::getStoreName();

        // All categories (used to render the round-icon bar at the top, cached for speed)
        $categories = Cache::remember('shop_active_categories_bar', 1800, function () {
            return Category::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });

        // Determine which category (if any) the user is filtering by
        $activeCategory = null;
        $categoryId = $request->get('category');
        if (! empty($categoryId)) {
            $activeCategory = Category::active()
                ->where('id', $categoryId)
                ->first();
        }

        $query = Product::active()->with(['primaryImage', 'categories'])->withAvg('approvedReviews', 'rating')->withCount('approvedReviews');

        if ($activeCategory) {
            // Include products from this category AND its sub-categories (root-friendly)
            $subIds = Category::active()
                ->where('parent_id', $activeCategory->id)
                ->pluck('id')
                ->all();
            $ids = collect([$activeCategory->id])->merge($subIds)->unique()->all();

            $query->where(function ($q) use ($ids) {
                $q->whereIn('category_id', $ids)
                  ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
            });
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low': $query->orderBy('price', 'asc');
                break;
            case 'price_high':$query->orderBy('price', 'desc');
                break;
            case 'name':      $query->orderBy('name', 'asc');
                break;
            default:          $query->orderByDesc('id');
        }

        $products = $query->paginate(24)->withQueryString();

        return view('shop.shop', compact(
            'storeName',
            'categories',
            'activeCategory',
            'products',
            'sort'
        ));
    }

    /**
     * Flash Deals page — products with active discounts.
     * URL: /deals
     */
    public function deals(Request $request)
    {
        $storeName = StoreSetting::getStoreName();

        $categories = Cache::remember('shop_active_categories_bar', 1800, function () {
            return Category::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });

        $activeCategory = null;
        $categoryId = $request->get('category');
        if (! empty($categoryId)) {
            $activeCategory = Category::active()->where('id', $categoryId)->first();
        }

        $query = Product::active()
            ->whereNotNull('special_price')
            ->where('special_price', '>', 0)
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews');

        if ($activeCategory) {
            $subIds = Category::active()->where('parent_id', $activeCategory->id)->pluck('id')->all();
            $ids = collect([$activeCategory->id])->merge($subIds)->unique()->all();

            $query->where(function ($q) use ($ids) {
                $q->whereIn('category_id', $ids)
                  ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
            });
        }

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low': $query->orderBy('special_price', 'asc'); break;
            case 'price_high':$query->orderBy('special_price', 'desc'); break;
            case 'name':      $query->orderBy('name', 'asc'); break;
            default:          $query->orderByDesc('id');
        }

        $products = $query->paginate(24)->withQueryString();

        return view('shop.shop', [
            'storeName'      => $storeName,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'products'       => $products,
            'sort'           => $sort,
            'pageTitle'      => 'Flash Deals & Discounts',
        ]);
    }

    /**
     * Home page — Amazon-style layout
     */
    public function home(Request $request)
    {
        $storeName = StoreSetting::getStoreName();
        $storeTagline = StoreSetting::getValue('store_tagline', '');

        $categories = Cache::remember('home_categories_list', 1800, function () {
            return Category::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(20)
                ->get();
        });

        $banners = Cache::remember('home_banners_list', 1800, function () {
            return Banner::active()->orderBy('sort_order')->orderByDesc('id')->get();
        });

        $sectionsConfig = StoreSetting::getHomepageSectionsConfig();

        $homeSections = Cache::remember('homepage_sections_prods', 300, function () use ($sectionsConfig) {
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

            $sections = [];
            foreach ($sectionsConfig as $secKey => $secData) {
                $limit = max(1, (int)($secData['limit'] ?? 10));
                $mode = $secData['mode'] ?? 'auto';
                $manualIds = $secData['product_ids'] ?? [];

                if (!$secData['enabled']) {
                    $secData['products'] = collect();
                    $sections[$secKey] = $secData;
                    continue;
                }

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
                        ->withAvg('approvedReviews', 'rating')
                        ->withCount('approvedReviews')
                        ->orderByRaw($caseSql, $bindings)
                        ->limit($limit)
                        ->get();
                } else {
                    switch ($secKey) {
                        case 'featured':
                            $prods = Product::active()
                                ->where('is_featured', true)
                                ->with(['primaryImage', 'categories'])
                                ->withAvg('approvedReviews', 'rating')
                                ->withCount('approvedReviews')
                                ->orderByDesc('id')
                                ->limit($limit)
                                ->get();
                            break;

                        case 'deals':
                            $prods = Product::active()
                                ->whereNotNull('special_price')
                                ->with(['primaryImage', 'categories'])
                                ->withAvg('approvedReviews', 'rating')
                                ->withCount('approvedReviews')
                                ->orderByDesc('id')
                                ->limit($limit)
                                ->get();
                            break;

                        case 'bestsellers':
                            $prods = Product::active()
                                ->whereIn('id', array_slice($bestSellerIds, 0, $limit))
                                ->with(['primaryImage', 'categories'])
                                ->withAvg('approvedReviews', 'rating')
                                ->withCount('approvedReviews')
                                ->get();
                            break;

                        case 'latest':
                        default:
                            $prods = Product::active()
                                ->with(['primaryImage', 'categories'])
                                ->withAvg('approvedReviews', 'rating')
                                ->withCount('approvedReviews')
                                ->orderByDesc('id')
                                ->limit($limit)
                                ->get();
                            break;
                    }
                }

                $secData['products'] = $prods;
                $sections[$secKey] = $secData;
            }

            return $sections;
        });

        // Backward compatibility variables
        $featured = $homeSections['featured']['products'] ?? collect();
        $deals = $homeSections['deals']['products'] ?? collect();
        $bestSellers = $homeSections['bestsellers']['products'] ?? collect();
        $latest = $homeSections['latest']['products'] ?? collect();

        $bonuses = Bonus::active()->get();
        $flashSaleBanner = Cache::remember('home_flash_sale_banner', 1800, function () {
            return Banner::flashSale()->first();
        });

        return view('shop.home', compact(
            'storeName',
            'storeTagline',
            'categories',
            'banners',
            'flashSaleBanner',
            'homeSections',
            'featured',
            'latest',
            'deals',
            'bestSellers',
            'bonuses'
        ));
    }

    /**
     * Category page — products within a category
     * Accepts either the slug (preferred) or numeric id (legacy).
     * For root categories (no parent), also includes products from all sub-categories.
     */
    public function category(Request $request, $slug)
    {
        // Lookup by slug first, fall back to id for legacy URLs
        $category = Category::active()
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->firstOrFail();

        $subcategories = Category::active()
            ->where('parent_id', $category->id)
            ->orderBy('sort_order')
            ->get();

        // Build the list of category IDs to query
        $isRoot = empty($category->parent_id);
        $categoryIds = $isRoot
        ? collect([$category->id])->merge($subcategories->pluck('id'))->unique()->all()
        : [$category->id];

        $query = Product::active()
            ->where(function ($q) use ($categoryIds) {
                $q->whereIn('category_id', $categoryIds)
                  ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $categoryIds));
            })
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews');

        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderByDesc('id');
        }

        $products = $query->paginate(24)->withQueryString();

        $storeName = StoreSetting::getStoreName();

        return view('shop.category', compact(
            'category',
            'subcategories',
            'products',
            'sort',
            'storeName',
            'isRoot'
        ));
    }

    /**
     * Product detail page — lookup by slug
     */
    public function product(Request $request, $slug)
    {
        $product = Product::active()
            ->with([
                'primaryImage',
                'images',
                'categories',
                'variations.images',
                'options',
                'colors',
                'approvedReviews.user',
                'relatedProducts.primaryImage',
            ])
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug);
                if (is_numeric($slug)) {
                    $q->orWhere('id', (int) $slug);
                }
            })
            ->firstOrFail();

        $related = $product->relatedProducts->count() > 0
        ? $product->relatedProducts
        : Product::active()
            ->where('id', '!=', $product->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->with('primaryImage')
            ->limit(6)
            ->get();

        $storeName = StoreSetting::getStoreName();

        return view('shop.product', compact('product', 'related', 'storeName'));
    }

    /**
     * Wishlist page — renders the wishlist view.
     * The actual product list is read from the browser's localStorage on the client,
     * and product details are fetched from /api/cart/items.
     */
    public function wishlist(Request $request)
    {
        $storeName = StoreSetting::getStoreName();

        return view('shop.wishlist', compact('storeName'));
    }

    /**
     * Search results
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $categoryId = $request->get('category');

        $products = collect();
        if ($q !== '' || !empty($categoryId)) {
            $query = Product::active()
                ->with(['primaryImage', 'categories'])
                ->withAvg('approvedReviews', 'rating')
                ->withCount('approvedReviews');

            if ($q !== '') {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhereHas('categories', function ($catQuery) use ($q) {
                            $catQuery->where('name', 'like', "%{$q}%");
                        });
                });
            }

            if (!empty($categoryId) && is_numeric($categoryId)) {
                $cat = Category::active()->find($categoryId);
                if ($cat) {
                    $subIds = Category::active()->where('parent_id', $cat->id)->pluck('id')->all();
                    $ids = collect([$cat->id])->merge($subIds)->unique()->all();
                    $query->where(function ($cq) use ($ids) {
                        $cq->whereIn('category_id', $ids)
                           ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
                    });
                }
            }

            $products = $query->orderByDesc('id')
                ->paginate(24)
                ->withQueryString();
        }

        $storeName = StoreSetting::getStoreName();

        return view('shop.search', compact('products', 'q', 'storeName'));
    }

    /**
     * Live search endpoint returning instant JSON product suggestions.
     * URL: /api/search/live
     */
    public function liveSearch(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $categoryId = $request->get('category');

        if ($q === '' || mb_strlen($q) < 1) {
            return response()->json([
                'success' => true,
                'count' => 0,
                'total' => 0,
                'products' => [],
                'query' => '',
                'view_all_url' => url('/search'),
            ]);
        }

        $cacheKey = 'live_srch_' . md5(mb_strtolower($q) . '_' . ($categoryId ?: 'all'));

        $data = Cache::remember($cacheKey, 180, function () use ($q, $categoryId) {
            $baseQuery = Product::active()
                ->select([
                    'id', 'name', 'code', 'slug', 'price', 'special_price',
                    'special_price_start', 'special_price_end',
                    'stock_status', 'qty', 'manage_inventory', 'image', 'category_id'
                ])
                ->with([
                    'primaryImage:id,product_id,image',
                    'categories:id,name,slug'
                ])
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%")
                        ->orWhereHas('categories', function ($catQuery) use ($q) {
                            $catQuery->where('name', 'like', "%{$q}%");
                        });
                });

            if (!empty($categoryId) && is_numeric($categoryId)) {
                $cat = Category::active()->select('id', 'parent_id')->find($categoryId);
                if ($cat) {
                    $subIds = Category::active()->where('parent_id', $cat->id)->pluck('id')->all();
                    $ids = collect([$cat->id])->merge($subIds)->unique()->all();
                    $baseQuery->where(function ($cq) use ($ids) {
                        $cq->whereIn('category_id', $ids)
                           ->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
                    });
                }
            }

            // Fetch up to 8 products prioritizing starts-with
            $products = $baseQuery
                ->orderByRaw("CASE WHEN LOWER(name) LIKE ? THEN 1 WHEN LOWER(name) LIKE ? THEN 2 ELSE 3 END", [
                    strtolower($q) . '%',
                    '%' . strtolower($q) . '%',
                ])
                ->orderByDesc('id')
                ->limit(8)
                ->get();

            $items = $products->map(function ($product) {
                $category = $product->categories->first();
                $inStock = $product->stock_status === 'in_stock' && (!$product->manage_inventory || $product->qty > 0);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug ?: (string) $product->id,
                    'url' => route('shop.product', $product->slug ?: $product->id),
                    'image' => $product->image_url ?: asset('assets/images/placeholder.png'),
                    'price' => (float) $product->price,
                    'formatted_price' => '₹' . number_format($product->price, 2),
                    'effective_price' => (float) $product->effective_price,
                    'formatted_effective_price' => '₹' . number_format($product->effective_price, 2),
                    'is_on_sale' => (bool) $product->is_on_sale,
                    'discount_percent' => (int) $product->discount_percent,
                    'category_name' => $category ? $category->name : null,
                    'category_url' => $category ? route('shop.category', $category->slug ?: $category->id) : null,
                    'in_stock' => $inStock,
                ];
            });

            $queryParams = array_filter([
                'q' => $q,
                'category' => $categoryId ?: null,
            ]);

            return [
                'success' => true,
                'count' => $items->count(),
                'total' => $items->count(),
                'query' => $q,
                'products' => $items->all(),
                'view_all_url' => url('/search') . (empty($queryParams) ? '' : '?' . http_build_query($queryParams)),
            ];
        });

        return response()->json($data);
    }
}
