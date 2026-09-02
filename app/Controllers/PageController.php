<?php

namespace App\Controllers;

/**
 * Page Controller — Handles static informational pages (Privacy/Refund Policy, etc.)
 */
class PageController
{
    /**
     * Display Privacy & Refund Policy page
     */
    public function policy(): void
    {
        $pageTitle = 'Privacy & Refund Policy — ' . APP_NAME;
        require APP_ROOT . '/app/views/policy.php';
    }

    /**
     * Serve the PWA web app manifest so installed icon/name follow live config
     */
    public function manifest(): void
    {
        header('Content-Type: application/manifest+json');

        echo json_encode([
            'name'             => APP_NAME,
            'short_name'       => APP_NAME,
            'start_url'        => APP_URL . '/',
            'scope'            => APP_URL . '/',
            'display'          => 'standalone',
            'background_color' => '#0a1628',
            'theme_color'      => '#0a1628',
            'orientation'      => 'portrait-primary',
            'icons' => [
                [
                    'src'   => asset('images/icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type'  => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'   => asset('images/icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'   => asset('images/icons/icon-512-maskable.png'),
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);
    }
}
