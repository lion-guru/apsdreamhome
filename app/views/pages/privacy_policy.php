<?php
// app/views/pages/privacy_policy.php
?>

<!-- Hero Section -->
<section class="privacy-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Privacy Policy</h1>
        <p class="lead mb-0">How we collect, use, and protect your data</p>
    </div>
</section>

<!-- Breadcrumb -->
<div class="bg-light py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <?php foreach ($breadcrumbs as $crumb): ?>
                    <?php if (isset($crumb['url'])): ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="section-padding py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden" data-aos="fade-up">
                    <div class="card-body p-4 p-md-5">
                        <div class="privacy-content">
                            <h4 class="fw-bold mb-3 text-primary">1. Information We Collect</h4>
                            <p class="text-muted mb-4">We collect information that you provide directly to us, such as when you create an account, subscribe to our newsletter, request customer support, or otherwise communicate with us. This may include your name, email address, phone number, and any other information you choose to provide.</p>

                            <h4 class="fw-bold mb-3 text-primary">2. How We Use Your Information</h4>
                            <p class="text-muted mb-4">We use the information we collect to provide, maintain, and improve our services, including to process transactions, send you technical notices and support messages, and to communicate with you about products, services, offers, and events offered by APS Dream Home.</p>

                            <h4 class="fw-bold mb-3 text-primary">3. Information Sharing</h4>
                            <p class="text-muted mb-4">We do not share your personal information with third parties except as described in this privacy policy. We may share your information with third-party vendors, consultants, and other service providers who need access to such information to carry out work on our behalf.</p>

                            <h4 class="fw-bold mb-3 text-primary">4. Data Security</h4>
                            <p class="text-muted mb-4">We take reasonable measures to help protect information about you from loss, theft, misuse, and unauthorized access, disclosure, alteration, and destruction.</p>

                            <h4 class="fw-bold mb-3 text-primary">5. Cookies and Tracking Technologies</h4>
                            <p class="text-muted mb-4">We use cookies and similar tracking technologies to track the activity on our service and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent.</p>

                            <h4 class="fw-bold mb-3 text-primary">6. Changes to This Policy</h4>
                            <p class="text-muted mb-4">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page. You are advised to review this Privacy Policy periodically for any changes.</p>

                            <h4 class="fw-bold mb-3 text-primary">7. Contact Us</h4>
                            <p class="text-muted mb-0">If you have any questions about this Privacy Policy, please contact us at <a href="mailto:privacy@apsdreamhomes.com" class="text-primary fw-bold">privacy@apsdreamhomes.com</a></p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="100">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
