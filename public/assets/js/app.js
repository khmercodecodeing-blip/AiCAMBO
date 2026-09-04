/**
 * CourseHub — Main Application JavaScript
 * Handles navigation, course interactions, SweetAlert2, and CSRF
 */

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initAdminSidebarMobile();
    initBuyButtons();
    initPaymentMethodSelector();
    initDeleteConfirmations();
    initFlashMessages();
    initServiceWorker();
    initProductSearch();
});

/**
 * Smart product search — works on any page with #course-search-input + .course-grid
 * (home catalog, /tools). Matches every typed word against title/description/type,
 * in any order, so "adder telegram" and "telegram adder" both find the same product.
 */
function initProductSearch() {
    const input = document.getElementById('course-search-input');
    const clearBtn = document.getElementById('search-clear-btn');
    const grid = document.querySelector('.course-grid');
    if (!input || !grid) return;

    const cards = Array.from(grid.querySelectorAll('.course-card')).map((card) => ({
        el: card,
        text: [
            card.querySelector('.card-title')?.textContent || '',
            card.querySelector('.card-desc')?.textContent || '',
            card.querySelector('.card-image span')?.textContent || '',
        ].join(' ').toLowerCase(),
    }));

    let noResultsEl = document.getElementById('no-search-results');
    if (!noResultsEl) {
        noResultsEl = document.createElement('div');
        noResultsEl.id = 'no-search-results';
        noResultsEl.className = 'text-center';
        noResultsEl.style.cssText = 'display: none; width: 100%; padding: 60px 0; grid-column: 1 / -1;';
        noResultsEl.innerHTML = `
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.5" style="margin: 0 auto 16px;">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="8" y1="11" x2="14" y2="11" stroke-width="2"/>
            </svg>
            <p class="text-muted" style="font-family: 'Kantumruy Pro', sans-serif;">រកមិនឃើញផលិតផលដែលអ្នកស្វែងរកទេ (No products found)</p>
        `;
        grid.appendChild(noResultsEl);
    }

    function runSearch() {
        const query = input.value.toLowerCase().trim().replace(/\s+/g, ' ');
        if (clearBtn) clearBtn.style.display = query.length > 0 ? 'flex' : 'none';

        if (!query) {
            cards.forEach((c) => { c.el.style.display = 'flex'; });
            noResultsEl.style.display = 'none';
            return;
        }

        const tokens = query.split(' ').filter(Boolean);
        let visibleCount = 0;

        cards.forEach((c) => {
            const matchesAll = tokens.every((t) => c.text.includes(t));
            c.el.style.display = matchesAll ? 'flex' : 'none';
            if (matchesAll) visibleCount++;
        });

        noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    input.addEventListener('input', runSearch);
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            input.value = '';
            runSearch();
            input.focus();
        });
    }
}

/**
 * Register the service worker so the site can be installed / used like an app on mobile
 */
function initServiceWorker() {
    if (!('serviceWorker' in navigator) || !window.APP_URL) return;
    window.addEventListener('load', () => {
        navigator.serviceWorker.register(window.APP_URL + '/sw.js').catch(() => {});
    });
}

/**
 * Mobile menu toggle
 */
function initMobileMenu() {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.navbar-links');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
            toggle.classList.toggle('active');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
                toggle.classList.remove('active');
            }
        });
    }
}

/**
 * Admin mobile sidebar toggle
 */
function initAdminSidebarMobile() {
    const toggle = document.getElementById('admin-sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');

    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            toggle.classList.toggle('active');
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!toggle.contains(e.target) && !sidebar.contains(e.target)) {
                sidebar.classList.remove('open');
                toggle.classList.remove('active');
            }
        });
    }
}

/**
 * Buy Now button confirmations (Quick Checkout via Custom Modal)
 */
