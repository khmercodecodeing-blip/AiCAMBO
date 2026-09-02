<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<section class="courses-section">
    <div class="container">
        <div class="section-header">
            <h2>All Tools</h2>
            <p>ឧបករណ៍ឌីជីថលទាំងអស់ដែលអាចទិញបានភ្លាមៗ ទូទាត់ប្រាក់ដោយស្វ័យប្រវត្តិតាមរយៈ KHQR</p>
        </div>

        <!-- Featured: Telegram Adder Pro (Tool Add Telegram) -->
        <a href="<?= APP_URL ?>/telegram-adder-pro" class="glass-card fade-in" style="display:flex;align-items:center;gap:16px;padding:20px 24px;margin-bottom:32px;border:1px solid var(--border-accent);text-decoration:none;">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;">🚀</div>
            <div style="flex:1;">
                <div style="font-weight:700;color:var(--text-primary);font-size:1rem;">Tool Add Telegram — Telegram Adder Pro</div>
                <div style="font-size:0.85rem;color:var(--text-secondary);">មើលគម្រោងតម្លៃ និងទាញយកកម្មវិធីបន្ថែមសមាជិក Telegram</div>
            </div>
            <span class="btn btn-ghost btn-sm" style="flex-shrink:0;">View Plans</span>
        </a>

        <?php if (empty($courses)): ?>
            <div class="text-center" style="padding:60px 0;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1" style="margin:0 auto 16px;">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                <p class="text-muted">No other tools available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="glass-card course-card">
                        <div class="card-image">
                            <div style="position:absolute;top:12px;left:12px;z-index:2;display:flex;gap:6px;">
                                <span style="background:rgba(16,185,129,0.2);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);color:var(--green-400);border:1px solid rgba(16,185,129,0.3);font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Tool</span>

                                <?php
                                $discountPercent = 0;
                                if (!empty($course['original_price']) && (float)$course['original_price'] > (float)$course['price']) {
                                    $discountPercent = round((((float)$course['original_price'] - (float)$course['price']) / (float)$course['original_price']) * 100);
                                }
                                ?>
                                <?php if ($discountPercent > 0): ?>
                                    <span style="background:rgba(239,68,68,0.25);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);color:var(--red-400);border:1px solid rgba(239,68,68,0.4);font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;letter-spacing:0.8px;">
                                        <?= $discountPercent ?>% OFF
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($course['thumbnail'])): ?>
                                <img src="<?= APP_URL ?>/storage/thumbnails/<?= e($course['thumbnail']) ?>"
                                     alt="<?= e($course['title']) ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="placeholder-icon">
                                    <?php
                                    $icons = ['📚', '💻', '🎨', '🚀', '🔐', '📊'];
                                    echo $icons[$course['id'] % count($icons)];
                                    ?>
                                </div>
                            <?php endif; ?>
                            <span class="price-badge">
                                <?php if (!empty($course['original_price']) && (float)$course['original_price'] > (float)$course['price']): ?>
                                    <span style="text-decoration:line-through;color:var(--text-muted);font-weight:400;font-size:0.75rem;margin-right:6px;">
                                        <?= format_price($course['original_price'], $course['currency']) ?>
                                    </span>
                                <?php endif; ?>
                                <?= format_price($course['price'], $course['currency']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= e($course['title']) ?></h3>
                            <p class="card-desc"><?= e($course['description']) ?></p>

                            <div style="font-size:0.85rem;color:var(--text-secondary);display:flex;align-items:center;gap:6px;margin-bottom:16px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--cyan-400);"><path d="M17 21v-2a4 4 0 0 0-3-3H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span><strong><?= (int) ($course['student_count'] ?? 0) ?></strong> Enrolled</span>
                            </div>

                            <div class="card-footer">
                                <a href="<?= APP_URL ?>/course/<?= $course['id'] ?>" class="btn btn-ghost btn-sm">
                                    Learn More
                                </a>
                                <a href="<?= APP_URL ?>/checkout?course_id=<?= $course['id'] ?>"
                                   class="btn btn-primary btn-sm"
                                   data-buy-course="<?= $course['id'] ?>"
                                   data-course-name="<?= e($course['title']) ?>"
                                   data-course-price="<?= format_price($course['price'], $course['currency']) ?>">
                                    Buy Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
