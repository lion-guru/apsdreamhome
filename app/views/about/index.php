<?php
//
// ERROR HANDLING CONFIGURATION
//
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function handleError(,  = null,  = null) {
     = date('Y-m-d H:i:s') . ' - ERROR: ' . ;
    if ()  .= ' in ' . ;
    if ()  .= ' on line ' . ;
    error_log();
    return false;
}

function safeExecute(,  = 'Operation failed') {
    try {
        return ();
    } catch (Exception ) {
        handleError( . ': ' . (), (), ());
        return null;
    }
}

//
/**
 * About Page - APS Dream Home
 * Professional company information and team details
 */

// Set page title and description for layout
$page_title = $title ?? 'About Us - APS Dream Home';
$page_description = $description ?? 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-4">
                        About <span class="text-warning">APS Dream Home</span>
                    </h1>
                    <p class="lead mb-4">
                        Leading real estate developer in Gorakhpur with <?php echo $company_info['experience'] ?? '8+ Years'; ?> of excellence in property development and customer satisfaction. Building dreams into reality with trust and innovation.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg">
                            <i class="fas fa-phone me-2"></i>Contact Us
                        </a>
                        <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-home me-2"></i>View Properties
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo BASE_URL; ?>/assets/images/hero-about.jpg" 
                         alt="APS Dream Home" 
                         class="img-fluid rounded shadow-lg"
                         onerror="this.src='https://via.placeholder.com/600x400/667eea/ffffff?text=APS+Dream+Home'">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Company Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Company</h2>
                <p class="lead text-muted">Building trust through excellence and innovation</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary mb-3">
                            <i class="fas fa-bullseye me-2"></i>Our Mission
                        </h4>
                        <p class="card-text">
                            <?php echo $mission ?? 'To provide transparent and hassle-free real estate services with a focus on customer satisfaction and quality construction.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary mb-3">
                            <i class="fas fa-eye me-2"></i>Our Vision
                        </h4>
                        <p class="card-text">
                            <?php echo $vision ?? 'To become the most trusted real estate developer in Uttar Pradesh by delivering excellence in every project.'; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['experience'] ?? '8+ Years'; ?>
                    </div>
                    <div class="text-muted">Experience</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['projects'] ?? '50+'; ?>
                    </div>
                    <div class="text-muted">Projects</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['properties'] ?? '500+'; ?>
                    </div>
                    <div class="text-muted">Properties</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="display-4 fw-bold text-primary mb-2">
                        <?php echo $company_info['happy_families'] ?? '2000+'; ?>
                    </div>
                    <div class="text-muted">Happy Families</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Core Values</h2>
                <p class="lead text-muted">The principles that guide our business</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($values)): ?>
                <?php foreach ($values as $value): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-star text-warning fa-2x"></i>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($value); ?></h5>
                                <p class="card-text text-muted small">
                                    We believe in <?php echo strtolower($value); ?> in everything we do.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Transparency</h5>
                            <p class="card-text text-muted small">Honest dealings and clear communication</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-gem text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Quality</h5>
                            <p class="card-text text-muted small">Excellence in every project</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                            <h5 class="card-title">Customer Satisfaction</h5>
                            <p class="card-text text-muted small">Your success is our priority</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Leadership Team</h2>
                <p class="lead text-muted">Experienced professionals driving our success</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($team)): ?>
                <?php foreach ($team as $member): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4 text-center">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 80px; font-size: 2rem;">
                                        <?php echo substr($member->name, 0, 2); ?>
                                    </div>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($member->name); ?></h5>
                                <div class="text-primary small mb-2"><?php echo htmlspecialchars($member->position); ?></div>
                                <div class="text-muted small mb-2"><?php echo htmlspecialchars($member->experience); ?></div>
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars($member->description); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    AK
                                </div>
                            </div>
                            <h5 class="card-title">Amit Kumar Singh</h5>
                            <div class="text-primary small mb-2">Managing Director</div>
                            <div class="text-muted small mb-2">15+ Years</div>
                            <p class="card-text text-muted small">
                                Leading the company with vision and expertise in real estate development.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    PS
                                </div>
                            </div>
                            <h5 class="card-title">Priya Singh</h5>
                            <div class="text-primary small mb-2">Operations Head</div>
                            <div class="text-muted small mb-2">10+ Years</div>
                            <p class="card-text text-muted small">
                                Managing day-to-day operations with focus on efficiency and quality.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="mb-3">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    RV
                                </div>
                            </div>
                            <h5 class="card-title">Rahul Verma</h5>
                            <div class="text-primary small mb-2">Technical Director</div>
                            <div class="text-muted small mb-2">12+ Years</div>
                            <p class="card-text text-muted small">
                                Ensuring technical excellence and innovation in construction.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Ready to Work With Us?</h2>
                <p class="lead mb-4">
                    Join thousands of satisfied customers who found their perfect property with APS Dream Home.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="<?php echo BASE_URL; ?>contact" class="btn btn-warning btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                    <a href="<?php echo BASE_URL; ?>properties" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-home me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>






























