<?php
// About Page - APS Dream Homes Private Limited
// Dynamic content from DB ($siteContent) with __() lang fallback

$sc = $siteContent ?? [];

function aboutContent($sc, $key, $fallbackKey = '') {
    if (!empty($sc[$key])) return $sc[$key];
    return $fallbackKey ? __($fallbackKey) : '';
}
?>

<!-- ============================================ -->
<!-- PREMIUM HERO SECTION -->
<!-- ============================================ -->
<section class="about-hero-section position-relative overflow-hidden">
    <!-- Ambient glow backgrounds -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    
    <div class="container position-relative z-1">
        <div class="row align-items-center g-5">
            <!-- Left Text Content -->
            <div class="col-lg-7">
                <div class="hero-badge mb-3">
                    <i class="fas fa-building text-teal"></i> 
                    <span>EST. APRIL 2022 &bull; CIN: U70109UP2022PTC163047</span>
                </div>
                
                <h1 class="display-3 fw-bolder mb-4 hero-title">
                    Building Dreams,<br>
                    <span class="gradient-text">Creating Communities</span>
                </h1>
                
                <p class="lead mb-5 hero-subtitle">
                    APS Dream Homes Private Limited is a trusted name in real estate across Eastern Uttar Pradesh, 
                    transforming land into thriving communities since 2022.
                </p>
                
                <div class="d-flex flex-wrap gap-3">
                    <a href="#leadership" class="btn btn-primary btn-lg px-4 rounded-pill shadow-sm custom-btn-primary">
                        <i class="fas fa-users me-2"></i> Meet Our Team
                    </a>
                    <a href="#story" class="btn btn-outline-secondary btn-lg px-4 rounded-pill custom-btn-outline">
                        Our Story <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Right Stats Section (Glassmorphism Cards) -->
            <div class="col-lg-5 d-none d-lg-block">
                <div class="glass-stats-wrapper">
                    <div class="glass-icon-header">
                        <div class="icon-circle">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                    
                    <div class="glass-stats-grid">
                        <div class="glass-stat-item">
                            <h3 class="hero-stat-number">4<span class="text-teal">+</span></h3>
                            <p class="stat-label">Active Projects</p>
                        </div>
                        <div class="glass-stat-item">
                            <h3 class="hero-stat-number">5000<span class="text-teal">+</span></h3>
                            <p class="stat-label">Plots Sold</p>
                        </div>
                        <div class="glass-stat-item">
                            <h3 class="hero-stat-number">500<span class="text-teal">+</span></h3>
                            <p class="stat-label">Happy Families</p>
                        </div>
                        <div class="glass-stat-item">
                            <h3 class="hero-stat-number">4<span class="text-teal">+</span></h3>
                            <p class="stat-label">Colonies</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ---- Premium About Hero ---- */
.about-hero-section {
    background: #0f172a;
    padding: 100px 0 120px;
    color: #f8fafc;
    min-height: 80vh;
    display: flex;
    align-items: center;
}

.ambient-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.5;
    z-index: 0;
    pointer-events: none;
}

.glow-1 {
    top: -10%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: rgba(13, 148, 136, 0.3); /* Teal */
}

.glow-2 {
    bottom: -20%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: rgba(56, 189, 248, 0.2); /* Sky Blue */
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 1px;
    backdrop-filter: blur(10px);
}

.text-teal {
    color: #2dd4bf;
}

.about-hero-section .hero-title {
    font-size: 4rem;
    line-height: 1.1;
    letter-spacing: -1px;
    color: #f8fafc !important;
}

.about-hero-section .gradient-text {
    background: linear-gradient(135deg, #2dd4bf, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.about-hero-section .hero-subtitle {
    color: #94a3b8 !important;
    font-size: 1.25rem;
    line-height: 1.7;
    max-width: 90%;
}

.custom-btn-primary {
    background: linear-gradient(135deg, #0d9488, #0284c7);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.custom-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(13, 148, 136, 0.4);
    color: white;
}

.custom-btn-outline {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #e2e8f0;
    transition: all 0.3s ease;
}

.custom-btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.4);
    color: #ffffff;
}

/* Glassmorphism Stats Card */
.glass-stats-wrapper {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 40px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    position: relative;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.glass-stats-wrapper:hover {
    transform: translateY(-10px);
    box-shadow: 0 35px 60px -15px rgba(45, 212, 191, 0.3);
}

.glass-icon-header {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}

.icon-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #2dd4bf, #38bdf8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    box-shadow: 0 10px 25px rgba(45, 212, 191, 0.3);
}

.glass-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px 20px;
}

.glass-stat-item {
    text-align: center;
}

.glass-stat-item .hero-stat-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: #f8fafc;
    margin-bottom: 5px;
}

.glass-stat-item .stat-label {
    color: #94a3b8;
    font-size: 0.95rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}

@media (max-width: 991px) {
    .about-hero-section .hero-title {
        font-size: 3rem;
    }
}

