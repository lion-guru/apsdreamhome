<?php
/**
 * List Property Page - Simple & Easy Property Posting
 */
$success = isset($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null;
$error = isset($_SESSION['flash_error']) ? $_SESSION['flash_error'] : null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!-- Hero Section -->
<section class="py-5 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container text-center py-5">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-home me-3"></i>Apni Property Free Mein List Karein</h1>
        <p class="lead">Bas 1 minute mein form fill karein - Aapke property ko buyers dhundhenge!</p>
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
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Property Details Submit Karein</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>/list-property/submit" method="POST">
                            <!-- Property Type -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kya karna hai? *</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" value="sell" id="sell" checked>
                                        <label class="form-check-label" for="sell">
                                            <i class="fas fa-tag text-success me-1"></i> Sell Karna Hai
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listing_type" value="rent" id="rent">
                                        <label class="form-check-label" for="rent">
                                            <i class="fas fa-key text-primary me-1"></i> Rent Karna Hai
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Type -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Property Type *</label>
                                <select name="property_type" class="form-select" required>
                                    <option value="">Select karein...</option>
                                    <option value="plot">Plot (Naksha)</option>
                                    <option value="house">House / Villa</option>
                                    <option value="flat">Flat / Apartment</option>
                                    <option value="shop">Shop</option>
                                    <option value="farmhouse">Farm House</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Property Location *</label>
                                <select name="location" class="form-select" required>
                                    <option value="">Select location...</option>
                                    <option value="Gorakhpur">Gorakhpur</option>
                                    <option value="Kushinagar">Kushinagar</option>
                                    <option value="Lucknow">Lucknow</option>
                                    <option value="Varanasi">Varanasi</option>
                                    <option value="Ayodhya">Ayodhya</option>
                                    <option value="Deoria">Deoria</option>
                                    <option value="Basti">Basti</option>
                                    <option value="Siddharthnagar">Siddharthnagar</option>
                                    <option value="Maharajganj">Maharajganj</option>
                                    <option value="Sant Kabir Nagar">Sant Kabir Nagar</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Price -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Expected Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="text" name="price" class="form-control" placeholder="e.g. 25 Lakh ya 15000/month" required>
                                </div>
                            </div>

                            <!-- Name & Phone -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Your Name *</label>
                                    <input type="text" name="name" class="form-control" placeholder="Naam" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+91 XXXXXXXXXX" required>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Short Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Plot size, road width, kya special hai..."></textarea>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Submit Karo - FREE!
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Info Box -->
                <div class="alert alert-info mt-4">
                    <h5><i class="fas fa-info-circle me-2"></i>Kaise Kaam Karta Hai?</h5>
                    <ol class="mb-0">
                        <li>Aap form submit karte hain</li>
                        <li>Hum aapko call karke verify karte hain</li>
                        <li>Aapki property ko buyers ko dikhate hain</li>
                        <li>Jab buyer milta hai → Aap deal karte hain!</li>
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
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Aapko Milega</h5>
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
                        <h5>Help Chahiye?</h5>
                        <p class="text-muted mb-3">Hum aapki madad ke liye hain!</p>
                        <div class="d-grid gap-2">
                            <a href="tel:+919277121112" class="btn btn-success">
                                <i class="fas fa-phone me-2"></i>Call: +91 92771 21112
                            </a>
                            <a href="https://wa.me/919277121112?text=Hi, I want to list my property for sale" target="_blank" class="btn btn-outline-success">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp Karein
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
        <h3 class="text-center mb-4">Kaise Kaam Karta Hai?</h3>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">1</span>
                </div>
                <h5>Form Fill Karein</h5>
                <p class="text-muted">Bas 1 minute - Name, Phone, Property Details</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">2</span>
                </div>
                <h5>Hum Call Karenge</h5>
                <p class="text-muted">Verification aur property ki details lenge</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <span class="h2 mb-0">3</span>
                </div>
                <h5>Buyer Mil Jayega!</h5>
                <p class="text-muted">Serious buyers aapse contact karenge</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5 text-center text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h3>Abhi Property List Karein!</h3>
        <p class="mb-4">Free hai - Koi charge nahi!</p>
        <a href="#form" class="btn btn-warning btn-lg">
            <i class="fas fa-arrow-up me-2"></i>Form Bharein
        </a>
    </div>
</section>
