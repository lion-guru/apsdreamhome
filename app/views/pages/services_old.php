<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .service-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        .service-card:hover {
            transform: translateY(-10px);
        }
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0 auto 25px;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }
        .cta-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Our Services</h1>
                    <p class="lead mb-4">
                        Comprehensive real estate solutions tailored to meet your unique needs.
                        From property development to investment advisory, we offer end-to-end services.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Comprehensive Services</h2>
                <p class="lead text-muted">Professional real estate services designed to exceed expectations</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h5>Property Development</h5>
                        <p class="text-muted">Complete property development solutions from concept to completion. We handle everything from site selection to project delivery with unmatched quality standards.</p>
                        <a href="/contact" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Investment Advisory</h5>
                        <p class="text-muted">Expert investment guidance to help you make informed decisions in the real estate market. We provide comprehensive market analysis and investment strategies.</p>
                        <a href="/contact" class="btn btn-outline-primary">Get Advice</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5>Commercial Properties</h5>
                        <p class="text-muted">Specialized services for commercial property development, leasing, and management. We cater to businesses of all sizes with customized solutions.</p>
                        <a href="/contact" class="btn btn-outline-primary">Explore</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h5>Land Acquisition</h5>
                        <p class="text-muted">Strategic land acquisition services to help you secure prime locations for development. We handle due diligence, legal processes, and negotiations.</p>
                        <a href="/contact" class="btn btn-outline-primary">Find Land</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5>Property Management</h5>
                        <p class="text-muted">Complete property management solutions including maintenance, tenant relations, and financial management for optimal returns on your investment.</p>
                        <a href="/contact" class="btn btn-outline-primary">Manage Property</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h5>Real Estate Consulting</h5>
                        <p class="text-muted">Professional consulting services covering market research, feasibility studies, and strategic planning for successful real estate ventures.</p>
                        <a href="/contact" class="btn btn-outline-primary">Get Consultation</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Process</h2>
                <p class="lead text-muted">How we ensure successful project delivery</p>
            </div>

            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; background: #667eea; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">1</div>
                    </div>
                    <h5>Consultation</h5>
                    <p class="text-muted">Initial consultation to understand your requirements and objectives</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; background: #667eea; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">2</div>
                    </div>
                    <h5>Planning</h5>
                    <p class="text-muted">Detailed planning and feasibility analysis for your project</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; background: #667eea; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">3</div>
                    </div>
                    <h5>Execution</h5>
                    <p class="text-muted">Professional execution with regular updates and quality control</p>
                </div>
                <div class="col-md-3 text-center mb-4">
                    <div class="mb-3">
                        <div style="width: 60px; height: 60px; background: #667eea; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold;">4</div>
                    </div>
                    <h5>Delivery</h5>
                    <p class="text-muted">On-time delivery with complete documentation and support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">500+</h2>
                    <h5>Projects Completed</h5>
                    <p class="text-white-50">Successful deliveries across UP</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">8+</h2>
                    <h5>Years Experience</h5>
                    <p class="text-white-50">Industry expertise since 2016</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">1000+</h2>
                    <h5>Happy Clients</h5>
                    <p class="text-white-50">Satisfied customers and families</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">15+</h2>
                    <h5>Locations</h5>
                    <p class="text-white-50">Prime development sites</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="mb-4">Ready to Start Your Project?</h2>
                    <p class="lead mb-4">
                        Let's discuss how we can help bring your real estate vision to life
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="/contact" class="btn btn-light btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us Today
                        </a>
                        <a href="/properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search me-2"></i>View Our Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