/* ---- About Page Global ---- */
.about-story{padding:80px 0;background:#fff}
.about-story .story-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(13,148,136,0.08);color:#0d9488;font-size:0.75rem;font-weight:700;padding:6px 16px;border-radius:50px;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:16px}
.about-story .story-title{font-size:2.2rem;font-weight:800;color:#1e293b;margin-bottom:20px;letter-spacing:-0.5px}
.about-story .story-title span{background:linear-gradient(135deg,#0d9488,#0f766e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.about-story .story-text{color:#475569;font-size:1rem;line-height:1.8}
.about-story .story-detail{background:#f8fafc;border-radius:16px;padding:20px;border-left:4px solid #0d9488;margin-bottom:16px}
.about-story .story-detail h6{font-weight:700;color:#1e293b;margin-bottom:6px;font-size:0.95rem}
.about-story .story-detail p{color:#64748b;font-size:0.88rem;margin:0;line-height:1.6}

/* ---- Company Highlights ---- */
.about-highlights{padding:60px 0;background:#f8fafc}
.highlight-card{background:#fff;border-radius:16px;padding:28px;text-align:center;border:none;box-shadow:0 2px 12px rgba(0,0,0,0.04);transition:all 0.3s;height:100%}
.highlight-card:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,0.08)}
.highlight-card .icon-wrap{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.4rem}
.highlight-card h5{font-weight:700;color:#1e293b;margin-bottom:8px;font-size:1rem}
.highlight-card p{color:#64748b;font-size:0.85rem;margin:0;line-height:1.6}

/* ---- Leadership ---- */
.about-leaders{padding:80px 0;background:#fff}
.about-leaders .section-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(13,148,136,0.08);color:#0d9488;font-size:0.75rem;font-weight:700;padding:6px 16px;border-radius:50px;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:16px}
.about-leaders .section-title{font-size:2rem;font-weight:800;color:#1e293b;margin-bottom:12px;letter-spacing:-0.5px}
.about-leaders .section-title span{background:linear-gradient(135deg,#0d9488,#0f766e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.about-leaders .section-sub{color:#64748b;font-size:0.95rem;margin-bottom:40px;max-width:600px}

.leader-card{background:#fff;border-radius:20px;overflow:hidden;border:none;box-shadow:0 4px 24px rgba(0,0,0,0.05);transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);position:relative}
.leader-card:hover{transform:translateY(-10px);box-shadow:0 20px 50px rgba(0,0,0,0.12)}
.leader-card .leader-img-wrap{position:relative;height:280px;overflow:hidden}
.leader-card .leader-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform 0.6s ease}
.leader-card:hover .leader-img-wrap img{transform:scale(1.08)}
.leader-card .leader-img-wrap::after{content:'';position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(transparent,rgba(0,0,0,0.5))}
.leader-card .leader-badge{position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.9);backdrop-filter:blur(10px);border-radius:10px;padding:6px 14px;font-size:0.72rem;font-weight:700;color:#0d9488;display:flex;align-items:center;gap:4px}
.leader-card .leader-body{padding:24px}
.leader-card .leader-name{font-size:1.15rem;font-weight:800;color:#1e293b;margin-bottom:2px}
.leader-card .leader-role{color:#0d9488;font-size:0.82rem;font-weight:600;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px}
.leader-card .leader-exp{color:#64748b;font-size:0.78rem;margin-bottom:10px;display:flex;align-items:center;gap:4px}
.leader-card .leader-exp i{color:#f59e0b}
.leader-card .leader-bio{color:#475569;font-size:0.88rem;line-height:1.6}

/* ---- Department Heads ---- */
.dept-heads{padding:60px 0;background:#f8fafc}
.dept-card{background:#fff;border-radius:16px;padding:24px;text-align:center;border:none;box-shadow:0 2px 12px rgba(0,0,0,0.04);transition:all 0.3s;height:100%}
.dept-card:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,0.08)}
.dept-card .dept-avatar{width:100px;height:100px;border-radius:50%;overflow:hidden;margin:0 auto 16px;border:4px solid #f1f5f9;transition:all 0.3s}
.dept-card:hover .dept-avatar{border-color:#0d9488;transform:scale(1.05)}
.dept-card .dept-avatar img{width:100%;height:100%;object-fit:cover}
.dept-card h5{font-weight:700;color:#1e293b;margin-bottom:4px;font-size:1rem}
.dept-card .dept-role{color:#0d9488;font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px}
.dept-card .dept-exp{color:#64748b;font-size:0.78rem;margin-bottom:10px}
.dept-card p{color:#475569;font-size:0.85rem;line-height:1.6;margin:0}

/* ---- Mission Vision Values ---- */
.about-mvv{padding:80px 0;background:#fff}
.mvv-card{background:#fff;border-radius:20px;padding:32px;border:1px solid #f1f5f9;transition:all 0.3s;height:100%;position:relative;overflow:hidden}
.mvv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:20px 20px 0 0}
.mvv-card.mission::before{background:linear-gradient(135deg,#0d9488,#06b6d4)}
.mvv-card.vision::before{background:linear-gradient(135deg,#3b82f6,#14b8a6)}
.mvv-card.values::before{background:linear-gradient(135deg,#f59e0b,#ef4444)}
.mvv-card:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,0.08)}
.mvv-card .mvv-icon{width:56px;height:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:16px}
.mvv-card h4{font-weight:800;color:#1e293b;margin-bottom:12px;font-size:1.15rem}
.mvv-card p{color:#64748b;font-size:0.9rem;line-height:1.7;margin:0}

/* ---- Stats Panel ---- */
.about-stats{padding:80px 0;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0d9488 100%);position:relative;overflow:hidden}
.about-stats::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,0.05) 0%,transparent 60%);animation:aboutGlow 8s ease-in-out infinite alternate}
@keyframes aboutGlow{0%{transform:translate(0,0)}100%{transform:translate(-10%,10%)}}
.about-stats .stat-box{background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:28px 20px;text-align:center;transition:all 0.3s}
.about-stats .stat-box:hover{background:rgba(255,255,255,0.15);transform:translateY(-4px)}
.about-stats .stat-box .stat-num{font-size:2.2rem;font-weight:800;line-height:1;margin-bottom:6px}
.about-stats .stat-box .stat-label{font-size:0.78rem;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.5px;font-weight:600}

/* ---- Office Locations ---- */
.about-offices{padding:80px 0;background:#f8fafc}
.office-card{background:#fff;border-radius:20px;overflow:hidden;border:none;box-shadow:0 4px 24px rgba(0,0,0,0.05);transition:all 0.3s;height:100%}
.office-card:hover{transform:translateY(-6px);box-shadow:0 16px 40px rgba(0,0,0,0.1)}
.office-card .office-header{background:linear-gradient(135deg,#0d9488,#0f766e);padding:20px 24px;color:#fff}
.office-card .office-header h5{font-weight:700;margin:0;font-size:1.05rem}
.office-card .office-header small{opacity:0.8;font-size:0.8rem}
.office-card .office-body{padding:24px}
.office-card .office-body .info-row{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px}
.office-card .office-body .info-row i{width:36px;height:36px;border-radius:10px;background:#f0fdfa;color:#0d9488;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.85rem}
.office-card .office-body .info-row div{flex:1}
.office-card .office-body .info-row .label{font-size:0.72rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;margin-bottom:2px}
.office-card .office-body .info-row .value{color:#1e293b;font-size:0.9rem;font-weight:600}

/* ---- Certifications ---- */
.about-certs{padding:60px 0;background:#fff}
.cert-card{background:#f8fafc;border-radius:16px;padding:24px;text-align:center;border:1px solid #f1f5f9;transition:all 0.3s;height:100%}
.cert-card:hover{background:#fff;box-shadow:0 8px 24px rgba(0,0,0,0.06);transform:translateY(-4px)}
.cert-card .cert-icon{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.5rem}
.cert-card h6{font-weight:700;color:#1e293b;margin-bottom:6px;font-size:0.95rem}
.cert-card p{color:#64748b;font-size:0.82rem;margin:0;line-height:1.5}

/* ---- Service Cards (clickable) ---- */
.service-card{cursor:pointer;position:relative}
.service-card:focus{outline:2px solid #0d9488;outline-offset:3px}
.service-more{display:inline-flex;align-items:center;gap:6px;margin-top:14px;color:#0d9488;font-size:0.8rem;font-weight:700;opacity:0;transform:translateX(-6px);transition:all 0.3s}
.service-card:hover .service-more{opacity:1;transform:translateX(0)}
.service-card .icon-wrap{transition:transform 0.3s}
.service-card:hover .icon-wrap{transform:scale(1.08)}

/* ---- Service Modal ---- */
.service-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:all 0.3s}
.service-modal-overlay.active{opacity:1;visibility:visible}
.service-modal{background:#fff;border-radius:24px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 30px 80px rgba(0,0,0,0.3);transform:translateY(30px) scale(0.96);transition:all 0.35s cubic-bezier(0.175,0.885,0.32,1.275);position:relative}
.service-modal-overlay.active .service-modal{transform:translateY(0) scale(1)}
.service-modal-close{position:absolute;top:16px;right:18px;width:36px;height:36px;border:none;background:rgba(255,255,255,0.2);color:#fff;border-radius:50%;font-size:1.5rem;line-height:1;cursor:pointer;z-index:3;transition:all 0.2s}
.service-modal-close:hover{background:rgba(255,255,255,0.35);transform:rotate(90deg)}
.service-modal-header{display:flex;align-items:center;gap:18px;padding:32px 32px 24px;color:#fff;background:linear-gradient(135deg,#0d9488,#0f766e)}
.service-modal-icon{width:64px;height:64px;border-radius:18px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0}
.service-modal-header h3{font-weight:800;margin:0;font-size:1.4rem}
.service-modal-body{padding:28px 32px}
.service-modal-desc{color:#475569;font-size:0.95rem;line-height:1.7;margin-bottom:22px}
.service-modal-steps-title{font-weight:700;color:#1e293b;font-size:0.95rem;margin-bottom:14px}
.service-modal-steps{margin:0;padding:0;list-style:none;counter-reset:step}
.service-modal-steps li{counter-increment:step;position:relative;padding:10px 0 10px 44px;border-bottom:1px solid #f1f5f9;color:#475569;font-size:0.9rem;line-height:1.5}
.service-modal-steps li:last-child{border-bottom:none}
.service-modal-steps li::before{content:counter(step);position:absolute;left:0;top:8px;width:30px;height:30px;border-radius:50%;background:#f0fdfa;color:#0d9488;font-weight:800;font-size:0.85rem;display:flex;align-items:center;justify-content:center}
.service-modal-footer{display:flex;gap:12px;padding:0 32px 32px}
.service-modal-btn-primary{display:inline-flex;align-items:center;gap:8px;flex:1;justify-content:center;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;padding:14px 24px;border-radius:12px;font-weight:700;text-decoration:none;font-size:0.9rem;transition:all 0.3s}
.service-modal-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(13,148,136,0.35);color:#fff}
.service-modal-btn-secondary{background:#f1f5f9;color:#475569;padding:14px 24px;border:none;border-radius:12px;font-weight:700;font-size:0.9rem;cursor:pointer;transition:all 0.3s}
.service-modal-btn-secondary:hover{background:#e2e8f0}

/* ---- Hero Responsive ---- */
@media(max-width:768px){
  section.position-relative[style*="min-height:500px"]{min-height:350px!important;padding:80px 0 50px!important}
  .display-3{font-size:1.8rem!important}
}
</style>

<!-- ============================================ -->
<!-- COMPANY STORY -->
<!-- ============================================ -->
<section class="about-story" id="story">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="story-badge"><i class="fas fa-building"></i> Our Journey</div>
                <h2 class="story-title">The <span>APS Dream Homes</span> Story</h2>
                <p class="story-text mb-4">
                    APS Dream Homes Private Limited was incorporated on <strong>April 26, 2022</strong> under the 
                    Companies Act, 2013 (CIN: U70109UP2022PTC163047). Registered with the Registrar of Companies, 
                    Kanpur, we set out with a singular vision — to make quality housing accessible to families in 
                    Eastern Uttar Pradesh.
                </p>
                <p class="story-text mb-4">
                    What started as a small venture in Gorakhpur has grown into a trusted name spanning 
                    <strong>Gorakhpur, Lucknow, and Kushinagar</strong>. Our founder, <strong>Mr. Abhay Kumar Singh</strong>, 
                    envisioned a company where every transaction would be transparent, every document legally sound, 
                    and every customer treated as family.
                </p>
                <p class="story-text">
                    Today, with over <strong>200+ plots delivered</strong> and <strong>500+ happy families</strong>, 
                    we continue to uphold the values of trust, quality, and community development that formed the 
                    foundation of our company.
                </p>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="story-detail">
                    <h6><i class="fas fa-calendar-alt me-2" class="style-5793"></i> Founded</h6>
                    <p>April 26, 2022 &bull; Incorporated under the Companies Act, 2013</p>
                </div>
                <div class="story-detail">
                    <h6><i class="fas fa-map-marker-alt me-2" class="style-5793"></i> Headquarters</h6>
                    <p>Virat Bhawan, Singhariya Kunraghat, Gorakhpur, UP 273008</p>
                </div>
                <div class="story-detail">
                    <h6><i class="fas fa-briefcase me-2" class="style-5793"></i> Industry</h6>
                    <p>Real Estate Activities (NIC Code: 7010) — Plot & Land, Residential Floors</p>
                </div>
                <div class="story-detail">
                    <h6><i class="fas fa-chart-line me-2" class="style-5793"></i> Capital</h6>
                    <p>Authorized & Paid-up Capital: ₹10,00,000</p>
                </div>
                <div class="story-detail">
                    <h6><i class="fas fa-star me-2" class="style-5793"></i> Rating</h6>
                    <p>5.0/5 Stars on India Online &bull; Trusted by 500+ Families</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- COMPANY HIGHLIGHTS / SERVICES -->
<!-- ============================================ -->
<section class="about-highlights">
    <div class="container">
        <div class="text-center mb-5">
            <div class="style-74105">
                <i class="fas fa-concierge-bell"></i> What We Do
            </div>
            <h2 class="style-84813">Our <span class="style-79458">Services</span></h2>
            <p class="style-95961">End-to-end real estate solutions — from finding the perfect plot to handing over the keys.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="plot-selling" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-75269">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h5>Plot Selling</h5>
                    <p>Residential & commercial plots in gated colonies across Gorakhpur, Lucknow, Kushinagar & Prayagraj. RERA registered with clear titles.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="construction" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-46608">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <h5>Construction & Development</h5>
                    <p>Complete colony development with roads, drainage, water supply, electricity, parks and community spaces. From raw land to livable neighborhoods.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="legal" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-64138">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h5>Legal & Documentation</h5>
                    <p>In-house legal team handles title verification, sale agreements, registry, mutation, and all paperwork. Every deal is legally airtight.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="payment" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-10633">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h5>Flexible Payment Plans</h5>
                    <p>EMI options, easy installment plans, and transparent pricing. No hidden charges — what you see is what you pay.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="resale" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-547">
                        <i class="fas fa-home"></i>
                    </div>
                    <h5>Resale & Resale Assistance</h5>
                    <p>Want to sell your plot? We help you find genuine buyers, handle documentation, and ensure fair market value for your property.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="site-visit" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-2487">
                        <i class="fas fa-car"></i>
                    </div>
                    <h5>Free Site Visits</h5>
                    <p>Visit any of our colonies before you buy. Our team arranges guided site visits with transport from Gorakhpur, Lucknow or nearby areas.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="flats" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-98426">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h5>Furnished Flats</h5>
                    <p>Ready-to-move flats in APS Heights, Prayagraj. 2BHK & 3BHK options with modern fittings, modular kitchen and parking.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="highlight-card service-card scroll-reveal" data-service="community" role="button" tabindex="0">
                    <div class="icon-wrap" class="style-62789">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>Community Building</h5>
                    <p>We don't just sell plots — we build neighborhoods. Parks, temples, schools nearby, and community events for all residents.</p>
                    <span class="service-more">Explore <i class="fas fa-arrow-right"></i></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- SERVICE DETAIL MODAL -->
<!-- ============================================ -->
<div class="service-modal-overlay" id="serviceModal" aria-hidden="true">
    <div class="service-modal" role="dialog" aria-modal="true" aria-labelledby="serviceModalTitle">
        <button class="service-modal-close" id="serviceModalClose" aria-label="Close">&times;</button>
        <div class="service-modal-header" id="serviceModalHeader">
            <div class="service-modal-icon" id="serviceModalIcon"></div>
            <div>
                <h3 id="serviceModalTitle"></h3>
                <p id="serviceModalSubtitle" class="style-43781"></p>
            </div>
        </div>
        <div class="service-modal-body">
            <p id="serviceModalDesc" class="service-modal-desc"></p>
            <h6 class="service-modal-steps-title"><i class="fas fa-list-check me-2"></i> How It Works</h6>
            <ol class="service-modal-steps" id="serviceModalSteps"></ol>
        </div>
        <div class="service-modal-footer">
            <button class="service-modal-btn-secondary" id="serviceModalClose2">Close</button>
            <a class="service-modal-btn-primary" id="serviceModalLink" href="#">Learn More &nbsp;<i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- LEADERSHIP TEAM (from DB) -->
<!-- ============================================ -->
<section class="about-leaders" id="leadership">
    <div class="container">
        <div class="section-badge"><i class="fas fa-crown"></i> Leadership Team</div>
        <h2 class="section-title">Meet the <span>People Behind</span> the Vision</h2>
        <p class="section-sub">Our leadership combines decades of experience in real estate, finance, and customer relations to deliver exceptional value.</p>
        
        <div class="row g-4">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="col-lg-4 col-md-6">
                <div class="leader-card scroll-reveal">
                    <div class="leader-img-wrap">
                        <?php 
                        $photo = aboutContent($sc, "leader_{$i}_photo");
                        $name = aboutContent($sc, "leader_{$i}_name", "about_leader_{$i}_name");
                        $role = aboutContent($sc, "leader_{$i}_role", "about_leader_{$i}_role");
                        $exp = aboutContent($sc, "leader_{$i}_exp", "about_leader_{$i}_exp");
                        $bio = aboutContent($sc, "leader_{$i}_bio", "about_leader_{$i}_bio");
                        ?>
                         <?php $imgRaw = $photo ?? '';
                               $imgSrc = (str_starts_with($imgRaw, 'http://') || str_starts_with($imgRaw, 'https://')) ? $imgRaw : BASE_URL . '/' . $imgRaw; ?>
                         <img loading="lazy" 
                              src="<?= htmlspecialchars($imgSrc ?? '') ?>" 
                              alt="<?= htmlspecialchars($name ?? '') ?>">
                        <div class="leader-badge"><i class="fas fa-crown"></i> <?= $i === 1 ? 'Founder' : ($role === 'Senior Property Advisor' ? 'Advisor' : 'Director') ?></div>
                    </div>
                    <div class="leader-body">
                        <h5 class="leader-name"><?= htmlspecialchars($name ?? '') ?></h5>
                        <div class="leader-role"><?= htmlspecialchars($role ?? '') ?></div>
                        <div class="leader-exp"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($exp ?? '') ?></div>
                        <p class="leader-bio"><?= htmlspecialchars($bio ?? '') ?></p>
                    </div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- DEPARTMENT HEADS (from DB) -->
<!-- ============================================ -->
<section class="dept-heads">
    <div class="container">
        <div class="text-center mb-5">
            <div class="style-74105">
                <i class="fas fa-user-tie"></i> Department Heads
            </div>
            <h2 class="style-14450">
                The <span class="style-79458">Experts</span> Leading Our Teams
            </h2>
            <p class="style-95961">
                Each department is led by experienced professionals committed to excellence.
            </p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php for ($i = 4; $i <= 6; $i++): ?>
            <?php
            $dName = aboutContent($sc, "leader_{$i}_name");
            $dRole = aboutContent($sc, "leader_{$i}_role");
            $dExp = aboutContent($sc, "leader_{$i}_exp");
            $dBio = aboutContent($sc, "leader_{$i}_bio");
            $dPhoto = aboutContent($sc, "leader_{$i}_photo");
            $dDept = aboutContent($sc, "leader_{$i}_dept");
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="dept-card scroll-reveal">
                    <div class="dept-avatar">
                        <img loading="lazy" 
                             src="<?= BASE_URL ?>/<?= htmlspecialchars($dPhoto ?? '') ?>" 
                             alt="<?= htmlspecialchars($dName ?? '') ?>">
                    </div>
                    <h5><?= htmlspecialchars($dName ?? '') ?></h5>
                    <div class="dept-role"><?= htmlspecialchars($dRole ?? '') ?></div>
                    <div class="dept-exp"><i class="fas fa-briefcase me-1" class="style-62735"></i> <?= htmlspecialchars($dExp ?? '') ?></div>
                    <p><?= htmlspecialchars($dBio ?? '') ?></p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- MISSION, VISION, VALUES -->
<!-- ============================================ -->
<section class="about-mvv">
    <div class="container">
        <div class="text-center mb-5">
            <div class="style-74105">
                <i class="fas fa-bullseye"></i> Our Foundation
            </div>
            <h2 class="style-84813">
                Mission, Vision & <span class="style-79458">Values</span>
            </h2>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="mvv-card mission scroll-reveal">
                    <div class="mvv-icon" class="style-75269">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4>Our Mission</h4>
                    <p>To provide affordable, quality housing solutions to families in Eastern Uttar Pradesh through 
                    transparent dealings, legal compliance, and customer-first approach. We aim to make the dream 
                    of homeownership a reality for every Indian family.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="mvv-card vision scroll-reveal">
                    <div class="mvv-icon" class="style-46608">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4>Our Vision</h4>
                    <p>To become the most trusted real estate brand in Eastern UP, known for integrity, quality 
                    construction, and community development. We envision thriving neighborhoods where families 
                    grow, children play, and communities flourish.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="mvv-card values scroll-reveal">
                    <div class="mvv-icon" class="style-64138">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Our Values</h4>
                    <p><strong>Trust</strong> in every transaction. <strong>Transparency</strong> in every deal. 
                    <strong>Quality</strong> in every plot. <strong>Community</strong> in every project. These are 
                    not just words — they are the foundation of every decision we make.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- STATS SECTION -->
<!-- ============================================ -->
<section class="about-stats">
    <div class="container position-relative" class="style-9174">
        <div class="text-center mb-5">
            <h2 class="style-69158">Our Numbers Speak</h2>
            <p class="style-57302">Building trust one project at a time</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-lg-3">
                <div class="stat-box scroll-reveal">
                    <div class="stat-num" class="style-2154">5000+</div>
                    <div class="stat-label">Plots Sold</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box scroll-reveal">
                    <div class="stat-num" class="style-64047">500+</div>
                    <div class="stat-label">Happy Families</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box scroll-reveal">
                    <div class="stat-num" class="style-62735">4+</div>
                    <div class="stat-label">Colonies Delivered</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box scroll-reveal">
                    <div class="stat-num" class="style-80751">3</div>
                    <div class="stat-label">Cities Covered</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- OFFICE LOCATIONS -->
<!-- ============================================ -->
<section class="about-offices">
    <div class="container">
        <div class="text-center mb-5">
            <div class="style-74105">
                <i class="fas fa-map-marked-alt"></i> Our Offices
            </div>
            <h2 class="style-84813">Visit Us <span class="style-79458">Anywhere</span></h2>
        </div>
        
        <div class="row g-4">
            <!-- Head Office -->
            <div class="col-lg-4 col-md-6">
                <div class="office-card scroll-reveal">
                    <div class="office-header">
                        <h5><i class="fas fa-building me-2"></i> Head Office</h5>
                        <small>Gorakhpur, Uttar Pradesh</small>
                    </div>
                    <div class="office-body">
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <div class="label">Address</div>
                                <div class="value">C/o Mahesh Chand Agrahri, H.no-1180, 1st Floor, Virat Bhawan, Singhariya Kunraghat, Gorakhpur - 273008</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <div>
                                <div class="label">Phone</div>
                                <div class="value">+91 9918061919</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <div class="label">Email</div>
                                <div class="value">apsdreamhome@gmail.com</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Working Office -->
            <div class="col-lg-4 col-md-6">
                <div class="office-card scroll-reveal">
                    <div class="office-header" class="style-85097">
                        <h5><i class="fas fa-hard-hat me-2"></i> Working Office</h5>
                        <small>Gorakhpur, Uttar Pradesh</small>
                    </div>
                    <div class="office-body">
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <div class="label">Address</div>
                                <div class="value">Near Ganpati Lawn, Singhariya Kunraghat, Gorakhpur - 274008</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="label">Hours</div>
                                <div class="value">Mon - Sat: 9:00 AM - 7:00 PM</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-map-pin"></i>
                            <div>
                                <div class="label">Location</div>
                                <div class="value">Near Ganpati Lawn, Kunraghat Area</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sehwan Office -->
            <div class="col-lg-4 col-md-6">
                <div class="office-card scroll-reveal">
                    <div class="office-header" class="style-21462">
                        <h5><i class="fas fa-store me-2"></i> Sehwan Office</h5>
                        <small>Pachrukhi, Gorakhpur</small>
                    </div>
                    <div class="office-body">
                        <div class="info-row">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <div class="label">Address</div>
                                <div class="value">Pachrukhi Market, Near Shiv Mandir, Thaney ke Bagal me, Gorakhpur</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-landmark"></i>
                            <div>
                                <div class="label">Landmark</div>
                                <div class="value">Shiv Mandir ke Bagal me, Pachrukhi Market</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="label">Hours</div>
                                <div class="value">Mon - Sat: 9:00 AM - 7:00 PM</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Across Cities -->
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="style-80841">
                    <div class="style-78042">
                        <h5 class="style-77763"><i class="fas fa-project-diagram me-2"></i> Our Projects Across Cities</h5>
                    </div>
                    <div class="style-57825">
                        <div class="row g-4">
                            <div class="col-md-3 col-6">
                                <div class="style-88083">
                                    <div class="style-57901">
                                        <i class="fas fa-city" class="style-67779"></i>
                                    </div>
                                    <h6 class="style-22646">Gorakhpur</h6>
                                    <p class="style-724">Suryoday Colony (1050+)</p>
                                    <p class="style-724">Braj Radha Nagri (1550+)</p>
                                    <p class="style-724">Raghunath Nagri (780+)</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="style-88083">
                                    <div class="style-35621">
                                        <i class="fas fa-city" class="style-26370"></i>
                                    </div>
                                    <h6 class="style-22646">Lucknow</h6>
                                    <p class="style-724">APS Valley (800+)</p>
                                    <p class="style-724">Awadhpuri</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="style-88083">
                                    <div class="style-12044">
                                        <i class="fas fa-city" class="style-57730"></i>
                                    </div>
                                    <h6 class="style-22646">Kushinagar</h6>
                                    <p class="style-724">Budh Bihar Colony (1280+)</p>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="style-88083">
                                    <div class="style-44154">
                                        <i class="fas fa-building" class="style-94843"></i>
                                    </div>
                                    <h6 class="style-22646">Prayagraj</h6>
                                    <p class="style-724">APS Heights (200 Flats)</p>
                                    <p class="style-724">Naini, ₹65 Lakh+</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CERTIFICATIONS -->
<!-- ============================================ -->
<section class="about-certs">
    <div class="container">
        <div class="text-center mb-5">
            <div class="style-74105">
                <i class="fas fa-certificate"></i> Legal & Compliance
            </div>
            <h2 class="style-84813">Registered & <span class="style-79458">Certified</span></h2>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-3 col-6">
                <div class="cert-card scroll-reveal">
                    <div class="cert-icon" class="style-75269">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h6>CIN Number</h6>
                    <p>U70109UP2022PTC163047</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="cert-card scroll-reveal">
                    <div class="cert-icon" class="style-46608">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <h6>ROC Kanpur</h6>
                    <p>Registered with Registrar of Companies, Kanpur</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="cert-card scroll-reveal">
                    <div class="cert-icon" class="style-64138">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h6>UP RERA</h6>
                    <p>Fully compliant with UP Real Estate Regulatory Authority</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="cert-card scroll-reveal">
                    <div class="cert-icon" class="style-10633">
                        <i class="fas fa-star"></i>
                    </div>
                    <h6>5.0 Rating</h6>
                    <p>Top rated on India Online platform</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CTA SECTION -->
<!-- ============================================ -->
<section class="style-69610">
    <div class="style-80207"></div>
    <div class="container position-relative" class="style-9174">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="style-19049">
                    Ready to Find Your Dream Plot?
                </h2>
                <p class="style-40126">
                    Join 500+ families who trust APS Dream Homes for their property needs. 
                    Browse our available plots or schedule a site visit today.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?= BASE_URL ?>/properties" class="style-15737">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
                <a href="<?= BASE_URL ?>/contact" class="style-29836">
                    <i class="fas fa-phone"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Scroll Reveal Init + Service Modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll reveal
    const reveals = document.querySelectorAll('.scroll-reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    reveals.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        observer.observe(el);
    });

    // Service modal data
    const SERVICES = {
        'plot-selling': {
            title: 'Plot Selling',
            subtitle: 'RERA-registered plots with clear titles',
            icon: 'fas fa-map-marked-alt',
            desc: 'Residential & commercial plots in gated colonies across Gorakhpur, Lucknow, Kushinagar & Prayagraj. Every plot comes with clear legal title, RERA registration, and transparent pricing — so you invest with complete peace of mind.',
            steps: [
                'Browse available plots & colonies on our listings',
                'Shortlist by location, budget & plot size',
                'Book your plot with a token amount',
                'Complete registry & get possession of your land'
            ],
            link: '<?= BASE_URL ?>/properties',
            linkText: 'Browse Plots'
        },
        'construction': {
            title: 'Construction & Development',
            subtitle: 'From raw land to livable neighborhoods',
            icon: 'fas fa-hard-hat',
            desc: 'We handle complete colony development — internal roads, drainage, 24x7 water supply, electricity, street lighting, parks and community spaces. Our in-house team manages the entire lifecycle from land acquisition to a finished, livable township.',
            steps: [
                'Land survey, planning & layout design',
                'Infrastructure: roads, drainage, water & power',
                'Amenities: parks, lighting, community spaces',
                'Quality checks & handover-ready colonies'
            ],
            link: '<?= BASE_URL ?>/construction-services',
            linkText: 'View Developments'
        },
        'legal': {
            title: 'Legal & Documentation',
            subtitle: 'Every deal is legally airtight',
            icon: 'fas fa-file-contract',
            desc: 'Our in-house legal team handles title verification, sale agreements, registry, mutation and all paperwork. We ensure a clean, dispute-free transfer of ownership so your investment is fully protected.',
            steps: [
                'Title & document verification',
                'Drafting of sale agreement',
                'Registry & stamp duty processing',
                'Mutation & post-sale documentation'
            ],
            link: '<?= BASE_URL ?>/legal/services',
            linkText: 'Legal Services'
        },
        'payment': {
            title: 'Flexible Payment Plans',
            subtitle: 'No hidden charges, ever',
            icon: 'fas fa-hand-holding-usd',
            desc: 'Choose from EMI options and easy installment plans designed around your cash flow. Transparent pricing means what you see is exactly what you pay — no surprises, no hidden charges.',
            steps: [
                'Pick a plan: EMI or installment',
                'Pay a comfortable booking amount',
                'Schedule payments across the tenure',
                'Clear dues & receive registry on completion'
            ],
            link: '<?= BASE_URL ?>/contact',
            linkText: 'Talk to an Advisor'
        },
        'resale': {
            title: 'Resale & Resale Assistance',
            subtitle: 'Sell at fair market value',
            icon: 'fas fa-home',
            desc: 'Want to sell your plot? We connect you with genuine, verified buyers, handle all the documentation, and ensure you get fair market value — making resale simple and secure.',
            steps: [
                'List your property with us',
                'We match verified interested buyers',
                'Negotiation & fair-value agreement',
                'Documentation & secure handover'
            ],
            link: '<?= BASE_URL ?>/resell',
            linkText: 'Sell Your Plot'
        },
        'site-visit': {
            title: 'Free Site Visits',
            subtitle: 'See before you decide',
            icon: 'fas fa-car',
            desc: 'Visit any of our colonies before you buy. Our team arranges guided site visits with transport from Gorakhpur, Lucknow or nearby areas — so you can experience the location, roads and surroundings firsthand.',
            steps: [
                'Request a site visit (call or form)',
                'We schedule & arrange transport',
                'Guided tour with our property expert',
                'Get clarity on plots, pricing & plans'
            ],
            link: '<?= BASE_URL ?>/contact',
            linkText: 'Book a Visit'
        },
        'flats': {
            title: 'Furnished Flats',
            subtitle: 'Ready-to-move in APS Heights, Prayagraj',
            icon: 'fas fa-couch',
            desc: 'Move in without the hassle. Our ready-to-move flats in APS Heights, Prayagraj offer 2BHK & 3BHK options with modern fittings, modular kitchen and dedicated parking — fully furnished and livable from day one.',
            steps: [
                'Explore available flat configurations',
                'Visit the model flat on site',
                'Choose 2BHK / 3BHK & book',
                'Move in — fully furnished & ready'
            ],
            link: '<?= BASE_URL ?>/properties',
            linkText: 'View Flats'
        },
        'community': {
            title: 'Community Building',
            subtitle: 'We build neighborhoods, not just plots',
            icon: 'fas fa-users',
            desc: 'We don—™t just sell plots — we build neighborhoods. Parks, temples, schools nearby and regular community events make every APS colony a place where families grow and communities flourish.',
            steps: [
                'Planned green & community spaces',
                'Nearby temples, schools & essentials',
                'Resident welfare & community events',
                'A thriving neighborhood for your family'
            ],
            link: '<?= BASE_URL ?>/about',
            linkText: 'Our Story'
        }
    };

    const overlay = document.getElementById('serviceModal');
    const elTitle = document.getElementById('serviceModalTitle');
    const elSub = document.getElementById('serviceModalSubtitle');
    const elIcon = document.getElementById('serviceModalIcon');
    const elDesc = document.getElementById('serviceModalDesc');
    const elSteps = document.getElementById('serviceModalSteps');
    const elLink = document.getElementById('serviceModalLink');
    const header = document.getElementById('serviceModalHeader');

    function openModal(key) {
        const s = SERVICES[key];
        if (!s) return;
        elTitle.textContent = s.title;
        elSub.textContent = s.subtitle;
        elIcon.innerHTML = '<i class="' + s.icon + '"></i>';
        elDesc.textContent = s.desc;
        elSteps.innerHTML = '';
        s.steps.forEach(step => {
            const li = document.createElement('li');
            li.textContent = step;
            elSteps.appendChild(li);
        });
        elLink.href = s.link;
        elLink.innerHTML = (s.linkText || 'Learn More') + ' &nbsp;<i class="fas fa-arrow-right"></i>';
        overlay.classList.add('active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', () => openModal(card.getAttribute('data-service')));
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(card.getAttribute('data-service')); }
        });
    });

    document.getElementById('serviceModalClose').addEventListener('click', closeModal);
    document.getElementById('serviceModalClose2').addEventListener('click', closeModal);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
});
</script>
