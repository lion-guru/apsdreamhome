<?php

/**
 * List Property Page - Simple & Easy Property Posting
 * Now with Smart Location Dropdowns & Guest Support
 */
if (session_status() === PHP_SESSION_NONE) @session_start();

$success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null;
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Check if user is logged in (customer, associate, or agent)
$isCustomer = isset($_SESSION['user_id']) && $_SESSION['user_id'];
$isAssociate = isset($_SESSION['associate_id']) && $_SESSION['associate_id'];
$isAgent = isset($_SESSION['agent_id']) && $_SESSION['agent_id'];
$isLoggedIn = $isCustomer || $isAssociate || $isAgent;

// Get user info for pre-fill
$userName = '';
$userPhone = '';
$userEmail = '';
if ($isCustomer) {
    $userName = $_SESSION['user_name'] ?? '';
    $userPhone = $_SESSION['user_phone'] ?? '';
    $userEmail = $_SESSION['user_email'] ?? '';
} elseif ($isAssociate) {
    $userName = $_SESSION['associate_name'] ?? '';
    $userPhone = $_SESSION['associate_phone'] ?? '';
    $userEmail = $_SESSION['associate_email'] ?? '';
} elseif ($isAgent) {
    $userName = $_SESSION['agent_name'] ?? '';
    $userPhone = $_SESSION['agent_phone'] ?? '';
    $userEmail = $_SESSION['agent_email'] ?? '';
}

// Load states for dropdown
$db = \App\Core\Database\Database::getInstance();
$states = $db->fetchAll("SELECT id, name FROM states WHERE is_active = 1 ORDER BY name LIMIT 50");
?>

<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-home me-3"></i>List Your Property for Free</h1>
        <p class="lead">Fill the form in just 1 minute - Verified buyers will find your property!</p>
    </div>
</section>

<?php if ($success): ?>
    <div class="container mt-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="container mt-4">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Simple Form -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white text-center py-3">
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Submit Your Property Details</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>/list-property/submit" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            <!-- Property Type -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">What is the purpose of listing? *</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" value="sell" id="sell" checked>
                                        <label class="form-check-label" for="sell">
                                            <i class="fas fa-tag text-success me-1"></i> Sell
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" value="rent" id="rent">
                                        <label class="form-check-label" for="rent">
                                            <i class="fas fa-key text-primary me-1"></i> Rent
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="mb-3">
                                <label for="property_type" class="form-label fw-bold">Property Type *</label>
                                <select name="property_type" id="property_type" class="form-select" required autocomplete="property-type">
                                    <option value="">Select...</option>
                                    <option value="plot">Plot (Naksha)</option>
                                    <option value="house">House / Villa</option>
                                    <option value="flat">Flat / Apartment</option>
                                    <option value="shop">Shop</option>
                                    <option value="farmhouse">Farm House</option>
                                </select>
                            </div>

                            <!-- Location with Smart Dropdowns -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="state_id" class="form-label fw-bold">State *</label>
                                    <select id="state_id" name="state_id" class="form-select" required autocomplete="address-level1">
                                        <option value="">Select State...</option>
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?= $state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="district_id" class="form-label fw-bold">District/City *</label>
                                    <select id="district_id" name="location" class="form-select" required disabled autocomplete="address-level2">
                                        <option value="">Select District First...</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Hidden fields for state/district IDs -->
                            <input type="hidden" name="selected_state_id" id="selected_state_id">
                            <input type="hidden" name="selected_district_id" id="selected_district_id">
                            <input type="hidden" name="selected_city_name" id="selected_city_name">

                            <!-- Price -->
                            <div class="mb-3">
                                <label for="price" class="form-label fw-bold">Expected Price (₹) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" name="price" id="price" class="form-control" placeholder="e.g. 25 Lakh or 15000/month" required autocomplete="transaction-amount">
                                </div>
                            </div>

                            <!-- Area (sq ft) -->
                            <div class="mb-3">
                                <label for="area" class="form-label fw-bold">Area (sq ft) *</label>
                                <input type="text" name="area" id="area" class="form-control" placeholder="e.g. 1500 sq ft" required autocomplete="off">
                            </div>

                            <!-- Name & Phone -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-bold">Your Name *</label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Name" required autocomplete="name" value="<?php echo htmlspecialchars($userName); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone Number *</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="+91 XXXXXXXXXX" required autocomplete="tel" value="<?php echo htmlspecialchars($userPhone); ?>">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="your@email.com" autocomplete="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Full Address / Location</label>
                                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Plot size, road width, nearby landmarks..." autocomplete="street-address"></textarea>
                            </div>

                            <!-- Pincode & City -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pincode" class="form-label fw-bold">Pincode</label>
                                    <input type="text" name="pincode" id="pincode" class="form-control" placeholder="e.g. 273008" maxlength="6" autocomplete="postal-code">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label fw-bold">City/Town</label>
                                    <input type="text" name="city" id="city" class="form-control" placeholder="e.g. Gorakhpur" autocomplete="address-level2">
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-3">
                                <label for="property_image" class="form-label fw-bold">Property Image (Optional)</label>
                                <input type="file" name="property_image" id="property_image" class="form-control" accept="image/jpeg,image/png,image/webp" autocomplete="photo">
                                <small class="text-muted">Upload JPG, PNG or WEBP (Max 5MB)</small>
                            </div>

                            <!-- Submit -->
                            <?php if ($isLoggedIn): ?>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Property Listing
                                </button>
                                <div class="text-center mt-3">
                                    <small class="text-muted">By submitting, you agree to our <a href="/terms">Terms of Service</a></small>
                                </div>
                            <?php else: ?>
                                <button type="button" class="btn btn-success btn-lg w-100" onclick="handleGuestSubmit()">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Property Listing
                                </button>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Guest Posting:</strong> Quick signup required to post your property. It's FREE and takes 10 seconds!
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="alert alert-info mt-4">
                    <h5><i class="fas fa-info-circle me-2"></i>How It Works?</h5>
                    <ol class="mb-0">
                        <li>You submit the form</li>
                        <li>We verify your details</li>
                        <li>We connect you with verified buyers</li>
                        <li>You close the deal!</li>
                    </ol>
                </div>
            </div>

            <!-- Right Side - Benefits -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>100% FREE Service</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>No listing charges</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>No commission on sale</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>No hidden fees</li>
                            <li><i class="fas fa-check text-success me-2"></i>Direct buyer contact</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>What You Get</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-phone text-primary me-2"></i>Free property valuation</li>
                            <li class="mb-2"><i class="fas fa-gavel text-primary me-2"></i>Legal document help</li>
                            <li class="mb-2"><i class="fas fa-hand-holding-usd text-primary me-2"></i>Home loan assistance</li>
                            <li><i class="fas fa-user-friends text-primary me-2"></i>Serious buyers only</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body text-center">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h5>Need Help?</h5>
                        <p class="text-muted mb-3">We're here to assist you!</p>
                        <div class="d-grid gap-2">
                            <a href="tel:+919277121112" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i>Call: +91 92771 21112
                            </a>
                            <a href="https://wa.me/919277121112?text=Hi, I want to list my property for sale" target="_blank" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="text-center mb-4">How It Works?</h3>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">1</span>
                </div>
                <h5>Submit Your Property Details</h5>
                <p class="text-muted">Fill the form in just 1 minute</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">2</span>
                </div>
                <h5>We Verify Your Details</h5>
                <p class="text-muted">We'll contact you to verify your property details</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">3</span>
                </div>
                <h5>Get Connected with Buyers</h5>
                <p class="text-muted">We'll connect you with verified buyers</p>
                <p class="text-muted">Serious buyers will contact you directly</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h3>List Your Property Today!</h3>
        <p class="mb-4">It's completely FREE - No charges at all!</p>
        <a href="#form" class="btn btn-warning btn-lg">
            <i class="fas fa-arrow-up me-2"></i>Fill the Form
        </a>
    </div>
