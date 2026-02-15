<?php
/**
 * Raghunath Nagri Project View - APS Dream Homes
 */
?>

<div class="row">
    <div class="col-lg-12">
        <div class="page-banner mb-5" style="background: linear-gradient(rgba(30,60,114,0.7), rgba(30,60,114,0.7)), url('<?= ASSETS_URL ?>images/banner/project-banner.jpg') center/cover; padding: 100px 0; color: #fff; border-radius: 0 0 50px 50px;">
            <div class="container text-center">
                <h1 class="display-3 fw-bold mb-3 animate-fade-up">Raghunath Nagri</h1>
                <p class="lead animate-fade-up">Premium Township at Motiram to Jhangha Road, Gorakhpur</p>
            </div>
        </div>

        <div class="container">
            <!-- Project Intro -->
            <div class="row align-items-center mb-5 pb-4">
                <div class="col-lg-7">
                    <h2 class="display-6 fw-bold text-primary mb-4">Integrated Township</h2>
                    <p class="lead text-muted mb-4">
                        <b>RAGHUNATH NAGRI</b> is a master-planned integrated township located at a prime location 
                        in Gorakhpur. Spread across more than 15 acres, it is designed to provide a 
                        luxurious and convenient lifestyle.
                    </p>
                    <p class="text-muted">
                        The township is strategically located near major educational institutions, 
                        engineering colleges, and essential services like hospitals, banks, and shopping malls.
                    </p>
                    <div class="row g-4 mt-2">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white p-2 rounded-3 me-3">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">15+ Acres</h6>
                                    <small class="text-muted">Total Area</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white p-2 rounded-3 me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Prime Location</h6>
                                    <small class="text-muted">Motiram to Jhangha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="position-relative">
                        <img src="<?= ASSETS_URL ?>images/projects/raghunath-nagri.jpg" alt="Raghunath Nagri" class="img-fluid rounded-4 shadow-lg">
                        <div class="position-absolute bottom-0 start-0 p-4 w-100">
                            <div class="bg-white p-3 rounded-3 shadow-sm d-flex align-items-center">
                                <div class="bg-success text-white p-2 rounded-circle me-3">
                                    <i class="fas fa-check"></i>
                                </div>
                                <h6 class="mb-0 fw-bold">RERA Registered</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities Section -->
            <?php if (!empty($amenities)): ?>
            <div class="row mb-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Modern Amenities</h2>
                    <div class="divider mx-auto" style="width: 100px; height: 4px; background: #1e3c72; border-radius: 2px;"></div>
                </div>
                <div class="col-12">
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($amenities as $item): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm text-center p-4 hover-up">
                                <div class="mb-3">
                                    <img src="<?= ASSETS_URL ?>images/<?= $item['image'] ?>" alt="<?= $item['alt_text'] ?>" class="img-fluid" style="height: 60px; object-fit: contain;">
                                </div>
                                <h6 class="fw-bold mb-0"><?= $item['title'] ?></h6>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Videos Section -->
            <?php if (!empty($videos)): ?>
            <div class="row mb-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Project Showcase</h2>
                </div>
                <?php foreach ($videos as $video): ?>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm overflow-hidden rounded-4">
                        <div class="ratio ratio-16x9">
                            <iframe src="https://www.youtube.com/embed/<?= $video['youtube_id'] ?>" title="<?= $video['title'] ?>" allowfullscreen></iframe>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-0 text-center"><?= $video['title'] ?></h6>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
