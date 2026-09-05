<?php

namespace App\Models {
    class CourseModel
    {
        public static ?array $existing = null;
        public static array $saved = [];
        public static bool $persist = false;
        public function getById(int $id): ?array { return self::$existing; }
        public function update(int $id, array $data): bool { self::$saved[] = $data; return true; }
        public function create(array $data): int {
            self::$saved[] = $data;
            if (!self::$persist) { return 4; }
            $database = Database::getInstance();
            $database->query('INSERT INTO courses (title, description, price, currency, type, is_active) VALUES (?, ?, ?, ?, ?, ?)',
                [$data['title'], $data['description'], $data['price'], $data['currency'], $data['type'], $data['is_active']]);
            return (int) $database->getConnection()->lastInsertId();
        }
    }
    class InvoiceModel {}
    class Database
    {
        public static ?self $instance = null;
        public static int $accesses = 0;
        public bool $failMapping = false;
        private \PDO $pdo;
        public function __construct() {
            $this->pdo = new \PDO('sqlite::memory:');
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->pdo->exec('CREATE TABLE courses (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT,
                price DECIMAL, currency TEXT, type TEXT, is_active INTEGER, qv_product_key TEXT, qv_variant_key TEXT, qv_max_cost DECIMAL)');
            $this->pdo->exec("INSERT INTO courses (id, title) VALUES (3, 'Reserved')");
            $this->pdo->exec('CREATE TABLE invoices (id INTEGER PRIMARY KEY, invoice_no TEXT, course_id INTEGER,
                payment_status TEXT, paid_at TEXT, qv_product_key TEXT, qv_variant_key TEXT, qv_max_cost DECIMAL,
                qv_status TEXT, qv_order_id TEXT UNIQUE, qv_response TEXT, qv_attempted_at TEXT, delivered_stock TEXT)');
        }
        public static function getInstance(): self { self::$accesses++; return self::$instance ??= new self(); }
        public function getConnection(): \PDO { return $this->pdo; }
        public function query(string $sql, array $params = []): \PDOStatement {
            if ($this->failMapping && str_starts_with($sql, 'UPDATE courses SET qv_')) {
                throw new \RuntimeException('SECRET simulated mapping error');
            }
            $statement = $this->pdo->prepare(str_replace(' FOR UPDATE', '', $sql));
            $statement->execute($params);
            return $statement;
        }
        public function fetch(string $sql, array $params = []): ?array { return $this->query($sql, $params)->fetch() ?: null; }
        public function fetchAll(string $sql, array $params = []): array { return $this->query($sql, $params)->fetchAll(); }
    }
}

namespace App\Services {
    class QuantumVaultClient
    {
        public static array $calls = [];
        public static bool $fail = false;
        public static function enabled(): bool { return getenv('QUANTUMVAULT_ENABLED') === '1'; }
        private function record(string $method): void {
            self::$calls[] = $method;
            if (self::$fail) { throw new \RuntimeException('SECRET provider raw response'); }
        }
        public function products(): array {
            $this->record('products');
            return [['productKey' => 'sample', 'name' => '<script>supplier</script>', 'currency' => 'USD', 'inStock' => true,
                'variants' => [['key' => 'month', 'name' => 'One month', 'price' => 2.5, 'inStock' => true]]]];
        }
        public function quote(string $product, ?string $variant, float $maxCost): array {
            $this->record('quote');
            if ($product !== 'sample' || $variant !== 'month' || $maxCost < 2.5) { throw new \RuntimeException('SECRET invalid quote'); }
            return ['product' => ['name' => 'Validated supplier name', 'description' => 'Validated supplier description'], 'price' => 2.5, 'currency' => 'USD'];
        }
        public function balance(): array { $this->record('balance'); return ['balance' => ['unknown' => 'SECRET'], 'apiKey' => 'SECRET']; }
        public function orders(): array {
            $this->record('orders');
            return [['orderId' => 'QV-TEST', 'productKey' => 'sample', 'variantKey' => 'month', 'status' => 'completed',
                'createdAt' => '2026-09-05T12:00:00Z', 'goods' => 'SECRET', 'password' => 'SECRET',
                'fields' => [['label' => 'Password', 'value' => 'SECRET']]]];
        }
    }
    class QuantumVaultDeliveryService
    {
        public static array $recovered = [];
        public static array $delivered = [];
        public function recover(string $invoiceNo, string $orderId): void { self::$recovered[] = [$invoiceNo, $orderId]; }
        public function deliver(array $invoice): string { self::$delivered[] = $invoice; return 'delivered'; }
    }
}

