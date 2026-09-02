<?php

/**
 * Route Definitions
 * Maps URL patterns to controller methods
 */

use App\Controllers\CourseController;
use App\Controllers\PaymentController;
use App\Controllers\WebhookController;
use App\Controllers\AdminController;
use App\Controllers\PageController;

/**
 * Simple router — matches request URI against patterns
 */
class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes['GET'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->routes['POST'][] = ['pattern' => $pattern, 'handler' => $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Security Trap: If custom ADMIN_PREFIX is active, and someone tries to access '/admin' prefix
        if (ADMIN_PREFIX !== 'admin') {
            $normalizedUri = rtrim(strtolower($uri), '/');
            if ($normalizedUri === '/admin' || str_starts_with($normalizedUri, '/admin/')) {
                http_response_code(403);
                require APP_ROOT . '/app/views/admin/trap.php';
                return;
            }
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            $pattern = $route['pattern'];

            // Convert route pattern to regex
            // e.g., /course/{id} becomes /course/([^/]+)
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // Remove full match
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        require APP_ROOT . '/app/views/layouts/header.php';
        echo '<div style="text-align:center;padding:100px 20px;">';
        echo '<h1 style="font-size:4rem;margin-bottom:20px;background:linear-gradient(135deg,#3b82f6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">404</h1>';
        echo '<p style="color:#94a3b8;font-size:1.2rem;">Page not found</p>';
        echo '<a href="' . APP_URL . '" style="display:inline-block;margin-top:20px;padding:12px 30px;background:linear-gradient(135deg,#3b82f6,#06b6d4);color:#fff;border-radius:12px;text-decoration:none;">Go Home</a>';
        echo '</div>';
        require APP_ROOT . '/app/views/layouts/footer.php';
    }
}

// Initialize router
$router = new Router();

// ===========================
// PUBLIC ROUTES
// ===========================

// Home — Course catalog
$router->get('/', function () {
    (new CourseController())->index();
});

// Course detail
$router->get('/course/{id}', function ($id) {
    (new CourseController())->detail((int) $id);
});

// Telegram Adder Pro Product Landing Page
$router->get('/telegram-adder-pro', function () {
    (new CourseController())->telegramAdderPage();
});

// Privacy & Refund Policy Page
$router->get('/policy', function () {
    (new PageController())->policy();
});

// Checkout form (GET)
$router->get('/checkout', function () {
    (new PaymentController())->checkout();
});

// Process checkout (POST)
$router->post('/checkout', function () {
    (new PaymentController())->processCheckout();
});

// Payment QR display
$router->get('/payment/{invoiceNo}', function ($invoiceNo) {
    (new PaymentController())->showQR($invoiceNo);
});

// Payment status check (AJAX polling)
$router->get('/api/check-payment/{invoiceNo}', function ($invoiceNo) {
    (new PaymentController())->checkPaymentStatus($invoiceNo);
});

// Quick checkout API (POST)
$router->post('/api/quick-checkout', function () {
    (new PaymentController())->quickCheckout();
});

// Promo code validation API (POST)
$router->post('/api/check-promo', function () {
    (new PaymentController())->checkPromoCode();
});

// Payment success page
$router->get('/payment/success/{invoiceNo}', function ($invoiceNo) {
    (new PaymentController())->success($invoiceNo);
});

// ===========================
// STUDENT AUTH ROUTES
// ===========================

// Login page
$router->get('/login', function () {
    (new App\Controllers\AuthController())->loginForm();
});

// Google OAuth Callback
$router->post('/auth/google', function () {
    (new App\Controllers\AuthController())->googleCallback();
});

// Logout
$router->get('/logout', function () {
    (new App\Controllers\AuthController())->logout();
});

// Downloads Dashboard
$router->get('/my-downloads', function () {
    (new App\Controllers\AuthController())->myDownloads();
});

// Dynamic Telegram Link Join
$router->get('/join/{invoiceNo}', function ($invoiceNo) {
    (new App\Controllers\AuthController())->joinGroup($invoiceNo);
});

// ===========================
// WEBHOOK ROUTES
// ===========================

$router->post('/webhook/bakong', function () {
    (new WebhookController())->handleBakong();
});

// ===========================
// ADMIN ROUTES
// ===========================

$adminPrefix = '/' . ADMIN_PREFIX;

$router->get($adminPrefix . '/login', function () {
    (new AdminController())->loginForm();
});

$router->post($adminPrefix . '/login', function () {
    (new AdminController())->login();
});

$router->get($adminPrefix . '/dashboard', function () {
    (new AdminController())->dashboard();
});

$router->get($adminPrefix . '/courses', function () {
    (new AdminController())->courses();
});

$router->get($adminPrefix . '/courses/form', function () {
    (new AdminController())->courseForm();
});

$router->post($adminPrefix . '/courses/save', function () {
    (new AdminController())->saveCourse();
});

$router->get($adminPrefix . '/courses/delete/{id}', function ($id) {
    (new AdminController())->deleteCourse((int) $id);
});

$router->get($adminPrefix . '/invoices', function () {
    (new AdminController())->invoices();
});

$router->get($adminPrefix . '/students', function () {
    (new AdminController())->students();
});

$router->get($adminPrefix . '/promos', function () {
    (new AdminController())->promos();
});

$router->get($adminPrefix . '/promos/form', function () {
    (new AdminController())->promoForm();
});

$router->post($adminPrefix . '/promos/save', function () {
    (new AdminController())->savePromo();
});

$router->get($adminPrefix . '/promos/delete/{id}', function ($id) {
    (new AdminController())->deletePromo((int) $id);
});

$router->post($adminPrefix . '/logout', function () {
    (new AdminController())->logout();
});

$router->get($adminPrefix . '/logout', function () {
    (new AdminController())->logout();
});

return $router;
