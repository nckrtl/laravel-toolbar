<?php

namespace NckRtl\Toolbar\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    /**
     * Serve toolbar assets from Vite's build output
     */
    public function __invoke(string $asset): Response
    {
        $assetPath = __DIR__.'/../../build/assets/'.$asset;

        if (! file_exists($assetPath)) {
            abort(404);
        }

        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
        ];

        $extension = pathinfo($asset, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[$extension] ?? 'text/plain';

        return response(file_get_contents($assetPath))
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000, immutable');
    }
}
