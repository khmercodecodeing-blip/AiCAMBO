<?php
/**
 * Application Configuration
 * Loads .env file and provides configuration helpers
 */

// Set default timezone
date_default_timezone_set('Asia/Phnom_Penh');

// Define root path
define('APP_ROOT', dirname(__DIR__));

// Load .env file
$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Get environment variable with optional default
 */
function env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'uhewwnouchsina7_user_license'));
define('DB_USER', env('DB_USER', 'uhewwnouchsina7_user_license_tel'));
define('DB_PASS', env('DB_PASS', 'D9W8$sk@6xA_4{R-'));

// Application
define('BASE_PATH', env('BASE_PATH', '/web'));
define('APP_NAME', env('APP_NAME', 'CourseHub'));

// Admin Configuration
define('ADMIN_PREFIX', env('ADMIN_PREFIX', 'admin'));

$detectedUrl = 'http://localhost' . BASE_PATH;
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $detectedUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_PATH;
}
$configuredUrl = env('APP_URL');
if (empty($configuredUrl) || $configuredUrl === 'http://localhost/web') {
    define('APP_URL', rtrim($detectedUrl, '/'));
} else {
    define('APP_URL', rtrim($configuredUrl, '/'));
}

define('ADMIN_URL', APP_URL . '/' . ADMIN_PREFIX);
define('ADMIN_TELEGRAM', env('ADMIN_TELEGRAM', 'https://t.me/MrrSina'));

// Bakong KHQR
define('BAKONG_TOKEN', env('BAKONG_TOKEN', ''));
define('BAKONG_ACCOUNT_ID', env('BAKONG_ACCOUNT_ID', env('BAKONG_ACCOUNT', '')));
define('BAKONG_ACCOUNT', env('BAKONG_ACCOUNT', env('BAKONG_ACCOUNT_ID', '')));
define('BAKONG_MERCHANT_NAME', env('BAKONG_MERCHANT_NAME', 'CourseHub'));
define('BAKONG_MERCHANT_CITY', env('BAKONG_MERCHANT_CITY', 'PHNOM PENH'));
define('BAKONG_CURRENCY', env('BAKONG_CURRENCY', 'USD'));

// Telegram
define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));

// Google OAuth
define('GOOGLE_CLIENT_ID', env('GOOGLE_CLIENT_ID', ''));

// Auto-Deploy Webhook (GitHub -> cPanel Git Version Control)
define('DEPLOY_WEBHOOK_SECRET', env('DEPLOY_WEBHOOK_SECRET', ''));
define('DEPLOY_BRANCH', env('DEPLOY_BRANCH', 'main'));
define('CPANEL_HOST', env('CPANEL_HOST', ''));
define('CPANEL_USER', env('CPANEL_USER', ''));
define('CPANEL_API_TOKEN', env('CPANEL_API_TOKEN', ''));
define('CPANEL_REPO_ROOT', env('CPANEL_REPO_ROOT', ''));


// Admin
define('ADMIN_USERNAME', env('ADMIN_USERNAME', 'admin'));
define('ADMIN_PASSWORD', env('ADMIN_PASSWORD', ''));

// Webhook
define('WEBHOOK_SECRET', env('WEBHOOK_SECRET', ''));

// QR Settings
define('QR_EXPIRY_MINUTES', (int) env('QR_EXPIRY_MINUTES', 4));

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or get CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output hidden CSRF field
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize output for HTML
 */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Current UI language ('km' or 'en'), stored in session, defaults to Khmer
 */
function current_lang(): string {
    $lang = $_SESSION['lang'] ?? 'km';
    return in_array($lang, ['km', 'en'], true) ? $lang : 'km';
}

/**
 * Translate a UI string key for the current language, falling back to English then the key itself
 */
function t(string $key): string {
    static $cache = [];
    $lang = current_lang();

    if (!isset($cache[$lang])) {
        $file = APP_ROOT . "/config/lang/{$lang}.php";
        $cache[$lang] = file_exists($file) ? require $file : [];
    }
    if (isset($cache[$lang][$key])) {
        return $cache[$lang][$key];
    }

    if (!isset($cache['en'])) {
        $file = APP_ROOT . '/config/lang/en.php';
        $cache['en'] = file_exists($file) ? require $file : [];
    }
    return $cache['en'][$key] ?? $key;
}

/**
 * Redirect helper
 */
function redirect(string $path): void {
    header('Location: ' . APP_URL . $path);
    exit;
}

/**
 * Set flash message
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash(string $type): ?string {
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

/**
 * Check if current user is admin
 */
function is_admin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Require admin authentication
 */
function require_admin(): void {
    if (!is_admin()) {
        redirect('/' . ADMIN_PREFIX . '/login');
    }
}

/**
 * Get asset URL
 */
function asset(string $path): string {
    return APP_URL . '/public/assets/' . ltrim($path, '/');
}

/**
 * Format currency
 */
function format_price(float $amount, string $currency = 'USD'): string {
    if ($currency === 'KHR') {
        return number_format($amount, 0) . ' ៛';
    }
    return '$' . number_format($amount, 2);
}

/**
 * Generate video player HTML for YouTube or direct MP4/WebM URL
 */
function get_video_player_html(?string $videoUrl): ?string {
    if (empty($videoUrl)) return null;

    $videoUrl = trim($videoUrl);

    // 1. YouTube watch/share/embed links
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $videoUrl, $matches)) {
        $videoId = $matches[1];
        return '<div class="video-container"><iframe src="https://www.youtube.com/embed/' . e($videoId) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
    }

    // 2. Direct Video File (.mp4, .webm, .ogg)
    $ext = strtolower(pathinfo(parse_url($videoUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
    if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
        return '<div class="video-container"><video src="' . e($videoUrl) . '" controls></video></div>';
    }

    // 3. Fallback: Treat as generic iframe
    return '<div class="video-container"><iframe src="' . e($videoUrl) . '" allowfullscreen></iframe></div>';
}

/**
 * Get the real client IP address
 */
function get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    // For local testing, map localhost IPv6 (::1) to IPv4
    if ($ip === '::1') {
        $ip = '127.0.0.1';
    }
    return $ip;
}