function initBuyButtons() {
    const modal = document.getElementById('payment-modal');
    if (!modal) return;

    const closeBtn = document.getElementById('payment-modal-close');
    const errCloseBtn = document.getElementById('modal-error-close-btn');

    // DOM Views
    const summaryView = document.getElementById('modal-summary-view');
    const loadingView = document.getElementById('modal-loading-view');
    const receiptView = document.getElementById('modal-receipt-view');
    const successView = document.getElementById('modal-success-view');
    const errorView = document.getElementById('modal-error-view');

    // Modal data fields
    const merchantNameEl = document.getElementById('modal-merchant-name');
    const amountValueEl = document.getElementById('modal-amount-value');
    const currencyCodeEl = document.getElementById('modal-currency-code');
    const qrContainer = document.getElementById('modal-qr-code');
    const qrLogoEl = document.getElementById('modal-qr-logo');
    const countdownTimerEl = document.getElementById('modal-countdown-timer');
    const telegramBtn = document.getElementById('modal-telegram-btn');
    const errorTextEl = document.getElementById('modal-error-text');

    // Summary fields
    const summaryProductTitle = document.getElementById('summary-product-title');
    const summaryOriginalPrice = document.getElementById('summary-original-price');
    const summaryDiscountRow = document.getElementById('summary-discount-row');
    const summaryDiscountAmount = document.getElementById('summary-discount-amount');
    const summaryTotalPrice = document.getElementById('summary-total-price');
    const promoInput = document.getElementById('modal-promo-input');
    const promoApplyBtn = document.getElementById('modal-promo-apply-btn');
    const promoMsg = document.getElementById('modal-promo-msg');
    const proceedPaymentBtn = document.getElementById('modal-proceed-payment-btn');
    const agreePolicyCheckbox = document.getElementById('modal-agree-policy');

    if (agreePolicyCheckbox && proceedPaymentBtn) {
        agreePolicyCheckbox.addEventListener('change', () => {
            proceedPaymentBtn.disabled = !agreePolicyCheckbox.checked;
        });
    }

    let countdownInterval;
    let pollingInterval;
    let activeCourseId = 0;
    let activePromoCode = '';

    function showView(view) {
        if (summaryView) summaryView.style.display = view === 'summary' ? 'flex' : 'none';
        loadingView.style.display = view === 'loading' ? 'flex' : 'none';
        receiptView.style.display = view === 'receipt' ? 'block' : 'none';
        successView.style.display = view === 'success' ? 'flex' : 'none';
        errorView.style.display = view === 'error' ? 'flex' : 'none';
    }

    function closeModal() {
        modal.classList.remove('open');
        setTimeout(() => {
            modal.style.display = 'none';
            // Clear intervals
            clearInterval(countdownInterval);
            clearInterval(pollingInterval);
            // Reset views
            showView('summary');
            qrContainer.innerHTML = '';
            // Reset promo inputs
            if (promoInput) promoInput.value = '';
            if (promoMsg) {
                promoMsg.style.display = 'none';
                promoMsg.textContent = '';
            }
            if (summaryDiscountRow) summaryDiscountRow.style.display = 'none';
            if (agreePolicyCheckbox) agreePolicyCheckbox.checked = false;
            if (proceedPaymentBtn) proceedPaymentBtn.disabled = true;
            activeCourseId = 0;
            activePromoCode = '';
        }, 300);
    }

    // Close button events
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (errCloseBtn) errCloseBtn.addEventListener('click', closeModal);

    document.querySelectorAll('[data-buy-course]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const courseId = parseInt(btn.dataset.buyCourse);
            activeCourseId = courseId;
            activePromoCode = '';

            // Reset summary fields
            if (summaryProductTitle) summaryProductTitle.textContent = btn.dataset.courseName;
            if (summaryOriginalPrice) summaryOriginalPrice.textContent = btn.dataset.coursePrice;
            if (summaryTotalPrice) summaryTotalPrice.textContent = btn.dataset.coursePrice;
            if (summaryDiscountRow) summaryDiscountRow.style.display = 'none';
            if (promoInput) promoInput.value = '';
            if (promoMsg) {
                promoMsg.style.display = 'none';
                promoMsg.textContent = '';
            }
            if (agreePolicyCheckbox) agreePolicyCheckbox.checked = false;
            if (proceedPaymentBtn) proceedPaymentBtn.disabled = true;

            // 1. Open Modal & Show Summary
            modal.style.display = 'flex';
            modal.offsetHeight; // Force reflow
            modal.classList.add('open');
            showView('summary');
        });
    });

    if (promoApplyBtn) {
        promoApplyBtn.addEventListener('click', async () => {
            const promoVal = promoInput.value.trim();
            if (!promoVal || !activeCourseId) return;

            promoApplyBtn.disabled = true;
            promoApplyBtn.textContent = 'Applying...';
            if (promoMsg) promoMsg.style.display = 'none';

            try {
                const response = await fetch((window.APP_URL || '/web') + '/api/check-promo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ course_id: activeCourseId, promo_code: promoVal })
                });

                if (!response.ok) throw new Error('Network error');
                const data = await response.json();

                if (data.status === 'success') {
                    if (summaryDiscountAmount) {
                        const isKHR = summaryOriginalPrice.textContent.includes('៛');
                        summaryDiscountAmount.textContent = isKHR 
                            ? '-' + Math.round(data.discount_amount).toLocaleString() + ' ៛'
                            : '-$' + parseFloat(data.discount_amount).toFixed(2);
                    }
                    if (summaryTotalPrice) {
                        const isKHR = summaryOriginalPrice.textContent.includes('៛');
                        summaryTotalPrice.textContent = isKHR
                            ? Math.round(data.final_price).toLocaleString() + ' ៛'
                            : '$' + parseFloat(data.final_price).toFixed(2);
                    }
                    if (summaryDiscountRow) summaryDiscountRow.style.display = 'flex';
                    
                    if (promoMsg) {
                        promoMsg.textContent = data.message;
                        promoMsg.style.color = '#34d399';
                        promoMsg.style.display = 'block';
                    }
                    activePromoCode = promoVal;
                } else {
                    if (summaryDiscountRow) summaryDiscountRow.style.display = 'none';
                    if (summaryTotalPrice) summaryTotalPrice.textContent = summaryOriginalPrice.textContent;
                    if (promoMsg) {
                        promoMsg.textContent = data.message || 'កូដមិនត្រឹមត្រូវទេ (Invalid code)';
                        promoMsg.style.color = '#f87171';
                        promoMsg.style.display = 'block';
                    }
                    activePromoCode = '';
                }
            } catch (err) {
                console.error(err);
                if (promoMsg) {
                    promoMsg.textContent = 'មានបញ្ហាក្នុងការភ្ជាប់ទៅកាន់ម៉ាស៊ីនបម្រើ (Connection error)';
                    promoMsg.style.color = '#f87171';
                    promoMsg.style.display = 'block';
                }
                activePromoCode = '';
            } finally {
                promoApplyBtn.disabled = false;
                promoApplyBtn.textContent = 'Apply';
            }
        });
    }

    if (proceedPaymentBtn) {
        proceedPaymentBtn.addEventListener('click', async () => {
            if (!activeCourseId) return;
            if (agreePolicyCheckbox && !agreePolicyCheckbox.checked) return;

            showView('loading');

            try {
                const response = await fetch((window.APP_URL || '/web') + '/api/quick-checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ course_id: activeCourseId, promo_code: activePromoCode, agree_policy: true })
                });

                if (!response.ok) throw new Error('Network response not ok');
                const data = await response.json();

                if (data.status !== 'success') {
                    errorTextEl.textContent = data.message || 'Payment system error. Please try again.';
                    showView('error');
                    return;
                }

                // Render Receipt view
                if (merchantNameEl) merchantNameEl.textContent = data.merchant_name;
                if (amountValueEl) {
                    amountValueEl.textContent = parseFloat(data.amount).toLocaleString(undefined, {
                        minimumFractionDigits: data.currency === 'KHR' ? 0 : 2
                    });
                }
                if (currencyCodeEl) currencyCodeEl.textContent = data.currency;
                if (qrLogoEl) qrLogoEl.textContent = data.currency === 'KHR' ? '៛' : '$';

                // Clear container before rendering
                if (qrContainer) {
                    qrContainer.innerHTML = '';
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(qrContainer, {
                            text: data.qr_string,
                            width: 160,
                            height: 160,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } else {
                        qrContainer.innerHTML = '<p style="color:#f87171;padding:15px;font-size:0.8rem;">Unable to load QR renderer library. Please reload page.</p>';
                    }
                }

                showView('receipt');

                // Start Countdown
                let remaining = data.remaining_secs;
                function updateCountdown() {
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                        clearInterval(pollingInterval);
                        if (errorTextEl) errorTextEl.textContent = 'The payment time has expired. Please try again.';
                        showView('error');
                        return;
                    }
                    const mins = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    if (countdownTimerEl) {
                        countdownTimerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                        
                        if (remaining <= 120) {
                            countdownTimerEl.style.background = 'linear-gradient(135deg, #f59e0b, #ef4444)';
                            countdownTimerEl.style.webkitBackgroundClip = 'text';
                            countdownTimerEl.style.webkitTextFillColor = 'transparent';
                        } else {
                            countdownTimerEl.style.background = 'var(--gradient-primary)';
                            countdownTimerEl.style.webkitBackgroundClip = 'text';
                            countdownTimerEl.style.webkitTextFillColor = 'transparent';
                        }
                    }
                    remaining--;
                }
                updateCountdown();
                clearInterval(countdownInterval);
                countdownInterval = setInterval(updateCountdown, 1000);

                // Start Polling
                let isChecking = false;
                clearInterval(pollingInterval);
                pollingInterval = setInterval(async () => {
                    if (isChecking) return;
                    isChecking = true;

                    try {
                        const checkRes = await fetch(data.check_url, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const pollData = await checkRes.json();

                        if (pollData.status === 'completed') {
                            clearInterval(pollingInterval);
                            clearInterval(countdownInterval);

                            const successDescEl = document.getElementById('modal-success-desc');
                            if (pollData.product_type === 'tool') {
                                if (successDescEl) {
                                    successDescEl.textContent = 'Your payment has been confirmed. Click below to download your tool:';
                                }
                                if (telegramBtn) {
                                    telegramBtn.textContent = 'Download Tool';
                                    telegramBtn.href = pollData.download_link;
                                    telegramBtn.className = 'custom-modal-btn tool-btn';
                                    telegramBtn.style.display = 'inline-block';
                                }
                                showView('success');

                                if (pollData.download_link) {
                                    setTimeout(() => {
                                        window.open(pollData.download_link, '_blank');
                                    }, 1000);
                                }
                            } else {
                                if (successDescEl) {
                                    successDescEl.textContent = 'Your payment has been confirmed. Click below to join the private group:';
                                }
                                if (telegramBtn) {
                                    telegramBtn.textContent = 'Join Telegram Group';
                                    telegramBtn.href = pollData.telegram_link;
                                    telegramBtn.className = 'custom-modal-btn telegram-btn';
                                    telegramBtn.style.display = 'inline-block';
                                }
                                showView('success');

                                if (pollData.telegram_link) {
                                    setTimeout(() => {
                                        window.open(pollData.telegram_link, '_blank');
                                    }, 1000);
                                }
                            }
                        }
                    } catch (err) {
                        console.error('Poll error:', err);
                    }
                    isChecking = false;
                }, 3000);

            } catch (err) {
                console.error(err);
                if (errorTextEl) errorTextEl.textContent = 'Unable to connect to the server. Please try again.';
                showView('error');
            }
        });
    }
}

