<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin Login') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.1.2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

<div class="login-page">
    <div class="glass-card login-card fade-in">
        <!-- Brand -->
        <div style="margin-bottom:24px;">
            <div style="width:56px;height:56px;margin:0 auto 16px;background:var(--gradient-primary);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
        </div>

        <h1>Admin Panel</h1>
        <p class="subtitle">Sign in to manage your courses</p>

        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error" style="text-align:left;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= ADMIN_URL ?>/login">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="Enter username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block mt-3">
                Sign In
            </button>
        </form>

        <p class="mt-3 text-muted" style="font-size:0.8rem;">
            <a href="<?= APP_URL ?>">← Back to website</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
