<?php
$createdAt = strtotime($invoice['created_at']);
$expiresAt = $createdAt + ($expiryMinutes * 60);
$remainingSecs = max(0, $expiresAt - time());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= asset('images/icons/favicon-32.png') ?>">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#0a1628">
    <link rel="apple-touch-icon" href="<?= asset('images/icons/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.2.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="<?= asset('js/qrcode.min.js') ?>"></script>
    <script>if (typeof QRCode === 'undefined') { document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>'); }</script>
    <script>window.APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= APP_URL ?>" class="navbar-brand">
            <img src="<?= asset('images/icons/logo-navbar.png') ?>" alt="<?= e(APP_NAME) ?>" width="32" height="32">
            <?= e(APP_NAME) ?>
        </a>
    </div>
</nav>

<div class="payment-page" style="min-height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; padding: 10px 16px;">
    <div class="custom-modal-content fade-in" style="transform: scale(1); margin: 0 auto; text-align: center; max-width: 320px; box-shadow: var(--shadow-lg); padding: 12px 14px;">
        
        <!-- The KHQR Receipt Card -->
        <div class="khqr-receipt-card" style="box-shadow: 0 4px 20px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); margin-bottom: 10px; max-width: 290px;">
            <!-- Red Header -->
            <div class="khqr-receipt-header" style="padding: 8px 16px;">
                <div class="khqr-logo-text" style="font-size: 1.1rem;">KHQR</div>
            </div>
            
            <!-- Info Section -->
            <div class="khqr-receipt-body" style="padding: 10px 16px 6px;">
                <div class="khqr-merchant-name" style="font-size: 0.7rem; margin-bottom: 2px;"><?= e(BAKONG_MERCHANT_NAME) ?></div>
                <div class="khqr-amount-row">
                    <span class="khqr-amount-value" style="font-size: 1.4rem; font-weight: 800;"><?= number_format($invoice['amount'], $invoice['currency'] === 'KHR' ? 0 : 2) ?></span>
                    <span class="khqr-currency-code" style="font-size: 0.8rem;"><?= e($invoice['currency']) ?></span>
                </div>
            </div>
            
            <!-- Dashed separator -->
            <div class="khqr-receipt-separator" style="margin: 4px 12px;"></div>
            
            <!-- QR Section -->
            <div class="khqr-receipt-qr-section" style="padding: 6px 16px 10px;">
                <div class="qr-container" style="padding: 10px; border-radius: 12px;">
                    <div id="qr-code" data-qr-string="<?= e($invoice['qr_string']) ?>"></div>
                    <div class="qr-logo" style="width: 28px; height: 28px; font-size: 0.85rem; border-width: 2px;">
                        <?= $invoice['currency'] === 'KHR' ? '៛' : '$' ?>
                    </div>
                </div>
            </div>
        </div>

        <p style="color: var(--text-secondary); font-size: 0.75rem; margin: 8px 0 6px; line-height: 1.35; padding: 0 6px;">
            Scan with ABA Mobile, Acleda Mobile, or any Mobile Banking App supporting KHQR
        </p>

        <!-- Countdown -->
        <div class="countdown" style="margin: 4px 0;">
            <div class="countdown-label" style="font-size:0.75rem; color:#64748b;">Time remaining</div>
            <div class="countdown-timer" id="countdown-timer" data-remaining="<?= $remainingSecs ?>" style="font-size: 1.6rem; font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                --:--
            </div>
        </div>

        <!-- Payment Status -->
        <div class="payment-status checking"
             id="payment-status"
             data-invoice-no="<?= e($invoice['invoice_no']) ?>"
             data-check-url="<?= APP_URL ?>/api/check-payment/<?= e($invoice['invoice_no']) ?>"
             style="margin: 6px auto 10px; max-width: 280px; font-size: 0.85rem; padding: 6px;">
            <div class="pulse-dot"></div>
            <span>Checking payment status...</span>
        </div>

        <p class="text-muted mt-2" style="font-size: 0.75rem; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px; line-height: 1.4;">
            Invoice: <?= e($invoice['invoice_no']) ?>
            <br>
            Plan: <?= e($invoice['course_title']) ?>
        </p>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/payment.js') ?>"></script>

</body>
</html>
