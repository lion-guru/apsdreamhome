<?php
/**
 * APS Dream Homes - Company Projects Portfolio
 * Modern UI/UX for displaying company projects and portfolio
 */

// Get company projects from database
$company_projects = [];
try {
    if (isset($pdo) && $pdo) {
        $projects_query = "
            SELECT
                cp.*,
                p.title as property_title,
                p.price,
                p.image_url,
                p.location,
                p.type,
                COUNT(cp.id) as project_count
            FROM company_projects cp
            LEFT JOIN properties p ON cp.property_id = p.id
            GROUP BY cp.id
            ORDER BY cp.created_at DESC
        ";
        $stmt = $pdo->prepare($projects_query);
        $stmt->execute();
        $company_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log('Company projects fetch error: ' . $e->getMessage());
}

// Get project statistics
$project_stats = [
    'total' => count($company_projects),
    'completed' => 0,
    'ongoing' => 0,
    'upcoming' => 0,
    'total_value' => 0
];

foreach ($company_projects as $project) {
    if ($project['status'] == 'completed') $project_stats['completed']++;
    if ($project['status'] == 'ongoing') $project_stats['ongoing']++;
    if ($project['status'] == 'upcoming') $project_stats['upcoming']++;
    $project_stats['total_value'] += $project['budget'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Homes - Company Projects & Portfolio</title>

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Modern Header */
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

        .page-header .container {
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: #666;
            font-weight: 600;
        }

        /* Project Cards */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .project-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            border: 2px solid transparent;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
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
            transition: transform 0.4s ease;
        }

        .project-card:hover .project-image img {
            transform: scale(1.05);
        }

        .project-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .project-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .project-badge.completed {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .project-badge.ongoing {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .project-badge.upcoming {
            background: linear-gradient(135deg, #17a2b8, #20c997);
        }

        .project-content {
            padding: 25px;
        }

        .project-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .project-location {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .project-location i {
            margin-right: 5px;
        }

        .project-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .project-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .project-detail {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .project-detail-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }

        .project-detail-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1a237e;
        }

        /* Filter Tabs */
        .filter-tabs {
            background: white;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-tab {
            background: none;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: var(--primary-gradient);
            color: white;
        }

        /* Company Info Section */
        .company-info {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .company-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .company-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a237e;
            margin-bottom: 1rem;
        }

        .company-description {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Achievement Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--primary-gradient);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }

        .timeline-marker {
            position: absolute;
            left: -2rem;
            top: 0;
            width: 40px;
            height: 40px;
            background: white;
            border: 3px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .timeline-year {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .timeline-description {
            color: #666;
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .projects-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .company-info {
                padding: 2rem;
            }

            .timeline {
                padding-left: 1rem;
            }

            .timeline-marker {
                left: -1.5rem;
                width: 30px;
                height: 30px;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                padding: 2rem 0 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-tabs {
                padding: 0.5rem;
            }

            .filter-tab {
                padding: 8px 15px;
                margin: 2px;
                font-size: 0.9rem;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Loading States */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>

<?php include '../app/views/layouts/header.php'; ?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="page-title animate-fade-up">
                    <i class="fas fa-building me-3"></i>
                    Company Projects & Portfolio
                </h1>
                <p class="page-subtitle animate-fade-up">
                    Discover APS Dream Homes' comprehensive portfolio of premium real estate developments
                </p>
            </div>
        </div>
    </div>
</section>

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
                            <img src="<?php echo $project['image_url'] ?? 'https://via.placeholder.com/400x250/667eea/white?text=' . urlencode($project['title']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
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
            <a href="/contact" class="btn btn-light btn-lg px-4 py-3">
                <i class="fas fa-phone me-2"></i>Contact Us
            </a>
            <a href="/properties" class="btn btn-outline-light btn-lg px-4 py-3">
                <i class="fas fa-search me-2"></i>Browse Properties
            </a>
            <a href="/about" class="btn btn-warning btn-lg px-4 py-3">
                <i class="fas fa-info-circle me-2"></i>Learn More
            </a>
        </div>
    </div>
</section>

<?php include '../app/views/layouts/footer.php'; ?>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

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
                entry.target.classList.add('animate-fade-up');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.stat-card, .company-info').forEach(el => {
        observer.observe(el);
    });
</script>

</body>
</html>
