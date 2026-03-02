<?php
/**
 * Settings Management Template
 * Admin interface for managing system settings
 */

?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-cog me-2"></i>
                    System Settings
                </h1>
                <p class="mb-0 opacity-75">Configure your APS Dream Home system settings</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <button type="button" class="btn btn-light btn-lg" onclick="resetToDefaults()">
                    <i class="fas fa-undo me-2"></i>Reset to Defaults
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Settings Management -->
<section class="settings-management py-5">
    <div class="container">
        <!-- Success/Error Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>admin/settings/save" method="POST" class="settings-form">
            <!-- General Settings -->
            <div class="row">
                <div class="col-12">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="settings-title">
                                <i class="fas fa-home text-primary me-2"></i>
                                General Settings
                            </h4>
                            <p class="settings-description">Basic system configuration</p>
                        </div>

                        <div class="settings-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="site_name" class="form-label fw-medium">Site Name</label>
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
</section>

<script>
// Reset form to original values
function resetForm() {
    if (confirm('Are you sure you want to reset all form fields?')) {
        location.reload();
    }
}

// Reset to default settings
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
}

// Auto-save draft functionality (optional)
let autoSaveTimer;
function autoSaveDraft() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        // Could implement auto-save to draft here
        console.log('Auto-saving settings draft...');
    }, 5000);
}

// Listen for form changes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.settings-form');
    const inputs = form.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        input.addEventListener('input', autoSaveDraft);
        input.addEventListener('change', autoSaveDraft);
    });
});
</script>
