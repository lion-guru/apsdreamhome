<?php
/**
 * Bohisawagar Gorakhpur Project View - APS Dream Homes
 *
 * Honesty note: Bohisawagar has been announced as an upcoming development but
 * has no colonies row, projects row, recorded plot inventory, published rate
 * card, area or possession date. Every numeric figure this file previously
 * carried (\u20B99,50,000 / 25 Acres / 200 units / March 2025 possession) was
 * invented with no data source and has been removed. This page therefore
 * states only what can be verified today.
 */

$page_title = 'Bohisawagar Gorakhpur - Upcoming APS Dream Homes Township';

ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="page-banner mb-5">
            <div class="container text-center">
                <h1 class="display-3 fw-bold mb-3 animate-fade-up">Bohisawagar</h1>
                <p class="lead animate-fade-up">Upcoming Township in Gorakhpur</p>
            </div>
        </div>

        <div class="container">
            <div class="row align-items-center mb-5 pb-4">
                <div class="col-lg-7">
                    <div class="pe-lg-5">
                        <h2 class="display-6 fw-bold text-primary mb-4">Bohisawagar Project</h2>
                        <h3 class="fs-4 fw-bold mb-3">Overview</h3>
                        <p class="mb-3">APS Dream Homes is developing land in the Bohisawagar region of Gorakhpur for a future integrated township. No rate card, inventory count, possession date or RERA registration number is published yet for this project.</p>
                        <p class="mb-4">If you are considering a plot here, please treat every figure you hear from any channel as unverified until the colony entry is formally registered and a rate card is published. Colony-wise RERA verification will be available on request at our legal desk (+91 70074 44842) or on up-rera.in once colony details are final.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> Gorakhpur (Bohisawagar corridor)</li>
                            <li class="mb-2"><i class="fas fa-road text-primary me-2"></i> Infrastructure under development</li>
                            <li class="mb-2"><i class="fas fa-file-alt text-primary me-2"></i> Colony-wise titles & documentation shared at the legal desk</li>
                            <li class="mb-2"><i class="fas fa-calendar-check text-primary me-2"></i> Status: announced, land acquisition in progress</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4">
                        <img src="<?= BASE_URL ?>/assets/images/projects/gorakhpur/suryoday.jpg" alt="APS Dream Homes township development in Gorakhpur" class="card-img-top rounded-4" loading="lazy">
                        <div class="card-body">
                            <h4 class="card-title mb-2">Current Townships in Gorakhpur</h4>
                            <p class="card-text text-muted small mb-3">Live, documented projects you can visit today:</p>
                            <div class="d-flex flex-column gap-2">
                                <a href="<?= BASE_URL ?>/colony/suryoday-colony" class="btn btn-outline-primary rounded-pill"><i class="fas fa-home me-2"></i>Suryoday Colony</a>
                                <a href="<?= BASE_URL ?>/project-locations/raghunath-nagri" class="btn btn-outline-primary rounded-pill"><i class="fas fa-home me-2"></i>Raghunath Nagri</a>
                                <a href="<?= BASE_URL ?>/contact" class="btn btn-primary rounded-pill"><i class="fas fa-phone-alt me-2"></i>Enquire About Bohisawagar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
                <h3 class="fs-4 fw-bold mb-3">What to expect</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-search-location text-primary"></i><span>Full township &amp; plot details shared on enquiry</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-file-contract text-primary"></i><span>Colony documentation at the legal desk</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-phone-alt text-primary"></i><span>Direct APS Dream Homes office contact</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 bg-primary text-white rounded-5 p-5 text-center mb-5 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-4 opacity-10">
                    <i class="fas fa-city fa-10x"></i>
                </div>
                <div class="position-relative z-index-1">
                    <h2 class="display-5 fw-bold mb-3">Interested in Bohisawagar?</h2>
                    <p class="lead mb-4" style="color: #dbeafe;">Talk to our Gorakhpur office team - we share current documented inventory and honest colony status.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="tel:+917007444842" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary">
                            <i class="fas fa-phone-alt me-2"></i>Call for Details
                        </a>
                        <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold">
                            Get Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
