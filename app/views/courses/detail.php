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

                    <div style="font-size:0.85rem;color:var(--text-secondary);display:flex;align-items:center;justify-content:center;gap:6px;margin:16px 0;padding:8px;background:rgba(255,255,255,0.03);border-radius:var(--radius-sm);border:1px solid var(--border-color);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--cyan-400);"><path d="M17 21v-2a4 4 0 0 0-3-3H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span><strong><?= (int) ($course['student_count'] ?? 0) ?></strong> Enrolled</span>
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

                    <a href="<?= APP_URL ?>/checkout?course_id=<?= $course['id'] ?>"
                       class="btn btn-primary btn-lg btn-block"
                       data-buy-course="<?= $course['id'] ?>"
                       data-course-name="<?= e($course['title']) ?>"
                       data-course-price="<?= format_price($course['price'], $course['currency']) ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Buy Now
                    </a>

                    <p class="text-center text-muted mt-2" style="font-size:0.8rem;">
                        Secure payment via Bakong KHQR
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
