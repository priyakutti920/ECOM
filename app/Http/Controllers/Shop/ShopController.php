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

        // All categories (used to render the round-icon bar at the top)
        $categories = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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

        $categories = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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

        $categories = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(20)
            ->get();

        $banners = Banner::active()->orderBy('sort_order')->get();

        $featured = Product::active()
            ->where('is_featured', true)
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $latest = Product::active()
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $deals = Product::active()
            ->whereNotNull('special_price')
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $bestSellerIds = Cache::remember('home_bestseller_ids', 1800, function () {
            $ids = OrderItem::select('product_id')
                ->selectRaw('SUM(quantity) as total_sold')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->pluck('product_id')
                ->all();

            if (empty($ids)) {
                $ids = Product::active()
                    ->orderByDesc('id')
                    ->limit(10)
                    ->pluck('id')
                    ->all();
            }

            return $ids;
        });

        $bestSellers = Product::active()
            ->whereIn('id', $bestSellerIds)
            ->with(['primaryImage', 'categories'])
            ->withAvg('approvedReviews', 'rating')
            ->withCount('approvedReviews')
            ->get();

        $bonuses = Bonus::active()->get();
        $flashSaleBanner = Banner::flashSale()->first();

        return view('shop.home', compact(
            'storeName',
            'storeTagline',
            'categories',
            'banners',
            'flashSaleBanner',
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
        $q = trim($request->get('q', ''));

        $products = collect();
        if ($q !== '') {
            $products = Product::active()
                ->with(['primaryImage', 'categories'])
                ->withAvg('approvedReviews', 'rating')
                ->withCount('approvedReviews')
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                })
                ->orderByDesc('id')
                ->paginate(24)
                ->withQueryString();
        }

        $storeName = StoreSetting::getStoreName();

        return view('shop.search', compact('products', 'q', 'storeName'));
    }
}
