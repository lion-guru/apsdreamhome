<?php
/**
 * Budha City Kushinagar Project View - APS Dream Homes
 */
?>

<!-- Page Header -->
<section class="project-hero-section section-padding bg-primary text-white text-center rounded-bottom-4 py-5" data-aos="fade-down">
    <div class="container py-4">
        <h1 class="display-5 fw-bold mb-2">Budha City</h1>
        <p class="lead mb-0">Integrated Township at Premwaliya, Kushinagar Highway</p>
    </div>
</section>

<!-- Breadcrumb -->
<nav class="bg-light border-bottom py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <?php foreach ($breadcrumbs as $crumb): ?>
                <?php if (isset($crumb['url'])): ?>
                    <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= $crumb['title'] ?></a></li>
                <?php else: ?>
                    <li class="breadcrumb-item active"><?= $crumb['title'] ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>

<div class="full-row bg-white py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4 mb-md-0" data-aos="fade-right">
                        <img src="<?= get_asset_url('projects/budha-city-main.jpg', 'images') ?>" class="img-fluid rounded-4 shadow-lg" alt="Budha City Overview">
                    </div>
                    <div class="col-md-6" data-aos="fade-left">
                        <h2 class="text-secondary double-down-line mb-4">OVERVIEW - Budha City</h2>
                        <p class="lead"><b>BUDHA CITY Situated At Premwaliya kushinagar highway.</b></p>
                        <p>APS Dream Homes is an integrated township located at Prime Location of Kushinagar. The township spread across more than 15 acres being developed in blocks, which includes Plots, Row Houses and commercial space along with Entrance Gate, Electricity, Drainage system, Shopping Centre etc.</p>
                        <p class="fw-bold text-dark">Our Township is located nearby maximum number of Engineering Colleges, Educational Institutions, Dental Colleges, Railway Station, Petrol Pumps, Airport, service stations, Banks, ATM, and City Mall.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amenities Section -->
        <div class="full-row bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center mb-5">
                        <h2 class="text-secondary">TOP AMENITIES</h2>
                        <p class="text-muted">Modern facilities for a comfortable lifestyle</p>
                    </div>
                </div>
                <div class="row g-4">
                    <?php if (!empty($amenities)): ?>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-3 col-sm-6" data-aos="zoom-in">
                                <div class="amenity-card bg-white p-4 rounded-4 shadow-sm text-center h-100 transition-hover">
                                    <img src="<?= get_asset_url($amenity['image'], 'images') ?>" class="img-fluid mb-3 rounded-3" alt="<?= $amenity['alt_text'] ?>">
                                    <h5 class="mb-0"><?= $amenity['title'] ?></h5>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .project-hero-section {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }
    .transition-hover {
        transition: all 0.3s ease;
    }
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>
