<?php
/**
 * Ganga Nagri Project View - APS Dream Homes
 */
?>

<div class="row">
    <div class="col-lg-12">
        <div class="page-banner mb-5" style="background: linear-gradient(rgba(30,60,114,0.7), rgba(30,60,114,0.7)), url('<?= ASSETS_URL ?>images/banner/project-banner.jpg') center/cover; padding: 100px 0; color: #fff; border-radius: 0 0 50px 50px;">
            <div class="container text-center">
                <h1 class="display-3 fw-bold mb-3 animate-fade-up">Ganga Nagri Varanasi</h1>
                <p class="lead animate-fade-up">Divine Living Near the Holy Ganges</p>
            </div>
        </div>

        <div class="container py-5">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6">
                    <h2 class="display-6 fw-bold text-primary mb-4">Divine Township</h2>
                    <p class="lead text-muted mb-4">
                        Ganga Nagri is our flagship project in Varanasi, offering a perfect blend of 
                        spirituality and modern living. Located in a serene environment, it provides 
                        all the modern conveniences you expect.
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Prime Location in Varanasi</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Gated Community</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Modern Infrastructure</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Excellent Connectivity</li>
                    </ul>
                </div>
                <div class="col-lg-6">
                    <img src="<?= ASSETS_URL ?>images/projects/ganga-nagri.jpg" alt="Ganga Nagri" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>

            <!-- Amenities Section -->
            <?php if (!empty($amenities)): ?>
            <div class="row mt-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Amenities</h2>
                </div>
                <div class="col-12">
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($amenities as $item): ?>
                        <div class="col-6 col-md-4 col-lg-3 text-center">
                            <div class="card h-100 border-0 shadow-sm p-4">
                                <div class="mb-3">
                                    <img src="<?= ASSETS_URL ?>images/<?= $item['image'] ?>" alt="<?= $item['alt_text'] ?>" class="img-fluid" style="height: 50px;">
                                </div>
                                <h6 class="fw-bold"><?= $item['title'] ?></h6>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Video Section -->
            <?php if (!empty($videos)): ?>
            <div class="row mt-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Project Highlights</h2>
                </div>
                <?php foreach ($videos as $video): ?>
                <div class="col-md-6 mb-4">
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                        <iframe src="https://www.youtube.com/embed/<?= $video['youtube_id'] ?>" title="<?= $video['title'] ?>" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
