<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '+91-9277121112'); $emailDisplay = $sc('contact_email', 'info@apsdreamhome.com'); ?>
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
            <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($property['title'] ?? ''); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Image Gallery & Content -->
        <div class="col-lg-8">
            <!-- Hero Image Section -->
            <div class="glass-card p-2 mb-4 overflow-hidden" data-gallery="property-<?= (int)($property['id'] ?? 0) ?>">
                <div class="position-relative">
                    <?php
                    $heroImg = $property['image'] ?? '';
                    if (empty($heroImg)) {
                        $heroImgSrc = BASE_URL . '/assets/images/property-placeholder.jpg';
                    } elseif (strpos($heroImg, 'http') === 0) {
                        $heroImgSrc = $heroImg;
                    } else {
                        $heroImgSrc = BASE_URL . '/' . ltrim($heroImg, '/');
                    }
                    ?>
                    <img src="<?= htmlspecialchars($heroImgSrc) ?>" alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" data-caption="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" class="w-100 rounded-lg shadow-2xl property-image" id="main-gallery-image" loading="lazy">

                    <div class="position-absolute top-0 end-0 p-3">
                        <span class="badge bg-primary glass-blur px-3 py-2 fs-6">
                            <?php echo ucfirst($property['property_type'] ?? 'Premium'); ?>
                        </span>
                    </div>
                </div>

                <!-- Thumbnails -->
                <?php if (!empty($property_images) && count($property_images) > 1): ?>
                    <div class="d-flex gap-2 mt-2 px-1 overflow-auto pb-2 scrollbar-hidden" data-gallery="property-<?= (int)($property['id'] ?? 0) ?>-thumbs">
                        <?php foreach ($property_images as $img): ?>
                            <img src="<?= htmlspecialchars(is_array($img) ? ($img['src'] ?? $img['image_path'] ?? '') : $img) ?>" alt="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" data-caption="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" class="rounded cursor-pointer thumbnail-hover" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Essential Info Bar -->
            <div class="glass-card p-4 mb-4 d-flex flex-wrap justify-content-around text-center gap-3">
                <div>
                    <i class="bi bi-door-open fs-3 text-primary d-block mb-1"></i>
                    <span class="text-white fw-bold d-block"><?php echo e($property['bedrooms']); ?> BHK</span>
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
                        <p class="lh-lg"><?php echo nl2br(htmlspecialchars($property['description'] ?? '')); ?></p>
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
                        <div class="rounded-lg overflow-hidden border border-secondary">
                            <!-- Mock Map -->
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" alt="Property location map" class="w-100 h-100" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Pricing & Lead Form -->
        <div class="col-lg-4">
            <div class="sticky-top">
                <!-- Pricing Card -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="text-white-50 small text-uppercase mb-1">Investment Amount</h5>
                    <h2 class="text-white fw-bold mb-4">₹<?php echo number_format($property['price']); ?></h2>

                    <div class="d-grid gap-3">
                        <!-- WhatsApp Primary CTA -->
                        <a href="https://wa.me/<?= $phoneRaw ?>?text=<?= urlencode("Hi, I'm interested in " . $property['title'] . " (ID: " . $property['id'] . ") - Price: ₹" . number_format($property['price']) . ". Could you share more details?") ?>" class="btn btn-success btn-lg" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#inquiryModal">
                            <i class="bi bi-chat-dots me-2"></i>Send Inquiry
                        </button>
                        <button class="btn btn-outline-light btn-lg" onclick="shareProperty()">
                            <i class="bi bi-share me-2"></i>Share Property
                        </button>
                        <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/pdf/download/brochure/<?php echo (int)($property['id'] ?? 0); ?>" class="btn btn-outline-light btn-lg" target="_blank" rel="noopener">
                            <i class="bi bi-file-pdf me-2"></i>Download Brochure
                        </a>
                    </div>

                    <div class="mt-4 pt-4 border-top border-white border-opacity-10 text-center">
                        <p class="text-white-50 small mb-0">Managed by <strong>APS Dream Home</strong></p>
                    </div>
                </div>

                <!-- Agent / Owner Contact (Lead Capture Wall) -->
                <div class="glass-card p-4">
                    <h5 class="text-white h6 mb-3">Contact Property <?php echo !empty($property['source']) && $property['source'] == 'ai_fetched' ? 'Owner' : 'Specialist'; ?></h5>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="<?= BASE_URL ?>/assets/images/placeholder/hero.svg" class="rounded-circle shadow" alt="Contact person" >
                        <div class="w-100">
                            <h6 class="text-white mb-0"><?php echo !empty($property['source']) && $property['source'] == 'ai_fetched' ? 'Verified Owner' : 'APS Sales Team'; ?></h6>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- Logged In View: Show Contact & Track Lead -->
                                <div class="mt-2" id="revealed-contact" >
                                    <h5 class="text-success fw-bold mb-0">
                                        <?php echo !empty($property['owner_contact']) ? htmlspecialchars($property['owner_contact'] ?? '') : htmlspecialchars($phoneDisplay ?? ''); ?>
                                    </h5>
                                    <small class="text-white-50">Verified Number <i class="bi bi-check-circle-fill text-success ms-1"></i></small>
                                </div>
                                <button id="reveal-btn" class="btn btn-sm btn-success mt-2 w-100 fw-bold shadow" onclick="revealContact(<?php echo e($property['id']); ?>)">
                                    <i class="bi bi-eye-fill me-1"></i> Reveal Phone Number
                                </button>
                            <?php else: ?>
                                <!-- Logged Out View: Lead Capture Wall -->
                                <div class="mt-2 position-relative">
                                    <h5 class="text-white-50 fw-bold mb-0">+91 98765 43210</h5>
                                </div>
                                <a href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/login?redirect=property/<?php echo $property['id']; ?>" class="btn btn-sm btn-warning mt-3 w-100 fw-bold shadow">
                                    <i class="bi bi-lock-fill me-1"></i> Login to View Contact
                                </a>
                                <small class="text-white-50 d-block mt-2 text-center">(100% Free - Verify you're human)</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <hr class="border-secondary opacity-25">
                        <form id="lead-form">
    <?php echo CSRFProtection::csrfField(); ?>
                            <h6 class="text-white-50 small mb-2">Or request a callback:</h6>
                            <div class="mb-2">
                                <input type="text" class="form-control bg-transparent text-white border-white border-opacity-10" placeholder="Your Name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                            </div>
                            <div class="mb-2">
                                <input type="email" class="form-control bg-transparent text-white border-white border-opacity-10" placeholder="Email Address" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                            </div>
                            <button type="button" class="btn btn-outline-primary w-100" onclick="alert('Call back request sent! Our team will contact you shortly.')">Request Callback</button>
                        </form>
                    <?php endif; ?>
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
        border-bottom: 2px solid var(--primary) !important;
        color: var(--primary) !important;
    }

    .thumbnail-hover {
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .thumbnail-hover:hover {
        transform: scale(1.05);
        border-color: var(--primary);
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
                title: '<?php echo htmlspecialchars($property['title'] ?? ''); ?>',
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(window.location.href);
            alert('Link copied to clipboard!');
        }
    }

    function revealContact(propertyId) {
        document.getElementById('reveal-btn').style.display = 'none';
        document.getElementById('revealed-contact').style.display = 'block';

        // Quietly log this interaction as a Lead using existing Tracking API
        const formData = new FormData();
        formData.append('property_id', propertyId);
        formData.append('interest_type', 'view_contact');
        formData.append('source', 'property_detail_page');

        const baseUrl = '<?php echo defined("BASE_URL") ? BASE_URL : ""; ?>';
        fetch(baseUrl + '/track/interest', {
            method: 'POST',
            body: formData
        }).catch(e => console.log('Lead tracked silently.'));
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

<!-- Smart Registration Behavior Tracking -->
<script>
(function() {
    var token = getCookie('smart_reg_token');
    if (!token) return;

    function getCookie(name) {
        var v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return v ? v.pop() : '';
    }

    function track(eventType, eventData) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= BASE_URL ?>/api/smart-register/track', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify({
                token: token,
                event_type: eventType,
                event_data: eventData || null,
                page_url: window.location.href
            }));
        } catch (e) { console.error("Error:", e); }
    }

    // Track property view
    track('property_view', {
        property_id: <?= (int)($property['id'] ?? 0) ?>,
        property_title: <?= json_encode($property['title'] ?? '') ?>,
        property_type: <?= json_encode($property['property_type'] ?? '') ?>,
        price: <?= (int)($property['price'] ?? 0) ?>,
        city: <?= json_encode($property['city'] ?? '') ?>
    });

    // Track "Earn Money" / "List Property" clicks
    document.querySelectorAll('[data-track-earn], [data-track-agent]').forEach(function(el) {
        el.addEventListener('click', function() {
            track('earn_click', { source: 'property_detail' });
        });
    });
})();
</script>

