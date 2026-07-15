<?php
$page_title = $page_title ?? 'Mobile App - APS Dream Home';
$apk_url_direct = BASE_URL . '/downloads/apsdreamhome.apk';
$apk_url_php = BASE_URL . '/download-apk.php';
$apk_size = '82 MB';
$app_version = '1.2.0';
$updated_date = '2026-07-07';
?>
<div class="mobile-app-page">
    <style>
    .mobile-app-hero {
        background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
        padding: 80px 0 60px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .mobile-app-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
        animation: pulse 4s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 1; }
    }
    .mobile-app-hero .phone-mockup {
        font-size: 120px;
        margin-bottom: 20px;
        display: block;
        filter: drop-shadow(0 20px 40px rgba(0,0,0,0.2));
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-15px); }
    }
    .mobile-app-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .mobile-app-hero p {
        font-size: 1.2rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto 30px;
    }
    .download-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 40px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 50px;
        background: white;
        color: #0d6efd;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .download-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        color: #0d6efd;
    }
    .download-btn i {
        font-size: 1.5rem;
    }
    .app-info-badge {
        display: inline-block;
        background: rgba(255,255,255,0.15);
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.9rem;
        margin-top: 20px;
    }
    .app-info-badge span {
        margin: 0 10px;
    }
    .app-info-badge .sep {
        opacity: 0.4;
    }
    .features-section {
        padding: 60px 0;
        background: #f8f9fa;
    }
    .features-section h2 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 50px;
        font-size: 2rem;
    }
    .feature-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        border: 1px solid rgba(0,0,0,0.03);
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }
    .feature-card .icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: #0d6efd;
    }
    .feature-card h5 {
        font-weight: 600;
        margin-bottom: 10px;
    }
    .feature-card p {
        color: #6c757d;
        font-size: 0.95rem;
        margin: 0;
    }
    .screenshot-section {
        padding: 60px 0;
        background: white;
    }
    .screenshot-section h2 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .screenshot-section .subtitle {
        text-align: center;
        color: #6c757d;
        margin-bottom: 40px;
    }
    .screenshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        max-width: 900px;
        margin: 0 auto;
    }
    .screenshot-placeholder {
        background: linear-gradient(135deg, #e9ecef, #dee2e6);
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        aspect-ratio: 9/16;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #6c757d;
    }
    .screenshot-placeholder i {
        font-size: 3rem;
        margin-bottom: 10px;
        opacity: 0.5;
    }
    .screenshot-placeholder span {
        font-size: 0.85rem;
    }
    .how-to-section {
        padding: 60px 0;
        background: #f8f9fa;
    }
    .how-to-section h2 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 40px;
    }
    .step-card {
        text-align: center;
        padding: 20px;
    }
    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #0d6efd;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 auto 15px;
    }
    .step-card h6 {
        font-weight: 600;
    }
    .safety-section {
        padding: 40px 0;
        background: #e8f5e9;
    }
    .safety-section .alert {
        margin: 0;
        border: none;
        background: transparent;
        padding: 0;
    }
    .faq-section {
        padding: 60px 0;
    }
    .faq-section h2 {
        text-align: center;
        font-weight: 700;
        margin-bottom: 30px;
    }
    .faq-item {
        border-bottom: 1px solid #dee2e6;
        padding: 15px 0;
    }
    .faq-question {
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .faq-answer {
        color: #6c757d;
        padding-top: 10px;
        display: none;
    }
    .faq-answer.show {
        display: block;
    }
    .qr-section {
        text-align: center;
        padding: 30px 0;
    }
    .qr-placeholder {
        display: inline-flex;
        align-items: center;
        gap: 20px;
        background: white;
        padding: 20px 30px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .qr-placeholder .qr-box {
        width: 120px;
        height: 120px;
        background: #f0f0f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: #999;
    }
    @media (max-width: 768px) {
        .mobile-app-hero h1 { font-size: 1.8rem; }
        .mobile-app-hero .phone-mockup { font-size: 80px; }
        .download-btn { padding: 14px 30px; font-size: 1rem; }
        .screenshot-grid { grid-template-columns: repeat(2, 1fr); }
    }
    </style>

    <div class="mobile-app-hero">
        <div class="container position-relative">
            <span class="phone-mockup">📱</span>
            <h1>APS Dream Home App</h1>
            <p>Find your dream property on the go. Browse plots, book site visits, track payments, and more — all from your phone.</p>
            <a href="<?= $apk_url_direct ?>" class="download-btn" download id="downloadBtn">
                <i class="fab fa-android"></i>
                Download APK (<?= $apk_size ?>)
            </a>
            <div class="mt-2">
                <small class="opacity-75">Direct download. If it fails, <a href="<?= $apk_url_php ?>" class="text-white text-decoration-underline">try alternate link</a>.</small>
            </div>
            <div class="app-info-badge">
                <span>📦 <?= $apk_size ?></span>
                <span class="sep">|</span>
                <span>📌 v<?= $app_version ?></span>
                <span class="sep">|</span>
                <span>🔄 <?= $updated_date ?></span>
            </div>
            <div class="mt-3">
                <small class="opacity-75">⚠️ Allows installation from unknown sources. Your data is secure.</small>
            </div>
        </div>
    </div>

    <div class="safety-section">
        <div class="container">
            <div class="alert alert-success d-flex align-items-center gap-3 mb-0 justify-content-center flex-wrap">
                <i class="fas fa-shield-alt fa-2x"></i>
                <div>
                    <strong>🔒 Safe & Secure</strong><br>
                    <span>This is our official APK. No malware, no tracking. Your data stays private.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="features-section">
        <div class="container">
            <h2>🚀 App Features</h2>
            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">🏠</div>
                        <h5>Browse Properties</h5>
                        <p>Search and filter plots, houses, and commercial properties with ease.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">📍</div>
                        <h5>Site Visits</h5>
                        <p>Schedule and manage property site visits directly from the app.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">📊</div>
                        <h5>EMI Tracker</h5>
                        <p>Track your EMI payments, view schedules, and download receipts.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">📞</div>
                        <h5>Direct Contact</h5>
                        <p>Call or WhatsApp agents directly from property listings.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">❤️</div>
                        <h5>Favorites</h5>
                        <p>Save your favorite properties and compare them side by side.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">🔔</div>
                        <h5>Push Notifications</h5>
                        <p>Get instant alerts for new properties, price drops, and booking updates.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">🌐</div>
                        <h5>Colony Maps</h5>
                        <p>Explore interactive colony maps with color-coded plot availability.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">📄</div>
                        <h5>Document Locker</h5>
                        <p>Upload and access your property documents anytime, anywhere.</p>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="feature-card">
                        <div class="icon">📈</div>
                        <h5>MLM Dashboard</h5>
                        <p>Associates can track network, commissions, and team performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="how-to-section">
        <div class="container">
            <h2>📲 How to Install</h2>
            <div class="row justify-content-center">
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h6>Download APK</h6>
                        <p class="text-muted small">Tap the download button above</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h6>Allow Unknown Apps</h6>
                        <p class="text-muted small">Enable install from unknown sources in Settings</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h6>Install</h6>
                        <p class="text-muted small">Open the downloaded APK file</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h6>Start Exploring</h6>
                        <p class="text-muted small">Login and find your dream property!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="faq-section">
        <div class="container">
            <h2>❓ FAQs</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Is the app free?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">Yes, the app is completely free to download and use.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Do I need to register again?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">No, use your existing APS Dream Home website credentials to login.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Is my data safe?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">Absolutely. All data is encrypted and transmitted securely. We never share your personal information.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Which Android versions are supported?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">Android 8.0 (Oreo) and above. The app is optimized for all screen sizes.</div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Will there be an iOS version?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">An iOS version is in development. Stay tuned for updates!</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center py-4 bg-light border-top">
        <p class="mb-0 text-muted">
            <i class="fas fa-code-branch me-1"></i>
            App Version <?= $app_version ?> &middot; Last updated <?= $updated_date ?>
            &middot; <a href="<?= BASE_URL ?>/contact" class="text-decoration-none">Report Issue</a>
        </p>
    </div>
</div>

<script>
function toggleFaq(el) {
    var answer = el.nextElementSibling;
    var icon = el.querySelector('i');
    answer.classList.toggle('show');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
}
</script>
