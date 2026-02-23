<!-- Hero Section -->
<section class="hero-team py-5 text-white" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= get_asset_url('assets/images/hero-3.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="hero-title display-4 fw-bold mb-4" data-aos="fade-up">
                    <i class="fas fa-users me-3"></i>
                    Meet Our Team
                </h1>
                <p class="hero-subtitle lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Our experienced team of real estate professionals is dedicated to helping you find your perfect property and achieve your real estate goals.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="#contact" class="btn btn-primary btn-lg" data-aos="fade-up" data-aos-delay="200">Get In Touch</a>
                    <a href="<?= BASE_URL ?>careers" class="btn btn-outline-light btn-lg" data-aos="fade-up" data-aos-delay="300">Join Our Team</a>
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
                    <li class="breadcrumb-item active" aria-current="page">Our Team</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Stats Section -->
<section class="stats-section py-5 bg-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stats-item">
                    <div class="stats-counter" id="teamMembersCount">50</div>
                    <p class="mb-0">Team Members</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stats-item">
                    <div class="stats-counter" id="yearsExperienceCount">75</div>
                    <p class="mb-0">Years Combined Experience</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stats-item">
                    <div class="stats-counter" id="propertiesSoldCount">2500</div>
                    <p class="mb-0">Properties Sold</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="stats-item">
                    <div class="stats-counter" id="clientSatisfactionCount">98</div>
                    <p class="mb-0">Client Satisfaction %</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section py-5">
    <div class="container">
        <h2 class="text-center mb-5" data-aos="fade-up">Our Leadership Team</h2>
        <div class="row g-4">
            <?php if (!empty($team_members)): ?>
                <?php foreach ($team_members as $index => $member): ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="team-page-member">
                            <div class="member-avatar">
                                <?php
                                $photoUrl = $member->photo ? get_asset_url($member->photo) : 'https://via.placeholder.com/150/667eea/ffffff?text=' . substr($member->name, 0, 2);
                                ?>
                                <img src="<?php echo htmlspecialchars($photoUrl); ?>"
                                    alt="<?php echo htmlspecialchars($member->name); ?>"
                                    onerror="this.src='https://via.placeholder.com/150/667eea/ffffff?text=<?php echo substr($member->name, 0, 2); ?>'">
                                <div class="social-links">
                                    <?php if (!empty($member->linkedin)): ?>
                                        <a href="<?php echo htmlspecialchars($member->linkedin); ?>" class="social-link" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h4 class="member-name"><?php echo htmlspecialchars($member->name); ?></h4>
                            <p class="member-role text-primary"><?php echo htmlspecialchars($member->position); ?></p>
                            <p class="member-description">
                                <?php echo htmlspecialchars($member->bio ?? ''); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback static content -->
                <div class="col-12 text-center">
                    <p>Team members are currently being updated. Please check back soon.</p>
                </div>
            <?php endif; ?>
            <div class="col-lg-3 col-md-6">
                <div class="value-card text-center">
                    <div class="value-icon mb-3">
                        <i class="fas fa-handshake fa-3x text-primary"></i>
                    </div>
                    <h5>Integrity</h5>
                    <p class="text-muted">
                        We conduct all our business with honesty, transparency, and ethical practices.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="value-card text-center">
                    <div class="value-icon mb-3">
                        <i class="fas fa-users fa-3x text-success"></i>
                    </div>
                    <h5>Client Focus</h5>
                    <p class="text-muted">
                        Our clients' needs and satisfaction are at the center of everything we do.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="value-card text-center">
                    <div class="value-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-warning"></i>
                    </div>
                    <h5>Excellence</h5>
                    <p class="text-muted">
                        We strive for excellence in every service we provide and every interaction we have.
                    </p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="value-card text-center">
                    <div class="value-icon mb-3">
                        <i class="fas fa-lightbulb fa-3x text-info"></i>
                    </div>
                    <h5>Innovation</h5>
                    <p class="text-muted">
                        We embrace new technologies and innovative approaches to serve our clients better.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section py-5 cta-section-success">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h3 class="text-white mb-4">
                    <i class="fas fa-phone me-2"></i>
                    Ready to Work with Our Expert Team?
                </h3>
                <p class="text-white-50 mb-4">
                    Contact us today and experience the APS Dream Home difference. Our team is ready to help you achieve your real estate goals.
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-phone me-2"></i>Contact Our Team
                    </a>
                    <a href="<?php echo BASE_URL; ?>about" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>