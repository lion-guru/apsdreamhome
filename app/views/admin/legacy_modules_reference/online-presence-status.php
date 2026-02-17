<?php
// Complete Online Presence Implementation Status
require_once __DIR__ . '/core/init.php';

// Set page title and metadata
$page_title = 'Online Presence Status - APS Dream Homes';
$page_description = 'Complete implementation status of APS Dream Homes online presence including website, SEO, social media, and digital marketing.';
$page_keywords = 'APS Dream Homes online presence, implementation status, digital marketing, SEO, website launch';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <meta name="keywords" content="<?php echo $page_keywords; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff20" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,96C1248,75,1344,53,1392,42.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        /* Status Overview Section */
        .status-overview {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .status-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .status-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .status-icon.complete {
            color: var(--success-color);
        }
        
        .status-icon.partial {
            color: var(--warning-color);
        }
        
        .status-icon.missing {
            color: var(--danger-color);
        }
        
        .status-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .status-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-badge.complete {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .status-badge.partial {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .status-badge.missing {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        /* Implementation Details Section */
        .implementation-section {
            padding: 100px 0;
            background: white;
        }
        
        .implementation-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .implementation-card:hover {
            transform: translateX(10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .implementation-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .implementation-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 20px;
        }
        
        .implementation-title {
            flex: 1;
        }
        
        .implementation-title h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .implementation-title p {
            color: #666;
            margin: 0;
        }
        
        .implementation-status {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .implementation-status.complete {
            background: #d4edda;
            color: var(--success-color);
        }
        
        .implementation-status.partial {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .implementation-status.missing {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        .implementation-details {
            margin-bottom: 25px;
        }
        
        .implementation-details h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #666;
        }
        
        .feature-list li i {
            color: var(--success-color);
            margin-right: 10px;
            font-size: 0.9rem;
        }
        
        .implementation-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-implementation {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-implementation:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-implementation-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-implementation-outline:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Next Steps Section */
        .next-steps-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .step-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .step-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            font-weight: 800;
            margin-bottom: 15px;
        }
        
        .step-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .step-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .step-urgency {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .step-urgency.immediate {
            background: #f8d7da;
            color: var(--danger-color);
        }
        
        .step-urgency.urgent {
            background: #fff3cd;
            color: var(--warning-color);
        }
        
        .step-urgency.important {
            background: #d1ecf1;
            color: var(--info-color);
        }
        
        /* Progress Bar */
        .progress-section {
            padding: 80px 0;
            background: white;
        }
        
        .progress-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .overall-progress {
            margin-bottom: 40px;
        }
        
        .progress-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 15px;
        }
        
        .progress-bar-container {
            background: #e9ecef;
            border-radius: 50px;
            height: 30px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-bar-fill {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            height: 100%;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            transition: width 2s ease;
        }
        
        .progress-details {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 60px;
            }
            
            .status-card {
                padding: 30px 20px;
            }
            
            .implementation-card {
                padding: 30px 20px;
            }
            
            .implementation-header {
                flex-direction: column;
                text-align: center;
            }
            
            .implementation-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .implementation-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include_once __DIR__ . '/includes/components/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center hero-content" data-aos="fade-up">
                    <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-rocket me-2"></i>Implementation Status
                    </div>
                    <h1 class="display-3 fw-bold mb-4">APS Dream Homes Online Presence</h1>
                    <p class="lead mb-4">
                        Complete overview of your digital presence implementation status. 
                        Track progress and identify next steps for maximum online visibility.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Overview Section -->
    <section class="status-overview">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Current Implementation Status</h2>
                <p class="lead text-muted">Quick overview of your online presence components</p>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="status-card">
                        <div class="status-icon complete">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="status-title">Website</h3>
                        <p class="status-description">
                            Complete professional website with all pages and features implemented
                        </p>
                        <div class="status-badge complete">
                            <i class="fas fa-check me-2"></i>100% Complete
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="status-card">
                        <div class="status-icon complete">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="status-title">SEO Foundation</h3>
                        <p class="status-description">
                            Sitemap, robots.txt, meta tags, and SEO optimization complete
                        </p>
                        <div class="status-badge complete">
                            <i class="fas fa-check me-2"></i>100% Ready
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="status-card">
                        <div class="status-icon partial">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <h3 class="status-title">Social Media</h3>
                        <p class="status-description">
                            Facebook page exists, needs enhancement and additional platforms
                        </p>
                        <div class="status-badge partial">
                            <i class="fas fa-exclamation me-2"></i>25% Complete
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="status-card">
                        <div class="status-icon missing">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="status-title">Reviews System</h3>
                        <p class="status-description">
                            Review pages created, need live deployment and customer collection
                        </p>
                        <div class="status-badge missing">
                            <i class="fas fa-times me-2"></i>Not Live
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Implementation Details Section -->
    <section class="implementation-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Detailed Implementation</h2>
                <p class="lead text-muted">Complete breakdown of what's been implemented and what's needed</p>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <!-- Website Implementation -->
                    <div class="implementation-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="implementation-header">
                            <div class="implementation-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="implementation-title">
                                <h3>Website Implementation</h3>
                                <p>Complete professional website with all features</p>
                            </div>
                            <div class="implementation-status complete">
                                <i class="fas fa-check me-1"></i>Complete
                            </div>
                        </div>
                        <div class="implementation-details">
                            <h4>✅ What's Implemented:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>18+ Professional pages (Homepage, Properties, Projects, About, Contact, etc.)</li>
                                <li><i class="fas fa-check"></i>Modern UI with Bootstrap 5 and AOS animations</li>
                                <li><i class="fas fa-check"></i>Company showcase with team presentation</li>
                                <li><i class="fas fa-check"></i>Property listings with search and filters</li>
                                <li><i class="fas fa-check"></i>Customer reviews system with rating functionality</li>
                                <li><i class="fas fa-check"></i>Careers page with job applications</li>
                                <li><i class="fas fa-check"></i>Blog and content marketing ready</li>
                                <li><i class="fas fa-check"></i>SEO optimized with proper meta tags</li>
                                <li><i class="fas fa-check"></i>Mobile responsive design</li>
                                <li><i class="fas fa-check"></i>Admin panel for content management</li>
                            </ul>
                        </div>
                        <div class="implementation-actions">
                            <a href="index_improved.php" class="btn-implementation">
                                <i class="fas fa-eye me-2"></i>View Website
                            </a>
                            <a href="#" class="btn-implementation btn-implementation-outline">
                                <i class="fas fa-rocket me-2"></i>Deploy to Live
                            </a>
                        </div>
                    </div>
                    
                    <!-- SEO Implementation -->
                    <div class="implementation-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="implementation-header">
                            <div class="implementation-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="implementation-title">
                                <h3>SEO & Search Optimization</h3>
                                <p>Complete SEO foundation for Google ranking</p>
                            </div>
                            <div class="implementation-status complete">
                                <i class="fas fa-check me-1"></i>Complete
                            </div>
                        </div>
                        <div class="implementation-details">
                            <h4>✅ What's Implemented:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>XML Sitemap (sitemap.xml.php) for Google indexing</li>
                                <li><i class="fas fa-check"></i>Robots.txt for search engine crawling rules</li>
                                <li><i class="fas fa-check"></i>Meta tags optimization for all pages</li>
                                <li><i class="fas fa-check"></i>Structured data markup ready</li>
                                <li><i class="fas fa-check"></i>SEO-friendly URL structure</li>
                                <li><i class="fas fa-check"></i>Page speed optimization</li>
                                <li><i class="fas fa-check"></i>Mobile-first responsive design</li>
                                <li><i class="fas fa-check"></i>Google Business Profile setup guide</li>
                            </ul>
                        </div>
                        <div class="implementation-actions">
                            <a href="sitemap.xml.php" class="btn-implementation">
                                <i class="fas fa-sitemap me-2"></i>View Sitemap
                            </a>
                            <a href="robots.txt" class="btn-implementation btn-implementation-outline">
                                <i class="fas fa-robot me-2"></i>View Robots.txt
                            </a>
                        </div>
                    </div>
                    
                    <!-- Social Media Implementation -->
                    <div class="implementation-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="implementation-header">
                            <div class="implementation-icon">
                                <i class="fas fa-share-alt"></i>
                            </div>
                            <div class="implementation-title">
                                <h3>Social Media Presence</h3>
                                <p>Basic social media setup, needs enhancement</p>
                            </div>
                            <div class="implementation-status partial">
                                <i class="fas fa-exclamation me-1"></i>Partial
                            </div>
                        </div>
                        <div class="implementation-details">
                            <h4>✅ What's Implemented:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Facebook page exists (AbhaySinghSuryawansi)</li>
                                <li><i class="fas fa-check"></i>Social media links in website footer</li>
                            </ul>
                            <h4>❌ What's Missing:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-times"></i>Professional Facebook page enhancement</li>
                                <li><i class="fas fa-times"></i>Instagram business account</li>
                                <li><i class="fas fa-times"></i>LinkedIn company page</li>
                                <li><i class="fas fa-times"></i>YouTube channel for property videos</li>
                                <li><i class="fas fa-times"></i>Content posting schedule</li>
                                <li><i class="fas fa-times"></i>Social media automation</li>
                            </ul>
                        </div>
                        <div class="implementation-actions">
                            <a href="https://www.facebook.com/AbhaySinghSuryawansi/" target="_blank" class="btn-implementation">
                                <i class="fab fa-facebook me-2"></i>View Facebook
                            </a>
                            <a href="#" class="btn-implementation btn-implementation-outline">
                                <i class="fas fa-plus me-2"></i>Enhance Social Media
                            </a>
                        </div>
                    </div>
                    
                    <!-- Reviews Implementation -->
                    <div class="implementation-card" data-aos="fade-up" data-aos-delay="400">
                        <div class="implementation-header">
                            <div class="implementation-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="implementation-title">
                                <h3>Customer Reviews System</h3>
                                <p>Complete review system, needs live deployment</p>
                            </div>
                            <div class="implementation-status missing">
                                <i class="fas fa-times me-1"></i>Not Live
                            </div>
                        </div>
                        <div class="implementation-details">
                            <h4>✅ What's Implemented:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-check"></i>Customer reviews page with rating system</li>
                                <li><i class="fas fa-check"></i>Review submission form with validation</li>
                                <li><i class="fas fa-check"></i>Review filtering and search functionality</li>
                                <li><i class="fas fa-check"></i>Sample customer testimonials</li>
                                <li><i class="fas fa-check"></i>Review statistics and analytics</li>
                            </ul>
                            <h4>❌ What's Missing:</h4>
                            <ul class="feature-list">
                                <li><i class="fas fa-times"></i>Live deployment to collect real reviews</li>
                                <li><i class="fas fa-times"></i>Google Business Profile reviews</li>
                                <li><i class="fas fa-times"></i>Facebook reviews integration</li>
                                <li><i class="fas fa-times"></i>Customer review collection campaign</li>
                            </ul>
                        </div>
                        <div class="implementation-actions">
                            <a href="customer-reviews.php" class="btn-implementation">
                                <i class="fas fa-star me-2"></i>View Reviews Page
                            </a>
                            <a href="#" class="btn-implementation btn-implementation-outline">
                                <i class="fas fa-rocket me-2"></i>Deploy Reviews System
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Progress Section -->
    <section class="progress-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Overall Progress</h2>
                <p class="lead text-muted">Track your complete online presence implementation</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="progress-card" data-aos="fade-up">
                        <div class="overall-progress">
                            <h3 class="progress-title">Complete Online Presence</h3>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" style="width: 0%">
                                    65% Complete
                                </div>
                            </div>
                            <div class="progress-details">
                                <span>6.5 out of 10 components implemented</span>
                                <span>65% Overall Progress</span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Website Development</span>
                                    <span style="color: var(--success-color); font-weight: 700;">100%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>SEO Foundation</span>
                                    <span style="color: var(--success-color); font-weight: 700;">100%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Google Business Profile</span>
                                    <span style="color: var(--warning-color); font-weight: 700;">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Social Media</span>
                                    <span style="color: var(--warning-color); font-weight: 700;">25%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 25%"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Customer Reviews</span>
                                    <span style="color: var(--danger-color); font-weight: 700;">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Analytics & Tracking</span>
                                    <span style="color: var(--danger-color); font-weight: 700;">0%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Steps Section -->
    <section class="next-steps-section">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-4 fw-bold mb-3">Critical Next Steps</h2>
                <p class="lead text-muted">Immediate actions to complete your online presence</p>
            </div>
            
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <div class="step-urgency immediate">IMMEDIATE</div>
                        <h3 class="step-title">Deploy Website to Live Domain</h3>
                        <p class="step-description">
                            Move your complete website from localhost to apsdreamhomes.com domain. 
                            This is the most critical step for establishing online presence.
                        </p>
                        <a href="#" class="btn-implementation">
                            <i class="fas fa-rocket me-2"></i>Deploy Now
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <div class="step-urgency immediate">IMMEDIATE</div>
                        <h3 class="step-title">Setup Google Business Profile</h3>
                        <p class="step-description">
                            Create and verify Google Business Profile to appear in local search 
                            results and Google Maps for Gorakhpur area.
                        </p>
                        <a href="google-business-profile.php" class="btn-implementation">
                            <i class="fab fa-google me-2"></i>Setup Profile
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <div class="step-urgency urgent">URGENT</div>
                        <h3 class="step-title">Submit to Google Search Console</h3>
                        <p class="step-description">
                            Submit your sitemap to Google Search Console for faster indexing 
                            and better search visibility.
                        </p>
                        <a href="#" class="btn-implementation">
                            <i class="fas fa-search me-2"></i>Submit Sitemap
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <div class="step-urgency urgent">URGENT</div>
                        <h3 class="step-title">Setup Google Analytics</h3>
                        <p class="step-description">
                            Install Google Analytics to track website traffic, user behavior, 
                            and conversion metrics.
                        </p>
                        <a href="#" class="btn-implementation">
                            <i class="fas fa-chart-bar me-2"></i>Setup Analytics
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="step-card">
                        <div class="step-number">5</div>
                        <div class="step-urgency important">IMPORTANT</div>
                        <h3 class="step-title">Generate Customer Reviews</h3>
                        <p class="step-description">
                            Launch review collection campaign to gather customer testimonials 
                            and build social proof.
                        </p>
                        <a href="customer-reviews.php" class="btn-implementation">
                            <i class="fas fa-star me-2"></i>Collect Reviews
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="step-card">
                        <div class="step-number">6</div>
                        <div class="step-urgency important">IMPORTANT</div>
                        <h3 class="step-title">Enhance Social Media</h3>
                        <p class="step-description">
                            Create Instagram and LinkedIn profiles, enhance Facebook page 
                            with professional content.
                        </p>
                        <a href="#" class="btn-implementation">
                            <i class="fas fa-share-alt me-2"></i>Enhance Social
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include_once __DIR__ . '/includes/components/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Animate progress bars on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const progressObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBars = entry.target.querySelectorAll('.progress-bar-fill');
                    progressBars.forEach(bar => {
                        const targetWidth = bar.style.width;
                        bar.style.width = '0%';
                        setTimeout(() => {
                            bar.style.width = targetWidth;
                        }, 200);
                    });
                    progressObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe progress card
        const progressCard = document.querySelector('.progress-card');
        if (progressCard) {
            progressObserver.observe(progressCard);
        }

        // Action button handlers
        document.querySelectorAll('.btn-implementation').forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.href === '#' || this.href.endsWith('#')) {
                    e.preventDefault();
                    
                    const actionTitle = this.closest('.implementation-card, .step-card').querySelector('h3, .step-title').textContent;
                    
                    // Show appropriate action based on context
                    if (actionTitle.includes('Deploy')) {
                        alert('Website Deployment:\n\nTo deploy your website to live domain:\n1. Purchase domain: apsdreamhomes.com\n2. Setup hosting account\n3. Upload files to server\n4. Configure database\n5. Test all functionality\n\nThis will make your website live and accessible to customers!');
                    } else if (actionTitle.includes('Google')) {
                        alert('Google Business Profile Setup:\n\nFollow our complete setup guide at google-business-profile.php\n\nThis will help you appear in local search results and Google Maps!');
                    } else if (actionTitle.includes('Analytics')) {
                        alert('Google Analytics Setup:\n\n1. Create Google Analytics account\n2. Add property for apsdreamhomes.com\n3. Install tracking code on website\n4. Set up conversion goals\n5. Create custom dashboards\n\nThis will help you track website performance!');
                    } else {
                        alert(`Action: ${actionTitle}\n\nThis feature is ready for implementation. Follow the detailed guide to complete this step.`);
                    }
                }
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.href === '#' || this.href.endsWith('#')) {
                    e.preventDefault();
                } else {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Update progress dynamically
        function updateOverallProgress() {
            const completedComponents = document.querySelectorAll('.implementation-status.complete').length;
            const totalComponents = document.querySelectorAll('.implementation-status').length;
            const progressPercentage = Math.round((completedComponents / totalComponents) * 100);
            
            console.log(`Overall Progress: ${progressPercentage}% (${completedComponents}/${totalComponents} components)`);
        }

        // Initialize progress
        updateOverallProgress();

        // Add hover effects to cards
        document.querySelectorAll('.implementation-card, .step-card, .status-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>

