<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0 60px;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-white">
                <h1 class="display-4 fw-bold mb-3">Find Your <span class="text-warning">Dream Home</span></h1>
                <p class="lead mb-4">Premium residential & commercial properties across India. Buy, Sell, Rent - All in one platform.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-warning btn-lg">View Projects</a>
                    <a href="<?php echo BASE_URL; ?>/list-property" class="btn btn-outline-light btn-lg">Post Property FREE</a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Properties</h5>
                    </div>
                    <div class="card-body p-4">
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
                                    <option value="under_5l">Under ₹5 Lakh</option>
                                    <option value="5_10l">₹5-10 Lakh</option>
                                    <option value="10_20l">₹10-20 Lakh</option>
                                    <option value="20_50l">₹20-50 Lakh</option>
                                    <option value="above_50l">Above ₹50 Lakh</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-search me-2"></i>Search
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
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=residential" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-home fa-2x text-primary mb-2"></i>
                        <h6>Residential</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=commercial" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-store fa-2x text-success mb-2"></i>
                        <h6>Commercial</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/properties?type=plot" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-vector-square fa-2x text-warning mb-2"></i>
                        <h6>Plots</h6>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <a href="<?php echo BASE_URL; ?>/list-property" class="text-decoration-none">
                    <div class="p-3 bg-white rounded shadow-sm">
                        <i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
                        <h6>Post FREE</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-primary mb-1"><?php echo $hero_stats['years_experience']; ?>+</div>
                <p class="text-muted mb-0">Years Experience</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-success mb-1"><?php echo $hero_stats['projects_completed']; ?>+</div>
                <p class="text-muted mb-0">Projects Completed</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-info mb-1"><?php echo $hero_stats['happy_customers']; ?>+</div>
                <p class="text-muted mb-0">Happy Customers</p>
            </div>
            <div class="col-6 col-md-3 mb-4">
                <div class="h2 text-warning mb-1"><?php echo $hero_stats['awards_won']; ?></div>
                <p class="text-muted mb-0">Awards Won</p>
            </div>
        </div>
    </div>
</section>

<!-- EMI Calculator Section -->
<section class="py-5" style="background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold text-white">Home Loan EMI Calculator</h2>
            <p class="lead text-white-50">Calculate your monthly EMI instantly — <strong>EMI Kitna Banega?</strong></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Loan Amount: <span id="loanAmtDisplay" class="text-primary">₹50,00,000</span></label>
                                    <input type="range" class="form-range" id="loanAmount" min="100000" max="50000000" step="100000" value="5000000" oninput="calcEMI()">
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>₹1 Lakh</span>
                                        <span>₹5 Crore</span>
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
                                <div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">
                                    <p class="text-white-50 mb-1 small text-uppercase tracking-wide">Your Monthly EMI</p>
                                    <p class="display-4 fw-bold mb-0" id="emiResult">₹42,426</p>
                                    <hr class="border-secondary my-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-white-50">Total Interest</span>
                                        <span class="fw-bold" id="totalInterest">₹51,82,240</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-white-50">Total Payment</span>
                                        <span class="fw-bold" id="totalPayment">₹1,01,82,240</span>
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

    document.getElementById('loanAmtDisplay').textContent = '₹' + P.toLocaleString('en-IN');
    document.getElementById('rateDisplay').textContent = document.getElementById('interestRate').value + '%';
    document.getElementById('tenureDisplay').textContent = document.getElementById('loanTenure').value + ' Years';

    if (R === 0) {
        document.getElementById('emiResult').textContent = '₹' + Math.round(P / N).toLocaleString('en-IN');
    } else {
        const emi = P * R * Math.pow(1 + R, N) / (Math.pow(1 + R, N) - 1);
        const totalPay = emi * N;
        const totalInt = totalPay - P;
        document.getElementById('emiResult').textContent = '₹' + Math.round(emi).toLocaleString('en-IN');
        document.getElementById('totalInterest').textContent = '₹' + Math.round(totalInt).toLocaleString('en-IN');
        document.getElementById('totalPayment').textContent = '₹' + Math.round(totalPay).toLocaleString('en-IN');
    }
}
calcEMI();
</script>