<input type="text"
                                           class="form-control"
                                           id="site_name"
                                           name="site_name"
                                           value="<?php echo htmlspecialchars($settings['site_name']['setting_value'] ?? 'APS Dream Home'); ?>"
                                           placeholder="Enter your site name">
                                </div>

                                <div class="col-md-6">
                                    <label for="site_description" class="form-label fw-medium">Site Description</label>
                                    <textarea class="form-control"
                                              id="site_description"
                                              name="site_description"
                                              rows="3"
                                              placeholder="Brief description of your real estate business"><?php echo htmlspecialchars($settings['site_description']['setting_value'] ?? ''); ?></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_email" class="form-label fw-medium">Contact Email</label>
                                    <input type="email"
                                           class="form-control"
                                           id="contact_email"
                                           name="contact_email"
                                           value="<?php echo htmlspecialchars($settings['contact_email']['setting_value'] ?? ''); ?>"
                                           placeholder="admin@apsdreamhome.com">
                                </div>

                                <div class="col-md-6">
                                    <label for="contact_phone" class="form-label fw-medium">Contact Phone</label>
                                    <input type="tel"
                                           class="form-control"
                                           id="contact_phone"
                                           name="contact_phone"
                                           value="<?php echo htmlspecialchars($settings['contact_phone']['setting_value'] ?? ''); ?>"
                                           placeholder="+91 98765 43210">
                                </div>

                                <div class="col-12">
                                    <label for="site_address" class="form-label fw-medium">Office Address</label>
                                    <textarea class="form-control"
                                              id="site_address"
                                              name="site_address"
                                              rows="3"
                                              placeholder="Complete office address"><?php echo htmlspecialchars($settings['site_address']['setting_value'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-building text-success me-2"></i>
                                Property Settings
                            </h4>
                            <p class="settings-description">Configure property-related settings</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label for="default_currency" class="form-label fw-medium">Default Currency</label>
                                    <select class="form-select" id="default_currency" name="default_currency">
                                        <option value="INR" <?php echo ($settings['default_currency']['setting_value'] ?? 'INR') === 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                        <option value="USD" <?php echo ($settings['default_currency']['setting_value'] ?? 'INR') === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                        <option value="EUR" <?php echo ($settings['default_currency']['setting_value'] ?? 'INR') === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="properties_per_page" class="form-label fw-medium">Properties Per Page</label>
                                    <select class="form-select" id="properties_per_page" name="properties_per_page">
                                        <option value="10" <?php echo ($settings['properties_per_page']['setting_value'] ?? '10') === '10' ? 'selected' : ''; ?>>10</option>
                                        <option value="20" <?php echo ($settings['properties_per_page']['setting_value'] ?? '10') === '20' ? 'selected' : ''; ?>>20</option>
                                        <option value="50" <?php echo ($settings['properties_per_page']['setting_value'] ?? '10') === '50' ? 'selected' : ''; ?>>50</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="max_image_size" class="form-label fw-medium">Max Image Size (MB)</label>
                                    <select class="form-select" id="max_image_size" name="max_image_size">
                                        <option value="5" <?php echo ($settings['max_image_size']['setting_value'] ?? '5') === '5' ? 'selected' : ''; ?>>5 MB</option>
                                        <option value="10" <?php echo ($settings['max_image_size']['setting_value'] ?? '5') === '10' ? 'selected' : ''; ?>>10 MB</option>
                                        <option value="20" <?php echo ($settings['max_image_size']['setting_value'] ?? '5') === '20' ? 'selected' : ''; ?>>20 MB</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="auto_approve_properties"
                                               name="auto_approve_properties"
                                               value="1"
                                               <?php echo ($settings['auto_approve_properties']['setting_value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="auto_approve_properties">
                                            <strong>Auto-approve new properties</strong><br>
                                            <small class="text-muted">Automatically approve properties when submitted by agents</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="require_agent_verification"
                                               name="require_agent_verification"
                                               value="1"
                                               <?php echo ($settings['require_agent_verification']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="require_agent_verification">
                                            <strong>Require agent verification</strong><br>
                                            <small class="text-muted">Require email verification for new agent accounts</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-envelope text-info me-2"></i>
                                Email Settings
                            </h4>
                            <p class="settings-description">Configure email notifications and SMTP settings</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                                    <input type="text"
                                           class="form-control"
                                           id="smtp_host"
                                           name="smtp_host"
                                           value="<?php echo htmlspecialchars($settings['smtp_host']['setting_value'] ?? 'smtp.gmail.com'); ?>"
                                           placeholder="smtp.gmail.com">
                                </div>

                                <div class="col-md-3">
                                    <label for="smtp_port" class="form-label fw-medium">SMTP Port</label>
                                    <input type="number"
                                           class="form-control"
                                           id="smtp_port"
                                           name="smtp_port"
                                           value="<?php echo htmlspecialchars($settings['smtp_port']['setting_value'] ?? '587'); ?>"
                                           placeholder="587">
                                </div>

                                <div class="col-md-3">
                                    <label for="smtp_encryption" class="form-label fw-medium">Encryption</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="none" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_username" class="form-label fw-medium">SMTP Username</label>
                                    <input type="text"
                                           class="form-control"
                                           id="smtp_username"
                                           name="smtp_username"
                                           value="<?php echo htmlspecialchars($settings['smtp_username']['setting_value'] ?? ''); ?>"
                                           placeholder="your-email@gmail.com">
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_password" class="form-label fw-medium">SMTP Password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="smtp_password"
                                           name="smtp_password"
                                           value="<?php echo htmlspecialchars($settings['smtp_password']['setting_value'] ?? ''); ?>"
                                           placeholder="App password or SMTP password">
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="email_notifications"
                                               name="email_notifications"
                                               value="1"
                                               <?php echo ($settings['email_notifications']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_notifications">
                                            <strong>Enable email notifications</strong><br>
                                            <small class="text-muted">Send email notifications for new inquiries and registrations</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-search text-warning me-2"></i>
                                SEO Settings
                            </h4>
                            <p class="settings-description">Search engine optimization settings</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="meta_title" class="form-label fw-medium">Default Meta Title</label>
                                    <input type="text"
                                           class="form-control"
                                           id="meta_title"
                                           name="meta_title"
                                           value="<?php echo htmlspecialchars($settings['meta_title']['setting_value'] ?? 'APS Dream Home - Find Your Perfect Property'); ?>"
                                           placeholder="Default page title for SEO">
                                </div>

                                <div class="col-md-6">
                                    <label for="meta_description" class="form-label fw-medium">Default Meta Description</label>
                                    <textarea class="form-control"
                                              id="meta_description"
                                              name="meta_description"
                                              rows="3"
                                              placeholder="Default meta description for SEO"><?php echo htmlspecialchars($settings['meta_description']['setting_value'] ?? ''); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label for="meta_keywords" class="form-label fw-medium">Default Meta Keywords</label>
                                    <input type="text"
                                           class="form-control"
                                           id="meta_keywords"
                                           name="meta_keywords"
                                           value="<?php echo htmlspecialchars($settings['meta_keywords']['setting_value'] ?? 'real estate, property, buy, sell, rent, apartments, houses'); ?>"
                                           placeholder="Comma-separated keywords">
                                    <div class="form-text">Separate keywords with commas</div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="enable_analytics"
                                               name="enable_analytics"
                                               value="1"
                                               <?php echo ($settings['enable_analytics']['setting_value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_analytics">
                                            <strong>Enable Google Analytics</strong><br>
                                            <small class="text-muted">Track website visitors and user behavior</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-share-alt text-danger me-2"></i>
                                Social Media Settings
                            </h4>
                            <p class="settings-description">Configure social media links and sharing</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="facebook_url" class="form-label fw-medium">Facebook URL</label>
                                    <input type="url"
                                           class="form-control"
                                           id="facebook_url"
                                           name="facebook_url"
                                           value="<?php echo htmlspecialchars($settings['facebook_url']['setting_value'] ?? ''); ?>"
                                           placeholder="https://facebook.com/apsdreamhome">
                                </div>

                                <div class="col-md-6">
                                    <label for="twitter_url" class="form-label fw-medium">Twitter URL</label>
                                    <input type="url"
                                           class="form-control"
                                           id="twitter_url"
                                           name="twitter_url"
                                           value="<?php echo htmlspecialchars($settings['twitter_url']['setting_value'] ?? ''); ?>"
                                           placeholder="https://twitter.com/apsdreamhome">
                                </div>

                                <div class="col-md-6">
                                    <label for="instagram_url" class="form-label fw-medium">Instagram URL</label>
                                    <input type="url"
                                           class="form-control"
                                           id="instagram_url"
                                           name="instagram_url"
                                           value="<?php echo htmlspecialchars($settings['instagram_url']['setting_value'] ?? ''); ?>"
                                           placeholder="https://instagram.com/apsdreamhome">
                                </div>

                                <div class="col-md-6">
                                    <label for="linkedin_url" class="form-label fw-medium">LinkedIn URL</label>
                                    <input type="url"
                                           class="form-control"
                                           id="linkedin_url"
                                           name="linkedin_url"
                                           value="<?php echo htmlspecialchars($settings['linkedin_url']['setting_value'] ?? ''); ?>"
                                           placeholder="https://linkedin.com/company/apsdreamhome">
                                </div>

                                <div class="col-md-6">
                                    <label for="youtube_url" class="form-label fw-medium">YouTube URL</label>
                                    <input type="url"
                                           class="form-control"
                                           id="youtube_url"
                                           name="youtube_url"
                                           value="<?php echo htmlspecialchars($settings['youtube_url']['setting_value'] ?? ''); ?>"
                                           placeholder="https://youtube.com/apsdreamhome">
                                </div>

                                <div class="col-md-6">
                                    <label for="whatsapp_number" class="form-label fw-medium">WhatsApp Number</label>
                                    <input type="tel"
                                           class="form-control"
                                           id="whatsapp_number"
                                           name="whatsapp_number"
                                           value="<?php echo htmlspecialchars($settings['whatsapp_number']['setting_value'] ?? ''); ?>"
                                           placeholder="+91 98765 43210">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-shield-alt text-dark me-2"></i>
                                Security Settings
                            </h4>
                            <p class="settings-description">Configure security and access control settings</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="session_timeout" class="form-label fw-medium">Session Timeout (minutes)</label>
                                    <select class="form-select" id="session_timeout" name="session_timeout">
                                        <option value="30" <?php echo ($settings['session_timeout']['setting_value'] ?? '30') === '30' ? 'selected' : ''; ?>>30 minutes</option>
                                        <option value="60" <?php echo ($settings['session_timeout']['setting_value'] ?? '30') === '60' ? 'selected' : ''; ?>>1 hour</option>
                                        <option value="120" <?php echo ($settings['session_timeout']['setting_value'] ?? '30') === '120' ? 'selected' : ''; ?>>2 hours</option>
                                        <option value="480" <?php echo ($settings['session_timeout']['setting_value'] ?? '30') === '480' ? 'selected' : ''; ?>>8 hours</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="max_login_attempts" class="form-label fw-medium">Max Login Attempts</label>
                                    <select class="form-select" id="max_login_attempts" name="max_login_attempts">
                                        <option value="3" <?php echo ($settings['max_login_attempts']['setting_value'] ?? '5') === '3' ? 'selected' : ''; ?>>3 attempts</option>
                                        <option value="5" <?php echo ($settings['max_login_attempts']['setting_value'] ?? '5') === '5' ? 'selected' : ''; ?>>5 attempts</option>
                                        <option value="10" <?php echo ($settings['max_login_attempts']['setting_value'] ?? '5') === '10' ? 'selected' : ''; ?>>10 attempts</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="enable_maintenance_mode"
                                               name="enable_maintenance_mode"
                                               value="1"
                                               <?php echo ($settings['enable_maintenance_mode']['setting_value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_maintenance_mode">
                                            <strong>Enable maintenance mode</strong><br>
                                            <small class="text-muted">Show maintenance page to visitors (except admins)</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="enable_registration"
                                               name="enable_registration"
                                               value="1"
                                               <?php echo ($settings['enable_registration']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="enable_registration">
                                            <strong>Allow user registration</strong><br>
                                            <small class="text-muted">Allow new users to register accounts</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Email Settings
                            </h4>
                            <p class="settings-description">Configure SMTP settings for email notifications</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="smtp_host" class="form-label fw-medium">SMTP Host</label>
                                    <input type="text"
                                           class="form-control"
                                           id="smtp_host"
                                           name="smtp_host"
                                           value="<?php echo htmlspecialchars($settings['smtp_host']['setting_value'] ?? 'smtp.gmail.com'); ?>"
                                           placeholder="smtp.gmail.com">
                                    <div class="form-text">SMTP server hostname (e.g., smtp.gmail.com)</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_port" class="form-label fw-medium">SMTP Port</label>
                                    <select class="form-select" id="smtp_port" name="smtp_port">
                                        <option value="587" <?php echo ($settings['smtp_port']['setting_value'] ?? '587') === '587' ? 'selected' : ''; ?>>587 (TLS)</option>
                                        <option value="465" <?php echo ($settings['smtp_port']['setting_value'] ?? '587') === '465' ? 'selected' : ''; ?>>465 (SSL)</option>
                                        <option value="25" <?php echo ($settings['smtp_port']['setting_value'] ?? '587') === '25' ? 'selected' : ''; ?>>25 (Unencrypted)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_username" class="form-label fw-medium">SMTP Username</label>
                                    <input type="email"
                                           class="form-control"
                                           id="smtp_username"
                                           name="smtp_username"
                                           value="<?php echo htmlspecialchars($settings['smtp_username']['setting_value'] ?? ''); ?>"
                                           placeholder="your-email@gmail.com">
                                    <div class="form-text">Your email address for SMTP authentication</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_password" class="form-label fw-medium">SMTP Password</label>
                                    <input type="password"
                                           class="form-control"
                                           id="smtp_password"
                                           name="smtp_password"
                                           value="<?php echo htmlspecialchars($settings['smtp_password']['setting_value'] ?? ''); ?>"
                                           placeholder="Your app password">
                                    <div class="form-text">App password or email password</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="smtp_encryption" class="form-label fw-medium">Encryption</label>
                                    <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                        <option value="tls" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="none" <?php echo ($settings['smtp_encryption']['setting_value'] ?? 'tls') === 'none' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               id="email_notifications"
                                               name="email_notifications"
                                               value="1"
                                               <?php echo ($settings['email_notifications']['setting_value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="email_notifications">
                                            <strong>Enable email notifications</strong><br>
                                            <small class="text-muted">Send email notifications for inquiries, registrations, etc.</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Note:</strong> For Gmail, use an "App Password" instead of your regular password.
                                        <a href="https://support.google.com/accounts/answer/185833" target="_blank" class="alert-link">Learn how to generate an App Password</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="form-actions text-center pt-4 border-top">
                        <button type="submit" class="btn btn-success btn-lg me-3">
                            <i class="fas fa-save me-2"></i>
                            Save All Settings
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm()">
                            <i class="fas fa-undo me-2"></i>
                            Reset Form
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section
<a class="dropdown-item update-status" href="#" data-id="<?php echo $visit['id']; ?>" data-status="completed">Mark Completed</a></li>
                                                    <li><a class="dropdown-item update-status" href="#" data-id="<?php echo $visit['id']; ?>" data-status="cancelled">Cancel Visit</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No upcoming visits found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section
<pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .blog-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .blog-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .featured-post {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .featured-post:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .category-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .reading-time {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .blog-stats {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            padding: 2rem;
            margin: -2rem 0 2rem 0;
            position: relative;
            z-index: 10;
        }

        .stat-item {
            text-align: center;
        }

        .recent-post {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .recent-post:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .recent-post-image {
            width: 120px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
        }

        .recent-post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tag-cloud a {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .tag-cloud a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .sidebar-widget {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-widget h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .category-list li:last-child {
            border-bottom: none;
        }

        .category-list a {
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            justify-content: between;
            align-items: center;
            transition: color 0.3s ease;
        }

        .category-list a:hover {
            color: var(--primary-color);
        }

        .post-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .search-form {
            position: relative;
        }

        .search-form input {
            padding-right: 45px;
            border-radius: 25px;
            border: 2px solid #e5e7eb;
            transition: border-color 0.3s ease;
        }

        .search-form input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background: #1e3a8a;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 0;
            }
            
            .blog-card img {
                height: 150px;
            }
            
            .recent-post-image {
                width: 80px;
                height: 60px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="http://localhost/apsdreamhome/public">
                <img src="http://localhost/apsdreamhome/public/assets/images/logo/apslogo.png" alt="APS Dream Home" height="40" class="me-2">
                APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="http://localhost/apsdreamhome/public/blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="http://localhost/apsdreamhome/public/contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-white py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold mb-4">Blog</h1>
                        <p class="lead mb-4"><?php echo htmlspecialchars($page_description); ?></p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="http://localhost/apsdreamhome/public/contact" class="btn btn-light btn-lg">Get Expert Advice</a>
                            <a href="http://localhost/apsdreamhome/public/properties" class="btn btn-outline-light btn-lg">Browse Properties</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <img src="http://localhost/apsdreamhome/public/assets/images/blog-hero.jpg" alt="Blog" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section
<button class="btn btn-outline-primary" id="load-more-posts">Load More Articles</button>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Popular Tags -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Popular Tags</h5>
                        <div class="tag-cloud">
                            @foreach($popular_tags ?? [] as $tag)
                            <a href="#" class="badge bg-light text-dark text-decoration-none me-2 mb-2 d-inline-block">
                                #{{ $tag['name'] }} ({{ $tag['count'] }})
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Newsletter Subscribe -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Subscribe to Newsletter</h5>
                        <p class="text-muted small mb-3">Get latest articles and market insights delivered to your inbox</p>
                        <form class="newsletter-form">
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Your email address" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Quick Links</h5>
                        <div class="list-group list-group-flush">
                            <a href="{{ url('/properties') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-home me-2"></i> Browse Properties
                            </a>
                            <a href="{{ url('/projects') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-building me-2"></i> Our Projects
                            </a>
                            <a href="{{ url('/gallery') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-images me-2"></i> Gallery
                            </a>
                            <a href="{{ url('/contact') }}" class="list-group-item list-group-item-action border-0 px-0">
                                <i class="fas fa-phone me-2"></i> Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section
<textarea name="message" class="form-control" id="message" rows="4" placeholder="Tell us about yourself..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section









<div id="map-<?php echo $index; ?>" style="height: 250px; border-radius: 8px;" 
                                     onclick="openGoogleMaps('<?php echo htmlspecialchars($location->address ?? $location['address'] ?? ''); ?>')"
                                     style="cursor: pointer;">
                                </div>
                                <small class="text-muted">Click on map to get directions</small>
                            </div>
                            
                            <p class="card-text">
                                <strong>Address:</strong><br>
                                <?php echo htmlspecialchars($location->address ?? $location['address'] ?? ''); ?><br><br>
                                <strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($location->phone ?? $location['phone'] ?? ''); ?>"><?php echo htmlspecialchars($location->phone ?? $location['phone'] ?? ''); ?></a><br>
                                <strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($location->email ?? $location['email'] ?? ''); ?>"><?php echo htmlspecialchars($location->email ?? $location['email'] ?? ''); ?></a>
                            </p>
                            <p class="text-muted small"><?php echo htmlspecialchars($location->hours ?? $location['hours'] ?? ''); ?></p>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button onclick="openGoogleMaps('<?php echo htmlspecialchars($location->address ?? $location['address'] ?? ''); ?>')" 
                                        class="btn btn-primary btn-sm">
                                    <i class="fas fa-map-marked-alt me-1"></i>Get Directions
                                </button>
                                <button onclick="shareLocation('<?php echo htmlspecialchars($location->name ?? $location['name'] ?? ''); ?>', '<?php echo htmlspecialchars($location->address ?? $location['address'] ?? ''); ?>')" 
                                        class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-share-alt me-1"></i>Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section
<select class="form-select" id="subject" name="subject" required>
                                    <option value="">Choose...</option>
                                    <option value="property_inquiry">Property Inquiry</option>
                                    <option value="investment">Investment Opportunity</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title text-primary mb-4">Frequently Asked Questions</h3>
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading1">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        How do I search for properties?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse"
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You can search for properties using our advanced search filters on the Properties page. Filter by location, price range, property type, and more.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading2">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        What documents do I need to buy a property?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse"
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You'll need identity proof, address proof, income proof, and other standard documentation. Our team will guide you through the complete process.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading3">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#collapse3">
                                        Do you provide home loans?
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse"
                                     data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Yes, we work with leading banks and financial institutions to help you secure home loans at competitive interest rates.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section


















<input type="text" class="form-control" id="faqSearch" placeholder="Search for questions...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="btn-group flex-wrap" role="group">
                    <button type="button" class="btn btn-outline-primary active mb-2" data-category="all">All</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="General">General</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="Booking">Booking</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="Legal">Legal</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="Finance">Finance</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="Pricing">Pricing</button>
                    <button type="button" class="btn btn-outline-primary mb-2" data-category="Support">Support</button>
                </div>
            </div>
        </div>

        <!-- FAQ Items -->
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="faqAccordion">
                    <?php if (!empty($faqs)): ?>
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="accordion-item border-0 shadow-sm mb-3 faq-item" data-category="<?php echo htmlspecialchars($faq['category']); ?>">
                                <h2 class="accordion-header" id="faq<?php echo $faq['id']; ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center w-100">
                                            <span><?php echo htmlspecialchars($faq['question']); ?></span>
                                            <span class="badge bg-primary ms-3"><?php echo htmlspecialchars($faq['category']); ?></span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $faq['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><?php echo htmlspecialchars($faq['answer']); ?></p>
                                        <div class="mt-3">
                                            <button class="btn btn-sm btn-outline-primary" onclick="markHelpful(<?php echo $faq['id']; ?>)">
                                                <i class="fas fa-thumbs-up me-1"></i> Helpful
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="markNotHelpful(<?php echo $faq['id']; ?>)">
                                                <i class="fas fa-thumbs-down me-1"></i> Not Helpful
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p class="text-muted lead">No FAQs available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Still Have Questions -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-3">Still Have Questions?</h3>
                        <p class="mb-4 opacity-75">Can't find what you're looking for? Our team is here to help!</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="tel:+917007444842" class="btn btn-light btn-lg px-4 rounded-pill text-primary fw-bold">
                                <i class="fas fa-phone me-2"></i>Call Us
                            </a>
                            <a href="<?php echo BASE_URL; ?>contact" class="btn btn-outline-light btn-lg px-4 rounded-pill fw-bold">
                                <i class="fas fa-envelope me-2"></i>Email Us
                            </a>
                            <a href="https://wa.me/917007444842" class="btn btn-outline-light btn-lg px-4 rounded-pill fw-bold">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section
<div class="gallery-grid" id="galleryGrid">
                <?php foreach ($gallery_items as $item): ?>
                    <div class="gallery-item" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-type="<?php echo htmlspecialchars($item['type']); ?>">
                        <div class="gallery-card">
                            <div class="gallery-image-container">
                                <?php if ($item['type'] === 'video'): ?>
                                    <div class="video-thumbnail">
                                        <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                             class="gallery-image">
                                        <div class="video-overlay">
                                            <div class="play-button">
                                                <i class="fas fa-play"></i>
                                            </div>
                                        </div>
                                        <div class="video-duration">3:45</div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars($item['url']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                                         class="gallery-image">
                                    <div class="image-overlay">
                                        <div class="overlay-content">
                                            <i class="fas fa-expand"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="item-badge">
                                    <?php if ($item['category'] === 'completed'): ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Ongoing</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="gallery-content">
                                <h4 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="gallery-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                
                                <div class="gallery-actions">
                                    <?php if ($item['type'] === 'video'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="openVideoModal('<?php echo htmlspecialchars($item['url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                            <i class="fas fa-play me-1"></i>Watch Video
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary btn-sm" onclick="openImageModal('<?php echo htmlspecialchars($item['url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                                            <i class="fas fa-expand me-1"></i>View Full
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($item['project_id'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/gallery/project/<?php echo $item['project_id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-images me-1"></i>Project Gallery
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-images fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted">No Gallery Items Found</h3>
                    <p class="text-muted">No items found in this category. Please try another category.</p>
                    <a href="<?php echo BASE_URL; ?>/gallery?category=all" class="btn btn-primary mt-3">
                        <i class="fas fa-th me-2"></i>View All Items
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section







<ul class="nav nav-pills justify-content-center" id="projectTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">All Projects</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ongoing-tab" data-bs-toggle="tab" data-bs-target="#ongoing" type="button">Ongoing</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button">Completed</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="residential-tab" data-bs-toggle="tab" data-bs-target="#residential" type="button">Residential</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="commercial-tab" data-bs-toggle="tab" data-bs-target="#commercial" type="button">Commercial</button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="tab-content" id="projectTabContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <div class="row g-4">
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <div class="col-lg-6 col-md-12">
                                <div class="card border-0 shadow-sm h-100 project-card">
                                    <div class="row g-0">
                                        <div class="col-md-4">
                                            <img src="<?php echo BASE_URL . ($project['image'] ?? '/assets/images/project-placeholder.jpg'); ?>" 
                                                 class="img-fluid rounded-start h-100" style="object-fit: cover;" 
                                                 alt="<?php echo htmlspecialchars($project['name']); ?>">
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($project['name']); ?></h5>
                                                    <?php if ($project['featured'] ?? false): ?>
                                                        <span class="badge bg-primary">Featured</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($project['location']); ?>
                                                </p>
                                                
                                                <div class="d-flex gap-3 mb-3">
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($project['type']); ?></span>
                                                    <span class="badge <?php echo $project['status'] === 'Completed' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo htmlspecialchars($project['status']); ?>
                                                    </span>
                                                </div>
                                                
                                                <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted">Units</small>
                                                        <p class="fw-bold"><?php echo htmlspecialchars($project['units']); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Price Range</small>
                                                        <p class="fw-bold text-primary"><?php echo htmlspecialchars($project['price_range']); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($project['status'] === 'Ongoing'): ?>
                                                    <div class="mb-3">
                                                        <small class="text-muted">Progress</small>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                                 style="width: <?php echo $project['completion']; ?>%;" 
                                                                 aria-valuenow="<?php echo $project['completion']; ?>" 
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                                <?php echo $project['completion']; ?>%
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-primary btn-sm" onclick="viewProjectDetails(<?php echo $project['id']; ?>)">
                                                        View Details
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" onclick="downloadBrochure(<?php echo $project['id']; ?>)">
                                                        Download Brochure
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <p class="text-muted lead">No projects available at the moment.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Other tabs will have similar content filtered by category -->
            <div class="tab-pane fade" id="ongoing" role="tabpanel">
                <div class="row g-4">
                    <?php foreach (array_filter($projects, fn($p) => $p['status'] === 'Ongoing') as $project): ?>
                        <!-- Same project card structure as above -->
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="tab-pane fade" id="completed" role="tabpanel">
                <div class="row g-4">
                    <?php foreach (array_filter($projects, fn($p) => $p['status'] === 'Completed') as $project): ?>
                        <!-- Same project card structure as above -->
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section


<section id="leadership" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Leadership Team</h2>
            <p class="lead text-muted">Meet the visionary leaders driving APS Dream Home's success</p>
        </div>
        
        <div class="row g-4">
            @foreach($leadership_team ?? [] as $leader)
            <div class="col-lg-6">
                <div class="leader-card card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="leader-image text-center mb-3 mb-md-0">
                                    <img src="{{ asset('images/' . ($leader['image'] ?? 'team/default.jpg')) }}" 
                                         alt="{{ $leader['name'] }}" 
                                         class="rounded-circle img-fluid"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <h4 class="fw-bold mb-1">{{ $leader['name'] }}</h4>
                                <h5 class="text-primary mb-3">{{ $leader['position'] }}</h5>
                                
                                <p class="text-muted mb-3">{{ $leader['bio'] }}</p>
                                
                                <div class="leader-details mb-3">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-graduation-cap"></i> {{ $leader['education'] }}
                                            </small>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-briefcase"></i> {{ $leader['experience'] }} Experience
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="achievements mb-3">
                                    <h6 class="fw-bold mb-2">Key Achievements:</h6>
                                    <ul class="small text-muted mb-0">
                                        @foreach($leader['achievements'] as $achievement)
                                        <li>{{ $achievement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="leader-contact">
                                    <div class="d-flex gap-3">
                                        <a href="mailto:{{ $leader['email'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                        <a href="tel:{{ $leader['phone'] }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-phone"></i> Call
                                        </a>
                                        @if(isset($leader['linkedin']))
                                        <a href="{{ $leader['linkedin'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fab fa-linkedin"></i> LinkedIn
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\settings\index.php

function resetForm() {
    if (confirm('Are you sure you want to reset all form fields?')) {
        location.reload();
    }
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to defaults?\n\nThis action cannot be undone.')) {
        // Submit form with reset action
        const form = document.querySelector('.settings-form');
        const resetInput = document.createElement('input');
        resetInput.type = 'hidden';
        resetInput.name = 'reset_to_defaults';
        resetInput.value = '1';
        form.appendChild(resetInput);
        form.submit();
    }
function autoSaveDraft() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        // Could implement auto-save to draft here
        console.log('Auto-saving settings draft...');
    }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\property\index.php

function changeSort(sortValue) {
        const url = new URL(window.location);
        url.searchParams.set('sort', sortValue);
        window.location.href = url.toString();
    }
function showQuickView(propertyId) {
        // Show loading in modal
        document.getElementById('propertyModalContent').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading property details...</p>
            </div>
        `;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('propertyModal'));
        modal.show();

        // Fetch property details
        fetch(`<?php echo BASE_URL; ?>api/property/${propertyId}
function displayPropertyModal(property) {
        const modalContent = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${property.main_image || 'https://via.placeholder.com/400x300/667eea/ffffff?text=No+Image'}
function loadMoreProperties() {
        currentPage++;
        const loadMoreBtn = event.target;
        const originalText = loadMoreBtn.innerHTML;

        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
        loadMoreBtn.disabled = true;

        // Simulate loading more properties
        setTimeout(() => {
            loadMoreBtn.innerHTML = originalText;
            loadMoreBtn.disabled = false;
            // In a real implementation, this would make an AJAX call to load more properties
        }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\payments\index.php

function viewPayment(id) {
        var modal = new bootstrap.Modal(document.getElementById('viewPaymentModal'));
        modal.show();

        $('#viewPaymentBody').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');

        $.ajax({
            url: '/admin/payments/show/' + id,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var p = response.data;
                    var html = `
                        <table class="table table-bordered">
                            <tr>
                                <th width="35%">Transaction ID</th>
                                <td>${p.transaction_id || 'N/A'}
function getStatusColor(status) {
        switch (status) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'failed':
                return 'danger';
            default:
                return 'secondary';
        }
function deletePayment(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\contact\index.php

function openGoogleMaps(address) {
    const encodedAddress = encodeURIComponent(address);
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}
function shareLocation(name, address) {
    const shareText = `Visit ${name}
function initGoogleMaps() {
    <?php if (isset($offices) && is_array($offices)): ?>
        <?php foreach($offices as $index => $location): ?>
            // Initialize map for <?php echo $index; ?>
            const mapDiv<?php echo $index; ?> = document.getElementById('map-<?php echo $index; ?>');
            if (mapDiv<?php echo $index; ?>) {
                // Static map image as fallback
                const address = '<?php echo htmlspecialchars($location->address ?? $location['address'] ?? ''); ?>';
                mapDiv<?php echo $index; ?>.innerHTML = `
                    <div style="width: 100%; height: 100%; background: url('https://maps.googleapis.com/maps/api/staticmap?center=${encodeURIComponent(address)}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\gallery\index.php

function openImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalImage').alt = title;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
function openVideoModal(videoSrc, title) {
    document.getElementById('modalVideo').src = videoSrc;
    new bootstrap.Modal(document.getElementById('videoModal')).show();
}
function startVirtualTour() {
    // Placeholder for virtual tour functionality
    alert('Virtual tour feature coming soon! Please contact us for site visits.');
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\chatbot\index.php

function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage('user', message);
            messageInput.value = '';

            // Show typing indicator
            typingIndicator.style.display = 'block';
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Send to server
            fetch('<?php echo BASE_URL; ?>api/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
function sendQuickReply(message) {
            messageInput.value = message;
            sendMessage();
        }
function addMessage(type, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}
function addBotResponse(data) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot';

            let content = `<div class="fw-bold">APS Assistant</div><div>${data.response}
function handleAction(action) {
            switch (action) {
                case 'search_more':
                    sendQuickReply('Show me more properties');
                    break;
                case 'contact_agent':
                    sendQuickReply('I want to contact an agent');
                    break;
                case 'show_emi':
                    sendQuickReply('Show me EMI calculator');
                    break;
                case 'schedule_visit':
                    sendQuickReply('Schedule a property visit');
                    break;
                default:
                    console.log('Unknown action:', action);
            }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\projects\index.php

function viewProjectDetails(projectId) {
    // Show project details in modal or navigate to detail page
    alert('Project details feature coming soon! Project ID: ' + projectId);
}
function downloadBrochure(projectId) {
    // Download project brochure
    alert('Brochure download feature coming soon! Project ID: ' + projectId);
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\employees\leaves\index.php

function applyLeave() {
    const modal = new bootstrap.Modal(document.getElementById('applyLeaveModal'));
    modal.show();
}
function updateLeaveBalance() {
    // This would update the leave balance display based on selected type
    // For now, it's handled by the static values in the options
}
function cancelLeave(leaveId) {
    if (!confirm('Are you sure you want to cancel this leave application?')) {
        return;
    }
function viewLeaveDetails(leaveId) {
    // This would fetch leave details via AJAX
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    document.getElementById('leaveDetailsContent').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Leave details functionality would be implemented here with AJAX calls to fetch full leave information.
        </div>
    `;
    modal.show();
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\employees\tasks\index.php

function updateTaskStatus(taskId, status) {
    fetch(`<?php echo htmlspecialchars(route('employee.tasks') ); ?>/${taskId}
function viewTaskDetails(taskId) {
    // This would typically fetch task details via AJAX
    // For now, just show a placeholder
    const modal = new bootstrap.Modal(document.getElementById('taskDetailsModal'));
    document.getElementById('taskDetailsContent').innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Task details functionality would be implemented here with AJAX calls to fetch full task information.
        </div>
    `;
    modal.show();
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\employees\attendance\index.php

function markAttendance() {
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    modal.show();
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\resell\index.php

function viewResellDetails(propertyId) {
    // Show property details
    alert('Resell property details feature coming soon! Property ID: ' + propertyId);
}
function scheduleVisit(propertyId) {
    // Schedule property visit
    alert('Schedule visit feature coming soon! Property ID: ' + propertyId);
}
function calculateEMI(propertyId) {
    // Calculate EMI
    alert('EMI calculator feature coming soon! Property ID: ' + propertyId);
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\leads\index.php

function deleteLead(leadId) {
            if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                // Here you would make an AJAX call to delete the lead
                alert('Delete functionality would be implemented here');
            }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\careers\index.php

function viewDetails(positionId) {
    // Show position details in modal or expand section
    alert('Position details feature coming soon! Position ID: ' + positionId);
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\customers\index.php

function deleteCustomer(id, name) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteModalBody').innerHTML = '<?php echo h($mlSupport->translate('Are you sure you want to delete customer')); ?> <strong>' + name + '</strong>? <?php echo h($mlSupport->translate('This action cannot be undone.')); ?>';
        document.getElementById('deleteForm').action = '/admin/customers/delete/' + id;
        modal.show();
    }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\payment\index.php

function verifyPayment(response) {
            fetch('<?= BASE_URL ?>/payment/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\faq\index.php

function markHelpful(faqId) {
    // Mark FAQ as helpful (you can implement AJAX call here)
    alert('Thank you for your feedback!');
}
function markNotHelpful(faqId) {
    // Mark FAQ as not helpful (you can implement AJAX call here)
    alert('Thank you for your feedback! We\'ll improve this answer.');
}

// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\admin\properties\index.php

function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this property?')) {
            document.getElementById('delete-form-' + id).submit();
        }
//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 2044 lines. Consider optimizations:
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