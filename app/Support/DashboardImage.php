<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DashboardImage
{
    public static function url(mixed $storedPath): ?string
    {
        $path = self::normalizePath($storedPath);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return URL::temporarySignedRoute(
            'dashboard.images.show',
            now()->addHour(),
            ['path' => $path],
        );
    }

    public static function avatar(string $name): string
    {
        $initials = Str::of($name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        $initials = $initials ?: 'DG';
        $safeInitials = htmlspecialchars($initials, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">
                <rect width="96" height="96" rx="48" fill="#082245"/>
                <circle cx="48" cy="48" r="44" fill="none" stroke="#C98D18" stroke-width="3"/>
                <text x="48" y="57" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="700" fill="#FFFFFF">{$safeInitials}</text>
            </svg>
            SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private static function normalizePath(mixed $storedPath): ?string
    {
        if (is_array($storedPath)) {
            foreach ($storedPath as $path) {
                if ($normalized = self::normalizePath($path)) {
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
                return self::normalizePath($decoded);
            }
        }

        $urlPath = parse_url($storedPath, PHP_URL_PATH);
        $path = ltrim(rawurldecode(is_string($urlPath) ? $urlPath : $storedPath), '/');
        $path = preg_replace('#^storage/#', '', $path);

        if (! is_string($path) || blank($path) || str_contains($path, '..')) {
            return null;
        }

        return $path;
    }
}
