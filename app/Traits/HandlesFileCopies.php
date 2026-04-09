<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileCopies
{
    /**
     * Copy a file from one storage path to another within the public disk.
     *
     * @param string|null $sourcePath
     * @param string $destinationFolder
     * @param string $prefix
     * @return string|null
     */
    protected function copyTenantFile(?string $sourcePath, string $destinationFolder, string $prefix = ''): ?string
    {
        if (!$sourcePath || !Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = ($prefix ?: Str::random(10)) . '_' . time() . '.' . $extension;
        $newPath = trim($destinationFolder, '/') . '/' . $filename;

        if (Storage::disk('public')->copy($sourcePath, $newPath)) {
            return $newPath;
        }

        return null;
    }

    /**
     * Store an uploaded file.
     *
     * @param $file
     * @param string $destinationFolder
     * @param string|null $oldPath
     * @return string|null
     */
    protected function storeTenantFile($file, string $destinationFolder, ?string $oldPath = null): ?string
    {
        if (!$file) {
            return $oldPath;
        }

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->store($destinationFolder, 'public');
    }
}
