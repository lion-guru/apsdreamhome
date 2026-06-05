<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-white">
                <span class="badge bg-white bg-opacity-15 text-white px-3 py-2 mb-3" style="background:rgba(255,255,255,0.12);"><?= __('trusted_by') ?> 500+ Families</span>
                <h1 class="fw-bold"><?= __('hero_title') ?></h1>
                <p class="lead mb-4"><?= __('hero_subtitle') ?></p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php
                        // A/B test: homepage_cta â€” variant-aware hero CTA copy
                        $heroVariant = $_SESSION['experiments']['homepage_cta'] ?? null;
                        $heroCtaText = match ($heroVariant) {
                            'urgent'   => 'Book Your Plot Now â€” Limited Inventory!',
                            'family'   => 'Find Your Family\'s Dream Home Today',
                            default    => __('hero_cta'),
                        };
                        // A/B test: cta_button_color â€” variant-aware hero CTA color
                        $ctaColorVariant = $_SESSION['experiments']['cta_button_color'] ?? 'blue';
                        $ctaColorClass = match ($ctaColorVariant) {
                            'green'  => 'ab-btn-green',
                            'orange' => 'ab-btn-orange',
                            default  => 'btn-warning',
                        };
                    ?>
                    <a href="<?php echo BASE_URL; ?>/company/projects"
                       class="btn btn-lg <?= htmlspecialchars($ctaColorClass) ?>"
                       data-experiment="homepage_cta"
                       data-variant="<?php echo htmlspecialchars((string) $heroVariant, ENT_QUOTES); ?>"
                       data-color-experiment="cta_button_color"
                       data-color-variant="<?php echo htmlspecialchars((string) $ctaColorVariant, ENT_QUOTES); ?>"
                       id="hero-cta"><?= htmlspecialchars($heroCtaText) ?></a>
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-outline-light btn-lg"><?= __('nav_post_property') ?></a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0">
                    <div class="card-header text-white text-center py-3" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                        <h5 class="mb-0 fw-semibold"><i class="fas fa-search me-2"></i><?= __('search') ?> <?= __('properties') ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo BASE_URL; ?>/properties" method="GET">
                            <div class="mb-3">
                                <select name="listing" class="form-select">
                                    <option value="">Buy / Rent</option>
                                    <option value="sell">Buy</option>
                                    <option value="rent">Rent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="type" class="form-select">
                                    <option value="">Property Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="plot">Plot/Land</option>
                                    <option value="house">House/Villa</option>
                                    <option value="flat">Flat/Apartment</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="location" class="form-select">
                                    <option value="">Select Location</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Kushinagar">Kushinagar</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Ayodhya">Ayodhya</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select name="budget" class="form-select">
                                    <option value="">Budget</option>
                                    <option value="under_5l">Under â‚¹5 Lakh</option>
                                    <option value="5_10l">â‚¹5-10 Lakh</option>
                                    <option value="10_20l">â‚¹10-20 Lakh</option>
                                    <option value="20_50l">â‚¹20-50 Lakh</option>
                                    <option value="above_50l">Above â‚¹50 Lakh</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100 btn-lg">
                                <i class="fas fa-search me-2"></i><?= __('search') ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Links -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=residential" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="icon-wrap" style="background:rgba(79,70,229,0.08);">
                            <i class="fas fa-home fa-xl" style="color:#4f46e5;"></i>
                        </div>
                        <h6><?= __('nav_residential') ?></h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=commercial" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="icon-wrap" style="background:rgba(16,185,129,0.08);">
                            <i class="fas fa-store fa-xl" style="color:#10b981;"></i>
                        </div>
                        <h6><?= __('nav_commercial') ?></h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=plot" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="icon-wrap" style="background:rgba(245,158,11,0.08);">
                            <i class="fas fa-vector-square fa-xl" style="color:#f59e0b;"></i>
                        </div>
                        <h6><?= __('nav_plots') ?></h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?php echo BASE_URL; ?>/list-property" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="icon-wrap" style="background:rgba(6,182,212,0.08);">
                            <i class="fas fa-plus-circle fa-xl" style="color:#06b6d4;"></i>
                        </div>
                        <h6><?= __('nav_post_property') ?></h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $hero_stats['years_experience']; ?>+</div>
                    <div class="stat-label"><?= __('years_experience') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $hero_stats['projects_completed']; ?>+</div>
                    <div class="stat-label"><?= __('projects_completed') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $hero_stats['happy_customers']; ?>+</div>
                    <div class="stat-label"><?= __('happy_clients') ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $hero_stats['awards_won']; ?></div>
                    <div class="stat-label"><?= __('awards_won') ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- EMI Calculator Section -->
<section class="py-5 emi-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-white bg-opacity-10 text-white px-3 py-2 mb-3" style="background:rgba(255,255,255,0.08);">Free Tool</span>
            <h2 class="fw-bold text-white"><?= __('emi_calculator') ?></h2>
            <p class="text-white-50" style="font-size:1.05rem;"><?= __('emi_subtitle') ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0">
                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Loan Amount: <span id="loanAmtDisplay" class="text-primary">â‚¹50,00,000</span></label>
                                    <input type="range" class="form-range" id="loanAmount" min="100000" max="50000000" step="100000" value="5000000" oninput="calcEMI()">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>â‚¹1 Lakh</span>
                                        <span>â‚¹5 Crore</span>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Interest Rate: <span id="rateDisplay" class="text-primary">8.5%</span></label>
                                    <input type="range" class="form-range" id="interestRate" min="5" max="20" step="0.1" value="8.5" oninput="calcEMI()">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>5%</span>
                                        <span>20%</span>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Loan Tenure: <span id="tenureDisplay" class="text-primary">20 Years</span></label>
                                    <input type="range" class="form-range" id="loanTenure" min="1" max="30" step="1" value="20" oninput="calcEMI()">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>1 Year</span>
                                        <span>30 Years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="result-card bg-dark text-white">
                                    <p class="text-white-50 mb-1 small text-uppercase" style="letter-spacing:0.08em;">Your Monthly EMI</p>
                                    <p class="display-4 fw-bold mb-0" id="emiResult" style="color:#818cf8;">â‚¹42,426</p>
                                    <hr class="border-secondary my-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-white-50">Total Interest</span>
                                        <span class="fw-bold text-white" id="totalInterest">â‚¹51,82,240</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-white-50">Total Payment</span>
                                        <span class="fw-bold text-white" id="totalPayment">â‚¹1,01,82,240</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-white-50 mt-3 small"><i class="fas fa-info-circle me-1"></i>EMI calculated on monthly reducing balance. Actual rates may vary by lender.</p>
            </div>
        </div>
    </div>
</section>

<script>
function calcEMI() {
    const P = parseFloat(document.getElementById('loanAmount').value);
    const R = parseFloat(document.getElementById('interestRate').value) / 12 / 100;
    const N = parseFloat(document.getElementById('loanTenure').value) * 12;

    document.getElementById('loanAmtDisplay').textContent = 'â‚¹' + P.toLocaleString('en-IN');
    document.getElementById('rateDisplay').textContent = document.getElementById('interestRate').value + '%';
    document.getElementById('tenureDisplay').textContent = document.getElementById('loanTenure').value + ' Years';

    if (R === 0) {
        document.getElementById('emiResult').textContent = 'â‚¹' + Math.round(P / N).toLocaleString('en-IN');
    } else {
        const emi = P * R * Math.pow(1 + R, N) / (Math.pow(1 + R, N) - 1);
        const totalPay = emi * N;
        const totalInt = totalPay - P;
        document.getElementById('emiResult').textContent = 'â‚¹' + Math.round(emi).toLocaleString('en-IN');
        document.getElementById('totalInterest').textContent = 'â‚¹' + Math.round(totalInt).toLocaleString('en-IN');
        document.getElementById('totalPayment').textContent = 'â‚¹' + Math.round(totalPay).toLocaleString('en-IN');
    }
}
calcEMI();
</script>