<!-- Inquiry Modal (wired to POST /property/interest) -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inquiryModalLabel"><i class="bi bi-chat-dots me-2"></i>Send Inquiry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Interested in <strong><?= htmlspecialchars($property['title'] ?? 'this property') ?></strong>? Share your details and our team will contact you.</p>
                <form id="detailInquiryForm">
                    <?php echo CSRFProtection::csrfField(); ?>
                    <input type="hidden" name="property_id" value="<?= (int)($property['id'] ?? 0) ?>">
                    <input type="hidden" name="source" value="property_detail">
                    <div class="mb-3">
                        <label class="form-label" for="inquiryName">Your Name *</label>
                        <input type="text" class="form-control" id="inquiryName" name="name" required maxlength="255" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inquiryPhone">Phone Number *</label>
                        <input type="tel" class="form-control" id="inquiryPhone" name="phone" required maxlength="20" pattern="[0-9+\-\s]{10,20}" value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="inquiryMessage">Message</label>
                        <textarea class="form-control" id="inquiryMessage" name="message" rows="3" maxlength="2000" placeholder="I would like to know more about this property..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="inquirySubmitBtn">
                        <i class="bi bi-send me-1"></i>Submit Inquiry
                    </button>
                </form>
                <div id="detailInquirySuccess" class="text-center py-3 d-none">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                    <h6 class="fw-bold">Inquiry Sent!</h6>
                    <p class="text-muted small mb-0">Our team will contact you shortly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var form = document.getElementById('detailInquiryForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('inquirySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
        fetch('<?= BASE_URL ?>/property/interest', {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success) {
                form.classList.add('d-none');
                document.getElementById('detailInquirySuccess').classList.remove('d-none');
                setTimeout(function() {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('inquiryModal'));
                    if (modal) modal.hide();
                }, 2500);
            } else {
                alert((data && data.message) || 'Something went wrong. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send me-1"></i>Submit Inquiry';
            }
        })
        .catch(function() {
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i>Submit Inquiry';
        });
    });
})();
</script>

