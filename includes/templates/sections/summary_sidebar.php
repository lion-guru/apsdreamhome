<?php
/**
 * Property Details: Summary Sidebar Section
 *
 * @var array $property Property data including price, type, agent info, etc.
 * @var callable $e HTML escaping function.
 * @var callable|null $formatPrice Price formatting function (optional, can be done in controller).
 */

if (empty($property)) {
    echo '<p>Property details are not available.</p>';
    return;
}

// Ensure $e is available, if not, define a basic one.
if (!function_exists('e')) {
    function e(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

// Price formatting - ideally done before passing to template or via a global helper
$formatted_price = isset($property['price']) ? (function_exists('formatPrice') ? formatPrice($property['price']) : '$' . number_format($property['price'], 2)) : 'N/A';

?>
<aside class="property-summary-sidebar col-lg-4 ps-lg-4">
    <div class="card shadow-sm mb-4 sticky-lg-top" style="top: 20px;">
        <div class="card-body">
            <h2 class="card-title h4 mb-0"><?php echo e($property['title'] ?? 'Property Title'); ?></h2>
            <p class="text-muted small mb-3"><?php echo e($property['address'] ?? 'Address not available'); ?></p>
            
            <p class="h3 text-primary fw-bold mb-3"><?php echo e($formatted_price); ?></p>

            <div class="property-meta mb-3">
                <span class="me-3" title="Property Type"><i class="fas fa-home me-1"></i> <?php echo e($property['property_type'] ?? 'N/A'); ?></span>
                <span class="me-3" title="Bedrooms"><i class="fas fa-bed me-1"></i> <?php echo e($property['bedrooms'] ?? 'N/A'); ?> beds</span>
                <span class="me-3" title="Bathrooms"><i class="fas fa-bath me-1"></i> <?php echo e($property['bathrooms'] ?? 'N/A'); ?> baths</span>
                <span title="Area"><i class="fas fa-ruler-combined me-1"></i> <?php echo e($property['area_sqft'] ?? 'N/A'); ?> sqft</span>
            </div>

            <hr>

            <h5 class="mt-4 mb-3">Agent Information</h5>
            <div class="agent-info d-flex align-items-center mb-3">
                <img src="<?php echo e(SITE_URL . '/' . ($property['agent_photo'] ?? 'assets/img/default-agent.png')); ?>" alt="<?php echo e($property['agent_name'] ?? 'Agent'); ?>" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                <div>
                    <h6 class="mb-0"><?php echo e($property['agent_name'] ?? 'N/A'); ?></h6>
                    <?php if (!empty($property['agent_phone'])): ?>
                        <p class="mb-0 small"><i class="fas fa-phone me-1"></i> <a href="tel:<?php echo e($property['agent_phone']); ?>"><?php echo e($property['agent_phone']); ?></a></p>
                    <?php endif; ?>
                    <?php if (!empty($property['agent_email'])): ?>
                        <p class="mb-0 small"><i class="fas fa-envelope me-1"></i> <a href="mailto:<?php echo e($property['agent_email']); ?>"><?php echo e($property['agent_email']); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <h5 class="mt-4 mb-3">Inquire About This Property</h5>
            <form id="inquiryForm" action="<?php echo e(SITE_URL . '/submit_inquiry.php'); ?>" method="POST">
                <input type="hidden" name="property_id" value="<?php echo e($property['id'] ?? ''); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token'] ?? ''); ?>"> <!-- CSRF Token -->
                
                <div class="mb-3">
                    <label for="inquiry_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="inquiry_name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="inquiry_email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="inquiry_email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="inquiry_phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="inquiry_phone" name="phone">
                </div>
                <div class="mb-3">
                    <label for="inquiry_message" class="form-label">Message</label>
                    <textarea class="form-control" id="inquiry_message" name="message" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Inquiry</button>
            </form>
            <div id="inquiryFormResponse" class="mt-3"></div>
        </div>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inquiryForm = document.getElementById('inquiryForm');
    if (inquiryForm) {
        inquiryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(inquiryForm);
            const responseDiv = document.getElementById('inquiryFormResponse');
            responseDiv.innerHTML = '<p class="text-info">Sending...</p>';

            fetch(inquiryForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    responseDiv.innerHTML = '<p class="alert alert-success">' + data.message + '</p>';
                    inquiryForm.reset();
                } else {
                    responseDiv.innerHTML = '<p class="alert alert-danger">Error: ' + (data.message || 'Could not send inquiry.') + '</p>';
                }
            })
            .catch(error => {
                console.error('Error submitting inquiry form:', error);
                responseDiv.innerHTML = '<p class="alert alert-danger">An unexpected error occurred. Please try again later.</p>';
            });
        });
    }
});
</script>
