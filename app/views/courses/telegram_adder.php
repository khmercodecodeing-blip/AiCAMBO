<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>

<style>
/* Custom page-specific styles for premium look */
.hero-section {
    position: relative;
    padding: 80px 0 60px;
    background: radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 60%),
                radial-gradient(circle at bottom left, rgba(37, 99, 235, 0.10), transparent 60%);
    overflow: hidden;
    text-align: center;
}

.hero-title {
    font-size: 2.8rem;
    font-weight: 800;
    line-height: 1.2;
    background: linear-gradient(135deg, #1d4ed8, #2563eb, #0ea5e9);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 20px;
    font-family: 'Kantumruy Pro', 'Inter', sans-serif;
    letter-spacing: -0.5px;
    animation: fadeInDown 0.8s ease-out;
}

.hero-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    max-width: 700px;
    margin: 0 auto 32px;
    line-height: 1.6;
    font-family: 'Kantumruy Pro', 'Inter', sans-serif;
    animation: fadeInUp 0.8s ease-out;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 40px;
}

.btn-premium-download {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff !important;
    font-weight: 700;
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 1rem;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.btn-premium-download:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
    background: linear-gradient(135deg, #059669, #047857);
}

.btn-premium-buy {
    background: #ffffff;
    color: var(--text-primary) !important;
    font-weight: 700;
    padding: 14px 32px;
    border-radius: 12px;
    font-size: 1rem;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: var(--shadow-sm);
}

.btn-premium-buy:hover {
    background: #eff6ff;
    border-color: var(--border-accent);
    transform: translateY(-3px);
}

.badge-tool {
    background: rgba(37, 99, 235, 0.08);
    border: 1px solid rgba(37, 99, 235, 0.25);
    color: var(--blue-500);
    font-size: 0.75rem;
    padding: 4px 12px;
    border-radius: 50px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    display: inline-block;
    margin-bottom: 16px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
}

.feature-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 30px;
    text-align: left;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.feature-card:hover {
    background: #ffffff;
    border-color: var(--border-accent);
    transform: translateY(-5px);
    box-shadow: var(--shadow-glow);
}

.feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: white;
}

.feature-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.feature-desc {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.5;
    font-family: 'Kantumruy Pro', sans-serif;
}

.pricing-section {
    padding: 60px 0;
    background: #ffffff;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
}

.pricing-card {
    position: relative;
    background: #f8fafc;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 35px 24px;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.pricing-card:hover {
    border-color: var(--border-accent);
    transform: translateY(-8px);
    box-shadow: var(--shadow-glow);
}

.pricing-card.popular {
    border-color: var(--blue-500);
    background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
    box-shadow: 0 8px 30px rgba(37, 99, 235, 0.15);
}

