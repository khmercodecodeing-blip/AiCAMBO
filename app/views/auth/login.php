<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<section class="login-section" style="min-height:calc(100vh - 140px); display:flex; align-items:center; justify-content:center; padding:40px 20px;">
    <div class="glass-card fade-in" style="width:100%; max-width:440px; padding:40px 30px; text-align:center; box-shadow: var(--shadow-lg); border-radius: var(--radius-lg);">
        
        <!-- Icon/Logo -->
        <div style="width:70px; height:70px; background:rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color:var(--blue-400);">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>

        <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:8px; background:var(--gradient-primary); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
            ចូលគណនី
        </h2>
        <p style="color:var(--text-secondary); font-size:0.95rem; margin-bottom:30px; line-height:1.5;">
            ចូលគណនីជាមួយ Gmail ដើម្បីរក្សាទុក និងមើលទិន្នន័យវគ្គសិក្សា ឬកម្មវិធីដែលអ្នកបានទាញយក។
        </p>

        <!-- Features list -->
        <div style="text-align:left; background:var(--bg-glass); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; margin-bottom:30px;">
            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:12px;">
                <span style="color:var(--green-400); font-weight:bold; font-size:1.1rem; line-height:1;">✓</span>
                <span style="font-size:0.85rem; color:var(--text-secondary);">រក្សាទុកប្រវត្តិនៃការទិញ និងទាញយក</span>
            </div>
            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:12px;">
                <span style="color:var(--green-400); font-weight:bold; font-size:1.1rem; line-height:1;">✓</span>
                <span style="font-size:0.85rem; color:var(--text-secondary);">ចូលរួមក្រុម Telegram វគ្គសិក្សាឡើងវិញបានគ្រប់ពេល</span>
            </div>
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <span style="color:var(--green-400); font-weight:bold; font-size:1.1rem; line-height:1;">✓</span>
                <span style="font-size:0.85rem; color:var(--text-secondary);">បំពេញព័ត៌មានដោយស្វ័យប្រវត្តិនៅពេលទិញបន្ទាប់</span>
            </div>
        </div>

        <?php if (empty(GOOGLE_CLIENT_ID)): ?>
            <!-- Helper for local developer if GOOGLE_CLIENT_ID is not configured in .env -->
            <div class="alert alert-error" style="text-align:left; font-size:0.8rem; line-height:1.4; border-radius:var(--radius-md); border:1px solid rgba(239,68,68,0.25); background:rgba(239,68,68,0.08); padding:16px; margin-bottom:20px;">
                <strong style="display:block; margin-bottom:6px; color:var(--red-400);">⚠️ Developer Notice: Google OAuth not configured</strong>
                សូមបន្ថែម <code style="background:#fee2e2; padding:2px 6px; border-radius:4px; font-family:monospace; color:#991b1b;">GOOGLE_CLIENT_ID</code> នៅក្នុងឯកសារ <code style="background:#fee2e2; padding:2px 6px; border-radius:4px; font-family:monospace; color:#991b1b;">.env</code> ដើម្បីដំណើរការការចូលគណនី។
            </div>
        <?php else: ?>
            <!-- Google Sign-In Button Container -->
            <div style="display:flex; justify-content:center; margin-bottom:20px;">
                <div id="g_id_onload"
                     data-client_id="<?= e(GOOGLE_CLIENT_ID) ?>"
                     data-context="signin"
                     data-ux_mode="redirect"
                     data-login_uri="<?= e(APP_URL) ?>/auth/google"
                     data-auto_prompt="false">
                </div>
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="rectangular"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="320">
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top:20px;">
            <a href="<?= APP_URL ?>" style="font-size:0.9rem; color:var(--text-muted); display:inline-flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-muted)'">
                ← ត្រឡប់ទៅទំព័រដើមវិញ
            </a>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