<!-- Our Projects -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Our Portfolio</span>
            <h2 class="fw-bold"><?= __('section_our_projects') ?></h2>
            <p class="section-subtitle"><?= __('projects_subtitle') ?></p>
        </div>
        <div class="row">
            <?php
            $hasProjects = !empty($featured_properties);
            if (!$hasProjects):
                // Fallback projects from database
                $fallbackProjects = [
                    ['title' => 'Suryoday Heights Phase 1', 'city' => 'Gorakhpur', 'price' => 'Starting â‚¹5.5 Lakhs', 'slug' => 'suryoday-colony', 'status' => 'Available', 'img' => 'gorakhpur/suryoday.jpg'],
                    ['title' => 'Raghunath Nagri', 'city' => 'Gorakhpur', 'price' => 'Starting â‚¹7.5 Lakhs', 'slug' => 'raghunath-nagri', 'status' => 'Available', 'img' => 'gorakhpur/raghunath nagri motiram.JPG'],
                    ['title' => 'Braj Radha Enclave', 'city' => 'Lucknow', 'price' => 'Starting â‚¹12 Lakhs', 'slug' => 'braj-radha-nagri', 'status' => 'Available', 'img' => 'gorakhpur/suryoday1.jpeg'],
                    ['title' => 'Budh Bihar Colony', 'city' => 'Kushinagar', 'price' => 'Starting â‚¹3.5 Lakhs', 'slug' => 'budh-bihar-colony', 'status' => 'Available', 'img' => 'kushinagar/budh-bihar.jpg'],
                ];
                foreach ($fallbackProjects as $project):
            ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="project-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img loading="lazy" src="<?php echo BASE_URL; ?>/assets/images/projects/<?= $project['img'] ?>" class="w-100" style="height:190px;object-fit:cover;" alt="<?= htmlspecialchars($project['title'] ?? 'Property image') ?>" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-2 px-3 py-1"><?= $project['status'] ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?= $project['title'] ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?= $project['city'] ?></p>
                            <p class="price"><?= $project['price'] ?></p>
                            <a href="<?php echo BASE_URL; ?>/colony/<?= $project['slug'] ?>" class="btn btn-outline-primary btn-sm px-4"><?= __('view_details') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
                <?php foreach (array_slice($featured_properties, 0, 4) as $project): 
                    $slug = $project['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project['title']));
                    $imgPath = '/assets/images/projects/';
                    if (stripos($project['title'], 'Suryoday') !== false) {
                        $imgPath .= 'gorakhpur/suryoday.jpg';
                    } elseif (stripos($project['title'], 'Raghunath') !== false) {
                        $imgPath .= 'gorakhpur/raghunath nagri motiram.JPG';
                    } elseif (stripos($project['title'], 'Braj') !== false) {
                        $imgPath .= 'gorakhpur/suryoday1.jpeg';
                    } elseif (stripos($project['title'], 'Budh') !== false) {
                        $imgPath .= 'kushinagar/budh-bihar.jpg';
                    } else {
                        $imgPath .= 'placeholder/property.svg';
                    }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="project-card h-100">
                        <div class="position-relative overflow-hidden">
                            <img loading="lazy" src="<?php echo BASE_URL . $imgPath; ?>" class="w-100" style="height:190px;object-fit:cover;" alt="<?php echo htmlspecialchars($project['title'] ?? 'Property image'); ?>" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-2 px-3 py-1"><?php echo $project['status']; ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($project['city']); ?></p>
                            <p class="price"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/colony/<?php echo $slug; ?>" class="btn btn-outline-primary btn-sm px-4"><?= __('view_details') ?></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-gradient btn-lg"><?= __('nav_all_projects') ?></a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-5" style="background:#f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Our Expertise</span>
            <h2 class="fw-bold"><?= __('our_services') ?></h2>
            <p class="section-subtitle"><?= __('services_tagline') ?></p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('loan')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(16,185,129,0.1);">
                            <i class="fas fa-hand-holding-usd fa-2x" style="color:#10b981;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Home Loan</h5>
                        <p class="text-muted small">SBI, HDFC, ICICI, PNB, Axis â€” best rates, easy processing, doorstep service.</p>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Starting 8.5% p.a.</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('legal')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(99,102,241,0.1);">
                            <i class="fas fa-gavel fa-2x" style="color:#6366f1;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Legal Services</h5>
                        <p class="text-muted small">Registry, Mutation, Sale Deed, Agreement â€” complete property documentation legally verified.</p>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">100% Legal</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('interior')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(245,158,11,0.1);">
                            <i class="fas fa-couch fa-2x" style="color:#f59e0b;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Interior Design</h5>
                        <p class="text-muted small">Modular kitchen, wardrobe, false ceiling, flooring â€” complete home interiors.</p>
                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">â‚¹249/sqft</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('registry')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(239,68,68,0.1);">
                            <i class="fas fa-file-signature fa-2x" style="color:#ef4444;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Property Registry</h5>
                        <p class="text-muted small">Complete property registration with sub-registrar. Mutation, certified copies, transfer.</p>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Fast Track</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('rental')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(139,92,246,0.1);">
                            <i class="fas fa-file-contract fa-2x" style="color:#8b5cf6;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Rental Agreement</h5>
                        <p class="text-muted small">11-month rental agreement, e-stamping, notarization. Landlord aur tenant dono ke liye.</p>
                        <span class="badge bg-purple bg-opacity-10 text-purple px-3 py-2 rounded-pill">e-Stamped</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-0 shadow-sm service-card" onclick="openServiceModal('tax')">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper mx-auto" style="background: rgba(6,182,212,0.1);">
                            <i class="fas fa-file-invoice-dollar fa-2x" style="color:#06b6d4;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Property Tax</h5>
                        <p class="text-muted small">Property tax calculation, payment, exemption. Municipal corporation ke saath complete support.</p>
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">Online Pay</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 p-4" id="serviceModalHeader" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-hand-holding-usd text-white fa-xl" id="serviceModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-1" id="serviceModalTitle">Service</h5>
                        <p class="text-white-50 small mb-0" id="serviceModalSubtitle">Complete real estate services</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="serviceModalBody">
            </div>
        </div>
    </div>
</div>

<script>
function openServiceModal(service) {
    var header = document.getElementById('serviceModalHeader');
    var icon = document.getElementById('serviceModalIcon');
    var title = document.getElementById('serviceModalTitle');
    var subtitle = document.getElementById('serviceModalSubtitle');
    var body = document.getElementById('serviceModalBody');

    var services = {
        loan: {
            color: 'linear-gradient(135deg, #10b981, #059669)',
            icon: 'fa-hand-holding-usd',
            title: 'Home Loan Assistance',
            subtitle: 'Best rates from all major banks â€” SBI, HDFC, ICICI, PNB, Axis, Kotak',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>Why Choose Us?</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-success me-2"></i>Free loan consultation & eligibility check</li>' +
                '<li class="mb-2"><i class="fas fa-check text-success me-2"></i>Compare rates from 12+ banks</li>' +
                '<li class="mb-2"><i class="fas fa-check text-success me-2"></i>Documentation & processing support</li>' +
                '<li class="mb-2"><i class="fas fa-check text-success me-2"></i>Doorstep service â€” aapke ghar aayenge</li>' +
                '<li class="mb-2"><i class="fas fa-check text-success me-2"></i>Loan pre-approval in 24 hours</li></ul>' +
                '<div class="bg-light rounded p-3 mt-3"><h6 class="fw-bold mb-2">Current Interest Rates</h6>' +
                '<div class="d-flex justify-content-between mb-1"><span>SBI</span><span class="fw-bold text-success">8.50%</span></div>' +
                '<div class="d-flex justify-content-between mb-1"><span>HDFC</span><span class="fw-bold text-success">8.55%</span></div>' +
                '<div class="d-flex justify-content-between mb-1"><span>ICICI</span><span class="fw-bold text-success">8.55%</span></div>' +
                '<div class="d-flex justify-content-between"><span>PNB</span><span class="fw-bold text-success">8.50%</span></div></div></div>' +
                '<div class="col-md-5"><div class="bg-success bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center">' +
                '<i class="fas fa-calculator fa-3x text-success mb-3"></i><h5 class="fw-bold">Calculate EMI</h5>' +
                '<p class="text-muted small">Use our free EMI calculator</p>' +
                '<a href="#" class="btn btn-success" onclick="openToolModal(\'emi\');return false;"><i class="fas fa-calculator me-2"></i>Calculate Now</a>' +
                '<hr class="my-3"><p class="small text-muted mb-1">ðŸ“ž Call: +91 92771 21112</p>' +
                '<p class="small text-muted mb-0">ðŸ’¬ <a href="https://wa.me/919277121112" target="_blank">WhatsApp</a></p></div></div></div>'
        },
        legal: {
            color: 'linear-gradient(135deg, #6366f1, #4f46e5)',
            icon: 'fa-gavel',
            title: 'Legal Services',
            subtitle: 'Complete legal support for all property transactions',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-primary me-2"></i>What We Offer</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Sale Deed drafting & registration</li>' +
                '<li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Property title verification & due diligence</li>' +
                '<li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Mutation of property records</li>' +
                '<li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Rental / Lease agreement lawyer review</li>' +
                '<li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Legal notice & dispute resolution</li></ul></div>' +
                '<div class="col-md-5"><div class="bg-primary bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center">' +
                '<i class="fas fa-shield-halved fa-3x text-primary mb-3"></i><h5 class="fw-bold">100% Legal Protection</h5>' +
                '<p class="text-muted small mb-1">Verified advocates & property experts</p>' +
                '<hr><p class="small text-muted mb-0">ðŸ“ž Call: +91 92771 21112</p></div></div></div>'
        },
        interior: {
            color: 'linear-gradient(135deg, #f59e0b, #d97706)',
            icon: 'fa-couch',
            title: 'Interior Design',
            subtitle: 'Turnkey interiors â€” modern, modular, affordable',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-warning me-2"></i>Services Include</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Modular kitchen design & installation</li>' +
                '<li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Wardrobe, cupboards & storage solutions</li>' +
                '<li class="mb-2"><i class="fas fa-check text-warning me-2"></i>False ceiling, lighting & flooring</li>' +
                '<li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Wall painting, wallpaper & textures</li>' +
                '<li class="mb-2"><i class="fas fa-check text-warning me-2"></i>Complete home furnishing</li></ul>' +
                '<div class="bg-light rounded p-3 mt-3"><h6 class="fw-bold mb-2">Starting Prices</h6>' +
                '<div class="d-flex justify-content-between mb-1"><span>Basic</span><span class="fw-bold text-warning">â‚¹249/sqft</span></div>' +
                '<div class="d-flex justify-content-between mb-1"><span>Standard</span><span class="fw-bold text-warning">â‚¹399/sqft</span></div>' +
                '<div class="d-flex justify-content-between"><span>Premium</span><span class="fw-bold text-warning">â‚¹599/sqft</span></div></div></div>' +
                '<div class="col-md-5"><div class="bg-warning bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center">' +
                '<i class="fas fa-paint-roller fa-3x text-warning mb-3"></i><h5 class="fw-bold">Free Estimate</h5>' +
                '<p class="text-muted small">Get a free interior design estimate</p></div></div></div>'
        },
        registry: {
            color: 'linear-gradient(135deg, #ef4444, #dc2626)',
            icon: 'fa-file-signature',
            title: 'Property Registry',
            subtitle: 'Fast, legal & transparent property registration',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-danger me-2"></i>We Handle</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Sale deed registration at sub-registrar office</li>' +
                '<li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Mutation of land records & khatauni</li>' +
                '<li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Certified copies of property documents</li>' +
                '<li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Property tax receipt & records</li>' +
                '<li class="mb-2"><i class="fas fa-check text-danger me-2"></i>Transfer of property title</li></ul></div>' +
                '<div class="col-md-5"><div class="bg-danger bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center">' +
                '<i class="fas fa-clock fa-3x text-danger mb-3"></i><h5 class="fw-bold">Fast Track</h5>' +
                '<p class="text-muted small">Registry complete in 7-10 days</p></div></div></div>'
        },
        rental: {
            color: 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
            icon: 'fa-file-contract',
            title: 'Rental Agreement',
            subtitle: 'Legal rental agreements with e-stamping',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-purple me-2"></i>Rental Services</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-purple me-2"></i>11-month rental agreement drafting</li>' +
                '<li class="mb-2"><i class="fas fa-check text-purple me-2"></i>E-stamping & notarization</li>' +
                '<li class="mb-2"><i class="fas fa-check text-purple me-2"></i>Landlord & tenant both covered</li>' +
                '<li class="mb-2"><i class="fas fa-check text-purple me-2"></i>Agreement renewal & termination</li>' +
                '<li class="mb-2"><i class="fas fa-check text-purple me-2"></i>Online & offline delivery</li></ul></div>' +
                '<div class="col-md-5"><div class="bg-purple bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center" style="background: rgba(139,92,246,0.1);">' +
                '<i class="fas fa-stamp fa-3x text-purple mb-3" style="color:#8b5cf6;"></i><h5 class="fw-bold">e-Stamped</h5>' +
                '<p class="text-muted small">Legally valid across India</p></div></div></div>'
        },
        tax: {
            color: 'linear-gradient(135deg, #06b6d4, #0891b2)',
            icon: 'fa-file-invoice-dollar',
            title: 'Property Tax',
            subtitle: 'Property tax calculation, payment & exemption support',
            html: '<div class="row g-4">' +
                '<div class="col-md-7"><h6 class="fw-bold mb-3"><i class="fas fa-check-circle text-info me-2"></i>Tax Services</h6>' +
                '<ul class="list-unstyled"><li class="mb-2"><i class="fas fa-check text-info me-2"></i>Property tax calculation & verification</li>' +
                '<li class="mb-2"><i class="fas fa-check text-info me-2"></i>Online payment assistance</li>' +
                '<li class="mb-2"><i class="fas fa-check text-info me-2"></i>Tax exemption & rebate filing</li>' +
                '<li class="mb-2"><i class="fas fa-check text-info me-2"></i>Municipal corporation coordination</li>' +
                '<li class="mb-2"><i class="fas fa-check text-info me-2"></i>Outstanding tax clearance certificate</li></ul></div>' +
                '<div class="col-md-5"><div class="bg-info bg-opacity-10 rounded-4 p-4 text-center h-100 d-flex flex-column justify-content-center">' +
                '<i class="fas fa-building-columns fa-3x text-info mb-3"></i><h5 class="fw-bold">Online Pay</h5>' +
                '<p class="text-muted small">Pay property tax online</p></div></div></div>'
        }
    };

    var svc = services[service] || services.loan;
    header.style.background = svc.color;
    icon.className = 'fas ' + svc.icon + ' text-white fa-xl';
    title.textContent = svc.title;
    subtitle.textContent = svc.subtitle;
    body.innerHTML = svc.html;

    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}
</script>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="section-label">Why Us</span>
                <h2 class="fw-bold mb-4"><?= __('section_why_choose_us') ?></h2>
                <div class="checklist-item">
                    <div class="check-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <h6><?= __('why_choose_15_years') ?></h6>
                        <p><?= __('why_choose_15_years_desc') ?></p>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="check-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <h6><?= __('why_choose_rera') ?></h6>
                        <p><?= __('why_choose_rera_desc') ?></p>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="check-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <h6><?= __('why_choose_transparent') ?></h6>
                        <p><?= __('why_choose_transparent_desc') ?></p>
                    </div>
                </div>
                <div class="checklist-item">
                    <div class="check-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <h6><?= __('why_choose_support') ?></h6>
                        <p><?= __('why_choose_support_desc') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg" style="border-radius:var(--radius-md);">
                    <div class="card-body p-4 text-center">
                        <div style="width:80px;height:80px;border-radius:50%;background:rgba(79,70,229,0.08);display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
                            <i class="fas fa-headset fa-3x" style="color:#4f46e5;"></i>
                        </div>
                        <h4><?= __('need_help') ?></h4>
                        <p class="text-muted mb-4"><?= __('need_help_desc') ?></p>
                        <div class="d-grid gap-3">
                            <a href="tel:+919277121112" class="btn btn-gradient btn-lg">
                                <i class="fas fa-phone me-2"></i><?= __('call_now') ?>
                            </a>
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-outline-dark btn-lg">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
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
            <span class="section-label" style="background:rgba(245,158,11,0.1);color:#d97706;">Why Real Estate?</span>
            <h2 class="fw-bold">Land, Plot ya Property Mein Paisa Kyo Lagayein?</h2>
            <p class="section-subtitle">Compare real estate with other investment options â€” results speak louder than words!</p>
        </div>

        <!-- Investment Comparison Chart -->
        <div class="row g-3 g-md-4 mb-5">
            <div class="col-md-3 col-6">
                <div class="invest-card" style="border-top:4px solid #10b981;">
                    <div class="icon-circle" style="background:rgba(16,185,129,0.08);">
                        <i class="fas fa-vector-square fa-2x" style="color:#10b981;"></i>
                    </div>
                    <h5 class="fw-bold" style="color:#10b981;">Real Estate</h5>
                    <div class="return-pct" style="color:#10b981;">15-25%</div>
                    <p class="text-muted small">Average Annual Returns</p>
                    <div class="progress mb-2" style="height:6px;">
                        <div class="progress-bar" style="width:85%;background:#10b981;"></div>
                    </div>
                    <span class="badge" style="background:#10b981;color:#fff;">â­ Best Investment</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="invest-card" style="border-top:4px solid #f59e0b;">
                    <div class="icon-circle" style="background:rgba(245,158,11,0.08);">
                        <i class="fas fa-coins fa-2x" style="color:#f59e0b;"></i>
                    </div>
                    <h5 class="fw-bold" style="color:#f59e0b;">Fixed Deposit</h5>
                    <div class="return-pct" style="color:#1e293b;">5-7%</div>
                    <p class="text-muted small">Average Annual Returns</p>
                    <div class="progress mb-2" style="height:6px;">
                        <div class="progress-bar" style="width:25%;background:#f59e0b;"></div>
                    </div>
                    <span class="badge bg-secondary">Low Returns</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="invest-card" style="border-top:4px solid #ef4444;">
                    <div class="icon-circle" style="background:rgba(239,68,68,0.08);">
                        <i class="fas fa-chart-line fa-2x" style="color:#ef4444;"></i>
                    </div>
                    <h5 class="fw-bold" style="color:#ef4444;">Stock Market</h5>
                    <div class="return-pct">10-14%</div>
                    <p class="text-muted small">High Risk / Volatile</p>
                    <div class="progress mb-2" style="height:6px;">
                        <div class="progress-bar" style="width:50%;background:#ef4444;"></div>
                    </div>
                    <span class="badge bg-warning text-dark">Moderate</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="invest-card" style="border-top:4px solid #4f46e5;">
                    <div class="icon-circle" style="background:rgba(79,70,229,0.08);">
                        <i class="fas fa-ring fa-2x" style="color:#4f46e5;"></i>
                    </div>
                    <h5 class="fw-bold" style="color:#4f46e5;">Gold</h5>
                    <div class="return-pct" style="color:#4f46e5;">8-10%</div>
                    <p class="text-muted small">Safe but No Passive Income</p>
                    <div class="progress mb-2" style="height:6px;">
                        <div class="progress-bar" style="width:35%;background:#4f46e5;"></div>
                    </div>
                    <span class="badge" style="background:rgba(6,182,212,0.15);color:#0891b2;">Safe Haven</span>
                </div>
            </div>
        </div>

        <!-- Why Invest Details -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-5 mb-4 mb-lg-0 text-center">
                <div style="font-size: 200px; line-height: 1; opacity: 0.15; position: relative;" class="text-success">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
                <div style="position: relative; margin-top: -180px;">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px; animation: pulse-glow 2s infinite;">
                        <i class="fas fa-landmark fa-3x text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="checklist-item">
                            <div class="check-icon" style="background:rgba(16,185,129,0.1);color:#10b981;">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <h6>Capital Appreciation</h6>
                                <p>Land prices double every 5-7 years in developing areas. Plot value grows faster than any FD or gold.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="checklist-item">
                            <div class="check-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
                                <i class="fas fa-hand-holding-dollar"></i>
                            </div>
                            <div>
                                <h6>Passive Income</h6>
                                <p>Rent out property for monthly income. Unlike gold or FD, real estate gives dual benefits â€” growth + income.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="checklist-item">
                            <div class="check-icon" style="background:rgba(79,70,229,0.1);color:#4f46e5;">
                                <i class="fas fa-building-columns"></i>
                            </div>
                            <div>
                                <h6>Loan Against Property</h6>
                                <p>Use your plot as collateral for business or personal loans at lower interest rates.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="checklist-item">
                            <div class="check-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div>
                                <h6>Inflation Hedge</h6>
                                <p>Real estate beats inflation by 5-8% annually. Your money in land grows while its value in bank shrinks.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Growth Projection Calculator -->
        <div class="card border-0 shadow-lg bg-dark text-white">
            <div class="card-body p-5">
                <div class="row align-items-center">
                    <div class="col-lg-7">
                        <h4 class="fw-bold mb-3"><i class="fas fa-calculator me-2 text-warning"></i>Investment Growth Calculator</h4>
                        <p class="text-white-50 mb-4">See how your investment grows in real estate vs FD vs Gold:</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">Investment Amount</label>
                                <select class="form-select form-select-sm" id="invAmount" onchange="calcGrowth()">
                                    <option value="500000">â‚¹5 Lakh</option>
                                    <option value="1000000" selected>â‚¹10 Lakh</option>
                                    <option value="2500000">â‚¹25 Lakh</option>
                                    <option value="5000000">â‚¹50 Lakh</option>
                                    <option value="10000000">â‚¹1 Crore</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">Time Period</label>
                                <select class="form-select form-select-sm" id="invYears" onchange="calcGrowth()">
                                    <option value="5">5 Years</option>
                                    <option value="10" selected>10 Years</option>
                                    <option value="15">15 Years</option>
                                    <option value="20">20 Years</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-white-50 small">&nbsp;</label>
                                <div class="fw-bold h4 mb-0 pt-1" id="growthResult">â‚¹40,45,558</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">Real Estate (18% CAGR)</span>
                                <span class="small fw-bold text-success" id="reValue">â‚¹52,33,855</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-success" id="reBar" style="width: 100%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">FD (6% CAGR)</span>
                                <span class="small fw-bold text-warning" id="fdValue">â‚¹17,90,848</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" id="fdBar" style="width: 34%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">Gold (9% CAGR)</span>
                                <span class="small fw-bold text-primary" id="goldValue">â‚¹23,67,364</span>
                            </div>
                            <div class="progress mb-0" style="height: 6px;">
                                <div class="progress-bar bg-primary" id="goldBar" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center mt-4 mt-lg-0">
                        <div style="font-size: 100px; line-height: 1; opacity: 0.15;">
                            <i class="fas fa-chart-simple"></i>
                        </div>
                        <div style="position: relative; margin-top: -90px;">
                            <h3 class="fw-bold text-warning mb-2">Real Estate Wins!</h3>
                            <p class="text-white-50 small mb-0"><i class="fas fa-check-circle text-success me-1"></i>Highest returns among all asset classes</p>
                            <p class="text-white-50 small mb-0"><i class="fas fa-check-circle text-success me-1"></i>Lowest risk with tangible asset</p>
                            <p class="text-white-50 small mb-0"><i class="fas fa-check-circle text-success me-1"></i>Dual benefit: Growth + Rental Income</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function calcGrowth() {
    const amt = parseFloat(document.getElementById('invAmount').value);
    const yrs = parseFloat(document.getElementById('invYears').value);

    const re = amt * Math.pow(1.18, yrs);
    const fd = amt * Math.pow(1.06, yrs);
    const gold = amt * Math.pow(1.09, yrs);

    const maxVal = Math.max(re, fd, gold);

    document.getElementById('reValue').textContent = 'â‚¹' + Math.round(re).toLocaleString('en-IN');
    document.getElementById('fdValue').textContent = 'â‚¹' + Math.round(fd).toLocaleString('en-IN');
    document.getElementById('goldValue').textContent = 'â‚¹' + Math.round(gold).toLocaleString('en-IN');
    document.getElementById('growthResult').textContent = 'â‚¹' + Math.round(re).toLocaleString('en-IN');

    document.getElementById('reBar').style.width = (re / maxVal * 100) + '%';
    document.getElementById('fdBar').style.width = (fd / maxVal * 100) + '%';
    document.getElementById('goldBar').style.width = (gold / maxVal * 100) + '%';
}
calcGrowth();
</script>

<style>
@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(40, 167, 69, 0.3); }
    50% { box-shadow: 0 0 40px rgba(40, 167, 69, 0.6); }
}
</style>

