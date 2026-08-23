<?php
$page_title = $page_title ?? 'Site Settings';
$settings = $settings ?? [];
$active_tab = $active_tab ?? 'general';

function sc($settings, $key, $default = '') {
    return htmlspecialchars($settings[$key] ?? $default);
}

$tabs = [
    'general'  => ['icon' => 'fa-cog',     'label' => 'General'],
    'contact'  => ['icon' => 'fa-phone',   'label' => 'Contact'],
    'social'   => ['icon' => 'fa-share-alt','label' => 'Social Media'],
    'seo'      => ['icon' => 'fa-search',  'label' => 'SEO'],
    'footer'   => ['icon' => 'fa-shoe-prints','label' => 'Footer'],
    'hero'     => ['icon' => 'fa-image',   'label' => 'Homepage'],
    'widget'   => ['icon' => 'fa-comments', 'label' => 'Widgets'],
    'email'    => ['icon' => 'fa-envelope', 'label' => 'Email/SMTP'],
    'payment'  => ['icon' => 'fa-credit-card','label' => 'Payments'],
    'sms'      => ['icon' => 'fa-sms',     'label' => 'SMS & WhatsApp'],
];
?>

<style>
.site-settings-wrap .nav-tabs { border-bottom: 2px solid #e9ecef; }
.site-settings-wrap .nav-tabs .nav-link {
    font-weight: 500; color: #6c757d; border: none;
    border-bottom: 3px solid transparent; padding: 12px 20px; font-size: 0.95rem;
    transition: all 0.2s;
}
.site-settings-wrap .nav-tabs .nav-link:hover { color: #495057; border-bottom-color: #dee2e6; }
.site-settings-wrap .nav-tabs .nav-link.active {
    color: var(--primary); border-bottom-color: var(--primary);
    background: transparent; font-weight: 600;
}
.site-settings-wrap .settings-group { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; padding: 24px; margin-bottom: 20px; }
.site-settings-wrap .settings-group h6 { color: var(--primary); font-weight: 600; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f0; }
.site-settings-wrap .form-label { font-weight: 500; color: #495057; font-size: 0.9rem; }
.site-settings-wrap .form-text { font-size: 0.8rem; }
.site-settings-wrap .img-preview { max-height: 60px; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px; }
.site-settings-wrap .social-input-group { display: flex; align-items: center; gap: 10px; }
.site-settings-wrap .social-input-group .input-group-text { min-width: 44px; justify-content: center; font-size: 1.1rem; border-radius: 8px 0 0 8px; }
.site-settings-wrap .save-bar { background: #fff; border-top: 1px solid #e9ecef; padding: 16px 0; position: sticky; bottom: 0; z-index: 10; margin: 0 -16px; padding-left: 16px; padding-right: 16px; }
</style>

<div class="container-fluid py-4 site-settings-wrap">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h3 mb-0"><i class="fas fa-cog me-2 text-primary"></i><?= $page_title ?></h3>
            <small class="text-muted">Manage all site-wide content, branding, and SEO from one place</small>
        </div>
        <a href="<?= BASE_URL ?>/admin/site-content" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-layer-group me-1"></i> Content Manager
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <?php foreach ($tabs as $tabId => $tabInfo): ?>
        <li class="nav-item" role="presentation">
            <a class="nav-link <?= $active_tab === $tabId ? 'active' : '' ?>" href="?tab=<?= $tabId ?>" role="tab">
                <i class="fas <?= $tabInfo['icon'] ?> me-1"></i><?= $tabInfo['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <!-- Tab Content -->
    <form action="<?= BASE_URL ?>/admin/site-settings/update" method="POST" enctype="multipart/form-data" id="settingsForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <input type="hidden" name="active_tab" value="<?= htmlspecialchars($active_tab ?? '') ?>">

        <?php if ($active_tab === 'general'): ?>
        <!-- •�•�•� GENERAL TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-8">
                <div class="settings-group">
                    <h6><i class="fas fa-building me-2"></i>Company Identity</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="settings[company_name]" class="form-control" value="<?= sc($settings, 'company_name', 'APS Dream Home') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Logo</label>
                            <?php if (!empty($settings['company_logo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($settings['company_logo'], '/'))): ?>
                                <div class="mb-2"><img src="<?= BASE_URL ?>/<?= sc($settings, 'company_logo') ?>" class="img-preview" alt="Logo"></div>
                            <?php elseif (!empty($settings['company_logo'])): ?>
                                <div class="mb-2"><div class="img-preview d-flex align-items-center justify-content-center bg-light border rounded" class="style-84517"><small class="text-muted">Logo file missing</small></div></div>
                            <?php endif; ?>
                            <input type="file" name="settings_image[company_logo]" class="form-control" accept="image/*">
                            <input type="hidden" name="settings[company_logo]" value="<?= sc($settings, 'company_logo') ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Tagline</label>
                            <input type="text" name="settings[company_tagline]" class="form-control" value="<?= sc($settings, 'company_tagline', 'Building Dreams, Delivering Trust') ?>">
                        </div>
                    </div>
                </div>

                <div class="settings-group">
                    <h6><i class="fas fa-id-card me-2"></i>Legal & Registration</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Registration Number</label>
                            <input type="text" name="settings[company_reg_number]" class="form-control" value="<?= sc($settings, 'company_reg_number') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">CIN Number</label>
                            <input type="text" name="settings[company_cin]" class="form-control" value="<?= sc($settings, 'company_cin') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="settings[company_gst]" class="form-control" value="<?= sc($settings, 'company_gst') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="settings[company_pan]" class="form-control" value="<?= sc($settings, 'company_pan') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="settings-group">
                    <h6><i class="fas fa-image me-2"></i>Brand Assets</h6>
                    <div class="mb-3">
                        <label class="form-label">Favicon</label>
                        <?php if (!empty($settings['company_favicon']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($settings['company_favicon'], '/'))): ?>
                            <div class="mb-2 text-center">
                                <img src="<?= BASE_URL ?>/<?= sc($settings, 'company_favicon') ?>" class="img-preview" alt="Favicon" class="style-24361">
                            </div>
                        <?php elseif (!empty($settings['company_favicon'])): ?>
                            <div class="mb-2 text-center"><div class="img-preview d-flex align-items-center justify-content-center bg-light border rounded" class="style-70997"><small class="text-muted">Favicon missing</small></div></div>
                        <?php endif; ?>
                        <input type="file" name="settings_image[company_favicon]" class="form-control" accept="image/*">
                        <input type="hidden" name="settings[company_favicon]" value="<?= sc($settings, 'company_favicon') ?>">
                    </div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-info-circle me-2"></i>Current Values</h6>
                    <div class="table-responsive"><table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted">Name:</td><td class="fw-bold"><?= sc($settings, 'company_name', '-') ?></td></tr>
                        <tr><td class="text-muted">Tagline:</td><td><?= sc($settings, 'company_tagline', '-') ?></td></tr>
                        <tr><td class="text-muted">Reg #:</td><td class="small"><?= sc($settings, 'company_reg_number', '-') ?></td></tr>
                        <tr><td class="text-muted">GST:</td><td class="small"><?= sc($settings, 'company_gst', '-') ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'contact'): ?>
        <!-- •�•�•� CONTACT TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-phone me-2"></i>Phone Numbers</h6>
                    <div class="mb-3">
                        <label class="form-label">Primary Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="settings[contact_phone]" class="form-control" value="<?= sc($settings, 'contact_phone', '+91 92771 21112') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secondary Phone</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="settings[contact_phone_2]" class="form-control" value="<?= sc($settings, 'contact_phone_2') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fab fa-whatsapp text-success"></i></span>
                            <input type="text" name="settings[contact_whatsapp]" class="form-control" value="<?= sc($settings, 'contact_whatsapp') ?>" placeholder="+91XXXXXXXXXX">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-envelope me-2"></i>Email Addresses</h6>
                    <div class="mb-3">
                        <label class="form-label">Primary Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="settings[contact_email]" class="form-control" value="<?= sc($settings, 'contact_email', 'info@apsdreamhome.com') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sales Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="settings[contact_email_2]" class="form-control" value="<?= sc($settings, 'contact_email_2') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="settings-group">
                    <h6><i class="fas fa-map-marker-alt me-2"></i>Address & Location</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Short Address</label>
                            <input type="text" name="settings[contact_address]" class="form-control" value="<?= sc($settings, 'contact_address') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Working Hours</label>
                            <input type="text" name="settings[contact_working_hours]" class="form-control" value="<?= sc($settings, 'contact_working_hours', 'Mon - Sat: 9:00 AM - 7:00 PM') ?>">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Full Address</label>
                            <textarea name="settings[contact_address_full]" class="form-control" rows="2"><?= sc($settings, 'contact_address_full') ?></textarea>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="settings[contact_map_lat]" class="form-control" value="<?= sc($settings, 'contact_map_lat') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="settings[contact_map_lng]" class="form-control" value="<?= sc($settings, 'contact_map_lng') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'social'): ?>
        <!-- •�•�•� SOCIAL TAB •�•�•� -->
        <div class="settings-group">
            <h6><i class="fas fa-share-alt me-2"></i>Social Media Links</h6>
            <p class="text-muted small mb-4">Enter full URLs including https://. Leave blank to hide the icon.</p>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-facebook text-primary me-2"></i>Facebook</label>
                    <input type="url" name="settings[social_facebook]" class="form-control" value="<?= sc($settings, 'social_facebook') ?>" placeholder="https://facebook.com/yourpage">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-instagram text-danger me-2"></i>Instagram</label>
                    <input type="url" name="settings[social_instagram]" class="form-control" value="<?= sc($settings, 'social_instagram') ?>" placeholder="https://instagram.com/yourpage">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-twitter text-info me-2"></i>Twitter / X</label>
                    <input type="url" name="settings[social_twitter]" class="form-control" value="<?= sc($settings, 'social_twitter') ?>" placeholder="https://twitter.com/yourpage">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-youtube text-danger me-2"></i>YouTube</label>
                    <input type="url" name="settings[social_youtube]" class="form-control" value="<?= sc($settings, 'social_youtube') ?>" placeholder="https://youtube.com/@yourchannel">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-linkedin text-primary me-2"></i>LinkedIn</label>
                    <input type="url" name="settings[social_linkedin]" class="form-control" value="<?= sc($settings, 'social_linkedin') ?>" placeholder="https://linkedin.com/company/yourcompany">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fab fa-telegram text-info me-2"></i>Telegram</label>
                    <input type="url" name="settings[social_telegram]" class="form-control" value="<?= sc($settings, 'social_telegram') ?>" placeholder="https://t.me/yourchannel">
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'seo'): ?>
        <!-- •�•�•� SEO TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-8">
                <div class="settings-group">
                    <h6><i class="fas fa-search me-2"></i>Default SEO Meta Tags</h6>
                    <p class="text-muted small mb-3">These are fallback meta tags. Individual pages can override these.</p>
                    <div class="mb-3">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="settings[seo_title]" class="form-control" value="<?= sc($settings, 'seo_title') ?>" maxlength="70">
                        <div class="form-text">Recommended: 50-60 characters. Currently: <span id="seoTitleCount"><?= strlen($settings['seo_title'] ?? '') ?></span>/70</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Description</label>
                        <textarea name="settings[seo_description]" class="form-control" rows="3" maxlength="160"><?= sc($settings, 'seo_description') ?></textarea>
                        <div class="form-text">Recommended: 150-160 characters. Currently: <span id="seoDescCount"><?= strlen($settings['seo_description'] ?? '') ?></span>/160</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta Keywords</label>
                        <textarea name="settings[seo_keywords]" class="form-control" rows="2" placeholder="keyword1, keyword2, keyword3"><?= sc($settings, 'seo_keywords') ?></textarea>
                        <div class="form-text">Comma-separated keywords</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="settings-group">
                    <h6><i class="fas fa-image me-2"></i>OG Image (Social Share)</h6>
                    <?php if (!empty($settings['seo_og_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($settings['seo_og_image'], '/'))): ?>
                        <div class="mb-2 text-center">
                            <img src="<?= BASE_URL ?>/<?= sc($settings, 'seo_og_image') ?>" class="img-fluid rounded" class="style-29190" alt="OG Image">
                        </div>
                    <?php elseif (!empty($settings['seo_og_image'])): ?>
                        <div class="mb-2 text-center"><div class="d-flex align-items-center justify-content-center bg-light border rounded" class="style-3604"><small class="text-muted">OG Image file missing</small></div></div>
                    <?php endif; ?>
                    <input type="file" name="settings_image[seo_og_image]" class="form-control" accept="image/*">
                    <input type="hidden" name="settings[seo_og_image]" value="<?= sc($settings, 'seo_og_image') ?>">
                    <div class="form-text">Recommended: 1200x630px for social sharing</div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-eye me-2"></i>Preview</h6>
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-bold text-primary" class="style-77830" id="seoPreviewTitle"><?= sc($settings, 'seo_title', 'Page Title') ?></div>
                        <div class="text-success small" id="seoPreviewUrl">apsdreamhome.com</div>
                        <div class="text-muted small" id="seoPreviewDesc"><?= sc($settings, 'seo_description', 'Page description...') ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'footer'): ?>
        <!-- •�•�•� FOOTER TAB •�•�•� -->
        <div class="settings-group">
            <h6><i class="fas fa-shoe-prints me-2"></i>Footer Content</h6>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">About Text (shown in footer)</label>
                    <textarea name="settings[footer_about]" class="form-control" rows="3"><?= sc($settings, 'footer_about') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Copyright Text</label>
                    <input type="text" name="settings[footer_copyright]" class="form-control" value="<?= sc($settings, 'footer_copyright', 'Â© 2026 APS Dream Home. All rights reserved.') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Developer Credit</label>
                    <input type="text" name="settings[footer_developer]" class="form-control" value="<?= sc($settings, 'footer_developer', 'Developed by APS Tech Team') ?>">
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'hero'): ?>
        <!-- •�•�•� HOMEPAGE TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-8">
                <div class="settings-group">
                    <h6><i class="fas fa-image me-2"></i>Hero Banner</h6>
                    <div class="mb-3">
                        <label class="form-label">Hero Title</label>
                        <input type="text" name="settings[hero_title]" class="form-control" value="<?= sc($settings, 'hero_title') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hero Subtitle</label>
                        <textarea name="settings[hero_subtitle]" class="form-control" rows="2"><?= sc($settings, 'hero_subtitle') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CTA Button Text</label>
                            <input type="text" name="settings[hero_cta_text]" class="form-control" value="<?= sc($settings, 'hero_cta_text', 'Explore Properties') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CTA Button URL</label>
                            <input type="text" name="settings[hero_cta_url]" class="form-control" value="<?= sc($settings, 'hero_cta_url', '/properties') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trust Badge Text</label>
                        <input type="text" name="settings[hero_badge]" class="form-control" value="<?= sc($settings, 'hero_badge', 'Trusted by 2000+ Families') ?>">
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="settings-group">
                    <h6><i class="fas fa-chart-bar me-2"></i>Homepage Stats</h6>
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label class="form-label small text-muted">Label</label>
                            <input type="text" name="settings[stat_properties_label]" class="form-control form-control-sm" value="<?= sc($settings, 'stat_properties_label') ?>">
                            <input type="text" name="settings[stat_properties_value]" class="form-control form-control-sm mt-1" value="<?= sc($settings, 'stat_properties_value') ?>">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small text-muted">Label</label>
                            <input type="text" name="settings[stat_families_label]" class="form-control form-control-sm" value="<?= sc($settings, 'stat_families_label') ?>">
                            <input type="text" name="settings[stat_families_value]" class="form-control form-control-sm mt-1" value="<?= sc($settings, 'stat_families_value') ?>">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small text-muted">Label</label>
                            <input type="text" name="settings[stat_projects_label]" class="form-control form-control-sm" value="<?= sc($settings, 'stat_projects_label') ?>">
                            <input type="text" name="settings[stat_projects_value]" class="form-control form-control-sm mt-1" value="<?= sc($settings, 'stat_projects_value') ?>">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small text-muted">Label</label>
                            <input type="text" name="settings[stat_experience_label]" class="form-control form-control-sm" value="<?= sc($settings, 'stat_experience_label') ?>">
                            <input type="text" name="settings[stat_experience_value]" class="form-control form-control-sm mt-1" value="<?= sc($settings, 'stat_experience_value') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'widget'): ?>
        <!-- •�•�•� WIDGETS TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Floating Widget</h6>
                    <div class="mb-3">
                        <label class="form-label">Enable Widget</label>
                        <select name="settings[whatsapp_enabled]" class="form-select">
                            <option value="1" <?= ($settings['whatsapp_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['whatsapp_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Message</label>
                        <textarea name="settings[whatsapp_message]" class="form-control" rows="2"><?= sc($settings, 'whatsapp_message', 'Hi! I am interested in your properties.') ?></textarea>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        WhatsApp number is taken from the <strong>Contact</strong> tab â†’ WhatsApp Number field.
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'email'): ?>
        <!-- •�•�•� EMAIL/SMTP TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-server me-2"></i>SMTP Server</h6>
                    <div class="mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="settings[smtp_host]" class="form-control" value="<?= sc($settings, 'smtp_host', 'smtp.gmail.com') ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" name="settings[smtp_port]" class="form-control" value="<?= sc($settings, 'smtp_port', '587') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Encryption</label>
                            <select name="settings[smtp_encryption]" class="form-select">
                                <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-key me-2"></i>SMTP Credentials</h6>
                    <div class="mb-3">
                        <label class="form-label">Username / Email</label>
                        <input type="text" name="settings[smtp_username]" class="form-control" value="<?= sc($settings, 'smtp_username') ?>" placeholder="your@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="settings[smtp_password]" class="form-control" value="<?= sc($settings, 'smtp_password') ?>" id="smtpPassword">
                            <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('smtpPassword');p.type=p.type==='password'?'text':'password'" aria-label="View"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="form-text">For Gmail, use App Password (not regular password)</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-paper-plane me-2"></i>Email Defaults</h6>
                    <div class="mb-3">
                        <label class="form-label">From Name</label>
                        <input type="text" name="settings[smtp_from_name]" class="form-control" value="<?= sc($settings, 'smtp_from_name', 'APS Dream Home') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Email</label>
                        <input type="email" name="settings[smtp_from_email]" class="form-control" value="<?= sc($settings, 'smtp_from_email') ?>" placeholder="noreply@apsdreamhome.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reply-To Email</label>
                        <input type="email" name="settings[smtp_reply_to]" class="form-control" value="<?= sc($settings, 'smtp_reply_to') ?>" placeholder="support@apsdreamhome.com">
                    </div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-flask me-2"></i>Test Connection</h6>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Test Email Recipient</label>
                            <input type="email" class="form-control" id="testEmail" placeholder="test@example.com">
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary w-100" onclick="testSmtpConnection()">
                                <i class="fas fa-paper-plane me-1"></i> Send Test
                            </button>
                        </div>
                    </div>
                    <div id="smtpTestResult" class="small"></div>
                </div>
                <div class="alert alert-warning small mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Gmail Users:</strong> Enable 2-Factor Auth â†’ Generate App Password at <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'payment'): ?>
        <!-- •�•�•� PAYMENT GATEWAY TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-credit-card me-2"></i>Razorpay Gateway</h6>
                    <div class="mb-3">
                        <label class="form-label">Enable Razorpay</label>
                        <select name="settings[razorpay_enabled]" class="form-select">
                            <option value="1" <?= ($settings['razorpay_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['razorpay_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Key ID</label>
                        <input type="text" name="settings[razorpay_key_id]" class="form-control" value="<?= sc($settings, 'razorpay_key_id') ?>" placeholder="rzp_test_...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Key Secret</label>
                        <div class="input-group">
                            <input type="password" name="settings[razorpay_key_secret]" class="form-control" value="<?= sc($settings, 'razorpay_key_secret') ?>" id="razorpaySecret">
                            <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('razorpaySecret');p.type=p.type==='password'?'text':'password'" aria-label="View"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Webhook Secret</label>
                        <input type="text" name="settings[razorpay_webhook_secret]" class="form-control" value="<?= sc($settings, 'razorpay_webhook_secret') ?>" placeholder="whsec_...">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-mobile-alt me-2"></i>UPI Configuration</h6>
                    <div class="mb-3">
                        <label class="form-label">Enable UPI</label>
                        <select name="settings[upi_enabled]" class="form-select">
                            <option value="1" <?= ($settings['upi_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['upi_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UPI VPA / ID</label>
                        <input type="text" name="settings[upi_vpa]" class="form-control" value="<?= sc($settings, 'upi_vpa') ?>" placeholder="apsdreamhome@upi">
                        <div class="form-text">This will be shown on QR code for payments</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Collectee Name</label>
                        <input type="text" name="settings[upi_name]" class="form-control" value="<?= sc($settings, 'upi_name', 'APS Dream Home') ?>">
                    </div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-info-circle me-2"></i>Payment Notes</h6>
                    <div class="alert alert-info small mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Razorpay is used for online card/netbanking/wallet payments. UPI is for direct bank transfers.
                    </div>
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Production:</strong> Replace test keys with live keys from <a href="https://dashboard.razorpay.com/app/keys" target="_blank">Razorpay Dashboard</a>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($active_tab === 'sms'): ?>
        <!-- •�•�•� SMS & WHATSAPP TAB •�•�•� -->
        <div class="row">
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fas fa-sms me-2"></i>SMS Gateway (MSG91)</h6>
                    <div class="mb-3">
                        <label class="form-label">Enable SMS</label>
                        <select name="settings[sms_enabled]" class="form-select">
                            <option value="1" <?= ($settings['sms_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['sms_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">MSG91 Auth Key</label>
                        <div class="input-group">
                            <input type="password" name="settings[sms_api_key]" class="form-control" value="<?= sc($settings, 'sms_api_key') ?>" id="smsApiKey">
                            <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('smsApiKey');p.type=p.type==='password'?'text':'password'" aria-label="View"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="form-text">Get from <a href="https://msg91.com" target="_blank">msg91.com</a> â†’ API section</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="settings[sms_sender_id]" class="form-control" value="<?= sc($settings, 'sms_sender_id') ?>" placeholder="APSDRM">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Route</label>
                        <select name="settings[sms_route]" class="form-select">
                            <option value="transactional" <?= ($settings['sms_route'] ?? '') === 'transactional' ? 'selected' : '' ?>>Transactional</option>
                            <option value="promotional" <?= ($settings['sms_route'] ?? '') === 'promotional' ? 'selected' : '' ?>>Promotional</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="settings-group">
                    <h6><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Business API</h6>
                    <div class="mb-3">
                        <label class="form-label">Enable WhatsApp</label>
                        <select name="settings[whatsapp_api_enabled]" class="form-select">
                            <option value="1" <?= ($settings['whatsapp_api_enabled'] ?? '') === '1' ? 'selected' : '' ?>>Enabled</option>
                            <option value="0" <?= ($settings['whatsapp_api_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Business Phone</label>
                        <input type="text" name="settings[whatsapp_business_phone]" class="form-control" value="<?= sc($settings, 'whatsapp_business_phone') ?>" placeholder="+91XXXXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <div class="input-group">
                            <input type="password" name="settings[whatsapp_api_token]" class="form-control" value="<?= sc($settings, 'whatsapp_api_token') ?>" id="whatsappToken">
                            <button class="btn btn-outline-secondary" type="button" onclick="var p=document.getElementById('whatsappToken');p.type=p.type==='password'?'text':'password'" aria-label="WhatsApp"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Webhook URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?= BASE_URL ?>/api/whatsapp/webhook" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value);this.innerHTML='<i class=\'fas fa-check\'></i> Copied'">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="settings-group">
                    <h6><i class="fas fa-bell me-2"></i>Notification Channels Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted">Email (SMTP)</td><td><span class="badge bg-<?= ($settings['smtp_host'] ?? '') ? 'success' : 'secondary' ?>"><?= ($settings['smtp_host'] ?? '') ? 'Configured' : 'Not Set' ?></span></td></tr>
                            <tr><td class="text-muted">SMS (MSG91)</td><td><span class="badge bg-<?= ($settings['sms_api_key'] ?? '') ? 'success' : 'secondary' ?>"><?= ($settings['sms_enabled'] ?? '0') === '1' ? 'Active' : 'Inactive' ?></span></td></tr>
                            <tr><td class="text-muted">WhatsApp</td><td><span class="badge bg-<?= ($settings['whatsapp_api_enabled'] ?? '0') === '1' ? 'success' : 'secondary' ?>"><?= ($settings['whatsapp_api_enabled'] ?? '0') === '1' ? 'Active' : 'Inactive' ?></span></td></tr>
                            <tr><td class="text-muted">Push (FCM)</td><td><span class="badge bg-success">Active</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <!-- Save Bar -->
        <div class="save-bar mt-3">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-save me-1"></i> Save <?= ucfirst($active_tab) ?> Settings
                </button>
                <a href="<?= BASE_URL ?>/" class="btn btn-outline-success btn-lg" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i> View on Site
                </a>
            </div>
        </div>
    </form>
</div>

<?php if ($active_tab === 'seo'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.querySelector('input[name="settings[seo_title]"]');
    const descInput = document.querySelector('textarea[name="settings[seo_description]"]');
    const titleCount = document.getElementById('seoTitleCount');
    const descCount = document.getElementById('seoDescCount');
    const previewTitle = document.getElementById('seoPreviewTitle');
    const previewDesc = document.getElementById('seoPreviewDesc');
    if (titleInput) titleInput.addEventListener('input', function() {
        titleCount.textContent = this.value.length;
        previewTitle.textContent = this.value || 'Page Title';
    });
    if (descInput) descInput.addEventListener('input', function() {
        descCount.textContent = this.value.length;
        previewDesc.textContent = this.value || 'Page description...';
    });
});
</script>
<?php endif; ?>
<?php if ($active_tab === 'email'): ?>
<script>
function testSmtpConnection() {
    var email = document.getElementById('testEmail').value;
    var resultDiv = document.getElementById('smtpTestResult');
    if (!email) { resultDiv.innerHTML = '<span class="text-danger">Enter an email address first</span>'; return; }
    resultDiv.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin me-1"></i> Sending test email...</span>';
    var fd = new FormData();
    fd.append('csrf_token', '<?= $_SESSION["csrf_token"] ?? "" ?>');
    fd.append('test_email', email);
    fetch('<?= BASE_URL ?>/admin/settings/email-config/test', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) { resultDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i> ' + (d.message || 'Test email sent!') + '</span>'; }
            else { resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> ' + (d.message || 'Failed to send') + '</span>'; }
        })
        .catch(function() { resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Network error</span>'; });
}
</script>
<?php endif; ?>
