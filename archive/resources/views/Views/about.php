<?php
// Start session and include necessary files
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database settings and other necessary files
require_once(__DIR__ . '/includes/db_settings.php');
require_once(__DIR__ . '/includes/templates/header.php');
require_once(__DIR__ . '/includes/functions/common-functions.php');

// Get database connection
$conn = get_db_connection();

// Set page specific variables
$page_title = "About Us - APS Dream Homes";
$meta_description = "Learn about APS Dream Homes, a trusted real estate company in Uttar Pradesh offering premium residential and commercial properties.";

// Additional CSS for this page (optional)
$additional_css = ''; // Moved to style.css

// Fetch about page content from database
$about_content = [];
if ($conn) {
    $sql = "SELECT * FROM site_settings WHERE setting_name IN ('about_content', 'mission_content', 'vision_content', 'team_members')";
    try {
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $about_content[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    } catch (Exception $e) {
        error_log('About page DB error: ' . $e->getMessage());
    }
}

// Default content if database fetch fails
$about_text = json_decode($about_content['about_content'] ?? '', true) ?: [
    'Welcome to APS Dream Homes',
    'APS Dream Homes Private Limited is a prestigious real estate company registered under the Companies Act, 2013, launched on 26 April 2022. We are committed to providing exceptional real estate services across Uttar Pradesh, with a focus on Gorakhpur, Lucknow, and Varanasi regions.',
    'Our company provides comprehensive services in buying, selling, construction, maintenance, development, advertising, and marketing various real estate projects. With years of experience in the real estate industry, we have established ourselves as a trusted name in the market.',
    'At APS Dream Homes, we strive to create an environment of trust and faith between our sales associates and customers. We believe in transparency, integrity, and customer satisfaction, which has helped us build long-lasting relationships with our clients.',
    'We take pride in playing a vital role in shaping the land of our great nation through our core values of quality and customer satisfaction. Our team of experienced professionals is dedicated to helping you find your dream property or investment opportunity.'
];

$mission_items = json_decode($about_content['mission_content'] ?? '', true) ?: [
    'Offering premium quality properties at competitive prices',
    'Maintaining transparency in all our dealings',
    'Providing personalized service to meet individual needs',
    'Creating sustainable and environmentally friendly developments',
    'Contributing to the economic growth of the regions we serve'
];

$vision_items = json_decode($about_content['vision_content'] ?? '', true) ?: [
    'Innovation in design and construction',
    'Creating communities that enhance quality of life',
    'Setting new standards in customer service',
    'Sustainable development practices',
    'Building a legacy of trust and excellence in the real estate industry'
];

$team_members = json_decode($about_content['team_members'] ?? '', true) ?: [
    [
        'name' => 'Abhay Singh Suryawansi',
        'position' => 'Director & Founder',
        'image' => 'director.jpg',
        'social' => [
            'facebook' => '#',
            'twitter' => '#',
            'linkedin' => '#'
        ]
    ],
    [
        'name' => 'Rajesh Kumar',
        'position' => 'Sales Manager',
        'image' => 'manager.jpg',
        'social' => [
            'facebook' => '#',
            'twitter' => '#',
            'linkedin' => '#'
        ]
    ],
    [
        'name' => 'Priya Singh',
        'position' => 'Property Consultant',
        'image' => 'consultant.jpg',
        'social' => [
            'facebook' => '#',
            'twitter' => '#',
            'linkedin' => '#'
        ]
    ]
];
?>

<!-- Page Banner Section -->
<div class="page-banner" style="background-image: url('<?php echo get_asset_url('banner/about-banner.jpg', 'images'); ?>')">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-title">About Us</h1>
                <ul class="breadcrumb">
                    <li><a href="<?php echo $base_url; ?>">Home</a></li>
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
                    <h2><?php echo htmlspecialchars($about_text[0]); ?></h2>
                    <?php foreach (array_slice($about_text, 1) as $paragraph): ?>
                    <p><?php echo htmlspecialchars($paragraph); ?></p>
                    <?php endforeach; ?>
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
                        <?php foreach ($mission_items as $item): ?>
                        <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="vision-card">
                    <h3>Our Vision</h3>
                    <p>Our vision is to become the leading real estate developer in Uttar Pradesh, recognized for:</p>
                    <ul>
                        <?php foreach ($vision_items as $item): ?>
                        <li><?php echo htmlspecialchars($item); ?></li>
                        <?php endforeach; ?>
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
            <?php foreach ($team_members as $member): ?>
            <div class="col-md-4">
                <div class="team-card">
                    <div class="team-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/team/<?php echo htmlspecialchars($member['image']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                    </div>
                    <div class="team-info">
                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                        <p><?php echo htmlspecialchars($member['position']); ?></p>
                        <div class="social-icons">
                            <a href="<?php echo htmlspecialchars($member['social']['facebook']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="<?php echo htmlspecialchars($member['social']['twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="<?php echo htmlspecialchars($member['social']['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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

// Include the footer
require_once(__DIR__ . '/includes/templates/footer.php');
?>