/**
 * Payment method selector (checkout page + quick-checkout modal)
 * Only Bakong KHQR is functional; ABA PayWay is shown as a selectable-looking
 * option but is disabled until the real integration is ready.
 */
function initPaymentMethodSelector() {
    document.querySelectorAll('.payment-method-option').forEach(option => {
        option.addEventListener('click', () => {
            if (option.classList.contains('disabled')) {
                const message = 'ABA PayWay is coming soon. Please use Bakong KHQR for now.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Coming Soon',
                        text: message,
                        background: '#ffffff',
                        color: '#0f172a'
                    });
                } else {
                    alert(message);
                }
                return;
            }

            const list = option.closest('.payment-method-list');
            if (list) {
                list.querySelectorAll('.payment-method-option').forEach(el => el.classList.remove('selected'));
            }
            option.classList.add('selected');
        });
    });
}

/**
 * Delete confirmation dialogs (admin)
 */
function initDeleteConfirmations() {
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const name = btn.dataset.confirmDelete || 'this item';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Confirmation',
                    html: `<p style="color:#475569;">Are you sure you want to delete <strong style="color:#dc2626;">${name}</strong>? This action cannot be undone.</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    background: '#ffffff',
                    color: '#0f172a',
                    backdrop: 'rgba(15,23,42,0.5)',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = btn.getAttribute('href');
                    }
                });
            } else if (confirm(`Delete ${name}?`)) {
                window.location.href = btn.getAttribute('href');
            }
        });
    });
}

/**
 * Auto-dismiss flash messages
 */
function initFlashMessages() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'all 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
}
