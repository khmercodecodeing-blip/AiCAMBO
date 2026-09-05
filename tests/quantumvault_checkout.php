<?php

namespace App\Models {
    class Database
    {
        public static ?self $instance = null;
        private \PDO $pdo;

        public function __construct(bool $supplierColumns = true)
        {
            $this->pdo = new \PDO('sqlite::memory:', null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            $supplierSchema = $supplierColumns
                ? ', qv_product_key TEXT, qv_variant_key TEXT, qv_max_cost NUMERIC, qv_status TEXT' : '';
            $this->pdo->exec('CREATE TABLE invoices (invoice_no TEXT PRIMARY KEY, course_id INTEGER,
                buyer_name TEXT, buyer_phone TEXT, buyer_email TEXT, amount NUMERIC, promo_code TEXT,
                discount_amount NUMERIC, currency TEXT, payment_status TEXT, qr_string TEXT,
                md5_hash TEXT, license_key TEXT, hardware_id TEXT' . $supplierSchema . ')');
            $this->pdo->exec('CREATE TABLE courses (id INTEGER PRIMARY KEY, title TEXT, telegram_group_id TEXT,
                thumbnail TEXT, type TEXT, download_link TEXT, qv_product_key TEXT, qv_variant_key TEXT, qv_max_cost NUMERIC)');
            self::$instance = $this;
        }

        public static function getInstance(): self { return self::$instance ??= new self(); }
        public function query(string $sql, array $params = []): \PDOStatement
        {
            if (str_starts_with($sql, 'INSERT INTO invoices')) {
                $GLOBALS['events'][] = 'insert';
            }
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);
            return $statement;
        }
        public function fetch(string $sql, array $params = []): ?array
        {
            return $this->query($sql, $params)->fetch() ?: null;
        }
    }

    class CourseModel
    {
        public static array $course = [];
        public function getById(int $id): ?array { return self::$course; }
    }

    class PromoCodeModel
    {
        public static float $finalAmount = 4.0;
        public function validateCode(string $code, float $amount, string $currency): array
        {
            return ['valid' => true, 'discount_amount' => $amount - self::$finalAmount,
                'final_price' => self::$finalAmount, 'code' => 'SYNTHETIC-PROMO'];
        }
    }
}

namespace App\Services {
    class QuantumVaultClient
    {
        public static bool $isEnabled = true;
        public static bool $failQuote = false;
        public static array $quotes = [];
        public static int $buys = 0;
        public static function enabled(): bool { return self::$isEnabled; }
        public function quote(string $product, ?string $variant, float $maxCost): array
        {
            $GLOBALS['events'][] = 'quote';
            self::$quotes[] = [$product, $variant, $maxCost];
            if (self::$failQuote) {
                throw new \RuntimeException('SYNTHETIC-PRIVATE-PROVIDER-ERROR');
            }
            return ['price' => 2.5, 'currency' => 'USD'];
        }
        public function purchase(string $product, ?string $variant): array
        {
            self::$buys++;
            throw new \LogicException('Checkout must never purchase.');
        }
    }

    class KHQRService
    {
        public static array $calls = [];
        public function generatePaymentQR(float $amount, string $invoiceNo, string $currency): array
        {
            $GLOBALS['events'][] = 'qr';
            self::$calls[] = [$amount, $invoiceNo, $currency];
            \App\Models\CourseModel::$course['qv_product_key'] = 'changed-during-qr';
            \App\Models\CourseModel::$course['qv_variant_key'] = 'changed-variant';
            \App\Models\CourseModel::$course['qv_max_cost'] = '99.0000';
            return ['qr' => 'SYNTHETIC-QR', 'md5' => 'SYNTHETIC-MD5'];
        }
    }

    class LicenseClient
    {
        public static function keyForPlan(int $courseId, $key = null): ?string { return null; }
    }
}

namespace App\Controllers {
    function file_get_contents(string $path): string
    {
        if ($path !== 'php://input') {
            throw new \LogicException('Only synthetic request input is permitted.');
        }
        return json_encode($GLOBALS['checkoutInput'], JSON_THROW_ON_ERROR);
    }
}

