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

                            <div style="font-size:0.85rem;display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                                <?php if (!empty($course['is_qv']) || isset($course['stock_qty']) || !empty($course['unlimited_stock'])): ?>
                                    <?php if (!empty($course['unlimited_stock'])): ?>
                                        <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;font-size:0.8rem;background:rgba(16,185,129,0.12);padding:2px 8px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                            <?= e(t('stock.unlimited')) ?>
                                        </span>
                                    <?php elseif (($course['stock_qty'] ?? null) !== null && (int) $course['stock_qty'] > 0): ?>
                                        <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;font-size:0.8rem;background:rgba(16,185,129,0.12);padding:2px 8px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                            <?= e(t('stock.available')) ?>: <strong><?= number_format((int) $course['stock_qty']) ?></strong>
                                        </span>
                                    <?php elseif (($course['stock_qty'] ?? null) === 0 || (isset($course['in_stock']) && !$course['in_stock'])): ?>
                                        <span style="display:inline-flex;align-items:center;gap:5px;color:#ef4444;font-weight:700;font-size:0.8rem;background:rgba(239,68,68,0.12);padding:2px 8px;border-radius:6px;border:1px solid rgba(239,68,68,0.25);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                            <?= e(t('stock.out_of_stock')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="display:inline-flex;align-items:center;gap:5px;color:#10b981;font-weight:700;font-size:0.8rem;background:rgba(16,185,129,0.12);padding:2px 8px;border-radius:6px;border:1px solid rgba(16,185,129,0.25);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <?= e(t('stock.in_stock')) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <span style="color:var(--text-secondary);display:inline-flex;align-items:center;gap:4px;font-size:0.8rem;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--cyan-400);"><path d="M17 21v-2a4 4 0 0 0-3-3H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <strong><?= (int) ($course['student_count'] ?? 0) ?></strong> <?= ($course['type'] ?? '') === 'tool' ? 'Sold' : 'Enrolled' ?>
                                </span>
                            </div>

                            <div class="card-footer">
                                <a href="<?= APP_URL ?>/course/<?= $course['id'] ?>" class="btn btn-ghost btn-sm">
                                    <?= e(t('btn.learn_more')) ?>
                                </a>
                                <?php if (isset($course['in_stock']) && !$course['in_stock']): ?>
                                    <button class="btn btn-sm" style="background:#475569;color:#94a3b8;cursor:not-allowed;" disabled>
                                        <?= e(t('stock.out_of_stock')) ?>
                                    </button>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/checkout?course_id=<?= $course['id'] ?>"
                                       class="btn btn-primary btn-sm"
                                       data-buy-course="<?= $course['id'] ?>"
                                       data-course-name="<?= e($course['title']) ?>"
                                       data-course-price="<?= format_price($course['price'], $course['currency']) ?>">
                                        <?= e(t('btn.buy_now')) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
