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

        if (!$course) {
            flash('error', 'Course not found.');
            redirect('/');
            return;
        }

        // Generate a unique invoice number first
        $invoiceNo = $this->invoiceModel->generateInvoiceNo();

        // Generate KHQR QR code
        try {
            $qrData = $this->khqrService->generatePaymentQR(
                (float) $course['price'],
                $invoiceNo
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

        // Auto-generate license key if purchasing directly on the website for plans 1, 2, 3
        if (in_array($courseId, [1, 2, 3]) && empty($licenseKey)) {
            $plan_map = [1 => '1_month', 2 => '3_months', 3 => '1_year'];
            $plan_id = $plan_map[$courseId] ?? '1_month';
            
            $plan_codes = [
                "1_month"  => "A1",
                "3_months" => "B3",
                "1_year"   => "CY",
            ];
            $code = $plan_codes[$plan_id] ?? "A1";
            try {
                $rand = strtoupper(bin2hex(random_bytes(5)));
            } catch (\Throwable $e) {
                $rand = strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
            }
            $body = $code . $rand;
            $secret = LICENSE_SIGNING_SECRET;
            $sig = strtoupper(substr(hash_hmac('sha256', $body, $secret), 0, 4));
            $raw = $body . $sig;
            $licenseKey = sprintf("%s-%s-%s-%s", substr($raw, 0, 4), substr($raw, 4, 4), substr($raw, 8, 4), substr($raw, 12, 4));
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
                $invoiceNo
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
        ]);

        // Redirect to QR display page
        redirect('/payment/' . $invoiceNo);
    }

    /**
     * Display QR code payment page
     */
    public function showQR(string $invoiceNo): void
    {
        $invoice = $this->invoiceModel->getByInvoiceNo($invoiceNo);

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

        $invoice = $this->invoiceModel->getByInvoiceNo($invoiceNo);

        if (!$invoice) {
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found']);
            return;
        }

        // Already completed — return success with product details
        if ($invoice['payment_status'] === 'completed') {
            echo json_encode([
                'status'        => 'completed',
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
        $invoice = $this->invoiceModel->getByInvoiceNo($invoiceNo);

        if (!$invoice || $invoice['payment_status'] !== 'completed') {
            redirect('/payment/' . $invoiceNo);
            return;
        }

        $pageTitle = 'Payment Successful — ' . APP_NAME;
        require APP_ROOT . '/app/views/payment/success.php';
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
            $result = $promoModel->validateCode($promoCode, $finalAmount);
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
                $invoiceNo
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
        $result = $promoModel->validateCode($promoCode, (float)$course['price']);

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
        $license_key = $invoice['license_key'];
        $hardware_id = $invoice['hardware_id'];
        
        // Parse plan days from license key prefix
        $raw = strtoupper(str_replace('-', '', $license_key));
        $days = 30; // default fallback
        if (strlen($raw) === 16) {
            $body = substr($raw, 0, 12);
            $sig = substr($raw, 12, 4);
            $secret = LICENSE_SIGNING_SECRET;
            $expected = strtoupper(substr(hash_hmac('sha256', $body, $secret), 0, 4));
            if ($sig === $expected) {
                $code = substr($body, 0, 2);
                if ($code === 'A1') $days = 30;
                elseif ($code === 'B3') $days = 90;
                elseif ($code === 'CY') $days = 365;
            }
        }

        $apiUrl = APP_URL . '/key/api/register.php';
        $apiKey = LICENSE_API_KEY;
        $expiresAt = date('Y-m-d', strtotime("+$days days"));
        
        $payload = [
            'api_key' => $apiKey,
            'license_key' => $license_key,
            'hardware_id' => $hardware_id ?? '',
            'pc_name' => 'Web Purchased',
            'customer_name' => $invoice['buyer_name'],
            'plan' => $days . ' Days',
            'amount' => $invoice['amount'],
            'expires_at' => $expiresAt,
            'transaction_ref' => $invoice['invoice_no']
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: WebCheckout'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        
        if ($err) {
            error_log("Failed to register license: " . $err);
            return false;
        }
        
        $resData = json_decode($response, true);
        if (!$resData || !$resData['success']) {
            error_log("License server API returned error: " . ($resData['message'] ?? 'Unknown error'));
            return false;
        }
        
        return true;
    }
}
