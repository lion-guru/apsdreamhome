<?php
// Get property ID from URL
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

// Include database connection
require_once __DIR__ . '/../includes/db_connection.php';

// Check if property exists
if ($pid <= 0) {
    header('Location: ../property-listings.php');
    exit;
}

// Get property details using PDO
try {
    $query = "SELECT p.*, pt.type as ptype_name, c.cname as cityname, u.name as aname, u.phone as aphone, u.email as aemail, u.profile_image as aimage
              FROM property p
              LEFT JOIN property_type pt ON p.type = pt.id
              LEFT JOIN city c ON p.city = c.cid
              LEFT JOIN users u ON p.user_id = u.id
              WHERE p.pid = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$pid]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        header('Location: ../property-listings.php');
        exit;
    }

    // Get similar properties using PDO
    $similar_query = "SELECT p.*, pt.type as ptype_name, c.cname as cityname
                     FROM property p
                     LEFT JOIN property_type pt ON p.ptype = pt.id
                     LEFT JOIN city c ON p.city = c.cid
                     WHERE p.status = ? AND p.ptype = ? AND p.id != ?
                     ORDER BY p.id DESC LIMIT 3";

    $similar_stmt = $pdo->prepare($similar_query);
    $similar_stmt->execute([$property['status'], $property['ptype'], $pid]);
    $similar_result = $similar_stmt;

} catch (PDOException $e) {
    error_log('Property detail query error: ' . $e->getMessage());
    header('Location: ../property-listings.php');
    exit;
}

// Set page title and meta tags
$page_title = $property['title'] . ' | APS Real Estate';
$meta_description = substr(strip_tags($property['description']), 0, 160);
$meta_keywords = 'property, real estate, ' . $property['ptype_name'] . ', ' . $property['cityname'];

// Custom CSS for this page
$additional_css = '<link rel="stylesheet" href="' . get_asset_url('css/swiper-bundle.min.css', 'vendor') . '">
<link rel="stylesheet" href="' . get_asset_url('css/lightgallery.min.css', 'vendor') . '">
<style>
    /* Property Gallery Styles */
    .property-gallery {
        position: relative;
        margin-bottom: 2rem;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .property-gallery-main {
        height: 400px;
        margin-bottom: 10px;
    }
    
    .property-gallery-main .swiper-slide {
        height: 100%;
        overflow: hidden;
    }
    
    .property-gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .property-gallery-thumbs {
        height: 80px;
    }
    
    .property-gallery-thumbs .swiper-slide {
        height: 100%;
        opacity: 0.5;
        cursor: pointer;
        overflow: hidden;
        border-radius: 4px;
    }
    
    .property-gallery-thumbs .swiper-slide-thumb-active {
        opacity: 1;
    }
    
    .property-gallery-thumbs img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .property-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background-color: var(--primary-color);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
        z-index: 10;
    }
    
    .property-price {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 1.2rem;
        z-index: 10;
    }
    
    /* Property Details Styles */
    .property-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .property-section-title {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #eee;
        font-size: 1.25rem;
    }
    
    .property-features {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    
    .property-feature-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .property-feature-item i {
        color: var(--primary-color);
        font-size: 1.2rem;
    }
    
    .property-details-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .property-details-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #eee;
    }
    
    .property-details-label {
        font-weight: 500;
        color: #555;
    }
    
    .property-details-value {
        font-weight: 600;
    }
    
    /* Agent Card Styles */
    .agent-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.5rem;
    }
    
    .agent-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 3px solid var(--primary-color);
    }
    
    .agent-name {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .agent-contact {
        margin-top: 1rem;
        width: 100%;
    }
    
    .agent-contact-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .agent-contact-item i {
        color: var(--primary-color);
    }
    
    /* Contact Form Styles */
    .contact-form-section {
        margin-top: 1.5rem;
    }
    
    /* Map Styles */
    #property-map {
        height: 300px;
        border-radius: 8px;
        margin-top: 1rem;
    }
    
    /* Similar Properties Styles */
    .similar-properties {
        margin-top: 3rem;
    }
    
    .similar-properties-title {
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    /* Floor Plan Styles */
    .floor-plan-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    
    /* Responsive Styles */
    @media (max-width: 992px) {
        .property-gallery-main {
            height: 350px;
        }
        
        .property-details-list {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .property-gallery-main {
            height: 300px;
        }
        
        .property-features {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 576px) {
        .property-gallery-main {
            height: 250px;
        }
        
        .property-features {
            grid-template-columns: repeat(1, 1fr);
        }
    }
</style>';

// Additional JS for this page
$additional_js = '<script src="' . get_asset_url('js/swiper-bundle.min.js', 'vendor') . '"></script>
<script src="' . get_asset_url('js/lightgallery.min.js', 'vendor') . '"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize thumbnail slider
        var galleryThumbs = new Swiper(".property-gallery-thumbs", {
            spaceBetween: 10,
            slidesPerView: 5,
            freeMode: true,
            watchSlidesVisibility: true,
            watchSlidesProgress: true,
            breakpoints: {
                576: {
                    slidesPerView: 3,
                    spaceBetween: 8
                },
                768: {
                    slidesPerView: 4,
                    spaceBetween: 10
                },
                992: {
                    slidesPerView: 5,
                    spaceBetween: 10
                }
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev"
            }
        });
        
        // Initialize main slider
        var galleryMain = new Swiper(".property-gallery-main", {
            spaceBetween: 10,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev"
            },
            thumbs: {
                swiper: galleryThumbs
            }
        });
        
        // Initialize lightbox gallery
        lightGallery(document.getElementById("property-gallery"), {
            selector: ".gallery-item",
            download: false,
            counter: false
        });
        
        // Initialize Google Map
        function initMap() {
            const propertyLocation = { lat: <?php echo ($property["latitude"] ?? 26.7606); ?>, lng: <?php echo ($property["longitude"] ?? 83.3732); ?> };
            const map = new google.maps.Map(document.getElementById("property-map"), {
                zoom: 15,
                center: propertyLocation
            });
            const marker = new google.maps.Marker({
                position: propertyLocation,
                map: map,
                title: "<?php echo addslashes($property["title"]); ?>"
            });
        }
        
        // Load Google Maps API using our utility
        // Google Maps integration (temporarily disabled)
        // require_once __DIR__ . \'/..\'/utils/google_maps.php\';
        // echo GoogleMapsUtil::getGoogleMapsScriptTag(\'initMap\');

        // If Google Maps is already loaded, initialize immediately
        if (typeof google !== "undefined" && typeof google.maps !== "undefined") {
            initMap();
        }
    });