namespace {
    define('ADMIN_PREFIX', 'private-admin');
    define('APP_ROOT', dirname(__DIR__));
    define('APP_URL', 'http://localhost');
    define('ADMIN_URL', APP_URL . '/' . ADMIN_PREFIX);
    define('APP_NAME', 'Synthetic Admin');
    class AdminTestRedirect extends RuntimeException {}
    class AdminTestDenied extends RuntimeException {}
    $authorized = true;
    $flashes = [];
    function require_admin(): void { if (!$GLOBALS['authorized']) { throw new AdminTestDenied(); } }
    function verify_csrf($token): bool { return $token === 'test-csrf'; }
    function flash(string $type, string $message): void { $GLOBALS['flashes'][$type] = $message; }
    function get_flash(string $type): ?string { $message = $GLOBALS['flashes'][$type] ?? null; unset($GLOBALS['flashes'][$type]); return $message; }
    function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    function asset(string $path): string { return '/public/assets/' . $path; }
    function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="test-csrf">'; }
    function redirect(string $path): void { throw new AdminTestRedirect($path); }
    function check(bool $condition, string $message): void {
        if (!$condition) { throw new RuntimeException($message); }
    }
    require dirname(__DIR__) . '/app/Controllers/AdminController.php';
    $controller = new App\Controllers\AdminController();
    $_POST = ['csrf_token' => 'test-csrf', 'id' => '4', 'title' => 'Mapped tool',
        'type' => 'tool', 'price' => '5', 'currency' => 'USD', 'qv_product_key' => 'forged'];
    foreach ([null, ['qv_product_key' => 'sample', 'qv_max_cost' => '3']] as $existing) {
        App\Models\CourseModel::$existing = $existing;
        App\Models\CourseModel::$saved = [];
        try { $controller->saveCourse(); } catch (AdminTestRedirect $expected) {}
        check(count(App\Models\CourseModel::$saved) === ($existing ? 1 : 0), 'Only stored mappings may omit a download link.');
    }
    foreach (['price' => '2', 'currency' => 'KHR', 'type' => 'course'] as $field => $value) {
        $original = $_POST[$field];
        $_POST[$field] = $value;
        App\Models\CourseModel::$saved = [];
        try { $controller->saveCourse(); } catch (AdminTestRedirect $expected) {}
        check(App\Models\CourseModel::$saved === [], 'Mapped product constraints must survive edits.');
        $_POST[$field] = $original;
    }
    require APP_ROOT . '/app/Models/QuantumVaultOrderModel.php';
    require APP_ROOT . '/app/Controllers/QuantumVaultController.php';
    $controller = new App\Controllers\QuantumVaultController();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/private-admin/quantumvault';
    function action(object $controller, string $method): void {
        try { $controller->$method(); } catch (AdminTestRedirect $expected) {
            check($expected->getMessage() === '/private-admin/quantumvault', 'Wrong redirect destination.');
            return;
        }
        throw new RuntimeException('Action must redirect.');
    }
    function render(object $controller): string {
        ob_start();
        $controller->index();
        return ob_get_clean();
    }
    putenv('QUANTUMVAULT_ENABLED=1');
    putenv('QUANTUMVAULT_API_KEY=synthetic-key-only');
    $authorized = false;
    foreach (['index', 'import', 'recover', 'retry'] as $method) {
        try { $controller->$method(); throw new RuntimeException('Missing admin guard.'); }
        catch (AdminTestDenied $expected) {}
    }
    check(App\Models\Database::$accesses === 0 && App\Services\QuantumVaultClient::$calls === [], 'Authorization must precede DB/provider access.');
    $authorized = true;
    foreach (['import', 'recover', 'retry'] as $method) {
        foreach (['wrong', ['malformed']] as $token) {
            $_POST = ['csrf_token' => $token];
            action($controller, $method);
        }
        $_POST = ['csrf_token' => 'test-csrf'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        action($controller, $method);
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }
    check(App\Models\Database::$accesses === 0 && App\Services\QuantumVaultClient::$calls === [], 'POST/CSRF guard must precede all side effects.');
    $_GET = ['refresh' => '1'];
    putenv('QUANTUMVAULT_API_KEY');
    check(str_contains(render($controller), 'API key is missing'), 'Missing-key empty state required.');
    putenv('QUANTUMVAULT_API_KEY=synthetic-key-only');
    putenv('QUANTUMVAULT_ENABLED=0');
    check(str_contains(render($controller), 'integration is disabled'), 'Disabled empty state required.');
    check(App\Models\Database::$accesses === 0 && App\Services\QuantumVaultClient::$calls === [], 'Disabled/unconfigured page must not access database/provider.');
    putenv('QUANTUMVAULT_ENABLED=1');
    $_GET = [];
    render($controller);
    check(App\Services\QuantumVaultClient::$calls === [], 'Supplier reads require explicit refresh.');
    $_GET = ['refresh' => '1'];
    $html = render($controller);
    check(str_contains($html, '&lt;script&gt;supplier&lt;/script&gt;') && !str_contains($html, '<script>supplier'), 'Catalog fields must be escaped.');
    check(str_contains($html, 'QV-TEST') && !str_contains($html, 'SECRET') && !str_contains($html, 'synthetic-key-only'), 'Only allowlisted order metadata may render.');
    check(str_contains($html, 'Balance: Unavailable'), 'Unknown balance object must not be dumped.');

    $database = App\Models\Database::getInstance();
    App\Models\CourseModel::$persist = true;
    $import = ['csrf_token' => 'test-csrf', 'mapping' => json_encode(['product' => 'sample', 'variant' => 'month']),
        'price' => '5.00', 'max_cost' => '3.0000', 'title' => 'Forged name', 'description' => 'Forged description', 'is_active' => '1'];
    foreach ([['max_cost' => '6'], ['max_cost' => 'NaN'], ['price' => '0'], ['price' => '5.001'], ['max_cost' => '3.00001'],
        ['mapping' => '{'], ['mapping' => 'null'], ['mapping' => json_encode(['product' => 'sample', 'variant' => 'wrong'])]] as $invalid) {
        $_POST = array_replace($import, $invalid);
        action($controller, 'import');
        check($database->query('SELECT COUNT(*) FROM courses')->fetchColumn() === 1, 'Invalid import must not create a tool.');
    }
    $_POST = $import;
    action($controller, 'import');
    $created = $database->fetch('SELECT * FROM courses WHERE id > 3');
    check($created && $created['title'] === 'Validated supplier name' && $created['description'] === 'Validated supplier description', 'Only quoted metadata may be imported.');
    check($created['type'] === 'tool' && $created['currency'] === 'USD' && (int) $created['is_active'] === 0
        && $created['qv_product_key'] === 'sample' && $created['qv_variant_key'] === 'month' && (float) $created['qv_max_cost'] === 3.0,
        'Import must create an inactive, mapped USD tool outside reserved IDs.');
    $saved = end(App\Models\CourseModel::$saved);
    check($saved['download_link'] === '' && $saved['telegram_group_id'] === '', 'Required CourseModel data keys must be present.');
    action($controller, 'import');
    check($database->query('SELECT COUNT(*) FROM courses')->fetchColumn() === 2, 'Duplicate mapping must be rejected.');
    $database->query('DELETE FROM courses WHERE id > 3');
    $database->failMapping = true;
    action($controller, 'import');
    check($database->query('SELECT COUNT(*) FROM courses')->fetchColumn() === 1 && !$database->getConnection()->inTransaction(), 'Mapping failure must roll back CourseModel creation.');
    check(!str_contains(implode(' ', $flashes), 'SECRET'), 'Provider/DB exceptions must never leak.');
    $database->failMapping = false;

    $database->query("INSERT INTO invoices (id, invoice_no, course_id, payment_status, qv_status, qv_product_key, qv_variant_key, paid_at)
        VALUES (1, 'INV-TEST', 4, 'completed', 'pending', 'sample', 'month', '2026-09-05 12:00:00')");
    $_POST = ['csrf_token' => 'test-csrf', 'invoice_no' => 'INV-TEST'];
    foreach (['review', 'processing', 'delivered', null] as $status) {
        $database->query('UPDATE invoices SET qv_status = ?', [$status]);
        action($controller, 'retry');
    }
    $database->query("UPDATE invoices SET qv_status = 'pending', payment_status = 'pending'");
    action($controller, 'retry');
    $database->query("UPDATE invoices SET payment_status = 'completed', qv_order_id = 'EXISTING'");
    action($controller, 'retry');
    check(App\Services\QuantumVaultDeliveryService::$delivered === [], 'Review, processing, delivered, unpaid and associated invoices cannot retry.');
    $database->query('UPDATE invoices SET qv_order_id = NULL');
    action($controller, 'retry');
    check(count(App\Services\QuantumVaultDeliveryService::$delivered) === 1, 'Explicit paid pending retry must invoke service once.');
    $_POST['order_id'] = 'QV-TEST';
    action($controller, 'recover');
    check(App\Services\QuantumVaultDeliveryService::$recovered === [['INV-TEST', 'QV-TEST']], 'Recovery must delegate the supplied references without purchasing.');
    $database->query("UPDATE invoices SET qv_status = 'review', qv_response = 'SECRET', qv_order_id = '<reference>'");
    $html = render($controller);
    check(!str_contains($html, 'Retry purchase') && str_contains($html, 'Recover existing order') && str_contains($html, '&lt;reference&gt;')
        && !str_contains($html, 'SECRET'), 'Review UI must allow recovery only, escape references and hide responses.');
    $database->query('ALTER TABLE courses DROP COLUMN qv_max_cost');
    check(str_contains(render($controller), 'required migration'), 'Missing migrations must render a useful empty state.');
    App\Services\QuantumVaultClient::$fail = true;
    $html = render($controller);
    check(str_contains($html, 'temporarily unavailable') && !str_contains($html, 'SECRET'), 'Provider failures must render generic messages.');

    require APP_ROOT . '/routes/web.php';
    $_POST = [];
    foreach (['import', 'recover', 'retry'] as $endpoint) {
        try { $router->dispatch('POST', '/private-admin/quantumvault/' . $endpoint); }
        catch (AdminTestRedirect $expected) { continue; }
        throw new RuntimeException('Missing POST admin route.');
    }
    $source = file_get_contents(APP_ROOT . '/app/Controllers/QuantumVaultController.php');
    check(str_contains($source, "header('Cache-Control: no-store')") && str_contains($source, "header('Referrer-Policy: no-referrer')"), 'All handlers need privacy headers.');
    check(!str_contains($source, '->purchase(') && !str_contains($source, 'UPDATE invoices'), 'Controller must not purchase directly or reset invoice states.');
    echo "PASS: QuantumVault admin authorization, CSRF, inactive imports, rollback, duplicates, retry/recovery guards, escaped metadata and empty states (synthetic SQLite/services)\n";
}