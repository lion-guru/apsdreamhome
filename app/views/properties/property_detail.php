<?php
$page_title = $property['title'] . ' - APS Dream Home';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container-fluid py-4 overflow-hidden">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb glass-breadcrumb p-2 px-3">
            <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
            <li class="breadcrumb-item"><a href="/properties" class="text-white-50">Properties</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Image Gallery & Content -->
        <div class="col-lg-8">
            <!-- Hero Image Section -->
            <div class="glass-card p-2 mb-4 overflow-hidden">
                <div class="position-relative">
                    <img src="<?php echo $property_images[0]['image_path'] ?? $property['image'] ?? 'https://via.placeholder.com/1200x600'; ?>" 
                         alt="<?php echo htmlspecialchars($property['title']); ?>" 
                         class="w-100 rounded-lg shadow-2xl" id="main-gallery-image"
                         style="height: 500px; object-fit: cover; border-radius: 12px;">
                    
                    <div class="position-absolute top-0 end-0 p-3">
                        <span class="badge bg-primary glass-blur px-3 py-2 fs-6">
                            <?php echo ucfirst($property['property_type'] ?? 'Premium'); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Thumbnails -->
                <?php if (!empty($property_images) && count($property_images) > 1): ?>
                <div class="d-flex gap-2 mt-2 px-1 overflow-auto pb-2 scrollbar-hidden">
                    <?php foreach ($property_images as $img): ?>
                    <img src="<?php echo $img['image_path']; ?>" 
                         class="rounded cursor-pointer thumbnail-hover" 
                         style="width: 100px; height: 70px; object-fit: cover;"
                         onclick="document.getElementById('main-gallery-image').src = this.src">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Essential Info Bar -->
            <div class="glass-card p-4 mb-4 d-flex flex-wrap justify-content-around text-center gap-3">
                <div>
                    <i class="bi bi-door-open fs-3 text-primary d-block mb-1"></i>
                    <span class="text-white fw-bold d-block"><?php echo $property['bedrooms']; ?> BHK</span>
                    <small class="text-white-50 lowercase">Bedrooms</small>
                </div>
                <div class="vr bg-white opacity-25 d-none d-md-block"></div>
                <div>
                    <i class="bi bi-water fs-3 text-info d-block mb-1"></i>
                    <span class="text-white fw-bold d-block"><?php echo $property['bathrooms'] ?? '2'; ?></span>
                    <small class="text-white-50 lowercase">Bathrooms</small>
                </div>
                <div class="vr bg-white opacity-25 d-none d-md-block"></div>
                <div>
                    <i class="bi bi-rulers fs-3 text-warning d-block mb-1"></i>
                    <span class="text-white fw-bold d-block"><?php echo number_format($property['area'] ?? $property['area_sqft'] ?? 0); ?></span>
                    <small class="text-white-50 lowercase">Sq.Ft Area</small>
                </div>
                <div class="vr bg-white opacity-25 d-none d-md-block"></div>
                <div>
                    <i class="bi bi-compass fs-3 text-success d-block mb-1"></i>
                    <span class="text-white fw-bold d-block"><?php echo ucfirst($property['facing'] ?? 'North'); ?></span>
                    <small class="text-white-50 lowercase">Facing</small>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="glass-card mb-4">
                <ul class="nav nav-tabs nav-fill border-0 p-2" id="propertyTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active text-white border-0 py-3" data-bs-toggle="tab" data-bs-target="#desc">Description</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white border-0 py-3" data-bs-toggle="tab" data-bs-target="#amenities">Amenities</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link text-white border-0 py-3" data-bs-toggle="tab" data-bs-target="#location">Location</button>
                    </li>
                </ul>
                <div class="tab-content p-4 text-white-50" id="propertyTabContent">
                    <div class="tab-pane fade show active" id="desc">
                        <h4 class="text-white h5 mb-3">About this Property</h4>
                        <p class="lh-lg"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                    </div>
                    <div class="tab-pane fade" id="amenities">
                        <div class="row g-3">
                            <?php 
                            $amenities = isset($property['amenities']) ? explode(',', $property['amenities']) : ['Parking', 'Security', 'Gated Community', 'Swimming Pool', 'CCTV'];
                            foreach ($amenities as $item): ?>
                            <div class="col-6 col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check2-circle text-primary"></i>
                                    <span><?php echo trim($item); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="location">
                        <p><i class="bi bi-geo-alt me-2 text-primary"></i><?php echo htmlspecialchars($property['address'] ?? $property['location']); ?></p>
                        <div class="rounded-lg overflow-hidden border border-secondary" style="height: 300px;">
                            <!-- Mock Map -->
                            <img src="https://via.placeholder.com/800x300/1e293b/ffffff?text=Interactive+Map+Coming+Soon" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Pricing & Lead Form -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 2rem; z-index: 10;">
                <!-- Pricing Card -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="text-white-50 small text-uppercase mb-1">Investment Amount</h5>
                    <h2 class="text-white fw-bold mb-4">₹<?php echo number_format($property['price']); ?></h2>
                    
                    <div class="d-grid gap-3">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                            <i class="bi bi-chat-dots me-2"></i>Send Inquiry
                        </button>
                        <button class="btn btn-outline-light btn-lg" onclick="shareProperty()">
                            <i class="bi bi-share me-2"></i>Share Property
                        </button>
                    </div>

                    <div class="mt-4 pt-4 border-top border-white border-opacity-10 text-center">
                        <p class="text-white-50 small mb-0">Managed by <strong>APS Dream Home</strong></p>
                    </div>
                </div>

                <!-- Agent Contact -->
                <div class="glass-card p-4">
                    <h5 class="text-white h6 mb-3">Contact Property Specialist</h5>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="https://via.placeholder.com/60/4f46e5/ffffff?text=AS" class="rounded-circle shadow">
                        <div>
                            <h6 class="text-white mb-0">APS Sales Team</h6>
                            <small class="text-white-50">+91 Premium Support</small>
                        </div>
                    </div>
                    <form id="lead-form">
                        <div class="mb-2">
                            <input type="text" class="form-control bg-transparent text-white border-white border-opacity-10" placeholder="Your Name">
                        </div>
                        <div class="mb-2">
                            <input type="email" class="form-control bg-transparent text-white border-white border-opacity-10" placeholder="Email Address">
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100" onclick="alert('Lead captured!')">Call Back Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .glass-breadcrumb {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
    }

    .glass-blur {
        backdrop-filter: blur(8px);
        background: rgba(41, 98, 255, 0.5) !important;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .nav-tabs .nav-link:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .nav-tabs .nav-link.active {
        background: rgba(41, 98, 255, 0.1) !important;
        border-bottom: 2px solid var(--primary-color) !important;
        color: var(--primary-color) !important;
    }

    .thumbnail-hover {
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .thumbnail-hover:hover {
        transform: scale(1.05);
        border-color: var(--primary-color);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }

    .scrollbar-hidden::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    function shareProperty() {
        if (navigator.share) {
            navigator.share({
                title: '<?php echo htmlspecialchars($property['title']); ?>',
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
            alert('Link copied to clipboard!');
        }
    }
</script>
 }

    .toast-body {
        padding: 1rem;
    }

    @media (max-width: 768px) {
        .toast {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
    }
</style>

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 815 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//