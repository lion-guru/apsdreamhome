<?php
if (!isset($sc)) {
    $sc = function ($k, $d = '') {
        return $GLOBALS['_site_settings_cache'][$k] ?? $d;
    };
}
$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112'));
$phoneDisplay = $sc('contact_phone', '+91 92771 21112');
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/journey-scroll.css">
<main id="main-content" >
    <!-- Hero Section (Premium Modern UI) -->
    <section class="hero-premium" aria-labelledby="hero-title">
        <!-- Particles Canvas -->
        <canvas id="particles-canvas" ></canvas>
        <!-- Gradient Overlay -->
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="premium-reveal hover-lift">
                        <span class="badge px-3 py-2 mb-3 badge-pulse badge-premium">
                            <i class="fas fa-star me-1 iu-navy"></i>
                            <?= __('trusted_by') ?> <?= __('home_families_count') ?>
                        </span>
                    </div>
                    <h1 id="hero-title" class="fw-bold text-white premium-reveal hero-title">
                        <?= __('hero_title') ?>
                    </h1>
                    <p class="lead mb-4 premium-reveal hero-subtitle">
                        <?= __('hero_subtitle') ?>
                    </p>
                    <!-- Typed Text Subtitle -->
                    <div class="mb-4 premium-reveal">
                        <span class="hero-typed-label">
                            <i class="fas fa-circle me-1"></i>
                            <?= __('home_we_offer') ?> <span id="typed-text" class="fw-semibold gradient-text-gold" data-strings="<?= __('hero_typed_strings') ?>||Premium Plots in Gorakhpur||Smart Investment Opportunities||Trusted by 5000+ Families||"></span><span class="typing-cursor iu-teal-light"></span>
                        </span>
                    </div>
                    <div class="d-flex gap-3 flex-wrap premium-reveal">
                        <?php
                        $heroVariant = $_SESSION['experiments']['homepage_cta'] ?? null;
                        $heroCtaText = match ($heroVariant) {
                            'urgent'   => __('hero_cta_urgent'),
                            'family'   => __('hero_cta_family'),
                            default    => __('hero_cta'),
                        };
                        $ctaColorVariant = $_SESSION['experiments']['cta_button_color'] ?? 'blue';
                        $ctaColorClass = match ($ctaColorVariant) {
                            'green'  => 'ab-btn-green',
                            'orange' => 'ab-btn-orange',
                            default  => 'btn-warning',
                        };
                        ?>
                        <a href="<?php echo BASE_URL; ?>/company/projects"
                            class="btn btn-premium btn-glow glow-on-hover" data-experiment="homepage_cta"
                            data-variant="<?php echo htmlspecialchars((string) $heroVariant, ENT_QUOTES); ?>"
                            data-color-experiment="cta_button_color"
                            data-color-variant="<?php echo htmlspecialchars((string) $ctaColorVariant, ENT_QUOTES); ?>"
                            id="hero-cta">
                            <i class="fas fa-building me-2"></i><?= htmlspecialchars($heroCtaText ?? '') ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/list-property"
                            class="btn btn-outline-premium hover-lift">
                            <i class="fas fa-plus-circle me-2"></i><?= __('nav_post_property') ?>
                        </a>
                    </div>
                    <!-- Trust Badges -->
                    <div class="d-flex gap-4 mt-4 flex-wrap premium-reveal">
                        <div class="d-flex align-items-center gap-2 hero-trust-badge">
                            <i class="fas fa-shield-halved iu-emerald"></i>
                            <span class="small"><?= __('home_trust_rera') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 hero-trust-badge">
                            <i class="fas fa-vector-square iu-amber"></i>
                            <span class="small"><?= __('home_trust_plots_sold') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 hero-trust-badge">
                            <i class="fas fa-users iu-teal-light"></i>
                            <span class="small"><?= __('home_trust_happy_families') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-search-glass glass-panel hover-lift premium-reveal">
                        <div class="card-header text-center py-4 border-bottom">
                            <h5 class="mb-0 fw-semibold text-dark"><i class="fas fa-search me-2"></i><?= __('search') ?> <?= __('properties') ?></h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="<?php echo BASE_URL; ?>/properties" method="GET">
                                <div class="mb-3">
                                    <select name="listing" class="form-select">
                                        <option value=""><?= __('home_buy_rent') ?></option>
                                        <option value="sell"><?= __('home_buy') ?></option>
                                        <option value="rent"><?= __('home_rent') ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select name="type" class="form-select">
                                        <option value=""><?= __('home_property_type') ?></option>
                                        <option value="residential"><?= __('home_residential') ?></option>
                                        <option value="commercial"><?= __('home_commercial') ?></option>
                                        <option value="plot"><?= __('home_plot_land') ?></option>
                                        <option value="house"><?= __('home_house_villa') ?></option>
                                        <option value="flat"><?= __('home_flat_apartment') ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select name="location" class="form-select">
                                        <option value=""><?= __('home_select_location') ?></option>
                                        <option value="Gorakhpur"><?= __('loc_gorakhpur') ?></option>
                                        <option value="Lucknow"><?= __('loc_lucknow') ?></option>
                                        <option value="Kushinagar"><?= __('loc_kushinagar') ?></option>
                                        <option value="Varanasi"><?= __('loc_varanasi') ?></option>
                                        <option value="Ayodhya"><?= __('loc_ayodhya') ?></option>
                                        <option value="Other"><?= __('home_other') ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select name="budget" class="form-select">
                                        <option value=""><?= __('home_budget') ?></option>
                                        <option value="under_5l"><?= __('home_budget_under_5l') ?></option>
                                        <option value="5_10l"><?= __('home_budget_5_10l') ?></option>
                                        <option value="10_20l"><?= __('home_budget_10_20l') ?></option>
                                        <option value="20_50l"><?= __('home_budget_20_50l') ?></option>
                                        <option value="above_50l"><?= __('home_budget_above_50l') ?></option>
                                    </select>
                                </div>
                                <button type="submit" class="btn w-100 btn-premium btn-glow glow-on-hover">
                                    <i class="fas fa-search me-2"></i><?= __('search') ?> <?= __('properties') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats - Big Company Feel -->
    <section class="py-0 stats-section">
        <div class="container">
            <div class="stats-banner">
                <div class="deco-circle-1"></div>
                <div class="deco-circle-2"></div>
                
                <div class="row g-4 align-items-center position-relative">
                    <div class="col-lg-3 col-6">
                        <div class="text-center">
                            <div class="stats-icon-wrap">
                                <i class="fas fa-vector-square fa-lg"></i>
                            </div>
                            <div class="stat-number" data-target="5000" data-suffix="+">0</div>
                            <div class="stat-label"><?= __('home_stat_plots_sold') ?></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="text-center">
                            <div class="stats-icon-wrap">
                                <i class="fas fa-home fa-lg"></i>
                            </div>
                            <div class="stat-number" data-target="500" data-suffix="+">0</div>
                            <div class="stat-label"><?= __('home_stat_happy_families') ?></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="text-center">
                            <div class="stats-icon-wrap">
                                <i class="fas fa-city fa-lg"></i>
                            </div>
                            <div class="stat-number" data-target="4" data-suffix="">0</div>
                            <div class="stat-label"><?= __('home_stat_colonies_delivered') ?></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="text-center">
                            <div class="stats-icon-wrap">
                                <i class="fas fa-calendar-alt fa-lg"></i>
                            </div>
                            <div class="stat-number" data-target="4" data-suffix="+">0</div>
                            <div class="stat-label"><?= __('home_stat_years_trust') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dream Home Journey Scroll Sequence -->
    <section class="journey-section" id="dream-home-journey">
        <div class="journey-sticky-container">
            <!-- Background Images -->
            <div class="journey-bg bg-step-1 active" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/journey/plot.jpg');"></div>
            <div class="journey-bg bg-step-2" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/journey/foundation.jpg');"></div>
            <div class="journey-bg bg-step-3" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/journey/construction.jpg');"></div>
            <div class="journey-bg bg-step-4" style="background-image: url('<?php echo BASE_URL; ?>/assets/images/journey/home.jpg');"></div>

            <!-- Text Content -->
            <div class="journey-text-container">
                <div class="journey-step step-1 active">
                    <h2 class="journey-title text-primary">1. The Plot</h2>
                    <p class="journey-subtitle">Start with a Premium Plot in a Secure Colony.</p>
                </div>
                <div class="journey-step step-2">
                    <h2 class="journey-title text-warning">2. The Foundation</h2>
                    <p class="journey-subtitle">We help you lay strong foundations for your future.</p>
                </div>
                <div class="journey-step step-3">
                    <h2 class="journey-title text-info">3. Construction</h2>
                    <p class="journey-subtitle">Watch your dream home take shape with trusted partners.</p>
                </div>
                <div class="journey-step step-4">
                    <h2 class="journey-title text-success">4. The Dream Home</h2>
                    <p class="journey-subtitle">Move into your Dream Home and build lasting memories.</p>
                </div>
            </div>
        </div>
        <!-- Scroll Spacer to trigger sticky events -->
        <div class="journey-scroll-spacer"></div>
    </section>

    <!-- Construction Excellence - Builder Section -->
    <section class="py-5 section-gray bg-light" aria-labelledby="construction-title">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 premium-reveal">
                    <div class="capsule-badge mb-3 capsule-teal">
                        <i class="fas fa-hard-hat"></i> <?= __('home_builder_badge') ?>
                    </div>
                    <h2 id="construction-title" class="fw-bold mb-3 iu-navy">
                        <?= __('home_construction_title') ?>
                    </h2>
                    <p class="text-muted mb-4 fs-6">
                        <?= __('home_construction_desc') ?>
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="service-card p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="service-icon mb-0">
                                        <i class="fas fa-drafting-compass"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6"><?= __('home_build_planning') ?></div>
                                        <div class="text-muted text-xs-sm"><?= __('home_build_planning_desc') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="service-card p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="service-icon mb-0">
                                        <i class="fas fa-road"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6"><?= __('home_build_infrastructure') ?></div>
                                        <div class="text-muted text-xs-sm"><?= __('home_build_infrastructure_desc') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="service-card p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="service-icon mb-0">
                                        <i class="fas fa-tree"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6"><?= __('home_build_green') ?></div>
                                        <div class="text-muted text-xs-sm"><?= __('home_build_green_desc') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="service-card p-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="service-icon mb-0">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6"><?= __('home_build_utilities') ?></div>
                                        <div class="text-muted text-xs-sm"><?= __('home_build_utilities_desc') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <a href="<?= BASE_URL ?>/company/projects" class="btn btn-dark-gradient">
                                <i class="fas fa-arrow-right"></i> <?= __('home_view_our_projects') ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="construction-grid">
                        <div>
                            <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" alt="Construction Work" class="img-cover" loading="lazy" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                        </div>
                        <div>
                            <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" alt="Building Progress" class="img-cover" loading="lazy" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                        </div>
                        <div>
                            <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/raghunath-nagri-motiram.jpg" alt="Modern Construction" class="img-cover" loading="lazy" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Colony Development Showcase -->
    <section class="py-5" aria-labelledby="colony-title">
        <div class="container">
            <div class="text-center mb-5 premium-reveal">
                <div class="capsule-badge mb-3 capsule-green">
                    <i class="fas fa-city"></i> <?= __('home_colony_badge') ?>
                </div>
                <h2 id="colony-title" class="fw-bold mb-3 iu-navy">
                    <?= __('home_colony_title') ?>
                </h2>
                <p class="text-muted fs-6 mx-auto section-desc">
                    <?= __('home_colony_desc') ?>
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card p-0 overflow-hidden hover-lift h-100 premium-reveal">
                        <div class="position-relative colony-img-height">
                            <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" alt="<?= __('home_colony_suryoday') ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient colony-overlay-gradient">
                                <span class="capsule-badge badge-delivered text-white"><?= __('home_colony_delivered') ?></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2"><?= __('home_colony_suryoday') ?></h5>
                            <p class="text-muted small mb-3"><?= __('home_colony_suryoday_desc') ?></p>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="fas fa-map-marker-alt me-1 text-success"></i><?= __('loc_gorakhpur') ?></span>
                                <span><i class="fas fa-vector-square me-1 text-success"></i><?= __('home_colony_suryoday_plots') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card p-0 overflow-hidden hover-lift h-100 premium-reveal">
                        <div class="position-relative colony-img-height">
                            <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday1.jpeg" alt="<?= __('home_colony_braj_radha') ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient colony-overlay-gradient">
                                <span class="capsule-badge badge-ongoing text-white"><?= __('home_colony_ongoing') ?></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2"><?= __('home_colony_braj_radha') ?></h5>
                            <p class="text-muted small mb-3"><?= __('home_colony_braj_radha_desc') ?></p>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="fas fa-map-marker-alt me-1 text-warning"></i><?= __('loc_gorakhpur') ?></span>
                                <span><i class="fas fa-vector-square me-1 text-warning"></i><?= __('home_colony_braj_radha_plots') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card p-0 overflow-hidden hover-lift h-100 premium-reveal">
                        <div class="position-relative colony-img-height">
                            <img src="<?= BASE_URL ?>/assets/images/projects/kushinagar/budh-bihar.jpg" alt="<?= __('home_colony_budh_bihar') ?>" class="w-100 h-100 object-fit-cover" loading="lazy">
                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient colony-overlay-gradient">
                                <span class="capsule-badge badge-upcoming text-white badge-upcoming"><?= __('home_colony_upcoming') ?></span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2"><?= __('home_colony_budh_bihar') ?></h5>
                            <p class="text-muted small mb-3"><?= __('home_colony_budh_bihar_desc') ?></p>
                            <div class="d-flex gap-3 small text-muted">
                                <span><i class="fas fa-map-marker-alt me-1 text-primary"></i><?= __('loc_kushinagar') ?></span>
                                <span><i class="fas fa-vector-square me-1 text-primary"></i><?= __('home_colony_budh_bihar_plots') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3D Virtual Tour Teaser -->
    <section class="py-5 bg-dark text-white text-center position-relative overflow-hidden" aria-labelledby="3d-tour-title">
        <!-- Background decorative elements -->
        <div class="position-absolute w-100 h-100 top-0 start-0" style="background: radial-gradient(circle at center, rgba(16,185,129,0.15) 0%, rgba(11,17,32,1) 100%);"></div>
        <div class="container position-relative z-1 premium-reveal">
            <span class="badge badge-premium px-3 py-2 mb-3 bg-gradient text-dark" style="background: linear-gradient(45deg, #ffd700, #ff8c00);"><i class="fas fa-cube me-1"></i> New Feature</span>
            <h2 id="3d-tour-title" class="fw-bold mb-3 display-5 text-white">Experience Real Estate Like Never Before</h2>
            <p class="lead text-white-50 mx-auto mb-4" style="max-width: 600px;">Take a breathtaking 3D flight over our premium plotted colonies. See every detail, road, and park before you visit the site.</p>
            <a href="<?= BASE_URL ?>/3d-tour" class="btn btn-lg btn-warning btn-glow rounded-pill px-5 py-3 fw-bold shadow-lg" style="transition: transform 0.3s; transform-origin: center;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fas fa-vr-cardboard me-2"></i> Start 3D Virtual Tour
            </a>
            
            <!-- Floating 3D Graphic (CSS) -->
            <div class="mt-5 mx-auto" style="width: 200px; height: 100px; perspective: 800px;">
                <div style="width: 100%; height: 100%; background: rgba(16,185,129,0.3); border: 2px solid #10b981; transform: rotateX(60deg) rotateZ(-45deg); box-shadow: 0 20px 50px rgba(16,185,129,0.5); animation: float3d 4s ease-in-out infinite;"></div>
            </div>
            <style>
                @keyframes float3d {
                    0%, 100% { transform: rotateX(60deg) rotateZ(-45deg) translateZ(0px); }
                    50% { transform: rotateX(60deg) rotateZ(-45deg) translateZ(20px); }
                }
            </style>
        </div>
    </section>

    <!-- Our Premium Projects -->
    <section class="projects-section" aria-labelledby="projects-title">
        <div class="container">
            <div class="text-center ps-section-header premium-reveal">
                <div class="capsule-badge mb-3 capsule-teal">
                    <i class="fas fa-building"></i> <?= __('home_portfolio_badge') ?>
                </div>
                <h2 id="projects-title" class="ps-heading fw-bold iu-navy"><?= __('section_our_projects') ?></h2>
                <p class="ps-subtitle text-muted fs-6"><?= __('projects_subtitle') ?></p>
            </div>

            <!-- Filter Tabs - mobile horizontal scroll -->
            <div class="ps-filter-scroll">
                <div class="ps-filter-bar">
                    <button class="ps-filter-btn active" data-filter="all">
                        <i class="fas fa-globe-asia"></i><span><?= __('all_locations') ?></span>
                    </button>
                    <button class="ps-filter-btn" data-filter="gorakhpur">
                        <i class="fas fa-map-pin"></i><span><?= __('loc_gorakhpur') ?></span>
                    </button>
                    <button class="ps-filter-btn" data-filter="lucknow">
                        <i class="fas fa-map-pin"></i><span><?= __('loc_lucknow') ?></span>
                    </button>
                    <button class="ps-filter-btn" data-filter="kushinagar">
                        <i class="fas fa-map-pin"></i><span><?= __('loc_kushinagar') ?></span>
                    </button>
                </div>
            </div>

            <div class="ps-grid">
                <?php
                $featured_properties = $featured_properties ?? [];
                $hasProjects = !empty($featured_properties);
                if (!$hasProjects):
                    $fallbackProjects = [
                        ['title' => 'Suryoday Colony', 'city' => 'Gorakhpur', 'price' => '₹999+/sqft', 'slug' => 'suryoday-colony', 'status' => 'Possession Ready', 'img' => 'gorakhpur/suryoday.jpg', 'plots' => '1050+', 'type' => 'Residential', 'area' => '35 Acres', 'sold' => '100+ Sold'],
                        ['title' => 'Braj Radha Nagri', 'city' => 'Gorakhpur', 'price' => '₹7.5L+', 'slug' => 'braj-radha-nagri', 'status' => 'Available', 'img' => 'projects/gorakhpur/suryoday.jpg', 'plots' => '1550+', 'type' => 'Premium', 'area' => '10 Acres', 'sold' => 'Best Seller'],
                        ['title' => 'Raghunath Nagri', 'city' => 'Gorakhpur', 'price' => '₹5.5L+', 'slug' => 'raghunath-nagri', 'status' => 'Available', 'img' => 'projects/gorakhpur/suryoday1.jpeg', 'plots' => '780+', 'type' => 'Residential', 'area' => '22 Acres', 'sold' => 'Hot Deal'],
                        ['title' => 'Budh Bihar Colony', 'city' => 'Kushinagar', 'price' => '₹3.5L+', 'slug' => 'budh-bihar-colony', 'status' => 'Available', 'img' => 'kushinagar/budh-bihar.jpg', 'plots' => '1280+', 'type' => 'Affordable', 'area' => '30 Acres', 'sold' => 'Value Buy'],
                    ];
                    foreach ($fallbackProjects as $p):
                ?>
                <div class="ps-card-wrap project-card-item" data-location="<?= strtolower($p['city']) ?>">
                    <div class="ps-card">
                        <div class="ps-card-img">
                            <img loading="lazy"
                                src="<?= BASE_URL ?>/assets/images/<?= $p['img'] ?>"
                                alt="<?= htmlspecialchars($p['title'] ?? '') ?>"
                                onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <div class="ps-card-img-grad"></div>
                            <span class="ps-badge ps-badge-status"><?= $p['status'] ?></span>
                            <span class="ps-badge ps-badge-type"><?= $p['type'] ?></span>
                            <div class="ps-card-location-chip"><i class="fas fa-map-marker-alt"></i> <?= $p['city'] ?></div>
                        </div>
                        <div class="ps-card-body">
                            <h5 class="ps-card-title"><?= $p['title'] ?></h5>
                            <div class="ps-card-stats">
                                <span><i class="fas fa-vector-square"></i> <?= $p['plots'] ?> <?= __('plots') ?></span>
                                <span><i class="fas fa-expand-arrows-alt"></i> <?= $p['area'] ?></span>
                            </div>
                            <div class="price-tag">
                                <i class="fas fa-check-circle me-1"></i> <?= $p['sold'] ?>
                            </div>
                            <div class="ps-card-bottom">
                                <div class="ps-price"><small><?= __('home_starting') ?></small> <?= $p['price'] ?></div>
                                <a href="<?= BASE_URL ?>/colony/<?= $p['slug'] ?>" class="ps-btn"><?= __('view_details') ?> <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <?php foreach (array_slice($featured_properties, 0, 6) as $project):
                        $projectTitle = $project['title'] ?? $project['name'] ?? '';
                        $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $projectTitle));
                        $imgPath = '/assets/images/projects/';
                        if ($projectTitle && stripos($projectTitle, 'Suryoday') !== false) {
                            $imgPath .= 'gorakhpur/suryoday.jpg';
                        } elseif ($projectTitle && stripos($projectTitle, 'Raghunath') !== false) {
                            $imgPath .= 'gorakhpur/raghunath-nagri.jpg';
                        } elseif ($projectTitle && stripos($projectTitle, 'Braj') !== false || stripos($projectTitle, 'Radha') !== false) {
                            $imgPath .= 'gorakhpur/braj-radha-nagri.jpg';
                        } elseif ($projectTitle && stripos($projectTitle, 'Budh') !== false) {
                            $imgPath .= 'kushinagar/budh-bihar.jpg';
                        } elseif ($projectTitle && stripos($projectTitle, 'APS Valley') !== false) {
                            $imgPath .= 'lucknow/aps-valley.jpg';
                        } elseif ($projectTitle && stripos($projectTitle, 'APS Heights') !== false) {
                            $imgPath .= 'gorakhpur/suryoday.jpg';
                        } else {
                            $imgPath .= 'placeholder/property.svg';
                        }
                    ?>
                <div class="ps-card-wrap project-card-item" data-location="<?= strtolower($project['city'] ?? '') ?>">
                    <div class="ps-card">
                        <div class="ps-card-img">
                            <img loading="lazy" src="<?= BASE_URL . $imgPath ?>"
                                alt="<?= htmlspecialchars($projectTitle ?? '') ?>"
                                onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <div class="ps-card-img-grad"></div>
                            <span class="ps-badge ps-badge-status"><?= $project['status'] ?? 'Available' ?></span>
                            <span class="ps-badge ps-badge-type"><?= $project['type'] ?? 'Residential' ?></span>
                            <div class="ps-card-location-chip"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($project['city'] ?? '') ?></div>
                        </div>
                        <div class="ps-card-body">
                            <h5 class="ps-card-title"><?= htmlspecialchars($projectTitle ?? '') ?></h5>
                            <div class="ps-card-stats">
                                <?php if (!empty($project['plots'])): ?>
                                <span><i class="fas fa-vector-square"></i> <?= $project['plots'] ?> <?= __('plots') ?></span>
                                <?php endif; ?>
                                <?php if (!empty($project['area'])): ?>
                                <span><i class="fas fa-expand-arrows-alt"></i> <?= $project['area'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($project['sold'])): ?>
                            <div class="price-tag">
                                <i class="fas fa-check-circle me-1"></i> <?= $project['sold'] ?>
                            </div>
                            <?php endif; ?>
                            <div class="ps-card-bottom">
                                <div class="ps-price"><small><?= __('home_starting') ?></small> <?= $project['price'] ?? '' ?></div>
                                <a href="<?= BASE_URL ?>/colony/<?= $slug ?>" class="ps-btn"><?= __('view_details') ?> <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

    <!-- Useful Free Tools -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><?= __('home_free_tools') ?></span>
                <h2 class="fw-bold"><?= __('home_free_tools_title') ?></h2>
                <p class="section-subtitle"><?= __('home_free_tools_subtitle') ?></p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('emi')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-calculator fa-2x text-primary"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_emi') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_emi_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('investment')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-arrow-trend-up fa-2x text-success"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_investment') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_investment_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('stamp')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-file-contract fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_stamp') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_stamp_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('converter')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-vector-square fa-2x text-info"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_converter') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_converter_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('eligibility')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-hand-holding-dollar fa-2x text-danger"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_eligibility') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_eligibility_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('valuation')">
                        <div class="card-body p-4 text-center">
                            <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3 tool-icon-circle">
                                <i class="fas fa-house-chimney fa-2x text-secondary"></i>
                            </div>
                            <h5 class="fw-bold"><?= __('home_tool_valuation') ?></h5>
                            <p class="text-muted small mb-0"><?= __('home_tool_valuation_desc') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- EMI Calculator Section -->
    <section class="py-5 emi-section bg-premium-navy" aria-labelledby="emi-title">
        <div class="container">
            <div class="text-center mb-5 premium-reveal">
                <span class="badge px-3 py-2 mb-3 badge-premium"><?= __('home_free_tool') ?></span>
                <h2 id="emi-title" class="fw-bold text-white"><?= __('emi_calculator') ?></h2>
                <p class="text-white-50"><?= __('emi_subtitle') ?></p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 glass-dark premium-reveal">
                        <div class="card-body p-4 p-md-5 text-white">
                            <div class="row g-4">
                                <div class="col-md-7">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold"><?= __('home_loan_amount') ?> <span
                                                id="loanAmtDisplay" class="text-warning">₹50,00,000</span></label>
                                        <input type="range" class="form-range" id="loanAmount" min="100000"
                                            max="50000000" step="100000" value="5000000" oninput="calcEMI()" title="<?= __('home_loan_amount') ?>">
                                        <div class="d-flex justify-content-between small text-white-50">
                                            <span><?= __('home_emi_min_label') ?></span>
                                            <span><?= __('home_emi_max_label') ?></span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold"><?= __('home_interest_rate') ?> <span
                                                id="rateDisplay" class="text-warning">8.5%</span></label>
                                        <input type="range" class="form-range" id="interestRate" min="5" max="20"
                                            step="0.1" value="8.5" oninput="calcEMI()">
                                        <div class="d-flex justify-content-between small text-white-50">
                                            <span>5%</span>
                                            <span>20%</span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold"><?= __('home_loan_tenure') ?> <span
                                                id="tenureDisplay" class="text-warning">20
                                                <?= __('home_years') ?></span></label>
                                        <input type="range" class="form-range" id="loanTenure" min="1" max="30" step="1"
                                            value="20" oninput="calcEMI()">
                                        <div class="d-flex justify-content-between small text-white-50">
                                            <span><?= __('home_emi_min_tenure') ?></span>
                                            <span><?= __('home_emi_max_tenure') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="result-card bg-dark text-white">
                                        <p class="text-white-50 mb-1 small text-uppercase text-center"><?= __('home_your_monthly_emi') ?></p>
                                        <p class="display-5 fw-bold mb-0 text-center iu-teal-light" id="emiResult">₹42,426</p>
                                        
                                        <!-- Animated Donut Chart -->
                                        <div class="emi-chart-container my-3">
                                            <svg width="120" height="120" viewBox="0 0 100 100" class="emi-donut-svg">
                                                <circle class="emi-donut-ring" cx="50" cy="50" r="40" />
                                                <circle id="emiPrincipalSegment" class="emi-donut-segment-principal" cx="50" cy="50" r="40" />
                                                <circle id="emiInterestSegment" class="emi-donut-segment-interest" cx="50" cy="50" r="40" />
                                                <g class="emi-donut-text">
                                                    <text x="50" y="48" id="chartPrincipalPct" class="emi-donut-val">50%</text>
                                                     <text x="50" y="58" class="emi-donut-label"><?= __('home_principal', null, 'Principal') ?></text>
                                                </g>
                                            </svg>
                                        </div>

                                        <hr class="border-light my-3 opacity-50">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-white-50"><i class="fas fa-circle me-1"></i><?= __('home_loan_amount') ?></span>
                                            <span class="fw-bold text-white" id="totalPrincipalDisp">₹50,00,000</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span class="text-white-50"><i class="fas fa-circle me-1"></i><?= __('home_total_interest') ?></span>
                                            <span class="fw-bold text-white" id="totalInterest">₹51,82,240</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2 pt-2 border-top border-light opacity-50">
                                            <span class="text-white-50"><?= __('home_total_payment') ?></span>
                                            <span class="fw-bold text-white" id="totalPayment">₹1,01,82,240</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-white-50 mt-3 small"><i
                            class="fas fa-info-circle me-1"></i><?= __('home_emi_disclaimer') ?></p>
                </div>
            </div>
        </div>
    </section>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    function calcEMI() {
        const P = parseFloat(document.getElementById('loanAmount').value);
        const R = parseFloat(document.getElementById('interestRate').value) / 12 / 100;
        const N = parseFloat(document.getElementById('loanTenure').value) * 12;

        document.getElementById('loanAmtDisplay').textContent = '₹' + P.toLocaleString('en-IN');
        document.getElementById('rateDisplay').textContent = document.getElementById('interestRate').value + '%';
        document.getElementById('tenureDisplay').textContent = document.getElementById('loanTenure').value + ' ' + '<?= __('home_years') ?>';

        let emi = 0;
        let totalPay = 0;
        let totalInt = 0;

        if (R === 0) {
            emi = P / N;
            totalPay = P;
            totalInt = 0;
        } else {
            emi = P * R * Math.pow(1 + R, N) / (Math.pow(1 + R, N) - 1);
            totalPay = emi * N;
            totalInt = totalPay - P;
        }

        document.getElementById('emiResult').textContent = '₹' + Math.round(emi).toLocaleString('en-IN');
        document.getElementById('totalInterest').textContent = '₹' + Math.round(totalInt).toLocaleString('en-IN');
        document.getElementById('totalPayment').textContent = '₹' + Math.round(totalPay).toLocaleString('en-IN');
        if (document.getElementById('totalPrincipalDisp')) {
            document.getElementById('totalPrincipalDisp').textContent = '₹' + P.toLocaleString('en-IN');
        }

        // Calculate donut segments
        const circumference = 2 * Math.PI * 40; // ~251.327
        const principalShare = P / totalPay;
        const interestShare = totalInt / totalPay;

        const principalOffset = 0;
        const interestOffset = principalShare * circumference;

        const principalSeg = document.getElementById('emiPrincipalSegment');
        const interestSeg = document.getElementById('emiInterestSegment');
        const pctLabel = document.getElementById('chartPrincipalPct');

        if (principalSeg && interestSeg && pctLabel) {
            // Set dasharrays and offsets
            principalSeg.style.strokeDasharray = `${circumference}`;
            principalSeg.style.strokeDashoffset = `${principalOffset}`;
            
            interestSeg.style.strokeDasharray = `${circumference}`;
            interestSeg.style.strokeDashoffset = `${circumference - interestOffset}`; // starts where principal ends
            
            pctLabel.textContent = Math.round(principalShare * 100) + '%';
        }
    }
    calcEMI();
    </script>

    <!-- Investment Growth Section -->
    <section class="py-5" aria-labelledby="inv-title">
        <div class="container">


            <!-- Growth Projection Calculator -->
            <div class="card border-0 shadow-lg bg-dark text-white">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <h4 class="fw-bold mb-3"><i
                                    class="fas fa-calculator me-2 text-warning"></i><?= __('home_growth_calculator_title') ?>
                            </h4>
                            <p class="text-white-50 mb-4"><?= __('home_growth_calculator_subtitle') ?></p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label
                                        class="form-label text-white-50 small"><?= __('home_investment_amount') ?></label>
                                    <select class="form-select form-select-sm" id="invAmount" onchange="calcGrowth()" title="<?= __('home_investment_amount') ?>">
                                        <option value="500000"><?= __('home_amount_5l') ?></option>
                                        <option value="1000000" selected><?= __('home_amount_10l') ?></option>
                                        <option value="2500000"><?= __('home_amount_25l') ?></option>
                                        <option value="5000000"><?= __('home_amount_50l') ?></option>
                                        <option value="10000000"><?= __('home_amount_1cr') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-white-50 small"><?= __('home_time_period') ?></label>
                                    <select class="form-select form-select-sm" id="invYears" onchange="calcGrowth()" title="<?= __('home_time_period') ?>">
                                        <option value="5"><?= __('home_years_5') ?></option>
                                        <option value="10" selected><?= __('home_years_10') ?></option>
                                        <option value="15"><?= __('home_years_15') ?></option>
                                        <option value="20"><?= __('home_years_20') ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-white-50 small">&nbsp;</label>
                                    <div class="fw-bold h4 mb-0 pt-1" id="growthResult">₹40,45,558</div>
                                </div>
                            </div>
        </div>
    </section>


            <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
            document.addEventListener('DOMContentLoaded', function() {
                var btns = document.querySelectorAll('.ps-filter-btn');
                var items = document.querySelectorAll('.project-card-item');
                btns.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        btns.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        var f = this.getAttribute('data-filter');
                        items.forEach(function(item) {
                            var loc = item.getAttribute('data-location') || '';
                            if (f === 'all' || loc.indexOf(f) !== -1) {
                                item.style.display = '';
                                item.style.opacity = '0';
                                item.style.transform = 'scale(0.95)';
                                requestAnimationFrame(function() {
                                    item.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                                    item.style.opacity = '1';
                                    item.style.transform = 'scale(1)';
                                });
                            } else {
                                item.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                                item.style.opacity = '0';
                                item.style.transform = 'scale(0.95)';
                                setTimeout(function() { item.style.display = 'none'; }, 250);
                            }
                        });
                    });
                });
            });
            </script>

            <div class="text-center ps-viewall-wrap">
                <a href="<?= BASE_URL ?>/company/projects" class="ps-viewall-btn">
                    <i class="fas fa-th-large"></i> <?= __('nav_all_projects') ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Platform Users - Big Company Social Proof -->
    <section class="py-5 section-gray bg-light" aria-labelledby="users-title">
        <div class="container">
            <div class="text-center mb-5 premium-reveal">
                <div class="capsule-badge mb-3 capsule-amber">
                    <i class="fas fa-users"></i> <?= __('home_trusted_platform') ?>
                </div>
                <h2 id="users-title" class="fw-bold mb-3 iu-navy">
                    <?= __('home_thousands_trust') ?>
                </h2>
                <p class="text-muted fs-6 mx-auto section-desc">
                    <?= __('home_platform_desc') ?>
                </p>
            </div>
            
            <div class="row g-4">
                <!-- Buyers -->
                <div class="col-lg-3 col-md-6">
                    <div class="service-card p-4 text-center hover-lift h-100 premium-reveal">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="fw-bold mb-1 section-heading-lg"><?= __('home_buyers_count') ?></h3>
                        <p class="text-muted fw-bold mb-3 fs-6"><?= __('home_buyers_label') ?></p>
                        <p class="text-muted small mb-0"><?= __('home_buyers_desc') ?></p>
                    </div>
                </div>
                
                <!-- Sellers -->
                <div class="col-lg-3 col-md-6">
                    <div class="service-card p-4 text-center hover-lift h-100 premium-reveal">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3 class="fw-bold mb-1 section-heading-lg"><?= __('home_sellers_count') ?></h3>
                        <p class="text-muted fw-bold mb-3 fs-6"><?= __('home_sellers_label') ?></p>
                        <p class="text-muted small mb-0"><?= __('home_sellers_desc') ?></p>
                    </div>
                </div>
                
                <!-- Renters -->
                <div class="col-lg-3 col-md-6">
                    <div class="service-card p-4 text-center hover-lift h-100 premium-reveal">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-key"></i>
                        </div>
                        <h3 class="fw-bold mb-1 section-heading-lg"><?= __('home_renters_count') ?></h3>
                        <p class="text-muted fw-bold mb-3 fs-6"><?= __('home_renters_label') ?></p>
                        <p class="text-muted small mb-0"><?= __('home_renters_desc') ?></p>
                    </div>
                </div>
                
                <!-- Agents/Associates -->
                <div class="col-lg-3 col-md-6">
                    <div class="service-card p-4 text-center hover-lift h-100 premium-reveal">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="fw-bold mb-1 section-heading-lg"><?= __('home_associates_count') ?></h3>
                        <p class="text-muted fw-bold mb-3 fs-6"><?= __('home_associates_label') ?></p>
                        <p class="text-muted small mb-0"><?= __('home_associates_desc') ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonial Ticker -->
            <div class="glass-panel p-4 mt-5 premium-reveal">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-inline-flex align-items-center gap-1 text-warning fw-bold fs-6">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="text-muted small mt-1"><?= __('home_rating_text') ?></div>
                    </div>
                    <div class="col-md-7">
                        <div class="testimonial-ticker-wrap">
                            <div id="testimonial-ticker" >
                                <p class="fw-bold mb-0 testimonial-ticker-item"><i class="fas fa-quote-left me-2 text-primary"></i><?= __('home_testimonial_tick_1') ?></p>
                                <p class="fw-bold mb-0 testimonial-ticker-item"><i class="fas fa-quote-left me-2 text-primary"></i><?= __('home_testimonial_tick_2') ?></p>
                                <p class="fw-bold mb-0 testimonial-ticker-item"><i class="fas fa-quote-left me-2 text-primary"></i><?= __('home_testimonial_tick_3') ?></p>
                                <p class="fw-bold mb-0 testimonial-ticker-item"><i class="fas fa-quote-left me-2 text-primary"></i><?= __('home_testimonial_tick_4') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-center text-md-end mt-3 mt-md-0">
                        <a href="<?= BASE_URL ?>/testimonials" class="btn btn-premium rounded-pill px-4 py-2">
                            <?= __('home_view_all_reviews') ?> <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services (Modern Cards) -->
    <section class="py-5 section-gradient-gray" aria-labelledby="services-title">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <div class="capsule-badge mb-3 capsule-green">
                    <i class="fas fa-star"></i> <?= __('home_expertise') ?>
                </div>
                <h2 id="services-title" class="fw-bold mb-3"><?= __('our_services') ?></h2>
                <p class="text-muted fs-6 mx-auto section-desc"><?= __('services_tagline') ?></p>
            </div>
            <div class="row g-4 stagger-children">
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('loan')" >
                        <div class="service-icon icon-green">
                            <i class="fas fa-hand-holding-usd fa-2x iu-emerald"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_home_loan') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_home_loan_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill capsule-green"><?= __('home_service_home_loan_badge') ?></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('legal')" >
                        <div class="service-icon icon-indigo">
                            <i class="fas fa-gavel fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_legal') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_legal_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill"><?= __('home_service_legal_badge') ?></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('interior')" >
                        <div class="service-icon icon-amber">
                            <i class="fas fa-couch fa-2x iu-amber"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_interior') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_interior_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill capsule-amber"><?= __('home_interior_badge') ?></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('registry')" >
                        <div class="service-icon icon-red">
                            <i class="fas fa-file-signature fa-2x iu-red"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_registry') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_registry_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill"><?= __('home_service_registry_badge') ?></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('rental')" >
                        <div class="service-icon icon-teal">
                            <i class="fas fa-file-contract fa-2x iu-teal-light"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_rental') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_rental_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill"><?= __('home_service_rental_badge') ?></span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 reveal">
                    <div class="service-card-modern" onclick="openServiceModal('tax')" >
                        <div class="service-icon icon-cyan">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2"><?= __('home_service_tax') ?></h5>
                        <p class="text-muted small mb-3"><?= __('home_service_tax_desc') ?></p>
                        <span class="badge px-3 py-2 rounded-pill"><?= __('home_service_tax_badge') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 p-4 bg-gradient-success" id="serviceModalHeader">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-hand-holding-usd text-white fa-xl" id="serviceModalIcon"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-1" id="serviceModalTitle">
                                <?= __('home_service') ?></h5>
                            <p class="text-white-50 small mb-0" id="serviceModalSubtitle">
                                <?= __('home_complete_real_estate_services') ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="serviceModalBody">
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    window.HOME_T = {
        service_home_loan: <?= json_encode(__('svc_home_loan')) ?>,
        service_home_loan_sub: <?= json_encode(__('svc_home_loan_sub')) ?>,
        service_legal: <?= json_encode(__('svc_legal')) ?>,
        service_legal_sub: <?= json_encode(__('svc_legal_sub')) ?>,
        service_interior: <?= json_encode(__('svc_interior')) ?>,
        service_interior_sub: <?= json_encode(__('svc_interior_sub')) ?>,
        service_registry: <?= json_encode(__('svc_registry')) ?>,
        service_registry_sub: <?= json_encode(__('svc_registry_sub')) ?>,
        service_rental: <?= json_encode(__('svc_rental')) ?>,
        service_rental_sub: <?= json_encode(__('svc_rental_sub')) ?>,
        service_tax: <?= json_encode(__('svc_tax')) ?>,
        service_tax_sub: <?= json_encode(__('svc_tax_sub')) ?>,
        tool_emi: <?= json_encode(__('tool_emi')) ?>,
        tool_emi_sub: <?= json_encode(__('tool_emi_sub')) ?>,
        tool_investment: <?= json_encode(__('tool_investment')) ?>,
        tool_investment_sub: <?= json_encode(__('tool_investment_sub')) ?>,
        tool_stamp: <?= json_encode(__('tool_stamp')) ?>,
        tool_stamp_sub: <?= json_encode(__('tool_stamp_sub')) ?>,
        tool_converter: <?= json_encode(__('tool_converter')) ?>,
        tool_converter_sub: <?= json_encode(__('tool_converter_sub')) ?>,
        tool_eligibility: <?= json_encode(__('tool_eligibility')) ?>,
        tool_eligibility_sub: <?= json_encode(__('tool_eligibility_sub')) ?>,
        tool_valuation: <?= json_encode(__('tool_valuation')) ?>,
        tool_valuation_sub: <?= json_encode(__('tool_valuation_sub')) ?>,
        why_choose_us: <?= json_encode(__('home_js_why_choose')) ?>,
        free_consultation: <?= json_encode(__('home_js_free_consultation')) ?>,
        compare_rates: <?= json_encode(__('home_js_compare_rates')) ?>,
        documentation_support: <?= json_encode(__('home_js_doc_support')) ?>,
        doorstep_service: <?= json_encode(__('home_js_doorstep')) ?>,
        loan_preapproval: <?= json_encode(__('home_js_preapproval')) ?>,
        current_rates: <?= json_encode(__('home_js_current_rates')) ?>,
        calculate_emi: <?= json_encode(__('home_js_calculate_emi')) ?>,
        emi_calc_desc: <?= json_encode(__('home_js_emi_desc')) ?>,
        calculate_now: <?= json_encode(__('home_js_calc_now')) ?>,
        what_we_offer: <?= json_encode(__('home_js_what_we_offer')) ?>,
        sale_deed_drafting: <?= json_encode(__('home_js_sale_deed')) ?>,
        title_verification: <?= json_encode(__('home_js_title_verify')) ?>,
        mutation_records: <?= json_encode(__('home_js_mutation')) ?>,
        legal_notice: <?= json_encode(__('home_js_legal_notice')) ?>,
        legal_protection: <?= json_encode(__('home_js_legal_protection')) ?>,
        verified_advocates: <?= json_encode(__('home_js_verified_advocates')) ?>,
        services_include: <?= json_encode(__('home_js_services_include')) ?>,
        modular_kitchen: <?= json_encode(__('home_js_modular_kitchen')) ?>,
        wardrobe_storage: <?= json_encode(__('home_js_wardrobe')) ?>,
        false_ceiling: <?= json_encode(__('home_js_false_ceiling')) ?>,
        wall_painting: <?= json_encode(__('home_js_wall_painting')) ?>,
        home_furnishing: <?= json_encode(__('home_js_home_furnishing')) ?>,
        starting_prices: <?= json_encode(__('home_js_starting_prices')) ?>,
        free_estimate: <?= json_encode(__('home_js_free_estimate')) ?>,
        free_estimate_desc: <?= json_encode(__('home_js_free_estimate_desc')) ?>,
        we_handle: <?= json_encode(__('home_js_we_handle')) ?>,
        sale_deed_reg: <?= json_encode(__('home_js_sale_deed_reg')) ?>,
        mutation_land: <?= json_encode(__('home_js_mutation_land')) ?>,
        certified_copies: <?= json_encode(__('home_js_certified_copies')) ?>,
        property_tax_receipt: <?= json_encode(__('home_js_tax_receipt')) ?>,
        transfer_title: <?= json_encode(__('home_js_transfer_title')) ?>,
        fast_track: <?= json_encode(__('home_js_fast_track')) ?>,
        fast_track_desc: <?= json_encode(__('home_js_fast_track_desc')) ?>,
        rental_services: <?= json_encode(__('home_js_rental_services')) ?>,
        rental_drafting: <?= json_encode(__('home_js_rental_drafting')) ?>,
        estamping: <?= json_encode(__('home_js_estamping')) ?>,
        landlord_tenant: <?= json_encode(__('home_js_landlord_tenant')) ?>,
        agreement_renewal: <?= json_encode(__('home_js_agreement_renewal')) ?>,
        online_offline: <?= json_encode(__('home_js_online_offline')) ?>,
        estamped: <?= json_encode(__('home_js_estamped')) ?>,
        legally_valid: <?= json_encode(__('home_js_legally_valid')) ?>,
        tax_services: <?= json_encode(__('home_js_tax_services')) ?>,
        tax_calc: <?= json_encode(__('home_js_tax_calc')) ?>,
        tax_payment: <?= json_encode(__('home_js_tax_payment')) ?>,
        tax_exemption: <?= json_encode(__('home_js_tax_exemption')) ?>,
        municipal_coord: <?= json_encode(__('home_js_municipal')) ?>,
        tax_clearance: <?= json_encode(__('home_js_tax_clearance')) ?>,
        online_pay: <?= json_encode(__('home_js_online_pay')) ?>,
        pay_online: <?= json_encode(__('home_js_pay_online')) ?>,
        emi_label: <?= json_encode(__('tool_js_emi_label')) ?>,
        emi_amount_label: <?= json_encode(__('tool_js_emi_amount')) ?>,
        emi_rate_label: <?= json_encode(__('tool_js_emi_rate')) ?>,
        emi_tenure_label: <?= json_encode(__('tool_js_emi_tenure')) ?>,
        monthly_emi: <?= json_encode(__('tool_js_monthly_emi')) ?>,
        total_interest: <?= json_encode(__('tool_js_total_interest')) ?>,
        total_payment: <?= json_encode(__('tool_js_total_payment')) ?>,
        investment_amount: <?= json_encode(__('tool_js_investment_amount')) ?>,
        time_period: <?= json_encode(__('tool_js_time_period')) ?>,
        real_estate: <?= json_encode(__('tool_js_real_estate')) ?>,
        re_wins: <?= json_encode(__('tool_js_re_wins')) ?>,
        highest_returns: <?= json_encode(__('tool_js_highest_returns')) ?>,
        tangible_asset: <?= json_encode(__('tool_js_tangible_asset')) ?>,
        property_price: <?= json_encode(__('tool_js_property_price')) ?>,
        stamp_duty: <?= json_encode(__('tool_js_stamp_duty')) ?>,
        registration: <?= json_encode(__('tool_js_registration')) ?>,
        total_cost: <?= json_encode(__('tool_js_total_cost')) ?>,
        loan_amount: <?= json_encode(__('tool_js_loan_amount')) ?>,
        interest_rate: <?= json_encode(__('tool_js_interest_rate')) ?>,
        tenure: <?= json_encode(__('tool_js_tenure')) ?>,
        monthly_income: <?= json_encode(__('tool_js_monthly_income')) ?>,
        existing_emi: <?= json_encode(__('tool_js_existing_emi')) ?>,
        eligible_for: <?= json_encode(__('tool_js_eligible_for')) ?>,
        max_emi: <?= json_encode(__('tool_js_max_emi')) ?>,
        foir_ratio: <?= json_encode(__('tool_js_foir_ratio')) ?>,
        ai_estimate: <?= json_encode(__('tool_js_ai_estimate')) ?>,
        bedrooms: <?= json_encode(__('tool_js_bedrooms')) ?>,
        furnishing: <?= json_encode(__('tool_js_furnishing')) ?>,
        unfurnished: <?= json_encode(__('tool_js_unfurnished')) ?>,
        semi_furnished: <?= json_encode(__('tool_js_semi_furnished')) ?>,
        fully_furnished: <?= json_encode(__('tool_js_fully_furnished')) ?>,
        estimated_value: <?= json_encode(__('tool_js_estimated_value')) ?>,
        per_sqft: <?= json_encode(__('tool_js_per_sqft')) ?>,
        confidence: <?= json_encode(__('tool_js_confidence')) ?>,
        quick_ref: <?= json_encode(__('tool_js_quick_ref')) ?>,
        result_label: <?= json_encode(__('tool_js_result')) ?>,
        call_label: <?= json_encode(__('home_call_label')) ?>
    };

    function openServiceModal(service) {
        var header = document.getElementById('serviceModalHeader');
        var icon = document.getElementById('serviceModalIcon');
        var title = document.getElementById('serviceModalTitle');
        var subtitle = document.getElementById('serviceModalSubtitle');
        var body = document.getElementById('serviceModalBody');
        var t = window.HOME_T;

        var services = {
            loan: {
                color: 'linear-gradient(135deg, #10b981, #059669)',
                icon: 'fa-hand-holding-usd',
                title: t.service_home_loan,
                subtitle: t.service_home_loan_sub,
                features: [t.free_consultation, t.compare_rates, t.documentation_support, t.doorstep_service, t
                    .loan_preapproval
                ],
                rates: [{
                    b: 'SBI',
                    r: '8.50%'
                }, {
                    b: 'HDFC',
                    r: '8.55%'
                }, {
                    b: 'ICICI',
                    r: '8.55%'
                }, {
                    b: 'PNB',
                    r: '8.50%'
                }],
                ctaHeading: t.calculate_emi,
                ctaDesc: t.emi_calc_desc,
                ctaBtn: t.calculate_now,
                ctaAction: "openToolModal('emi')",
                ctaColor: 'success',
                phone: <?= json_encode($phoneDisplay) ?>,
                whatsapp: 'https://wa.me/<?= htmlspecialchars($phoneRaw ?? '') ?>'
            },
            legal: {
                color: 'linear-gradient(135deg, #6366f1, #0d9488)',
                icon: 'fa-gavel',
                title: t.service_legal,
                subtitle: t.service_legal_sub,
                features: [t.sale_deed_drafting, t.title_verification, t.mutation_records, t.agreement_renewal, t
                    .legal_notice
                ],
                ctaHeading: t.legal_protection,
                ctaDesc: t.verified_advocates,
                ctaColor: 'primary',
                phone: <?= json_encode($phoneDisplay) ?>
            },
            interior: {
                color: 'linear-gradient(135deg, #f59e0b, #d97706)',
                icon: 'fa-couch',
                title: t.service_interior,
                subtitle: t.service_interior_sub,
                features: [t.modular_kitchen, t.wardrobe_storage, t.false_ceiling, t.wall_painting, t
                    .home_furnishing
                ],
                prices: [{
                    tier: '<?= __("tier_basic") ?>',
                    price: '\u20B9249<?= __("home_price_per_sqft") ?>'
                }, {
                    tier: '<?= __("tier_standard") ?>',
                    price: '\u20B9399<?= __("home_price_per_sqft") ?>'
                }, {
                    tier: '<?= __("tier_premium") ?>',
                    price: '\u20B9599<?= __("home_price_per_sqft") ?>'
                }],
                ctaHeading: t.free_estimate,
                ctaDesc: t.free_estimate_desc,
                ctaColor: 'warning'
            },
            registry: {
                color: 'linear-gradient(135deg, #ef4444, #dc2626)',
                icon: 'fa-file-signature',
                title: t.service_registry,
                subtitle: t.service_registry_sub,
                features: [t.sale_deed_reg, t.mutation_land, t.certified_copies, t.property_tax_receipt, t
                    .transfer_title
                ],
                ctaHeading: t.fast_track,
                ctaDesc: t.fast_track_desc,
                ctaColor: 'danger'
            },
            rental: {
                color: 'linear-gradient(135deg, #14b8a6, #0f766e)',
                icon: 'fa-file-contract',
                title: t.service_rental,
                subtitle: t.service_rental_sub,
                features: [t.rental_drafting, t.estamping, t.landlord_tenant, t.agreement_renewal, t
                    .online_offline
                ],
                ctaHeading: t.estamped,
                ctaDesc: t.legally_valid,
                ctaColor: 'primary'
            },
            tax: {
                color: 'linear-gradient(135deg, #06b6d4, #0891b2)',
                icon: 'fa-file-invoice-dollar',
                title: t.service_tax,
                subtitle: t.service_tax_sub,
                features: [t.tax_calc, t.tax_payment, t.tax_exemption, t.municipal_coord, t.tax_clearance],
                ctaHeading: t.online_pay,
                ctaDesc: t.pay_online,
                ctaColor: 'info'
            }
        };

        var svc = services[service] || services.loan;
        header.style.background = svc.color;
        icon.className = 'fas ' + svc.icon + ' text-white fa-xl';
        title.textContent = svc.title;
        subtitle.textContent = svc.subtitle;

        var featuresHtml =
            '<div class="row g-4"><div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-' +
            svc.ctaColor + ' me-2"></i>' + (t.why_choose_us || t.services_include || t.what_we_offer || t.we_handle || t
                .rental_services || t.tax_services) + '</h6><ul class="list-unstyled">';
        svc.features.forEach(function(f) {
            featuresHtml += '<li class="mb-2"><i class="fas fa-check text-' + svc.ctaColor + ' me-2"></i>' + f +
                '</li>';
        });
        featuresHtml += '</ul>';

        if (svc.rates) {
            featuresHtml += '<div class="bg-light rounded p-3 mt-3"><h6 class="fw-bold mb-2">' + t.current_rates +
                '</h6>';
            svc.rates.forEach(function(r) {
                featuresHtml += '<div class="d-flex justify-content-between mb-1"><span>' + r.b +
                    '</span><span class="fw-bold text-success">' + r.r + '</span></div>';
            });
            featuresHtml += '</div>';
        }
        if (svc.prices) {
            featuresHtml += '<div class="bg-light rounded p-3 mt-3"><h6 class="fw-bold mb-2">' + t.starting_prices +
                '</h6>';
            svc.prices.forEach(function(p) {
                featuresHtml += '<div class="d-flex justify-content-between mb-1"><span>' + p.tier +
                    '</span><span class="fw-bold text-warning">' + p.price + '</span></div>';
            });
            featuresHtml += '</div>';
        }
        featuresHtml += '</div>';

        featuresHtml += '<div class="col-md-5"><div class="card border-0 shadow-sm p-4 h-100">';
        featuresHtml += '<h5 class="fw-bold mb-3"><i class="fas fa-paper-plane text-' + svc.ctaColor + ' me-2"></i><?= __("enquire_services", null, "Quick Inquiry") ?></h5>';
        featuresHtml += '<form id="modalServiceInquiryForm" class="small">';
        featuresHtml += '<input type="hidden" name="service_type" value="' + service + '">';
        featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1"><?= __("your_name", null, "Name") ?> *</label><input type="text" class="form-control form-control-sm" name="name" required></div>';
        featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1"><?= __("phone", null, "Phone") ?> *</label><input type="tel" class="form-control form-control-sm" name="phone" required></div>';
        featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1"><?= __("email", null, "Email") ?> *</label><input type="email" class="form-control form-control-sm" name="email" required></div>';
        featuresHtml += '<div class="mb-3"><label class="form-label fw-semibold mb-1"><?= __("additional_details", null, "Details") ?></label><textarea class="form-control form-control-sm" name="message" rows="2" placeholder="<?= __('home_placeholder_tell_us') ?>"></textarea></div>';
        featuresHtml += '<button type="submit" class="btn btn-sm btn-' + svc.ctaColor + ' w-100 mt-1" id="modalSubmitBtn"><i class="fas fa-paper-plane me-1"></i><?= __("submit_inquiry", null, "Submit Inquiry") ?></button>';
        featuresHtml += '<div id="modalFormResponse" class="alert p-2 mt-2 small d-none"></div>';
        featuresHtml += '</form></div></div></div>';

        body.innerHTML = featuresHtml;
        showBootstrapModal('serviceModal');

        // Dynamic event binding for modal form
        const modalForm = document.getElementById('modalServiceInquiryForm');
        if (modalForm) {
            modalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const mSubmitBtn = document.getElementById('modalSubmitBtn');
                const mResponse = document.getElementById('modalFormResponse');
                
                mSubmitBtn.disabled = true;
                mSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><?= __("services_sending", null, "Sending...") ?>';
                
                const formData = new FormData(modalForm);
                fetch('<?php echo BASE_URL; ?>/service-interest', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    mResponse.classList.remove('d-none', 'alert-success', 'alert-danger');
                    mResponse.classList.add(d.success ? 'alert-success' : 'alert-danger');
                    mResponse.textContent = d.message;
                    if (d.success) {
                        modalForm.reset();
                        if (window.APS && window.APS.showNotification) {
                            window.APS.showNotification('<?= __('home_inquiry_success') ?>', 'success');
                        }
                    }
                    mSubmitBtn.disabled = false;
                    mSubmitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>' + '<?= __("submit_inquiry", null, "Submit Inquiry") ?>';
                })
                .catch(err => {
                    mResponse.classList.remove('d-none', 'alert-success');
                    mResponse.classList.add('alert-danger');
                    mResponse.textContent = '<?= __('something_went_wrong') ?>';
                    mSubmitBtn.disabled = false;
                    mSubmitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>' + '<?= __("submit_inquiry", null, "Submit Inquiry") ?>';
                });
            });
        }
    }
    </script>

    <!-- Why Choose Us (Modern) -->
    <section class="py-5 bg-light" aria-labelledby="why-choose-title">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 reveal-left">
                    <span class="section-label"><?= __('home_why_us_label') ?></span>
                    <h2 id="why-choose-title" class="fw-bold mb-4"><?= __('section_why_choose_us') ?></h2>
                    <div class="checklist-item">
                        <div class="check-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= __('why_choose_15_years') ?></h6>
                            <p class="text-muted small mb-0"><?= __('why_choose_15_years_desc') ?></p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <div class="check-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= __('why_choose_rera') ?></h6>
                            <p class="text-muted small mb-0"><?= __('why_choose_rera_desc') ?></p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <div class="check-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= __('why_choose_transparent') ?></h6>
                            <p class="text-muted small mb-0"><?= __('why_choose_transparent_desc') ?></p>
                        </div>
                    </div>
                    <div class="checklist-item">
                        <div class="check-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1"><?= __('why_choose_support') ?></h6>
                            <p class="text-muted small mb-0"><?= __('why_choose_support_desc') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 reveal-right">
                    <div class="glass-card p-4 text-center">
                        <div class="float-slow">
                            <i class="fas fa-headset fa-3x iu-teal"></i>
                        </div>
                        <h4 class="fw-bold"><?= __('need_help') ?></h4>
                        <p class="text-muted mb-4"><?= __('need_help_desc') ?></p>
                        <div class="d-grid gap-3">
                            <a href="tel:<?= $phoneRaw ?>" class="btn btn-lg btn-glow btn-shine">
                                <i class="fas fa-phone me-2"></i><?= __('call_now') ?>
                            </a>
                             <a href="https://wa.me/<?= htmlspecialchars($phoneRaw ?? '') ?>" target="_blank"
                                class="btn btn-outline-dark btn-lg btn-glow">
                                <i class="fab fa-whatsapp me-2"></i><?= __('home_whatsapp') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Invest in Real Estate -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label"><?= __('home_why_real_estate') ?></span>
                <h2 class="fw-bold"><?= __('home_why_real_estate_title') ?></h2>
                <p class="section-subtitle"><?= __('home_why_real_estate_subtitle') ?></p>
            </div>

            <!-- Investment Comparison Chart -->
            <div class="row g-3 g-md-4 mb-5">
                <div class="col-md-3 col-6">
                    <div class="invest-card">
                        <div class="icon-circle ic-green">
                            <i class="fas fa-vector-square fa-2x iu-emerald"></i>
                        </div>
                        <h5 class="fw-bold iu-emerald"><?= __('home_invest_real_estate') ?></h5>
                        <div class="return-pct iu-emerald">15-25%</div>
                        <p class="text-muted small"><?= __('home_avg_annual_returns') ?></p>
                        <div class="progress mb-2 progress-thin">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="badge">â­�
                            <?= __('home_best_investment') ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="invest-card">
                        <div class="icon-circle ic-amber">
                            <i class="fas fa-coins fa-2x iu-amber"></i>
                        </div>
                        <h5 class="fw-bold iu-amber"><?= __('home_invest_fd') ?></h5>
                        <div class="return-pct">5-7%</div>
                        <p class="text-muted small"><?= __('home_avg_annual_returns') ?></p>
                        <div class="progress mb-2 progress-thin">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="badge bg-secondary"><?= __('home_low_returns') ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="invest-card">
                        <div class="icon-circle ic-red">
                            <i class="fas fa-chart-line fa-2x iu-red"></i>
                        </div>
                        <h5 class="fw-bold iu-red"><?= __('home_invest_stock') ?></h5>
                        <div class="return-pct">10-14%</div>
                        <p class="text-muted small"><?= __('home_high_risk') ?></p>
                        <div class="progress mb-2 progress-thin">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="badge bg-warning text-dark"><?= __('home_moderate') ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="invest-card">
                        <div class="icon-circle ic-teal">
                            <i class="fas fa-ring fa-2x iu-teal"></i>
                        </div>
                        <h5 class="fw-bold iu-teal"><?= __('home_invest_gold') ?></h5>
                        <div class="return-pct iu-teal">8-10%</div>
                        <p class="text-muted small"><?= __('home_gold_desc') ?></p>
                        <div class="progress mb-2 progress-thin">
                            <div class="progress-bar"></div>
                        </div>
                        <span class="badge"><?= __('home_safe_haven') ?></span>
                    </div>
                </div>
            </div>

            <!-- Why Invest Details -->
            <div class="row align-items-center mb-5">
                <div class="col-lg-5 mb-4 mb-lg-0 text-center">
                    <img src="<?php echo BASE_URL; ?>/assets/images/hero/luxury-home-1.jpg"
                        alt="<?= __('home_why_real_estate_title') ?>"
                        class="img-fluid rounded-4 shadow-lg"
                        onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                </div>
                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <div class="check-icon ci-green">
                                    <i class="fas fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h6><?= __('home_capital_appreciation') ?></h6>
                                    <p><?= __('home_capital_appreciation_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <div class="check-icon ci-amber">
                                    <i class="fas fa-hand-holding-dollar"></i>
                                </div>
                                <div>
                                    <h6><?= __('home_passive_income') ?></h6>
                                    <p><?= __('home_passive_income_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <div class="check-icon ci-teal">
                                    <i class="fas fa-building-columns"></i>
                                </div>
                                <div>
                                    <h6><?= __('home_loan_against_property') ?></h6>
                                    <p><?= __('home_loan_against_property_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <div class="check-icon ci-red">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div>
                                    <h6><?= __('home_inflation_hedge') ?></h6>
                                    <p><?= __('home_inflation_hedge_desc') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-white-50"><?= __('home_re_cagr') ?></span>
                                    <span class="small fw-bold text-success" id="reValue">₹52,33,855</span>
                                </div>
                                <div class="progress mb-2 progress-thin">
                                    <div class="progress-bar bg-success" id="reBar" ></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-white-50"><?= __('home_fd_cagr') ?></span>
                                    <span class="small fw-bold text-warning" id="fdValue">₹17,90,848</span>
                                </div>
                                <div class="progress mb-2 progress-thin">
                                    <div class="progress-bar bg-warning" id="fdBar" ></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-white-50"><?= __('home_gold_cagr') ?></span>
                                    <span class="small fw-bold text-info" id="goldValue">₹23,67,364</span>
                                </div>
                                <div class="progress mb-0 progress-thin">
                                    <div class="progress-bar bg-primary" id="goldBar" ></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 text-center mt-4 mt-lg-0">
                            <img src="<?php echo BASE_URL; ?>/assets/images/hero/luxury-home-2.jpg"
                                alt="<?= __('home_growth_calculator_title') ?>"
                                class="img-fluid rounded-4 shadow-lg mb-3"
                                onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <h3 class="fw-bold text-warning mb-2"><?= __('home_real_estate_wins') ?></h3>
                            <p class="text-white-50 small mb-0"><i
                                    class="fas fa-check-circle text-success me-1"></i><?= __('home_highest_returns') ?>
                            </p>
                            <p class="text-white-50 small mb-0"><i
                                    class="fas fa-check-circle text-success me-1"></i><?= __('home_lowest_risk') ?>
                            </p>
                            <p class="text-white-50 small mb-0"><i
                                    class="fas fa-check-circle text-success me-1"></i><?= __('home_dual_benefit') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    function calcGrowth() {
        const amt = parseFloat(document.getElementById('invAmount').value);
        const yrs = parseFloat(document.getElementById('invYears').value);

        const re = amt * Math.pow(1.18, yrs);
        const fd = amt * Math.pow(1.06, yrs);
        const gold = amt * Math.pow(1.09, yrs);

        const maxVal = Math.max(re, fd, gold);

        document.getElementById('reValue').textContent = '₹' + Math.round(re).toLocaleString('en-IN');
        document.getElementById('fdValue').textContent = '₹' + Math.round(fd).toLocaleString('en-IN');
        document.getElementById('goldValue').textContent = '₹' + Math.round(gold).toLocaleString('en-IN');
        document.getElementById('growthResult').textContent = '₹' + Math.round(re).toLocaleString('en-IN');

        document.getElementById('reBar').style.width = (re / maxVal * 100) + '%';
        document.getElementById('fdBar').style.width = (fd / maxVal * 100) + '%';
        document.getElementById('goldBar').style.width = (gold / maxVal * 100) + '%';
    }
    calcGrowth();
    </script>


    <!-- Tool Modal -->
    <div class="modal fade" id="toolModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 p-4" id="toolModalHeader"
                    >
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center">
                            <i class="fas fa-calculator text-white fa-xl" id="toolModalIcon"></i>
                        </div>
                        <div>
                            <h5 class="modal-title text-white fw-bold mb-1" id="toolModalTitle"><?= __('home_tool') ?>
                            </h5>
                            <p class="text-white-50 small mb-0" id="toolModalSubtitle">
                                <?= __('home_calculate_instantly') ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-dark" id="toolModalBody">
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
    var T = {
        tool: <?= json_encode(__('home_tool')) ?>,
        calcInstantly: <?= json_encode(__('home_calculate_instantly')) ?>,
        emiCalc: <?= json_encode(__('home_tool_emi')) ?>,
        emiCalcDesc: <?= json_encode(__('home_tool_emi_desc')) ?>,
        invCalc: <?= json_encode(__('home_tool_investment')) ?>,
        invCalcDesc: <?= json_encode(__('home_tool_investment_desc')) ?>,
        stampCalc: <?= json_encode(__('home_tool_stamp_duty')) ?>,
        stampCalcDesc: <?= json_encode(__('home_tool_stamp_duty_desc')) ?>,
        convCalc: <?= json_encode(__('home_tool_plot_converter')) ?>,
        convCalcDesc: <?= json_encode(__('home_tool_plot_converter_desc')) ?>,
        eligCalc: <?= json_encode(__('home_tool_loan_eligibility')) ?>,
        eligCalcDesc: <?= json_encode(__('home_tool_loan_eligibility_desc')) ?>,
        valCalc: <?= json_encode(__('home_tool_valuation')) ?>,
        valCalcDesc: <?= json_encode(__('home_tool_valuation_desc')) ?>,
        loanAmt: <?= json_encode(__('calc_loan_amount')) ?>,
        intRate: <?= json_encode(__('calc_interest_rate')) ?>,
        tenure: <?= json_encode(__('calc_tenure')) ?>,
        monthlyEmi: <?= json_encode(__('calc_monthly_emi')) ?>,
        totalInt: <?= json_encode(__('calc_total_interest')) ?>,
        totalPay: <?= json_encode(__('calc_total_payment')) ?>,
        invAmt: <?= json_encode(__('calc_investment_amount')) ?>,
        timePeriod: <?= json_encode(__('calc_time_period')) ?>,
        reEstate: <?= json_encode(__('calc_real_estate')) ?>,
        fd: <?= json_encode(__('calc_fd')) ?>,
        gold: <?= json_encode(__('calc_gold')) ?>,
        reWins: <?= json_encode(__('calc_real_estate_wins')) ?>,
        highestRet: <?= json_encode(__('calc_highest_returns')) ?>,
        tangible: <?= json_encode(__('calc_tangible_asset')) ?>,
        growthRental: <?= json_encode(__('calc_growth_rental')) ?>,
        propPrice: <?= json_encode(__('calc_property_price')) ?>,
        state: <?= json_encode(__('calc_state')) ?>,
        up: <?= json_encode(__('calc_up')) ?>,
        bihar: <?= json_encode(__('calc_bihar')) ?>,
        mp: <?= json_encode(__('calc_mp')) ?>,
        delhi: <?= json_encode(__('calc_delhi')) ?>,
        maharashtra: <?= json_encode(__('calc_maharashtra')) ?>,
        propType: <?= json_encode(__('calc_property_type')) ?>,
        male: <?= json_encode(__('calc_male')) ?>,
        female: <?= json_encode(__('calc_female')) ?>,
        joint: <?= json_encode(__('calc_joint')) ?>,
        stampDuty: <?= json_encode(__('calc_stamp_duty')) ?>,
        regFee: <?= json_encode(__('calc_registration')) ?>,
        totalCost: <?= json_encode(__('calc_total_cost')) ?>,
        value: <?= json_encode(__('calc_value')) ?>,
        from: <?= json_encode(__('calc_from')) ?>,
        to: <?= json_encode(__('calc_to')) ?>,
        result: <?= json_encode(__('calc_result')) ?>,
        quickRef: <?= json_encode(__('calc_quick_reference')) ?>,
        monthlyInc: <?= json_encode(__('calc_monthly_income')) ?>,
        existEmi: <?= json_encode(__('calc_existing_emi')) ?>,
        tenureYrs: <?= json_encode(__('calc_tenure_years')) ?>,
        eligFor: <?= json_encode(__('calc_eligible_for')) ?>,
        maxEmi: <?= json_encode(__('calc_max_emi')) ?>,
        foir: <?= json_encode(__('calc_foir_ratio')) ?>,
        aiEst: <?= json_encode(__('calc_ai_estimate')) ?>,
        location: <?= json_encode(__('calc_location')) ?>,
        areaSqft: <?= json_encode(__('calc_area_sqft')) ?>,
        bedrooms: <?= json_encode(__('calc_bedrooms')) ?>,
        furnishing: <?= json_encode(__('calc_furnishing')) ?>,
        unfurnished: <?= json_encode(__('calc_unfurnished')) ?>,
        semiFurn: <?= json_encode(__('calc_semi_furnished')) ?>,
        fullFurn: <?= json_encode(__('calc_fully_furnished')) ?>,
        estValue: <?= json_encode(__('calc_estimated_value')) ?>,
        perSqft: <?= json_encode(__('calc_per_sqft')) ?>,
        confidence: <?= json_encode(__('calc_confidence')) ?>,
        high: <?= json_encode(__('calc_high')) ?>,
        years: <?= json_encode(__('home_years')) ?>,
        sqft: <?= json_encode(__('home_sqft')) ?>,
        sqm: <?= json_encode(__('home_sqm')) ?>,
        acre: <?= json_encode(__('home_unit_acre')) ?>,
        hectare: <?= json_encode(__('home_hectare')) ?>,
        bigha: <?= json_encode(__('home_bigha')) ?>,
        gaj: <?= json_encode(__('home_gaj')) ?>,
        sqYd: <?= json_encode(__('home_sq_yd')) ?>,
        katha: <?= json_encode(__('home_katha')) ?>,
        marla: <?= json_encode(__('home_marla')) ?>,
        medium: <?= json_encode(__('home_medium')) ?>,
        calculating: <?= json_encode(__('home_calculating')) ?>,
        valFailed: <?= json_encode(__('home_valuation_failed')) ?>,
        valError: <?= json_encode(__('home_valuation_error')) ?>,
        na: <?= json_encode(__('home_na')) ?>,
        bhk1: <?= json_encode(__('home_bhk_1')) ?>,
        bhk2: <?= json_encode(__('home_bhk_2')) ?>,
        bhk3: <?= json_encode(__('home_bhk_3')) ?>,
        bhk4: <?= json_encode(__('home_bhk_4')) ?>,
        pricePerSqft: <?= json_encode(__('home_price_per_sqft')) ?>,
        salaryMonthly: <?= json_encode(__('home_salary_monthly')) ?>,
        amountLakh: <?= json_encode(__('home_amount_lakh')) ?>
    };

    function openToolModal(tool) {
        var header = document.getElementById('toolModalHeader');
        var icon = document.getElementById('toolModalIcon');
        var title = document.getElementById('toolModalTitle');
        var subtitle = document.getElementById('toolModalSubtitle');
        var body = document.getElementById('toolModalBody');

        var configs = {
            emi: {
                color: 'linear-gradient(135deg, #0d9488, #0f766e)',
                icon: 'fa-calculator',
                title: T.emiCalc,
                subtitle: T.emiCalcDesc,
                html: getEMICalculator()
            },
            investment: {
                color: 'linear-gradient(135deg, #11998e, #38ef7d)',
                icon: 'fa-arrow-trend-up',
                title: T.invCalc,
                subtitle: T.invCalcDesc,
                html: getInvestmentCalculator()
            },
            stamp: {
                color: 'linear-gradient(135deg, #f093fb, #f5576c)',
                icon: 'fa-file-contract',
                title: T.stampCalc,
                subtitle: T.stampCalcDesc,
                html: getStampDutyCalculator()
            },
            converter: {
                color: 'linear-gradient(135deg, #4facfe, #00f2fe)',
                icon: 'fa-vector-square',
                title: T.convCalc,
                subtitle: T.convCalcDesc,
                html: getPlotConverter()
            },
            eligibility: {
                color: 'linear-gradient(135deg, #fa709a, #fee140)',
                icon: 'fa-hand-holding-dollar',
                title: T.eligCalc,
                subtitle: T.eligCalcDesc,
                html: getLoanEligibility()
            },
            valuation: {
                color: 'linear-gradient(135deg, #a8edea, #fed6e3)',
                icon: 'fa-house-chimney',
                title: T.valCalc,
                subtitle: T.valCalcDesc,
                html: getPropertyValuation()
            }
        };

        var cfg = configs[tool] || configs.emi;
        header.style.background = cfg.color;
        icon.className = 'fas ' + cfg.icon + ' text-white fa-xl';
        title.textContent = cfg.title;
        subtitle.textContent = cfg.subtitle;
        body.innerHTML = cfg.html;

        showBootstrapModal('toolModal');
    }

    function showBootstrapModal(id) {
        if (typeof bootstrap !== 'undefined') {
            new bootstrap.Modal(document.getElementById(id)).show();
        } else {
            setTimeout(function() { showBootstrapModal(id); }, 200);
        }
    }

    // Make function globally accessible
    window.openToolModal = openToolModal;

    function getEMICalculator() {
        return '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.loanAmt +
            ' <span class="text-warning" id="mEmiAmt">₹50,00,000</span></label>' +
            '<input type="range" class="form-range" min="100000" max="50000000" step="100000" value="5000000" oninput="mCalcEMI()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.intRate +
            ' <span class="text-warning" id="mEmiRate">8.5%</span></label>' +
            '<input type="range" class="form-range" min="5" max="20" step="0.1" value="8.5" oninput="mCalcEMI()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.tenure +
            ' <span class="text-warning" id="mEmiTenure">20 ' + T.years + '</span></label>' +
            '<input type="range" class="form-range" min="1" max="30" step="1" value="20" oninput="mCalcEMI()"></div></div>' +
            '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">' + T.monthlyEmi + '</p>' +
             '<p class="display-5 fw-bold mb-0 text-warning" id="mEmiResult">₹42,426</p><hr class="border-light opacity-50 my-3">' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">' + T.totalInt +
            '</span><span class="fw-bold" id="mEmiInterest">₹51,82,240</span></div>' +
            '<div class="d-flex justify-content-between mt-2"><span class="text-white-50">' + T.totalPay +
            '</span><span class="fw-bold" id="mEmiTotal">₹1,01,82,240</span></div>' +
            '</div></div></div>';
    }

    function getInvestmentCalculator() {
        return '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.invAmt + '</label>' +
            '<select class="form-select" id="mInvAmt" onchange="mCalcInv()">' +
            '<option value="500000">' + '<?= __("home_amount_5l") ?>' + '</option><option value="1000000" selected>' +
            '<?= __("home_amount_10l") ?>' + '</option>' +
            '<option value="2500000">' + '<?= __("home_amount_25l") ?>' + '</option><option value="5000000">' +
            '<?= __("home_amount_50l") ?>' + '</option><option value="10000000">' + '<?= __("home_amount_1cr") ?>' +
            '</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.timePeriod + '</label>' +
            '<select class="form-select" id="mInvYrs" onchange="mCalcInv()">' +
            '<option value="5">' + '<?= __("home_years_5") ?>' + '</option><option value="10" selected>' +
            '<?= __("home_years_10") ?>' + '</option>' +
            '<option value="15">' + '<?= __("home_years_15") ?>' + '</option><option value="20">' +
            '<?= __("home_years_20") ?>' + '</option></select></div>' +
            '<div class="mt-3">' +
            '<div class="d-flex justify-content-between mb-1"><span>' + T.reEstate +
            ' <span class="text-success">(18%)</span></span><span class="fw-bold text-success" id="mInvRE">₹52,33,855</span></div>' +
            '<div class="progress mb-2"><div class="progress-bar bg-success" id="mInvREBar" ></div></div>' +
            '<div class="d-flex justify-content-between mb-1"><span>' + T.fd +
            ' <span class="text-warning">(6%)</span></span><span class="fw-bold text-warning" id="mInvFD">₹17,90,848</span></div>' +
            '<div class="progress mb-2"><div class="progress-bar bg-warning" id="mInvFDBar" ></div></div>' +
            '<div class="d-flex justify-content-between"><span>' + T.gold +
             ' <span class="text-info">(9%)</span></span><span class="fw-bold text-info" id="mInvGold">₹23,67,364</span></div>' +
            '<div class="progress"><div class="progress-bar bg-primary" id="mInvGoldBar" ></div></div></div></div>' +
            '<div class="col-md-5 text-center d-flex flex-column justify-content-center">' +
            '<div class="bg-success bg-opacity-10 rounded-4 p-4"><i class="fas fa-trophy fa-3x text-success mb-3"></i>' +
            '<h4 class="fw-bold text-success">' + T.reWins + '</h4>' +
            '<p class="text-muted small mb-1"><i class="fas fa-check-circle text-success me-1"></i>' + T.highestRet +
            '</p>' +
            '<p class="text-muted small mb-1"><i class="fas fa-check-circle text-success me-1"></i>' + T.tangible +
            '</p>' +
            '<p class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i>' + T.growthRental +
            '</p></div></div></div>';
    }

    function getStampDutyCalculator() {
        return '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.propPrice + '</label>' +
            '<input type="number" class="form-control form-control-lg" id="mStampPrice" value="5000000" oninput="mCalcStamp()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.state + '</label>' +
            '<select class="form-select" id="mStampState" onchange="mCalcStamp()">' +
            '<option value="up">' + T.up + '</option><option value="bihar">' + T.bihar + '</option>' +
            '<option value="mp">' + T.mp + '</option><option value="delhi">' + T.delhi + '</option>' +
            '<option value="maharashtra">' + T.maharashtra + '</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.propType + '</label>' +
            '<select class="form-select" id="mStampType" onchange="mCalcStamp()">' +
            '<option value="male">' + T.male + '</option><option value="female">' + T.female + '</option>' +
            '<option value="joint">' + T.joint + '</option></select></div></div>' +
            '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">' + T.propPrice +
            '</span><span class="fw-bold" id="mStampBase">₹50,00,000</span></div>' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">' + T.stampDuty +
            '</span><span class="fw-bold text-warning" id="mStampDuty">₹2,50,000</span></div>' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">' + T.regFee +
            '</span><span class="fw-bold text-info" id="mStampReg">₹50,000</span></div>' +
             '<hr class="border-light opacity-50 my-2">' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">' + T.totalCost +
            '</span><span class="fw-bold text-success fs-5" id="mStampTotal">₹53,00,000</span></div>' +
            '</div></div></div>';
    }

    function getPlotConverter() {
        var units = [T.sqft, T.sqm, T.acre, T.hectare, T.bigha, T.gaj, T.sqYd, T.katha, T.marla];
        var unitOptions = '';
        for (var i = 0; i < units.length; i++) unitOptions += '<option value="' + i + '">' + units[i] + '</option>';
        return '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.value + '</label>' +
            '<input type="number" class="form-control form-control-lg" id="mConvVal" value="1000" oninput="mCalcConv()"></div>' +
            '<div class="row g-2 mb-3"><div class="col-6"><label class="form-label small">' + T.from + '</label>' +
            '<select class="form-select" id="mConvFrom" onchange="mCalcConv()">' + unitOptions + '</select></div>' +
            '<div class="col-6"><label class="form-label small">' + T.to + '</label>' +
            '<select class="form-select" id="mConvTo" onchange="mCalcConv()"><option value="0" selected>'+T.sqft+'</option><option value="1">'+T.sqm+'</option><option value="2">'+T.acre+'</option><option value="3">'+T.hectare+'</option><option value="4">'+T.bigha+'</option><option value="5">'+T.gaj+'</option><option value="6">'+T.sqYd+'</option><option value="7">'+T.katha+'</option><option value="8">'+T.marla+'</option></select></div></div>' +
            '<div class="alert alert-success mb-0"><i class="fas fa-exchange-alt me-2"></i>' + T.result +
            ' <strong id="mConvResult">0.0913 Acre</strong></div></div>' +
            '<div class="col-md-5"><div class="bg-light rounded-4 p-4 h-100">' +
            '<h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-info"></i>' + T.quickRef + '</h6>' +
            '<div class="small"><strong>1 '+T.bigha+'</strong> = 27,000 '+T.sqft+'<br><strong>1 '+T.gaj+'</strong> = 9 '+T.sqft+'<br><strong>1 '+T.acre+'</strong> = 43,560 '+T.sqft+'<br><strong>1 '+T.hectare+'</strong> = 2.47 '+T.acre+'<br><strong>1 '+T.katha+'</strong> = 1,361 '+T.sqft+'<br><strong>1 '+T.marla+'</strong> = 272 '+T.sqft+'</div>' +
            '</div></div></div>';
    }

    function getLoanEligibility() {
        return '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.monthlyInc + '</label>' +
            '<input type="number" class="form-control form-control-lg" id="mEligIncome" value="60000" oninput="mCalcElig()" step="1000"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.existEmi + '</label>' +
            '<input type="number" class="form-control" id="mEligEmi" value="0" oninput="mCalcElig()"></div>' +
            '<div class="row g-2 mb-3"><div class="col-6"><label class="form-label small">' + T.intRate + '</label>' +
            '<select class="form-select" id="mEligRate" onchange="mCalcElig()">' +
            '<option value="8.5">8.5%</option><option value="9.0" selected>9.0%</option><option value="9.5">9.5%</option><option value="10.0">10%</option></select></div>' +
            '<div class="col-6"><label class="form-label small">' + T.tenureYrs + '</label>' +
            '<select class="form-select" id="mEligTenure" onchange="mCalcElig()">' +
            '<option value="5">' + '<?= __("home_years_5") ?>' + '</option><option value="10">' +
            '<?= __("home_years_10") ?>' + '</option><option value="15">' + '<?= __("home_years_15") ?>' +
            '</option><option value="20" selected>' + '<?= __("home_years_20") ?>' + '</option><option value="25">' + '<?= __("home_years_25") ?>' +
            '</option><option value="30">' + '<?= __("home_years_30") ?>' + '</option></select></div></div></div>' +
            '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">' + T.eligFor + '</p>' +
             '<p class="display-5 fw-bold mb-0 text-success" id="mEligResult">₹27,23,250</p><hr class="border-light opacity-50 my-3">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">' + T.maxEmi +
            '</span><span class="fw-bold" id="mEligMaxEmi">₹27,000</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">' + T.foir +
            '</span><span class="fw-bold" id="mEligFoir">45%</span></div>' +
            '</div></div></div>';
    }

    function getPropertyValuation() {
        return '<p class="text-center text-muted mb-3"><i class="fas fa-robot me-2"></i>' + T.aiEst + '</p>' +
            '<div class="row g-4">' +
            '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.propType + '</label>' +
            '<select class="form-select" id="mValType" onchange="mCalcVal()">' +
            '<option value="plot">' + '<?= __("home_prop_plot") ?>' + '</option><option value="house">' + '<?= __("home_prop_house") ?>' + '</option><option value="flat">' + '<?= __("home_prop_flat") ?>' + '</option><option value="commercial">' + '<?= __("home_prop_commercial") ?>' + '</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.location + '</label>' +
            '<select class="form-select" id="mValLoc" onchange="mCalcVal()">' +
            '<option value="gorakhpur"><?= __("loc_gorakhpur") ?></option><option value="lucknow"><?= __("loc_lucknow") ?></option>' +
            '<option value="kushinagar"><?= __("loc_kushinagar") ?></option><option value="varanasi"><?= __("loc_varanasi") ?></option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">' + T.areaSqft + '</label>' +
            '<input type="number" class="form-control form-control-lg" id="mValArea" value="1500" oninput="mCalcVal()" step="10"></div>' +
            '<div class="d-flex gap-2"><div class="flex-fill"><label class="form-label small">' + T.bedrooms +
            '</label>' +
            '<select class="form-select" id="mValBhk" onchange="mCalcVal()"><option value="1">' + T.bhk1 + '</option><option value="2" selected>' + T.bhk2 + '</option><option value="3">' + T.bhk3 + '</option><option value="4">' + T.bhk4 + '</option></select></div>' +
            '<div class="flex-fill"><label class="form-label small">' + T.furnishing + '</label>' +
            '<select class="form-select" id="mValFurn" onchange="mCalcVal()"><option value="0.9">' + T.unfurnished +
            '</option><option value="1" selected>' + T.semiFurn + '</option><option value="1.2">' + T.fullFurn +
            '</option></select></div></div></div>' +
            '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">' + T.estValue + '</p>' +
             '<p class="display-5 fw-bold mb-0 text-warning" id="mValResult">₹22,50,000</p><hr class="border-light opacity-50 my-3">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">' + T.pricePerSqft +
            '</span><span class="fw-bold" id="mValPsf">₹1,500</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">' + T.confidence +
            '</span><span class="fw-bold text-success" id="mValConf">' + T.high + '</span></div>' +
            '</div></div></div>';
    }

    function mCalcEMI() {
        var inputs = document.querySelectorAll('#toolModalBody input[type="range"]');
        var P = parseFloat(inputs[0].value);
        var R = parseFloat(inputs[1].value) / 12 / 100;
        var N = parseFloat(inputs[2].value) * 12;
        document.getElementById('mEmiAmt').textContent = '\u20B9' + P.toLocaleString('en-IN');
        document.getElementById('mEmiRate').textContent = inputs[1].value + '%';
        document.getElementById('mEmiTenure').textContent = inputs[2].value + ' ' + T.years;
        var emi = P * R * Math.pow(1 + R, N) / (Math.pow(1 + R, N) - 1);
        var totalPay = emi * N;
        var totalInt = totalPay - P;
        document.getElementById('mEmiResult').textContent = '\u20B9' + Math.round(emi).toLocaleString('en-IN');
        document.getElementById('mEmiInterest').textContent = '\u20B9' + Math.round(totalInt).toLocaleString('en-IN');
        document.getElementById('mEmiTotal').textContent = '\u20B9' + Math.round(totalPay).toLocaleString('en-IN');
    }

    function mCalcInv() {
        var amt = parseFloat(document.getElementById('mInvAmt').value);
        var yrs = parseFloat(document.getElementById('mInvYrs').value);
        var re = amt * Math.pow(1.18, yrs);
        var fd = amt * Math.pow(1.06, yrs);
        var gold = amt * Math.pow(1.09, yrs);
        var maxVal = Math.max(re, fd, gold);
        document.getElementById('mInvRE').textContent = '\u20B9' + Math.round(re).toLocaleString('en-IN');
        document.getElementById('mInvFD').textContent = '\u20B9' + Math.round(fd).toLocaleString('en-IN');
        document.getElementById('mInvGold').textContent = '\u20B9' + Math.round(gold).toLocaleString('en-IN');
        document.getElementById('mInvREBar').style.width = (re / maxVal * 100) + '%';
        document.getElementById('mInvFDBar').style.width = (fd / maxVal * 100) + '%';
        document.getElementById('mInvGoldBar').style.width = (gold / maxVal * 100) + '%';
    }

    function mCalcStamp() {
        var price = parseFloat(document.getElementById('mStampPrice').value) || 0;
        var state = document.getElementById('mStampState').value;
        var rates = {
            up: 0.05,
            bihar: 0.06,
            mp: 0.06,
            delhi: 0.05,
            maharashtra: 0.05
        };
        var rate = rates[state] || 0.05;
        var stamp = price * rate;
        var reg = price * 0.01;
        document.getElementById('mStampBase').textContent = '\u20B9' + price.toLocaleString('en-IN');
        document.getElementById('mStampDuty').textContent = '\u20B9' + Math.round(stamp).toLocaleString('en-IN');
        document.getElementById('mStampReg').textContent = '\u20B9' + Math.round(reg).toLocaleString('en-IN');
        document.getElementById('mStampTotal').textContent = '\u20B9' + Math.round(price + stamp + reg).toLocaleString(
            'en-IN');
    }

    var convFactors = [1, 0.092903, 2.2957e-5, 9.2903e-6, 3.7037e-5, 0.111111, 0.111111, 0.000734, 0.003673];

    function mCalcConv() {
        var val = parseFloat(document.getElementById('mConvVal').value) || 0;
        var from = parseInt(document.getElementById('mConvFrom').value);
        var to = parseInt(document.getElementById('mConvTo').value);
        var sqft = val / convFactors[from];
        var result = sqft * convFactors[to];
        var unitNames = [T.sqft, T.sqm, T.acre, T.hectare, T.bigha, T.gaj, T.sqYd, T.katha, T.marla];
        document.getElementById('mConvResult').textContent = result.toFixed(4) + ' ' + unitNames[to];
    }

    function mCalcElig() {
        var income = parseFloat(document.getElementById('mEligIncome').value) || 0;
        var existing = parseFloat(document.getElementById('mEligEmi').value) || 0;
        var rate = parseFloat(document.getElementById('mEligRate').value) / 12 / 100;
        var tenure = parseFloat(document.getElementById('mEligTenure').value) * 12;
        var maxEmi = (income * 0.5) - existing;
        var eligible = maxEmi * (Math.pow(1 + rate, tenure) - 1) / (rate * Math.pow(1 + rate, tenure));
        document.getElementById('mEligResult').textContent = '\u20B9' + Math.round(Math.max(0, eligible))
            .toLocaleString('en-IN');
        document.getElementById('mEligMaxEmi').textContent = '\u20B9' + Math.round(Math.max(0, maxEmi)).toLocaleString(
            'en-IN');
        document.getElementById('mEligFoir').textContent = Math.round((existing + maxEmi) / income * 100) + '%';
    }

    var valRates = {
        gorakhpur: {
            plot: 1200,
            house: 1800,
            flat: 2200,
            commercial: 3000
        },
        lucknow: {
            plot: 2000,
            house: 3000,
            flat: 3500,
            commercial: 5000
        },
        kushinagar: {
            plot: 800,
            house: 1200,
            flat: 1500,
            commercial: 2000
        },
        varanasi: {
            plot: 1500,
            house: 2500,
            flat: 2800,
            commercial: 4000
        }
    };

    async function mCalcVal() {
            const type = document.getElementById('mValType').value;
            const loc = document.getElementById('mValLoc').value;
            const area = parseFloat(document.getElementById('mValArea').value) || 0;
            const bhk = parseInt(document.getElementById('mValBhk').value);
            const furnSelect = document.getElementById('mValFurn');
            const furn = furnSelect.options[furnSelect.selectedIndex].text;

            // Show loading state
            document.getElementById('mValResult').textContent = T.calculating;
            document.getElementById('mValPsf').textContent = '-';
            document.getElementById('mValConf').textContent = '-';

            try {
                const response = await fetch('<?= BASE_URL ?>/api/ai-valuation/calculator', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
                    },
                    body: JSON.stringify({
                        property_type: type,
                        location: loc,
                        area_sqft: area,
                        bedrooms: bhk,
                        furnishing: furn
                    })
                });
                if (!response.ok) throw new Error('Network response was not ok');
                const result = await response.json();

                if (result.success && result.data) {
                    document.getElementById('mValResult').textContent = '₹' + (result.data
                        .estimated_value_formatted || T.na);
                    document.getElementById('mValPsf').textContent = '₹' + (result.data.price_per_sqft_formatted ||
                        T.na);
                    document.getElementById('mValConf').textContent = result.data.confidence || T.medium;
                } else {
                    throw new Error(result.error || T.valFailed);
                }
            } catch (error) {
                console.error('Valuation Error:', error);
                document.getElementById('mValResult').textContent = T.valError;
            }
        }
    </script>

    <!-- Testimonials (Modern Cards) -->
    <section class="py-5" aria-labelledby="testimonials-title">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <div class="capsule-badge mb-3">
                    <i class="fas fa-quote-left"></i> <?= __('home_testimonials_badge') ?>
                </div>
                <h2 id="testimonials-title" class="fw-bold mb-3"><?= __('testimonials_title') ?></h2>
            </div>
            <div class="row g-4 stagger-children">
                <div class="col-md-4 mb-4 reveal">
                    <div class="testimonial-card-modern">
                        <div class="text-warning mb-3 section-subtitle">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4 testimonial-text"><?= __('home_testimonial_1') ?></p>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/hero.svg"
                                alt="<?= __('home_testimonial_1_name') ?>"
                                class="rounded-circle me-3" width="50" height="50" >
                            <div>
                                <h6 class="mb-0 fw-bold"><?= __('home_testimonial_1_name') ?></h6>
                                <small class="testimonial-location"><i class="fas fa-map-marker-alt me-1"></i><?= __('home_testimonial_1_location') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 reveal">
                    <div class="testimonial-card-modern">
                        <div class="text-warning mb-3 section-subtitle">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="card-text mb-4 testimonial-text"><?= __('home_testimonial_2') ?></p>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/hero.svg"
                                alt="<?= __('home_testimonial_2_name') ?>"
                                class="rounded-circle me-3" width="50" height="50" >
                            <div>
                                <h6 class="mb-0 fw-bold"><?= __('home_testimonial_2_name') ?></h6>
                                <small class="testimonial-location"><i class="fas fa-map-marker-alt me-1"></i><?= __('home_testimonial_2_location') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 reveal">
                    <div class="testimonial-card-modern">
                        <div class="text-warning mb-3 section-subtitle">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p class="card-text mb-4 testimonial-text"><?= __('home_testimonial_3') ?></p>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/hero.svg"
                                alt="<?= __('home_testimonial_3_name') ?>"
                                class="rounded-circle me-3" width="50" height="50" >
                            <div>
                                <h6 class="mb-0 fw-bold"><?= __('home_testimonial_3_name') ?></h6>
                                <small class="testimonial-location"><i class="fas fa-map-marker-alt me-1"></i><?= __('home_testimonial_3_location') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Join APS Dream Home — COMMENTED OUT: moved to /opportunity page
    <section class="py-5 bg-light">
        ... (career section with salary, insurance, commission, training, MLM, reasons — see /opportunity page)
    </section>
    -->

    <!-- CTA (Modern) -->
    <section class="py-5 text-white text-center cta-modern section-teal-gradient">
        <!-- Decorative elements -->
        <div ></div>
        <div ></div>
        <div class="container">
            <div class="reveal">
                <span class="badge px-3 py-2 mb-3">
                    <i class="fas fa-phone-volume me-1"></i>
                    <?= __('home_get_in_touch') ?>
                </span>
                <h2 class="fw-bold mb-3"><?= __('cta_title') ?></h2>
                <p class="mb-4"><?= __('cta_subtitle') ?></p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="tel:<?= $phoneRaw ?>" class="btn btn-premium btn-lg px-4 btn-glow btn-shine">
                        <i class="fas fa-phone me-2"></i><?= __('home_call_now') ?>
                    </a>
                    <a href="https://wa.me/<?= htmlspecialchars($phoneRaw ?? '') ?>" target="_blank" class="btn btn-light btn-lg text-success px-4 btn-glow">
                        <i class="fab fa-whatsapp me-2"></i><?= __('home_whatsapp') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Particles.js for Hero Section -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if(document.getElementById('particles-canvas')) {
        particlesJS("particles-canvas", {
            "particles": {
                "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#ffffff" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": false, "anim": { "enable": false } },
                "size": { "value": 3, "random": true, "anim": { "enable": false } },
                "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.4, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": { "enable": true, "mode": "grab" },
                    "onclick": { "enable": true, "mode": "push" },
                    "resize": true
                },
                "modes": {
                    "grab": { "distance": 140, "line_linked": { "opacity": 1 } },
                    "push": { "particles_nb": 4 }
                }
            },
            "retina_detect": true
        });
    }
});
</script>
<script nonce="<?php echo $GLOBALS['csp_nonce'] ?? ''; ?>">
window.addEventListener('scroll', function() {
  var journeySection = document.getElementById('dream-home-journey');
  if(!journeySection) return;
  var rect = journeySection.getBoundingClientRect();
  var sectionHeight = journeySection.offsetHeight;
  var viewportHeight = window.innerHeight;
  var scrollY = -rect.top;
  var progress = Math.max(0, Math.min(1, scrollY / (sectionHeight - viewportHeight)));
  var totalSteps = 4;
  var step = Math.min(Math.floor(progress * totalSteps) + 1, totalSteps);
  var backgrounds = document.querySelectorAll('.journey-bg');
  var textSteps = document.querySelectorAll('.journey-step');
  backgrounds.forEach((bg, idx) => { bg.classList.toggle('active', idx === step - 1); });
  textSteps.forEach((txt, idx) => { txt.classList.toggle('active', idx === step - 1); });
});
</script>

