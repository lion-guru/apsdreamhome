<?php

/**
 * Enhanced About Page - APS Dream Home
 * Modern UI/UX for company information and team details
 */

// Set page title and description for layout
$page_title = $title ?? 'About Us - APS Dream Home';
$page_description = 'Learn about APS Dream Home - your trusted real estate partner since 2009. Discover our story, values, and commitment to excellence.';
?>

<?php include __DIR__ . '/../layouts/modern_header.php'; ?>

<!-- Enhanced Hero Section -->
<section class="hero-about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content animate-fade-up">
                    <h1 class="hero-title">
                        About
                        <span class="text-warning">APS Dream Home</span>
                    </h1>
                    <p class="hero-subtitle">
                        Your trusted partner in real estate solutions since 2009. We bring dreams to reality
                        with our commitment to excellence, integrity, and customer satisfaction.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-about-cta btn-contact">
                            <i class="fas fa-phone me-2"></i>Get In Touch
                        </a>
                        <a href="<?php echo BASE_URL; ?>properties" class="btn btn-about-cta btn-properties">
                            <i class="fas fa-home me-2"></i>View Properties
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 animate-slide-right">
                <div class="hero-image position-relative">
                    <img src="<?php echo ASSET_URL ?? '/assets/'; ?>images/about-hero.jpg"
                        alt="APS Dream Home Team"
                        class="img-fluid rounded shadow"
                        onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=APS+Dream+Home+Team'">
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i>15+ Years Experience
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Story Section -->
<section class="story-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3 animate-fade-up">
                    <i class="fas fa-history text-primary me-2"></i>Our Story
                </h2>
                <p class="lead text-muted animate-fade-up" style="animation-delay: 0.1s;">
                    From humble beginnings to becoming Uttar Pradesh's most trusted real estate partner
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="story-card animate-fade-up">
                    <div class="story-icon">
                        <i class="fas fa-foundation"></i>
                    </div>
                    <h4 class="story-title">Foundation (2009)</h4>
                    <p class="story-text">
                        Started with a vision to transform the real estate landscape in Uttar Pradesh.
                        Our founders recognized the need for trustworthy, customer-centric property solutions.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="story-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="story-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="story-title">Growth Phase</h4>
                    <p class="story-text">
                        Expanded our operations across multiple cities in UP, building a reputation
                        for quality construction and transparent dealings. Reached 1000+ satisfied customers.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="story-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="story-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h4 class="story-title">Leadership Today</h4>
                    <p class="story-text">
                        Now recognized as UP's leading real estate company with innovative technology
                        integration, sustainable practices, and a vast network of satisfied customers.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Values Section -->
<section class="values-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3 animate-fade-up">
                    Our
                    <span class="text-primary">Core Values</span>
                </h2>
                <p class="lead text-muted animate-fade-up" style="animation-delay: 0.1s;">
                    The principles that guide every decision we make and every relationship we build
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="value-title">Trust & Integrity</h5>
                    <p class="value-text">
                        We build lasting relationships through honest dealings, transparent processes,
                        and unwavering commitment to our promises.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="value-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="value-title">Excellence</h5>
                    <p class="value-text">
                        We strive for perfection in every project, from planning to execution,
                        ensuring the highest standards of quality and craftsmanship.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="value-title">Customer First</h5>
                    <p class="value-text">
                        Every decision we make prioritizes our customers' needs and dreams.
                        Your satisfaction is our ultimate success metric.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="value-title">Sustainability</h5>
                    <p class="value-text">
                        We are committed to environmentally responsible development practices
                        that benefit both our customers and the planet.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up" style="animation-delay: 0.4s;">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h5 class="value-title">Innovation</h5>
                    <p class="value-text">
                        We embrace cutting-edge technology and modern construction methods
                        to deliver superior value to our customers.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card animate-fade-up" style="animation-delay: 0.5s;">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5 class="value-title">Community</h5>
                    <p class="value-text">
                        We actively contribute to the communities we serve, supporting local
                        development and creating positive social impact.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Statistics -->
<section class="stats-about">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-about-card animate-fade-up">
                    <div class="stat-about-number" data-target="15">0</div>
                    <div class="stat-about-label">Years of Excellence</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-about-card animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="stat-about-number" data-target="10000">0</div>
                    <div class="stat-about-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-about-card animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="stat-about-number" data-target="500">0</div>
                    <div class="stat-about-label">Projects Completed</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-about-card animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="stat-about-number" data-target="25">0</div>
                    <div class="stat-about-label">Awards Won</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Timeline -->
