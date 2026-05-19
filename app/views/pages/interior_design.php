<style>
:root { --primary: #6a1b9a; --secondary: #ff6f00; --accent: #00c853; }
.interior-hero {
    background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 50%, #8e24aa 100%);
    color: #fff; padding: 100px 0 80px; position: relative; overflow: hidden;
}
.interior-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="rgba(255,255,255,0.03)" cx="50" cy="50" r="40"/><circle fill="rgba(255,255,255,0.03)" cx="150" cy="150" r="60"/><circle fill="rgba(255,255,255,0.02)" cx="180" cy="30" r="30"/></svg>') repeat;
}
.service-card { border: none; border-radius: 16px; transition: all 0.3s ease; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; padding: 2rem; }
.service-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
.service-card .icon-wrap { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 1rem; }
.portfolio-item { position: relative; border-radius: 12px; overflow: hidden; height: 250px; }
.portfolio-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.portfolio-item:hover img { transform: scale(1.1); }
.portfolio-item .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 1.5rem; color: #fff; }
.team-card { text-align: center; padding: 2rem; border-radius: 16px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
.team-card img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; }
.testimonial-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 15px rgba(0,0,0,0.06); height: 100%; }
.faq-accordion .accordion-button:not(.collapsed) { background: #f3e5f5; color: #4a148c; }
.lead-form-section { background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); }
.flash-message { position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.tool-card { text-align: center; padding: 1.5rem; border: 2px dashed #e0e0e0; border-radius: 12px; transition: all 0.3s; cursor: pointer; }
.tool-card:hover { border-color: #6a1b9a; background: #f3e5f5; }
</style>

<section class="interior-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2"><i class="fas fa-palette me-1"></i> Premium Interior Design</span>
                <h1 class="display-4 fw-bold mb-4">Transform Your Space<br><span class="text-warning">Into a Masterpiece</span></h1>
                <p class="lead mb-4 fs-5 opacity-90">Professional interior design services in Gorakhpur, Lucknow, Varanasi & Kushinagar. We turn your dream home into reality with innovative designs and quality execution.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-form" class="btn btn-warning btn-lg px-4"><i class="fas fa-pen-fancy me-2"></i>Free Consultation</a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-list me-2"></i>Our Services</a>
                    <a href="#tools" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-calculator me-2"></i>Free Tools</a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4"><h3 class="text-warning mb-0">200+</h3><small>Projects Done</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">10+</h3><small>Designers</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">98%</h3><small>Satisfaction</small></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="position-relative">
                    <img src="https://img.freepik.com/free-photo/living-room-interior-design_23-2148892625.jpg" alt="Interior Design" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 end-0 bg-white text-dark p-3 rounded-3 m-3 shadow">
                        <i class="fas fa-star text-warning me-1"></i> Award-Winning Designs
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
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services</a></li>
            <li class="breadcrumb-item active">Interior Design</li>
        </ol>
    </div>
</nav>

<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">WHAT WE OFFER</span>
            <h2 class="display-5 fw-bold">Our Interior Design Services</h2>
            <p class="lead text-muted">Comprehensive interior design solutions for every space and budget</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($services) && is_array($services)): ?>
                <?php foreach ($services as $svc): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="icon-wrap bg-primary bg-opacity-10 text-primary">
                                <i class="<?= htmlspecialchars($svc['icon'] ?? 'fas fa-palette') ?>"></i>
                            </div>
                            <h4 class="fw-bold mb-3"><?= htmlspecialchars($svc['title'] ?? 'Design Service') ?></h4>
                            <p class="text-muted mb-4"><?= htmlspecialchars($svc['description'] ?? '') ?></p>
                            <?php if (!empty($svc['features'])): ?>
                                <?php $features = is_string($svc['features']) ? json_decode($svc['features'], true) : $svc['features']; ?>
                                <?php if (is_array($features)): ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($features as $f): ?>
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-2"></i><?= htmlspecialchars(is_string($f) ? $f : ($f['name'] ?? $f)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="#contact-form" class="btn btn-outline-primary mt-2">Enquire Now <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-palette fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Contact us for interior design services</h4>
                    <p class="text-muted mb-4">We offer residential, commercial, and modular interior design solutions</p>
                    <a href="#contact-form" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="tools" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success mb-2 px-3 py-2">FREE TOOLS</span>
            <h2 class="display-5 fw-bold">Interior Design Tools</h2>
            <p class="lead text-muted">Use our free tools to plan and estimate your interior design project</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4"><a href="<?= BASE_URL ?>/stamp-duty-calculator" class="text-decoration-none"><div class="tool-card"><i class="fas fa-calculator fa-3x text-primary mb-3"></i><h5>Cost Estimator</h5><p class="text-muted small mb-0">Estimate interior design costs per sq ft</p></div></a></div>
            <div class="col-md-4"><a href="<?= BASE_URL ?>/plot-size-converter" class="text-decoration-none"><div class="tool-card"><i class="fas fa-ruler-combined fa-3x text-success mb-3"></i><h5>Room Size Planner</h5><p class="text-muted small mb-0">Convert and plan room dimensions</p></div></a></div>
            <div class="col-md-4"><a href="<?= BASE_URL ?>/home-loan-eligibility" class="text-decoration-none"><div class="tool-card"><i class="fas fa-home fa-3x text-warning mb-3"></i><h5>Budget Planner</h5><p class="text-muted small mb-0">Plan your interior design budget</p></div></a></div>
        </div>
    </div>
</section>

<?php if (!empty($portfolio)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">OUR WORK</span>
            <h2 class="display-5 fw-bold">Portfolio</h2>
            <p class="lead text-muted">Browse our recent interior design projects</p>
        </div>
        <div class="row g-3">
            <?php foreach (array_slice($portfolio, 0, 6) as $item): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="portfolio-item">
                        <img src="<?= htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/400x300?text=Interior+Design') ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Portfolio') ?>">
                        <div class="overlay">
                            <h5 class="mb-1"><?= htmlspecialchars($item['title'] ?? 'Project') ?></h5>
                            <small><?= htmlspecialchars($item['category'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">TESTIMONIALS</span>
            <h2 class="display-5 fw-bold">What Our Clients Say</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $t): ?>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-quote-left fa-2x text-primary opacity-25 mb-2"></i>
                        <p class="text-muted"><?= htmlspecialchars($t['content'] ?? $t['message'] ?? '') ?></p>
                        <div class="d-flex align-items-center mt-3">
                            <div><strong><?= htmlspecialchars($t['name'] ?? $t['client_name'] ?? 'Client') ?></strong><br><small class="text-muted"><?= htmlspecialchars($t['location'] ?? '') ?></small></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="contact-form" class="lead-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2">GET STARTED</span>
                    <h2 class="display-5 fw-bold">Book Your Free Consultation</h2>
                    <p class="lead text-muted">Get a free design consultation and estimate. No obligation, just expert advice.</p>
                </div>
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <form action="<?= BASE_URL ?>/service-interest" method="POST">
                            <input type="hidden" name="service_type" value="interior">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label fw-medium">Your Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label><input type="tel" name="phone" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Email Address</label><input type="email" name="email" class="form-control form-control-lg"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Property Type</label>
                                    <select name="property_type" class="form-select form-select-lg">
                                        <option value="">Select...</option>
                                        <option value="apartment">Apartment/Flat</option>
                                        <option value="house">Independent House</option>
                                        <option value="villa">Villa</option>
                                        <option value="office">Office/Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-medium">Approx. Area (sq ft)</label><input type="number" name="area" class="form-control form-control-lg" placeholder="e.g. 1200"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Budget Range (₹)</label>
                                    <select name="budget" class="form-select form-select-lg">
                                        <option value="">Select range</option>
                                        <option value="50000">Under ₹50,000</option>
                                        <option value="100000">₹50,000 - ₹1,00,000</option>
                                        <option value="200000">₹1,00,000 - ₹2,00,000</option>
                                        <option value="500000">₹2,00,000 - ₹5,00,000</option>
                                        <option value="1000000">₹5,00,000+</option>
                                    </select>
                                </div>
                                <div class="col-12"><label class="form-label fw-medium">Your Requirements</label><textarea name="message" rows="3" class="form-control" placeholder="Tell us about your interior design needs..."></textarea></div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Get Free Consultation</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-4 g-3 text-center">
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-phone-alt text-primary fa-2x mb-2"></i><h6>Call Us</h6><p class="mb-0 text-muted">+91 92771 21112</p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fab fa-whatsapp text-success fa-2x mb-2"></i><h6>WhatsApp</h6><p class="mb-0 text-muted">+91 92771 21112</p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-map-marker-alt text-primary fa-2x mb-2"></i><h6>Visit Us</h6><p class="mb-0 text-muted">Gorakhpur, UP</p></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($faqs)): ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2">FAQ</span>
                    <h2 class="display-5 fw-bold">Frequently Asked Questions</h2>
                </div>
                <div class="accordion faq-accordion" id="interiorFaq">
                    <?php foreach ($faqs as $idx => $faq): ?>
                        <div class="accordion-item border-0 mb-2 shadow-sm rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $idx ?>">
                                    <?= htmlspecialchars($faq['question'] ?? '') ?>
                                </button>
                            </h2>
                            <div id="faq<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#interiorFaq">
                                <div class="accordion-body text-muted"><?= htmlspecialchars($faq['answer'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-4 bg-dark text-white text-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-md-start">
                <h4 class="mb-1">Ready to Transform Your Space?</h4>
                <p class="mb-0 opacity-75">Get a free consultation and detailed estimate within 24 hours</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="tel:+919277121112" class="btn btn-warning btn-lg px-4"><i class="fas fa-phone me-2"></i>Call Now</a>
            </div>
        </div>
    </div>
</section>
