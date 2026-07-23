<?php
$breadcrumbs = $breadcrumbs ?? [['title' => 'Home', 'url' => BASE_URL], ['title' => 'Disclaimer', 'url' => '']];
?>
<section class="section-padding py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-6 fw-bold text-primary mb-2">Disclaimer</h1>
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <?php if (isset($crumb['url']) && $crumb['url']): ?>
                                <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= $crumb['title'] ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-3 text-primary">1. General Information</h4>
                        <p class="text-muted mb-4">The information provided on APS Dream Home website and mobile application is for general informational purposes only. All information on the site is provided in good faith; however, we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the site.</p>

                        <h4 class="fw-bold mb-3 text-primary">2. Property Information</h4>
                        <p class="text-muted mb-4">All property listings, prices, dimensions, images, and specifications are provided by the respective builders/developers and are subject to change without notice. APS Dream Home acts as an intermediary and does not guarantee the accuracy of such information. Buyers are advised to verify all details independently before making any purchase decision.</p>

                        <h4 class="fw-bold mb-3 text-primary">3. RERA Compliance</h4>
                        <p class="text-muted mb-4">Real estate projects displayed on our platform may be subject to the Real Estate (Regulation and Development) Act, 2016 (RERA). Buyers should independently verify RERA registration status of any project with the respective State RERA authority before entering into any agreement.</p>

                        <h4 class="fw-bold mb-3 text-primary">4. Financial Calculators</h4>
                        <p class="text-muted mb-4">The financial calculators (stamp duty, EMI, property tax, etc.) provided on this platform offer approximate estimates for informational purposes only. Actual values may vary based on multiple factors. Please consult a certified financial advisor for precise calculations.</p>

                        <h4 class="fw-bold mb-3 text-primary">5. External Links</h4>
                        <p class="text-muted mb-4">The site may contain links to external websites that are not provided or maintained by APS Dream Home. We do not guarantee the accuracy, relevance, timeliness, or completeness of information on these external websites.</p>

                        <h4 class="fw-bold mb-3 text-primary">6. Professional Advice</h4>
                        <p class="text-muted mb-4">The content on this platform should not be construed as professional legal, financial, or real estate advice. Users are encouraged to consult qualified professionals before making property-related decisions.</p>

                        <h4 class="fw-bold mb-3 text-primary">7. Limitation of Liability</h4>
                        <p class="text-muted mb-4">In no event shall APS Dream Home be liable for any loss or damage, including without limitation indirect or consequential loss or damage, arising from use of or reliance on the information provided on this platform.</p>

                        <h4 class="fw-bold mb-3 text-primary">8. Contact Us</h4>
                        <p class="text-muted mb-0">If you have any questions about this disclaimer, please contact us at <a href="mailto:legal@apsdreamhome.com" class="text-primary fw-bold">legal@apsdreamhome.com</a> or call us at <a href="tel:+919277121112" class="text-primary fw-bold">+91 9277121112</a>.</p>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="<?= BASE_URL ?>" class="btn btn-primary rounded-pill px-5 py-3 shadow-sm">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
