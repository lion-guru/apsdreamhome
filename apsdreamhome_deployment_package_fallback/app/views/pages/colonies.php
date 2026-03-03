<!-- Hero Section -->
<section class="hero-section text-white text-center py-5" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= get_asset_url('assets/images/hero-1.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4" data-aos="fade-up">
                    Our Premium <span class="text-warning">Colonies</span>
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Discover APS Dream Homes' exceptional real estate developments across Uttar Pradesh.
                    From luxury residential colonies to thriving commercial spaces, we create communities that inspire.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#colonies-container" class="btn btn-light btn-lg px-4 py-3">
                        <i class="fas fa-building me-2"></i>Explore Colonies
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-light btn-lg px-4 py-3">
                        <i class="fas fa-phone me-2"></i>Get Quote
                    </a>
                </div>
            </div>
        </div>
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
                    <li class="breadcrumb-item active" aria-current="page">Colonies</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up">
                    <span class="stat-number" data-target="<?php echo $colony_stats['total_colonies']; ?>"><?php echo $colony_stats['total_colonies']; ?></span>
                    <span class="stat-label">Active Colonies</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="100">
                    <span class="stat-number" data-target="<?php echo (int)$colony_stats['total_area']; ?>"><?php echo $colony_stats['total_area']; ?></span>
                    <span class="stat-label">Total Area</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="200">
                    <span class="stat-number" data-target="<?php echo $colony_stats['total_plots']; ?>"><?php echo number_format($colony_stats['total_plots']); ?></span>
                    <span class="stat-label">Total Plots</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-counter" data-aos="fade-up" data-aos-delay="300">
                    <span class="stat-number" data-target="<?php echo $colony_stats['cities_covered']; ?>"><?php echo $colony_stats['cities_covered']; ?></span>
                    <span class="stat-label">Cities Covered</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="py-5">
    <div class="container">
        <div class="filter-buttons" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all">All Colonies</button>
            <button class="filter-btn" data-filter="gorakhpur">Gorakhpur</button>
            <button class="filter-btn" data-filter="lucknow">Lucknow</button>
            <button class="filter-btn" data-filter="residential">Residential</button>
            <button class="filter-btn" data-filter="commercial">Commercial</button>
        </div>

        <!-- Colonies Grid -->
        <div class="row" id="colonies-container">
            <?php foreach ($colonies as $index => $colony): ?>
                <div class="col-lg-4 col-md-6 colony-item" data-location="<?php echo strtolower(explode(',', $colony['location'])[0]); ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index * 100); ?>">
                    <div class="colony-card">
                        <div class="colony-image">
                            <?php
                            $imagePath = $colony['image'];
                            if (strpos($imagePath, 'http') !== 0) {
                                $imagePath = get_asset_url($imagePath);
                            }
                            ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo $colony['name']; ?>" class="img-fluid">
                            <div class="colony-placeholder" style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; flex-direction: column; background: #eee; color: #555;">
                                <i class="fas fa-city fa-3x mb-2"></i>
                                <p class="mb-0 text-center px-2"><?php echo $colony['name']; ?></p>
                            </div>
                        </div>
                        <div class="colony-overlay">
                            <span class="status-badge"><?php echo $colony['completion_status']; ?></span>
                        </div>
                    </div>

                    <div class="colony-content">
                        <h3 class="colony-title"><?php echo $colony['name']; ?></h3>

                        <div class="colony-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo $colony['location']; ?>
                        </div>

                        <p class="colony-description"><?php echo $colony['description']; ?></p>

                        <div class="colony-highlights">
                            <?php foreach ($colony['highlights'] as $highlight): ?>
                                <span class="highlight-tag"><?php echo $highlight; ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="colony-specs">
                            <div class="spec-item">
                                <span class="spec-value"><?php echo $colony['total_area']; ?></span>
                                <span class="spec-label">Total Area</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-value"><?php echo $colony['available_plots']; ?></span>
                                <span class="spec-label">Available</span>
                            </div>
                            <div class="spec-item">
                                <span class="spec-value"><?php echo $colony['starting_price']; ?></span>
                                <span class="spec-label">Starting Price</span>
                            </div>
                        </div>

                        <div class="colony-amenities">
                            <h6><i class="fas fa-star me-2"></i>Amenities</h6>
                            <?php foreach (array_slice($colony['amenities'], 0, 4) as $amenity): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-check"></i>
                                    <?php echo $amenity; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($colony['amenities']) > 4): ?>
                                <small class="text-muted">+<?php echo count($colony['amenities']) - 4; ?> more amenities</small>
                            <?php endif; ?>
                        </div>

                        <div class="colony-actions">
                            <button class="btn btn-view-plots flex-fill">
                                <i class="fas fa-eye me-2"></i>View Plots
                            </button>
                            <button class="btn btn-outline-primary" onclick="showInterest('<?php echo $colony['id']; ?>')">
                                <i class="fas fa-heart me-2"></i>I'm Interested
                            </button>
                        </div>
                    </div>
                </div>
        </div>
    <?php endforeach; ?>
    </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2 class="mb-4">Ready to Invest in Your Future?</h2>
                <p class="lead mb-4">
                    Join thousands of happy customers who have found their dream properties with APS Dream Homes.
                    Our colonies offer the perfect blend of modern living and investment opportunities.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg px-5 py-3">
                        <i class="fas fa-calendar me-2"></i>Schedule Visit
                    </a>
                    <a href="<?php echo BASE_URL; ?>careers" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-handshake me-2"></i>Become Associate
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interest Modal -->
<div class="modal fade" id="interestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Express Interest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="interestForm">
                    <input type="hidden" id="colony_id" name="colony_id">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="I'm interested in this colony. Please contact me with more details."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>Submit Interest
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>