.pricing-card.popular::before {
    content: "POPULAR";
    position: absolute;
    top: 14px;
    right: 14px;
    background: linear-gradient(135deg, #2563eb, #0ea5e9);
    color: #ffffff;
    font-size: 0.65rem;
    font-weight: 900;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 1px;
}

.plan-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 16px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.plan-price {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.plan-duration {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 24px;
}

.plan-features {
    list-style: none;
    padding: 0;
    margin: 0 0 32px;
    text-align: left;
    flex: 1;
}

.plan-features li {
    font-size: 0.88rem;
    color: var(--text-secondary);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.plan-features li svg {
    color: var(--green-400);
    flex-shrink: 0;
}

.btn-buy-plan {
    width: 100%;
    justify-content: center;
    font-weight: 700;
}

.tutorial-section {
    padding: 80px 0;
}

.video-wrapper {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 12px;
    box-shadow: var(--shadow-lg);
    margin-bottom: 40px;
}

.instruction-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step-item {
    display: flex;
    gap: 20px;
    background: #ffffff;
    border: 1px solid var(--border-color);
    padding: 20px;
    border-radius: var(--radius-md);
    align-items: flex-start;
    box-shadow: var(--shadow-sm);
}

.step-num {
    background: var(--gradient-primary);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1rem;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 6px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.step-text {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.6;
    font-family: 'Kantumruy Pro', sans-serif;
}

/* Animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hero Mockup styles */
.hero-mockup {
    margin-top: 45px;
    max-width: 850px;
    margin-left: auto;
    margin-right: auto;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-lg);
    background: #ffffff;
    padding: 10px;
    animation: fadeInUp 1s ease-out;
}

.hero-mockup img {
    width: 100%;
    height: auto;
    border-radius: var(--radius-md);
    display: block;
}

/* Support section styles */
.support-section {
    padding: 80px 0;
    background: #ffffff;
    border-top: 1px solid var(--border-color);
}

.support-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
}

.support-card {
    background: #f8fafc;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 30px 24px;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.support-card:hover {
    transform: translateY(-5px);
    border-color: var(--border-accent);
    box-shadow: var(--shadow-glow);
}

.support-card-icon {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: rgba(36, 161, 222, 0.15);
    color: #24A1DE;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.support-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 12px;
    font-family: 'Kantumruy Pro', sans-serif;
}

.support-card-desc {
    font-size: 0.9rem;
    color: var(--text-secondary);
    margin-bottom: 24px;
    line-height: 1.6;
    font-family: 'Kantumruy Pro', sans-serif;
    flex: 1;
}

.support-card-btn {
    width: 100%;
    justify-content: center;
    font-weight: 700;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 28px 0 32px;
    }

    .hero-title {
        font-size: 1.8rem;
        letter-spacing: 0;
        overflow-wrap: anywhere;
        margin-bottom: 12px;
    }

    .hero-subtitle {
        font-size: 0.95rem;
        margin-bottom: 20px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn-premium-download,
    .btn-premium-buy {
        min-height: 48px;
        padding: 12px 16px;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.95rem;
    }

    .hero-mockup {
        margin-top: 20px;
        padding: 0;
        border: 0;
        box-shadow: none;
        border-radius: 8px;
    }

    .features-grid,
    .pricing-grid,
    .support-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 16px;
    }

    .feature-card,
    .pricing-card,
    .support-card {
        min-width: 0;
        padding: 24px 20px;
        border-radius: 8px;
        overflow-wrap: anywhere;
    }

    .section-header h2 {
        font-size: 1.4rem;
        letter-spacing: 0;
        overflow-wrap: anywhere;
    }

    #pricing {
        scroll-margin-top: 80px;
    }
}
</style>

<!-- Hero / Intro Section -->
<section class="hero-section">
    <div class="container">
        <span class="badge-tool">Digital Marketing Tool</span>
        <h1 class="hero-title">Telegram Adder Pro</h1>
        <p class="hero-subtitle">
            កម្មវិធីស្វ័យប្រវត្តិកម្មអូសសមាជិក (Members) ចូលទៅក្នុងក្រុម Telegram របស់អ្នកបានលឿន រហ័ស និងមានសុវត្ថិភាពខ្ពស់បំផុត។ កំណែទម្រង់ថ្មីឆ្នាំ ២០២៦ ជាមួយប្រព័ន្ធការពារការជាប់សោរ (Anti-Flood Wait Lock)។
        </p>

        <div class="action-buttons">
            <a href="<?= APP_URL ?>/Update/TelegramAdderProSetup.exe" class="btn-premium-download">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                ទាញយកកម្មវិធី (Download Tool)
            </a>
            <a href="#pricing" class="btn-premium-buy">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                ទិញ License (Buy License)
            </a>
        </div>

        <div class="hero-mockup">
            <img src="<?= APP_URL ?>/PhotoTool/Aderr.PNG" alt="Telegram Adder Pro App Interface" width="1631" height="875" />
        </div>
    </div>
</section>

