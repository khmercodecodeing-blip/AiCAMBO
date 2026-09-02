<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="checkout-page">
    <div class="checkout-grid">
        <!-- Checkout Form -->
        <div class="glass-card checkout-form-card fade-in">
            <h2 style="margin-bottom:4px;">Checkout</h2>
            <p class="text-secondary mb-3" style="font-size:0.95rem;">Complete your purchase details</p>

            <form method="POST" action="<?= APP_URL ?>/checkout" id="checkout-form">
                <?= csrf_field() ?>
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">

                <div class="form-group">
                    <label class="form-label" for="buyer_name">Full Name *</label>
                    <input type="text" id="buyer_name" name="buyer_name" class="form-control"
                           placeholder="Enter your full name" required
                           minlength="2" maxlength="100">
                </div>

                <div class="form-group">
                    <label class="form-label" for="buyer_phone">Phone Number</label>
                    <input type="tel" id="buyer_phone" name="buyer_phone" class="form-control"
                           placeholder="+855 XX XXX XXXX"
                           pattern="[0-9+\-\s]{6,20}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="buyer_email">Email (Optional)</label>
                    <input type="email" id="buyer_email" name="buyer_email" class="form-control"
                           placeholder="your@email.com">
                </div>

                <div class="form-group" style="display:flex;align-items:flex-start;gap:8px;">
                    <input type="checkbox" id="agree_policy" name="agree_policy" required style="width:16px;height:16px;margin-top:3px;flex-shrink:0;">
                    <label for="agree_policy" style="font-size:0.82rem;color:var(--text-secondary);line-height:1.5;">
                        ខ្ញុំបានអាន និងយល់ព្រមតាម <a href="<?= APP_URL ?>/policy" target="_blank">គោលការណ៍មិនសងប្រាក់វិញ</a> — ទិញរួច
                        <strong>មិនអាចសងប្រាក់វិញបានទេ</strong> (I have read and agree to the <a href="<?= APP_URL ?>/policy" target="_blank">No-Refund Policy</a>. All sales are final).
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block mt-3" id="btn-proceed">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    Proceed to Payment
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="glass-card checkout-summary-card slide-up">
            <h3 style="font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:16px;">Order Summary</h3>

            <?php if (!empty($course['thumbnail'])): ?>
                <img src="<?= APP_URL ?>/storage/thumbnails/<?= e($course['thumbnail']) ?>"
                     alt="<?= e($course['title']) ?>"
                     style="width:100%;height:140px;object-fit:cover;border-radius:var(--radius-md);margin-bottom:16px;">
            <?php endif; ?>

            <div class="course-title"><?= e($course['title']) ?></div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <div class="summary-row">
                    <span class="label">Course Price</span>
                    <span><?= format_price($course['price'], $course['currency']) ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Access</span>
                    <span>Telegram Group</span>
                </div>
                <div class="summary-row" style="border-bottom:none;padding-bottom:0;">
                    <span class="label">Payment Method</span>
                </div>
            </div>

            <div class="payment-method-list">
                <div class="payment-method-option selected" data-payment-method="bakong">
                    <span class="pm-logo pm-logo-bakong">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="21" y2="14"/><line x1="14" y1="21" x2="21" y2="21"/><line x1="17.5" y1="14" x2="17.5" y2="21"/></svg>
                        KHQR
                    </span>
                    <div class="pm-info">
                        <span class="pm-name">Bakong KHQR</span>
                        <span class="text-muted" style="font-size:0.75rem;">Scan &amp; pay instantly via any banking app</span>
                    </div>
                    <span class="badge badge-recommended">Recommended</span>
                </div>
                <div class="payment-method-option disabled" data-payment-method="aba" title="Coming Soon">
                    <span class="pm-logo pm-logo-aba">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 4l6-2 6 2"/></svg>
                        ABA
                    </span>
                    <div class="pm-info">
                        <span class="pm-name">ABA PayWay</span>
                        <span class="text-muted" style="font-size:0.75rem;">Card &amp; ABA account payment</span>
                    </div>
                    <span class="badge badge-coming-soon">Coming Soon</span>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:4px;">
                <div class="summary-row" style="margin-top:8px;padding-top:16px;border-top:1px solid var(--border-color);">
                    <span class="label">Total</span>
                    <span style="font-size:1.3rem;font-weight:800;color:var(--cyan-400);"><?= format_price($course['price'], $course['currency']) ?></span>
                </div>
            </div>

            <div class="mt-3" style="padding:12px;background:var(--bg-glass);border-radius:var(--radius-sm);font-size:0.8rem;color:var(--text-muted);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Secure payment via Bakong KHQR. Your data is protected.
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