<!-- Our Projects -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Our Projects</h2>
            <p class="text-muted">Explore our premium projects across Uttar Pradesh</p>
        </div>
        <div class="row">
            <?php if (!empty($featured_properties)): ?>
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
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="position-relative" style="height: 180px;">
                            <img src="<?php echo BASE_URL . $imgPath; ?>" class="img-fluid" class="w-100 h-100" style="object-fit: cover;" alt="<?php echo htmlspecialchars($project['title']); ?>" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/placeholder/property.svg'">
                            <span class="badge bg-success position-absolute top-0 end-0 m-2"><?php echo $project['status']; ?></span>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($project['city']); ?></p>
                            <p class="h5 text-primary mb-3"><?php echo $project['price']; ?></p>
                            <a href="<?php echo BASE_URL; ?>/projects/<?php echo $slug; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL; ?>/company/projects" class="btn btn-primary btn-lg">View All Projects</a>
        </div>
    </div>
</section>

<!-- Services -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">Our Services</h2>
            <p class="text-muted">Complete real estate solutions under one roof</p>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-hand-holding-usd fa-2x text-success"></i>
                    </div>
                    <h5>Home Loan</h5>
                    <p class="text-muted">SBI, HDFC, ICICI - Best rates & easy processing</p>
                    <a href="<?php echo BASE_URL; ?>/financial-services" class="btn btn-outline-success btn-sm">Learn More</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-gavel fa-2x text-primary"></i>
                    </div>
                    <h5>Legal Services</h5>
                    <p class="text-muted">Registry, Mutation, Agreement - Complete documentation</p>
                    <a href="<?php echo BASE_URL; ?>/legal-services" class="btn btn-outline-primary btn-sm">Learn More</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm text-center p-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-couch fa-2x text-warning"></i>
                    </div>
                    <h5>Interior Design</h5>
                    <p class="text-muted">Modular kitchen, wardrobe, complete furnishing</p>
                    <a href="<?php echo BASE_URL; ?>/interior-design" class="btn btn-outline-warning btn-sm">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-6 fw-bold mb-4">Why Choose APS Dream Home?</h2>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">15+ Years Experience</h6>
                        <p class="text-muted mb-0 small">Trusted name in UP real estate since 2010</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">RERA Verified Projects</h6>
                        <p class="text-muted mb-0 small">All properties legally approved</p>
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">Transparent Dealings</h6>
                        <p class="text-muted mb-0 small">No hidden charges, clear documentation</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-1">24/7 Support</h6>
                        <p class="text-muted mb-0 small">Always here to help you</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 text-center">
                        <i class="fas fa-headset fa-4x text-primary mb-4"></i>
                        <h4>Need Help?</h4>
                        <p class="text-muted mb-4">Our team is ready to assist you</p>
                        <div class="d-grid gap-3">
                            <a href="tel:+919277121112" class="btn btn-success btn-lg">
                                <i class="fas fa-phone me-2"></i>Call: +91 92771 21112
                            </a>
                            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-outline-success btn-lg">
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
            <span class="badge bg-warning text-dark px-3 py-2 mb-3">Why Real Estate?</span>
            <h2 class="display-6 fw-bold">Land, Plot ya Property Mein Paisa Kyo Lagayein?</h2>
            <p class="text-muted lead">Compare real estate with other investment options — results speak louder than words!</p>
        </div>

        <!-- Investment Comparison Chart -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-top: 4px solid #28a745 !important;">
                    <div class="mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-vector-square fa-2x text-success"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-success">Real Estate</h5>
                    <div class="h3 fw-bold text-success">15-25%</div>
                    <p class="text-muted small">Average Annual Returns</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 85%"></div>
                    </div>
                    <span class="badge bg-success">⭐ Best Investment</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-top: 4px solid #ffc107 !important;">
                    <div class="mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-coins fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-warning">Fixed Deposit</h5>
                    <div class="h3 fw-bold">5-7%</div>
                    <p class="text-muted small">Average Annual Returns</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 25%"></div>
                    </div>
                    <span class="badge bg-secondary">Low Returns</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-top: 4px solid #dc3545 !important;">
                    <div class="mb-3">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-chart-line fa-2x text-danger"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-danger">Stock Market</h5>
                    <div class="h3 fw-bold">10-14%</div>
                    <p class="text-muted small">High Risk / Volatile</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-danger" style="width: 50%"></div>
                    </div>
                    <span class="badge bg-warning text-dark">Moderate</span>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4" style="border-top: 4px solid #0d6efd !important;">
                    <div class="mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-ring fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-primary">Gold</h5>
                    <div class="h3 fw-bold">8-10%</div>
                    <p class="text-muted small">Safe but No Passive Income</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 35%"></div>
                    </div>
                    <span class="badge bg-info text-dark">Safe Haven</span>
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
                        <div class="d-flex align-items-start p-3 bg-light rounded-3 h-100">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-shield-halved fa-2x text-success"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Capital Appreciation</h6>
                                <p class="small text-muted mb-0">Land prices double every 5-7 years in developing areas. Plot value grows faster than any FD or gold.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 bg-light rounded-3 h-100">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-hand-holding-dollar fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Passive Income</h6>
                                <p class="small text-muted mb-0">Rent out property for monthly income. Unlike gold or FD, real estate gives dual benefits — growth + income.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 bg-light rounded-3 h-100">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-building-columns fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Loan Against Property</h6>
                                <p class="small text-muted mb-0">Use your plot as collateral for business or personal loans at lower interest rates.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start p-3 bg-light rounded-3 h-100">
                            <div class="flex-shrink-0 me-3">
                                <i class="fas fa-flag-checkered fa-2x text-danger"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Inflation Hedge</h6>
                                <p class="small text-muted mb-0">Real estate beats inflation by 5-8% annually. Your money in land grows while its value in bank shrinks.</p>
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
                                    <option value="500000">₹5 Lakh</option>
                                    <option value="1000000" selected>₹10 Lakh</option>
                                    <option value="2500000">₹25 Lakh</option>
                                    <option value="5000000">₹50 Lakh</option>
                                    <option value="10000000">₹1 Crore</option>
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
                                <div class="fw-bold h4 mb-0 pt-1" id="growthResult">₹40,45,558</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">Real Estate (18% CAGR)</span>
                                <span class="small fw-bold text-success" id="reValue">₹52,33,855</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-success" id="reBar" style="width: 100%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">FD (6% CAGR)</span>
                                <span class="small fw-bold text-warning" id="fdValue">₹17,90,848</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" id="fdBar" style="width: 34%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-white-50">Gold (9% CAGR)</span>
                                <span class="small fw-bold text-primary" id="goldValue">₹23,67,364</span>
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
            <span class="badge bg-primary px-3 py-2 mb-3">Free Tools</span>
            <h2 class="display-6 fw-bold">Real Estate Tools — Bilkul Free!</h2>
            <p class="text-muted lead">Apna property calculate karein, compare karein aur smart decision lein</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/calc" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-calculator fa-2x text-primary"></i>
                            </div>
                            <h5 class="fw-bold">EMI Calculator</h5>
                            <p class="text-muted small mb-0">Home loan, car loan — kisi bhi loan ka monthly EMI nikaalein. Principal, interest aur total payment dekhein.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/calc" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-arrow-trend-up fa-2x text-success"></i>
                            </div>
                            <h5 class="fw-bold">Investment Calculator</h5>
                            <p class="text-muted small mb-0">Real Estate vs FD vs Gold — kaunsa investment better hai? 5, 10, 15 saal ka growth compare karein.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/stamp-duty-calculator" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-file-contract fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold">Stamp Duty Calculator</h5>
                            <p class="text-muted small mb-0">Property price ke hisaab se stamp duty, registration fee aur total cost nikaalein. State-wise rates ke saath.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/plot-size-converter" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-vector-square fa-2x text-info"></i>
                            </div>
                            <h5 class="fw-bold">Plot Size Converter</h5>
                            <p class="text-muted small mb-0">Square feet, square meter, acre, hectare, bigha, gaj — sabhi units mein plot size convert karein.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/home-loan-eligibility" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-hand-holding-dollar fa-2x text-danger"></i>
                            </div>
                            <h5 class="fw-bold">Loan Eligibility Check</h5>
                            <p class="text-muted small mb-0">Aapki salary ke hisaab se kitna loan milega? SBI, HDFC, ICICI sabhi banks ke eligibility criteria ke saath.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="<?php echo BASE_URL; ?>/property-valuation" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 tool-card">
                        <div class="card-body p-4 text-center">
                            <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                                <i class="fas fa-house-chimney fa-2x text-secondary"></i>
                            </div>
                            <h5 class="fw-bold">Property Valuation</h5>
                            <p class="text-muted small mb-0">AI-powered property valuation. Apni property ki current market price turant jaanein. Free report!</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
