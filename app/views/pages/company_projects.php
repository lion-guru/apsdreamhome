<?php

/**
 * APS Dream Homes - Company Projects Portfolio
 * Partial View
 */
?>

<!-- Hero Section -->
<section class="page-header py-5 text-white text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?= get_asset_url('assets/images/hero-3.jpg') ?>'); background-size: cover; background-position: center;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="page-title display-4 fw-bold animate-fade-up">
                    <i class="fas fa-building me-3"></i>
                    Company Projects & Portfolio
                </h1>
                <p class="page-subtitle lead animate-fade-up">
                    Discover APS Dream Homes' comprehensive portfolio of premium real estate developments
                </p>
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
                    <li class="breadcrumb-item active" aria-current="page">Company Projects</li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Company Information -->
<section class="py-5">
    <div class="container">
        <div class="company-info animate-fade-up">
            <div class="row align-items-center">
                <div class="col-lg-3 text-center">
                    <div class="company-logo">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3 class="company-title">APS Dream Homes Pvt Ltd</h3>
                    <p class="text-muted">Established 2022</p>
                </div>
                <div class="col-lg-9">
                    <div class="company-description">
                        <p>APS Dream Homes Pvt Ltd is a registered real estate development company specializing in residential and commercial properties across Gorakhpur and surrounding regions. With a commitment to quality construction, innovative design, and customer satisfaction, we have established ourselves as a trusted name in the real estate industry.</p>
                        <p>Our portfolio includes premium apartments, luxury villas, commercial spaces, and plotted developments, each designed to meet the evolving needs of modern homeowners and investors.</p>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Locations</h6>
                            <p class="text-muted">Gorakhpur, Lucknow, Delhi NCR</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-certificate text-success me-2"></i>Registration</h6>
                            <p class="text-muted">U70109UP2022PTC163047</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-users text-info me-2"></i>Team Size</h6>
                            <p class="text-muted">50+ Professionals</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Overview -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 animate-fade-up">
            <i class="fas fa-chart-bar me-2"></i>
            Portfolio Overview
        </h2>
        <div class="stats-grid">
            <div class="stat-card animate-fade-up">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number"><?php echo $project_stats['total']; ?></div>
                <div class="stat-label">Total Projects</div>
            </div>

            <div class="stat-card animate-fade-up" style="animation-delay: 0.1s;">
                <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $project_stats['completed']; ?></div>
                <div class="stat-label">Completed</div>
            </div>

            <div class="stat-card animate-fade-up" style="animation-delay: 0.2s;">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <i class="fas fa-hammer"></i>
                </div>
                <div class="stat-number"><?php echo $project_stats['ongoing']; ?></div>
                <div class="stat-label">Under Construction</div>
            </div>

            <div class="stat-card animate-fade-up" style="animation-delay: 0.3s;">
                <div class="stat-icon" style="background: linear-gradient(135deg, #17a2b8, #20c997);">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="stat-number"><?php echo $project_stats['upcoming']; ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
    </div>
</section>

<!-- Project Filter -->
<section class="py-4">
    <div class="container">
        <div class="filter-tabs animate-fade-up">
            <div class="d-flex justify-content-center flex-wrap">
                <button class="filter-tab active" data-filter="all">All Projects</button>
                <button class="filter-tab" data-filter="completed">Completed</button>
                <button class="filter-tab" data-filter="ongoing">Under Construction</button>
                <button class="filter-tab" data-filter="upcoming">Upcoming</button>
                <button class="filter-tab" data-filter="residential">Residential</button>
                <button class="filter-tab" data-filter="commercial">Commercial</button>
            </div>
        </div>
    </div>
</section>

