<?php
$page_title = "Properties - APS Dream Homes";
$meta_description = "Browse our exclusive collection of premium properties across Uttar Pradesh. Find your dream home with APS Dream Homes.";
$additional_css = '<link href="/apsdreamhomefinal/assets/css/properties.css" rel="stylesheet">';

require_once __DIR__ . '/includes/db_settings.php';
require_once __DIR__ . '/includes/templates/dynamic_header.php';

// Get database connection
$conn = get_db_connection();

// Default properties in case database fetch fails
$default_properties = [
    [
        'title' => 'Luxury Villa in Gorakhpur',
        'location' => 'Gorakhpur, Uttar Pradesh',
        'price' => '₹1.5 Cr',
        'bedrooms' => 4,
        'bathrooms' => 3,
        'area' => '3000 sq ft',
        'image' => '/apsdreamhomefinal/assets/images/properties/villa1.jpg',
        'type' => 'Villa',
        'status' => 'For Sale'
    ],
    [
        'title' => 'Modern Apartment',
        'location' => 'Kunraghat, Gorakhpur',
        'price' => '₹45 Lac',
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area' => '1500 sq ft',
        'image' => '/apsdreamhomefinal/assets/images/properties/apartment1.jpg',
        'type' => 'Apartment',
        'status' => 'For Sale'
    ]
];

// Initialize properties array
$properties = [];

// Try to fetch properties from database
if ($conn) {
    $sql = "SELECT p.*, pt.name as property_type, pr.project_name, pr.description as project_description,
                  CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                  (SELECT GROUP_CONCAT(amenity_name) FROM project_amenities pa WHERE pa.project_id = pr.id) as amenities,
                  (SELECT image_url FROM project_gallery pg WHERE pg.project_id = pr.id ORDER BY pg.display_order ASC LIMIT 1) as project_image
            FROM properties p 
            LEFT JOIN property_types pt ON p.type_id = pt.id 
            LEFT JOIN projects pr ON p.project_id = pr.id 
            LEFT JOIN users u ON p.owner_id = u.id
            WHERE p.status = 'available' 
            ORDER BY p.created_at DESC";
    try {
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
            }
            $result->free();
        } else {
            $properties = $default_properties;
        }
    } catch (Exception $e) {
        error_log('Properties fetch error: ' . $e->getMessage());
        $properties = $default_properties;
    }
} else {
    $properties = $default_properties;
}
?>

<!-- Properties Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 mb-4">Discover Your Dream Home</h1>
                <p class="lead mb-4">Browse through our exclusive collection of premium properties across Uttar Pradesh</p>
                <div class="search-box">
                    <form action="/apsdreamhomefinal/api/search_properties.php" method="GET" class="d-flex justify-content-center">
                        <input type="text" name="q" class="form-control form-control-lg me-2" placeholder="Search by location, property type, or features..." aria-label="Search properties">
                        <button type="submit" class="btn btn-primary btn-lg">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Properties Filter Section -->
<section class="filter-section py-4 bg-white border-bottom">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="filters d-flex flex-wrap gap-2 justify-content-center">
                    <button class="btn btn-outline-primary active" data-filter="all">All Properties</button>
                    <button class="btn btn-outline-primary" data-filter="villa">Villas</button>
                    <button class="btn btn-outline-primary" data-filter="apartment">Apartments</button>
                    <button class="btn btn-outline-primary" data-filter="plot">Plots</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Properties Listing Section -->
<section class="properties-section py-5">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($properties as $property): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="property-card card h-100">
                        <img src="<?php echo htmlspecialchars($property['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>"
                             loading="lazy">
                        <div class="card-body">
                            <div class="property-tag mb-2">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($property['type']); ?></span>
                                <span class="badge bg-success"><?php echo htmlspecialchars($property['status']); ?></span>
                            </div>
                            <h3 class="card-title h5"><?php echo htmlspecialchars($property['title']); ?></h3>
                            <p class="location mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?php echo htmlspecialchars($property['location']); ?>
                            </p>
                            <div class="property-features d-flex justify-content-between mb-3">
                                <span><i class="fas fa-bed me-2"></i><?php echo htmlspecialchars($property['bedrooms']); ?> Beds</span>
                                <span><i class="fas fa-bath me-2"></i><?php echo htmlspecialchars($property['bathrooms']); ?> Baths</span>
                                <span><i class="fas fa-ruler-combined me-2"></i><?php echo htmlspecialchars($property['area']); ?></span>
                            </div>
                            <div class="price-box d-flex justify-content-between align-items-center">
                                <span class="price h5 mb-0 text-primary"><?php echo htmlspecialchars($property['price']); ?></span>
                                <a href="/apsdreamhomefinal/property-details.php?id=<?php echo htmlspecialchars($property['id'] ?? '1'); ?>" 
                                   class="btn btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">Can't Find What You're Looking For?</h2>
                <p class="mb-4">Contact us and let our experts help you find the perfect property that matches your requirements.</p>
                <a href="/apsdreamhomefinal/contact.php" class="btn btn-primary btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Properties CSS -->
<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/apsdreamhomefinal/assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 100px 0;
    }
    
    .search-box {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .property-card {
        transition: transform 0.3s ease;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
    }
    
    .property-card:hover {
        transform: translateY(-5px);
    }
    
    .property-card .card-img-top {
        height: 200px;
        object-fit: cover;
    }
    
    .property-features span {
        font-size: 0.9rem;
        color: #666;
    }
    
    .price {
        font-weight: bold;
    }
    
    @media (max-width: 768px) {
        .property-features {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .property-card {
            transition: none;
        }
    }
</style>

<!-- Properties JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterButtons = document.querySelectorAll('[data-filter]');
    const propertyCards = document.querySelectorAll('.property-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter properties
            propertyCards.forEach(card => {
                const type = card.querySelector('.badge').textContent.toLowerCase();
                if (filter === 'all' || type === filter) {
                    card.closest('.col-md-6').style.display = '';
                } else {
                    card.closest('.col-md-6').style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php
require_once __DIR__ . '/includes/templates/dynamic_footer.php';
?>
