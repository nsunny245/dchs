<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardImageController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $path = ltrim(rawurldecode((string) $request->query('path')), '/');

        abort_if(blank($path) || str_contains($path, '..'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $mimeType = $disk->mimeType($path);
        abort_unless(is_string($mimeType) && str_starts_with($mimeType, 'image/'), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