//
// ─────────────────────────────────────────────────────────────────────────────
// SHARE PROPERTY FUNCTIONALITY
// ─────────────────────────────────────────────────────────────────────────────

function shareProperty() {
    const propertyId = <?= (int)($property['id'] ?? 0) ?>;
    const propertyTitle = <?= json_encode($property['title'] ?? '') ?>;
    const propertyPrice = <?= (int)($property['price'] ?? 0) ?>;
    const propertyLocation = <?= json_encode($property['location'] ?? $property['address'] ?? '') ?>;
    const propertyImage = <?= json_encode($heroImgSrc) ?>;
    const propertyType = <?= json_encode($property['property_type'] ?? '') ?>;
    const propertyId = <?= (int)($property['id'] ?? 0) ?>;
    
    // Get user's referral code if logged in
    let referralCode = '';
    let userName = '';
    let userPhone = '';
    try {
        const branding = JSON.parse(localStorage.getItem('user_branding') || '{}');
        if (branding.display_name) {
            userName = branding.display_name;
        }
        if (branding.phone) {
            userPhone = branding.phone;
        }
        // Fallback to referral code from meta tag or session
        const refMeta = document.querySelector('meta[name="referral-code"]');
        if (refMeta) {
            referralCode = refMeta.getAttribute('content');
        }
    } catch (e) {
        console.warn('Could not read branding from localStorage:', e);
    }
    
    // Build share message
    const propertyUrl = '<?= BASE_URL ?>/property/' + propertyId;
    const referralLink = referralCode ? '<?= BASE_URL ?>/register?ref=' + encodeURIComponent(referralCode) : '';
    
    let shareMessage = '🏡 ' + propertyTitle + '\n\n';
    shareMessage += '💰 Price: ₹' + propertyPrice.toLocaleString('en-IN') + '\n';
    shareMessage += '📍 Location: ' + propertyLocation + '\n\n';
    shareMessage += '🏡 View Property: ' + propertyUrl + '\n';
    if (referralLink) {
        shareMessage += '🔗 Register with my referral: ' + referralLink + '\n';
    }
    shareMessage += '\n📞 Contact: ' + '<?= addslashes($phoneDisplay) ?>' + '\n';
    shareMessage += '🌐 ' + '<?= BASE_URL ?>';
    
    // Generate WhatsApp share link
    const waUrl = 'https://wa.me/?text=' + encodeURIComponent(shareMessage);
    const smsUrl = 'sms:?body=' + encodeURIComponent(shareMessage);
    const emailUrl = 'mailto:?subject=' + encodeURIComponent('Property: ' + propertyTitle) + '&body=' + encodeURIComponent(shareMessage);
    const callUrl = 'tel:<?= addslashes($phoneRaw) ?>';
    const fbUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(propertyUrl);
    const instaUrl = 'https://www.instagram.com/';
    const copyUrl = propertyUrl;

    // Create share modal
    const modalHtml = `
        <div class="modal fade" id="sharePropertyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content glass-card" style="border: 1px solid rgba(250,204,21,0.3);">
                    <div class="modal-header bg-royal-900 text-white border-0" style="border-radius: 12px 12px 0 0;">
                        <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Share Property</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <img src="${propertyImage || '<?= BASE_URL ?>/assets/images/placeholder/property.svg'}" class="img-fluid rounded" alt="${propertyTitle}" style="max-height: 250px; object-fit: cover;">
                            </div>
                            <div class="col-md-7">
                                <h5 class="text-primary fw-bold">${propertyTitle}</h5>
                                <p class="text-muted small mb-1"><i class="bi bi-map-marker-alt me-1"></i>${propertyLocation}</p>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="badge bg-info">${propertyType || 'Property'}</span>
                                    <span class="badge bg-warning text-dark">ID: ${propertyId}</span>
                                </div>
                                <h5 class="text-success fw-bold">₹${propertyPrice.toLocaleString('en-IN')}</h5>
                            </div>
                        </div>
                        <hr class="border-secondary">
                        <h6 class="fw-bold mb-3"><i class="fas fa-share-alt me-2"></i>Share Options</h6>
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-success btn-lg d-flex align-items-center justify-content-center gap-2" onclick="navigator.clipboard.writeText('${shareMessage.replace(/'/g, "\\'")}').then(() => { alert('Copied to clipboard!'); }); event.preventDefault();">
                                <i class="fab fa-whatsapp text-white me-2"></i> WhatsApp
                            </a>
                            <a href="sms:?body=${encodeURIComponent(shareMessage)}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-chat-dots me-2"></i> SMS
                            </a>
                            <a href="mailto:?subject=${encodeURIComponent('Property: ' + propertyTitle)}&body=${encodeURIComponent(shareMessage)}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-envelope me-2"></i> Email
                            </a>
                            <a href="tel:<?= addslashes($phoneRaw) ?>" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-telephone me-2"></i> Call
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(propertyUrl)}" target="_blank" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fab fa-facebook me-2"></i> Facebook
                            </a>
                            <a href="https://www.instagram.com/" target="_blank" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="fab fa-instagram me-2"></i> Instagram
                            </a>
                            <button class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center gap-2" onclick="navigator.clipboard.writeText('${copyUrl.replace(/'/g, "\\'")}').then(() => { alert('Link copied!'); }); event.preventDefault();">
                                <i class="bi bi-link-45deg me-2"></i> Copy Link
                            </button>
                        </div>
                        <hr>
                        <div class="text-center">
                            <img id="qrCodeImg" src="" alt="QR Code" style="max-width: 180px;">
                            <p class="text-muted small mt-2">Scan to open on mobile</p>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Inject modal into body
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHtml;
    document.body.appendChild(modalContainer.firstElementChild);
    
    const modal = new bootstrap.Modal(document.getElementById('sharePropertyModal'));
    modal.show();
    
    // Generate QR code after modal is shown
    document.getElementById('sharePropertyModal').addEventListener('shown.bs.modal', function() {
        const qrText = '<?= BASE_URL ?>/property/' + propertyId;
        const qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(qrText);
        document.getElementById('qrCodeImg').src = qrApiUrl;
    });
    
    // Clean up modal on hide
    document.getElementById('sharePropertyModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
    
    modal.show();
}
//