<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
?>
<?php
/**
 * User Favorites Page Template
 * Shows user's favorite properties
 */

?>

<!-- Hero Section -->
<section class="favorites-hero py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 fw-bold text-white mb-4">
                    <i class="fas fa-heart me-3"></i>
                    My Favorite Properties
                </h1>
                <p class="lead text-white-50 mb-4">
                    Your saved properties are waiting for you. Keep track of properties you're interested in and never miss out on your dream home.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Favorites Section -->
<section class="favorites-section py-5">
    <div class="container">
        <?php if (empty($favorites)): ?>
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="empty-favorites">
                        <div class="empty-icon mb-4">
                            <i class="fas fa-heart-broken fa-4x text-muted"></i>
                        </div>
                        <h3 class="mb-3">No Favorite Properties Yet</h3>
                        <p class="text-muted mb-4">
                            Start browsing properties and save your favorites for easy access later.
                        </p>
                        <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Favorites Grid -->
            <div class="row g-4">
                <?php foreach ($favorites as $property): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="property-card">
                            <!-- Property Image -->
                            <div class="property-image">
                                <img src="<?php echo BASE_URL . ($property['main_image'] ?? 'assets/images/no-image.jpg'); ?>"
                                     alt="<?php echo htmlspecialchars($property['title']); ?>"
                                     class="img-fluid"
                                     onerror="this.src='<?php echo BASE_URL; ?>assets/images/no-image.jpg'">
                                <div class="property-overlay">
                                    <div class="property-actions">
                                        <button class="btn btn-danger btn-sm remove-favorite"
                                                data-property-id="<?php echo $property['id']; ?>"
                                                title="Remove from favorites">
                                            <i class="fas fa-heart-broken"></i>
                                        </button>
                                        <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                                <?php if ($property['featured']): ?>
                                    <div class="featured-badge">
                                        <i class="fas fa-star"></i> Featured
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Property Info -->
                            <div class="property-info">
                                <div class="property-header">
                                    <h5 class="property-title">
                                        <a href="<?php echo BASE_URL; ?>property/<?php echo $property['id']; ?>">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h5>
                                    <div class="property-location">
                                        <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                        <?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?>
                                    </div>
                                </div>

                                <div class="property-details">
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-bed text-primary"></i>
                                                <span><?php echo $property['bedrooms']; ?> Bed</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-bath text-primary"></i>
                                                <span><?php echo $property['bathrooms']; ?> Bath</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="detail-item">
                                                <i class="fas fa-ruler-combined text-primary"></i>
                                                <span><?php echo number_format($property['area_sqft']); ?> sqft</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="property-footer">
                                    <div class="property-price">
                                        <span class="price-amount">₹<?php echo number_format($property['price']); ?></span>
                                    </div>
                                    <div class="property-actions">
                                        <button class="btn btn-outline-primary btn-sm favorite-toggle"
                                                data-property-id="<?php echo $property['id']; ?>">
                                            <i class="fas fa-heart"></i> Favorited
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove favorite functionality
    document.querySelectorAll('.remove-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.getAttribute('data-property-id');
            const button = this;

            if (confirm('Are you sure you want to remove this property from your favorites?')) {
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;

                fetch('<?php echo BASE_URL; ?>favorites/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'property_id=' + propertyId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the property card from DOM
                        button.closest('.col-lg-4').remove();
                        // Show success message
                        showToast(data.message, 'success');
                    } else {
                        // Show error message
                        showToast(data.message, 'error');
                        // Reset button
                        button.innerHTML = '<i class="fas fa-heart-broken"></i>';
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                    // Reset button
                    button.innerHTML = '<i class="fas fa-heart-broken"></i>';
                    button.disabled = false;
                });
            }
        });
    });

    // Toast notification function
    function showToast(message, type) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-body">
                ${message}
            </div>
        `;

        // Add to page
        document.body.appendChild(toast);

        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Hide toast after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
});
</script>

<style>
.property-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.property-image {
    position: relative;
    height: 200px;
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
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.property-card:hover .property-overlay {
    opacity: 1;
}

.property-actions {
    display: flex;
    gap: 0.5rem;
}

.property-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.featured-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #ffc107;
    color: #212529;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.property-info {
    padding: 1.5rem;
}

.property-title {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    line-height: 1.4;
}

.property-title a {
    color: #2c3e50;
    text-decoration: none;
}

.property-title a:hover {
    color: #007bff;
}

.property-location {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.detail-item {
    text-align: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.detail-item i {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 1.2rem;
}

.detail-item span {
    font-size: 0.875rem;
    color: #495057;
}

.property-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.price-amount {
    font-size: 1.25rem;
    font-weight: bold;
    color: #007bff;
}

.empty-favorites {
    padding: 4rem 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.empty-icon {
    opacity: 0.5;
}

/* Toast notifications */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 500px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    background: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

.toast-error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.toast-body {
    padding: 1rem;
}

@media (max-width: 768px) {
    .property-overlay {
        opacity: 1;
        background: rgba(0, 0, 0, 0.3);
    }

    .property-actions {
        flex-direction: column;
        gap: 0.25rem;
    }

    .property-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    .toast {
        right: 10px;
        left: 10px;
        min-width: auto;
    }
}
</style>
