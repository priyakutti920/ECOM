<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AppearanceFilesController extends Controller
{
    /**
     * Display the appearance files & media manager.
     */
    public function index(Request $request)
    {
        // Auto-sync files from disk if table is currently completely empty
        if (MediaFile::withTrashed()->count() === 0) {
            MediaFile::syncFromDisk();
        }

        $status = $request->input('status', 'active');
        $query = MediaFile::query();

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Search by name or filename
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('filename', 'like', "%{$q}%")
                   ->orWhere('path', 'like', "%{$q}%");
            });
        }

        // Filter by folder / category
        if ($request->filled('folder') && $request->folder !== 'all') {
            $query->where('folder', $request->folder);
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            if ($request->type === 'image') {
                $query->where(function ($sq) {
                    $sq->where('mime_type', 'like', 'image/%')
                       ->orWhere('filename', 'like', '%.png')
                       ->orWhere('filename', 'like', '%.jpg')
                       ->orWhere('filename', 'like', '%.jpeg')
                       ->orWhere('filename', 'like', '%.webp')
                       ->orWhere('filename', 'like', '%.svg')
                       ->orWhere('filename', 'like', '%.ico');
                });
            }
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'size_desc':
                $query->orderBy('size', 'desc');
                break;
            case 'size_asc':
                $query->orderBy('size', 'asc');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $files = $query->paginate(24)->withQueryString();

        // If requested via AJAX or JSON, return lightweight JSON response for Media Picker
        if ($request->wantsJson() || $request->ajax() || $request->has('json')) {
            return response()->json([
                'success' => true,
                'data' => $files->getCollection()->map(fn ($f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                    'filename' => $f->filename,
                    'url' => $f->url,
                    'path' => $f->path,
                    'size' => $f->formatted_size,
                    'folder' => $f->folder,
                    'is_image' => $f->is_image,
                    'width' => $f->width,
                    'height' => $f->height,
                    'is_trashed' => $f->trashed(),
                    'created_at' => $f->created_at?->format('M d, Y'),
                ]),
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'total' => $files->total(),
            ]);
        }

        // Statistics
        $totalFiles = MediaFile::count();
        $trashedCount = MediaFile::onlyTrashed()->count();
        $totalBytes = (int) MediaFile::sum('size');
        if ($totalBytes >= 1048576) {
            $totalStorage = number_format($totalBytes / 1048576, 1) . ' MB';
        } elseif ($totalBytes >= 1024) {
            $totalStorage = number_format($totalBytes / 1024, 0) . ' KB';
        } else {
            $totalStorage = $totalBytes . ' B';
        }

        // Folders with count
        $folders = MediaFile::select('folder')
            ->selectRaw('count(*) as count')
            ->groupBy('folder')
            ->pluck('count', 'folder')
            ->toArray();

        // Current Logo and Favicon
        $currentLogoUrl = StoreSetting::getLogoUrl();
        $currentFaviconUrl = StoreSetting::getFaviconUrl();

        // Products & Categories for quick-attach dropdowns
        $products = Product::select('id', 'name', 'code')->orderBy('name')->take(100)->get();
        $categories = Category::select('id', 'name')->where('status', 0)->orderBy('name')->take(100)->get();

        return view('admin.appearance.files.index', compact(
            'files',
            'totalFiles',
            'trashedCount',
            'status',
            'totalStorage',
            'folders',
            'currentLogoUrl',
            'currentFaviconUrl',
            'products',
            'categories'
        ));
    }

    /**
     * Upload one or multiple files.
     */
    public function upload(Request $request)
    {
        $rawFiles = $request->file('files') ?: ($request->file('file') ? [$request->file('file')] : []);
        if (empty($rawFiles)) {
            return back()->withErrors(['files' => 'Please select at least one file to upload.']);
        }
        if (!is_array($rawFiles)) {
            $rawFiles = [$rawFiles];
        }

        $folder = trim($request->input('folder') ?: 'media');
        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder) ?: 'media';

        $uploaded = [];
        $disk = Storage::disk('public');

        foreach ($rawFiles as $file) {
            $originalName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico', 'pdf', 'txt', 'css', 'js'])) {
                continue;
            }
            if ($file->getSize() > 20971520) {
                continue;
            }

            $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $filename = time() . '_' . ($cleanBase ? substr($cleanBase, 0, 30) . '_' : '') . Str::random(6) . '.' . $ext;
            $path = $file->storeAs($folder, $filename, 'public');

            // Mirror to public/storage if directory exists
            $publicDir = public_path('storage/' . $folder);
            if (is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
                if (!is_dir($publicDir)) {
                    @mkdir($publicDir, 0755, true);
                }
                @copy($disk->path($path), public_path('storage/' . $path));
            }

            $mime = $file->getClientMimeType() ?: 'application/octet-stream';
            $size = $file->getSize() ?: 0;

            $width = null;
            $height = null;
            if (str_starts_with($mime, 'image/')) {
                $info = @getimagesize($file->getRealPath());
                if ($info) {
                    $width = $info[0] ?? null;
                    $height = $info[1] ?? null;
                }
            }

            $media = MediaFile::create([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'filename' => $filename,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $mime,
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'folder' => $folder,
            ]);

            $uploaded[] = [
                'id' => $media->id,
                'name' => $media->name,
                'filename' => $media->filename,
                'url' => $media->url,
                'path' => $media->path,
                'size' => $media->formatted_size,
                'width' => $media->width,
                'height' => $media->height,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' file(s) uploaded successfully.',
                'files' => $uploaded,
            ]);
        }

        return back()->with('success', count($uploaded) . ' file(s) uploaded successfully.');
    }

    /**
     * Bulk image import endpoint with individual status reporting.
     */
    public function bulkUpload(Request $request)
    {
        $folder = trim($request->input('folder') ?: 'media');
        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder) ?: 'media';

        if (!$request->hasFile('files')) {
            return response()->json([
                'success' => false,
                'message' => 'No files were provided for upload.',
            ], 422);
        }

        $files = $request->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        $uploaded = [];
        $failed = [];
        $disk = Storage::disk('public');
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml', 'image/x-icon'];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'];

        foreach ($files as $file) {
            $origName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $size = $file->getSize();

            // 1. Extension & Executable check
            if (in_array($ext, ['php', 'phtml', 'phar', 'sh', 'exe', 'bat', 'cmd', 'js', 'html', 'htm'])) {
                $failed[] = [
                    'name' => $origName,
                    'error' => 'Executable scripts are strictly forbidden.',
                ];
                continue;
            }

            if (!in_array($ext, $allowedExts)) {
                $failed[] = [
                    'name' => $origName,
                    'error' => "Unsupported format (.{$ext}). Allowed: JPG, PNG, WebP, GIF, SVG, ICO.",
                ];
                continue;
            }

            if ($size > 12582912) { // 12 MB limit
                $failed[] = [
                    'name' => $origName,
                    'error' => 'File exceeds 12MB limit.',
                ];
                continue;
            }

            // 2. MIME check
            $mime = $file->getClientMimeType() ?: 'application/octet-stream';
            if (!in_array($mime, $allowedMimes)) {
                $failed[] = [
                    'name' => $origName,
                    'error' => 'File type verification failed: invalid image MIME.',
                ];
                continue;
            }

            // 3. Store file safely
            $cleanBase = Str::slug(pathinfo($origName, PATHINFO_FILENAME));
            $filename = time() . '_' . ($cleanBase ? substr($cleanBase, 0, 30) . '_' : '') . Str::random(8) . '.' . $ext;
            $path = $file->storeAs($folder, $filename, 'public');

            // Mirror to public/storage if directory exists
            $publicDir = public_path('storage/' . $folder);
            if (is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
                if (!is_dir($publicDir)) {
                    @mkdir($publicDir, 0755, true);
                }
                @copy($disk->path($path), public_path('storage/' . $path));
            }

            // Dimensions
            $width = null;
            $height = null;
            if (str_starts_with($mime, 'image/')) {
                $info = @getimagesize($file->getRealPath());
                if ($info) {
                    $width = $info[0] ?? null;
                    $height = $info[1] ?? null;
                }
            }

            $media = MediaFile::create([
                'name' => pathinfo($origName, PATHINFO_FILENAME),
                'filename' => $filename,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $mime,
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'folder' => $folder,
            ]);

            $uploaded[] = [
                'id' => $media->id,
                'name' => $media->name,
                'filename' => $media->filename,
                'url' => $media->url,
                'path' => $media->path,
                'size' => $media->formatted_size,
                'width' => $media->width,
                'height' => $media->height,
                'folder' => $media->folder,
                'created_at' => $media->created_at?->format('d M'),
            ];
        }

        $totalSuccess = count($uploaded);
        $totalFailed = count($failed);

        if ($totalSuccess === 0 && $totalFailed > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . implode('; ', array_column($failed, 'error')),
                'data' => [
                    'uploaded' => [],
                    'failed' => $failed,
                ],
            ], 422);
        }

        return response()->json([
            'success' => $totalSuccess > 0,
            'message' => "{$totalSuccess} file(s) imported successfully." . ($totalFailed > 0 ? " ({$totalFailed} failed)" : ''),
            'data' => [
                'uploaded' => $uploaded,
                'failed' => $failed,
            ],
        ]);
    }

    /**
     * Replace an existing media file with a new uploaded file.
     */
    public function replace(Request $request, $id)
    {
        $file = $request->file('image') ?: $request->file('file');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'The image or file field is required.',
                'errors' => ['image' => ['The image field is required.']],
            ], 422);
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported format. Allowed: JPG, PNG, WebP, GIF, SVG, ICO.',
            ], 422);
        }

        $media = MediaFile::withTrashed()->findOrFail($id);
        $oldPath = $media->path;
        $folder = $media->folder ?: 'media';

        $origName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $cleanBase = Str::slug(pathinfo($origName, PATHINFO_FILENAME));
        $filename = time() . '_' . ($cleanBase ? substr($cleanBase, 0, 30) . '_' : '') . Str::random(8) . '.' . $ext;
        $newPath = $file->storeAs($folder, $filename, 'public');

        // Mirror to public/storage if directory exists
        $publicDir = public_path('storage/' . $folder);
        if (is_dir(public_path('storage')) && !is_link(public_path('storage'))) {
            if (!is_dir($publicDir)) {
                @mkdir($publicDir, 0755, true);
            }
            @copy(Storage::disk('public')->path($newPath), public_path('storage/' . $newPath));
        }

        $size = $file->getSize() ?: 0;
        $mime = $file->getClientMimeType() ?: 'image/jpeg';
        $width = null;
        $height = null;
        $info = @getimagesize($file->getRealPath());
        if ($info) {
            $width = $info[0] ?? null;
            $height = $info[1] ?? null;
        }

        // Update MediaFile record
        $media->update([
            'filename' => $filename,
            'path' => $newPath,
            'mime_type' => $mime,
            'size' => $size,
            'width' => $width,
            'height' => $height,
        ]);

        // Propagate updates to referencing models
        $oldStoragePath = 'storage/' . ltrim($oldPath, '/');
        $newStoragePath = 'storage/' . ltrim($newPath, '/');

        Banner::where('image', $oldPath)->orWhere('image', $oldStoragePath)->update(['image' => $newPath]);
        Category::where('image', $oldPath)->orWhere('image', $oldStoragePath)->update(['image' => $newPath]);
        Product::where('image', $oldPath)->orWhere('image', $oldStoragePath)->update(['image' => $newPath]);
        ProductImage::where('image', $oldPath)->orWhere('image', $oldStoragePath)->update(['image' => $newPath]);
        StoreSetting::where('value', $oldPath)->orWhere('value', $oldStoragePath)->update(['value' => $newPath]);

        // Invalidate all caches
        StoreSetting::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Image replaced successfully! All references updated.',
            'data' => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->url,
                'path' => $media->path,
                'size' => $media->formatted_size,
                'width' => $media->width,
                'height' => $media->height,
            ],
        ]);
    }

    /**
     * Context-aware image assignment: Logo, Favicon, Banner, Category, Product, Variation, Avatar.
     */
    public function setAs(Request $request)
    {
        $request->validate([
            'file_id' => ['required', 'exists:media_files,id'],
            'action' => ['required', 'string', 'in:logo,favicon,banner,category,product,variation,avatar'],
            'product_id' => ['nullable', 'required_if:action,product', 'exists:products,id'],
            'category_id' => ['nullable', 'required_if:action,category', 'exists:categories,id'],
            'variation_id' => ['nullable', 'required_if:action,variation', 'exists:product_variations,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $media = MediaFile::findOrFail($request->file_id);
        $action = $request->input('action');

        if ($action === 'logo') {
            StoreSetting::setValue('logo', $media->path);
            StoreSetting::clearCache();
            return response()->json([
                'success' => true,
                'message' => 'Store Logo updated successfully! It is now active on the store.',
                'url' => StoreSetting::getLogoUrl(),
            ]);
        }

        if ($action === 'favicon') {
            StoreSetting::setValue('favicon', $media->path);
            StoreSetting::clearCache();
            return response()->json([
                'success' => true,
                'message' => 'Store Favicon updated successfully! It is now active on the store.',
                'url' => StoreSetting::getFaviconUrl(),
            ]);
        }

        if ($action === 'banner') {
            $maxOrder = Banner::max('sort_order') ?? 0;
            $title = $request->input('banner_title') ?: $media->name;
            $banner = Banner::create([
                'image' => $media->path,
                'primary_text' => $title,
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
            ]);
            StoreSetting::clearCache();
            return response()->json([
                'success' => true,
                'message' => 'New Homepage Banner created from media asset!',
                'banner_id' => $banner->id,
                'url' => $banner->image_url,
            ]);
        }

        if ($action === 'category') {
            $categoryId = (int) $request->input('category_id');
            $category = Category::findOrFail($categoryId);
            $category->update(['image' => $media->path]);
            StoreSetting::clearCache();
            return response()->json([
                'success' => true,
                'message' => "Category \"{$category->name}\" image updated successfully!",
                'category_id' => $category->id,
                'url' => $category->image_url,
            ]);
        }

        if ($action === 'avatar') {
            $user = auth()->user();
            if ($user) {
                $user->update(['avatar' => $media->path]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Your admin avatar has been updated!',
                'url' => $media->url,
            ]);
        }

        if ($action === 'product') {
            $productId = (int) $request->input('product_id');
            $product = Product::findOrFail($productId);
            $isPrimary = $request->boolean('is_primary');

            if ($isPrimary) {
                ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
            }

            $existingCount = ProductImage::where('product_id', $productId)->count();
            if ($existingCount === 0) {
                $isPrimary = true;
            }

            $img = ProductImage::create([
                'product_id' => $productId,
                'image' => $media->path,
                'sort_order' => $isPrimary ? 0 : ($existingCount + 1),
                'is_primary' => $isPrimary,
            ]);

            if ($isPrimary || empty($product->image)) {
                $product->update(['image' => $media->path]);
            }

            StoreSetting::clearCache();

            return response()->json([
                'success' => true,
                'message' => "Image attached to product \"{$product->name}\" successfully!",
                'product_id' => $productId,
                'product_name' => $product->name,
                'image_id' => $img->id,
                'image_path' => $img->image,
                'image_url' => $img->url,
                'is_primary' => $img->is_primary,
            ]);
        }

        if ($action === 'variation') {
            $variationId = (int) $request->input('variation_id');
            $variation = \App\Models\ProductVariation::findOrFail($variationId);
            $isPrimary = $request->boolean('is_primary');

            if ($isPrimary) {
                \App\Models\ProductVariationImage::where('product_variation_id', $variationId)->update(['is_primary' => false]);
            }

            $existingCount = \App\Models\ProductVariationImage::where('product_variation_id', $variationId)->count();
            if ($existingCount === 0) {
                $isPrimary = true;
            }

            $vimg = \App\Models\ProductVariationImage::create([
                'product_variation_id' => $variationId,
                'image' => $media->path,
                'sort_order' => $isPrimary ? 0 : ($existingCount + 1),
                'is_primary' => $isPrimary,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image attached to variation successfully!',
                'variation_id' => $variationId,
                'image_id' => $vimg->id,
                'image_path' => $vimg->image,
                'image_url' => $vimg->url,
                'is_primary' => $vimg->is_primary,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Soft delete a media file.
     */
    public function destroy(Request $request, $id)
    {
        $media = MediaFile::findOrFail($id);
        $media->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File moved to Trash.',
            ]);
        }

        return back()->with('success', 'File moved to Trash.');
    }

    /**
     * Restore a soft-deleted media file.
     */
    public function restore(Request $request, $id)
    {
        $media = MediaFile::onlyTrashed()->findOrFail($id);
        $media->restore();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File restored from Trash.',
            ]);
        }

        return back()->with('success', 'File restored from Trash.');
    }

    /**
     * Permanently delete a media file with clean reference detachment and physical disk cleanup.
     */
    public function forceDelete(Request $request, $id)
    {
        $media = MediaFile::withTrashed()->findOrFail($id);

        // Check if referenced before permanent delete, unless force is explicitly requested
        $refs = $media->getReferences();
        if (!empty($refs) && !$request->boolean('force')) {
            $msg = 'Cannot permanently delete this file because it is currently assigned to: ' . implode(', ', $refs) . '. Please detach or reassign it first.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'is_referenced' => true,
                    'references' => $refs,
                ], 422);
            }
            return back()->with('error', $msg);
        }

        // Cleanly detach all references so no broken links or orphaned gallery records remain
        $media->detachReferences();

        // Delete physical files from disk
        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }
        if ($media->path && file_exists(public_path('storage/' . $media->path))) {
            @unlink(public_path('storage/' . $media->path));
        }

        $media->forceDelete();

        $message = 'File permanently deleted from disk and database.';
        if (!empty($refs)) {
            $message .= ' Also cleanly detached from: ' . implode(', ', $refs) . '.';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Synchronize files from storage disk into database.
     */
    public function sync()
    {
        $count = MediaFile::syncFromDisk();

        return back()->with('success', "Storage disk synchronized! {$count} new file(s) indexed.");
    }

    /**
     * Safely serve public storage assets with path traversal protection and broken-image placeholder fallback.
     */
    public function serveStorageFile(string $path)
    {
        // Directory traversal protection
        if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            abort(403, 'Forbidden');
        }

        $fullPath = storage_path('app/public/' . $path);
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            $altPath = public_path('storage/' . $path);
            if (file_exists($altPath) && is_file($altPath)) {
                $fullPath = $altPath;
            } else {
                // If requested an image, stream the clean vector placeholder
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'])) {
                    $placeholder = public_path('assets/images/placeholder.svg');
                    if (file_exists($placeholder)) {
                        return response()->file($placeholder, [
                            'Content-Type' => 'image/svg+xml',
                            'Cache-Control' => 'no-cache, private',
                        ]);
                    }
                }
                abort(404, 'File not found');
            }
        }

        $mime = @mime_content_type($fullPath) ?: 'application/octet-stream';
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Perform bulk actions on selected media files.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'action' => 'required|string|in:delete,restore,force_delete,move_folder',
            'folder' => 'nullable|string|in:media,banner,product,appearance,settings',
        ]);

        $ids = $request->input('ids', []);
        $action = $request->input('action');
        $affected = 0;
        $skipped = [];

        if ($action === 'delete') {
            $files = MediaFile::whereIn('id', $ids)->get();
            foreach ($files as $file) {
                $file->delete();
                $affected++;
            }
            $msg = "{$affected} file(s) moved to Trash.";
        } elseif ($action === 'restore') {
            $files = MediaFile::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($files as $file) {
                $file->restore();
                $affected++;
            }
            $msg = "{$affected} file(s) restored from Trash.";
        } elseif ($action === 'force_delete') {
            $force = $request->boolean('force');
            $files = MediaFile::withTrashed()->whereIn('id', $ids)->get();
            foreach ($files as $file) {
                $refs = $file->getReferences();
                if (!empty($refs) && !$force) {
                    $skipped[] = "File '{$file->name}' is in use by: " . implode(', ', $refs);
                    continue;
                }
                $file->detachReferences();
                if ($file->path && Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->delete($file->path);
                }
                if ($file->path && file_exists(public_path('storage/' . $file->path))) {
                    @unlink(public_path('storage/' . $file->path));
                }
                $file->forceDelete();
                $affected++;
            }
            $msg = "{$affected} file(s) permanently deleted.";
            if (count($skipped) > 0) {
                $msg .= ' ' . count($skipped) . ' file(s) skipped because they are in active use.';
            }
        } elseif ($action === 'move_folder') {
            $folder = $request->input('folder') ?: 'media';
            $affected = MediaFile::withTrashed()->whereIn('id', $ids)->update(['folder' => $folder]);
            $msg = "{$affected} file(s) moved to folder '{$folder}'.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'affected' => $affected,
                'skipped' => $skipped,
            ]);
        }

        return back()->with('success', $msg);
    }
}
