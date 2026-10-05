<?php

namespace App\Models;

use App\Traits\HasCustomAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasCustomAsset;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'alt_text',
        'filename',
        'path',
        'disk',
        'mime_type',
        'size',
        'width',
        'height',
        'folder',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_favorite' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Check if this media file is referenced by active store resources.
     * Returns an array of human-readable usage descriptions.
     */
    public function getReferences(): array
    {
        $references = [];
        $p = $this->path;
        $storagePath = 'storage/' . ltrim($p, '/');

        // Check Store Settings (logo, favicon, etc.)
        $settings = StoreSetting::where(function ($q) use ($p, $storagePath) {
            $q->where('value', $p)->orWhere('value', $storagePath);
        })->get();

        foreach ($settings as $setting) {
            $references[] = "Store Setting: " . ucfirst(str_replace('_', ' ', $setting->key));
        }

        // Check Banners
        $bannerCount = Banner::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->count();
        if ($bannerCount > 0) {
            $references[] = "Banner ({$bannerCount} banner(s))";
        }

        // Check Products (primary image)
        $productCount = Product::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->count();
        if ($productCount > 0) {
            $references[] = "Product Primary Image ({$productCount} product(s))";
        }

        // Check Product Gallery Images
        $prodImgCount = ProductImage::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->count();
        if ($prodImgCount > 0) {
            $references[] = "Product Gallery ({$prodImgCount} image(s))";
        }

        // Check Categories
        $catCount = Category::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->count();
        if ($catCount > 0) {
            $references[] = "Category ({$catCount} category(ies))";
        }

        return $references;
    }

    /**
     * Cleanly detach or nullify all references to this media file.
     * Removes associated gallery images and resets orphaned image columns to null.
     */
    public function detachReferences(): int
    {
        $detached = 0;
        $p = $this->path;
        if (empty($p)) {
            return 0;
        }

        $storagePath = 'storage/' . ltrim($p, '/');

        // Detach product gallery images
        $detached += ProductImage::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->delete();

        // Nullify product primary image
        $detached += Product::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->update(['image' => null]);

        // Nullify category images
        $detached += Category::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->update(['image' => null]);

        // Reset banner images to placeholder (banners.image column is not nullable)
        $detached += Banner::where(function ($q) use ($p, $storagePath) {
            $q->where('image', $p)->orWhere('image', $storagePath);
        })->update(['image' => 'assets/images/placeholder.svg']);

        // Nullify store settings
        $detached += StoreSetting::where(function ($q) use ($p, $storagePath) {
            $q->where('value', $p)->orWhere('value', $storagePath);
        })->update(['value' => null]);

        return $detached;
    }

    /**
     * Determine if this file is referenced anywhere.
     */
    public function isReferenced(): bool
    {
        return !empty($this->getReferences());
    }

    /**
     * Get the browser-loadable URL for this file.
     */
    public function getUrlAttribute(): string
    {
        $placeholder = asset('assets/images/placeholder.svg');
        return static::resolveMediaUrl($this->path, $placeholder) ?: $placeholder;
    }

    /**
     * Human readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Determine if file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        if ($this->mime_type && str_starts_with($this->mime_type, 'image/')) {
            return true;
        }

        $ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico']);
    }

    /**
     * Discover and register files on disk into the media_files table.
     */
    public static function syncFromDisk(): int
    {
        $count = 0;
        $disk = Storage::disk('public');
        $allFiles = $disk->allFiles();

        foreach ($allFiles as $filePath) {
            // Ignore hidden files and gitkeep
            if (str_starts_with(basename($filePath), '.')) {
                continue;
            }

            $exists = static::withTrashed()
                ->where(function ($q) use ($filePath) {
                    $q->where('path', $filePath)
                      ->orWhere('path', 'storage/' . $filePath);
                })
                ->exists();

            if (!$exists) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mime = $disk->mimeType($filePath) ?: 'application/octet-stream';
                $size = $disk->size($filePath) ?: 0;
                $folder = explode('/', $filePath)[0] ?? 'general';

                $width = null;
                $height = null;
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $fullPath = $disk->path($filePath);
                    if (file_exists($fullPath)) {
                        $info = @getimagesize($fullPath);
                        if ($info) {
                            $width = $info[0] ?? null;
                            $height = $info[1] ?? null;
                        }
                    }
                }

                static::create([
                    'name' => pathinfo($filePath, PATHINFO_FILENAME),
                    'filename' => basename($filePath),
                    'path' => $filePath,
                    'disk' => 'public',
                    'mime_type' => $mime,
                    'size' => $size,
                    'width' => $width,
                    'height' => $height,
                    'folder' => $folder,
                ]);
                $count++;
            }
        }

        // Also scan public/product and public/uploads
        foreach (['product', 'uploads'] as $pubFolder) {
            $pubDir = public_path($pubFolder);
            if (is_dir($pubDir)) {
                $files = @scandir($pubDir);
                if ($files) {
                    foreach ($files as $f) {
                        if ($f === '.' || $f === '..' || str_starts_with($f, '.')) continue;
                        $fullFPath = $pubDir . DIRECTORY_SEPARATOR . $f;
                        if (!is_file($fullFPath)) continue;
                        $relPath = $pubFolder . '/' . $f;

                        $exists = static::where('path', $relPath)
                            ->orWhere('path', 'storage/' . $relPath)
                            ->exists();

                        if (!$exists) {
                            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                            $mime = @mime_content_type($fullFPath) ?: 'application/octet-stream';
                            $size = @filesize($fullFPath) ?: 0;
                            $width = null;
                            $height = null;
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                                $info = @getimagesize($fullFPath);
                                if ($info) {
                                    $width = $info[0] ?? null;
                                    $height = $info[1] ?? null;
                                }
                            }

                            static::create([
                                'name' => pathinfo($f, PATHINFO_FILENAME),
                                'filename' => $f,
                                'path' => $relPath,
                                'disk' => 'public',
                                'mime_type' => $mime,
                                'size' => $size,
                                'width' => $width,
                                'height' => $height,
                                'folder' => $pubFolder,
                            ]);
                            $count++;
                        }
                    }
                }
            }
        }

        return $count;
    }
}
