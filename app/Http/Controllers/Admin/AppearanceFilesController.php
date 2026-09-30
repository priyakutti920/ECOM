<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AppearanceFilesController extends Controller
{
    /**
     * Display the appearance files & media manager.
     */
    public function index(Request $request)
    {
        // Auto-sync files from disk if table is currently empty
        if (MediaFile::count() === 0) {
            MediaFile::syncFromDisk();
        }

        $query = MediaFile::query();

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
                    'created_at' => $f->created_at?->format('M d, Y'),
                ]),
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'total' => $files->total(),
            ]);
        }

        // Statistics
        $totalFiles = MediaFile::count();
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

        // Products for quick-attach dropdown
        $products = Product::select('id', 'name', 'code')->orderBy('name')->take(100)->get();

        return view('admin.appearance.files.index', compact(
            'files',
            'totalFiles',
            'totalStorage',
            'folders',
            'currentLogoUrl',
            'currentFaviconUrl',
            'products'
        ));
    }

    /**
     * Upload one or multiple files.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif,svg,ico,pdf,txt,css,js'],
            'folder' => ['nullable', 'string', 'max:50'],
        ], [
            'files.required' => 'Please select at least one file to upload.',
            'files.*.max' => 'Each file cannot exceed 20MB.',
            'files.*.mimes' => 'Supported formats: JPG, JPEG, PNG, WEBP, GIF, SVG, ICO, PDF.',
        ]);

        $folder = trim($request->input('folder') ?: 'media');
        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder) ?: 'media';

        $uploaded = [];
        $disk = Storage::disk('public');

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

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
     * Quick actions: Set as Logo, Favicon, or attach to Product.
     */
    public function setAs(Request $request)
    {
        $request->validate([
            'file_id' => ['required', 'exists:media_files,id'],
            'action' => ['required', 'string', 'in:logo,favicon,product,variation'],
            'product_id' => ['nullable', 'required_if:action,product', 'exists:products,id'],
            'variation_id' => ['nullable', 'required_if:action,variation', 'exists:product_variations,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $media = MediaFile::findOrFail($request->file_id);
        $action = $request->input('action');

        if ($action === 'logo') {
            StoreSetting::setValue('logo', $media->path);
            \Illuminate\Support\Facades\Cache::forget('store_settings_all');
            return response()->json([
                'success' => true,
                'message' => 'Store Logo updated successfully! It is now active on the store.',
                'url' => StoreSetting::getLogoUrl(),
            ]);
        }

        if ($action === 'favicon') {
            StoreSetting::setValue('favicon', $media->path);
            \Illuminate\Support\Facades\Cache::forget('store_settings_all');
            return response()->json([
                'success' => true,
                'message' => 'Store Favicon updated successfully! It is now active on the store.',
                'url' => StoreSetting::getFaviconUrl(),
            ]);
        }

        if ($action === 'product') {
            $productId = (int) $request->input('product_id');
            $product = Product::findOrFail($productId);
            $isPrimary = $request->boolean('is_primary');

            if ($isPrimary) {
                // Remove primary flag from other images of this product
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

            // Also update legacy single image field if primary or empty
            if ($isPrimary || empty($product->image)) {
                $product->update(['image' => $media->path]);
            }

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
     * Delete a media file.
     */
    public function destroy(Request $request, $id)
    {
        $media = MediaFile::findOrFail($id);

        // Delete from disk if exists
        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        }

        return back()->with('success', 'File deleted successfully.');
    }

    /**
     * Synchronize files from storage disk into database.
     */
    public function sync()
    {
        $count = MediaFile::syncFromDisk();

        return back()->with('success', "Storage disk synchronized! {$count} new file(s) indexed.");
    }
}
