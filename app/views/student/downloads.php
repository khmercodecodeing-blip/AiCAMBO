<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<section class="downloads-dashboard" style="padding:60px 0 80px; min-height:calc(100vh - 140px);">
    <div class="container">
        
        <!-- User Profile Card -->
        <div class="glass-card fade-in" style="display:flex; align-items:center; gap:24px; padding:30px 40px; margin-bottom:40px; border-radius:var(--radius-lg); flex-wrap:wrap;">
            <div style="position:relative;">
                <?php if (!empty($_SESSION['user_picture'])): ?>
                    <img src="<?= e($_SESSION['user_picture']) ?>" alt="<?= e($_SESSION['user_name']) ?>" style="width:90px; height:90px; border-radius:50%; border:3px solid var(--border-accent); box-shadow:var(--shadow-glow);">
                <?php else: ?>
                    <div style="width:90px; height:90px; border-radius:50%; background:var(--gradient-primary); display:flex; align-items:center; justify-content:center; font-size:2.2rem; font-weight:bold; color:#fff; border:3px solid var(--border-accent); box-shadow:var(--shadow-glow);">
                        <?= strtoupper(substr(e($_SESSION['user_name']), 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="flex:1; min-width:200px;">
                <h1 style="font-size:1.8rem; font-weight:800; margin-bottom:4px; line-height:1.2;"><?= e($_SESSION['user_name']) ?></h1>
                <p style="color:var(--text-secondary); font-size:0.95rem; display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--blue-400);"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= e($_SESSION['user_email']) ?>
                </p>
            </div>

            <div>
                <a href="<?= APP_URL ?>/logout" class="btn btn-outline btn-sm" style="border-color:rgba(239,68,68,0.25); color:var(--red-400);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px; margin-right:4px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    ចាកចេញ (Logout)
                </a>
            </div>
        </div>

        <!-- Purchases Section -->
        <div class="slide-up">
            <h2 style="font-size:1.4rem; font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--cyan-400);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                មេរៀន និងកម្មវិធីរបស់អ្នក (My Purchased Products)
            </h2>

            <?php if (empty($purchases)): ?>
                <!-- Empty State -->
                <div class="glass-card" style="padding:80px 40px; text-align:center; border-radius:var(--radius-lg);">
                    <div style="font-size:4rem; margin-bottom:20px; filter:grayscale(0.5);"></div>
                    <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:8px; color:var(--text-primary);">មិនទាន់មានការទិញនៅឡើយទេ</h3>
                    <p style="color:var(--text-secondary); font-size:0.9rem; max-width:400px; margin:0 auto 24px;">
                        រាល់វគ្គសិក្សា ឬកម្មវិធីដែលអ្នកទិញដោយប្រើប្រាស់ Gmail នេះ នឹងបង្ហាញនៅទីនេះដោយស្វ័យប្រវត្តិ។
                    </p>
                    <a href="<?= APP_URL ?>" class="btn btn-primary btn-sm">
                        ស្វែងរកវគ្គសិក្សា (Browse Courses)
                    </a>
                </div>
            <?php else: ?>
                <!-- Purchase Cards list -->
                <div style="display:grid; grid-template-columns:1fr; gap:20px;">
                    <?php foreach ($purchases as $purchase): ?>
                        <div class="glass-card" style="padding:20px 24px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; transition:all 0.2s ease;">
                            
                            <!-- Product Details -->
                            <div style="display:flex; align-items:center; gap:20px; flex:1; min-width:280px;">
                                <div style="width:70px; height:70px; border-radius:var(--radius-sm); overflow:hidden; background:linear-gradient(135deg, #eff6ff, #e0f2fe); flex-shrink:0; border:1px solid var(--border-color);">
                                    <?php if (!empty($purchase['course_thumbnail'])): ?>
                                        <img src="<?= APP_URL ?>/storage/thumbnails/<?= e($purchase['course_thumbnail']) ?>" alt="<?= e($purchase['course_title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:1.8rem;">
                                            <?= (($purchase['product_type'] ?? 'course') === 'tool') ? '💻' : '📚' ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                        <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; padding:2px 8px; border-radius:50px; background:<?= ($purchase['product_type'] === 'tool') ? 'rgba(6,182,212,0.15); color:var(--cyan-400); border:1px solid rgba(6,182,212,0.25);' : 'rgba(59,130,246,0.15); color:var(--blue-400); border:1px solid rgba(59,130,246,0.25);' ?>">
                                            <?= ($purchase['product_type'] === 'tool') ? 'Tool' : 'Course' ?>
                                        </span>
                                        <span style="font-size:0.75rem; color:var(--text-muted);">
                                            <?= date('M d, Y', strtotime($purchase['paid_at'])) ?>
                                        </span>
                                    </div>
                                    <h3 style="font-size:1.05rem; font-weight:700; color:var(--text-primary); line-height:1.3;"><?= e($purchase['course_title']) ?></h3>
                                    <p style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">
                                        វិក្កយបត្រ៖ <strong style="color:var(--text-secondary);"><?= e($purchase['invoice_no']) ?></strong> • 
                                        តម្លៃ៖ <strong style="color:var(--green-400);"><?= format_price($purchase['amount'], $purchase['currency']) ?></strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div style="display:flex; gap:10px; align-items:center; flex-shrink:0; flex-wrap:wrap;">
                                <a href="<?= APP_URL ?>/payment/success/<?= e($purchase['invoice_no']) ?>" class="btn btn-outline btn-sm">
                                    ព័ត៌មានការទិញ (Order Details)
                                </a>
                                <?php if (($purchase['product_type'] ?? 'course') === 'tool'): ?>
                                    <?php if (!empty($purchase['download_link'])): ?>
                                        <a href="<?= e($purchase['download_link']) ?>" target="_blank" class="btn btn-success btn-sm">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-3px; margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            ទាញយកកម្មវិធី (Download Tool)
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size:0.8rem; color:var(--red-400); background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.15); padding:6px 12px; border-radius:var(--radius-sm);">
                                            គ្មានតំណភ្ជាប់ទាញយក (No download link)
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <!-- Course Link -->
                                    <a href="<?= APP_URL ?>/join/<?= e($purchase['invoice_no']) ?>" target="_blank" class="btn btn-primary btn-sm" style="background:#24A1DE; box-shadow:none;">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="vertical-align:-3px; margin-right:4px;">
                                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                        </svg>
                                        ចូលក្រុម Telegram (Join Telegram)
                                    </a>
                                <?php endif; ?>
                                <a href="<?= APP_URL ?>/payment/receipt/<?= e($purchase['invoice_no']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                    🧾 វិក្កយបត្រ (Receipt)
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