.tool-card { transition: all 0.3s ease; cursor: pointer; }
.tool-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important; }
.tool-card:hover .rounded-circle { transform: scale(1.1); transition: transform 0.3s; }
</style>

<!-- Testimonials -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold">What Customers Say</h2>
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
            <span class="badge bg-danger px-3 py-2 mb-3">Career Opportunity</span>
            <h2 class="display-6 fw-bold">APS Dream Home Ke Saath Kyo Judein?</h2>
            <p class="text-muted lead">Real Estate mein ek nayi shuruaat — Salary + Commission + Insurance ke saath!</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-sack-dollar fa-2x text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Fixed Monthly Salary</h5>
                        <p class="text-muted small">Real estate mein aam taur par sirf commission milta hai. Lekin APS Dream home associates aur agents ko <strong class="text-danger">fixed monthly salary</strong> bhi di jaati hai! Target complete karke salary guarantee paayein.</p>
                        <div class="bg-light rounded p-3 mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Starter: <strong>₹5,000/mo</strong> <span class="text-muted">(₹15L target, 6mo)</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Basic: <strong>₹5,000/mo</strong> <span class="text-muted">(₹30L target, 12mo)</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Professional: <strong>₹8,000/mo</strong> <span class="text-muted">(₹50L target, 12mo)</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small">Executive: <strong>₹12,000/mo</strong> <span class="text-muted">(₹75L target, 12mo)</span></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small">Elite: <strong>₹20,000/mo</strong> <span class="text-muted">(₹1Cr target, 12mo)</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-heart fa-2x text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Free Insurance Cover</h5>
                        <p class="text-muted small">Company aapke parivar ki suraksha ka khayal rakhti hai. Sabhi associates aur employees ko <strong class="text-success">free health aur life insurance</strong> cover diya jaata hai.</p>
                        <div class="bg-light rounded p-3 mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Health Insurance: <strong>₹5 Lakh</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Life Cover: <strong>₹10 Lakh</strong></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Accidental Cover: <strong>₹5 Lakh</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-percentage fa-2x text-warning"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Commission Plans</h5>
                        <p class="text-muted small">Salary ke alawa har sale par <strong class="text-warning">multiple commission plans</strong>. Jaise team badhegi, waise commission rate bhi badhega!</p>
                        <div class="bg-light rounded p-3 mt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Direct Business: <strong>10%</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Junior Business: <strong>5%</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Team Override: <strong>3%</strong></span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Leadership Bonus: <strong>2%</strong></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Director Override: <strong>1%</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-graduation-cap fa-2x text-info"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Free Training & Certification</h5>
                        <p class="text-muted small">Real estate experience nahi hai? Koi baat nahi! Company aapko <strong class="text-info">free training</strong> degi — property knowledge, negotiation skills, aur sales techniques.</p>
                        <div class="bg-light rounded p-3 mt-3">
                            <span class="small"><i class="fas fa-check-circle text-success me-1"></i>7-Day Induction Program</span><br>
                            <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Monthly Skill Workshops</span><br>
                            <span class="small"><i class="fas fa-check-circle text-success me-1"></i>Certified Real Estate Professional</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-users-between-lines fa-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-2">MLM Network Benefits</h5>
                        <p class="text-muted small">Naye associates join karwaiye aur unki sales par <strong class="text-primary">residual commission</strong> paayein. 10 rank structure — har rank ke saath badti hai earning!</p>
                        <div class="bg-light rounded p-3 mt-3" style="max-height:160px;overflow-y:auto;">
                            <div class="small mb-1"><i class="fas fa-crown text-warning me-1"></i>Associate: <strong>5%</strong> (0 team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-secondary me-1"></i>Bronze: <strong>7%</strong> (5+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-secondary me-1"></i>Silver: <strong>10%</strong> (10+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-warning me-1"></i>Gold: <strong>12.5%</strong> (20+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-info me-1"></i>Platinum: <strong>15%</strong> (35+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-primary me-1"></i>Diamond: <strong>18%</strong> (50+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-success me-1"></i>Executive: <strong>20%</strong> (75+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-success me-1"></i>Sr. Executive: <strong>22%</strong> (100+ team)</div>
                            <div class="small mb-1"><i class="fas fa-crown text-danger me-1"></i>Director: <strong>25%</strong> (150+ team)</div>
                            <div class="small"><i class="fas fa-crown text-danger me-1"></i>Global Director: <strong>30%</strong> (250+ team)</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 text-center">
                        <div class="bg-secondary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-handshake fa-2x text-secondary"></i>
                        </div>
                        <h5 class="fw-bold mb-2">More Reasons to Join</h5>
                        <p class="text-muted small">Aur bhi kai saare reasons jo APS Dream Home ko best banate hain:</p>
                        <div class="bg-light rounded p-3 mt-3 text-start">
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
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h2 class="display-6 fw-bold mb-3">Ready to Find Your Dream Home?</h2>
        <p class="lead mb-4">Contact us today and let us help you find the perfect property</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="tel:+919277121112" class="btn btn-warning btn-lg">
                <i class="fas fa-phone me-2"></i>Call Now
            </a>
            <a href="https://wa.me/919277121112" target="_blank" class="btn btn-success btn-lg">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp
            </a>
        </div>
    </div>
</section>

<!-- WhatsApp float moved to base.php layout -->
