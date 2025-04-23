<?php
// Start session and include necessary files
session_start();
// Use modern dynamic header/footer for all user-facing pages
require_once(__DIR__ . '/includes/templates/dynamic_header.php');
include("config.php");
include(__DIR__ . '/includes/updated-config-paths.php');
include(__DIR__ . '/includes/common-functions.php');

// Set page specific variables
$page_title = "About Us - APS Dream Homes";
$meta_description = "Learn about APS Dream Homes, a trusted real estate company in Uttar Pradesh offering premium residential and commercial properties.";

// Additional CSS for this page (optional)
$additional_css = '<style>
    /* About Page Specific Styles */
    .about-company {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    
    .about-image {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .about-image img {
        width: 100%;
        height: auto;
    }
    
    .company-info {
        padding: 20px;
    }
    
    .company-info h2 {
        margin-bottom: 20px;
        color: var(--primary-color);
    }
    
    .company-info p {
        margin-bottom: 15px;
        line-height: 1.8;
    }
    
    .team-section {
        padding: 80px 0;
        background-color: #fff;
    }
    
    .team-card {
        text-align: center;
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }
    
    .team-card:hover {
        transform: translateY(-10px);
    }
    
    .team-image {
        width: 200px;
        height: 200px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .team-info h4 {
        margin-bottom: 5px;
        color: var(--primary-color);
    }
    
    .team-info p {
        color: #777;
        margin-bottom: 15px;
    }
    
    .social-icons a {
        display: inline-block;
        width: 35px;
        height: 35px;
        background-color: var(--primary-color);
        color: #fff;
        border-radius: 50%;
        line-height: 35px;
        margin: 0 5px;
        transition: all 0.3s ease;
    }
    
    .social-icons a:hover {
        background-color: var(--secondary-color);
        transform: translateY(-3px);
    }
    
    .mission-vision {
        padding: 80px 0;
        background-color: #f8f9fa;
    }
    
    .mission-card, .vision-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 30px;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .mission-card:hover, .vision-card:hover {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .mission-card h3, .vision-card h3 {
        margin-bottom: 20px;
        color: var(--primary-color);
        position: relative;
        padding-bottom: 15px;
    }
    
    .mission-card h3:after, .vision-card h3:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--primary-color);
    }
    
    @media (max-width: 767px) {
        .about-image {
            margin-bottom: 30px;
        }
    }
</style>';

?>

<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url("banner/about-banner.jpg", "images"); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">About Us</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li>About Us</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- About Company Section -->
<section class="about-company">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="<?php echo get_asset_url('about/company.jpg', 'images'); ?>" alt="APS Dream Homes Office" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="company-info">
                    <h2>Welcome to APS Dream Homes</h2>
                    <p>APS Dream Homes Private Limited is a prestigious real estate company registered under the Companies Act, 2013, launched on 26 April 2022. We are committed to providing exceptional real estate services across Uttar Pradesh, with a focus on Gorakhpur, Lucknow, and Varanasi regions.</p>
                    <p>Our company provides comprehensive services in buying, selling, construction, maintenance, development, advertising, and marketing various real estate projects. With years of experience in the real estate industry, we have established ourselves as a trusted name in the market.</p>
                    <p>At APS Dream Homes, we strive to create an environment of trust and faith between our sales associates and customers. We believe in transparency, integrity, and customer satisfaction, which has helped us build long-lasting relationships with our clients.</p>
                    <p>We take pride in playing a vital role in shaping the land of our great nation through our core values of quality and customer satisfaction. Our team of experienced professionals is dedicated to helping you find your dream property or investment opportunity.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission and Vision Section -->
<section class="mission-vision">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">Our Mission & Vision</h2>
                <p class="lead">Guided by our core values and commitment to excellence</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="mission-card">
                    <h3>Our Mission</h3>
                    <p>Our mission is to provide exceptional real estate services that exceed our clients' expectations. We are committed to:</p>
                    <ul>
                        <li>Offering premium quality properties at competitive prices</li>
                        <li>Maintaining transparency in all our dealings</li>
                        <li>Providing personalized service to meet individual needs</li>
                        <li>Creating sustainable and environmentally friendly developments</li>
                        <li>Contributing to the economic growth of the regions we serve</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="vision-card">
                    <h3>Our Vision</h3>
                    <p>Our vision is to become the leading real estate developer in Uttar Pradesh, recognized for:</p>
                    <ul>
                        <li>Innovation in design and construction</li>
                        <li>Creating communities that enhance quality of life</li>
                        <li>Setting new standards in customer service</li>
                        <li>Sustainable development practices</li>
                        <li>Building a legacy of trust and excellence in the real estate industry</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center mb-5">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="lead">The dedicated professionals behind APS Dream Homes</p>
            </div>
        </div>
        <div class="row">
            <!-- Team Member 1 -->
            <div class="col-md-4">
                <div class="team-card">
                    <div class="team-image">
                        <img src="<?php echo get_asset_url('team/director.jpg', 'images'); ?>" alt="Abhay Singh Suryawansi">
                    </div>
                    <div class="team-info">
                        <h4>Abhay Singh Suryawansi</h4>
                        <p>Director & Founder</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Team Member 2 -->
            <div class="col-md-4">
                <div class="team-card">
                    <div class="team-image">
                        <img src="<?php echo get_asset_url('team/manager.jpg', 'images'); ?>" alt="Rajesh Kumar">
                    </div>
                    <div class="team-info">
                        <h4>Rajesh Kumar</h4>
                        <p>Sales Manager</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Team Member 3 -->
            <div class="col-md-4">
                <div class="team-card">
                    <div class="team-image">
                        <img src="<?php echo get_asset_url('team/consultant.jpg', 'images'); ?>" alt="Priya Singh">
                    </div>
                    <div class="team-info">
                        <h4>Priya Singh</h4>
                        <p>Property Consultant</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Additional JS for this page
$additional_js = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Any page-specific JavaScript can go here
        console.log("About page loaded successfully!");
    });
</script>';

// Include the updated common footer
require_once(__DIR__ . '/includes/templates/new_footer.php'); ?>
</body>
</html>