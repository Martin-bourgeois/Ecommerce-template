<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Servir le manifest.json dynamiquement
     */
    public function manifest(): JsonResponse
    {
        $config = config('pwa') ?? [];

        $manifest = [
            'name' => $config['name'] ?? config('app.name'),
            'short_name' => $config['short_name'] ?? 'App',
            'description' => $config['description'] ?? 'Progressive Web App',
            'start_url' => '/',
            'scope' => '/',
            'display' => $config['display'] ?? 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => $config['background_color'] ?? '#ffffff',
            'theme_color' => $config['theme_color'] ?? '#3b82f6',
            'categories' => ['shopping'],
            'icons' => $this->buildIcons($config['icons'] ?? []),
            'screenshots' => $this->buildScreenshots($config['screenshots'] ?? []),
            'shortcuts' => $this->buildShortcuts($config['shortcuts'] ?? []),
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Page offline
     */
    public function offline(): Response
    {
        return response()->view('offline', [], 503);
    }

    /**
     * Ping pour vérifier la connexion
     */
    public function ping(): Response
    {
        return response('OK', 200);
    }

    /**
     * Construire les icônes pour le manifest
     */
    private function buildIcons(array $icons): array
    {
        $result = [];

        foreach ($icons as $size => $path) {
            $isMaskable = str_contains($size, 'maskable');
            $dimensions = preg_replace('/[^0-9]/', '', (string)$size);

            $result[] = [
                'src' => asset($path),
                'sizes' => "{$dimensions}x{$dimensions}",
                'type' => 'image/png',
                'purpose' => $isMaskable ? 'maskable' : 'any',
            ];
        }

        return $result;
    }

    /**
     * Construire les screenshots pour le manifest
     */
    private function buildScreenshots(array $screenshots): array
    {
        return array_map(function ($screenshot) {
            return [
                'src' => asset($screenshot['src']),
                'sizes' => $screenshot['sizes'],
                'type' => 'image/png',
                'form_factor' => $screenshot['form_factor'] ?? 'narrow',
            ];
        }, $screenshots);
    }

    /**
     * Construire les shortcuts pour le manifest
     */
    private function buildShortcuts(array $shortcuts): array
    {
        return array_map(function ($shortcut) {
            return [
                'name' => $shortcut['name'],
                'short_name' => $shortcut['short_name'],
                'description' => $shortcut['description'],
                'url' => $shortcut['url'],
                'icons' => [
                    [
                        'src' => asset('/images/shortcut-' . Str::slug($shortcut['short_name']) . '.png'),
                        'sizes' => '192x192',
                    ],
                ],
            ];
        }, $shortcuts);
    }
}
