<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\InvoiceModel;
use App\Services\KHQRService;
use App\Services\BakongService;
use App\Services\TelegramService;

/**
 * Payment Controller — Handles checkout, QR display, payment status polling, and success page
 */
class PaymentController
{
    private CourseModel $courseModel;
    private InvoiceModel $invoiceModel;
    private KHQRService $khqrService;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->invoiceModel = new InvoiceModel();
        $this->khqrService = new KHQRService();
    }

    /**
     * Bypassed checkout form and redirect to payment page directly
     */
    public function checkout(): void
    {
        $courseId = (int) ($_GET['course_id'] ?? 0);
        $course = $this->courseModel->getById($courseId);

        if (!$course) {
            if (in_array($courseId, [1, 2, 3])) {
                $plans = [
                    1 => ['title' => 'Telegram Adder - 1 Month', 'price' => 7.00, 'desc' => '1 Month License'],
                    2 => ['title' => 'Telegram Adder - 3 Months', 'price' => 18.00, 'desc' => '3 Months License'],
                    3 => ['title' => 'Telegram Adder - 1 Year', 'price' => 50.00, 'desc' => '1 Year License']
                ];
                
                if (isset($plans[$courseId])) {
                    $plan = $plans[$courseId];
                    try {
                        $db = \App\Models\Database::getInstance();
                        $db->query(
                            "INSERT INTO courses (id, title, description, price, currency, type, download_link, is_active)
                             VALUES (:id, :title, :description, :price, 'USD', 'tool', 'https://aicambo.store', 1)",
                            [
                                ':id' => $courseId,
                                ':title' => $plan['title'],
                                ':description' => $plan['desc'],
                                ':price' => $plan['price']
                            ]
                        );
                        $course = $this->courseModel->getById($courseId);
                    } catch (\Throwable $ex) {
                        error_log("Failed to auto-create license course: " . $ex->getMessage());
                    }
                }
            }
        }

        if (!$course || !$course['is_active']) {
            flash('error', 'Course not available.');
            redirect('/');
            return;
        }

        // Generate a unique invoice number first
        $invoiceNo = $this->invoiceModel->generateInvoiceNo();

        // Generate KHQR QR code
        try {
            $qrData = $this->khqrService->generatePaymentQR(
                (float) $course['price'],
                $invoiceNo,
                $course['currency']
            );
        } catch (\Throwable $e) {
            error_log('KHQR Generation Error: ' . $e->getMessage());
            flash('error', 'Payment system error. Please try again later.');
            redirect('/course/' . $courseId);
            return;
        }

        // Create invoice with generic student name or user profile if logged in
        $buyerName = $_SESSION['user_name'] ?? ('Student #' . rand(1000, 9999));
        $buyerEmail = $_SESSION['user_email'] ?? null;

        $licenseKey = $_GET['license_key'] ?? null;
        $hardwareId = $_GET['hardware_id'] ?? null;

        try {
            $licenseKey = \App\Services\LicenseClient::keyForPlan($courseId, $licenseKey);
            if ($hardwareId !== null && (!is_string($hardwareId) || strlen($hardwareId) > 255)) {
                throw new \InvalidArgumentException('Invalid device identifier.');
            }
        } catch (\InvalidArgumentException $error) {
            flash('error', $error->getMessage());
            redirect('/');
            return;
        }

        $this->invoiceModel->create([
            'invoice_no'  => $invoiceNo,
            'course_id'   => $courseId,
            'buyer_name'  => $buyerName,
            'buyer_phone' => null,
            'buyer_email' => $buyerEmail,
            'amount'      => $course['price'],
            'currency'    => $course['currency'],
            'qr_string'   => $qrData['qr'],
            'md5_hash'    => $qrData['md5'],
            'license_key' => $licenseKey,
            'hardware_id' => $hardwareId,
        ]);

        // Redirect to QR display page
        redirect('/payment/' . $invoiceNo);
    }

    /**
     * Process checkout form and create invoice
     */
    public function processCheckout(): void
    {
        // Verify CSRF
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid request. Please try again.');
            redirect('/');
            return;
        }

        // Validate inputs
        $courseId  = (int) ($_POST['course_id'] ?? 0);
        $buyerName  = trim($_POST['buyer_name'] ?? '');
        $buyerPhone = trim($_POST['buyer_phone'] ?? '');
        $buyerEmail = trim($_POST['buyer_email'] ?? '');

        if (empty($buyerName) || $courseId <= 0) {
            flash('error', 'Please fill in all required fields.');
            redirect('/checkout?course_id=' . $courseId);
            return;
        }

        // Buyer must confirm the no-refund policy before checkout proceeds
        if (empty($_POST['agree_policy'])) {
            flash('error', 'Please agree to the No-Refund Policy before proceeding.');
            redirect('/checkout?course_id=' . $courseId);
            return;
        }

        // Validate phone format (optional field but validate if provided)
        if ($buyerPhone && !preg_match('/^[0-9+\-\s]{6,20}$/', $buyerPhone)) {
            flash('error', 'Invalid phone number format.');
            redirect('/checkout?course_id=' . $courseId);
            return;
        }

        // Get course
        $course = $this->courseModel->getById($courseId);
        if (!$course || !$course['is_active']) {
            flash('error', 'Course not available.');
            redirect('/');
            return;
        }

        // Generate a unique invoice number first
        $invoiceNo = $this->invoiceModel->generateInvoiceNo();

        // Generate KHQR QR code
        try {
            $qrData = $this->khqrService->generatePaymentQR(
                (float) $course['price'],
                $invoiceNo,
                $course['currency']
            );
        } catch (\Throwable $e) {
            error_log('KHQR Generation Error: ' . $e->getMessage());
            flash('error', 'Payment system error. Please try again later.');
            redirect('/course/' . $courseId);
            return;
        }

        // Create invoice
        $this->invoiceModel->create([
            'invoice_no'  => $invoiceNo,
            'course_id'   => $courseId,
            'buyer_name'  => $buyerName,
            'buyer_phone' => $buyerPhone ?: null,
            'buyer_email' => $buyerEmail ?: null,
            'amount'      => $course['price'],
            'currency'    => $course['currency'],
            'qr_string'   => $qrData['qr'],
            'md5_hash'    => $qrData['md5'],
            'license_key' => \App\Services\LicenseClient::keyForPlan($courseId),
        ]);

        // Redirect to QR display page
        redirect('/payment/' . $invoiceNo);
    }

    /**
     * Display QR code payment page
     */
    public function showQR(string $invoiceNo): void
    {
        $invoice = $this->authorizedInvoice($invoiceNo);

        if (!$invoice) {
            http_response_code(404);
            echo '<h1>Invoice Not Found</h1>';
            return;
        }

        // If already completed, redirect to success
        if ($invoice['payment_status'] === 'completed') {
            redirect('/payment/success/' . $invoiceNo);
            return;
        }

        $pageTitle = 'Complete Payment — ' . APP_NAME;
        $expiryMinutes = QR_EXPIRY_MINUTES;
        require APP_ROOT . '/app/views/payment/qr.php';
    }

    /**
     * AJAX endpoint — Check payment status (polled every 3 seconds)
     */
    public function checkPaymentStatus(string $invoiceNo): void
    {
        header('Content-Type: application/json');

        $invoice = $this->authorizedInvoice($invoiceNo);

        if (!$invoice) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found']);
            return;
        }

        // Already completed — return success with product details
        if ($invoice['payment_status'] === 'completed') {
            echo json_encode([
                'status'        => 'completed',
                'delivery_status' => (new \App\Services\LicenseDeliveryService($this->invoiceModel))->deliver($invoice),
                'product_type'  => $invoice['product_type'] ?? 'course',
                'telegram_link' => $invoice['telegram_link'] ?? null,
                'download_link' => $invoice['download_link'] ?? null,
                'invoice_no'    => $invoiceNo,
            ]);
            return;
        }

        // Check if expired (past QR_EXPIRY_MINUTES)
        $createdAt = strtotime($invoice['created_at']);
        $expiresAt = $createdAt + (QR_EXPIRY_MINUTES * 60);
        if (time() > $expiresAt) {
            $this->invoiceModel->updateStatus($invoiceNo, 'expired');
            echo json_encode(['status' => 'expired', 'message' => 'Payment time expired']);
            return;
        }

        // Poll Bakong API to check if payment arrived
        if (!empty($invoice['md5_hash'])) {
            try {
                $bakongService = new BakongService();
                $result = $bakongService->checkTransactionByMD5($invoice['md5_hash']);

                if ($result['found']) {
                    // Verify amount
                    $amountOk = $bakongService->verifyAmount(
                        $result['data'],
                        (float) $invoice['amount'],
                        $invoice['currency']
                    );

                    if ($amountOk) {
                        // Update invoice status (atomic, prevents duplicates)
                        $updated = $this->invoiceModel->updateStatus($invoiceNo, 'completed');

                        if ($updated) {
                            // Increment promo code uses if applied
                            if (!empty($invoice['promo_code'])) {
                                try {
                                    $promoModel = new \App\Models\PromoCodeModel();
                                    $promoModel->incrementUses($invoice['promo_code']);
                                } catch (\Throwable $e) {
                                    error_log('Polling increment promo uses error: ' . $e->getMessage());
                                }
                            }

                            // Auto-register license if applicable
                            if (!empty($invoice['license_key'])) {
                                $this->registerLicense($invoice);
                            }

                            // Generate Telegram invite link only if it is a course
                            $telegramLink = (($invoice['product_type'] ?? 'course') === 'course') ? $this->generateTelegramLink($invoice) : null;

                            echo json_encode([
                                'status'        => 'completed',
                                'product_type'  => $invoice['product_type'] ?? 'course',
                                'telegram_link' => $telegramLink,
                                'download_link' => $invoice['download_link'] ?? null,
                                'invoice_no'    => $invoiceNo,
                            ]);
                            return;
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('Payment check error: ' . $e->getMessage());
                // Don't expose internal errors, just return pending
            }
        }

        // Calculate remaining time
        $remaining = max(0, $expiresAt - time());

        echo json_encode([
            'status'         => 'pending',
            'remaining_secs' => $remaining,
        ]);
    }

    /**
     * Display payment success page
     */
    public function success(string $invoiceNo): void
    {
        $invoice = $this->authorizedInvoice($invoiceNo);

        if (!$invoice || $invoice['payment_status'] !== 'completed') {
            redirect('/payment/' . $invoiceNo);
            return;
        }

        $pageTitle = 'Payment Successful — ' . APP_NAME;
        $licenseDeliveryStatus = (new \App\Services\LicenseDeliveryService($this->invoiceModel))->deliver($invoice);
        require APP_ROOT . '/app/views/payment/success.php';
    }

    public function retryDelivery(string $invoiceNo): void
    {
        $invoice = $this->authorizedInvoice($invoiceNo);
        if (!$invoice || $invoice['payment_status'] !== 'completed') {
            http_response_code(404);
            echo 'Purchase not found';
            return;
        }
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo 'Invalid request';
            return;
        }
        $status = (new \App\Services\LicenseDeliveryService($this->invoiceModel))->deliver($invoice);
        flash($status === 'delivered' ? 'success' : 'info', $status === 'delivered'
            ? 'License registration confirmed.' : 'Payment received. Your key is available; license registration is pending. Please retry after one minute.');
        redirect('/payment/success/' . $invoiceNo);
    }

    /**
     * Display printable payment receipt (only for completed invoices)
     */
    public function receipt(string $invoiceNo): void
    {
        $invoice = $this->authorizedInvoice($invoiceNo);

        if (!$invoice || $invoice['payment_status'] !== 'completed') {
            redirect('/payment/' . $invoiceNo);
            return;
        }

        $pageTitle = 'Receipt — ' . APP_NAME;
        require APP_ROOT . '/app/views/payment/receipt.php';
    }

    private function authorizedInvoice(string $invoiceNo): ?array
    {
        header('Cache-Control: private, no-store');
        header('Referrer-Policy: no-referrer');
        $invoice = $this->invoiceModel->getByInvoiceNo($invoiceNo);
        return $invoice && \App\Services\PurchaseAccess::canView($invoice, $_SESSION) ? $invoice : null;
    }

    /**
     * Generate Telegram invite link and save to database
     */
    private function generateTelegramLink(array $invoice): ?string
    {
        try {
            $telegram = new TelegramService();
            $result = $telegram->createInviteLink(
                $invoice['telegram_group_id'],
                10, // 10 minutes expiry
                1   // single use
            );

            if ($result['success'] && $result['invite_link']) {
                $this->invoiceModel->updateTelegramLink(
                    $invoice['invoice_no'],
                    $result['invite_link'],
                    $result['expires_at']
                );
                return $result['invite_link'];
            }

            error_log('Telegram invite link creation failed: ' . ($result['error'] ?? 'Unknown'));
        } catch (\Throwable $e) {
            error_log('Telegram service error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * AJAX endpoint — Process quick checkout and return KHQR data
     */
    public function quickCheckout(): void
    {
        header('Content-Type: application/json');

        // Read JSON post data
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = (int) ($input['course_id'] ?? 0);
        $promoCode = trim($input['promo_code'] ?? '');

        if ($courseId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid course ID']);
            return;
        }

        if (empty($input['agree_policy'])) {
            echo json_encode(['status' => 'error', 'message' => 'Please agree to the No-Refund Policy before proceeding.']);
            return;
        }

        // Get course
        $course = $this->courseModel->getById($courseId);
        if (!$course || !$course['is_active']) {
            echo json_encode(['status' => 'error', 'message' => 'Course not available']);
            return;
        }

        // Calculate amount and discounts if promo code is provided
        $finalAmount = (float)$course['price'];
        $discountAmount = 0.00;
        $appliedCode = null;

        if (!empty($promoCode)) {
            $promoModel = new \App\Models\PromoCodeModel();
            $result = $promoModel->validateCode($promoCode, $finalAmount, $course['currency']);
            if ($result['valid']) {
                $discountAmount = $result['discount_amount'];
                $finalAmount = $result['final_price'];
                $appliedCode = $result['code'];
            } else {
                echo json_encode(['status' => 'error', 'message' => $result['message']]);
                return;
            }
        }

        // Generate a unique invoice number first
        $invoiceNo = $this->invoiceModel->generateInvoiceNo();

        // Generate KHQR QR code for the final amount
        try {
            $qrData = $this->khqrService->generatePaymentQR(
                $finalAmount,
                $invoiceNo,
                $course['currency']
            );
        } catch (\Throwable $e) {
            error_log('Quick KHQR Generation Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Payment system error. Please try again.']);
            return;
        }

        // Create invoice with generic buyer name or user profile if logged in
        $buyerName = $_SESSION['user_name'] ?? ('Student #' . rand(1000, 9999));
        $buyerEmail = $_SESSION['user_email'] ?? null;

        $this->invoiceModel->create([
            'invoice_no'       => $invoiceNo,
            'course_id'        => $courseId,
            'buyer_name'       => $buyerName,
            'buyer_phone'      => null,
            'buyer_email'      => $buyerEmail,
            'amount'           => $finalAmount,
            'promo_code'       => $appliedCode,
            'discount_amount'  => $discountAmount,
            'currency'         => $course['currency'],
            'qr_string'        => $qrData['qr'],
            'md5_hash'         => $qrData['md5'],
            'license_key'      => \App\Services\LicenseClient::keyForPlan($courseId),
        ]);

        echo json_encode([
            'status'        => 'success',
            'invoice_no'    => $invoiceNo,
            'qr_string'     => $qrData['qr'],
            'md5_hash'      => $qrData['md5'],
            'amount'        => $finalAmount,
            'currency'      => $course['currency'],
            'course_title'  => $course['title'],
            'merchant_name' => BAKONG_MERCHANT_NAME,
            'check_url'     => APP_URL . '/api/check-payment/' . $invoiceNo,
            'remaining_secs'=> QR_EXPIRY_MINUTES * 60,
        ]);
    }

    /**
     * AJAX endpoint — Verify promo code and return discount details
     */
    public function checkPromoCode(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = (int) ($input['course_id'] ?? 0);
        $promoCode = trim($input['promo_code'] ?? '');

        if ($courseId <= 0 || empty($promoCode)) {
            echo json_encode(['status' => 'error', 'message' => 'ទិន្នន័យមិនត្រឹមត្រូវ (Invalid input data)']);
            return;
        }

        $course = $this->courseModel->getById($courseId);
        if (!$course) {
            echo json_encode(['status' => 'error', 'message' => 'រកមិនឃើញវគ្គសិក្សាទេ (Course not found)']);
            return;
        }

        $promoModel = new \App\Models\PromoCodeModel();
        $result = $promoModel->validateCode($promoCode, (float)$course['price'], $course['currency']);

        if (!$result['valid']) {
            echo json_encode(['status' => 'error', 'message' => $result['message']]);
            return;
        }

        echo json_encode([
            'status'          => 'success',
            'discount_amount' => $result['discount_amount'],
            'final_price'     => $result['final_price'],
            'message'         => $result['message']
        ]);
    }

    /**
     * Helper to call Key Server register API
     */
    private function registerLicense(array $invoice): bool
    {
        $current = $this->invoiceModel->getByInvoiceNo($invoice['invoice_no']);
        return $current && (new \App\Services\LicenseDeliveryService($this->invoiceModel))->deliver($current) === 'delivered';
    }
}
