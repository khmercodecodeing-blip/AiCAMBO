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
                                    <span style="background:rgba(16,185,129,0.2);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);color:var(--green-400);border:1px solid rgba(16,185,129,0.3);font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Tool</span>
                                <?php else: ?>
                                    <span style="background:rgba(59,130,246,0.2);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);color:var(--blue-400);border:1px solid rgba(59,130,246,0.3);font-size:0.65rem;padding:3px 8px;border-radius:20px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Course</span>
                                <?php endif; ?>

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