</section>

<!-- Smart Form JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Location cascade
    const stateSelect = document.getElementById('state_id');
    const districtSelect = document.getElementById('district_id');

    stateSelect.addEventListener('change', async function() {
        const stateId = this.value;
        if (!stateId) {
            districtSelect.innerHTML = '<option value="">Select State First...</option>';
            districtSelect.disabled = true;
            return;
        }
        districtSelect.disabled = true;
        districtSelect.innerHTML = '<option value="">Loading...</option>';
        try {
            const resp = await fetch(BASE_URL + '/api/locations/districts?state_id=' + stateId);
            const districts = await resp.json();
            districtSelect.innerHTML = '<option value="">Select District...</option>';
            districts.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.name;
                opt.dataset.id = d.id;
                opt.textContent = d.name;
                districtSelect.appendChild(opt);
            });
            districtSelect.disabled = false;
        } catch(e) {
            console.error('Error loading districts:', e);
            districtSelect.innerHTML = '<option value="">Error loading</option>';
        }
    });

    // Update hidden fields when district changes
    districtSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('selected_state_id').value = stateSelect.value;
        document.getElementById('selected_district_id').value = selected ? (selected.dataset.id || '') : '';
        document.getElementById('selected_city_name').value = this.value;
    });

    // Pincode autofill via API
    const pincodeInput = document.getElementById('pincode');
    if (pincodeInput) {
        let pincodeTimer;
        pincodeInput.addEventListener('input', function() {
            clearTimeout(pincodeTimer);
            pincodeTimer = setTimeout(async () => {
                const pincode = this.value.trim();
                if (pincode.length === 6 && /^\d+$/.test(pincode)) {
                    try {
                        const resp = await fetch(BASE_URL + '/api/locations/pincode/' + pincode);
                        const data = await resp.json();
                        if (data.found && data.data) {
                            const cityInput = document.getElementById('city');
                            if (cityInput && data.data.city) cityInput.value = data.data.city;
                            if (data.data.district && districtSelect) {
                                for (let i = 0; i < districtSelect.options.length; i++) {
                                    if (districtSelect.options[i].textContent.includes(data.data.district)) {
                                        districtSelect.value = districtSelect.options[i].value;
                                        districtSelect.dispatchEvent(new Event('change'));
                                        break;
                                    }
                                }
                            }
                        }
                    } catch(e) {
                        console.log('Pincode lookup not available');
                    }
                }
            }, 500);
        });
    }
});

function handleGuestSubmit() {
    const name = document.querySelector('input[name="name"]').value;
    const phone = document.querySelector('input[name="phone"]').value;
    if (!name || !phone) {
        alert('Please fill in your name and phone number first');
        return;
    }
    document.getElementById('qrName').value = name;
    document.getElementById('qrPhone').value = phone;
    document.getElementById('qrEmail').value = document.querySelector('input[name="email"]').value || '';
    document.getElementById('qrReferralCode').value = '';
    const modal = new bootstrap.Modal(document.getElementById('quickRegisterModal'));
    modal.show();
}
</script>

<!-- Include Quick Register Modal -->
<?php include __DIR__ . '/../components/quick_register_modal.php'; ?>