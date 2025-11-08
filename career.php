<?php
/**
 * Career Page - APS Dream Homes
 * Job opportunities and career information
 */

session_start();
require_once 'core/functions.php';
require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();

    // Get active job openings
    $jobs_query = "SELECT * FROM jobs WHERE status = 'active' ORDER BY created_at DESC";
    $stmt = $pdo->query($jobs_query);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Career page database error: ' . $e->getMessage());
    $jobs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Include site settings
    require_once 'includes/site_settings.php';
    ?>
    <title><?php echo getSiteSetting('site_title', 'APS Dream Homes'); ?> - Careers</title>
    <meta name="description" content="Join the APS Dream Homes team! Explore exciting career opportunities in real estate, sales, marketing, and more. Build your future with us.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        .hero-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 100px 0 60px;
            text-align: center;
        }
        .section-padding {
            padding: 80px 0;
        }
        .job-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .value-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 40px 20px;
            text-align: center;
            transition: transform 0.3s ease;
            height: 100%;
        }
        .value-card:hover {
            transform: translateY(-5px);
        }
        .apply-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .apply-btn:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .benefit-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/templates/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Join Our Team</h1>
                    <p class="lead mb-4">Build your career with India's premier real estate network</p>
                    <p class="mb-0">We're always looking for talented individuals who are passionate about real estate and technology. Join us in transforming the way India buys and sells properties.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Join Us -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold">Why Join APS Dream Homes?</h2>
                    <p class="lead text-muted">Discover what makes us a great place to work</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="benefit-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5>Growth Opportunities</h5>
                        <p>Fast-track your career with our rapid growth and learning programs</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h5>Work-Life Balance</h5>
                        <p>Flexible working hours and supportive environment for personal growth</p>
                    </div>
              // Form submission
        document.getElementById('careerApplicationForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;

            fetch('job_application_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    this.reset();
                } else {
                    // Show validation errors
                    if (data.errors && data.errors.length > 0) {
                        alert('Please fix the following errors:\n\n' + data.errors.join('\n'));
                    } else {
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again later.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>

    <div class="col-lg-3 col-md-6">
                <div class="value-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Competitive Pay</h5>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Current Openings -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold">Current Openings</h2>
                    <p class="lead text-muted">Find your dream job with us</p>
                </div>
            </div>

            <?php if (empty($jobs)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        No job openings available at the moment. Please check back later or send us your resume for future opportunities.
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($jobs as $job): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card job-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($job['title']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?php echo htmlspecialchars($job['location']); ?>
                                    </p>
                                </div>
                                <span class="badge bg-<?php echo $job['type'] === 'full_time' ? 'success' : 'primary'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $job['type'])); ?>
                                </span>
                            </div>

                            <div class="mb-3">
                                <span class="text-primary fw-bold">₹<?php echo number_format($job['salary_min']); ?> - ₹<?php echo number_format($job['salary_max']); ?></span>
                                <small class="text-muted"> per month</small>
                            </div>

                            <p class="card-text mb-3"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>

                            <div class="mb-3">
                                <h6 class="mb-2">Requirements:</h6>
                                <small class="text-muted"><?php echo htmlspecialchars($job['requirements']); ?></small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Posted: <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                </small>
                                <a href="apply-job.php?id=<?php echo $job['id']; ?>" class="apply-btn">
                                    <i class="fas fa-paper-plane me-1"></i>Apply Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- General Application CTA -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <div class="bg-light p-4 rounded">
                        <h4 class="mb-3">Don't see a suitable opening?</h4>
                        <p class="mb-3">Send us your resume and we'll keep you in mind for future opportunities</p>
                        <a href="mailto:careers@apsdreamhomes.com?subject=General Application" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Email Your Resume
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Company Culture -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold">Our Culture</h2>
                    <p class="lead text-muted">What it's like to work at APS Dream Homes</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="text-center">
                        <i class="fas fa-lightbulb fa-3x text-warning mb-3"></i>
                        <h5>Innovation First</h5>
                        <p>We encourage creative thinking and embrace new technologies to solve real estate challenges.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h5>Collaborative Environment</h5>
                        <p>Teamwork is at our core. We believe the best solutions come from diverse perspectives working together.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="text-center">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h5>Customer Obsessed</h5>
                        <p>Every decision we make is guided by how it improves the experience for our customers.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits -->
    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="fw-bold">Employee Benefits</h2>
                    <p class="lead text-muted">We care about our team's well-being</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center p-3">
                        <i class="fas fa-briefcase fa-2x text-primary mb-3"></i>
                        <h6>Health Insurance</h6>
                        <small class="text-muted">Comprehensive medical coverage for you and your family</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center p-3">
                        <i class="fas fa-graduation-cap fa-2x text-success mb-3"></i>
                        <h6>Learning Budget</h6>
                        <small class="text-muted">Annual budget for courses, conferences, and certifications</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center p-3">
                        <i class="fas fa-home fa-2x text-info mb-3"></i>
                        <h6>Remote Work</h6>
                        <small class="text-muted">Flexible work arrangements to suit your lifestyle</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center p-3">
                        <i class="fas fa-gift fa-2x text-warning mb-3"></i>
                        <h6>Performance Bonus</h6>
                        <small class="text-muted">Quarterly bonuses based on individual and team performance</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact CTA -->
    <section class="section-padding bg-primary text-white">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-4">Ready to Join Us?</h2>
                    <p class="lead mb-4">Take the first step towards an exciting career in real estate technology</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="mailto:careers@apsdreamhomes.com" class="btn btn-light btn-lg">
                            <i class="fas fa-envelope me-2"></i>Email Resume
                        </a>
                        <form id="careerApplicationForm" class="application-form" action="<?php echo BASE_URL; ?>/submit/job-application" method="POST" enctype="multipart/form-data" data-aos="fade-up">
                            <button type="submit" class="btn btn-light btn-lg">
                                <i class="fas fa-phone me-2"></i>Contact HR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
