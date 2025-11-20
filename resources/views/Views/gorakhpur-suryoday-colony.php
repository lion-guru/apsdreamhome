<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("config.php");
include_once __DIR__ . '/includes/db_config.php';

// Fetch Suryoday Colony project info from DB
$conn = getMysqliConnection();
$project_id = 1; // Set the correct project_id for Suryoday Colony
$project = null;
$amenities = [];
$gallery = [];
$brochure = '';
$youtube_url = '';
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    if ($project) {
        $youtube_url = $project['youtube_url'] ?? '';
        $brochure = $project['brochure_path'] ?? '';
    }
    $stmt->close();
    // Amenities
    $stmt = $conn->prepare("SELECT * FROM project_amenities WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $amenities[] = $row;
    }
    $stmt->close();
    // Gallery
    $stmt = $conn->prepare("SELECT * FROM project_gallery WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $gallery[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Suryoday Colony - APS Dream Homes</title>
    <meta name="description" content="Suryoday Colony, Gorakhpur - Integrated township by APS Dream Homes. Explore plots, row houses, amenities, and more.">
    <link rel="shortcut icon" href="<?php echo get_asset_url('favicon.ico', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/owl.carousel.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <style>
        body { background: #f8f9fa; }
        .hero-section {
            background: url('<?php echo get_asset_url('site_photo/gorakhpur/suryoday/suryoday.png', 'images'); ?>') center/cover no-repeat;
            min-height: 400px;
            display: flex;
            align-items: center;
            color: #fff;
            position: relative;
        }
        .hero-overlay {
            background: rgba(30,60,114,0.6);
            position: absolute;
            top:0;left:0;right:0;bottom:0;
            z-index:1;
        }
        .hero-content {
            position: relative;
            z-index:2;
            text-align: center;
        }
        .site-logo {
            max-width: 180px;
            margin: 30px auto 10px auto;
            display: block;
        }
        .modern-section {
            padding: 40px 0;
        }
        .about-img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.12);
        }
        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
        }
        .amenity-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(30,60,114,0.08);
            padding: 20px 28px;
            text-align: center;
            min-width: 160px;
            transition: box-shadow 0.2s;
        }
        .amenity-card img {
            width: 48px; height: 48px; margin-bottom: 10px;
        }
        .amenity-card:hover {
            box-shadow: 0 4px 24px rgba(30,60,114,0.18);
        }
        .gallery-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(30,60,114,0.10);
        }
        .video-section iframe {
            width: 100%;
            height: 360px;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(30,60,114,0.12);
        }
    </style>
</head>
<body>
    <img src="<?php echo get_asset_url('logo/aps1.png', 'images'); ?>" alt="APS Dream Homes Logo" class="site-logo">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="display-4 fw-bold mb-3">Suryoday Colony</h1>
            <p class="lead">Integrated Township at Prempur Maniram, Gorakhpur<br>Plots, Row Houses, Commercial Spaces &amp; More</p>
            <a href="#site-map" class="btn btn-light btn-lg mt-3 shadow">View Site Map</a>
        </div>
    </section>
    <!-- About Section -->
    <section class="modern-section bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="<?php echo get_asset_url('site_photo/gorakhpur/suryoday/suryoday1.png', 'images'); ?>" alt="Suryoday Colony Layout" class="about-img">
                </div>
                <div class="col-lg-6">
                    <h2 class="mb-3 text-primary">About Suryoday Colony</h2>
                    <p><b>Suryoday Colony</b> is an integrated township located at a prime location in Gorakhpur, spread across more than 15 acres and developed in blocks. It offers Plots, Row Houses, and Commercial Spaces along with amenities like an Entrance Gate, Electricity, Drainage System, Shopping Centre, and Park.</p>
                    <ul>
                        <li>Located beside Jungle Kaudiya to Kalesar Four Lane</li>
                        <li>Close to Engineering &amp; Dental Colleges, Railway Station, Airport, Petrol Pumps, Banks, ATMs, City Mall</li>
                        <li>Modern infrastructure and green surroundings</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- Site Map Section -->
    <section class="modern-section" id="site-map">
        <div class="container">
            <h2 class="mb-4 text-center text-primary">Site Map Layout</h2>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <img src="<?php echo get_asset_url('site_photo/gorakhpur/suryoday/suryoday2.png', 'images'); ?>" alt="Suryoday Colony Site Map" class="about-img">
                </div>
            </div>
        </div>
    </section>
    <!-- Amenities Section -->
    <section class="modern-section bg-light">
        <div class="container">
            <h2 class="mb-4 text-center text-primary">Top Amenities</h2>
            <div class="amenities-list">
                <?php foreach ($amenities as $amenity): ?>
                    <div class="amenity-card">
                        <img src="<?php echo htmlspecialchars($amenity['icon_path']); ?>" alt="Amenity">
                        <div><?php echo htmlspecialchars($amenity['label']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Gallery Section -->
    <section class="modern-section">
        <div class="container">
            <h2 class="mb-4 text-center text-primary">Gallery</h2>
            <div class="row g-3 justify-content-center">
                <?php foreach ($gallery as $img): ?>
                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Gallery Image" class="gallery-img">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Brochure Section -->
    <?php if ($brochure): ?>
    <section class="modern-section bg-light">
        <div class="container text-center">
            <h2 class="mb-4 text-primary">Download Brochure</h2>
            <a href="<?php echo htmlspecialchars($brochure); ?>" class="btn btn-outline-primary btn-lg" target="_blank"><i class="fa fa-file-pdf"></i> Download PDF</a>
        </div>
    </section>
    <?php endif; ?>
    <!-- Video Section -->
    <section class="modern-section video-section bg-light">
        <div class="container">
            <h2 class="mb-4 text-center text-primary">Project Video</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="ratio ratio-16x9">
                        <?php if ($youtube_url): ?>
                            <iframe src="<?php echo htmlspecialchars($youtube_url); ?>" title="Suryoday Colony Video" allowfullscreen></iframe>
                        <?php else: ?>
                            <div class="alert alert-info">Video coming soon.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/owl.carousel.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/custom.js', 'js'); ?>"></script>
</body>
</html>
