<?php

define('APP_ROOT', __DIR__ . '/ui');
define('APP_URL', 'https://example.com');
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function t(string $key): string { return $key; }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }

foreach ([[], [['id' => 10, 'title' => 'Test Tool', 'description' => 'Test description', 'price' => 10, 'currency' => 'USD', 'thumbnail' => 'test.png']]] as $courses) {
    ob_start();
    require dirname(__DIR__) . '/app/views/courses/tools.php';
    $html = ob_get_clean();
    $document = new DOMDocument();
    @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($document);
    $cards = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " course-grid ")]/*[contains(concat(" ", normalize-space(@class), " "), " course-card ")]');
    if ($cards->length !== count($courses) + 1
        || !str_contains($cards->item(0)->textContent, 'Telegram Adder Pro')
        || !str_contains($html, '/telegram-adder-pro#pricing')
        || str_contains($html, 'width:72px')
        || str_contains($html, 'tools.empty')) {
        throw new RuntimeException('Telegram must remain a full catalog card with working plan navigation, including an otherwise empty catalog.');
    }
    if ($courses && (!str_contains($html, '/checkout?course_id=10') || !str_contains($html, '/course/10'))) {
        throw new RuntimeException('Existing tool purchase and detail links must remain unchanged.');
    }
}
echo "PASS: Telegram catalog card, empty catalog, and existing purchase links\n";