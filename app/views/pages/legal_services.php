<?php require_once 'app/views/layouts/header.php'; ?>

<!-- Page Header -->
<div class="page-header" style="background-image: url('<?= BASE_URL ?>/assets/img/legal-bg.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Legal Services</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Legal Services</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

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
                <img src="<?= BASE_URL ?>/assets/img/legal-services.jpg" alt="Legal Services" class="img-fluid rounded shadow-lg">
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
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm team-card">
                        <img src="<?= BASE_URL ?>/uploads/team/<?= htmlspecialchars($lawyer['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($lawyer['name']) ?>">
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
                                    <button class="btn btn-link btn-block text-left text-dark font-weight-bold collapsed" type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                                        <?= htmlspecialchars($faq['question']) ?>
                                    </button>
                                </h5>
                            </div>

                            <div id="collapse<?= $index ?>" class="collapse" aria-labelledby="heading<?= $index ?>" data-parent="#legalFaq">
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

<?php require_once 'app/views/layouts/footer.php'; ?>
