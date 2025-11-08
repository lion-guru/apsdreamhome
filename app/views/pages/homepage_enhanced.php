<?php
/**
 * Enhanced Modern Homepage - APS Dream Home
 * Complete UI/UX overhaul with modern design patterns
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home - Your Trusted Real Estate Partner'; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Find your dream home with APS Dream Home. Premium properties in Gorakhpur, Lucknow & UP. Expert real estate services with modern technology.">
    <meta name="keywords" content="real estate Gorakhpur, property for sale, buy house, apartments Lucknow, real estate UP, dream home">
    <meta name="author" content="APS Dream Home">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    <meta property="og:title" content="APS Dream Home - Premium Real Estate in UP">
    <meta property="og:description" content="Discover exclusive properties with the most trusted real estate platform in Uttar Pradesh.">
    <meta property="og:image" content="<?php echo BASE_URL; ?>assets/images/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo BASE_URL; ?>">
    <meta property="twitter:title" content="APS Dream Home - Premium Real Estate">
    <meta property="twitter:description" content="Find your dream home with APS Dream Home - Premium properties in UP">
    <meta property="twitter:image" content="<?php echo BASE_URL; ?>assets/images/twitter-card.jpg">

    <!-- Modern CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://unpkg.com/swiper@10/swiper-bundle.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --purple-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background: #f8f9fa;
            scroll-behavior: smooth;
        }

        /* Modern Hero Section */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: var(--primary-gradient);
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" style="stop-color:%23ffffff;stop-opacity:0.1"/><stop offset="100%" style="stop-color:%23ffffff;stop-opacity:0"/></radialGradient></defs><circle cx="500" cy="500" r="400" fill="url(%23a)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        /* Modern Buttons */
        .btn-modern {
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
        }

        .btn-primary-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(79, 172, 254, 0.4);
        }

        .btn-outline-modern {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-outline-modern:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }

        /* Glass Morphism Search Card */
        .search-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .search-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 25px;
        }

        .search-card > * {
            position: relative;
            z-index: 2;
        }

        /* Modern Form Controls */
        .form-control-modern {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-control-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .form-select-modern {
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .form-select-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        /* Property Cards */
        .property-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .property-image {
            height: 250px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
            position: relative;
            overflow: hidden;
        }

        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .property-card:hover .property-image img {
            transform: scale(1.05);
        }

        .property-overlay {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .property-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .property-content {
            padding: 1.5rem;
        }

        .property-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .property-location {
            color: #667eea;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .property-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .property-feature {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .property-feature-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            display: block;
        }

        .property-feature-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .property-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: #28a745;
            text-align: center;
        }

        /* Statistics Section */
        .stats-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 50px 50px;
            animation: move 30s linear infinite;
        }

        @keyframes move {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-50px) translateY(-50px); }
        }

        .stat-card {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Testimonials */
        .testimonial-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            margin-bottom: 2rem;
        }

        .testimonial-quote {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .testimonial-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .testimonial-info h6 {
            margin-bottom: 0;
            color: #2c3e50;
        }

        .testimonial-info small {
            color: #6c757d;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
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

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-slide-right {
            animation: slideInRight 0.8s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 80vh;
                text-align: center;
            }

            .search-card {
                margin-top: 2rem;
                padding: 1.5rem;
            }

            .property-features {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .cta-title {
                font-size: 2rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5a6fd8;
        }

        /* Loading Animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 30%;
            left: 10%;
            animation-delay: 4s;
        }
    </style>
</head>
<body>

<?php
// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}
?>

<?php include __DIR__ . '/../layouts/header_unified.php'; ?>

<!-- Modern Hero Section -->
<section class="hero-section">
    <!-- Floating Elements -->
    <div class="floating-element">
        <i class="fas fa-home text-white opacity-25" style="font-size: 2rem;"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-building text-white opacity-25" style="font-size: 1.5rem;"></i>
    </div>
    <div class="floating-element">
        <i class="fas fa-map-marker-alt text-white opacity-25" style="font-size: 1.8rem;"></i>
    </div>

    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content animate-fade-up">
                    <!-- Trust Badge -->
                    <div class="mb-4">
                        <span class="badge bg-white bg-opacity-20 text-white px-4 py-2 rounded-pill">
                            <i class="fas fa-shield-alt me-2"></i>Trusted by 10,000+ Families
                        </span>
                    </div>

                    <!-- Main Title -->
                    <h1 class="hero-title">
                        Find Your
                        <span class="text-warning">Dream Home</span>
                        in Uttar Pradesh
                    </h1>

                    <!-- Subtitle -->
                    <p class="hero-subtitle">
                        Discover premium properties with APS Dream Home - your trusted partner in real estate.
                        From luxury apartments to commercial spaces, we make your property dreams come true.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="#featured-properties" class="btn btn-modern btn-primary-modern">
                            <i class="fas fa-search me-2"></i>Explore Properties
                        </a>
                        <a href="#contact" class="btn btn-modern btn-outline-modern">
                            <i class="fas fa-headset me-2"></i>Get Expert Help
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="d-flex align-items-center flex-wrap gap-4">
                        <div class="d-flex align-items-center">
                            <div class="d-flex me-2">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" class="rounded-circle" width="30" height="30" alt="Client">
                                <img src="https://randomuser.me/api/portraits/men/44.jpg" class="rounded-circle ms-1" width="30" height="30" alt="Client">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" class="rounded-circle ms-1" width="30" height="30" alt="Client">
                            </div>
                            <div>
                                <div class="text-white fw-bold">5,000+</div>
                                <small class="text-white-75">Happy Clients</small>
                            </div>
                        </div>

                        <div class="vr opacity-50"></div>

                        <div class="d-flex align-items-center">
                            <div class="text-warning me-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold">4.8/5</div>
                                <small class="text-white-75">Client Rating</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Search Card -->
            <div class="col-lg-6">
                <div class="search-card animate-slide-right">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">
                            <i class="fas fa-magic text-primary me-2"></i>
                            Smart Property Search
                        </h3>
                        <p class="text-muted">Find your perfect property in seconds</p>
                    </div>

                    <form action="<?php echo BASE_URL; ?>properties" method="GET" class="modern-search-form">
                        <div class="row g-3">
                            <!-- Property Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-home me-2 text-primary"></i>Property Type
                                </label>
                                <select class="form-select form-select-modern" name="type">
                                    <option value="">All Types</option>
                                    <option value="apartment">🏢 Apartment</option>
                                    <option value="villa">🏘️ Villa</option>
                                    <option value="house">🏠 Independent House</option>
                                    <option value="plot">🏗️ Plot/Land</option>
                                    <option value="commercial">🏢 Commercial</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Location
                                </label>
                                <select class="form-select form-select-modern" name="location">
                                    <option value="">All Locations</option>
                                    <?php if (!empty($locations)): ?>
                                        <?php foreach ($locations as $state => $cities): ?>
                                            <optgroup label="<?php echo htmlspecialchars($state); ?>">
                                                <?php foreach ($cities as $city): ?>
                                                    <option value="<?php echo htmlspecialchars($city['city']); ?>">
                                                        <?php echo htmlspecialchars($city['city']); ?> (<?php echo $city['count']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Budget Range -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-rupee-sign me-2 text-primary"></i>Budget Range
                                </label>
                                <select class="form-select form-select-modern" name="budget">
                                    <option value="">Any Budget</option>
                                    <option value="0-3000000">Under ₹30 Lakh</option>
                                    <option value="3000000-5000000">₹30-50 Lakh</option>
                                    <option value="5000000-10000000">₹50 Lakh - ₹1 Cr</option>
                                    <option value="10000000-20000000">₹1-2 Cr</option>
                                    <option value="20000000+">Above ₹2 Cr</option>
                                </select>
                            </div>

                            <!-- Bedrooms -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-bed me-2 text-primary"></i>Bedrooms
                                </label>
                                <select class="form-select form-select-modern" name="bedrooms">
                                    <option value="">Any</option>
                                    <option value="1">1 BHK</option>
                                    <option value="2">2 BHK</option>
                                    <option value="3">3 BHK</option>
                                    <option value="4">4+ BHK</option>
                                </select>
                            </div>

                            <!-- Search Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-modern w-100 py-3">
                                    <i class="fas fa-search me-2"></i>
                                    Search Properties
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Quick Filters -->
                    <div class="mt-4 text-center">
                        <small class="text-muted">Popular Searches:</small>
                        <div class="mt-2">
                            <a href="<?php echo BASE_URL; ?>properties?type=apartment&location=gorakhpur" class="badge bg-light text-dark me-2 px-3 py-2">
                                Apartments in Gorakhpur
                            </a>
                            <a href="<?php echo BASE_URL; ?>properties?budget=5000000-10000000" class="badge bg-light text-dark me-2 px-3 py-2">
                                ₹50L - ₹1Cr Properties
                            </a>
                            <a href="<?php echo BASE_URL; ?>properties?featured=1" class="badge bg-light text-dark px-3 py-2">
                                Featured Properties
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Statistics Section -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card animate-fade-up">
                    <div class="stat-number" data-target="<?php echo $company_stats['properties_listed'] ?? 0; ?>">0</div>
                    <div class="stat-label">Properties Listed</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="stat-number" data-target="<?php echo $company_stats['happy_customers'] ?? 0; ?>">0</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="stat-number" data-target="<?php echo $company_stats['years_experience'] ?? 0; ?>">0</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="stat-number" data-target="<?php echo $company_stats['cities_covered'] ?? 0; ?>">0</div>
                    <div class="stat-label">Cities Covered</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties Section -->
<section id="featured-properties" class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">
                    <i class="fas fa-star text-warning me-2"></i>
                    Featured Properties
                </h2>
                <p class="lead text-muted">
                    Discover our handpicked selection of premium properties across Uttar Pradesh
                </p>
            </div>
        </div>

        <?php if (empty($featured_properties)): ?>
            <div class="text-center py-5">
                <div class="loading-shimmer" style="height: 300px; border-radius: 20px; margin-bottom: 2rem;"></div>
                <h4 class="text-muted">Loading Premium Properties...</h4>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach (array_slice($featured_properties, 0, 6) as $property): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="property-card animate-fade-up">
                            <div class="property-image">
                                <?php if (!empty($property['main_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($property['main_image']); ?>"
                                         alt="<?php echo htmlspecialchars($property['title']); ?>">
                                <?php endif; ?>

                                <div class="property-overlay">
                                    <?php if ($property['featured']): ?>
                                        <span class="property-badge">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($property['status'] === 'sold'): ?>
                                        <span class="property-badge" style="background: #dc3545;">
                                            <i class="fas fa-check-circle me-1"></i>Sold
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="property-content">
                                <h5 class="property-title">
                                    <?php echo htmlspecialchars(substr($property['title'], 0, 50)); ?>
                                    <?php if (strlen($property['title']) > 50): ?>...<?php endif; ?>
                                </h5>

                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($property['address'] ?? $property['city'] ?? 'Gorakhpur'); ?>
                                </div>

                                <div class="property-features">
                                    <?php if (!empty($property['bedrooms'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bedrooms']; ?></span>
                                            <span class="property-feature-label">Beds</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property['bathrooms'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo $property['bathrooms']; ?></span>
                                            <span class="property-feature-label">Baths</span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($property['area_sqft'])): ?>
                                        <div class="property-feature">
                                            <span class="property-feature-value"><?php echo number_format($property['area_sqft']); ?></span>
                                            <span class="property-feature-label">Sqft</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="property-price">
                                    ₹<?php echo number_format($property['price']); ?>
                                </div>

                                <div class="mt-3">
                                    <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>"
                                       class="btn btn-outline-primary w-100">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- View All Button -->
            <div class="text-center mt-5">
                <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary-modern btn-lg px-5 py-3">
                    <i class="fas fa-th-large me-2"></i>View All Properties
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">
                    Why Choose
                    <span class="text-primary">APS Dream Home?</span>
                </h2>
                <p class="lead text-muted">
                    We're not just another real estate company - we're your trusted partner in finding the perfect home
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Trusted & Secure</h4>
                    <p class="text-muted">
                        With over <?php echo $company_stats['years_experience'] ?? 15; ?> years of experience and 10,000+ satisfied customers,
                        we ensure every transaction is secure and transparent.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="mb-4">
                        <i class="fas fa-home text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Premium Properties</h4>
                    <p class="text-muted">
                        Curated collection of premium properties across Uttar Pradesh.
                        From luxury apartments to commercial spaces, we have it all.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="mb-4">
                        <i class="fas fa-headset text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Expert Support</h4>
                    <p class="text-muted">
                        Our dedicated team of real estate experts provides personalized guidance
                        throughout your property buying journey.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="mb-4">
                        <i class="fas fa-mobile-alt text-info" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Modern Technology</h4>
                    <p class="text-muted">
                        Advanced AI-powered property matching, virtual tours, and seamless
                        digital experience for modern property buyers.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up" style="animation-delay: 0.4s;">
                    <div class="mb-4">
                        <i class="fas fa-handshake text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Best Deals</h4>
                    <p class="text-muted">
                        Exclusive deals, competitive pricing, and flexible payment options
                        to make your dream home more affordable.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="text-center animate-fade-up" style="animation-delay: 0.5s;">
                    <div class="mb-4">
                        <i class="fas fa-clock text-secondary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mb-3">Quick Process</h4>
                    <p class="text-muted">
                        Streamlined buying process with minimal paperwork and fast approvals.
                        Get your property in record time.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">
                    What Our
                    <span class="text-primary">Customers Say</span>
                </h2>
                <p class="lead text-muted">
                    Don't just take our word for it - hear from our satisfied customers
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card animate-fade-up">
                    <div class="mb-3">
                        <i class="fas fa-quote-left text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                    </div>
                    <p class="testimonial-quote">
                        "APS Dream Home made finding our dream home incredibly easy. Their team was professional,
                        knowledgeable, and guided us through every step. Highly recommended!"
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">SP</div>
                        <div class="testimonial-info">
                            <h6>Sarah Patel</h6>
                            <small>Happy Homeowner, Gorakhpur</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="mb-3">
                        <i class="fas fa-quote-left text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                    </div>
                    <p class="testimonial-quote">
                        "The property I purchased through APS Dream Home has exceeded all my expectations.
                        The quality and location are perfect for my family."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">RK</div>
                        <div class="testimonial-info">
                            <h6>Rajesh Kumar</h6>
                            <small>Property Investor, Lucknow</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="testimonial-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="mb-3">
                        <i class="fas fa-quote-left text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                    </div>
                    <p class="testimonial-quote">
                        "Outstanding service! The team helped us find the perfect commercial space for our business.
                        The entire process was smooth and transparent."
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">AM</div>
                        <div class="testimonial-info">
                            <h6>Anita Mehta</h6>
                            <small>Business Owner, Varanasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="cta-title">
                    Ready to Find Your Dream Home?
                </h2>
                <p class="cta-subtitle">
                    Join thousands of satisfied customers who found their perfect property with APS Dream Home.
                    Start your journey today!
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="#featured-properties" class="btn btn-light btn-lg px-5 py-3">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-phone-alt me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>associate" class="btn btn-warning btn-lg px-5 py-3">
                        <i class="fas fa-users me-2"></i>Join as Associate
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer_unified.php'; ?>

<!-- Modern JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>

<script>
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Counter Animation
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);

        function updateCounter() {
            start += increment;
            if (start < target) {
                element.textContent = Math.floor(start).toLocaleString();
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        updateCounter();
    }

    // Animate counters when they come into view
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.getAttribute('data-target'));
                animateCounter(entry.target, target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all stat numbers
    document.querySelectorAll('.stat-number').forEach(stat => {
        counterObserver.observe(stat);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if (window.scrollY > 100) {
            header?.classList.add('scrolled');
        } else {
            header?.classList.remove('scrolled');
        }
    });

    // Loading animation for property cards
    function showLoadingCards() {
        const propertyGrid = document.querySelector('.row.g-4');
        if (propertyGrid && propertyGrid.children.length === 0) {
            for (let i = 0; i < 6; i++) {
                const loadingCard = document.createElement('div');
                loadingCard.className = 'col-lg-4 col-md-6';
                loadingCard.innerHTML = `
                    <div class="property-card">
                        <div class="property-image loading-shimmer"></div>
                        <div class="property-content">
                            <div class="loading-shimmer" style="height: 20px; margin-bottom: 10px;"></div>
                            <div class="loading-shimmer" style="height: 16px; margin-bottom: 15px;"></div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <div class="loading-shimmer" style="height: 20px; margin-bottom: 5px;"></div>
                                    <div class="loading-shimmer" style="height: 12px;"></div>
                                </div>
                                <div class="property-feature">
                                    <div class="loading-shimmer" style="height: 20px; margin-bottom: 5px;"></div>
                                    <div class="loading-shimmer" style="height: 12px;"></div>
                                </div>
                                <div class="property-feature">
                                    <div class="loading-shimmer" style="height: 20px; margin-bottom: 5px;"></div>
                                    <div class="loading-shimmer" style="height: 12px;"></div>
                                </div>
                            </div>
                            <div class="loading-shimmer" style="height: 25px; margin-bottom: 15px;"></div>
                            <div class="loading-shimmer" style="height: 40px; border-radius: 8px;"></div>
                        </div>
                    </div>
                `;
                propertyGrid.appendChild(loadingCard);
            }
        }
    }

    // Show loading cards initially
    document.addEventListener('DOMContentLoaded', function() {
        showLoadingCards();

        // Remove loading cards after 2 seconds (simulate loading)
        setTimeout(() => {
            document.querySelectorAll('.loading-shimmer').forEach(el => {
                el.style.display = 'none';
            });
        }, 2000);
    });

    // Property card hover effects
    document.querySelectorAll('.property-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Search form enhancement
    const searchForm = document.querySelector('.modern-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
            submitBtn.disabled = true;

            // Re-enable after 3 seconds (in case of slow response)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 3000);
        });
    }
</script>

</body>
</html>
