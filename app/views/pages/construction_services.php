<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<style>
:root { --primary: #1a237e; --secondary: #ff6f00; --accent: #00c853; }
.construction-hero {
    background: linear-gradient(135deg, #1a237e 0%, #283593 50%, #3949ab 100%);
    color: #fff; padding: 100px 0 80px; position: relative; overflow: hidden;
}
.construction-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.03)" d="M0 0h100v100H0z"/><rect fill="rgba(255,255,255,0.05)" x="10" y="10" width="80" height="80" rx="5"/></svg>') repeat; opacity: 0.5;
}
.service-card { border: none; border-radius: 16px; transition: all 0.3s ease; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; }
.service-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
.service-card .card-icon { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 1rem; }
.process-step { text-align: center; padding: 2rem 1rem; }
.process-step .step-num { width: 50px; height: 50px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700; margin: 0 auto 1rem; }
.project-card { border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08); transition: all 0.3s; }
.project-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.12); }
.project-card .card-img-top { height: 200px; object-fit: cover; }
.contact-form-section { background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); }
.flash-message { position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="flash-message alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php elseif (isset($_SESSION['flash_error'])): ?>
    <div class="flash-message alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<section class="construction-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2"><i class="fas fa-hard-hat me-1"></i> ISO Certified Construction Company</span>
                <h1 class="display-4 fw-bold mb-4">Building Your Dreams<br><span class="text-warning">With Quality & Trust</span></h1>
                <p class="lead mb-4 fs-5 opacity-90">APS Dream Home provides end-to-end construction and project contracting services. From residential homes to commercial complexes, we deliver quality, on time, and on budget.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-form" class="btn btn-warning btn-lg px-4"><i class="fas fa-building me-2"></i>Get a Free Quote</a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-list me-2"></i>Our Services</a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4"><h3 class="text-warning mb-0">50+</h3><small>Projects Completed</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">15+</h3><small>Years Experience</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">1000+</h3><small>Happy Clients</small></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="position-relative">
                    <img loading="lazy" src="https://img.freepik.com/free-photo/architectural-blueprints-construction-site_23-2148901413.jpg" alt="Construction" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 start-0 bg-white text-dark p-3 rounded-3 m-3 shadow">
                        <i class="fas fa-check-circle text-success me-1"></i> ISO 9001:2015 Certified
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<nav class="bg-white border-bottom shadow-sm" aria-label="breadcrumb">
    <div class="container py-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item active">Construction Services</li>
        </ol>
    </div>
</nav>

<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">WHAT WE OFFER</span>
            <h2 class="display-5 fw-bold">Construction & Contracting Services</h2>
            <p class="lead text-muted">Complete construction solutions for residential, commercial, and industrial projects</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-home"></i></div>
                    <h4>Residential Construction</h4>
                    <p class="text-muted">Custom homes, villas, apartments, and residential complexes built with quality materials and modern techniques.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> Custom Home Building</li>
                        <li><i class="fas fa-check text-success me-1"></i> Villa Construction</li>
                        <li><i class="fas fa-check text-success me-1"></i> Apartment Complexes</li>
                        <li><i class="fas fa-check text-success me-1"></i> Renovation & Extension</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-building"></i></div>
                    <h4>Commercial Construction</h4>
                    <p class="text-muted">Office buildings, retail spaces, showrooms, and commercial complexes designed for functionality.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> Office Buildings</li>
                        <li><i class="fas fa-check text-success me-1"></i> Retail & Showrooms</li>
                        <li><i class="fas fa-check text-success me-1"></i> Shopping Complexes</li>
                        <li><i class="fas fa-check text-success me-1"></i> Industrial Sheds</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-success bg-opacity-10 text-success"><i class="fas fa-drafting-compass"></i></div>
                    <h4>Architectural Design</h4>
                    <p class="text-muted">Professional architectural plans, 3D elevations, structural designs, and Vastu-compliant layouts.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> Architectural Plans</li>
                        <li><i class="fas fa-check text-success me-1"></i> 3D Elevations</li>
                        <li><i class="fas fa-check text-success me-1"></i> Structural Design</li>
                        <li><i class="fas fa-check text-success me-1"></i> Vastu Consultation</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-road"></i></div>
                    <h4>Infrastructure Development</h4>
                    <p class="text-muted">Road construction, drainage systems, sewage lines, water supply, and community infrastructure.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> Road Construction</li>
                        <li><i class="fas fa-check text-success me-1"></i> Drainage Systems</li>
                        <li><i class="fas fa-check text-success me-1"></i> Water Supply</li>
                        <li><i class="fas fa-check text-success me-1"></i> Community Facilities</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-info bg-opacity-10 text-info"><i class="fas fa-tools"></i></div>
                    <h4>Renovation & Repair</h4>
                    <p class="text-muted">Complete renovation services for homes, offices, and commercial spaces including structural repairs.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> Home Renovation</li>
                        <li><i class="fas fa-check text-success me-1"></i> Office Remodeling</li>
                        <li><i class="fas fa-check text-success me-1"></i> Structural Repairs</li>
                        <li><i class="fas fa-check text-success me-1"></i> Waterproofing</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="service-card card p-4">
                    <div class="card-icon bg-secondary bg-opacity-10 text-secondary"><i class="fas fa-handshake"></i></div>
                    <h4>Turnkey Projects</h4>
                    <p class="text-muted">End-to-end project management from concept to handover. We handle everything.</p>
                    <ul class="list-unstyled text-muted small">
                        <li><i class="fas fa-check text-success me-1"></i> End-to-End Management</li>
                        <li><i class="fas fa-check text-success me-1"></i> Material Procurement</li>
                        <li><i class="fas fa-check text-success me-1"></i> Labour Management</li>
                        <li><i class="fas fa-check text-success me-1"></i> Quality Assurance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">HOW IT WORKS</span>
            <h2 class="display-5 fw-bold">Our Construction Process</h2>
        </div>
        <div class="row">
            <div class="col-md-3"><div class="process-step"><div class="step-num">1</div><h5>Consultation</h5><p class="text-muted small">Free site visit and requirement discussion</p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">2</div><h5>Design & Estimate</h5><p class="text-muted small">Detailed plans with cost estimation</p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">3</div><h5>Construction</h5><p class="text-muted small">Quality execution with regular updates</p></div></div>
            <div class="col-md-3"><div class="process-step"><div class="step-num">4</div><h5>Handover</h5><p class="text-muted small">Final inspection and keys delivered</p></div></div>
        </div>
    </div>
</section>

<section id="projects" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">OUR WORK</span>
            <h2 class="display-5 fw-bold">Recent Projects</h2>
            <p class="lead text-muted">Take a look at some of our completed and ongoing construction projects</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $p): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="project-card card">
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?= htmlspecialchars($p['name'] ?? 'Project') ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height:200px"><i class="fas fa-building fa-4x text-primary opacity-50"></i></div>
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'warning' ?> mb-2"><?= $p['status'] ?? 'In Progress' ?></span>
                                <h5 class="fw-bold"><?= htmlspecialchars($p['name'] ?? 'Project') ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($p['location'] ?? '') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-hard-hat fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Projects Gallery Coming Soon</h4>
                    <p class="text-muted">We are currently updating our project portfolio. Contact us for information about our completed projects.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="contact-form" class="contact-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2">GET STARTED</span>
                    <h2 class="display-5 fw-bold">Request a Free Quote</h2>
                    <p class="lead text-muted">Tell us about your construction project and we'll get back to you within 24 hours</p>
                </div>
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <form action="<?= BASE_URL ?>/construction-services/inquiry" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label fw-medium">Your Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label><input type="tel" name="phone" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Email Address</label><input type="email" name="email" class="form-control form-control-lg"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Project Type</label>
                                    <select name="project_type" class="form-select form-select-lg">
                                        <option value="">Select...</option>
                                        <option value="residential">Residential House</option>
                                        <option value="commercial">Commercial Building</option>
                                        <option value="renovation">Renovation / Repair</option>
                                        <option value="infrastructure">Infrastructure</option>
                                        <option value="turnkey">Turnkey Project</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-medium">Budget Range (₹)</label><input type="number" name="budget" class="form-control form-control-lg" placeholder="Approximate budget"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Location</label><input type="text" name="location" class="form-control form-control-lg" placeholder="Project location"></div>
                                <div class="col-12"><label class="form-label fw-medium">Project Details</label><textarea name="message" rows="4" class="form-control" placeholder="Describe your project requirements..."></textarea></div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Submit Inquiry</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-4 g-3 text-center">
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-phone-alt text-primary fa-2x mb-2"></i><h6>Call Us</h6><p class="mb-0 text-muted"><?= $phoneDisplay ?></p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-envelope text-primary fa-2x mb-2"></i><h6>Email</h6><p class="mb-0 text-muted">info@apsdreamhome.com</p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-map-marker-alt text-primary fa-2x mb-2"></i><h6>Office</h6><p class="mb-0 text-muted">Gorakhpur, UP</p></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-4 bg-dark text-white text-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-md-start">
                <h4 class="mb-1">Ready to Build Your Dream Project?</h4>
                <p class="mb-0 opacity-75">Get a free consultation and detailed estimate with no obligation</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg px-4"><i class="fas fa-phone me-2"></i>Call Now</a>
            </div>
        </div>
    </div>
</section>