<!-- Useful Free Tools -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Free Tools</span>
            <h2 class="fw-bold">Real Estate Tools â€” Bilkul Free!</h2>
            <p class="section-subtitle">Apna property calculate karein, compare karein aur smart decision lein</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('emi')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-calculator fa-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold">EMI Calculator</h5>
                        <p class="text-muted small mb-0">Home loan, car loan â€” kisi bhi loan ka monthly EMI nikaalein. Principal, interest aur total payment dekhein.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('investment')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-arrow-trend-up fa-2x text-success"></i>
                        </div>
                        <h5 class="fw-bold">Investment Calculator</h5>
                        <p class="text-muted small mb-0">Real Estate vs FD vs Gold â€” kaunsa investment better hai? 5, 10, 15 saal ka growth compare karein.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('stamp')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-file-contract fa-2x text-warning"></i>
                        </div>
                        <h5 class="fw-bold">Stamp Duty Calculator</h5>
                        <p class="text-muted small mb-0">Property price ke hisaab se stamp duty, registration fee aur total cost nikaalein. State-wise rates ke saath.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('converter')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-vector-square fa-2x text-info"></i>
                        </div>
                        <h5 class="fw-bold">Plot Size Converter</h5>
                        <p class="text-muted small mb-0">Square feet, square meter, acre, hectare, bigha, gaj â€” sabhi units mein plot size convert karein.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('eligibility')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-hand-holding-dollar fa-2x text-danger"></i>
                        </div>
                        <h5 class="fw-bold">Loan Eligibility Check</h5>
                        <p class="text-muted small mb-0">Aapki salary ke hisaab se kitna loan milega? SBI, HDFC, ICICI sabhi banks ke eligibility criteria ke saath.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 tool-card" onclick="openToolModal('valuation')">
                    <div class="card-body p-4 text-center">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-house-chimney fa-2x text-secondary"></i>
                        </div>
                        <h5 class="fw-bold">Property Valuation</h5>
                        <p class="text-muted small mb-0">AI-powered property valuation. Apni property ki current market price turant jaanein. Free report!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tool Modal -->
