<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileCopies
{
    /**
     * Store a new uploaded file without trusting any client-supplied source path.
     *
     * @param mixed $file
     */
    protected function storeTenantFile($file, string $destinationFolder): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store(trim($destinationFolder, '/'), 'public');
    }

    /**
     * Copy a file from an approved public-disk source path into a tenant-managed folder.
     *
     * @param string|null $sourcePath
     * @param string $destinationFolder
     * @param array<int, string> $allowedSourcePrefixes
     * @param string $prefix
     * @return string|null
     */
    protected function copyTenantFile(
        ?string $sourcePath,
        string $destinationFolder,
        array $allowedSourcePrefixes = [],
        string $prefix = ''
    ): ?string
    {
        $sourcePath = $this->sanitizePublicPath($sourcePath);

        if (!$sourcePath || !$this->isAllowedPublicPath($sourcePath, $allowedSourcePrefixes)) {
            return null;
        }

        if (!Storage::disk('public')->exists($sourcePath)) {
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
     * Replace an existing tenant-managed file with a newly uploaded file.
     *
     * @param mixed $file
     * @param string $destinationFolder
     * @param string|null $existingPath
     * @param array<int, string> $allowedExistingPrefixes
     * @return string|null
     */
    protected function replaceTenantFile(
        $file,
        string $destinationFolder,
        ?string $existingPath = null,
        array $allowedExistingPrefixes = []
    ): ?string
    {
        if (!$file) {
            return $existingPath;
        }

        $existingPath = $this->sanitizePublicPath($existingPath);

        if ($existingPath && $this->isAllowedPublicPath($existingPath, $allowedExistingPrefixes) && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $this->storeTenantFile($file, $destinationFolder);
    }

    /**
     * Validate and normalize a relative public-disk path.
     */
    protected function sanitizePublicPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    /**
     * Check whether a public-disk path is inside one of the approved prefixes.
     *
     * @param array<int, string> $allowedPrefixes
     */
    protected function isAllowedPublicPath(?string $path, array $allowedPrefixes): bool
    {
        $path = $this->sanitizePublicPath($path);

        if (!$path || empty($allowedPrefixes)) {
            return false;
        }

        foreach ($allowedPrefixes as $prefix) {
            $normalizedPrefix = trim(str_replace('\\', '/', $prefix), '/');

            if ($normalizedPrefix !== '' && ($path === $normalizedPrefix || str_starts_with($path, $normalizedPrefix . '/'))) {
                return true;
            }
        }

        return false;
    }
}