namespace {
    use App\Models\CourseModel;
    use App\Models\Database;
    use App\Models\InvoiceModel;
    use App\Models\PromoCodeModel;
    use App\Services\KHQRService;
    use App\Services\QuantumVaultClient;
    use App\Services\QuantumVaultDeliveryService;

    define('APP_URL', 'https://checkout.example.test');
    define('BAKONG_MERCHANT_NAME', 'Synthetic Merchant');
    define('QR_EXPIRY_MINUTES', 15);
    class CheckoutRedirect extends RuntimeException {}
    function redirect(string $path): void { throw new CheckoutRedirect($path); }
    function verify_csrf($token): bool { return $token === 'synthetic-csrf'; }
    function flash(string $type, string $message): void { $GLOBALS['flashes'][] = [$type, $message]; }
    function check(bool $condition, string $message): void
    {
        if (!$condition) { throw new RuntimeException($message); }
    }
    function snapshotMatches(array $invoice, array $snapshot): bool
    {
        return ($invoice['qv_product_key'] ?? null) === $snapshot['qv_product_key']
            && ($invoice['qv_variant_key'] ?? null) === $snapshot['qv_variant_key']
            && (float) ($invoice['qv_max_cost'] ?? 0) === (float) $snapshot['qv_max_cost']
            && ($invoice['qv_status'] ?? null) === 'pending';
    }
    function invokeCheckout(object $controller, string $method): array
    {
        http_response_code(200);
        ob_start();
        $location = null;
        try {
            $controller->$method();
        } catch (CheckoutRedirect $redirect) {
            $location = $redirect->getMessage();
        } finally {
            $body = ob_get_clean();
        }
        return ['location' => $location, 'body' => $body, 'status' => http_response_code()];
    }

    set_error_handler(function (int $severity, string $message): bool {
        throw new RuntimeException($message);
    });
    require dirname(__DIR__) . '/app/Services/PurchaseAccess.php';
    require dirname(__DIR__) . '/app/Models/InvoiceModel.php';
    require dirname(__DIR__) . '/app/Services/QuantumVaultDeliveryService.php';
    require dirname(__DIR__) . '/app/Controllers/PaymentController.php';

    $_SESSION = [];
    $events = [];
    $baseInvoice = ['invoice_no' => 'INV-SYNTHETIC-ORDINARY', 'course_id' => 4,
        'buyer_name' => 'Synthetic Buyer', 'amount' => 5.0];
    new Database(false);
    $invoices = new InvoiceModel();
    check($invoices->create($baseInvoice) === $baseInvoice['invoice_no'], 'Ordinary create must return its invoice number.');
    $ordinary = Database::getInstance()->fetch('SELECT * FROM invoices');
    check($ordinary !== null && !array_key_exists('qv_product_key', $ordinary)
        && $ordinary['payment_status'] === 'pending' && (float) $ordinary['amount'] === 5.0,
        'Ordinary invoices must insert into a schema with no supplier columns.');
    check(($_SESSION['purchase_invoices'][$baseInvoice['invoice_no']] ?? false) === true,
        'Real create must remember purchase access.');

