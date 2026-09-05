<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
        <p class="mt-1" style="font-size:0.8rem;"><?= e(t('footer.powered')) ?></p>
        <p class="mt-1" style="font-size:0.8rem;">
            <a href="<?= APP_URL ?>/policy" style="color:var(--text-muted);"><?= e(t('footer.policy')) ?></a>
        </p>
    </div>
</footer>

<!-- Custom Payment Modal HTML -->
<div id="payment-modal" class="custom-modal-overlay">
    <div class="custom-modal-content">
        <!-- Close Button -->
        <button class="custom-modal-close" id="payment-modal-close">&times;</button>
        
        <!-- Modal Views Container -->
        <div id="modal-views-container">
            <!-- Order Summary & Promo Code View (Active by default, initially hidden in HTML) -->
            <div id="modal-summary-view" style="display: none; flex-direction: column; align-items: center; justify-content: center; padding: 12px 10px; text-align: center;">
                <h3 style="margin-bottom: 16px; font-weight: 700; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Order Summary</h3>
                
                <div style="width: 100%; text-align: left; background: var(--bg-glass); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 16px;">
                    <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 12px; color: var(--text-primary);" id="summary-product-title">Product Title</div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 6px;">
                        <span>Original Price:</span>
                        <span id="summary-original-price" style="font-weight: 600;">$0.00</span>
                    </div>
                    
                    <div id="summary-discount-row" style="display: none; justify-content: space-between; font-size: 0.85rem; color: var(--green-400); margin-bottom: 6px;">
                        <span>Discount:</span>
                        <span id="summary-discount-amount" style="font-weight: 600;">-$0.00</span>
                    </div>
                    
                    <div style="height: 1px; background: var(--border-color); margin: 8px 0;"></div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1rem; color: var(--text-primary); font-weight: 700;">
                        <span>Total Price:</span>
                        <span id="summary-total-price" style="color: var(--cyan-400);">$0.00</span>
                    </div>
                </div>

                <!-- Payment Method Selector -->
                <div class="payment-method-list" style="width: 100%;">
                    <div class="payment-method-option selected" data-payment-method="bakong">
                        <span class="pm-logo pm-logo-bakong">
                            <img src="<?= asset('images/payment/bakong.png') ?>" alt="Bakong">
                        </span>
                        <div class="pm-info">
                            <span class="pm-name">Bakong KHQR</span>
                        </div>
                        <span class="badge badge-recommended">Recommended</span>
                    </div>
                    <div class="payment-method-option disabled" data-payment-method="aba" title="Coming Soon">
                        <span class="pm-logo pm-logo-aba">
                            <img src="<?= asset('images/payment/aba.jpg') ?>" alt="ABA">
                        </span>
                        <div class="pm-info">
                            <span class="pm-name">ABA PayWay</span>
                        </div>
                        <span class="badge badge-coming-soon">Coming Soon</span>
                    </div>
                </div>

                <!-- Promo Input -->
                <div style="width: 100%; margin-bottom: 16px;">
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="modal-promo-input" placeholder="កូដបញ្ចុះតម្លៃ (Promo Code)" style="flex: 1; padding: 10px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: var(--radius-md); color: var(--text-primary); font-family: inherit; font-size: 0.85rem; outline: none;" />
                        <button id="modal-promo-apply-btn" class="btn btn-sm btn-ghost" style="padding: 10px 16px; border-radius: var(--radius-md); font-size: 0.85rem; height: auto; margin: 0;">Apply</button>
                    </div>
                    <div id="modal-promo-msg" style="text-align: left; font-size: 0.75rem; margin-top: 6px; display: none;"></div>
                </div>

                <!-- No-Refund Policy Agreement -->
                <div style="width: 100%; display: flex; align-items: flex-start; gap: 8px; margin-bottom: 16px; text-align: left;">
                    <input type="checkbox" id="modal-agree-policy" style="width:16px;height:16px;margin-top:3px;flex-shrink:0;">
                    <label for="modal-agree-policy" style="font-size: 0.78rem; color: var(--text-secondary); line-height:1.5;">
                        ខ្ញុំយល់ព្រមតាម <a href="<?= APP_URL ?>/policy" target="_blank">គោលការណ៍មិនសងប្រាក់វិញ</a> — ទិញរួច<strong>មិនអាចសងប្រាក់វិញបានទេ</strong>
                        (I agree to the <a href="<?= APP_URL ?>/policy" target="_blank">No-Refund Policy</a>. All sales are final).
                    </label>
                </div>

                <button id="modal-proceed-payment-btn" class="custom-modal-btn" style="margin-top: 0;" disabled>Proceed to Pay</button>
            </div>

            <!-- Loader View (Hidden initially) -->
            <div id="modal-loading-view" class="modal-loading-view" style="display: none;">
                <div class="modal-spinner"></div>
                <h3 style="margin-bottom: 8px; font-weight: 700;">Preparing Secure Payment</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem;">Generating your KHQR code. Please wait...</p>
            </div>
            
            <!-- QR Receipt View (Hidden initially) -->
            <div id="modal-receipt-view" style="display: none; text-align: center;">
                <div class="khqr-receipt-card" style="box-shadow: 0 4px 20px rgba(15,23,42,0.12); border: 1px solid var(--border-color); margin-bottom: 12px;">
                    <!-- Red Header -->
                    <div class="khqr-receipt-header">
                        <div class="khqr-logo-text">KHQR</div>
                    </div>
                    
                    <!-- Info Section -->
                    <div class="khqr-receipt-body">
                        <div class="khqr-merchant-name" id="modal-merchant-name"></div>
                        <div class="khqr-amount-row">
                            <span class="khqr-amount-value" id="modal-amount-value"></span>
                            <span class="khqr-currency-code" id="modal-currency-code"></span>
                        </div>
                    </div>
                    
                    <!-- Dashed separator -->
                    <div class="khqr-receipt-separator"></div>
                    
                    <!-- QR Section -->
                    <div class="khqr-receipt-qr-section">
                        <div class="qr-container">
                            <div id="modal-qr-code"></div>
                            <div class="qr-logo" id="modal-qr-logo"></div>
                        </div>
                    </div>
                </div>

                <p style="color: var(--text-secondary); font-size: 0.8rem; margin: 12px 0 8px; line-height: 1.4; padding: 0 10px;">
                    Scan with ABA Mobile, Acleda Mobile, or any Mobile Banking App supporting KHQR
                </p>

                <!-- Countdown -->
                <div class="countdown" style="margin: 8px 0;">
                    <div class="countdown-label" style="font-size:0.75rem; color:var(--text-muted);">Time remaining</div>
                    <div class="countdown-timer" id="modal-countdown-timer" style="font-size: 2rem; font-weight: 800; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">04:00</div>
                </div>

                <!-- Payment Status -->
                <div class="payment-status checking" id="modal-payment-status" style="margin: 10px auto; max-width: 280px; font-size: 0.85rem; padding: 8px;">
                    <div class="pulse-dot"></div>
                    <span>Waiting for payment...</span>
                </div>
            </div>
            
            <!-- Success View (Hidden initially) -->
            <div id="modal-success-view" class="modal-success-view" style="display: none;">
                <div class="modal-success-icon">✓</div>
                <h3 style="margin-bottom: 8px; color: var(--green-400); font-weight: 700;">Payment Successful!</h3>
                <p id="modal-success-desc" style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 16px;">Your payment has been confirmed. Click below to join the private group:</p>
                <a href="#" id="modal-telegram-btn" target="_blank" class="custom-modal-btn telegram-btn">Join Telegram Group</a>
            </div>

            <!-- Error View (Hidden initially) -->
            <div id="modal-error-view" class="modal-error-view" style="display: none;">
                <div class="modal-error-icon">✕</div>
                <h3 style="margin-bottom: 8px; color: var(--red-400); font-weight: 700;">Error</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem;" id="modal-error-text">Payment system error. Please try again.</p>
                <button class="custom-modal-btn" id="modal-error-close-btn">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Chat Widget -->
