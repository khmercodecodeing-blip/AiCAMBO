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
foreach (['receipt-text', 'languages'] as $id) {
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
        || substr_count($html, 'ui.svg#languages') !== 2
        || !str_contains($html, 'aria-current="true"')
        || preg_match('/[\x{1F1E6}-\x{1F1FF}]/u', $html)) {
        throw new RuntimeException('Language icon or accessible state failed: ' . $language);
    }
}
echo "PASS: Local SVG symbols and desktop/mobile language markup\n";