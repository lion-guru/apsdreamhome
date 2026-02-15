<?php
/**
 * Suryoday Colony Project View - APS Dream Homes
 */
?>

<div class="row">
    <div class="col-lg-12">
        <div class="hero-section-project" style="background: linear-gradient(rgba(30,60,114,0.7), rgba(30,60,114,0.7)), url('<?= ASSETS_URL ?>images/site_photo/gorakhpur/suryoday/suryoday.png') center/cover no-repeat; padding: 100px 0; color: #fff; text-align: center; border-radius: 0 0 50px 50px;">
            <div class="container">
                <h1 class="display-3 fw-bold mb-3 animate-fade-up">Suryoday Colony</h1>
                <p class="lead animate-fade-up">Gorakhpur's Finest Residential Community</p>
            </div>
        </div>

        <div class="container py-5">
            <div class="row g-5">
                <!-- Project Description -->
                <div class="col-lg-7">
                    <h2 class="display-6 fw-bold text-primary mb-4">About the Project</h2>
                    <p class="lead text-muted">
                        Suryoday Colony is a premium residential project designed for those who seek 
                        a peaceful yet connected lifestyle. Located in the heart of Gorakhpur, 
                        this colony offers modern infrastructure and essential amenities.
                    </p>
                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="card project-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary-subtle p-3 rounded-3 me-3 text-primary">
                                        <i class="fas fa-check-double fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">Ready to Move</h6>
                                        <small class="text-muted">Possession Status</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card project-card p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success-subtle p-3 rounded-3 me-3 text-success">
                                        <i class="fas fa-certificate fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">GDA Approved</h6>
                                        <small class="text-muted">Legal Approval</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Sidebar -->
                <div class="col-lg-5">
                    <div class="card shadow-lg border-0 rounded-4 p-4 sticky-top" style="top: 100px; z-index: 10;">
                        <h4 class="fw-bold mb-4">Interested?</h4>
                        <form action="<?= BASE_URL ?>contact" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="subject" value="Inquiry for Suryoday Colony">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
                            </div>
                            <button type="submit" class="btn btn-premium w-100 py-3 rounded-pill shadow-none">Enquire Now</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Amenities Section -->
            <?php if (!empty($amenities)): ?>
            <div class="row mt-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Amenities</h2>
                </div>
                <?php foreach ($amenities as $amenity): ?>
                <div class="col-6 col-md-3 mb-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="amenity-icon mb-3">
                            <img src="<?= ASSETS_URL ?>images/<?= $amenity['image'] ?>" alt="<?= $amenity['title'] ?>" class="img-fluid" style="height: 40px;">
                        </div>
                        <h6 class="fw-bold"><?= $amenity['title'] ?></h6>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Video Section -->
            <?php if (!empty($videos)): ?>
            <div class="row mt-5">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold text-primary">Project Walkthrough</h2>
                </div>
                <?php foreach ($videos as $video): ?>
                <div class="col-md-10 mx-auto">
                    <div class="ratio ratio-16x9 shadow-lg rounded-4 overflow-hidden">
                        <iframe src="https://www.youtube.com/embed/<?= $video['youtube_id'] ?>" title="<?= $video['title'] ?>" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
