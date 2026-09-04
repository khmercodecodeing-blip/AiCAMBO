<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<style>
.policy-hero {
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #e0f2fe 100%);
    border-bottom: 1px solid var(--border-color);
    padding: 56px 0 44px;
    text-align: center;
}
.policy-hero .eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    background: #dbeafe; color: var(--blue-500); border: 1px solid #bfdbfe;
    font-size: 0.72rem; font-weight: 800; letter-spacing: 1.2px; text-transform: uppercase;
    padding: 5px 14px; border-radius: 50px; margin-bottom: 16px;
}
.policy-hero h1 {
    font-size: clamp(1.6rem, 3.5vw, 2.4rem); font-weight: 800; line-height: 1.3;
    color: var(--text-primary); margin-bottom: 10px; letter-spacing: -0.3px;
}
.policy-hero p { color: var(--text-secondary); font-size: 1rem; }
.policy-hero .meta { margin-top: 14px; font-size: 0.8rem; color: var(--text-muted); }

.policy-layout {
    display: grid; grid-template-columns: 240px 1fr; gap: 32px;
    max-width: 1080px; margin: 0 auto; padding: 40px 24px 80px;
}
.policy-toc {
    position: sticky; top: 90px; align-self: start;
    background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-lg);
    padding: 18px; box-shadow: var(--shadow-sm);
}
.policy-toc .toc-title {
    font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-muted); margin-bottom: 10px;
}
.policy-toc a {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 10px; border-radius: var(--radius-sm);
    color: var(--text-secondary); font-size: 0.88rem; font-weight: 600;
}
.policy-toc a:hover { background: #eff6ff; color: var(--blue-500); }
.policy-toc a .n {
    width: 22px; height: 22px; border-radius: 6px; flex-shrink: 0;
    background: #dbeafe; color: var(--blue-500); font-size: 0.72rem; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
}

.policy-content { display: flex; flex-direction: column; gap: 20px; }

.policy-notice {
    display: flex; gap: 14px; align-items: flex-start;
    background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626;
    border-radius: var(--radius-lg); padding: 20px 22px;
}
.policy-notice .icon {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center;
}
.policy-notice h3 { font-size: 1rem; font-weight: 800; color: #991b1b; margin-bottom: 6px; }
.policy-notice p { color: #7f1d1d; font-size: 0.92rem; line-height: 1.75; }
.policy-notice p strong { color: #991b1b; }

.policy-card {
    background: #fff; border: 1px solid var(--border-color); border-radius: var(--radius-lg);
    padding: 26px 28px; box-shadow: var(--shadow-sm); scroll-margin-top: 90px;
}
.policy-card-head { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
.policy-card-head .icon {
    width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
    background: var(--gradient-primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
.policy-card-head h2 { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); line-height: 1.3; }
.policy-card-head .sub { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; margin-top: 2px; }

.policy-list { list-style: none; display: flex; flex-direction: column; gap: 12px; }
.policy-list li {
    display: flex; gap: 12px; align-items: flex-start;
    color: var(--text-secondary); font-size: 0.95rem; line-height: 1.8;
    padding: 12px 14px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md);
}
.policy-list li::before {
    content: '✓'; flex-shrink: 0; margin-top: 3px;
    width: 22px; height: 22px; border-radius: 50%;
    background: #dcfce7; color: #15803d; font-size: 0.8rem; font-weight: 800;
    display: inline-flex; align-items: center; justify-content: center;
}
.policy-list li strong { color: var(--text-primary); }
.policy-list li span { flex: 1; min-width: 0; }

.policy-contact {
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
    background: linear-gradient(135deg, #2563eb, #0ea5e9); color: #fff;
    border-radius: var(--radius-lg); padding: 26px 28px;
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
}
.policy-contact h2 { font-size: 1.15rem; font-weight: 800; margin-bottom: 6px; }
.policy-contact p { font-size: 0.92rem; opacity: 0.92; line-height: 1.7; max-width: 560px; }
.policy-contact .btn-tg {
    display: inline-flex; align-items: center; gap: 10px; white-space: nowrap;
    background: #fff; color: var(--blue-500); font-weight: 800; padding: 12px 22px;
    border-radius: var(--radius-md); box-shadow: var(--shadow-md); transition: var(--transition);
}
.policy-contact .btn-tg:hover { transform: translateY(-2px); color: var(--blue-600); }

.policy-en p { color: var(--text-secondary); font-size: 0.95rem; line-height: 1.85; }
.policy-en p strong { color: var(--text-primary); }

@media (max-width: 900px) {
    .policy-layout { grid-template-columns: 1fr; gap: 20px; padding: 28px 16px 70px; }
    .policy-toc { position: static; }
    .policy-toc nav { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
    .policy-card { padding: 22px 18px; }
    .policy-notice { padding: 18px 16px; }
    .policy-contact { padding: 22px 18px; }
    .policy-contact .btn-tg { width: 100%; justify-content: center; }
}
@media (max-width: 520px) {
    .policy-toc nav { grid-template-columns: 1fr; }
}
</style>

<section class="policy-hero">
    <div class="container">
        <span class="eyebrow">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Terms &amp; Policy
        </span>
        <h1>គោលការណ៍ឯកជនភាព &amp; គោលការណ៍មិនសងប្រាក់វិញ</h1>
        <p>Privacy Policy &amp; No-Refund Policy — សូមអានឱ្យបានច្បាស់មុនពេលទិញ</p>
        <div class="meta">អនុវត្តចំពោះផលិតផលឌីជីថលទាំងអស់នៅលើ <?= e(APP_NAME) ?></div>
    </div>
</section>

<div class="policy-layout">
    <!-- Table of Contents -->
    <aside class="policy-toc">
        <div class="toc-title">មាតិកា</div>
        <nav>
            <a href="#refund"><span class="n">1</span> មិនសងប្រាក់វិញ</a>
            <a href="#delivery"><span class="n">2</span> ការផ្តល់ជូន</a>
            <a href="#privacy"><span class="n">3</span> ឯកជនភាព</a>
            <a href="#contact"><span class="n">4</span> ទំនាក់ទំនង</a>
            <a href="#english"><span class="n">EN</span> English Summary</a>
        </nav>
    </aside>

    <div class="policy-content">
        <!-- Highlighted No-Refund Notice -->
        <div class="policy-notice fade-in">
            <div class="icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <h3>សូមអានឱ្យបានច្បាស់មុនពេលទិញ</h3>
                <p>
                    ផលិតផលទាំងអស់ (Tool, License Key, ឬ Key ផ្សេងៗ) គឺជាទំនិញឌីជីថល។ បន្ទាប់ពីទូទាត់ប្រាក់ និងទទួលបាន Key/Tool រួច
                    <strong>Admin នឹងមិនសងប្រាក់វិញ (No Refund) ក្នុងករណីណាមួយឡើយ</strong> ទោះជាមានការប្តូរចិត្ត ឬមិនប្រើប្រាស់ក៏ដោយ។
                    សូមពិនិត្យព័ត៌មានផលិតផលឱ្យបានច្បាស់លាស់ និងសួរសំណួរទៅ Admin មុនពេលធ្វើការទូទាត់។
                </p>
            </div>
        </div>

        <!-- 1. Refund -->
        <section class="policy-card fade-in" id="refund">
            <div class="policy-card-head">
                <div class="icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </div>
                <div>
                    <h2>1. គោលការណ៍មិនសងប្រាក់វិញ</h2>
                    <div class="sub">Refund Policy</div>
                </div>
            </div>
            <ul class="policy-list">
                <li><span>ការទិញ Tool, License Key ឬ Key ផ្សេងៗគ្រប់ប្រភេទ ជាការទិញចុងក្រោយ (Final Sale) — <strong>មិនអាចដូរ ឬសងប្រាក់វិញបានទេ</strong> បន្ទាប់ពី Key/Tool ត្រូវបានប្រគល់ជូន។</span></li>
                <li><span>Admin នឹងផ្តល់ជំនួយបច្ចេកទេស ប្រសិនបើ Tool/Key មានបញ្ហាដែលបណ្តាលមកពីភាគីលក់ (ឧ. Key មិនដំណើរការ ដោយសារកំហុសបច្ចេកទេសពីខ្ញុំផ្ទាល់)។</span></li>
                <li><span>ការសម្រេចចិត្តទិញត្រូវបានចាត់ទុកថាអតិថិជនបានយល់ព្រម និងទទួលយកលក្ខខណ្ឌនេះរួចជាស្រេច។</span></li>
            </ul>
        </section>

        <!-- 2. Delivery -->
        <section class="policy-card fade-in" id="delivery">
            <div class="policy-card-head">
                <div class="icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div>
                    <h2>2. គោលការណ៍ផ្តល់ជូន / សិទ្ធិប្រើប្រាស់</h2>
                    <div class="sub">Delivery Policy</div>
                </div>
            </div>
            <ul class="policy-list">
                <li><span>License Key ឬតំណភ្ជាប់ Telegram Group នឹងផ្តល់ជូនភ្លាមៗ (Instant) បន្ទាប់ពីការទូទាត់ត្រូវបានបញ្ជាក់ដោយប្រព័ន្ធ។</span></li>
                <li><span>អតិថិជនត្រូវរក្សាទុក License Key ដោយខ្លួនឯង។ Admin នឹងជួយស្តារ Key វិញ ក្នុងករណីមានភស្តុតាងបញ្ជាក់ការទូទាត់ត្រឹមត្រូវ។</span></li>
            </ul>
        </section>

        <!-- 3. Privacy -->
        <section class="policy-card fade-in" id="privacy">
            <div class="policy-card-head">
                <div class="icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <h2>3. គោលការណ៍ឯកជនភាព</h2>
                    <div class="sub">Privacy Policy</div>
                </div>
            </div>
            <ul class="policy-list">
                <li><span>ខ្ញុំប្រមូលព័ត៌មានចាំបាច់តែប៉ុណ្ណោះ (ឈ្មោះ, លេខទូរស័ព្ទ, អ៊ីមែល) ដើម្បីដំណើរការការទូទាត់ និងទំនាក់ទំនងជាមួយអតិថិជន។</span></li>
                <li><span>ការទូទាត់ប្រាក់ដំណើរការតាមរយៈ KHQR / Bakong និង/ឬ ABA PayWay ដែលជាប្រព័ន្ធសុវត្ថិភាពស្ដង់ដារធនាគារ។ ខ្ញុំមិនរក្សាទុកព័ត៌មានកាតឥណទាន ឬគណនីធនាគាររបស់អតិថិជនឡើយ។</span></li>
                <li><span>ព័ត៌មានផ្ទាល់ខ្លួននឹងមិនត្រូវបានលក់ ឬចែករំលែកទៅភាគីទីបីណាមួយក្រៅពីគោលបំណងដំណើរការការទូទាត់ និងការផ្ដល់សេវាកម្មនោះទេ។</span></li>
            </ul>
        </section>

        <!-- 4. Contact -->
        <section class="policy-contact fade-in" id="contact">
            <div>
                <h2>4. ទំនាក់ទំនង (Contact)</h2>
                <p>ប្រសិនបើអ្នកមានសំណួរទាក់ទងនឹងការទិញ ឬគោលការណ៍ខាងលើ សូមទាក់ទងមកកាន់ Admin ដោយផ្ទាល់ មុនពេលធ្វើការទូទាត់ប្រាក់។</p>
            </div>
            <a href="https://t.me/NouchSina" target="_blank" class="btn-tg">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                ទាក់ទង Admin តាម Telegram
            </a>
        </section>

        <!-- English Summary -->
        <section class="policy-card policy-en fade-in" id="english">
            <div class="policy-card-head">
                <div class="icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <h2>Summary in English</h2>
                    <div class="sub">Quick overview of the policies above</div>
                </div>
            </div>
            <p>
                All Tools, License Keys, and digital products sold on this website are considered digital goods.
                Once a purchase is completed and a License Key / Tool access has been delivered,
                <strong>the purchase is final and non-refundable under any circumstances</strong>.
                Payments are processed securely via Bakong KHQR / ABA PayWay; I do not store your banking card details.
                Please review product details carefully and contact the Admin directly via Telegram before making a payment
                if you have any questions.
            </p>
        </section>

        <div>
            <a href="<?= APP_URL ?>" class="btn btn-ghost">← ត្រឡប់ទៅទំព័រដើម (Back to Home)</a>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
