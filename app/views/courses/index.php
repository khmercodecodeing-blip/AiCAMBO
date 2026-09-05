<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>


<!-- Infinite Scroll Banner -->
<div style="text-align: center; margin-top: 30px; font-size: 0.9rem; font-weight: 700; color: var(--blue-400); text-transform: uppercase; letter-spacing: 1.5px; font-family: 'Kantumruy Pro', 'Inter', sans-serif;">
</div>
<div class="scroll-banner-container" style="margin-top: 12px;">
    <div class="scroll-banner-track">
        <!-- Group 1 -->
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="scroll-banner-item" style="gap: 12px; margin: 0 25px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90 60" width="28" height="19" style="border-radius:2px; vertical-align:middle; box-shadow: 0 1px 3px rgba(0,0,0,0.35);">
              <rect width="90" height="60" fill="#032FA1"/>
              <rect y="15" width="90" height="30" fill="#E00025"/>
              <g fill="#FFFFFF" transform="translate(23, 20)">
                <rect x="2" y="16" width="40" height="2"/>
                <rect x="4" y="14" width="36" height="2"/>
                <rect x="6" y="12" width="32" height="2"/>
                <path d="M 9 12 L 12 5 L 15 12 Z"/>
                <path d="M 29 12 L 32 5 L 35 12 Z"/>
                <path d="M 18 12 L 22 1 L 26 12 Z"/>
                <rect x="12" y="9" width="8" height="3"/>
                <rect x="24" y="9" width="8" height="3"/>
              </g>
            </svg>
            <span style="font-family: 'Kantumruy Pro', sans-serif; font-weight: 700; font-size: 1rem; color: var(--text-primary); letter-spacing: 0.5px;">ខ្មែរ ស្រឡាញ់ខ្មែរ ខ្មែររួបរួមខ្មែរខ្លាំង</span>
        </div>
        <?php endfor; ?>
        
        <!-- Group 2 (Duplicate for loop) -->
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="scroll-banner-item" style="gap: 12px; margin: 0 25px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90 60" width="28" height="19" style="border-radius:2px; vertical-align:middle; box-shadow: 0 1px 3px rgba(0,0,0,0.35);">
              <rect width="90" height="60" fill="#032FA1"/>
              <rect y="15" width="90" height="30" fill="#E00025"/>
              <g fill="#FFFFFF" transform="translate(23, 20)">
                <rect x="2" y="16" width="40" height="2"/>
                <rect x="4" y="14" width="36" height="2"/>
                <rect x="6" y="12" width="32" height="2"/>
                <path d="M 9 12 L 12 5 L 15 12 Z"/>
                <path d="M 29 12 L 32 5 L 35 12 Z"/>
                <path d="M 18 12 L 22 1 L 26 12 Z"/>
                <rect x="12" y="9" width="8" height="3"/>
                <rect x="24" y="9" width="8" height="3"/>
              </g>
            </svg>
            <span style="font-family: 'Kantumruy Pro', sans-serif; font-weight: 700; font-size: 1rem; color: var(--text-primary); letter-spacing: 0.5px;">ខ្មែរ ស្រឡាញ់ខ្មែរ ខ្មែររួបរួមខ្មែរខ្លាំង</span>
        </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Courses Section -->
<section class="courses-section">
    <div class="container">
        <div class="section-header">
            <h2><?= e(t('home.title')) ?></h2>
            <p><?= e(t('home.subtitle')) ?></p>
        </div>


        <?php if (empty($courses)): ?>
            <div class="text-center" style="padding:60px 0;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1" style="margin:0 auto 16px;">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                <p class="text-muted">No courses available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="glass-card course-card">
                        <div class="card-image">
                            <!-- Badge container -->
                            <div style="position:absolute;top:12px;left:12px;z-index:2;display:flex;gap:6px;">
                                <?php if (($course['type'] ?? 'course') === 'tool'): ?>
                                    <span style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Tool</span>
                                <?php else: ?>
                                    <span style="background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Course</span>
                                <?php endif; ?>

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
        <?php endif; ?>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