<div class="modal fade" id="toolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 p-4" id="toolModalHeader" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-calculator text-white fa-xl" id="toolModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-1" id="toolModalTitle">Tool</h5>
                        <p class="text-white-50 small mb-0" id="toolModalSubtitle">Calculate instantly</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="toolModalBody">
            </div>
        </div>
    </div>
</div>

<style>
.tool-card { transition: all 0.3s ease; cursor: pointer; }
.tool-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important; }
.tool-card:hover .rounded-circle { transform: scale(1.1); transition: transform 0.3s; }
.modal-content { animation: modalSlideUp 0.3s ease; }
@keyframes modalSlideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
function openToolModal(tool) {
    var header = document.getElementById('toolModalHeader');
    var icon = document.getElementById('toolModalIcon');
    var title = document.getElementById('toolModalTitle');
    var subtitle = document.getElementById('toolModalSubtitle');
    var body = document.getElementById('toolModalBody');

    var configs = {
        emi: {
            color: 'linear-gradient(135deg, #667eea, #764ba2)',
            icon: 'fa-calculator',
            title: 'EMI Calculator',
            subtitle: 'Home loan, car loan â€” monthly EMI instantly',
            html: getEMICalculator()
        },
        investment: {
            color: 'linear-gradient(135deg, #11998e, #38ef7d)',
            icon: 'fa-arrow-trend-up',
            title: 'Investment Calculator',
            subtitle: 'Real Estate vs FD vs Gold â€” compare growth',
            html: getInvestmentCalculator()
        },
        stamp: {
            color: 'linear-gradient(135deg, #f093fb, #f5576c)',
            icon: 'fa-file-contract',
            title: 'Stamp Duty Calculator',
            subtitle: 'Property price ke hisaab se total cost nikaalein',
            html: getStampDutyCalculator()
        },
        converter: {
            color: 'linear-gradient(135deg, #4facfe, #00f2fe)',
            icon: 'fa-vector-square',
            title: 'Plot Size Converter',
            subtitle: 'Sabhi units mein plot size convert karein',
            html: getPlotConverter()
        },
        eligibility: {
            color: 'linear-gradient(135deg, #fa709a, #fee140)',
            icon: 'fa-hand-holding-dollar',
            title: 'Loan Eligibility Check',
            subtitle: 'Aapki salary ke hisaab se kitna loan milega?',
            html: getLoanEligibility()
        },
        valuation: {
            color: 'linear-gradient(135deg, #a8edea, #fed6e3)',
            icon: 'fa-house-chimney',
            title: 'Property Valuation',
            subtitle: 'AI-powered property valuation. Free report!',
            html: getPropertyValuation()
        }
    };

    var cfg = configs[tool] || configs.emi;
    header.style.background = cfg.color;
    icon.className = 'fas ' + cfg.icon + ' text-white fa-xl';
    title.textContent = cfg.title;
    subtitle.textContent = cfg.subtitle;
    body.innerHTML = cfg.html;

    var modal = new bootstrap.Modal(document.getElementById('toolModal'));
    modal.show();
}

