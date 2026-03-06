<!-- Hero Section -->
<section class="legal-hero text-center" style="background-image: url('<?= get_asset_url('assets/images/hero-1.jpg') ?>');">
    <div class="container">
        <h1 class="display-4 fw-bold">Legal Services</h1>
        <p class="lead mb-0">Expert legal guidance for all your property transactions</p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php if (isset($breadcrumbs)): ?>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (empty($crumb['url']) || $crumb === end($breadcrumbs)): ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($crumb['title']) ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= htmlspecialchars($crumb['title']) ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Legal Services</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Legal Services Content -->

<!-- Intro Section -->
<section class="section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="section-title mb-3">Professional Legal Assistance for Real Estate</h2>
                <p class="lead text-muted mb-4">Secure your property transactions with our expert legal verification and documentation services.</p>
                <p>At APS Dream Homes, we understand that buying a property is one of the biggest financial decisions you'll make. Our dedicated legal team ensures that your investment is safe, secure, and legally sound. From title verification to registration, we handle all legal aspects so you can have peace of mind.</p>
                <div class="mt-4">
                    <a href="#consultation" class="btn btn-primary btn-lg">Get Free Consultation</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?= get_asset_url('assets/images/work.jpg') ?>" alt="Legal Services" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Services Grid -->
<section class="section bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Our Legal Services</h2>
            <p class="text-muted">Comprehensive legal solutions for all your property needs</p>
        </div>

        <div class="row">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $service): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm service-card">
                            <div class="card-body p-4 text-center">
                                <div class="icon-box mb-3 text-primary">
                                    <i class="<?= htmlspecialchars($service['icon_class'] ?? 'fas fa-gavel') ?> fa-3x"></i>
                                </div>
                                <h4 class="card-title mb-3"><?= htmlspecialchars($service['title']) ?></h4>
                                <p class="card-text text-muted"><?= htmlspecialchars($service['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No services currently listed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Team Section -->
<?php if (!empty($lawyers)): ?>
    <section class="section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Meet Our Legal Experts</h2>
                <p class="text-muted">Experienced lawyers and legal advisors at your service</p>
            </div>

            <div class="row justify-content-center">
                <?php foreach ($lawyers as $lawyer): ?>
                    <?php
                    $lawyerImage = !empty($lawyer['image']) ? 'uploads/team/' . $lawyer['image'] : '';
                    $lawyerImageUrl = !empty($lawyerImage) ? get_asset_url($lawyerImage) : 'https://via.placeholder.com/300x300/667eea/ffffff?text=' . substr($lawyer['name'], 0, 2);
                    ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 border-0 shadow-sm team-card">
                            <img src="<?= htmlspecialchars($lawyerImageUrl) ?>" class="card-img-top" alt="<?= htmlspecialchars($lawyer['name']) ?>"
                                onerror="this.src='https://via.placeholder.com/300x300/667eea/ffffff?text=<?= substr($lawyer['name'], 0, 2) ?>'">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($lawyer['name']) ?></h5>
                                <p class="text-primary mb-2"><?= htmlspecialchars($lawyer['designation']) ?></p>
                                <p class="card-text small text-muted"><?= htmlspecialchars($lawyer['specialization']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- FAQ Section -->
<?php if (!empty($faqs)): ?>
    <section class="section bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="text-muted">Common queries about property laws and documentation</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="legalFaq">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="card border-0 mb-2 shadow-sm">
                                <div class="card-header bg-white border-0" id="heading<?= $index ?>">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link btn-block text-start text-dark fw-bold collapsed w-100 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($faq['question']) ?></span>
                                                <i class="fas fa-chevron-down small"></i>
                                            </div>
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#legalFaq">
                                    <div class="card-body text-muted">
                                        <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section -->
<section class="section py-5 bg-primary text-white text-center" id="consultation">
    <div class="container">
        <h2 class="mb-4">Need Legal Advice?</h2>
        <p class="lead mb-4">Book a consultation with our legal experts today.</p>
        <a href="<?= BASE_URL ?>/contact" class="btn btn-light btn-lg">Contact Us Now</a>
    </div>
</section>

<!-- End Legal Services Content -->