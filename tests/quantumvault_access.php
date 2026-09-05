<?php

namespace App\Models {
    class QuantumVaultOrderModel
    {
        public static int $constructions = 0;
        public static int $claims = 0;
        public function __construct() { self::$constructions++; }
        public function claim(string $invoiceNo): bool { self::$claims++; return false; }
        public function get(string $invoiceNo): ?array { return ['qv_status' => 'pending']; }
    }
}

namespace App\Services {
    class QuantumVaultClient
    {
        public static int $constructions = 0;
        public static int $quotes = 0;
        public static int $buys = 0;
        public function __construct() { self::$constructions++; }
        public static function enabled(): bool { return true; }
        public function quote(string $product, ?string $variant, float $maxCost): array
        {
            self::$quotes++;
            throw new \LogicException('Provider quotes are forbidden in access tests.');
        }
        public function purchase(string $product, ?string $variant): array
        {
            self::$buys++;
            throw new \LogicException('Provider purchases are forbidden in access tests.');
        }
    }
}

namespace {
    use App\Models\QuantumVaultOrderModel;
    use App\Services\QuantumVaultClient;

    define('APP_ROOT', __DIR__ . '/ui');
    define('APP_URL', 'https://access.example.test');
    function e($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    function format_price($amount, $currency): string { return '$' . number_format((float) $amount, 2); }
    function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="synthetic-csrf">'; }
    function verify_csrf($token): bool { return $token === 'synthetic-csrf'; }
    function flash(string $type, string $message): void {}
    class AccessRedirect extends RuntimeException {}
    function redirect(string $path): void { throw new AccessRedirect($path); }
    function check(bool $condition, string $message): void
    {
        if (!$condition) { throw new RuntimeException($message); }
    }
    function capture(callable $action): array
    {
        http_response_code(200);
        ob_start();
        $location = null;
        try {
            $action();
        } catch (AccessRedirect $redirect) {
            $location = $redirect->getMessage();
        } finally {
            $body = ob_get_clean();
        }
        return ['body' => $body, 'status' => http_response_code(), 'location' => $location];
    }
    function renderSuccess(array $invoice): array
    {
        return capture(static function () use ($invoice): void {
            $licenseDeliveryStatus = 'pending';
            require dirname(__DIR__) . '/app/views/payment/success.php';
        });
    }
    function parseHtml(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            check($document->loadHTML($html, LIBXML_NONET), 'Synthetic success HTML must parse.');
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    set_error_handler(function (int $severity, string $message): bool {
        throw new RuntimeException($message);
    });
    require dirname(__DIR__) . '/app/Models/InvoiceModel.php';
    require dirname(__DIR__) . '/app/Services/PurchaseAccess.php';
    require dirname(__DIR__) . '/app/Services/QuantumVaultDeliveryService.php';
    require dirname(__DIR__) . '/app/Controllers/PaymentController.php';

    class QuantumVaultAccessInvoices extends App\Models\InvoiceModel
    {
        public ?array $invoice = null;
        public int $reads = 0;
        public function __construct() {}
        public function getByInvoiceNo(string $invoiceNo): ?array
        {
            $this->reads++;
            return ($this->invoice['invoice_no'] ?? null) === $invoiceNo ? $this->invoice : null;
        }
    }

    $_GET = [];
    $_POST = [];
    $secret = "Email: synthetic@example.test\nPassword: SYNTHETIC-ONLY-NOT-A-CREDENTIAL\n"
        . '</pre><script id="qv-secret-injection">throw new Error("synthetic")</script>'
        . '<img src="invalid" onerror="syntheticAttack()">' . "\nQuotes: \" ' & < >\n";
    $baseInvoice = ['invoice_no' => 'INV-SYNTHETIC-QV', 'buyer_email' => 'buyer@example.test',
        'payment_status' => 'completed', 'product_type' => 'tool', 'course_title' => 'Synthetic Account',
        'amount' => 5.0, 'currency' => 'USD', 'license_key' => null, 'download_link' => '',
        'qv_product_key' => 'synthetic-product', 'qv_variant_key' => 'month', 'qv_max_cost' => '3.1250',
        'qv_status' => 'delivered', 'delivered_stock' => $secret];
    $invoices = new QuantumVaultAccessInvoices();
    $invoices->invoice = $baseInvoice;
    $reflection = new ReflectionClass(App\Controllers\PaymentController::class);
    $controller = $reflection->newInstanceWithoutConstructor();
    $property = $reflection->getProperty('invoiceModel');
    $property->setAccessible(true);
    $property->setValue($controller, $invoices);

    foreach ([[], ['user_email' => 'other@example.test'],
        ['purchase_invoices' => ['INV-SYNTHETIC-QV' => 'true']]] as $session) {
        $_SESSION = $session;
        $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-QV'));
        check($response['status'] === 404 && $response['body'] === 'Account delivery not found',
            'Unauthorized downloads must return a generic 404 without stored goods.');
    }
    $_SESSION = ['user_email' => 'buyer@example.test'];
    foreach (['pending', 'expired', 'failed'] as $paymentStatus) {
        $invoices->invoice = array_replace($baseInvoice, ['payment_status' => $paymentStatus]);
        $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-QV'));
        check($response['status'] === 404 && $response['body'] === 'Account delivery not found',
            $paymentStatus . ': unpaid invoices must not expose even already stored secrets.');
    }
    foreach (['pending', 'processing', 'review', null] as $deliveryStatus) {
        $invoices->invoice = array_replace($baseInvoice, ['qv_status' => $deliveryStatus]);
        $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-QV'));
        check($response['status'] === 404 && $response['body'] === 'Account delivery not found',
            'Incomplete delivery must not expose stored secrets.');
    }
    foreach (['', null] as $emptyStock) {
        $invoices->invoice = array_replace($baseInvoice, ['delivered_stock' => $emptyStock]);
        $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-QV'));
        check($response['status'] === 404 && $response['body'] === 'Account delivery not found',
            'Delivered status without goods must return 404.');
    }
    $invoices->invoice = null;
    $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-MISSING'));
    check($response['status'] === 404 && $response['body'] === 'Account delivery not found', 'Missing invoice must return 404.');
    $invoices->invoice = $baseInvoice;
    foreach ([['user_email' => 'buyer@example.test'], ['user_email' => 'BUYER@EXAMPLE.TEST'],
        ['purchase_invoices' => ['INV-SYNTHETIC-QV' => true]]] as $session) {
        $_SESSION = $session;
        $response = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-QV'));
        check($response['status'] === 200 && $response['body'] === $secret && $response['location'] === null,
            'Authorized completed delivery must return the exact synthetic bytes, including whitespace and markup.');
        $missing = capture(fn() => $controller->accountDownload('INV-SYNTHETIC-OTHER'));
        check($missing['status'] === 404 && $missing['body'] === 'Account delivery not found',
            'Ownership of one purchase must not expose a different invoice.');
    }

    $invoices->invoice = array_replace($baseInvoice, ['qv_status' => 'pending']);
    $_SESSION = ['user_email' => 'buyer@example.test'];
    foreach ([[], ['csrf_token' => 'wrong'], ['csrf_token' => ['malformed']]] as $post) {
        $_POST = $post;
        $response = capture(fn() => $controller->retryDelivery('INV-SYNTHETIC-QV'));
        check($response['status'] === 403 && $response['body'] === 'Invalid request' && $response['location'] === null,
            'Denied retry CSRF must return 403.');
        check(QuantumVaultClient::$constructions === 0 && QuantumVaultClient::$quotes === 0
            && QuantumVaultClient::$buys === 0 && QuantumVaultOrderModel::$constructions === 0
            && QuantumVaultOrderModel::$claims === 0, 'CSRF denial must precede supplier construction, claims, quotes and purchases.');
    }
    $_POST = ['csrf_token' => 'synthetic-csrf'];
    $_SESSION = [];
    $response = capture(fn() => $controller->retryDelivery('INV-SYNTHETIC-QV'));
    check($response['status'] === 404 && $response['body'] === 'Purchase not found', 'Unauthorized retry must return 404.');
    $_SESSION = ['user_email' => 'buyer@example.test'];
    $invoices->invoice['payment_status'] = 'pending';
    $response = capture(fn() => $controller->retryDelivery('INV-SYNTHETIC-QV'));
    check($response['status'] === 404 && $response['body'] === 'Purchase not found'
        && QuantumVaultClient::$constructions === 0 && QuantumVaultOrderModel::$claims === 0,
        'Unpaid retry must not start delivery.');
    $invoices->invoice['payment_status'] = 'completed';
    $response = capture(fn() => $controller->retryDelivery('INV-SYNTHETIC-QV'));
    check($response['location'] === '/payment/success/INV-SYNTHETIC-QV'
        && QuantumVaultClient::$constructions === 1 && QuantumVaultOrderModel::$claims === 1
        && QuantumVaultClient::$quotes === 0 && QuantumVaultClient::$buys === 0,
        'Valid retry must reach delivery, while the synthetic denied claim prevents any supplier request.');

    $templateInvoice = array_replace($baseInvoice, [
        'invoice_no' => 'INV-SYNTHETIC-"><script id="qv-invoice-injection">syntheticAttack()</script>',
        'course_title' => '</script><script id="qv-title-injection">syntheticAttack()</script> " &',
    ]);
    foreach (['delivered', 'pending', 'review', 'processing'] as $deliveryStatus) {
        $invoice = array_replace($templateInvoice, ['qv_status' => $deliveryStatus]);
        $response = renderSuccess($invoice);
        $html = $response['body'];
        check($response['status'] === 200, 'Completed invoice must render the success template.');
        check(str_contains($html, e($invoice['invoice_no'])) && str_contains($html, e($invoice['course_title'])),
            'Invoice and product text must be HTML escaped.');
        foreach (['invoiceNo' => 'invoice_no', 'productTitle' => 'course_title'] as $variable => $field) {
            $encoded = json_encode($invoice[$field], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            check(str_contains($html, 'const ' . $variable . ' = ' . $encoded . ';'),
                'Dynamic JavaScript values must use context-safe JSON encoding.');
        }
        $xpath = parseHtml($html);
        check($xpath->query('//script')->length === 1 && $xpath->query('//img | //*[@onerror]')->length === 0
            && $xpath->query('//*[@id="qv-secret-injection" or @id="qv-invoice-injection" or @id="qv-title-injection"]')->length === 0,
            'Synthetic markup must remain inert text, never injected script, image or event-handler nodes.');
        $account = '//section[@aria-labelledby="account-delivery-title"]';
        check($xpath->query($account)->length === 1, 'Supplier account section must be present.');
        $downloads = $xpath->query('//a[contains(@href, "/payment/account/")]');
        $retry = $xpath->query('//form[contains(@action, "/payment/retry-delivery/")]');
        if ($deliveryStatus === 'delivered') {
            check(str_contains($html, e($secret)) && !str_contains($html, $secret)
                && $xpath->query($account . '//pre')->item(0)?->textContent === $secret,
                'Delivered dynamic fields must be escaped while preserving their exact text.');
            $download = $downloads->item(0);
            check($downloads->length === 1 && $download instanceof DOMElement
                && $download->getAttribute('href') === APP_URL . '/payment/account/' . $invoice['invoice_no']
                && $retry->length === 0 && $xpath->query($account . '//*[@role="status"]')->length === 0,
                'Only delivered accounts may expose an account download link, without a retry or pending notice.');
        } else {
            $notice = $xpath->query($account . '//*[@role="status"]');
            check($downloads->length === 0 && $xpath->query($account . '//pre')->length === 0
                && !str_contains($html, 'SYNTHETIC-ONLY-NOT-A-CREDENTIAL')
                && $notice->length === 1 && str_contains($notice->item(0)->textContent, 'Support'),
                'Pending/review/processing accounts must show the support notice and hide goods and downloads.');
            check($retry->length === ($deliveryStatus === 'pending' ? 1 : 0),
                'Only pending delivery may show retry; review and processing must not.');
            if ($deliveryStatus === 'pending') {
                $form = $retry->item(0);
                check($form instanceof DOMElement && $form->getAttribute('method') === 'post'
                    && $form->getAttribute('action') === APP_URL . '/payment/retry-delivery/' . $invoice['invoice_no']
                    && $xpath->query($account . '//input[@name="csrf_token" and @value="synthetic-csrf"]')->length === 1,
                    'Pending retry must submit POST to the escaped invoice action with a CSRF field.');
            }
        }
    }
    $response = renderSuccess(array_replace($templateInvoice, ['delivered_stock' => '']));
    $xpath = parseHtml($response['body']);
    check($xpath->query('//a[contains(@href, "/payment/account/")]')->length === 0
        && $xpath->query('//section[@aria-labelledby="account-delivery-title"]//*[@role="status"]')->length === 1,
        'Delivered status without goods must render a notice instead of an unusable download.');
    foreach (['pending', 'expired', 'failed'] as $paymentStatus) {
        $response = renderSuccess(array_replace($templateInvoice, ['payment_status' => $paymentStatus]));
        check($response['status'] === 404 && $response['body'] === '', 'Unpaid success templates must render no content.');
    }
    check(QuantumVaultClient::$constructions === 1 && QuantumVaultOrderModel::$claims === 1
        && QuantumVaultClient::$quotes === 0 && QuantumVaultClient::$buys === 0,
        'Rendering must not trigger delivery or supplier requests.');
    restore_error_handler();
    echo "PASS: QuantumVault ownership, paid/delivered downloads, exact synthetic bytes, retry CSRF and real success-template escaping/state controls (DOM checks; no network)\n";
}