<!-- Company Projects -->
<section class="py-5">
    <div class="container">
        <?php if (empty($company_projects)): ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3 class="text-muted">No Projects Available</h3>
                <p class="text-muted">Company projects will be displayed here once they are added to the database.</p>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($company_projects as $project): ?>
                    <div class="project-card animate-fade-up" data-status="<?php echo $project['status']; ?>" data-type="<?php echo $project['project_type']; ?>">
                        <div class="project-image">
                            <?php
                            $projectImage = !empty($project['image_url']) ? get_asset_url($project['image_url']) : 'https://via.placeholder.com/400x250/667eea/white?text=' . urlencode($project['title']);
                            ?>
                            <img src="<?php echo $projectImage; ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <div class="project-badges">
                                <span class="project-badge <?php echo $project['status']; ?>">
                                    <?php echo ucfirst($project['status']); ?>
                                </span>
                                <?php if ($project['featured']): ?>
                                    <span class="project-badge featured">Featured</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <div class="project-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($project['location']); ?>
                            </div>

                            <?php if ($project['description']): ?>
                                <div class="project-description">
                                    <?php echo htmlspecialchars(substr($project['description'], 0, 150)); ?>
                                    <?php if (strlen($project['description']) > 150): ?>
                                        ...
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="project-details">
                                <div class="project-detail">
                                    <div class="project-detail-label">Budget</div>
                                    <div class="project-detail-value">
                                        ₹<?php echo number_format($project['budget'] ?? 0); ?>
                                    </div>
                                </div>
                                <div class="project-detail">
                                    <div class="project-detail-label">Units</div>
                                    <div class="project-detail-value">
                                        <?php echo $project['total_units'] ?? 'N/A'; ?>
                                    </div>
                                </div>
                                <div class="project-detail">
                                    <div class="project-detail-label">Type</div>
                                    <div class="project-detail-value">
                                        <?php echo ucfirst($project['project_type'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <div class="project-detail">
                                    <div class="project-detail-label">Area</div>
                                    <div class="project-detail-value">
                                        <?php echo $project['total_area'] ?? 'N/A'; ?> sq.ft
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Company Achievements Timeline -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5 animate-fade-up">
            <i class="fas fa-trophy me-2"></i>
            Company Milestones
        </h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline">
                    <div class="timeline-item animate-fade-up">
                        <div class="timeline-marker">
                            <i class="fas fa-star text-primary"></i>
                        </div>
                        <div>
                            <div class="timeline-year">2024</div>
                            <div class="timeline-description">
                                Launched 5 major residential projects in prime locations, achieving 98% customer satisfaction rate
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item animate-fade-up" style="animation-delay: 0.2s;">
                        <div class="timeline-marker">
                            <i class="fas fa-building text-success"></i>
                        </div>
                        <div>
                            <div class="timeline-year">2023</div>
                            <div class="timeline-description">
                                Expanded operations to commercial real estate with the launch of APS Business Park
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item animate-fade-up" style="animation-delay: 0.4s;">
                        <div class="timeline-marker">
                            <i class="fas fa-certificate text-warning"></i>
                        </div>
                        <div>
                            <div class="timeline-year">2022</div>
                            <div class="timeline-description">
                                Company registration and launch of first project - APS Green Valley Apartments
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item animate-fade-up" style="animation-delay: 0.6s;">
                        <div class="timeline-marker">
                            <i class="fas fa-handshake text-info"></i>
                        </div>
                        <div>
                            <div class="timeline-year">2022</div>
                            <div class="timeline-description">
                                Partnership with leading architects and construction firms for quality assurance
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5" style="background: var(--primary-gradient);">
    <div class="container text-center">
        <h2 class="text-white mb-4">Interested in Our Projects?</h2>
        <p class="text-white-50 mb-4">Get in touch with us for detailed project information and investment opportunities</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="http://localhost.//contact" class="btn btn-light btn-lg px-4 py-3">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="http://localhost.//properties" class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="fas fa-search me-2"></i>Browse Properties
            </a>
            <a href="http://localhost.//about" class="btn btn-warning btn-lg px-4 py-3">
                <i class="fas fa-info-circle me-2"></i>Learn More
            </a>
        </div>
    </div>
</section>

<!-- Page Specific CSS -->
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }

    /* Page Header */
    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 4rem 0 2rem;
        margin-bottom: 3rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        margin-top: -3rem;
        position: relative;
        z-index: 10;
    }

    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-10px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: white;
        font-size: 1.5rem;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #636e72;
        font-weight: 500;
    }

    /* Filter Tabs */
    .filter-tabs {
        margin-bottom: 3rem;
    }

    .filter-tab {
        background: none;
        border: none;
        padding: 0.8rem 1.5rem;
        margin: 0.5rem;
        border-radius: 50px;
        font-weight: 600;
        color: #636e72;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .filter-tab:hover,
    .filter-tab.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    /* Project Cards */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }

    .project-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
    }

    .project-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .project-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .project-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .project-card:hover .project-image img {
        transform: scale(1.1);
    }

    .project-badges {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .project-badge {
        padding: 0.4rem 1rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .project-badge.completed {
        background: #28a745;
    }

    .project-badge.ongoing {
        background: #fd7e14;
    }

    .project-badge.upcoming {
        background: #17a2b8;
    }

    .project-badge.featured {
        background: #ffc107;
        color: #000;
    }

    .project-content {
        padding: 2rem;
    }

    .project-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: #2d3436;
    }

    .project-location {
        color: #636e72;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .project-description {
        color: #636e72;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .project-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }

    .project-detail-label {
        font-size: 0.8rem;
        color: #b2bec3;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
    }

    .project-detail-value {
        font-weight: 600;
        color: #2d3436;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding: 2rem 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        margin-bottom: 3rem;
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .timeline-item:hover .timeline-marker {
        border-color: #667eea;
        transform: scale(1.1);
    }

    .timeline-year {
        font-size: 1.2rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 0.5rem;
    }

    .timeline-description {
        color: #636e72;
        line-height: 1.6;
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    /* Company Info */
    .company-logo {
        width: 100px;
        height: 100px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        margin: 0 auto 1.5rem;
    }

    .company-title {
        font-weight: 700;
        color: #2d3436;
        margin-bottom: 0.5rem;
    }

    /* Animations */
    .animate-fade-up {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .animate-fade-up.visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<!-- Page Specific JS -->
<script>
    // Initialize AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }

    // Project Filter Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const projectCards = document.querySelectorAll('.project-card');

        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Filter projects
                projectCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    const type = card.getAttribute('data-type');

                    if (filter === 'all' ||
                        (filter === 'completed' && status === 'completed') ||
                        (filter === 'ongoing' && status === 'ongoing') ||
                        (filter === 'upcoming' && status === 'upcoming') ||
                        (filter === 'residential' && type === 'residential') ||
                        (filter === 'commercial' && type === 'commercial')) {
                        card.style.display = 'block';
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            card.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });

        // Animate cards on load
        projectCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';

            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible'); // Changed to visible class to avoid conflict with AOS
                entry.target.classList.remove('animate-fade-up'); // Remove initial class
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.stat-card, .company-info').forEach(el => {
        observer.observe(el);
    });
</script>