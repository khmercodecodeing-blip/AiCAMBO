<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<section class="course-detail">
    <div class="container">
        <div class="course-detail-grid">
            <!-- Left: Course Info -->
            <div class="fade-in">
                <?php if (!empty($course['video_url'])): ?>
                    <?= get_video_player_html($course['video_url']) ?>
                <?php else: ?>
                    <div class="course-image">
                        <?php if (!empty($course['thumbnail'])): ?>
                            <img src="<?= APP_URL ?>/storage/thumbnails/<?= e($course['thumbnail']) ?>"
                                 alt="<?= e($course['title']) ?>">
                        <?php else: ?>
                            <div class="placeholder-icon">
                                <?php
                                $icons = ['📚', '💻', '🎨', '🚀', '🔐', '📊'];
                                echo $icons[$course['id'] % count($icons)];
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h1><?= e($course['title']) ?></h1>

                <div class="description">
                    <?= nl2br(e($course['description'])) ?>
                </div>
            </div>

            <!-- Right: Purchase Card -->
            <div class="slide-up">
                <div class="glass-card purchase-card">
                    <div class="price-container" style="display:flex;align-items:baseline;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
                        <?php if (!empty($course['original_price']) && (float)$course['original_price'] > (float)$course['price']): ?>
                            <span style="text-decoration:line-through;color:var(--text-muted);font-weight:600;font-size:1.3rem;">
                                <?= format_price($course['original_price'], $course['currency']) ?>
                            </span>
                        <?php endif; ?>
                        <div class="price" style="margin-bottom:0;line-height:1;"><?= format_price($course['price'], $course['currency']) ?></div>
                        
                        <?php
                        $discountPercent = 0;
                        if (!empty($course['original_price']) && (float)$course['original_price'] > (float)$course['price']) {
                            $discountPercent = round((((float)$course['original_price'] - (float)$course['price']) / (float)$course['original_price']) * 100);
                        }
                        ?>
                        <?php if ($discountPercent > 0): ?>
                            <span style="background:rgba(239,68,68,0.15);color:var(--red-400);border:1px solid rgba(239,68,68,0.25);font-size:0.75rem;padding:2px 8px;border-radius:20px;font-weight:800;letter-spacing:0.5px;">
                                <?= $discountPercent ?>% OFF
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="price-label">One-time payment</div>

                    <div style="font-size:0.85rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:center;gap:12px;margin:16px 0;padding:10px;background:var(--bg-glass);border-radius:var(--radius-sm);border:1px solid var(--border-color);flex-wrap:wrap;">
                        <?php if (!empty($course['is_qv']) || isset($course['stock_qty']) || !empty($course['unlimited_stock'])): ?>
                            <?php if (!empty($course['unlimited_stock'])): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;background:rgba(16,185,129,0.12);padding:3px 10px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    <?= e(t('stock.unlimited')) ?>
                                </span>
                            <?php elseif (($course['stock_qty'] ?? null) !== null && (int) $course['stock_qty'] > 0): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;background:rgba(16,185,129,0.12);padding:3px 10px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                    <?= e(t('stock.available')) ?>: <strong><?= number_format((int) $course['stock_qty']) ?></strong>
                                </span>
                            <?php elseif (($course['stock_qty'] ?? null) === 0 || (isset($course['in_stock']) && !$course['in_stock'])): ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;color:#ef4444;font-weight:700;background:rgba(239,68,68,0.12);padding:3px 10px;border-radius:6px;border:1px solid rgba(239,68,68,0.25);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    <?= e(t('stock.out_of_stock')) ?>
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;background:rgba(16,185,129,0.12);padding:3px 10px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <?= e(t('stock.in_stock')) ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <span style="display:inline-flex;align-items:center;gap:5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--cyan-400);"><path d="M17 21v-2a4 4 0 0 0-3-3H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span><strong><?= (int) ($course['student_count'] ?? 0) ?></strong> <?= ($course['type'] ?? '') === 'tool' ? 'Sold' : 'Enrolled' ?></span>
                        </span>
                    </div>

                    <ul class="features">
                        <?php if (($course['type'] ?? 'course') === 'tool'): ?>
                            <li>Instant file download link</li>
                            <li>Lifetime updates of the tool</li>
                            <li>All related documentation included</li>
                            <li>Direct developer support</li>
                            <li>Instant access after payment</li>
                        <?php else: ?>
                            <li>Lifetime access to course group</li>
                            <li>Private Telegram community</li>
                            <li>All course materials included</li>
                            <li>Direct instructor support</li>
                            <li>Instant access after payment</li>
                        <?php endif; ?>
                    </ul>

                    <?php if (isset($course['in_stock']) && !$course['in_stock']): ?>
                        <button class="btn btn-lg btn-block" style="background:#475569;color:#94a3b8;cursor:not-allowed;" disabled>
                            <?= e(t('stock.out_of_stock')) ?>
                        </button>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/checkout?course_id=<?= $course['id'] ?>"
                           class="btn btn-primary btn-lg btn-block"
                           data-buy-course="<?= $course['id'] ?>"
                           data-course-name="<?= e($course['title']) ?>"
                           data-course-price="<?= format_price($course['price'], $course['currency']) ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <?= e(t('btn.buy_now')) ?>
                        </a>
                    <?php endif; ?>

                    <p class="text-center text-muted mt-2" style="font-size:0.8rem;">
                        Secure payment via Bakong KHQR
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