<!-- Features Grid Section -->
<section class="container" style="padding-bottom: 80px;">
    <div class="section-header text-center">
        <h2>លក្ខណៈពិសេសដ៏អស្ចារ្យ (Features)</h2>
        <p>ឧបករណ៍ដ៏មានឥទ្ធិពលសម្រាប់ការគ្រប់គ្រង និងពង្រីកសហគមន៍ Telegram របស់អ្នក</p>
    </div>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3 class="feature-title">Bulk Adding Members</h3>
            <p class="feature-desc">អូសបន្ថែមសមាជិកពីក្រុមផ្សេង ចូលមកក្នុងក្រុមរបស់អ្នកបានរាប់រយនាក់ក្នុងមួយថ្ងៃ ដោយប្រើប្រាស់គណនីច្រើនក្នុងពេលតែមួយ។</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:linear-gradient(135deg, #10b981, #059669);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
                </svg>
            </div>
            <h3 class="feature-title">Group Member Scraper</h3>
            <p class="feature-desc">ស្កេនទាញយកបញ្ជីឈ្មោះសមាជិកសកម្មពីក្រុមគោលដៅផ្សេងៗបានលឿនបំផុត រួមទាំងការចម្រោះយកតែអ្នកដែលទើបតែចូលលេងចុងក្រោយ។</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:linear-gradient(135deg, #f59e0b, #d97706);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h3 class="feature-title">Smart Anti-Block System</h3>
            <p class="feature-desc">ប្រព័ន្ធកំណត់ពេលវេលារង់ចាំរវាងការបន្ថែមនីមួយៗ (Sleep Time Settings) ជួយការពារគណនីរបស់អ្នកពីការជាប់កម្រិត ឬ Block ពី Telegram។</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="background:linear-gradient(135deg, #ec4899, #db2777);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                </svg>
            </div>
            <h3 class="feature-title">Auto-Update System</h3>
            <p class="feature-desc">ប្រព័ន្ធត្រួតពិនិត្យ និងធ្វើបច្ចុប្បន្នភាពកំណែទម្រង់ថ្មីៗដោយស្វ័យប្រវត្តិនៅពេលបើកកម្មវិធី ធានាបាននូវមុខងារថ្មីៗជានិច្ច។</p>
        </div>
    </div>
</section>

<!-- Pricing / Purchase Key Section -->
<section id="pricing" class="pricing-section">
    <div class="container">
        <div class="section-header text-center">
            <h2>ជ្រើសរើសគម្រោងដែលអ្នកចង់ទិញ(Pricing Plans)</h2>
            <p>ទូទាត់ប្រាក់ដោយស្វ័យប្រវត្តិតាមរយៈ KHQR ដើម្បីទទួលបានលេខកូដសកម្មភាព (Activation Key) ភ្លាមៗនៅលើវេបសាយនេះ</p>
        </div>

        <div class="pricing-grid">
            <!-- 1 Month Plan -->
            <div class="pricing-card">
                <div class="plan-name">គម្រោង ១ ខែ (1 Month)</div>
                <div class="plan-price">$7.00</div>
                <div class="plan-duration">មានសុពលភាពរយៈពេល ៣០ ថ្ងៃ</div>
                <ul class="plan-features">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ប្រើប្រាស់បានគ្រប់មុខងារទាំងអស់
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ដំឡើងប្រើប្រាស់បាន ១ គ្រឿង (1 PC Link)
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការធ្វើបច្ចុប្បន្នភាពស្វ័យប្រវត្តិឥតគិតថ្លៃ
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការគាំទ្រផ្នែកបច្ចេកទេស
                    </li>
                </ul>
                <a href="<?= APP_URL ?>/checkout?course_id=1" class="btn btn-primary btn-buy-plan">
                    ទិញឥឡូវនេះ (Buy Now)
                </a>
            </div>

            <!-- 3 Months Plan (Popular) -->
            <div class="pricing-card popular">
                <div class="plan-name">គម្រោង ៣ ខែ (3 Months)</div>
                <div class="plan-price">$18.00</div>
                <div class="plan-duration">មានសុពលភាពរយៈពេល ៩០ ថ្ងៃ</div>
                <ul class="plan-features">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ប្រើប្រាស់បានគ្រប់មុខងារទាំងអស់
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ដំឡើងប្រើប្រាស់បាន ១ គ្រឿង (1 PC Link)
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការធ្វើបច្ចុប្បន្នភាពស្វ័យប្រវត្តិឥតគិតថ្លៃ
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការគាំទ្រលឿនរហ័ស (Priority Support)
                    </li>
                    <li style="color:var(--cyan-400); font-weight:bold;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="color:var(--cyan-400);"><polyline points="20 6 9 17 4 12"/></svg>
                        សន្សំសំចៃបានជាង ១៥%
                    </li>
                </ul>
                <a href="<?= APP_URL ?>/checkout?course_id=2" class="btn btn-primary btn-buy-plan" style="background: linear-gradient(135deg, #06b6d4, #0891b2); border:none;">
                    ទិញឥឡូវនេះ (Buy Now)
                </a>
            </div>

            <!-- 1 Year Plan -->
            <div class="pricing-card">
                <div class="plan-name">គម្រោង ១ ឆ្នាំ (1 Year)</div>
                <div class="plan-price">$50.00</div>
                <div class="plan-duration">មានសុពលភាពរយៈពេល ៣៦៥ ថ្ងៃ</div>
                <ul class="plan-features">
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ប្រើប្រាស់បានគ្រប់មុខងារទាំងអស់
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ដំឡើងប្រើប្រាស់បាន ១ គ្រឿង (1 PC Link)
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការធ្វើបច្ចុប្បន្នភាពស្វ័យប្រវត្តិឥតគិតថ្លៃ
                    </li>
                    <li>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        ការគាំទ្រលំដាប់ខ្ពស់ (VIP Support)
                    </li>
                    <li style="color:var(--green-400); font-weight:bold;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="color:var(--green-400);"><polyline points="20 6 9 17 4 12"/></svg>
                        សន្សំសំចៃបានរហូតដល់ ៤០%
                    </li>
                </ul>
                <a href="<?= APP_URL ?>/checkout?course_id=3" class="btn btn-primary btn-buy-plan">
                    ទិញឥឡូវនេះ (Buy Now)
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Tutorial Section (របៀបប្រើប្រាស់) -->
<section class="tutorial-section">
    <div class="container">
        <div class="section-header text-center">
            <h2>របៀបប្រើប្រាស់កម្មវិធី (How to Use)</h2>
            <p>សូមទស្សនាវីដេអូណែនាំ និងអនុវត្តតាមជំហានងាយៗខាងក្រោមដើម្បីដំឡើង និងប្រើប្រាស់កម្មវិធី</p>
        </div>

        <!-- Video Player Wrapper -->
        <div class="video-wrapper">
            <div class="video-container">
                <iframe src="https://www.youtube.com/embed/videoseries?list=PL65bkMdl13jrRRvMFe81rgnTgC6HGpigy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
        
        <div class="text-center" style="margin-top: -20px; margin-bottom: 45px;">
            <a href="https://www.youtube.com/playlist?list=PL65bkMdl13jrRRvMFe81rgnTgC6HGpigy" target="_blank" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 8px; font-family: 'Kantumruy Pro', sans-serif;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="color: #ff0000; vertical-align: middle;">
                    <path d="M23.498 6.163a3.003 3.003 0 0 0-2.11-2.11C19.518 3.545 12 3.545 12 3.545s-7.518 0-9.388.507a3.003 3.003 0 0 0-2.11 2.11C0 8.033 0 12 0 12s0 3.967.502 5.837a3.003 3.003 0 0 0 2.11 2.11c1.87.507 9.388.507 9.388.507s7.518 0 9.388-.507a3.003 3.003 0 0 0 2.11-2.11C24 15.967 24 12 24 12s0-3.967-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                មើលបញ្ជីរឿងណែនាំទាំងអស់លើ YouTube (Watch Full Playlist)
            </a>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="instruction-steps">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <h4 class="step-title">ទាញយក និងពន្លាឯកសារ (Download & Extract)</h4>
                            <p class="step-text">ចុចលើប៊ូតុង "ទាញយកកម្មវិធី" ខាងលើដើម្បីទទួលបានឯកសារតម្លើង `.exe`។ បន្ទាប់មក សូមបង្កើតថត (Folder) ថ្មីមួយនៅលើកុំព្យូទ័ររបស់អ្នក រួចកូពីឯកសារកម្មវិធីទៅដាក់ក្នុងនោះ។</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <h4 class="step-title">ទិញ និងចម្លងលេខកូដសកម្មភាព (Buy & Copy License Key)</h4>
                            <p class="step-text">ជ្រើសរើសគម្រោងដែលអ្នកចង់បានក្នុងផ្នែក Pricing រួចចុច "ទិញឥឡូវនេះ"។ ធ្វើការស្កេន KHQR ដើម្បីទូទាត់ប្រាក់។ នៅពេលជោគជ័យ វេបសាយនឹងបង្ហាញលេខកូដសកម្មភាព (License Key)។ សូមចុច "Copy Key" រក្សាទុក។</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <h4 class="step-title">បើកកម្មវិធី និងបញ្ចូលលេខកូដ (Run & Activate)</h4>
                            <p class="step-text">បើកឯកសារ `TelegramAdderPro.exe` នៅលើកុំព្យូទ័ររបស់អ្នក។ ប្រអប់ផ្ទាំងតម្លើង (License Activation Dialog) នឹងបង្ហាញឡើង។ សូមយកលេខកូដដែលបានចម្លងមកផាស (Paste) ចូលក្នុងប្រអប់ "Manual Activation" រួចចុចលើប៊ូតុង "Activate" ជាការស្រេច។</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support & Contact Section -->
<section class="support-section">
    <div class="container">
        <div class="section-header text-center">
            <h2>សហគមន៍ & ការគាំទ្រ (Community & Support)</h2>
            <p>ចូលរួមជាមួយសហគមន៍របស់យើងដើម្បីទទួលបានព័ត៌មានថ្មីៗ និងការគាំទ្រផ្ទាល់ពីអាដមីន</p>
        </div>
        
        <div class="support-grid">
            <!-- Group Telegram -->
            <div class="support-card">
                <div class="support-card-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>
                <h3 class="support-card-title">ក្រុមពិភាក្សា Telegram</h3>
                <p class="support-card-desc">ចូលរួមក្រុមតេឡេក្រាមផ្លូវការ ដើម្បីសួរនាំ និងចែករំលែកបទពិសោធន៍ជាមួយសមាជិកដទៃទៀត។</p>
                <a href="https://t.me/tooltelegramadder" target="_blank" class="btn btn-primary support-card-btn" style="background:#24A1DE; border-color:#24A1DE;">
                    ចូលរួមក្រុម (Join Group)
                </a>
            </div>
            
            <!-- Channel Telegram -->
            <div class="support-card">
                <div class="support-card-icon" style="color: #229ED9; background: rgba(34, 158, 217, 0.15);">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-1-.65-.35-1 .22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.24-5.54 3.65-.52.36-.99.53-1.4.52-.46-.01-1.34-.26-1.99-.47-.8-.26-1.43-.4-1.38-.85.03-.23.35-.47.97-.71 3.8-1.65 6.33-2.74 7.6-3.26 3.62-1.48 4.37-1.74 4.86-1.75.11 0 .35.03.5.15.13.1.17.24.18.37 0 .04.01.12.01.17z"/>
                    </svg>
                </div>
                <h3 class="support-card-title">ឆានែល Telegram</h3>
                <p class="support-card-desc">តាមដានឆានែលផ្លូវការ ដើម្បីទទួលបានព័ត៌មានបច្ចុប្បន្នភាពថ្មីៗ និងការអាប់ដេតកំណែទម្រង់ផ្សេងៗ។</p>
                <a href="https://t.me/demotelegramadderpro" target="_blank" class="btn btn-primary support-card-btn" style="background:#229ED9; border-color:#229ED9;">
                    ចូលរួមឆានែល (Join Channel)
                </a>
            </div>
            
            <!-- Contact Admin -->
            <div class="support-card">
                <div class="support-card-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.15);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <h3 class="support-card-title">ទាក់ទងមកកាន់ Admin</h3>
                <p class="support-card-desc">មានចម្ងល់ ឬត្រូវការជំនួយក្នុងការទិញសេវាកម្ម និងតម្លើងកម្មវិធី សូមទាក់ទងមកកាន់អាដមីនផ្ទាល់។</p>
                <a href="https://t.me/NouchSina" target="_blank" class="btn btn-primary support-card-btn" style="background:#10b981; border-color:#10b981;">
                    ផ្ញើសារទៅកាន់ Admin (@NouchSina)
                </a>
            </div>
        </div>
    </div>
</section>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
