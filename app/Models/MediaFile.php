<?php

namespace App\Models;

use App\Traits\HasCustomAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasCustomAsset;

    protected $fillable = [
        'name',
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
        ];
    }

    /**
     * Get the browser-loadable URL for this file.
     */
    public function getUrlAttribute(): string
    {
        return static::resolveMediaUrl($this->path) ?? asset('storage/' . ltrim($this->path, '/'));
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

            $exists = static::where('path', $filePath)
                ->orWhere('path', 'storage/' . $filePath)
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
