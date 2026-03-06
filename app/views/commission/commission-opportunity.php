<?php
/**
 * APS Dream Home - Enhanced Marketing Page with Hybrid Commission System
 * Showcases both MLM and Traditional commission options with visual assets
 */

require_once __DIR__ . '/init.php';
require_once 'includes/hybrid_commission_manager.php';

// Initialize Hybrid Commission Manager
$hybridManager = new HybridCommissionManager($conn);

// Get commission system comparison data
$commissionComparison = $hybridManager->getCommissionComparison(0); // Get general comparison

// Page metadata
$pageTitle = "Choose Your Commission System - APS Dream Home";
$pageDescription = "Select between MLM Network System, Traditional Local Market, or Hybrid Commission System. Maximize your earnings with APS Dream Home's flexible commission options.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
        }
        .commission-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
        }
        .commission-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .commission-mlm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .commission-traditional {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .commission-hybrid {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .comparison-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .visual-showcase {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
        }
        .cta-button {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo/apslogo.png" alt="APS Dream Home" height="30">
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                <a class="nav-link" href="properties.php"><i class="fas fa-building"></i> Properties</a>
                <a class="nav-link active" href="commission-opportunity.php"><i class="fas fa-coins"></i> Commission Systems</a>
                <a class="nav-link" href="register_mlm.php"><i class="fas fa-user-plus"></i> Register</a>
                <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">🚀 Choose Your Commission System</h1>
            <p class="lead mb-4">Maximize your earnings with APS Dream Home's flexible commission options</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <p class="mb-4">Whether you prefer building networks, direct sales, or both - we have the perfect commission system for you!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Commission Systems Overview -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Three Powerful Commission Systems</h2>
                <p class="lead text-muted">Choose the system that fits your business style and goals</p>
            </div>

            <div class="row g-4">
                <!-- MLM Network System -->
                <div class="col-md-4">
                    <div class="card commission-card commission-mlm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <h3 class="card-title">🎯 MLM Network System</h3>
                            <p class="card-text">Build your team and earn from network performance across 10 levels</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check"></i> Up to 28% total commissions</li>
                                <li><i class="fas fa-check"></i> 10-level network structure</li>
                                <li><i class="fas fa-check"></i> Leadership bonuses</li>
                                <li><i class="fas fa-check"></i> Passive income potential</li>
                                <li><i class="fas fa-check"></i> Team building rewards</li>
                            </ul>
                            <div class="mt-3">
                                <small>Perfect for: Network builders, Team leaders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traditional Local Market -->
                <div class="col-md-4">
                    <div class="card commission-card commission-traditional">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3 class="card-title">🏢 Traditional Local Market</h3>
                            <p class="card-text">Direct sales commissions with regional performance bonuses</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check"></i> 7-8.5% direct commissions</li>
                                <li><i class="fas fa-check"></i> Regional performance bonuses</li>
                                <li><i class="fas fa-check"></i> Quarterly rewards</li>
                                <li><i class="fas fa-check"></i> Local market focus</li>
                                <li><i class="fas fa-check"></i> Individual recognition</li>
                            </ul>
                            <div class="mt-3">
                                <small>Perfect for: Direct sellers, Regional specialists</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hybrid System (Recommended) -->
                <div class="col-md-4">
                    <div class="card commission-card commission-hybrid">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h3 class="card-title">🚀 Hybrid System</h3>
                            <p class="card-text">Best of both worlds - MLM + Traditional commissions</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check"></i> Maximum earning potential</li>
                                <li><i class="fas fa-check"></i> Diversified income streams</li>
                                <li><i class="fas fa-check"></i> Network + Direct sales</li>
                                <li><i class="fas fa-check"></i> Regional + Network bonuses</li>
                                <li><i class="fas fa-check"></i> Ultimate flexibility</li>
                            </ul>
                            <div class="mt-3">
                                <small class="badge bg-light text-dark">RECOMMENDED</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visual Showcase -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">See How It Works</h2>
                <p class="lead text-muted">Visual representation of our commission systems</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="visual-showcase">
                        <h4 class="mb-3">🎯 MLM Commission Structure</h4>
                        <img src="assets/images/mlm/commission-visualization.jpg" alt="MLM Commission Structure" class="img-fluid rounded mb-3">
                        <p class="text-muted">Build your network across 10 levels and earn from team performance. The more your team grows, the more you earn!</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="visual-showcase">
                        <h4 class="mb-3">💰 Earning Potential Visualization</h4>
                        <img src="assets/images/portfolio/enhanced-design.jpg" alt="Earning Potential" class="img-fluid rounded mb-3">
                        <p class="text-muted">See the potential earnings with our hybrid commission system. Combine direct sales with network building for maximum income!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Commission System Comparison</h2>
                <p class="lead text-muted">Detailed comparison of all three commission options</p>
            </div>

            <div class="comparison-table p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Feature</th>
                                <th>🎯 MLM Network</th>
                                <th>🏢 Traditional</th>
                                <th>🚀 Hybrid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Commission Rate</strong></td>
                                <td>2% - 18% (10 levels)</td>
                                <td>7% - 8.5% (direct)</td>
                                <td>Both systems</td>
                            </tr>
                            <tr>
                                <td><strong>Max Earning Potential</strong></td>
                                <td>28% total</td>
                                <td>8.5% + bonuses</td>
                                <td>36.5% combined</td>
                            </tr>
                            <tr>
                                <td><strong>Network Building</strong></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-times text-danger"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Direct Sales</strong></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Regional Bonuses</strong></td>
                                <td><i class="fas fa-times text-danger"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Performance Bonuses</strong></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Passive Income</strong></td>
                                <td><i class="fas fa-check text-success"></i></td>
                                <td><i class="fas fa-times text-danger"></i></td>
                                <td><i class="fas fa-check text-success"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Best For</strong></td>
                                <td>Network builders</td>
                                <td>Direct sellers</td>
                                <td>Ambitious agents</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Stories -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Success Stories</h2>
                <p class="lead text-muted">Real earnings from our commission systems</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle p-3 me-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Raj Kumar</h5>
                                <small class="text-muted">MLM Network Builder</small>
                            </div>
                        </div>
                        <p>"I built a team of 50+ agents in just 3 months. Now earning ₹1.5L monthly from network commissions alone!"</p>
                        <div class="text-success fw-bold">💰 ₹1,50,000/month</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success rounded-circle p-3 me-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Priya Sharma</h5>
                                <small class="text-muted">Traditional Agent</small>
                            </div>
                        </div>
                        <p>"Focus on direct sales in my region. Quarterly performance bonuses add ₹25K to my regular commissions!"</p>
                        <div class="text-success fw-bold">💰 ₹75,000/month</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info rounded-circle p-3 me-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Amit Patel</h5>
                                <small class="text-muted">Hybrid System User</small>
                            </div>
                        </div>
                        <p>"Best of both worlds! Network commissions + direct sales + regional bonuses. Maximum earning potential!"</p>
                        <div class="text-success fw-bold">💰 ₹2,25,000/month</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose APS Dream Home?</h2>
                <p class="lead text-muted">Industry-leading commission system with proven results</p>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-primary fs-2 mb-2">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>500+</h3>
                        <p class="text-muted">Active Agents</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-success fs-2 mb-2">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h3>₹2.5Cr+</h3>
                        <p class="text-muted">Commissions Paid</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-info fs-2 mb-2">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>1000+</h3>
                        <p class="text-muted">Properties Sold</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="text-warning fs-2 mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>4.9/5</h3>
                        <p class="text-muted">Agent Satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Start Earning?</h2>
            <p class="lead mb-4">Join APS Dream Home today and choose your commission system</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <p class="mb-4">Free registration • No hidden charges • Start earning immediately</p>
                    <a href="register_mlm.php" class="btn btn-light btn-lg cta-button me-3">
                        <i class="fas fa-user-plus"></i> Register Now
                    </a>
                    <a href="commission-calculator.php" class="btn btn-outline-light btn-lg cta-button">
                        <i class="fas fa-calculator"></i> Calculate Earnings
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img src="assets/images/logo/apslogo.png" alt="APS Dream Home" height="40" class="mb-2">
                    <p>Building dreams and creating wealth through innovative commission systems</p>
                </div>
                <div class="col-md-6 text-end">
                    <h5>Quick Links</h5>
                    <div class="d-flex justify-content-end gap-3">
                        <a href="properties.php" class="text-light">Properties</a>
                        <a href="register_mlm.php" class="text-light">Register</a>
                        <a href="login.php" class="text-light">Login</a>
                        <a href="contact.php" class="text-light">Contact</a>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <p>&copy; 2025 APS Dream Home. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add animation to cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.commission-card, .stats-card, .testimonial-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