function getEMICalculator() {
    return '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Loan Amount: <span class="text-primary" id="mEmiAmt">â‚¹50,00,000</span></label>' +
            '<input type="range" class="form-range" min="100000" max="50000000" step="100000" value="5000000" oninput="mCalcEMI()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Interest Rate: <span class="text-primary" id="mEmiRate">8.5%</span></label>' +
            '<input type="range" class="form-range" min="5" max="20" step="0.1" value="8.5" oninput="mCalcEMI()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Tenure: <span class="text-primary" id="mEmiTenure">20 Years</span></label>' +
            '<input type="range" class="form-range" min="1" max="30" step="1" value="20" oninput="mCalcEMI()"></div></div>' +
        '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">Monthly EMI</p>' +
            '<p class="display-5 fw-bold mb-0 text-warning" id="mEmiResult">â‚¹42,426</p><hr class="border-secondary my-3">' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">Total Interest</span><span class="fw-bold" id="mEmiInterest">â‚¹51,82,240</span></div>' +
            '<div class="d-flex justify-content-between mt-2"><span class="text-white-50">Total Payment</span><span class="fw-bold" id="mEmiTotal">â‚¹1,01,82,240</span></div>' +
        '</div></div></div>';
}

function getInvestmentCalculator() {
    return '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Investment Amount</label>' +
            '<select class="form-select" id="mInvAmt" onchange="mCalcInv()">' +
                '<option value="500000">â‚¹5 Lakh</option><option value="1000000" selected>â‚¹10 Lakh</option>' +
                '<option value="2500000">â‚¹25 Lakh</option><option value="5000000">â‚¹50 Lakh</option><option value="10000000">â‚¹1 Crore</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Time Period</label>' +
            '<select class="form-select" id="mInvYrs" onchange="mCalcInv()">' +
                '<option value="5">5 Years</option><option value="10" selected>10 Years</option>' +
                '<option value="15">15 Years</option><option value="20">20 Years</option></select></div>' +
            '<div class="mt-3">' +
            '<div class="d-flex justify-content-between mb-1"><span>Real Estate <span class="text-success">(18%)</span></span><span class="fw-bold text-success" id="mInvRE">â‚¹52,33,855</span></div>' +
            '<div class="progress mb-2" style="height:6px"><div class="progress-bar bg-success" id="mInvREBar" style="width:100%"></div></div>' +
            '<div class="d-flex justify-content-between mb-1"><span>FD <span class="text-warning">(6%)</span></span><span class="fw-bold text-warning" id="mInvFD">â‚¹17,90,848</span></div>' +
            '<div class="progress mb-2" style="height:6px"><div class="progress-bar bg-warning" id="mInvFDBar" style="width:34%"></div></div>' +
            '<div class="d-flex justify-content-between"><span>Gold <span class="text-primary">(9%)</span></span><span class="fw-bold text-primary" id="mInvGold">â‚¹23,67,364</span></div>' +
            '<div class="progress" style="height:6px"><div class="progress-bar bg-primary" id="mInvGoldBar" style="width:45%"></div></div></div></div>' +
        '<div class="col-md-5 text-center d-flex flex-column justify-content-center">' +
            '<div class="bg-success bg-opacity-10 rounded-4 p-4"><i class="fas fa-trophy fa-3x text-success mb-3"></i>' +
            '<h4 class="fw-bold text-success">Real Estate Wins!</h4>' +
            '<p class="text-muted small mb-1"><i class="fas fa-check-circle text-success me-1"></i>Highest returns</p>' +
            '<p class="text-muted small mb-1"><i class="fas fa-check-circle text-success me-1"></i>Tangible asset</p>' +
            '<p class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i>Growth + Rental Income</p></div></div></div>';
}

