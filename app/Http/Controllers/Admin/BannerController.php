<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\MediaFile;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display the banners manager with tab filtering and counts.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $query = Banner::query();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'trashed') {
            $query->onlyTrashed();
        }

        if ($status !== 'trashed') {
            $query->withoutTrashed();
        }

        // Search by primary text or tagline
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sq) use ($q) {
                $sq->where('primary_text', 'like', "%{$q}%")
                   ->orWhere('tagline', 'like', "%{$q}%")
                   ->orWhere('button_name', 'like', "%{$q}%");
            });
        }

        $banners = $query->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        // Status counts for tabs
        $totalCount = Banner::count();
        $activeCount = Banner::where('is_active', true)->count();
        $inactiveCount = Banner::where('is_active', false)->count();
        $trashedCount = Banner::onlyTrashed()->count();

        return view('admin.banners', compact(
            'banners',
            'status',
            'totalCount',
            'activeCount',
            'inactiveCount',
            'trashedCount'
        ));
    }

    /**
     * Create a new banner from file upload or media library selection.
     */
    public function store(Request $request)
    {
        $rules = [
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:20480',
            'image_path'   => 'nullable|string|max:500',
            'primary_text' => 'nullable|string|max:255',
            'tagline'      => 'nullable|string|max:255',
            'show_button'  => 'boolean',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'is_flash_sale'=> 'boolean',
        ];

        if ($request->boolean('show_button')) {
            $rules['button_name'] = 'required|string|max:100';
            $rules['button_link'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $imagePath = $request->input('image_path');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $cleanBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = time() . '_' . ($cleanBase ? substr($cleanBase, 0, 30) . '_' : '') . Str::random(6) . '.' . $ext;
            $imagePath = $file->storeAs('banner', $filename, 'public');

            // Mirror to public/storage if directory exists
            $publicDir = public_path('storage/banner');
            if (is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
                if (!is_dir($publicDir)) {
                    @mkdir($publicDir, 0755, true);
                }
                @copy(Storage::disk('public')->path($imagePath), public_path('storage/' . $imagePath));
            }

            // Index into MediaFile library
            try {
                $info = @getimagesize($file->getRealPath());
                MediaFile::create([
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'filename' => $filename,
                    'path' => $imagePath,
                    'disk' => 'public',
                    'mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                    'size' => $file->getSize() ?: 0,
                    'width' => $info ? ($info[0] ?? null) : null,
                    'height' => $info ? ($info[1] ?? null) : null,
                    'folder' => 'banner',
                ]);
            } catch (\Throwable $e) {
                // Silently ignore index errors
            }
        }

        if (empty($imagePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a banner image file or select one from the Media Library.',
                'errors' => ['image' => ['Banner image is required.']],
            ], 422);
        }

        $banner = Banner::create([
            'image'        => $imagePath,
            'primary_text' => $validated['primary_text'] ?? null,
            'tagline'      => $validated['tagline'] ?? null,
            'show_button'  => $request->boolean('show_button'),
            'button_name'  => $request->boolean('show_button') ? ($validated['button_name'] ?? null) : null,
            'button_link'  => $request->boolean('show_button') ? ($validated['button_link'] ?? null) : null,
            'sort_order'   => $validated['sort_order'] ?? 0,
            'is_active'    => $request->has('is_active') ? $request->boolean('is_active') : true,
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'is_flash_sale'=> $request->boolean('is_flash_sale'),
        ]);

        // Explicitly clear caches
        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.',
            'banner' => $banner,
        ]);
    }

    /**
     * Update an existing banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $rules = [
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:20480',
            'image_path'   => 'nullable|string|max:500',
            'primary_text' => 'nullable|string|max:255',
            'tagline'      => 'nullable|string|max:255',
            'show_button'  => 'boolean',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date',
            'is_flash_sale'=> 'boolean',
        ];

        if ($request->boolean('show_button')) {
            $rules['button_name'] = 'required|string|max:100';
            $rules['button_link'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $imagePath = $banner->image;

        if ($request->filled('image_path')) {
            $imagePath = $request->input('image_path');
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $cleanBase = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = time() . '_' . ($cleanBase ? substr($cleanBase, 0, 30) . '_' : '') . Str::random(6) . '.' . $ext;
            $imagePath = $file->storeAs('banner', $filename, 'public');

            // Mirror to public/storage if directory exists
            $publicDir = public_path('storage/banner');
            if (is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
                if (!is_dir($publicDir)) {
                    @mkdir($publicDir, 0755, true);
                }
                @copy(Storage::disk('public')->path($imagePath), public_path('storage/' . $imagePath));
            }

            // Index into MediaFile library
            try {
                $info = @getimagesize($file->getRealPath());
                MediaFile::create([
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'filename' => $filename,
                    'path' => $imagePath,
                    'disk' => 'public',
                    'mime_type' => $file->getClientMimeType() ?: 'image/jpeg',
                    'size' => $file->getSize() ?: 0,
                    'width' => $info ? ($info[0] ?? null) : null,
                    'height' => $info ? ($info[1] ?? null) : null,
                    'folder' => 'banner',
                ]);
            } catch (\Throwable $e) {
                // Silently ignore index errors
            }
        }

        $banner->update([
            'image'        => $imagePath,
            'primary_text' => $validated['primary_text'] ?? null,
            'tagline'      => $validated['tagline'] ?? null,
            'show_button'  => $request->boolean('show_button'),
            'button_name'  => $request->boolean('show_button') ? ($validated['button_name'] ?? null) : null,
            'button_link'  => $request->boolean('show_button') ? ($validated['button_link'] ?? null) : null,
            'sort_order'   => $validated['sort_order'] ?? $banner->sort_order,
            'is_active'    => $request->has('is_active') ? $request->boolean('is_active') : $banner->is_active,
            'start_date'   => $validated['start_date'] ?? null,
            'end_date'     => $validated['end_date'] ?? null,
            'is_flash_sale'=> $request->boolean('is_flash_sale'),
        ]);

        // Explicitly clear caches
        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.',
            'banner' => $banner,
        ]);
    }

    /**
     * Fast 1-click AJAX toggle for banner active status with instant cache clearing.
     */
    public function toggleActive(Banner $banner)
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => $banner->is_active ? 'Banner activated! Visible on storefront.' : 'Banner deactivated! Removed from storefront.',
            'is_active' => (bool) $banner->is_active,
        ]);
    }

    /**
     * Soft-delete a banner.
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner moved to Trash.',
        ]);
    }

    /**
     * Restore a soft-deleted banner.
     */
    public function restore($id)
    {
        $banner = Banner::onlyTrashed()->findOrFail($id);
        $banner->restore();

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner restored successfully.',
        ]);
    }

    /**
     * Permanently delete a banner and check if image file should be cleaned up.
     */
    public function forceDelete($id)
    {
        $banner = Banner::withTrashed()->findOrFail($id);
        $imgPath = $banner->image;

        $banner->forceDelete();

        // Check if image is still used by other banners or products before physical delete
        if ($imgPath && !str_starts_with($imgPath, 'http')) {
            $otherBanners = Banner::withTrashed()->where('image', $imgPath)->exists();
            $otherProducts = \App\Models\Product::where('image', $imgPath)->exists();
            if (!$otherBanners && !$otherProducts) {
                if (file_exists(public_path($imgPath))) {
                    @unlink(public_path($imgPath));
                }
                Storage::disk('public')->delete(str_replace('storage/', '', $imgPath));
            }
        }

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner permanently deleted.',
        ]);
    }

    /**
     * Reorder banners sort order.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->input('order') as $sortOrder => $bannerId) {
            Banner::where('id', $bannerId)->update(['sort_order' => $sortOrder]);
        }

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Banner order updated successfully.',
        ]);
    }

    /**
     * Perform bulk actions on selected banners.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:activate,deactivate,delete,restore,force_delete',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $affected = 0;

        if ($action === 'activate') {
            $affected = Banner::withoutTrashed()->whereIn('id', $ids)->update(['is_active' => true]);
            $msg = "{$affected} banner(s) activated successfully.";
        } elseif ($action === 'deactivate') {
            $affected = Banner::withoutTrashed()->whereIn('id', $ids)->update(['is_active' => false]);
            $msg = "{$affected} banner(s) deactivated.";
        } elseif ($action === 'delete') {
            $banners = Banner::whereIn('id', $ids)->get();
            foreach ($banners as $b) {
                $b->delete();
                $affected++;
            }
            $msg = "{$affected} banner(s) moved to Trash.";
        } elseif ($action === 'restore') {
            $banners = Banner::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($banners as $b) {
                $b->restore();
                $affected++;
            }
            $msg = "{$affected} banner(s) restored successfully.";
        } elseif ($action === 'force_delete') {
            $banners = Banner::withTrashed()->whereIn('id', $ids)->get();
            foreach ($banners as $b) {
                $imgPath = $b->image;
                $b->forceDelete();
                $affected++;

                if ($imgPath && !str_starts_with($imgPath, 'http')) {
                    $otherBanners = Banner::withTrashed()->where('image', $imgPath)->exists();
                    $otherProducts = \App\Models\Product::where('image', $imgPath)->exists();
                    if (!$otherBanners && !$otherProducts) {
                        if (file_exists(public_path($imgPath))) {
                            @unlink(public_path($imgPath));
                        }
                        Storage::disk('public')->delete(str_replace('storage/', '', $imgPath));
                    }
                }
            }
            $msg = "{$affected} banner(s) permanently deleted.";
        }

        Cache::forget('home_banners_list');
        Cache::forget('home_flash_sale_banner');
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
}
