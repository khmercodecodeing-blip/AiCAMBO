<?php

define('APP_NAME', 'Test Store');
define('APP_URL', 'https://example.com');
$_SESSION = [];
$_SERVER['REQUEST_URI'] = '/my-downloads';
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function asset(string $path): string { return APP_URL . '/public/assets/' . $path; }
function current_lang(): string { return $GLOBALS['testLanguage']; }
function t(string $key): string { return $key; }
function get_flash(string $type): ?string { return null; }

$sprite = simplexml_load_file(dirname(__DIR__) . '/public/assets/images/icons/ui.svg');
if ($sprite === false) {
    throw new RuntimeException('Invalid SVG sprite.');
}
foreach (['receipt-text', 'languages', 'book-open-text'] as $id) {
    if (count($sprite->xpath('//*[local-name()="symbol" and @id="' . $id . '"]')) !== 1) {
        throw new RuntimeException('Missing or duplicate icon: ' . $id);
    }
}
foreach (['km', 'en'] as $language) {
    $GLOBALS['testLanguage'] = $language;
    ob_start();
    require dirname(__DIR__) . '/app/views/layouts/header.php';
    $html = ob_get_clean();
    if (!str_contains($html, '<html lang="' . $language . '">')
        || substr_count($html, 'ui.svg#languages') !== 1
        || !str_contains($html, '#book-open-text')
        || !str_contains($html, 'aria-current="true"')
        || !str_contains($html, 'aria-expanded="false" aria-controls="primary-navigation"')
        || !str_contains($html, 'id="primary-navigation"')
        || preg_match('/[\x{1F1E6}-\x{1F1FF}]/u', $html)) {
        throw new RuntimeException('Language icon or accessible state failed: ' . $language);
    }
}
$_SERVER['REQUEST_URI'] = '/tools';
ob_start();
require dirname(__DIR__) . '/app/views/layouts/header.php';
$html = ob_get_clean();
if (!str_contains($html, '<button type="button" id="search-clear-btn"')
    || !str_contains($html, 'id="course-search-input" aria-label=')) {
    throw new RuntimeException('Catalog search and clear control must be accessible.');
}
foreach (['km', 'en'] as $language) {
    $labels = require dirname(__DIR__) . '/config/lang/' . $language . '.php';
    foreach (['btn.learn_more', 'btn.buy_now', 'nav.menu', 'search.clear'] as $label) {
        if (empty($labels[$label])) {
            throw new RuntimeException('Missing mobile label: ' . $language . '/' . $label);
        }
    }
}
echo "PASS: Local SVG symbols and desktop/mobile language markup\n";