function getStampDutyCalculator() {
    return '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Property Price</label>' +
            '<input type="number" class="form-control form-control-lg" id="mStampPrice" value="5000000" oninput="mCalcStamp()"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">State</label>' +
            '<select class="form-select" id="mStampState" onchange="mCalcStamp()">' +
                '<option value="up">Uttar Pradesh</option><option value="bihar">Bihar</option>' +
                '<option value="mp">Madhya Pradesh</option><option value="delhi">Delhi</option>' +
                '<option value="maharashtra">Maharashtra</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Property Type</label>' +
            '<select class="form-select" id="mStampType" onchange="mCalcStamp()">' +
                '<option value="male">Male (Male Name)</option><option value="female">Female (Female Name)</option>' +
                '<option value="joint">Joint (Male+Female)</option></select></div></div>' +
        '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">Property Price</span><span class="fw-bold" id="mStampBase">â‚¹50,00,000</span></div>' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">Stamp Duty (5%)</span><span class="fw-bold text-warning" id="mStampDuty">â‚¹2,50,000</span></div>' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">Registration (1%)</span><span class="fw-bold text-info" id="mStampReg">â‚¹50,000</span></div>' +
            '<hr class="border-secondary my-2">' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">Total Cost</span><span class="fw-bold text-success fs-5" id="mStampTotal">â‚¹53,00,000</span></div>' +
        '</div></div></div>';
}

function getPlotConverter() {
    var units = ['Square Feet','Square Meter','Acre','Hectare','Bigha (UP)','Gaj','Square Yard','Katha (UP)','Marla'];
    var unitOptions = '';
    for (var i = 0; i < units.length; i++) unitOptions += '<option value="' + i + '">' + units[i] + '</option>';
    return '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Value</label>' +
            '<input type="number" class="form-control form-control-lg" id="mConvVal" value="1000" oninput="mCalcConv()"></div>' +
            '<div class="row g-2 mb-3"><div class="col-6"><label class="form-label small">From</label>' +
            '<select class="form-select" id="mConvFrom" onchange="mCalcConv()">' + unitOptions + '</select></div>' +
            '<div class="col-6"><label class="form-label small">To</label>' +
            '<select class="form-select" id="mConvTo" onchange="mCalcConv()"><option value="0" selected>Square Feet</option><option value="1">Square Meter</option><option value="2">Acre</option><option value="3">Hectare</option><option value="4">Bigha (UP)</option><option value="5">Gaj</option><option value="6">Square Yard</option><option value="7">Katha (UP)</option><option value="8">Marla</option></select></div></div>' +
            '<div class="alert alert-success mb-0"><i class="fas fa-exchange-alt me-2"></i>Result: <strong id="mConvResult">0.0913 Acre</strong></div></div>' +
        '<div class="col-md-5"><div class="bg-light rounded-4 p-4 h-100">' +
            '<h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-info"></i>Quick Reference</h6>' +
            '<div class="small"><strong>1 Bigha (UP)</strong> = 27,000 Sq Ft<br><strong>1 Gaj</strong> = 9 Sq Ft<br><strong>1 Acre</strong> = 43,560 Sq Ft<br><strong>1 Hectare</strong> = 2.47 Acre<br><strong>1 Katha (UP)</strong> = 1,361 Sq Ft<br><strong>1 Marla</strong> = 272 Sq Ft</div>' +
        '</div></div></div>';
}

function getLoanEligibility() {
    return '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Monthly Income (Net Salary)</label>' +
            '<input type="number" class="form-control form-control-lg" id="mEligIncome" value="60000" oninput="mCalcElig()" step="1000"></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Existing Monthly EMI</label>' +
            '<input type="number" class="form-control" id="mEligEmi" value="0" oninput="mCalcElig()"></div>' +
            '<div class="row g-2 mb-3"><div class="col-6"><label class="form-label small">Interest Rate</label>' +
            '<select class="form-select" id="mEligRate" onchange="mCalcElig()">' +
                '<option value="8.5">8.5%</option><option value="9.0" selected>9.0%</option><option value="9.5">9.5%</option><option value="10.0">10%</option></select></div>' +
            '<div class="col-6"><label class="form-label small">Tenure (Years)</label>' +
            '<select class="form-select" id="mEligTenure" onchange="mCalcElig()">' +
                '<option value="5">5 Years</option><option value="10">10 Years</option><option value="15">15 Years</option><option value="20" selected>20 Years</option><option value="25">25 Years</option><option value="30">30 Years</option></select></div></div></div>' +
        '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">You Are Eligible For</p>' +
            '<p class="display-5 fw-bold mb-0 text-success" id="mEligResult">â‚¹27,23,250</p><hr class="border-secondary my-3">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">Max EMI</span><span class="fw-bold" id="mEligMaxEmi">â‚¹27,000</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">FOIR Ratio</span><span class="fw-bold" id="mEligFoir">45%</span></div>' +
        '</div></div></div>';
}

