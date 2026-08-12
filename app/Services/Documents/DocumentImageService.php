<?php

namespace App\Services\Documents;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class DocumentImageService
{
    /**
     * Convert an image stored on Laravel's public disk to an embeddable data URI.
     *
     * This deliberately avoids public/storage URLs so generated and printable
     * documents continue to work when a shared host has no storage symlink.
     */
    public function fromPublicDisk(mixed $storedPath): ?string
    {
        $path = $this->normalizePath($storedPath);

        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        $mimeType = $this->imageMimeType($disk, $path, $contents);

        if ($mimeType === null) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mimeType, base64_encode($contents));
    }

    private function normalizePath(mixed $storedPath): ?string
    {
        if (is_array($storedPath)) {
            foreach ($storedPath as $path) {
                if ($normalized = $this->normalizePath($path)) {
                    return $normalized;
                }
            }

            return null;
        }

        if (! is_string($storedPath) || blank($storedPath)) {
            return null;
        }

        $storedPath = trim($storedPath);

        if (str_starts_with($storedPath, '[')) {
            $decoded = json_decode($storedPath, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizePath($decoded);
            }
        }

        $urlPath = parse_url($storedPath, PHP_URL_PATH);
        $path = is_string($urlPath) ? $urlPath : $storedPath;
        $path = ltrim(rawurldecode($path), '/');

        return preg_replace('#^storage/#', '', $path) ?: null;
    }

    private function imageMimeType(FilesystemAdapter $disk, string $path, string $contents): ?string
    {
        $mimeType = $disk->mimeType($path);

        if (! is_string($mimeType) || ! str_starts_with($mimeType, 'image/')) {
            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->buffer($contents) ?: null;
        }

        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ], true) ? $mimeType : null;
    }
}
