<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ConvertsImagesToWebp
{
    public static function bootConvertsImagesToWebp()
    {
        static::saving(function ($model) {
            if (method_exists($model, 'webpImageFields')) {
                $imageFields = $model->webpImageFields() ?: [];
                foreach ($imageFields as $field) {
                    if ($model->isDirty($field) && $model->$field) {
                        $model->$field = static::convertToWebp($model->$field);
                    }
                }
            }
        });
    }

    protected static function convertToWebp($filePath)
    {
        // Support array paths (sometimes Filament passes array of paths)
        if (is_array($filePath)) {
            $converted = [];
            foreach ($filePath as $path) {
                $converted[] = static::convertToWebpSingle($path);
            }
            return $converted;
        }

        return static::convertToWebpSingle($filePath);
    }

    protected static function convertToWebpSingle($filePath)
    {
        if (empty($filePath) || !is_string($filePath)) {
            return $filePath;
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($filePath)) {
            return $filePath;
        }

        $fullPath = $disk->path($filePath);
        $info = @getimagesize($fullPath);
        if (!$info) {
            return $filePath; // Not an image (e.g. PDF)
        }

        $mime = $info['mime'];
        if ($mime === 'image/webp') {
            return $filePath; // Already webp
        }

        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($fullPath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($fullPath);
                break;
            default:
                return $filePath; // Unsupported
        }

        if (!$image) {
            return $filePath;
        }

        $pathInfo = pathinfo($filePath);
        $dir = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'] . '/';
        $newRelativePath = $dir . $pathInfo['filename'] . '.webp';
        $newFullPath = $disk->path($newRelativePath);

        if (@imagewebp($image, $newFullPath, 80)) {
            @imagedestroy($image);
            $disk->delete($filePath); // Delete old image
            return $newRelativePath;
        }

        @imagedestroy($image);
        return $filePath;
    }
}
