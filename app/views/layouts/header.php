<?php
// Parse request URI relative to BASE_PATH
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
if (!empty($basePath) && str_starts_with($currentUri, $basePath)) {
    $currentUri = substr($currentUri, strlen($basePath));
}
if (empty($currentUri)) {
    $currentUri = '/';
}
if ($currentUri[0] !== '/') {
    $currentUri = '/' . $currentUri;
}
if ($currentUri !== '/' && str_ends_with($currentUri, '/')) {
    $currentUri = rtrim($currentUri, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageTitle ?? 'Premium online courses with instant Telegram group access') ?>">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%233b82f6'/%3E%3Ctext x='16' y='22' font-family='Inter' font-size='18' font-weight='bold' fill='white' text-anchor='middle'%3EC%3C/text%3E%3C/svg%3E">

    <!-- PWA: installable "app" experience on mobile -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#0a1628">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= e(APP_NAME) ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/icons/apple-touch-icon.png') ?>">

    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.1.1">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <script>
        window.APP_URL = '<?= APP_URL ?>';
    </script>
    <!-- Google Identity Services for Gmail Login -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>

<!-- Navigation -->
<nav class="navbar" id="main-navbar">
    <div class="navbar-inner">
        <a href="<?= APP_URL ?>" class="navbar-brand">
            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="32" height="32" rx="8" fill="url(#brand-gradient)"/>
                <path d="M8 16C8 11.5817 11.5817 8 16 8V8C20.4183 8 24 11.5817 24 16V24H16C11.5817 24 8 20.4183 8 16V16Z" fill="white" fill-opacity="0.9"/>
                <defs><linearGradient id="brand-gradient" x1="0" y1="0" x2="32" y2="32"><stop stop-color="#3b82f6"/><stop offset="1" stop-color="#06b6d4"/></linearGradient></defs>
            </svg>
            <?= e(APP_NAME) ?>
        </a>

        <!-- Navbar Search -->
        <?php if (in_array($currentUri, ['/', '/tools'], true)): ?>
        <div class="navbar-search">
            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); display: flex; align-items: center; pointer-events: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" id="course-search-input" placeholder="ស្វែងរក... (Search...)" />
            <span id="search-clear-btn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer; display: none; align-items: center; font-size: 1.1rem; user-select: none;">&times;</span>
        </div>
        <?php endif; ?>

        <button class="menu-toggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <div class="navbar-links">
            <a href="<?= APP_URL ?>" class="<?= $currentUri === '/' ? 'active' : '' ?>">Courses</a>
            <a href="<?= APP_URL ?>/tools" class="<?= $currentUri === '/tools' ? 'active' : '' ?>">Tools</a>
            <a href="<?= APP_URL ?>/telegram-adder-pro" class="<?= $currentUri === '/telegram-adder-pro' ? 'active' : '' ?>">Tool Add Telegram</a>
            <?php if (isset($_SESSION['user_email'])): ?>
                <a href="<?= APP_URL ?>/my-downloads" class="<?= $currentUri === '/my-downloads' ? 'active' : '' ?>" style="display:inline-flex; align-items:center; gap:8px;">
                    <?php if (!empty($_SESSION['user_picture'])): ?>
                        <img src="<?= e($_SESSION['user_picture']) ?>" alt="<?= e($_SESSION['user_name']) ?>" style="width:24px; height:24px; border-radius:50%; border:1px solid var(--border-accent); vertical-align:middle; display:inline-block;">
                    <?php else: ?>
                        <span style="width:24px; height:24px; border-radius:50%; background:var(--gradient-primary); display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; color:#fff; font-weight:bold; vertical-align:middle;">
                            <?= strtoupper(substr(e($_SESSION['user_name']), 0, 1)) ?>
                        </span>
                    <?php endif; ?>
                    <span>Profile</span>
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/login" class="btn-nav btn-sm <?= $currentUri === '/login' ? 'active' : '' ?>" style="padding: 6px 14px !important; font-size:0.8rem; font-weight:700; height:auto; margin:0; display:inline-flex; align-items:center; gap:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                     Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Mobile App-style Bottom Tab Bar (hidden on desktop) -->
<nav class="bottom-nav">
    <a href="<?= APP_URL ?>" class="bottom-nav-item <?= $currentUri === '/' ? 'active' : '' ?>">
        <span class="bottom-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
        <span>Home</span>
    </a>
    <a href="<?= APP_URL ?>/tools" class="bottom-nav-item <?= $currentUri === '/tools' ? 'active' : '' ?>">
        <span class="bottom-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></span>
        <span>Tools</span>
    </a>
    <?php if (isset($_SESSION['user_email'])): ?>
        <a href="<?= APP_URL ?>/my-downloads" class="bottom-nav-item <?= $currentUri === '/my-downloads' ? 'active' : '' ?>">
            <span class="bottom-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <span>Account</span>
        </a>
    <?php else: ?>
        <a href="<?= APP_URL ?>/login" class="bottom-nav-item <?= $currentUri === '/login' ? 'active' : '' ?>">
            <span class="bottom-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <span>Login</span>
        </a>
    <?php endif; ?>
    <button type="button" class="bottom-nav-item" id="bottom-nav-support">
        <span class="bottom-nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>
        <span>Support</span>
    </button>
</nav>

<!-- Flash Messages -->
<?php if ($successMsg = get_flash('success')): ?>
    <div class="container" style="padding-top:16px;">
        <div class="alert alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?= e($successMsg) ?>
        </div>
    </div>
<?php endif; ?>
<?php if ($errorMsg = get_flash('error')): ?>
    <div class="container" style="padding-top:16px;">
        <div class="alert alert-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <?= e($errorMsg) ?>
        </div>
    </div>
<?php endif; ?>
