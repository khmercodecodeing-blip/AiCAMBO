<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\InvoiceModel;
use App\Models\CourseModel;
use App\Models\Database;
use App\Services\TelegramService;

/**
 * Auth Controller — handles user login, Google callbacks, logout, student dashboard, and dynamic Telegram invites
 */
class AuthController
{
    private UserModel $userModel;
    private InvoiceModel $invoiceModel;
    private CourseModel $courseModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->invoiceModel = new InvoiceModel();
        $this->courseModel = new CourseModel();
    }

    /**
     * Display student login form
     */
    public function loginForm(): void
    {
        if (isset($_SESSION['user_email'])) {
            redirect('/my-downloads');
            return;
        }
        $pageTitle = 'Student Login — ' . APP_NAME;
        require APP_ROOT . '/app/views/auth/login.php';
    }

    /**
     * Handle Google OAuth redirect POST request containing 'credential' (JWT ID Token)
     */
    public function googleCallback(): void
    {
        $idToken = $_POST['credential'] ?? null;
        if (!$idToken) {
            flash('error', 'Authentication failed: No credential received.');
            redirect('/login');
            return;
        }

        // Verify Google ID Token via Google API
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                flash('error', 'Failed to verify Google account with Google servers. Please try again.');
                redirect('/login');
                return;
            }

            $payload = json_decode($response, true);
            if (isset($payload['error_description']) || !isset($payload['email'])) {
                $err = $payload['error_description'] ?? 'Email not found in payload';
                flash('error', 'Google Auth Error: ' . $err);
                redirect('/login');
                return;
            }

            $email = $payload['email'];
            $name = $payload['name'] ?? 'Student';
            $picture = $payload['picture'] ?? null;
            $googleId = $payload['sub'] ?? null;

            // Create or update user profile
            $userId = $this->userModel->createOrUpdate([
                'email'     => $email,
                'name'      => $name,
                'picture'   => $picture,
                'google_id' => $googleId
            ]);

            // Save details to Session
            $_SESSION['user_id']      = $userId;
            $_SESSION['user_email']    = $email;
            $_SESSION['user_name']     = $name;
            $_SESSION['user_picture']  = $picture;

            flash('success', 'ស្វាគមន៍មកកាន់ ' . APP_NAME . '! ចូលគណនីបានជោគជ័យ។');

            // Redirect back to intended page or downloads dashboard
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/my-downloads';
            unset($_SESSION['redirect_after_login']);
            redirect($redirectUrl);

        } catch (\Throwable $e) {
            error_log('Google Verification Error: ' . $e->getMessage());
            flash('error', 'An unexpected authentication error occurred.');
            redirect('/login');
        }
    }

    /**
     * Log the student out
     */
    public function logout(): void
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_picture']);

        flash('success', 'ចាកចេញពីគណនីបានជោគជ័យ (Logged out successfully).');
        redirect('/');
    }

    /**
     * Student dashboard: shows purchase history and download links
     */
    public function myDownloads(): void
    {
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['redirect_after_login'] = '/my-downloads';
            redirect('/login');
            return;
        }

        $email = $_SESSION['user_email'];

        // Get completed purchases for this email
        $db = Database::getInstance();
        $purchases = $db->fetchAll(
            "SELECT i.*, c.title as course_title, c.thumbnail as course_thumbnail, 
                    c.type as product_type, c.download_link, c.telegram_group_id
             FROM invoices i
             JOIN courses c ON i.course_id = c.id
             WHERE i.buyer_email = :email AND i.payment_status = 'completed'
             ORDER BY i.paid_at DESC",
            [':email' => $email]
        );

        $pageTitle = 'My Purchases & Downloads — ' . APP_NAME;
        require APP_ROOT . '/app/views/student/downloads.php';
    }

    /**
     * Dynamic Telegram join route for a specific purchase
     */
    public function joinGroup(string $invoiceNo): void
    {
        if (!isset($_SESSION['user_email'])) {
            redirect('/login');
            return;
        }

        $invoice = $this->invoiceModel->getByInvoiceNo($invoiceNo);
        if (!$invoice || $invoice['buyer_email'] !== $_SESSION['user_email'] || $invoice['payment_status'] !== 'completed') {
            flash('error', 'Purchase record not found or unauthorized.');
            redirect('/my-downloads');
            return;
        }

        if (($invoice['product_type'] ?? 'course') !== 'course') {
            flash('error', 'This product does not have a Telegram group.');
            redirect('/my-downloads');
            return;
        }

        if (empty($invoice['telegram_group_id'])) {
            flash('error', 'Telegram group is not configured for this course.');
            redirect('/my-downloads');
            return;
        }

        // Check if existing telegram link is still valid (not expired)
        $expiresAt = $invoice['telegram_link_expires_at'] ? strtotime($invoice['telegram_link_expires_at']) : 0;
        if (!empty($invoice['telegram_link']) && $expiresAt > time()) {
            header('Location: ' . $invoice['telegram_link']);
            exit;
        }

        // Generate a new Telegram invite link dynamically
        try {
            $telegram = new TelegramService();
            $result = $telegram->createInviteLink(
                $invoice['telegram_group_id'],
                10, // 10 minutes expiry
                1   // single use
            );

            if ($result['success'] && $result['invite_link']) {
                $this->invoiceModel->updateTelegramLink(
                    $invoiceNo,
                    $result['invite_link'],
                    $result['expires_at']
                );
                header('Location: ' . $result['invite_link']);
                exit;
            }

            flash('error', 'Failed to generate Telegram invite link: ' . ($result['error'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            error_log('Dynamic Invite Generation Error: ' . $e->getMessage());
            flash('error', 'Telegram connection error. Please try again later.');
        }

        redirect('/my-downloads');
    }
}
