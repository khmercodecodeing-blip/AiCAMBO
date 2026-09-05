<?php

require dirname(__DIR__) . '/vendor/autoload.php';
define('APP_ROOT', dirname(__DIR__));
define('APP_URL', 'https://example.com');
define('APP_NAME', 'Test Store');
$_SESSION = [];
function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function t(string $key): string { return $key; }
function asset(string $path): string { return APP_URL . '/public/assets/' . $path; }
function current_lang(): string { return 'en'; }
function get_flash(string $type): ?string { return null; }
function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }

class CatalogTestModel extends App\Models\CourseModel
{
    public array $calls = [];
    public array $products = [];
    public function __construct() {}
    public function getAll(): array { $this->calls[] = 'all'; return $this->products; }
    public function getAllByType(string $type): array
    {
        $this->calls[] = $type;
        return array_values(array_filter($this->products, fn(array $product): bool => $product['type'] === $type));
    }
    public function getAllAiAccounts(): array
    {
        $this->calls[] = 'ai';
        return array_values(array_filter($this->products, fn(array $product): bool => ($product['type'] ?? '') === 'tool'));
    }
}

$model = new CatalogTestModel();
$model->products = [
    ['id' => 10, 'type' => 'tool', 'title' => 'TOOL_SENTINEL', 'description' => '', 'price' => 10, 'currency' => 'USD'],
    ['id' => 11, 'type' => 'course', 'title' => 'COURSE_SENTINEL', 'description' => '', 'price' => 20, 'currency' => 'USD'],
];
$reflection = new ReflectionClass(App\Controllers\CourseController::class);
$controller = $reflection->newInstanceWithoutConstructor();
$reflection->getProperty('courseModel')->setValue($controller, $model);

foreach ([
    ['index', '/', 'all', true, true, true],
    ['aiAccountsPage', '/ai-accounts', 'ai', true, false, false],
    ['toolsPage', '/tools', 'tool', true, false, true],
    ['coursesPage', '/courses', 'course', false, true, false]
] as [$method, $path, $filter, $hasTool, $hasCourse, $hasTelegram]) {
    $_SERVER['REQUEST_URI'] = $path;
    ob_start();
    $controller->$method();
    $html = ob_get_clean();
    if (end($model->calls) !== $filter
        || str_contains($html, 'TOOL_SENTINEL') !== $hasTool
        || str_contains($html, 'COURSE_SENTINEL') !== $hasCourse
        || str_contains($html, 'telegram-product-card') !== $hasTelegram) {
        throw new RuntimeException('Incorrect catalog contents or model filter: ' . $path);
    }
    $document = new DOMDocument();
    @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
    $xpath = new DOMXPath($document);
    foreach (['//*[@id="primary-navigation"]', '//nav[@class="bottom-nav"]'] as $navigation) {
        $active = $xpath->query($navigation . '/a[@aria-current="page"]');
        $expectedUrl = APP_URL . ($path === '/' ? '' : $path);
        $activeLink = $active->item(0);
        if ($active->length !== 1 || !($activeLink instanceof DOMElement) || $activeLink->getAttribute('href') !== $expectedUrl) {
            throw new RuntimeException('Incorrect active navigation tab: ' . $path);
        }
    }
    if ($xpath->query('//nav[@class="bottom-nav"]/*')->length !== 6
        || !str_contains($html, 'id="course-search-input"')) {
        throw new RuntimeException('Catalog pages require search and six mobile navigation controls.');
    }
}
$model->products = [];
ob_start();
$controller->coursesPage();
$html = ob_get_clean();
if (!str_contains($html, 'catalog.courses_empty') || str_contains($html, 'telegram-product-card')) {
    throw new RuntimeException('Course-only empty state must not display tools.');
}
echo "PASS: Home includes all products; Tools and Courses are filtered with correct empty state\n";