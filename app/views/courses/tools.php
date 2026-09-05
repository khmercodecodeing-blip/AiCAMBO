<?php
$catalogType = $catalogType ?? 'tool';
$catalogTitle = ['all' => 'catalog.all_title', 'tool' => 'tools.title', 'course' => 'catalog.courses_title'][$catalogType];
$catalogSubtitle = ['all' => 'catalog.all_subtitle', 'tool' => 'tools.subtitle', 'course' => 'catalog.courses_subtitle'][$catalogType];
require APP_ROOT . '/app/views/layouts/header.php';
?>

<section class="courses-section tools-catalog">
    <div class="container">
        <div class="section-header">
            <h2><?= e(t($catalogTitle)) ?></h2>
            <p><?= e(t($catalogSubtitle)) ?></p>
        </div>

            <div class="course-grid">
                <?php if ($catalogType !== 'course'): ?>
                <article class="glass-card course-card telegram-product-card">
                    <a class="card-image" href="<?= APP_URL ?>/telegram-adder-pro" aria-label="Telegram Adder Pro">
                        <img src="<?= APP_URL ?>/PhotoTool/Aderr.PNG" alt="Telegram Adder Pro" width="1631" height="875" fetchpriority="high">
                    </a>
                    <div class="card-body">
                        <h3 class="card-title"><a href="<?= APP_URL ?>/telegram-adder-pro">Telegram Adder Pro</a></h3>
                        <p class="card-desc"><?= e(t('tools.featured_desc')) ?></p>
                        <div class="card-footer">
                            <a href="<?= APP_URL ?>/telegram-adder-pro" class="btn btn-ghost btn-sm"><?= e(t('btn.learn_more')) ?></a>
                            <a href="<?= APP_URL ?>/telegram-adder-pro#pricing" class="btn btn-primary btn-sm"><?= e(t('tools.view_plans')) ?></a>
                        </div>
                    </div>
                </article>
                <?php endif; ?>
                <?php if ($catalogType === 'course' && empty($courses)): ?>
                    <p class="text-muted catalog-empty"><?= e(t('catalog.courses_empty')) ?></p>
                <?php endif; ?>
                <?php foreach ($courses ?? [] as $course): ?>
                    <div class="glass-card course-card">
                        <div class="card-image">
                            <div style="position:absolute;top:12px;left:12px;z-index:2;display:flex;gap:6px;">
                                <span class="catalog-type <?= ($course['type'] ?? 'course') === 'tool' ? 'catalog-type-tool' : 'catalog-type-course' ?>"><?= e(t(($course['type'] ?? 'course') === 'tool' ? 'catalog.tool' : 'catalog.course')) ?></span>

                                <?php
                                $discountPercent = 0;
                                if (!empty($course['original_price']) && (float)$course['original_price'] > (float)$course['price']) {
                                    $discountPercent = round((((float)$course['original_price'] - (float)$course['price']) / (float)$course['original_price']) * 100);
                                }
                                ?>
                                <?php if ($discountPercent > 0): ?>
                                    <span style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;letter-spacing:0.8px;">
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
                                    <?= e(t('btn.learn_more')) ?>
                                </a>
                                <a href="<?= APP_URL ?>/checkout?course_id=<?= $course['id'] ?>"
                                   class="btn btn-primary btn-sm"
                                   data-buy-course="<?= $course['id'] ?>"
                                   data-course-name="<?= e($course['title']) ?>"
                                   data-course-price="<?= format_price($course['price'], $course['currency']) ?>">
                                    <?= e(t('btn.buy_now')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
