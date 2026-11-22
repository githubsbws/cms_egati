<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * One place for back office uploads. Files go to the public disk, in a folder named
 * after the feature and split by year/month, e.g. news/2026/11/<random>.webp.
 * The returned path is what gets saved in the database.
 */
class UploadHelper
{
    public static function store(UploadedFile $file, string $feature): string
    {
        $folder = trim($feature, '/').'/'.now()->format('Y/m');

        return $file->store($folder, 'public');
    }

    /**
     * Delete a stored file. Files shipped with the design (web-assets/...) are never touched.
     */
    public static function delete(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, 'web-assets/')) {
            return;
        }
        Storage::disk('public')->delete($path);
    }
}