function getPropertyValuation() {
    return '<p class="text-center text-muted mb-3"><i class="fas fa-robot me-2"></i>AI-powered estimate based on current market data</p>' +
        '<div class="row g-4">' +
        '<div class="col-md-7">' +
            '<div class="mb-3"><label class="form-label fw-bold">Property Type</label>' +
            '<select class="form-select" id="mValType" onchange="mCalcVal()">' +
                '<option value="plot">Plot/Land</option><option value="house">House/Villa</option><option value="flat">Flat/Apartment</option><option value="commercial">Commercial</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Location</label>' +
            '<select class="form-select" id="mValLoc" onchange="mCalcVal()">' +
                '<option value="gorakhpur">Gorakhpur</option><option value="lucknow">Lucknow</option>' +
                '<option value="kushinagar">Kushinagar</option><option value="varanasi">Varanasi</option></select></div>' +
            '<div class="mb-3"><label class="form-label fw-bold">Area (Sq Ft)</label>' +
            '<input type="number" class="form-control form-control-lg" id="mValArea" value="1500" oninput="mCalcVal()" step="10"></div>' +
            '<div class="d-flex gap-2"><div class="flex-fill"><label class="form-label small">Bedrooms</label>' +
            '<select class="form-select" id="mValBhk" onchange="mCalcVal()"><option value="1">1 BHK</option><option value="2" selected>2 BHK</option><option value="3">3 BHK</option><option value="4">4 BHK</option></select></div>' +
            '<div class="flex-fill"><label class="form-label small">Furnishing</label>' +
            '<select class="form-select" id="mValFurn" onchange="mCalcVal()"><option value="0.9">Unfurnished</option><option value="1" selected>Semi-Furnished</option><option value="1.2">Fully Furnished</option></select></div></div></div>' +
        '<div class="col-md-5"><div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">' +
            '<p class="text-white-50 mb-1 small">Estimated Market Value</p>' +
            '<p class="display-5 fw-bold mb-0 text-warning" id="mValResult">â‚¹22,50,000</p><hr class="border-secondary my-3">' +
            '<div class="d-flex justify-content-between mb-2"><span class="text-white-50">Per Sq Ft</span><span class="fw-bold" id="mValPsf">â‚¹1,500</span></div>' +
            '<div class="d-flex justify-content-between"><span class="text-white-50">Confidence</span><span class="fw-bold text-success" id="mValConf">High</span></div>' +
        '</div></div></div>';
}

function mCalcEMI() {
    var inputs = document.querySelectorAll('#toolModalBody input[type="range"]');
    var P = parseFloat(inputs[0].value);
    var R = parseFloat(inputs[1].value) / 12 / 100;
    var N = parseFloat(inputs[2].value) * 12;
    document.getElementById('mEmiAmt').textContent = '\u20B9' + P.toLocaleString('en-IN');
    document.getElementById('mEmiRate').textContent = inputs[1].value + '%';
    document.getElementById('mEmiTenure').textContent = inputs[2].value + ' Years';
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
    var rates = { up: 0.05, bihar: 0.06, mp: 0.06, delhi: 0.05, maharashtra: 0.05 };
    var rate = rates[state] || 0.05;
    var stamp = price * rate;
    var reg = price * 0.01;
    document.getElementById('mStampBase').textContent = '\u20B9' + price.toLocaleString('en-IN');
    document.getElementById('mStampDuty').textContent = '\u20B9' + Math.round(stamp).toLocaleString('en-IN');
    document.getElementById('mStampReg').textContent = '\u20B9' + Math.round(reg).toLocaleString('en-IN');
    document.getElementById('mStampTotal').textContent = '\u20B9' + Math.round(price + stamp + reg).toLocaleString('en-IN');
}

var convFactors = [1, 0.092903, 2.2957e-5, 9.2903e-6, 3.7037e-5, 0.111111, 0.111111, 0.000734, 0.003673];
function mCalcConv() {
    var val = parseFloat(document.getElementById('mConvVal').value) || 0;
    var from = parseInt(document.getElementById('mConvFrom').value);
    var to = parseInt(document.getElementById('mConvTo').value);
    var sqft = val / convFactors[from];
    var result = sqft * convFactors[to];
    var unitNames = ['Sq Ft','Sq M','Acre','Ha','Bigha','Gaj','Sq Yd','Katha','Marla'];
    document.getElementById('mConvResult').textContent = result.toFixed(4) + ' ' + unitNames[to];
}

function mCalcElig() {
    var income = parseFloat(document.getElementById('mEligIncome').value) || 0;
    var existing = parseFloat(document.getElementById('mEligEmi').value) || 0;
    var rate = parseFloat(document.getElementById('mEligRate').value) / 12 / 100;
    var tenure = parseFloat(document.getElementById('mEligTenure').value) * 12;
    var maxEmi = (income * 0.5) - existing;
    var eligible = maxEmi * (Math.pow(1 + rate, tenure) - 1) / (rate * Math.pow(1 + rate, tenure));
    document.getElementById('mEligResult').textContent = '\u20B9' + Math.round(Math.max(0, eligible)).toLocaleString('en-IN');
    document.getElementById('mEligMaxEmi').textContent = '\u20B9' + Math.round(Math.max(0, maxEmi)).toLocaleString('en-IN');
    document.getElementById('mEligFoir').textContent = Math.round((existing + maxEmi) / income * 100) + '%';
}