<div class="chat-widget-container">
    <!-- Chat Card Popup -->
    <div class="chat-widget-card" id="chat-widget-card">
        <button class="chat-widget-close" id="chat-widget-close" aria-label="Close widget">&times;</button>
        <div class="chat-widget-header">
            <div class="chat-widget-avatar">TG</div>
            <div class="chat-widget-header-info">
                <h4 class="chat-widget-title">AICAMBO Support</h4>
                <div class="chat-widget-status">ជាធម្មតាឆ្លើយតបភ្លាមៗ</div>
            </div>
        </div>
        <div class="chat-widget-body">
            <!-- Contact Admin -->
            <a href="https://t.me/NouchSina" target="_blank" class="chat-widget-link">
                <div class="chat-widget-link-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="chat-widget-link-info">
                    <div class="chat-widget-link-title">ទាក់ទងមកកាន់ Admin</div>
                    <div class="chat-widget-link-desc">សំណួរទូទៅ និងការទិញ License</div>
                </div>
            </a>
            
            <!-- Group Telegram -->
            <a href="https://t.me/tooltelegramadder" target="_blank" class="chat-widget-link">
                <div class="chat-widget-link-icon" style="color: #24A1DE; background: rgba(36, 161, 222, 0.1);">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>
                <div class="chat-widget-link-info">
                    <div class="chat-widget-link-title">ក្រុមពិភាក្សា Telegram</div>
                    <div class="chat-widget-link-desc">សួរនាំ និងចែករំលែកបទពិសោធន៍</div>
                </div>
            </a>
            
            <!-- Channel Telegram -->
            <a href="https://t.me/demotelegramadderpro" target="_blank" class="chat-widget-link">
                <div class="chat-widget-link-icon" style="color: #229ED9; background: rgba(34, 158, 217, 0.1);">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.24-5.54 3.65-.52.36-.99.53-1.4.52-.46-.01-1.34-.26-1.99-.47-.8-.26-1.43-.4-1.38-.85.03-.23.35-.47.97-.71 3.8-1.65 6.33-2.74 7.6-3.26 3.62-1.48 4.37-1.74 4.86-1.75.11 0 .35.03.5.15.13.1.17.24.18.37 0 .04.01.12.01.17z"/>
                    </svg>
                </div>
                <div class="chat-widget-link-info">
                    <div class="chat-widget-link-title">ឆានែល Telegram</div>
                    <div class="chat-widget-link-desc">ទទួលបានព័ត៌មានបច្ចុប្បន្នភាពថ្មីៗ</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Floating Circular Button -->
    <div class="chat-widget-btn" id="chat-widget-btn" title="ទាក់ទងមកយើង (Contact Us)">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
    </div>
