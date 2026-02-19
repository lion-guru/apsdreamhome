<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if (!isset($property)) {
    $property = [
        'id' => '',
        'title' => 'Property Details',
        'description' => 'Discover this exceptional property',
        'location' => 'Location not specified',
        'price' => 0,
        'type' => 'residential',
        'bedrooms' => null,
        'bathrooms' => null,
        'area' => null,
        'status' => 'Available',
        'image' => null
    ];
}

$page_title = htmlspecialchars($property['title']) . ' - APS Dream Home';
$page_description = htmlspecialchars($property['description']);

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/properties">Properties</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <article class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="position-relative" style="height: 420px;">
                    <?php if (!empty($property['image'])): ?>
                        <img src="/uploads/properties/<?php echo htmlspecialchars($property['image']); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($property['title']); ?>">
                    <?php else: ?>
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                            <i class="fas fa-city fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill px-3 py-2"><?php echo ucfirst(htmlspecialchars($property['type'])); ?></span>
                        <?php if (!empty($property['status'])): ?>
                            <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2"><?php echo htmlspecialchars($property['status']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
                        <div>
                            <h1 class="h3 fw-semibold mb-2"><?php echo htmlspecialchars($property['title']); ?></h1>
                            <p class="text-secondary mb-0"><i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property['location']); ?></p>
                        </div>
                        <div class="text-lg-end">
                            <span class="d-block text-secondary small">Listed price</span>
                            <span class="display-6 fw-semibold text-primary">₹<?php echo number_format((float) $property['price']); ?></span>
                        </div>
                    </div>

                    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                        <?php if (!empty($property['bedrooms'])): ?>
                            <div class="col">
                                <div class="border rounded-4 p-3 h-100">
                                    <i class="fas fa-bed text-primary mb-2"></i>
                                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($property['bedrooms']); ?> Bedrooms</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property['bathrooms'])): ?>
                            <div class="col">
                                <div class="border rounded-4 p-3 h-100">
                                    <i class="fas fa-bath text-primary mb-2"></i>
                                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($property['bathrooms']); ?> Bathrooms</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($property['area'])): ?>
                            <div class="col">
                                <div class="border rounded-4 p-3 h-100">
                                    <i class="fas fa-vector-square text-primary mb-2"></i>
                                    <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($property['area']); ?> sq.ft</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col">
                            <div class="border rounded-4 p-3 h-100">
                                <i class="fas fa-calendar-day text-primary mb-2"></i>
                                <p class="mb-0 fw-semibold">Available now</p>
                            </div>
                        </div>
                    </div>

                    <h2 class="h5 fw-semibold mb-3">Description</h2>
                    <p class="text-secondary"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>

                    <div class="accordion mt-4" id="propertyDetails">
                        <div class="accordion-item border-0 shadow-sm rounded-4 mb-3">
                            <h2 class="accordion-header" id="headingAmenities">
                                <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAmenities" aria-expanded="true" aria-controls="collapseAmenities">
                                    Amenities & highlights
                                </button>
                            </h2>
                            <div id="collapseAmenities" class="accordion-collapse collapse show" aria-labelledby="headingAmenities" data-bs-parent="#propertyDetails">
                                <div class="accordion-body text-secondary">
                                    Premium amenities information coming soon. Contact our advisors for full specification sheets.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 shadow-sm rounded-4">
                            <h2 class="accordion-header" id="headingLocation">
                                <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLocation" aria-expanded="false" aria-controls="collapseLocation">
                                    Neighborhood & connectivity
                                </button>
                            </h2>
                            <div id="collapseLocation" class="accordion-collapse collapse" aria-labelledby="headingLocation" data-bs-parent="#propertyDetails">
                                <div class="accordion-body text-secondary">
                                    Detailed neighborhood insights, commute options and lifestyle highlights will be added soon.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Contact Advisor</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="/properties/<?php echo $property['id']; ?>/contact" method="POST" class="vstack gap-3">
                            <div>
                                <label for="name" class="form-label fw-semibold">Your name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="email" class="form-label fw-semibold">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="phone" class="form-label fw-semibold">Phone number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user_phone'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="message" class="form-label fw-semibold">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="I'm interested in scheduling a visit..."></textarea>
                            </div>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <button type="submit" class="btn btn-primary w-100">Send inquiry</button>
                            <p class="text-muted small mb-0">Our advisors typically respond within 30 minutes during business hours.</p>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-lock fa-2x text-primary mb-3"></i>
                            <p class="text-secondary mb-3">Please log in to connect with a property advisor.</p>
                            <a href="/login" class="btn btn-primary w-100">Login to continue</a>
                            <a href="/register" class="btn btn-outline-primary w-100 mt-2">Create an account</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Need help comparing?</h6>
                    <p class="text-secondary small">Our specialists can shortlist properties based on your budget, location and timeline.</p>
                    <a href="/contact" class="btn btn-outline-primary w-100">Request callback</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>