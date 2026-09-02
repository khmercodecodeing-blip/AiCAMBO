<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="success-page">
    <div class="glass-card success-card fade-in">
        <!-- Success Icon -->
        <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>

        <h1>Payment Successful!</h1>
        <p class="subtitle">Your payment has been confirmed. You now have access to the <?= (($invoice['product_type'] ?? 'course') === 'tool') ? 'tool' : 'course' ?>.</p>

        <!-- Invoice Summary -->
        <div class="payment-info" style="margin-bottom:32px;">
            <div class="payment-info-row">
                <span class="label">Invoice</span>
                <span class="value"><?= e($invoice['invoice_no']) ?></span>
            </div>
            <div class="payment-info-row">
                <span class="label"><?= (($invoice['product_type'] ?? 'course') === 'tool') ? 'Tool' : 'Course' ?></span>
                <span class="value" style="font-size:0.85rem;"><?= e($invoice['course_title']) ?></span>
            </div>
            <div class="payment-info-row">
                <span class="label">Amount Paid</span>
                <span class="value" style="color:var(--green-400);"><?= format_price($invoice['amount'], $invoice['currency']) ?></span>
            </div>
            <?php if (!empty($invoice['license_key'])): ?>
                <div class="payment-info-row" style="flex-direction: column; align-items: flex-start; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; width: 100%;">
                        <span class="label">License Key</span>
                        <span class="value" id="license-key-value" style="font-family: monospace; font-weight: bold; color: var(--cyan-400); letter-spacing: 0.5px;"><?= e($invoice['license_key']) ?></span>
                    </div>
                    <div style="display: flex; width: 100%; margin-top: 4px;">
                        <button onclick="copySuccessLicenseKey()" class="btn btn-sm btn-ghost" style="flex: 1; padding: 6px; font-size: 0.75rem; height: auto; margin: 0; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            Copy Key
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            <div class="payment-info-row">
                <span class="label">Status</span>
                <span class="badge badge-completed">Completed</span>
            </div>
        </div>

        <?php if (($invoice['product_type'] ?? 'course') === 'tool'): ?>
            <!-- Download License Key TXT Button -->
            <button onclick="downloadKeyTxt()" class="telegram-btn" style="background: var(--gradient-primary); border: none; width: 100%; justify-content: center; cursor: pointer; display: inline-flex; align-items: center;" id="download-key-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-4px;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Key (.txt)
            </button>
        <?php else: ?>
            <?php if (!empty($invoice['telegram_link'])): ?>
                <!-- Telegram Join Button -->
                <a href="<?= e($invoice['telegram_link']) ?>" target="_blank" class="telegram-btn" id="join-telegram-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    Join Telegram Group
                </a>

                <p class="text-muted mt-2" style="font-size:0.8rem;">
                    ⏱ This link expires in 10 minutes. Click it now to join!
                </p>
            <?php else: ?>
                <div class="alert alert-info" style="text-align:left;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Telegram invite link is being prepared. Please contact support if you don't receive it shortly.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="mt-4">
            <a href="<?= APP_URL ?>" class="btn btn-ghost">
                ← Back to Courses
            </a>
        </div>
    </div>
</div>

<script>
function copySuccessLicenseKey() {
    const keyEl = document.getElementById('license-key-value');
    if (!keyEl) return;
    const keyText = keyEl.textContent ? keyEl.textContent.trim() : keyEl.innerText.trim();
    navigator.clipboard.writeText(keyText).then(() => {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                background: '#0f1d32',
                color: '#f1f5f9'
            });
            Toast.fire({
                icon: 'success',
                title: 'License key copied!'
            });
        } else {
            alert('License key copied to clipboard!');
        }
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}

function downloadKeyTxt() {
    const invoiceNo = "<?= e($invoice['invoice_no']) ?>";
    const productTitle = "<?= e($invoice['course_title']) ?>";
    const licenseKey = "<?= e($invoice['license_key']) ?>";
    const amountPaid = "<?= format_price($invoice['amount'], $invoice['currency']) ?>";
    
    const fileContent = `===================================================
TELEGRAM ADDER PRO - LICENSE KEY
===================================================

Invoice No:  ${invoiceNo}
Product:     ${productTitle}
License Key: ${licenseKey}
Amount Paid: ${amountPaid}
Status:      COMPLETED

Thank you for your purchase!
Please copy the License Key above and paste it into the Telegram Adder Pro app to activate.

Website: https://aicambo.store
`;

    const blob = new Blob([fileContent], { type: 'text/plain;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `License_${invoiceNo}.txt`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
