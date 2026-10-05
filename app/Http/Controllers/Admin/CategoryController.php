<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        // Auto-heal any orphaned categories whose parent has status != 0 or no longer exists
        $activeParentIds = Category::where('status', 0)->pluck('id')->all();
        Category::where('status', 0)
            ->whereNotNull('parent_id')
            ->whereNotIn('parent_id', $activeParentIds)
            ->update(['parent_id' => null]);

        $categories = Category::where('status', 0)
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg,avif|max:20480',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imagePath = $file->store('category', 'public');
        }

        $maxOrder = Category::where('status', 0)->max('sort_order') ?? 0;

        // Generate unique slug from category name or custom slug
        $baseSlug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $category = Category::create([
            'name'  => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id ?: null,
            'image' => $imagePath,
            'sort_order' => $maxOrder + 1,
            'status' => 0,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        $category->load('parent');

        return response()->json([
            'success' => true,
            'message' => 'Category created.',
            'category' => $this->formatCategory($category),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg,avif|max:20480',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                if (file_exists(public_path($imagePath))) {
                    @unlink(public_path($imagePath));
                } else {
                    Storage::disk('public')->delete(str_replace('storage/', '', $imagePath));
                }
            }
            $file = $request->file('image');
            $imagePath = $file->store('category', 'public');
        }

        // Regenerate or use custom slug
        $newSlug = $category->slug;
        if ($request->filled('slug') && $request->slug !== $category->slug) {
            $baseSlug = Str::slug($request->slug);
            $slug = $baseSlug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $newSlug = $slug;
        } elseif ($category->name !== $request->name && !$request->filled('slug')) {
            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $newSlug = $slug;
        }

        $category->update([
            'name'  => $request->name,
            'slug' => $newSlug,
            'parent_id' => $request->parent_id ?: null,
            'image' => $imagePath,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $category->is_active,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
        ]);

        $category->refresh()->load('parent');

        return response()->json([
            'success' => true,
            'message' => 'Category updated.',
            'category' => $this->formatCategory($category),
        ]);
    }

    public function toggleActive(Category $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success' => true,
            'message' => $category->is_active ? 'Category enabled.' : 'Category disabled.',
            'is_active' => (bool)$category->is_active,
        ]);
    }

    // Soft delete — status 1 means deleted, image kept on disk
    public function destroy(Category $category)
    {
        $category->update(['status' => 1]);
        Category::where('parent_id', $category->id)->update(['status' => 1]);

        Cache::forget('home_categories_list');
        Cache::forget('shop_header_categories_v2');
        StoreSetting::clearCache();

        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        Cache::forget('home_categories_list');
        Cache::forget('shop_header_categories_v2');
        StoreSetting::clearCache();

        return response()->json(['success' => true]);
    }

    /**
     * Perform bulk actions on selected categories.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:activate,deactivate,delete',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $affected = 0;

        if ($action === 'activate') {
            $affected = Category::whereIn('id', $ids)->where('status', 0)->update(['is_active' => true]);
            $msg = "{$affected} category/categories activated.";
        } elseif ($action === 'deactivate') {
            $affected = Category::whereIn('id', $ids)->where('status', 0)->update(['is_active' => false]);
            $msg = "{$affected} category/categories deactivated.";
        } elseif ($action === 'delete') {
            $affected = Category::whereIn('id', $ids)->update(['status' => 1]);
            Category::whereIn('parent_id', $ids)->update(['status' => 1]);
            $msg = "{$affected} category/categories deleted.";
        }

        Cache::forget('home_categories_list');
        Cache::forget('shop_header_categories_v2');
        StoreSetting::clearCache();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'affected' => $affected,
            ]);
        }

        return back()->with('success', $msg);
    }

    private function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image,
            'image_url' => $category->image_url,
            'parent_id' => $category->parent_id,
            'parent_name' => $category->parent?->name,
            'sort_order' => $category->sort_order,
            'status' => $category->status,
            'is_active' => (bool)$category->is_active,
            'meta_title' => $category->meta_title,
            'meta_description' => $category->meta_description,
            'meta_keywords' => $category->meta_keywords,
        ];
    }
}