<section class="timeline-section py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3 animate-fade-up">
                    Our
                    <span class="text-primary">Journey</span>
                </h2>
                <p class="lead text-muted animate-fade-up" style="animation-delay: 0.1s;">
                    Key milestones that shaped our growth and success
                </p>
            </div>
        </div>

        <div class="timeline">
            <div class="timeline-item animate-fade-up">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2009</div>
                    <h5 class="timeline-title">Company Founded</h5>
                    <p class="timeline-text">
                        APS Dream Home was established with a vision to revolutionize real estate in Uttar Pradesh.
                        Started operations in Gorakhpur with focus on quality and customer satisfaction.
                    </p>
                </div>
            </div>

            <div class="timeline-item animate-fade-up" style="animation-delay: 0.1s;">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2012</div>
                    <h5 class="timeline-title">First Major Project</h5>
                    <p class="timeline-text">
                        Launched our first residential colony in Gorakhpur, setting new standards for
                        quality construction and community living.
                    </p>
                </div>
            </div>

            <div class="timeline-item animate-fade-up" style="animation-delay: 0.2s;">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2015</div>
                    <h5 class="timeline-title">Expansion to Multiple Cities</h5>
                    <p class="timeline-text">
                        Extended operations to Lucknow and Varanasi, establishing APS Dream Home as
                        a regional leader in real estate development.
                    </p>
                </div>
            </div>

            <div class="timeline-item animate-fade-up" style="animation-delay: 0.3s;">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2018</div>
                    <h5 class="timeline-title">Technology Integration</h5>
                    <p class="timeline-text">
                        Pioneered the use of modern technology in real estate, including AI-powered
                        property recommendations and virtual property tours.
                    </p>
                </div>
            </div>

            <div class="timeline-item animate-fade-up" style="animation-delay: 0.4s;">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2022</div>
                    <h5 class="timeline-title">MLM Network Launch</h5>
                    <p class="timeline-text">
                        Introduced innovative Multi-Level Marketing distribution model, creating
                        entrepreneurial opportunities for thousands of associates.
                    </p>
                </div>
            </div>

            <div class="timeline-item animate-fade-up" style="animation-delay: 0.5s;">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-year">2025</div>
                    <h5 class="timeline-title">Future Vision</h5>
                    <p class="timeline-text">
                        Continuing our journey towards becoming India's most trusted and technologically
                        advanced real estate company with pan-India presence.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Leadership Team Section -->
<section class="team-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3 animate-fade-up">
                    Meet Our
                    <span class="text-primary">Leadership Team</span>
                </h2>
                <p class="lead text-muted animate-fade-up" style="animation-delay: 0.1s;">
                    Experienced professionals driving innovation and excellence in real estate
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up">
                    <div class="team-avatar">AS</div>
                    <h5 class="team-name">Abhay Singh</h5>
                    <div class="team-role">Founder & CEO</div>
                    <p class="team-description">
                        Visionary leader with 15+ years of experience in real estate development.
                        Passionate about creating exceptional living spaces and building lasting relationships.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up" style="animation-delay: 0.1s;">
                    <div class="team-avatar">RS</div>
                    <h5 class="team-name">Rajesh Sharma</h5>
                    <div class="team-role">Chief Technology Officer</div>
                    <p class="team-description">
                        Technology innovator driving digital transformation in real estate.
                        Expert in AI, blockchain, and emerging technologies for property solutions.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up" style="animation-delay: 0.2s;">
                    <div class="team-avatar">PS</div>
                    <h5 class="team-name">Priya Singh</h5>
                    <div class="team-role">Head of Operations</div>
                    <p class="team-description">
                        Operations specialist ensuring seamless project execution and delivery.
                        Focuses on quality control and customer satisfaction across all projects.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up" style="animation-delay: 0.3s;">
                    <div class="team-avatar">VK</div>
                    <h5 class="team-name">Vikash Kumar</h5>
                    <div class="team-role">Head of MLM Operations</div>
                    <p class="team-description">
                        MLM strategy expert building and managing our extensive associate network.
                        Ensures fair commission distribution and associate success.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up" style="animation-delay: 0.4s;">
                    <div class="team-avatar">AG</div>
                    <h5 class="team-name">Anita Gupta</h5>
                    <div class="team-role">Head of Sustainability</div>
                    <p class="team-description">
                        Environmental specialist leading our green initiatives and sustainable development practices.
                        Committed to creating eco-friendly living spaces.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-member animate-fade-up" style="animation-delay: 0.5s;">
                    <div class="team-avatar">MS</div>
                    <h5 class="team-name">Manoj Srivastava</h5>
                    <div class="team-role">Head of Finance</div>
                    <p class="team-description">
                        Financial strategist ensuring sustainable growth and profitability.
                        Manages investments, partnerships, and financial planning for expansion.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 cta-about-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">
                    Ready to Start Your Real Estate Journey?
                </h2>
                <p class="lead opacity-90 mb-4">
                    Join thousands of satisfied customers who found their dream properties with APS Dream Home.
                    Let's turn your property dreams into reality!
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-light btn-lg px-5 py-3">
                        <i class="fas fa-search me-2"></i>Browse Properties
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-light btn-lg px-5 py-3">
                        <i class="fas fa-phone-alt me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>associate" class="btn btn-warning btn-lg px-5 py-3">
                        <i class="fas fa-users me-2"></i>Join Our Network
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
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
    document.querySelectorAll('.stat-about-number').forEach(stat => {
        counterObserver.observe(stat);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
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

    // Team member card interactions
    document.querySelectorAll('.team-member').forEach(member => {
        member.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        member.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Intersection Observer for timeline animations
    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    // Observe timeline items
    document.querySelectorAll('.timeline-item').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        timelineObserver.observe(item);
    });
</script>