<?php
$subtotal = (float) $invoice['amount'] + (float) ($invoice['discount_amount'] ?? 0);
$isTool = ($invoice['product_type'] ?? 'course') === 'tool';
$paidAt = !empty($invoice['paid_at']) ? date('d M Y, h:i A', strtotime($invoice['paid_at'])) : '—';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= asset('images/icons/favicon-32.png') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=2.0.0">
    <style>
        .receipt-wrap { max-width: 480px; margin: 40px auto; padding: 0 16px; }
        .receipt-card {
            background: #ffffff; color: #1e293b; border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15,23,42,0.10); overflow: hidden;
        }
        .receipt-head {
            background: linear-gradient(135deg, #2563eb, #0ea5e9); color: #fff; padding: 24px 28px; text-align: center;
        }
        .receipt-head img { width: 40px; height: 40px; margin-bottom: 8px; }
        .receipt-head h2 { margin: 0; font-size: 1.1rem; letter-spacing: 0.5px; }
        .receipt-head .status-badge {
            display: inline-block; margin-top: 10px; padding: 4px 14px; border-radius: 999px;
            background: #16a34a; color: #fff; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;
        }
        .receipt-body { padding: 24px 28px; }
        .receipt-row {
            display: flex; justify-content: space-between; gap: 12px;
            padding: 8px 0; font-size: 0.9rem; border-bottom: 1px dashed #e2e8f0;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .label { color: #64748b; }
        .receipt-row .value { font-weight: 600; text-align: right; word-break: break-word; }
        .receipt-total {
            display: flex; justify-content: space-between; align-items: center;
            margin-top: 16px; padding-top: 16px; border-top: 2px solid #2563eb;
        }
        .receipt-total .label { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .receipt-total .value { font-size: 1.4rem; font-weight: 800; color: #2563eb; }
        .receipt-footer {
            text-align: center; padding: 16px 28px 24px; font-size: 0.75rem; color: #94a3b8;
        }
        .receipt-actions { display: flex; gap: 10px; margin-top: 20px; }
        .receipt-actions button, .receipt-actions a {
            flex: 1; text-align: center; padding: 10px; border-radius: 10px; font-size: 0.85rem;
            font-weight: 600; cursor: pointer; border: none; text-decoration: none;
        }
        .btn-print { background: var(--gradient-primary, #2563eb); color: #fff; }
        .btn-back { background: #ffffff; color: #475569; border: 1px solid #cbd5e1 !important; }

        @media print {
            body { background: #fff !important; }
            .navbar, .receipt-actions { display: none !important; }
            .receipt-wrap { margin: 0; max-width: 100%; }
            .receipt-card { box-shadow: none; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= APP_URL ?>" class="navbar-brand">
            <img src="<?= asset('images/icons/logo-navbar.png') ?>" alt="<?= e(APP_NAME) ?>" width="32" height="32">
            <?= e(APP_NAME) ?>
        </a>
    </div>
</nav>

<div class="receipt-wrap">
    <div class="receipt-card">
        <div class="receipt-head">
            <h2><?= e(APP_NAME) ?></h2>
            <div style="font-size:0.75rem; opacity:0.7; margin-top:2px;">Official Payment Receipt</div>
            <div class="status-badge">PAID</div>
        </div>
        <div class="receipt-body">
            <div class="receipt-row">
                <span class="label">Invoice No.</span>
                <span class="value"><?= e($invoice['invoice_no']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="label">Date Paid</span>
                <span class="value"><?= e($paidAt) ?></span>
            </div>
            <div class="receipt-row">
                <span class="label">Billed To</span>
                <span class="value"><?= e($invoice['buyer_name']) ?></span>
            </div>
            <?php if (!empty($invoice['buyer_email'])): ?>
            <div class="receipt-row">
                <span class="label">Email</span>
                <span class="value"><?= e($invoice['buyer_email']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($invoice['buyer_phone'])): ?>
            <div class="receipt-row">
                <span class="label">Phone</span>
                <span class="value"><?= e($invoice['buyer_phone']) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row">
                <span class="label"><?= $isTool ? 'Product' : 'Course' ?></span>
                <span class="value"><?= e($invoice['course_title']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="label">Payment Method</span>
                <span class="value">KHQR (Bakong)</span>
            </div>
            <?php if (!empty($invoice['license_key']) && ($invoice['license_delivery_status'] ?? 'pending') === 'delivered'): ?>
            <div class="receipt-row">
                <span class="label">License Key</span>
                <span class="value" style="font-family:monospace;"><?= e($invoice['license_key']) ?></span>
            </div>
            <?php endif; ?>

            <div class="receipt-row" style="margin-top:12px;">
                <span class="label">Subtotal</span>
                <span class="value"><?= format_price($subtotal, $invoice['currency']) ?></span>
            </div>
            <?php if (!empty($invoice['discount_amount']) && (float)$invoice['discount_amount'] > 0): ?>
            <div class="receipt-row">
                <span class="label">Discount <?= !empty($invoice['promo_code']) ? '(' . e($invoice['promo_code']) . ')' : '' ?></span>
                <span class="value">-<?= format_price((float)$invoice['discount_amount'], $invoice['currency']) ?></span>
            </div>
            <?php endif; ?>

            <div class="receipt-total">
                <span class="label">Total Paid</span>
                <span class="value"><?= format_price((float)$invoice['amount'], $invoice['currency']) ?></span>
            </div>

            <div class="receipt-actions">
                <button type="button" class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
                <a href="<?= APP_URL ?>/payment/success/<?= e($invoice['invoice_no']) ?>" class="btn-back">← Back</a>
            </div>
        </div>
        <div class="receipt-footer">
            This is a computer-generated receipt and does not require a signature.<br>
            Thank you for your purchase — <?= e(APP_NAME) ?>
        </div>
    </div>
</div>

</body>
</html>