    new Database();
    $database = Database::getInstance();
    $invoices = new InvoiceModel();
    $course = ['id' => 4, 'title' => 'Synthetic Account', 'is_active' => 1, 'type' => 'tool',
        'price' => 5.0, 'currency' => 'USD', 'qv_product_key' => 'synthetic-product',
        'qv_variant_key' => 'month', 'qv_max_cost' => '3.1250'];
    $snapshot = QuantumVaultDeliveryService::checkout($course, 5.0);
    check($snapshot === ['qv_product_key' => 'synthetic-product', 'qv_variant_key' => 'month',
        'qv_max_cost' => '3.1250', 'qv_status' => 'pending'], 'Checkout must return the complete mapping snapshot.');
    $database->query('INSERT INTO courses (id, title, type, qv_product_key, qv_variant_key, qv_max_cost)
        VALUES (4, ?, ?, ?, ?, ?)', ['Synthetic Account', 'tool', 'synthetic-product', 'month', 3.125]);
    $mappedNo = $invoices->create(array_replace($baseInvoice, $snapshot,
        ['invoice_no' => 'INV-SYNTHETIC-MAPPED', 'md5_hash' => 'SYNTHETIC-MAPPED-HASH', 'qv_status' => 'delivered']));
    $stored = $database->fetch('SELECT * FROM invoices WHERE invoice_no = ?', [$mappedNo]);
    check($stored !== null && snapshotMatches($stored, $snapshot), 'Real create must persist the snapshot and force pending.');
    $database->query('UPDATE courses SET qv_product_key = ?, qv_variant_key = ?, qv_max_cost = ?',
        ['new-current-product', 'year', 99]);
    foreach ([$invoices->getByInvoiceNo($mappedNo), $invoices->getByMd5Hash('SYNTHETIC-MAPPED-HASH')] as $loaded) {
        check($loaded !== null && snapshotMatches($loaded, $snapshot), 'Invoice reads must not replace supplier snapshots with current course mappings.');
    }
    $ordinaryNo = $invoices->create(array_replace($baseInvoice, ['invoice_no' => 'INV-SYNTHETIC-UNMAPPED']));
    $loaded = $invoices->getByInvoiceNo($ordinaryNo);
    check($loaded !== null && $loaded['qv_product_key'] === null && $loaded['qv_variant_key'] === null
        && $loaded['qv_max_cost'] === null && $loaded['qv_status'] === null,
        'An ordinary invoice must not acquire the current course supplier mapping.');
    $noVariant = QuantumVaultDeliveryService::checkout(array_replace($course, ['qv_variant_key' => '']), 5.0);
    $invoices->create(array_replace($baseInvoice, $noVariant, ['invoice_no' => 'INV-SYNTHETIC-NO-VARIANT']));
    check(snapshotMatches($invoices->getByInvoiceNo('INV-SYNTHETIC-NO-VARIANT'), $noVariant)
        && $noVariant['qv_variant_key'] === null, 'An empty variant must persist as NULL.');

    foreach (['disabled' => [], 'currency' => ['currency' => 'KHR'], 'reserved-1' => ['id' => 1],
        'reserved-2' => ['id' => 2], 'reserved-3' => ['id' => 3], 'wrong-type' => ['type' => 'course'],
        'promo-below-cost' => [], 'zero' => [], 'non-finite' => []] as $case => $changes) {
        QuantumVaultClient::$isEnabled = $case !== 'disabled';
        QuantumVaultClient::$quotes = [];
        $amount = match ($case) { 'promo-below-cost' => 3.0, 'zero' => 0.0, 'non-finite' => INF, default => 5.0 };
        $rejected = false;
        try { QuantumVaultDeliveryService::checkout(array_replace($course, $changes), $amount); }
        catch (RuntimeException $expected) { $rejected = true; }
        check($rejected && QuantumVaultClient::$quotes === [], $case . ': validation must reject before a provider request.');
    }
    QuantumVaultClient::$isEnabled = false;
    QuantumVaultClient::$quotes = [];
    check(QuantumVaultDeliveryService::checkout([], 0.0) === []
        && QuantumVaultDeliveryService::checkout(['id' => 1, 'type' => 'course', 'currency' => 'KHR'], 5.0) === []
        && QuantumVaultClient::$quotes === [], 'Ordinary products must remain unaffected while the supplier is disabled.');

    $controller = new App\Controllers\PaymentController();
    foreach (['checkout', 'processCheckout', 'quickCheckout'] as $method) {
        foreach (['success', 'disabled', 'currency', 'reserved', 'cost', 'quote-failure', 'ordinary'] as $case) {
            CourseModel::$course = $course;
            QuantumVaultClient::$isEnabled = !in_array($case, ['disabled', 'ordinary'], true);
            QuantumVaultClient::$failQuote = $case === 'quote-failure';
            QuantumVaultClient::$quotes = [];
            KHQRService::$calls = [];
            PromoCodeModel::$finalAmount = $case === 'cost' ? 3.0 : 4.0;
            $events = [];
            $flashes = [];
            if ($case === 'currency') { CourseModel::$course['currency'] = 'KHR'; }
            if ($case === 'reserved') { CourseModel::$course['id'] = 3; }
            if ($case === 'cost' && $method !== 'quickCheckout') { CourseModel::$course['price'] = 3.0; }
            if ($case === 'ordinary') { unset(CourseModel::$course['qv_product_key']); }
            $_GET = ['course_id' => CourseModel::$course['id']];
            $_POST = $_GET + ['csrf_token' => 'synthetic-csrf', 'buyer_name' => 'Synthetic Buyer', 'agree_policy' => '1',
                'qv_product_key' => 'forged', 'qv_variant_key' => 'forged', 'qv_max_cost' => '0.01', 'qv_status' => 'delivered'];
            $checkoutInput = $_POST + ['promo_code' => 'SYNTHETIC-PROMO'];
            $before = (int) $database->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
            $response = invokeCheckout($controller, $method);
            $after = (int) $database->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
            $label = $method . '/' . $case;
            if (!in_array($case, ['success', 'ordinary'], true)) {
                check($after === $before && KHQRService::$calls === []
                    && $events === ($case === 'quote-failure' ? ['quote'] : []),
                    $label . ': supplier rejection must precede QR generation and persistence.');
                if ($method === 'quickCheckout') {
                    check($response['status'] === 503 && json_decode($response['body'], true)['status'] === 'error',
                        $label . ': supplier rejection must return a JSON 503.');
                } else {
                    check($response['location'] === '/course/' . $_GET['course_id'] && count($flashes) === 1,
                        $label . ': rejected checkout must redirect with a generic error.');
                }
                check(!str_contains($response['body'] . json_encode($flashes), 'SYNTHETIC-PRIVATE-PROVIDER-ERROR'),
                    $label . ': raw supplier failures must not reach the buyer.');
                continue;
            }
            check($after === $before + 1 && $events === ($case === 'ordinary' ? ['qr', 'insert'] : ['quote', 'qr', 'insert']),
                $label . ': validate supplier, then generate QR, then persist exactly once.');
            [$qrAmount, $invoiceNo, $qrCurrency] = KHQRService::$calls[0];
            $created = $database->fetch('SELECT * FROM invoices WHERE invoice_no = ?', [$invoiceNo]);
            check($created !== null && (float) $created['amount'] === $qrAmount && $qrCurrency === 'USD'
                && $qrAmount === ($method === 'quickCheckout' ? 4.0 : 5.0), $label . ': QR and invoice must use the final price.');
            if ($case === 'success') {
                check(snapshotMatches($created, $snapshot)
                    && QuantumVaultClient::$quotes === [['synthetic-product', 'month', 3.125]],
                    $label . ': only the validated snapshot may persist, despite forged input or changing course data.');
            } else {
                check($created['qv_product_key'] === null && $created['qv_variant_key'] === null
                    && $created['qv_max_cost'] === null && $created['qv_status'] === null,
                    $label . ': ordinary checkout must not persist supplier data.');
            }
            if ($method === 'quickCheckout') {
                $json = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);
                check($json['status'] === 'success' && $json['invoice_no'] === $invoiceNo
                    && $created['promo_code'] === 'SYNTHETIC-PROMO' && (float) $created['discount_amount'] === 1.0,
                    $label . ': quick checkout must preserve the accepted promotion.');
            } else {
                check($response['location'] === '/payment/' . $invoiceNo, $label . ': successful checkout must redirect to its invoice.');
            }
        }
    }
    check(QuantumVaultClient::$buys === 0, 'No checkout path may purchase supplier goods.');
    restore_error_handler();
    echo "PASS: QuantumVault checkout snapshots, real InvoiceModel SQLite persistence, legacy schema, static guards and all three controller paths (synthetic services/input; no network)\n";
}