</div>

<style>
/* Floating Chat Widget CSS */
.chat-widget-container {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 10000;
    font-family: 'Kantumruy Pro', 'Inter', sans-serif;
}

.chat-widget-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.35);
    border: 2px solid #ffffff;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-widget-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 24px rgba(37, 99, 235, 0.5);
}

.chat-widget-btn svg {
    width: 26px;
    height: 26px;
}

.chat-widget-card {
    position: absolute;
    bottom: 72px;
    right: 0;
    width: 310px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    overflow: hidden;
    transform: translateY(20px) scale(0.95);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chat-widget-card.active {
    transform: translateY(0) scale(1);
    opacity: 1;
    pointer-events: auto;
}

.chat-widget-header {
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    padding: 18px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-widget-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    font-weight: 800;
    font-size: 1.1rem;
}

.chat-widget-header-info {
    flex: 1;
}

.chat-widget-title {
    font-size: 0.9rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0;
}

.chat-widget-status {
    font-size: 0.72rem;
    color: #dcfce7;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

.chat-widget-status::before {
    content: "";
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #4ade80;
    animation: chatPulse 1.6s infinite;
}

@keyframes chatPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.4; }
    100% { transform: scale(1); opacity: 1; }
}

.chat-widget-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-widget-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    color: #0f172a;
    text-decoration: none;
    transition: all 0.2s ease;
    text-align: left;
}

.chat-widget-link:hover {
    background: #eff6ff;
    border-color: rgba(37, 99, 235, 0.35);
    color: #1d4ed8;
    transform: translateX(3px);
}

.chat-widget-link-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chat-widget-link-info {
    flex: 1;
}

.chat-widget-link-title {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0;
    font-family: 'Kantumruy Pro', sans-serif;
}

.chat-widget-link-desc {
    font-size: 0.7rem;
    color: #64748b;
    margin-top: 1px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.chat-widget-close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.8);
    font-size: 1.3rem;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    transition: color 0.2s ease;
    z-index: 2;
}

.chat-widget-close:hover {
    color: #ffffff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('chat-widget-btn');
    const card = document.getElementById('chat-widget-card');
    const closeBtn = document.getElementById('chat-widget-close');
    const bottomNavSupport = document.getElementById('bottom-nav-support');

    if (bottomNavSupport && card) {
        bottomNavSupport.addEventListener('click', (e) => {
            e.stopPropagation();
            card.classList.toggle('active');
        });
    }

    if (btn && card) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            card.classList.toggle('active');
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                card.classList.remove('active');
            });
        }
        
        document.addEventListener('click', (e) => {
            const clickedSupport = bottomNavSupport && bottomNavSupport.contains(e.target);
            if (!card.contains(e.target) && e.target !== btn && !btn.contains(e.target) && !clickedSupport) {
                card.classList.remove('active');
            }
        });
    }
});
</script>

<!-- Scripts -->
<script src="<?= asset('js/qrcode.min.js') ?>"></script>
<script>if (typeof QRCode === 'undefined') { document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>'); }</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= asset('js/app.js') ?>?v=2.0.1"></script>

</body>
</html>