var valRates = { gorakhpur: { plot: 1200, house: 1800, flat: 2200, commercial: 3000 }, lucknow: { plot: 2000, house: 3000, flat: 3500, commercial: 5000 }, kushinagar: { plot: 800, house: 1200, flat: 1500, commercial: 2000 }, varanasi: { plot: 1500, house: 2500, flat: 2800, commercial: 4000 } };
function mCalcVal() {
    var type = document.getElementById('mValType').value;
    var loc = document.getElementById('mValLoc').value;
    var area = parseFloat(document.getElementById('mValArea').value) || 0;
    var bhk = parseInt(document.getElementById('mValBhk').value);
    var furn = parseFloat(document.getElementById('mValFurn').value);
    var baseRate = (valRates[loc] || valRates.gorakhpur)[type] || 1500;
    var bhkFactor = 0.9 + bhk * 0.1;
    var value = area * baseRate * bhkFactor * furn;
    document.getElementById('mValResult').textContent = '\u20B9' + Math.round(value).toLocaleString('en-IN');
    document.getElementById('mValPsf').textContent = '\u20B9' + Math.round(baseRate * bhkFactor * furn).toLocaleString('en-IN');
    document.getElementById('mValConf').textContent = loc === 'kushinagar' ? 'Medium' : 'High';
}
</script>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Testimonials</span>
            <h2 class="fw-bold"><?= __('testimonials_title') ?></h2>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"Bought a plot in Suryoday Colony. Best decision! Process was smooth and team was very helpful."</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Rajesh Kumar</h6>
                            <small class="text-muted">Gorakhpur</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="card-text">"Excellent service! Got home loan easily through their assistance. Highly recommended."</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Priya Singh</h6>
                            <small class="text-muted">Kushinagar</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="text-warning mb-3">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="card-text">"Great investment opportunity! The team guided me at every step. Thank you APS Dream Home!"</p>
                    <div class="d-flex align-items-center">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Amit Verma</h6>
                            <small class="text-muted">Lucknow</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Join APS Dream Home -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label" style="background:rgba(239,68,68,0.08);color:#dc2626;">Career Opportunity</span>
            <h2 class="fw-bold">APS Dream Home Ke Saath Kyo Judein?</h2>
            <p class="section-subtitle">Real Estate mein ek nayi shuruaat â€” Salary + Commission + Insurance ke saath!</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(239,68,68,0.08);">
                            <i class="fas fa-sack-dollar fa-2x" style="color:#ef4444;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Fixed Monthly Salary</h5>
                        <p class="text-muted small">Real estate mein aam taur par sirf commission milta hai. Lekin APS Dream Home users ko <strong style="color:#ef4444;">fixed monthly salary</strong> bhi di jaati hai!</p>
                        <div class="bg-light rounded-3 p-3 mt-3">
                            <div class="d-flex justify-content-between mb-1"><span class="small">Starter: <strong>â‚¹5,000/mo</strong></span><span class="small text-muted">â‚¹15L target</span></div>
                            <div class="d-flex justify-content-between mb-1"><span class="small">Basic: <strong>â‚¹5,000/mo</strong></span><span class="small text-muted">â‚¹30L target</span></div>
                            <div class="d-flex justify-content-between mb-1"><span class="small">Professional: <strong>â‚¹8,000/mo</strong></span><span class="small text-muted">â‚¹50L target</span></div>
                            <div class="d-flex justify-content-between mb-1"><span class="small">Executive: <strong>â‚¹12,000/mo</strong></span><span class="small text-muted">â‚¹75L target</span></div>
                            <div class="d-flex justify-content-between"><span class="small">Elite: <strong>â‚¹20,000/mo</strong></span><span class="small text-muted">â‚¹1Cr target</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(16,185,129,0.08);">
                            <i class="fas fa-shield-heart fa-2x" style="color:#10b981;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Free Insurance Cover</h5>
                        <p class="text-muted small">Company aapke parivar ki suraksha ka khayal rakhti hai. Sabhi users ko <strong style="color:#10b981;">free health aur life insurance</strong> cover diya jaata hai.</p>
                        <div class="bg-light rounded-3 p-3 mt-3 text-start">
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Health Insurance: <strong>â‚¹5 Lakh</strong></span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Life Cover: <strong>â‚¹10 Lakh</strong></span>
                            <span class="small d-block"><i class="fas fa-check-circle text-success me-1"></i>Accidental Cover: <strong>â‚¹5 Lakh</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(245,158,11,0.08);">
                            <i class="fas fa-percentage fa-2x" style="color:#f59e0b;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Commission Plans</h5>
                        <p class="text-muted small">Salary ke alawa har sale par <strong style="color:#f59e0b;">multiple commission plans</strong>. Jaise team badhegi, waise commission rate bhi badhega!</p>
                        <div class="bg-light rounded-3 p-3 mt-3 text-start">
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Direct Business: <strong>10%</strong></span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Junior Business: <strong>5%</strong></span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Team Override: <strong>3%</strong></span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Leadership Bonus: <strong>2%</strong></span>
                            <span class="small d-block"><i class="fas fa-check-circle text-success me-1"></i>Director Override: <strong>1%</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(6,182,212,0.08);">
                            <i class="fas fa-graduation-cap fa-2x" style="color:#06b6d4;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Free Training & Certification</h5>
                        <p class="text-muted small">Real estate experience nahi hai? Koi baat nahi! Company aapko <strong style="color:#06b6d4;">free training</strong> degi â€” property knowledge, negotiation skills, aur sales techniques.</p>
                        <div class="bg-light rounded-3 p-3 mt-3 text-start">
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>7-Day Induction Program</span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Monthly Skill Workshops</span>
                            <span class="small d-block"><i class="fas fa-check-circle text-success me-1"></i>Certified Real Estate Professional</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(79,70,229,0.08);">
                            <i class="fas fa-users-between-lines fa-2x" style="color:#4f46e5;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">MLM Network Benefits</h5>
                        <p class="text-muted small">Naye users join karwaiye aur unki sales par <strong style="color:#4f46e5;">residual commission</strong> paayein. 10 rank structure â€” har rank ke saath badti hai earning!</p>
                        <div class="bg-light rounded-3 p-3 mt-3" style="max-height:160px;overflow-y:auto;">
                            <div class="small mb-1"><span class="text-warning me-1">ðŸ‘‘</span>Associate: <strong>5%</strong></div>
                            <div class="small mb-1"><span class="text-secondary me-1">ðŸ‘‘</span>Bronze: <strong>7%</strong></div>
                            <div class="small mb-1"><span class="text-secondary me-1">ðŸ‘‘</span>Silver: <strong>10%</strong></div>
                            <div class="small mb-1"><span class="text-warning me-1">ðŸ‘‘</span>Gold: <strong>12.5%</strong></div>
                            <div class="small mb-1"><span class="text-info me-1">ðŸ‘‘</span>Platinum: <strong>15%</strong></div>
                            <div class="small mb-1"><span class="text-primary me-1">ðŸ‘‘</span>Diamond: <strong>18%</strong></div>
                            <div class="small mb-1"><span class="text-success me-1">ðŸ‘‘</span>Executive: <strong>20%</strong></div>
                            <div class="small mb-1"><span class="text-success me-1">ðŸ‘‘</span>Sr. Executive: <strong>22%</strong></div>
                            <div class="small mb-1"><span class="text-danger me-1">ðŸ‘‘</span>Director: <strong>25%</strong></div>
                            <div class="small"><span class="text-danger me-1">ðŸ‘‘</span>Global Director: <strong>30%</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="career-card">
                    <div class="card-body p-4 text-center">
                        <div class="icon-circle" style="background:rgba(100,116,139,0.08);">
                            <i class="fas fa-handshake fa-2x" style="color:#64748b;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">More Reasons to Join</h5>
                        <p class="text-muted small">Aur bhi kai saare reasons jo APS Dream Home ko best banate hain:</p>
                        <div class="bg-light rounded-3 p-3 mt-3 text-start">
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Flexible working hours</span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>15+ years of market trust</span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Free marketing materials</span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>Office space & support team</span>
                            <span class="small d-block mb-1"><i class="fas fa-check-circle text-success me-1"></i>AI-powered lead generation</span>
                            <span class="small d-block"><i class="fas fa-check-circle text-success me-1"></i>Career growth to Branch Manager</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <p class="mb-3 fw-bold">Ready to start your real estate career with a fixed salary?</p>
            <a href="<?php echo BASE_URL; ?>/associate/register" class="btn btn-danger btn-lg px-5">
                <i class="fas fa-user-plus me-2"></i>Register as Associate
            </a>
            <a href="tel:+919277121112" class="btn btn-outline-dark btn-lg px-4 ms-2">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-white text-center cta-gradient">
    <div class="container">
        <span class="badge bg-white bg-opacity-15 text-white px-3 py-2 mb-3" style="background:rgba(255,255,255,0.12);">Get In Touch</span>
        <h2 class="fw-bold mb-3"><?= __('cta_title') ?></h2>
        <p class="mb-4" style="font-size:1.1rem;opacity:0.9;"><?= __('cta_subtitle') ?></p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg px-4">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success btn-lg px-4">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- WhatsApp float moved to base.php layout -->
