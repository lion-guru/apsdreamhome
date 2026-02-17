<?php
namespace Views;

class PropertyView {
    public static function renderPropertyList($properties) {
        ob_start();
        ?>
        <div class="property-list">
            <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <div class="property-image">
                    <?php if (!empty($property['image'])): ?>
                    <img src="<?php echo APP_URL . '/uploads/' . $property['image']; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    <?php else: ?>
                    <img src="<?php echo BASE_URL; ?>/assets/<?php echo get_asset_url('default-property.jpg', 'images'); ?>" alt="Default Property Image">
                    <?php endif; ?>
                </div>
                <div class="property-details">
                    <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                    <p class="price"><?php echo format_indian_currency($property['price']); ?></p>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($property['city']); ?></p>
                    <p class="description"><?php echo substr(htmlspecialchars($property['description']), 0, 150) . '...'; ?></p>
                    <div class="property-features">
                        <span><i class="fas fa-home"></i> <?php echo htmlspecialchars($property['property_type']); ?></span>
                        <?php if (!empty($property['area'])): ?>
                        <span><i class="fas fa-ruler-combined"></i> <?php echo htmlspecialchars($property['area']); ?> sq.ft</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo APP_URL . '/propertydetail.php?id=' . $property['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function renderPropertyDetail($property) {
        if (!$property) {
            return '<div class="alert alert-danger">Property not found</div>';
        }

        ob_start();
        ?>
        <div class="property-detail">
            <div class="property-header">
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="price"><?php echo format_indian_currency($property['price']); ?></p>
            </div>
            
            <div class="property-gallery">
                <?php if (!empty($property['image'])): ?>
                <div class="main-image">
                    <img src="<?php echo APP_URL . '/uploads/' . $property['image']; ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                </div>
                <?php endif; ?>
            </div>

            <div class="property-info">
                <div class="info-section">
                    <h3>Property Details</h3>
                    <ul>
                        <li><strong>Type:</strong> <?php echo htmlspecialchars($property['property_type']); ?></li>
                        <li><strong>Location:</strong> <?php echo htmlspecialchars($property['city']); ?></li>
                        <?php if (!empty($property['area'])): ?>
                        <li><strong>Area:</strong> <?php echo htmlspecialchars($property['area']); ?> sq.ft</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="description-section">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>

                <?php if (!empty($property['amenities'])): ?>
                <div class="amenities-section">
                    <h3>Amenities</h3>
                    <ul class="amenities-list">
                        <?php foreach (explode(',', $property['amenities']) as $amenity): ?>
                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars(trim($amenity)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <?php if (is_logged_in()): ?>
            <div class="contact-section">
                <h3>Contact Agent</h3>
                <form action="<?php echo APP_URL; ?>/contact_agent.php" method="post" class="contact-form">
                    <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <?php else: ?>
            <div class="login-prompt">
                <p>Please <a href="<?php echo APP_URL; ?>/login.php">login</a> to contact the agent.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}