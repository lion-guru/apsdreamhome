<?php
/**
 * Earning & Career Opportunity Page - APS Dream Home
 * Beautifully designed page explaining the Unified 10-Rank Slab Differential Plan,
 * monthly salaries, and insurance benefits.
 */
?>

<!-- Custom Premium Styling -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        --secondary-gradient: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --dark-glass: rgba(15, 23, 42, 0.95);
        --light-glass: rgba(255, 255, 255, 0.85);
        --border-glass: rgba(255, 255, 255, 0.15);
        --text-muted: #64748b;
    }

    .hero-section {
        background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-circle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
        filter: blur(40px);
    }

    .glass-card {
        background: var(--light-glass);
        backdrop-filter: blur(16px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(99, 102, 241, 0.08);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .btn-premium {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        padding: 12px 30px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    }

    .btn-premium:hover {
        background: linear-gradient(135deg, #4338ca 0%, #134e4a 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    /* Tab controls */
    .plan-tabs {
        background: #f1f5f9;
        border-radius: 14px;
        padding: 6px;
        display: inline-flex;
        border: 1px solid #e2e8f0;
    }

    .plan-tab-btn {
        border: none;
        background: transparent;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.3s ease;
    }

    .plan-tab-btn.active {
        background: white;
        color: #0d9488;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Rank badges */
    .rank-badge-item {
        border-left: 4px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .rank-badge-item:hover {
        border-left-color: #0d9488;
        background: #fafafa;
    }

    .math-pill {
        background: #f0fdf4;
        color: #166534;
        border: 1px dashed #bbf7d0;
        font-family: monospace;
        font-size: 0.95rem;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>

<!-- Hero Section -->
<section class="hero-section py-5 text-white text-center">
    <div class="hero-circle" style="width: 400px; height: 400px; top: -100px; left: -100px;"></div>
    <div class="hero-circle" style="width: 500px; height: 500px; bottom: -150px; right: -150px;"></div>
    <div class="container py-4 position-relative">
        <span class="badge bg-indigo-500 bg-opacity-20 text-indigo-300 px-3 py-2 rounded-pill mb-3 fw-bold text-uppercase tracking-wider"><?= __('opp_badge', [], 'Career Opportunities') ?></span>
        <h1 class="display-4 fw-extrabold mb-3"><?= __('opp_heading', [], 'Why Join APS Dream Home?') ?></h1>
        <p class="lead text-indigo-200 mx-auto mb-4" style="max-width: 750px;">
            <?= __('opp_hero_desc', [], "A new beginning in Real Estate — with Salary + Commission + Health Insurance! Join Uttar Pradesh's leading real estate network and build your lifetime career.") ?>
        </p>
    </div>
</section>

<!-- Switch Tabs Section -->
<section class="py-5 bg-light">
    <div class="container">
        
        <!-- Core Pillars Section -->
        <div class="row g-4 mb-5 text-start">
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box bg-primary text-white mb-3"><i class="fas fa-wallet"></i></div>
                    <h5 class="fw-bold"><?= __('opp_pillar_salary', [], 'Fixed Monthly Salary') ?></h5>
                    <p class="text-muted small"><?= __('opp_pillar_salary_desc', [], 'रियल एस्टेट में आमतौर पर केवल कमीशन मिलता है। लेकिन APS Dream Home आपको आपकी सेल्स परफॉर्मेंस के आधार पर एक फिक्स्ड मासिक सैलरी भी प्रदान करता है!') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box bg-success text-white mb-3"><i class="fas fa-heartbeat"></i></div>
                    <h5 class="fw-bold"><?= __('opp_pillar_insurance', [], 'Free Insurance Cover') ?></h5>
                    <p class="text-muted small"><?= __('opp_pillar_insurance_desc', [], 'हम आपके परिवार की सुरक्षा का ध्यान रखते हैं। सभी सक्रिय पार्टनर्स को स्वास्थ्य, जीवन और दुर्घटना बीमा कवर मुफ्त प्रदान किया जाता है।') ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="icon-box bg-warning text-white mb-3"><i class="fas fa-graduation-cap"></i></div>
                    <h5 class="fw-bold"><?= __('opp_pillar_training', [], 'Training & Certification') ?></h5>
                    <p class="text-muted small"><?= __('opp_pillar_training_desc', [], 'कोई अनुभव नहीं? कोई बात नहीं! कंपनी 7-दिवसीय इंडक्शन प्रोग्राम और स्किल वर्कशॉप के जरिए आपको सेल्स और प्रॉपर्टी एक्सपर्ट बनाएगी।') ?></p>
                </div>
            </div>
        </div>

        <!-- Tab Controls -->
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-3 text-dark"><?= __('opp_tabs_heading', [], 'अपना पार्टनरशिप मॉडल चुनें') ?></h3>
            <p class="text-muted"><?= __('opp_tabs_subtitle', [], 'हम एसोसिएट्स और स्वतंत्र एजेंट्स दोनों के लिए अनुकूल प्लान प्रदान करते हैं।') ?></p>
            <div class="plan-tabs">
                <button class="plan-tab-btn active" onclick="switchPlan('mlm')">
                    <i class="fas fa-sitemap me-2"></i><?= __('opp_tab_mlm', [], '1. MLM Career Associate') ?>
                </button>
                <button class="plan-tab-btn" onclick="switchPlan('independent')">
                    <i class="fas fa-user-tie me-2"></i><?= __('opp_tab_independent', [], '2. Independent Super-Agent') ?>
                </button>
            </div>
        </div>

        <!-- MLM Network Plan View -->
        <div id="mlmPlanView" class="text-start">
            <div class="row g-4">
                <!-- Left Side: MLM Details & Tracks -->
                <div class="col-lg-7">
                    <!-- Differential Payout explanation -->
                    <div class="glass-card p-4 mb-4">
                        <h4 class="fw-bold text-indigo-700 mb-3"><i class="fas fa-money-check-alt me-2"></i><?= __('opp_slab_heading', [], 'Slab-Based Differential Commission') ?></h4>
                        <p class="text-muted small">
                            आपकी रैंक के अनुसार डायरेक्ट कमीशन **5% से 30%** तक होता है। आपकी टीम की सेल पर आपको आपकी रैंक और आपके डाउनलाइन की रैंक का **अंतर (Slab Difference)** मिलता है।
                        </p>
                        <div class="bg-light p-3 rounded-3 mb-2 small">
                            <strong><?= __('opp_example_label', [], 'उदाहरण (Example):') ?></strong><br>
                            आप **Global Director (30%)** पर हैं और आपकी टीम के एक **Associate (5%)** ने ₹10 लाख का प्लॉट बेचा:
                            <ul class="mt-2 mb-0">
                                <li>उसे सीधे **5% (₹50,000)** डायरेक्ट कमीशन मिलेगा।</li>
                                <li>आपको स्लैब का अंतर: **25% (30% - 5%) यानी ₹2,50,000** प्राप्त होगा।</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Free Insurance Details -->
                    <div class="glass-card p-4 mb-4">
                        <h4 class="fw-bold text-rose-600 mb-3"><i class="fas fa-user-shield me-2"></i><?= __('opp_insurance_heading', [], 'Free Family Insurance Policy') ?></h4>
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="bg-rose-50 p-3 rounded border border-rose-100">
                                    <div class="text-rose-600 mb-2"><i class="fas fa-notes-medical fa-2x"></i></div>
                                    <h6 class="fw-bold mb-1"><?= __('opp_health_cover', [], 'Health Cover') ?></h6>
                                    <span class="badge bg-rose-500 text-white">₹5 Lakhs</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-indigo-50 p-3 rounded border border-indigo-100">
                                    <div class="text-indigo-600 mb-2"><i class="fas fa-file-medical-alt fa-2x"></i></div>
                                    <h6 class="fw-bold mb-1"><?= __('opp_life_cover', [], 'Life Cover') ?></h6>
                                    <span class="badge bg-indigo-500 text-white">₹10 Lakhs</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-amber-50 p-3 rounded border border-amber-100">
                                    <div class="text-amber-600 mb-2"><i class="fas fa-car-crash fa-2x"></i></div>
                                    <h6 class="fw-bold mb-1"><?= __('opp_accidental_cover', [], 'Accidental') ?></h6>
                                    <span class="badge bg-amber-500 text-white">₹5 Lakhs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Same-Rank Override Details -->
                    <div class="card border-0 shadow-sm p-4 bg-white mb-4">
                        <h4 class="fw-bold text-indigo-700 mb-3"><i class="fas fa-shield-alt me-2"></i><?= __('opp_breakaway_heading', [], 'Same-Rank Breakaway Safeguard') ?></h4>
                        <p class="text-muted small">
                            यदि कोई डाउनलाइन तरक्की करके आपकी ही रैंक पर आ जाता है, तो आपका डिफरेंशियल 0% हो जाता है। ऐसे में कंपनी आपको सुरक्षा कवच देती है:
                        </p>
                        <ul class="list-group list-group-flush mb-0 small">
                            <li class="list-group-item bg-transparent border-0 px-0 d-flex gap-2">
                                <i class="fas fa-check-circle text-success mt-1"></i>
                                <div><strong>Generation 1 Same-Rank Override: 1.5%</strong> (इमीडिएट डाउनलाइन टीम वॉल्यूम पर)</div>
                            </li>
                            <li class="list-group-item bg-transparent border-0 px-0 d-flex gap-2">
                                <i class="fas fa-check-circle text-success mt-1"></i>
                                <div><strong>Generation 2 Same-Rank Override: 1.0%</strong> (उनके नीचे वाले टीम वॉल्यूम पर)</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Side: 10 Ranks & Salaries -->
                <div class="col-lg-5">
                    <div class="glass-card p-4">
                        <h4 class="fw-bold text-dark mb-4"><i class="fas fa-network-wired text-warning me-2"></i><?= __('opp_career_heading', [], '10-Rank Career Path') ?></h4>
                        
                        <!-- Timeline Ranks -->
                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>1. <?= __('opp_rank_associate', [], 'Associate') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">5.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_associate_detail', [], 'Business: ₹50K+ | Reward: Starter Path') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>2. <?= __('opp_rank_bronze', [], 'Bronze') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">7.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_bronze_detail', [], 'Business: ₹2L+ | Reward: ₹5K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>3. <?= __('opp_rank_silver', [], 'Silver') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">10.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_silver_detail', [], 'Business: ₹5L+ | Reward: ₹8K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>4. <?= __('opp_rank_gold', [], 'Gold') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">12.5% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_gold_detail', [], 'Business: ₹10L+ | Reward: ₹12K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>5. <?= __('opp_rank_platinum', [], 'Platinum') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">15.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_platinum_detail', [], 'Business: ₹25L+ | Reward: ₹15K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>6. <?= __('opp_rank_diamond', [], 'Diamond') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">18.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_diamond_detail', [], 'Business: ₹50L+ | Reward: ₹20K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>7. <?= __('opp_rank_executive', [], 'Executive') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">20.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_executive_detail', [], 'Business: ₹1Cr+ | Reward: ₹25K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>8. <?= __('opp_rank_sr_executive', [], 'Sr. Executive') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">22.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_sr_executive_detail', [], 'Business: ₹2Cr+ | Reward: ₹30K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 mb-2 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>9. <?= __('opp_rank_director', [], 'Director') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">25.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_director_detail', [], 'Business: ₹5Cr+ | Reward: ₹40K/mo salary') ?></span>
                        </div>

                        <div class="rank-badge-item p-3 rounded glass-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>10. <?= __('opp_rank_global_director', [], 'Global Director') ?></strong>
                                <span class="badge bg-indigo-100 text-indigo-700">30.0% <?= __('opp_rate', [], 'Rate') ?></span>
                            </div>
                            <span class="small text-muted"><?= __('opp_rank_global_director_detail', [], 'Business: ₹10Cr+ | Reward: ₹50K/mo salary') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Independent Broker Plan View -->
        <div id="independentPlanView" class="text-start d-none">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="glass-card p-4 h-100">
                        <h4 class="fw-bold text-primary mb-3"><i class="fas fa-handshake me-2"></i><?= __('opp_independent_heading', [], 'स्वतंत्र एजेंट (Flat Deal) मॉडल') ?></h4>
                        <p class="text-muted">
                            <?= __('opp_independent_desc', [], 'यदि आप MLM टीम बिल्डिंग में रुचि नहीं रखते हैं और स्वतंत्र ब्रोकर की तरह बड़ी कमर्शियल या रेसिडेंशियल डील्स क्लोज करना चाहते हैं, तो यह विकल्प आपके लिए सर्वोत्तम है:') ?>
                        </p>
                        
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item bg-transparent border-0 px-0 d-flex gap-2">
                                <i class="fas fa-check-circle text-success mt-1"></i>
                                <div>
                                    <strong><?= __('opp_flat_pct_title', [], 'तय प्रतिशत (Flat Percentage)') ?></strong>: <?= __('opp_flat_pct_desc', [], 'सीधे कुल बुकिंग/भुगतान का 8% या 10% कमीशन प्राप्त करें।') ?>
                                </div>
                            </li>
                            <li class="list-group-item bg-transparent border-0 px-0 d-flex gap-2">
                                <i class="fas fa-check-circle text-success mt-1"></i>
                                <div>
                                    <strong><?= __('opp_flat_rate_title', [], 'तय दर (Flat Rate Per SqFt)') ?></strong>: <?= __('opp_flat_rate_desc', [], 'प्लॉट के साइज के अनुसार तय दर (जैसे ₹150 प्रति वर्ग फुट) पर सीधा भुगतान पाएं।') ?>
                                </div>
                            </li>
                            <li class="list-group-item bg-transparent border-0 px-0 d-flex gap-2">
                                <i class="fas fa-check-circle text-success mt-1"></i>
                                <div>
                                    <strong><?= __('opp_upline_title', [], 'Upline से मुक्ति') ?></strong>: <?= __('opp_upline_desc', [], 'स्वतंत्र एजेंट की डील्स पर MLM सीनियर्स को डिफरेंशियल कमीशन नहीं जाता है, जिससे आपके डील्स पूरी तरह सुरक्षित रहते हैं।') ?>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 bg-white h-100">
                        <h4 class="fw-bold text-indigo-700 mb-3"><i class="fas fa-users-cog me-2"></i><?= __('opp_team_heading', [], 'स्वतंत्र टीम प्रबंधन (Sub-Agent Splits)') ?></h4>
                        <p class="text-muted">
                            <?= __('opp_team_desc', [], 'स्वतंत्र एजेंट्स अपनी डील्स और सब-एजेंट्स के बीच कमीशन का विभाजन खुद तय करते हैं:') ?>
                        </p>
                        <div class="bg-light p-3 rounded-3 mb-3 small">
                            <strong><?= __('opp_workflow_label', [], 'कार्यप्रणाली:') ?></strong>
                            <ol class="mt-2 mb-0">
                                <li><?= __('opp_workflow_step1', [], 'कंपनी स्वतंत्र एजेंट को पूरा तय कमीशन डायरेक्ट पे करती है।') ?></li>
                                <li><?= __('opp_workflow_step2', [], 'एजेंट अपने नीचे जुड़े जूनियर एजेंट्स का कमीशन स्वयं तय और वितरित करता है।') ?></li>
                                <li><?= __('opp_workflow_step3', [], 'कंपनी इसमें किसी भी प्रकार का हस्तक्षेप नहीं करती, जिससे एजेंट्स को पूर्ण स्वतंत्रता मिलती है।') ?></li>
                            </ol>
                        </div>
                        <p class="small text-muted mb-0">
                            <?= __('opp_team_note', [], 'यह मॉडल उन डीलरशिप फर्मों और रियल एस्टेट सलाहकारों के लिए सर्वोत्तम है जो अपने बैनर तले अपनी टीम को संभालते हैं।') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 text-center bg-indigo-900 text-white">
    <div class="container py-3">
        <h2 class="fw-bold mb-3"><?= __('opp_cta_heading', [], 'आज ही हमारे एसोसिएट नेटवर्क का हिस्सा बनें!') ?></h2>
        <p class="text-indigo-200 mb-4 mx-auto" style="max-width: 600px;">
            <?= __('opp_cta_desc', [], 'Grow your network, build a career, and earn commissions. Register as a partner today.') ?>
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/associate/register" class="btn btn-premium btn-lg"><?= __('opp_cta_register', [], 'पार्टनर रजिस्टर करें') ?></a>
            <a href="/contact" class="btn btn-outline-light btn-lg rounded-3"><?= __('opp_cta_contact', [], 'हमसे संपर्क करें') ?></a>
        </div>
    </div>
</section>

<!-- Switch Tab Script -->
<script>
    function switchPlan(type) {
        // Toggle Buttons
        const buttons = document.querySelectorAll('.plan-tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        
        // Target active button
        const activeBtn = Array.from(buttons).find(btn => btn.innerHTML.includes(type === 'mlm' ? 'MLM' : 'Independent'));
        if (activeBtn) activeBtn.classList.add('active');

        // Toggle Views
        const mlmView = document.getElementById('mlmPlanView');
        const independentView = document.getElementById('independentPlanView');

        if (type === 'mlm') {
            mlmView.classList.remove('d-none');
            independentView.classList.add('d-none');
        } else {
            mlmView.classList.add('d-none');
            independentView.classList.remove('d-none');
        }
    }
</script>