</script>';

// Include header
require_once __DIR__ . '/../includes/templates/header.php';

?>

<!-- Hero Section -->
<section class="hero-section" style="height: 40vh; min-height: 300px;">
    <div class="slide-bg" style="background-image: url('<?php echo !empty($property['pimage']) ? 'admin/property/' . $property['pimage'] : get_asset_url('property-banner.jpg', 'images'); ?>'); filter: brightness(0.65);"></div>
    <div class="slide-content">
        <h1 class="slide-title"><?php echo htmlspecialchars($property['title']); ?></h1>
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="property-listings.php">Properties</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Property Detail Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Property Main Content -->
            <div class="col-lg-8">
                <!-- Property Gallery -->
                <div class="property-gallery" id="property-gallery">
                    <div class="property-badge"><?php echo htmlspecialchars(ucfirst($property['status'])); ?> for <?php echo htmlspecialchars(ucfirst($property['stype'])); ?></div>
                    <div class="property-price">₹<?php echo number_format($property['price']); ?></div>
                    
                    <!-- Main Gallery Slider -->
                    <div class="swiper-container property-gallery-main">
                        <div class="swiper-wrapper">
                            <?php
                            // Display property images
                            $image_fields = ['pimage', 'pimage1', 'pimage2', 'pimage3', 'pimage4'];
                            foreach ($image_fields as $img) {
                                if (!empty($property[$img])) {
                                    echo '<div class="swiper-slide">';
                                    echo '<a href="admin/property/' . $property[$img] . '" class="gallery-item">';
                                    echo '<img src="admin/property/' . $property[$img] . '" alt="Property Image">';
                                    echo '</a>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                    
                    <!-- Thumbnail Slider -->
                    <div class="swiper-container property-gallery-thumbs">
                        <div class="swiper-wrapper">
                            <?php
                            // Display property thumbnails
                            foreach ($image_fields as $img) {
                                if (!empty($property[$img])) {
                                    echo '<div class="swiper-slide">';
                                    echo '<img src="admin/property/' . $property[$img] . '" alt="Property Thumbnail">';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Property Description -->
                <div class="property-section">
                    <h3 class="property-section-title">Description</h3>
                    <div class="property-description">
                        <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                    </div>
                </div>

                <!-- Property Amenities -->
                <div class="property-section">
                    <h3 class="property-section-title">Amenities</h3>
                    <div class="property-features">
                        <?php
                        $amenities = [
                            'balcony' => ['icon' => 'fas fa-door-open', 'label' => 'Balcony'],
                            'parking' => ['icon' => 'fas fa-car', 'label' => 'Parking'],
                            'garden' => ['icon' => 'fas fa-tree', 'label' => 'Garden'],
                            'security' => ['icon' => 'fas fa-shield-alt', 'label' => 'Security'],
                            'lift' => ['icon' => 'fas fa-elevator', 'label' => 'Lift'],
                            'power_backup' => ['icon' => 'fas fa-bolt', 'label' => 'Power Backup'],
                            'gym' => ['icon' => 'fas fa-dumbbell', 'label' => 'Gym'],
                            'swimming_pool' => ['icon' => 'fas fa-swimming-pool', 'label' => 'Swimming Pool'],
                            'club_house' => ['icon' => 'fas fa-building', 'label' => 'Club House'],
                            'children_play_area' => ['icon' => 'fas fa-child', 'label' => 'Children Play Area']
                        ];

                        foreach ($amenities as $key => $amenity) {
                            if (isset($property[$key]) && $property[$key] == 1) {
                                echo '<div class="property-feature-item">';
                                echo '<i class="' . $amenity['icon'] . '"></i>';
                                echo '<span>' . $amenity['label'] . '</span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <!-- Floor Plans -->
                <?php if (!empty($property['floor_plan'])) : ?>
                <div class="property-section">
                    <h3 class="property-section-title">Floor Plans</h3>
                    <div class="floor-plan-content">
                        <img src="admin/property/<?php echo $property['floor_plan']; ?>" alt="Floor Plan">
                    </div>
                </div>
                <?php endif; ?>

                <!-- Property Details -->
                <div class="property-section">
                    <h3 class="property-section-title">Property Details</h3>
                    <div class="property-details-list">
                        <div class="property-details-item">
                            <span class="property-details-label">Property ID</span>
                            <span class="property-details-value"><?php echo $property['id']; ?></span>
                        </div>
                        <div class="property-details-item">
                            <span class="property-details-label">Property Type</span>
                            <span class="property-details-value"><?php echo htmlspecialchars($property['ptype_name']); ?></span>
                        </div>
                        <div class="property-details-item">
                            <span class="property-details-label">Status</span>
                            <span class="property-details-value"><?php echo htmlspecialchars(ucfirst($property['status'])); ?></span>
                        </div>
                        <div class="property-details-item">
                            <span class="property-details-label">Location</span>
                            <span class="property-details-value"><?php echo htmlspecialchars($property['cityname']); ?></span>
                        </div>
                        <?php if (!empty($property['bhk'])) : ?>
                        <div class="property-details-item">
                            <span class="property-details-label">BHK</span>
                            <span class="property-details-value"><?php echo $property['bhk']; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['bedroom'])) : ?>
                        <div class="property-details-item">
                            <span class="property-details-label">Bedrooms</span>
                            <span class="property-details-value"><?php echo $property['bedroom']; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['bathroom'])) : ?>
                        <div class="property-details-item">
                            <span class="property-details-label">Bathrooms</span>
                            <span class="property-details-value"><?php echo $property['bathroom']; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['size'])) : ?>
                        <div class="property-details-item">
                            <span class="property-details-label">Area</span>
                            <span class="property-details-value"><?php echo $property['size']; ?> <?php echo $property['unit'] ?? 'sq ft'; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($property['year'])) : ?>
                        <div class="property-details-item">
                            <span class="property-details-label">Year Built</span>
                            <span class="property-details-value"><?php echo $property['year']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Location Map -->
                <div class="property-section">
                    <h3 class="property-section-title">Location</h3>
                    <p><?php echo htmlspecialchars($property['address']); ?></p>
                    <div id="property-map"></div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Agent Information -->
                <div class="property-section">
                    <h3 class="property-section-title">Contact Agent</h3>
                    <div class="agent-card">
                        <img src="<?php echo !empty($property['aimage']) ? 'admin/user/' . $property['aimage'] : get_asset_url('user-default.png', 'images'); ?>" alt="Agent" class="agent-image">
                        <h4 class="agent-name"><?php echo htmlspecialchars($property['aname'] ?? 'APS Real Estate'); ?></h4>
                        <div class="agent-contact">
                            <?php if (!empty($property['aphone'])) : ?>
                            <div class="agent-contact-item">
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($property['aphone']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($property['aemail'])) : ?>
                            <div class="agent-contact-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($property['aemail']); ?></span>
                            </div>
                            <?php endif; ?>
                            <a href="contact.php?agent=<?php echo $property['agent_id']; ?>" class="btn btn-primary btn-block mt-3">Contact Agent</a>
                        </div>
                    </div>
                    
                    <!-- Contact Form -->
                    <div class="contact-form-section">
                        <form action="property-inquiry.php" method="post" class="contact-form">
                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                            <input type="hidden" name="property_title" value="<?php echo htmlspecialchars($property['title']); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="tel" name="phone" class="form-control" placeholder="Your Phone" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <select name="inquiry_type" class="form-control" required>
                                        <option value="">Select Inquiry Type</option>
                                        <option value="viewing">Schedule Viewing</option>
                                        <option value="question">Ask a Question</option>
                                        <option value="offer">Make an Offer</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Send Inquiry</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Share Property -->
                <div class="property-section">
                    <h3 class="property-section-title">Share Property</h3>
                    <div class="d-flex justify-content-between">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentUrl()); ?>" target="_blank" class="btn btn-outline-primary"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(getCurrentUrl()); ?>&text=<?php echo urlencode($property['title']); ?>" target="_blank" class="btn btn-outline-info"><i class="fab fa-twitter"></i> Twitter</a>
                        <a href="https://wa.me/?text=<?php echo urlencode($property['title'] . ' - ' . getCurrentUrl()); ?>" target="_blank" class="btn btn-outline-success"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Similar Properties -->
        <?php if ($similar_result->rowCount() > 0) : ?>
        <div class="similar-properties">
            <h2 class="similar-properties-title">Similar Properties</h2>
            <div class="row">
                <?php while ($similar = $similar_result->fetch(PDO::FETCH_ASSOC)) : ?>
                <div class="col-md-4 mb-4">
                    <div class="property-card">
                        <div class="property-card-image">
                            <a href="property-detail.php?pid=<?php echo $similar['id']; ?>">
                                <img src="admin/property/<?php echo $similar['pimage']; ?>" alt="<?php echo htmlspecialchars($similar['title']); ?>">
                            </a>
                            <div class="property-card-badge"><?php echo htmlspecialchars(ucfirst($similar['status'])); ?></div>
                            <div class="property-card-price">₹<?php echo number_format($similar['price']); ?></div>
                        </div>
                        <div class="property-card-content">
                            <h3 class="property-card-title">
                                <a href="property-detail.php?pid=<?php echo $similar['id']; ?>"><?php echo htmlspecialchars($similar['title']); ?></a>
                            </h3>
                            <p class="property-card-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($similar['cityname']); ?></p>
                            <div class="property-card-features">
                                <?php if (!empty($similar['bedroom'])) : ?>
                                <span><i class="fas fa-bed"></i> <?php echo $similar['bedroom']; ?> Bed</span>
                                <?php endif; ?>
                                <?php if (!empty($similar['bathroom'])) : ?>
                                <span><i class="fas fa-bath"></i> <?php echo $similar['bathroom']; ?> Bath</span>
                                <?php endif; ?>
                                <?php if (!empty($similar['size'])) : ?>
                                <span><i class="fas fa-vector-square"></i> <?php echo $similar['size']; ?> <?php echo $similar['unit'] ?? 'sq ft'; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Helper function to get current URL
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Include footer
require_once __DIR__ . '/../includes/templates/footer.php';
?>
