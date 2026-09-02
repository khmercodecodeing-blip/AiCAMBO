/**
 * CourseHub — Payment Page JavaScript
 * QR code rendering, countdown timer, and AJAX payment status polling
 */

document.addEventListener('DOMContentLoaded', () => {
    initQRCode();
    initCountdown();
    initPaymentPolling();
});

/**
 * Render QR code from the QR string data attribute
 */
function initQRCode() {
    const container = document.getElementById('qr-code');
    if (!container) return;

    const qrString = container.dataset.qrString;
    if (!qrString) return;

    // Use QRCode.js library
    if (typeof QRCode !== 'undefined') {
        new QRCode(container, {
            text: qrString,
            width: 180,
            height: 180,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
    } else {
        container.innerHTML = '<p style="color:#94a3b8;padding:20px;">QR Code library not loaded</p>';
    }
}

/**
 * Countdown timer
 */
function initCountdown() {
    const timerEl = document.getElementById('countdown-timer');
    if (!timerEl) return;

    let remaining = parseInt(timerEl.dataset.remaining, 10);
    if (isNaN(remaining) || remaining <= 0) {
        timerEl.textContent = '00:00';
        timerEl.classList.add('expired');
        return;
    }

    function updateTimer() {
        if (remaining <= 0) {
            timerEl.textContent = '00:00';
            timerEl.classList.add('expired');
            handleExpired();
            return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        // Warning color under 2 minutes
        if (remaining <= 120) {
            timerEl.style.background = 'linear-gradient(135deg, #f59e0b, #ef4444)';
            timerEl.style.webkitBackgroundClip = 'text';
            timerEl.style.webkitTextFillColor = 'transparent';
        }

        remaining--;
    }

    updateTimer();
    window._countdownInterval = setInterval(updateTimer, 1000);
}

/**
 * Handle expired payment
 */
function handleExpired() {
    if (window._countdownInterval) {
        clearInterval(window._countdownInterval);
    }
    if (window._pollingInterval) {
        clearInterval(window._pollingInterval);
    }

    const statusEl = document.getElementById('payment-status');
    if (statusEl) {
        statusEl.className = 'payment-status expired';
        statusEl.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            Payment time expired
        `;
    }

    // Show SweetAlert
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Payment Expired',
            text: 'The payment time has expired. Please try again.',
            icon: 'error',
            confirmButtonText: 'Back to Courses',
            confirmButtonColor: '#3b82f6',
            background: '#0f1d32',
            color: '#f1f5f9',
            backdrop: 'rgba(0,0,0,0.7)',
        }).then(() => {
            window.location.href = window.APP_URL || '/web';
        });
    }
}

/**
 * AJAX polling for payment status
 */
function initPaymentPolling() {
    const statusEl = document.getElementById('payment-status');
    const invoiceNo = statusEl?.dataset.invoiceNo;
    const checkUrl = statusEl?.dataset.checkUrl;

    if (!invoiceNo || !checkUrl) return;

    let isChecking = false;

    async function checkPayment() {
        if (isChecking) return;
        isChecking = true;

        try {
            const response = await fetch(checkUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                isChecking = false;
                return;
            }

            const data = await response.json();

            if (data.status === 'completed') {
                handlePaymentSuccess(data);
                return;
            }

            if (data.status === 'expired') {
                handleExpired();
                return;
            }

            // Update remaining time from server
            if (data.remaining_secs !== undefined) {
                const timerEl = document.getElementById('countdown-timer');
                if (timerEl) {
                    const current = parseInt(timerEl.dataset.remaining, 10);
                    // Sync with server time if significantly different
                    if (Math.abs(current - data.remaining_secs) > 5) {
                        timerEl.dataset.remaining = data.remaining_secs;
                    }
                }
            }

        } catch (error) {
            console.error('Payment check error:', error);
        }

        isChecking = false;
    }

    // Poll every 3 seconds
    window._pollingInterval = setInterval(checkPayment, 3000);

    // Also check immediately
    setTimeout(checkPayment, 1000);
}

/**
 * Handle successful payment
 */
function handlePaymentSuccess(data) {
    // Stop polling and countdown
    if (window._pollingInterval) clearInterval(window._pollingInterval);
    if (window._countdownInterval) clearInterval(window._countdownInterval);

    // Update status display
    const statusEl = document.getElementById('payment-status');
    if (statusEl) {
        statusEl.className = 'payment-status success';
        statusEl.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            Payment Confirmed!
        `;
    }

    // Show success with SweetAlert
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '🎉 Payment Successful!',
            html: `
                <div style="color:#94a3b8;">
                    <p style="margin-bottom:16px;">Your payment has been confirmed.</p>
                    ${data.telegram_link ? `
                        <p style="font-size:0.9rem;">Click below to join the course group:</p>
                    ` : '<p style="font-size:0.9rem;">Preparing your access link...</p>'}
                </div>
            `,
            icon: 'success',
            confirmButtonText: data.telegram_link ? '🚀 Join Telegram Group' : 'Continue',
            confirmButtonColor: '#0088cc',
            background: '#0f1d32',
            color: '#f1f5f9',
            backdrop: 'rgba(0,0,0,0.7)',
            allowOutsideClick: false,
        }).then(() => {
            if (data.telegram_link) {
                window.open(data.telegram_link, '_blank');
            }
            // Redirect to success page
            const successUrl = (window.APP_URL || '/web') + '/payment/success/' + data.invoice_no;
            window.location.href = successUrl;
        });
    } else {
        // Fallback without SweetAlert
        const successUrl = (window.APP_URL || '/web') + '/payment/success/' + data.invoice_no;
        window.location.href = successUrl;
    }
}
