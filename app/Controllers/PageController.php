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
}
