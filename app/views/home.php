
<?php
// Get featured properties
$featured_properties_query = "SELECT * FROM properties WHERE featured = 1 LIMIT 6";
$featured_properties = $conn->query($featured_properties_query);

// Get latest properties
$latest_properties_query = "SELECT * FROM properties ORDER BY created_at DESC LIMIT 6";
$latest_properties = $conn->query($latest_properties_query);
?>

<?php include '../app/views/includes/header.php'; ?>

<section class="hero-section">
    <div class="hero-content">
        <h1>Find Your Dream Property</h1>
        <p>Discover the perfect property that matches your lifestyle and dreams</p>
        <form class="search-form" action="/properties" method="GET">
            <div class="search-group">
                <input type="text" name="location" placeholder="Location">
                <select name="type">
                    <option value="">Property Type</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="land">Land</option>
                </select>
                <input type="number" name="min_price" placeholder="Min Price">
                <input type="number" name="max_price" placeholder="Max Price">
                <button type="submit" class="search-btn">Search</button>
            </div>
        </form>
    </div>
</section>

<section class="featured-properties">
    <h2>Featured Properties</h2>
    <div class="property-grid">
        <?php if ($featured_properties && $featured_properties->num_rows > 0): ?>
            <?php while ($property = $featured_properties->fetch_assoc()): ?>
                <div class="property-card">
                    <div class="property-image">
                        <img src="/uploads/properties/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                        <?php if ($property['featured']): ?>
                            <span class="featured-badge">Featured</span>
                        <?php endif; ?>
                    </div>
                    <div class="property-details">
                        <h3><?php echo $property['title']; ?></h3>
                        <p class="location"><?php echo $property['location']; ?></p>
                        <p class="price"><?php echo Helpers::formatCurrency($property['price']); ?></p>
                        <div class="property-features">
                            <span><?php echo $property['bedrooms']; ?> Beds</span>
                            <span><?php echo $property['bathrooms']; ?> Baths</span>
                            <span><?php echo $property['area']; ?> sq.ft</span>
                        </div>
                        <a href="/property/<?php echo $property['id']; ?>" class="view-details">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No featured properties available.</p>
        <?php endif; ?>
    </div>
</section>

<section class="latest-properties">
    <h2>Latest Properties</h2>
    <div class="property-grid">
        <?php if ($latest_properties && $latest_properties->num_rows > 0): ?>
            <?php while ($property = $latest_properties->fetch_assoc()): ?>
                <div class="property-card">
                    <div class="property-image">
                        <img src="/uploads/properties/<?php echo $property['image']; ?>" alt="<?php echo $property['title']; ?>">
                    </div>
                    <div class="property-details">
                        <h3><?php echo $property['title']; ?></h3>
                        <p class="location"><?php echo $property['location']; ?></p>
                        <p class="price"><?php echo Helpers::formatCurrency($property['price']); ?></p>
                        <div class="property-features">
                            <span><?php echo $property['bedrooms']; ?> Beds</span>
                            <span><?php echo $property['bathrooms']; ?> Baths</span>
                            <span><?php echo $property['area']; ?> sq.ft</span>
                        </div>
                        <a href="/property/<?php echo $property['id']; ?>" class="view-details">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No properties available.</p>
        <?php endif; ?>
    </div>
</section>

<?php include '../app/views/includes/footer.php'; ?>