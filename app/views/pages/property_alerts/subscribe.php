<?php
$page_title = $page_title ?? __('alert_sub_page_title', [], 'Property Alerts');
$page_heading = $page_heading ?? __('alert_sub_page_heading', [], 'Property Alerts');
$content = $content ?? '';
$errors = $errors ?? [];
$logged_in = $logged_in ?? false;
?>
<style>
.alert-hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 60px 0; }
.alert-card { border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.alert-card .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.feature-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.match-card { border: 1px solid #e5e7eb; border-radius: 8px; transition: all 0.2s; }
.match-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #667eea; }
</style>

<section class="alert-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3"><i class="fas fa-bell me-2"></i><?= __('alert_sub_hero_heading', [], 'Property Alerts') ?></h1>
        <p class="lead mb-0"><?= __('alert_sub_hero_subtitle', [], 'Get instant notifications when properties matching your criteria are listed') ?></p>
    </div>
</section>

<div class="container py-5">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i><?= __('alert_sub_fix_errors', [], 'Please fix the following:') ?></strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card alert-card">
                <div class="card-header p-4">
                    <h4 class="mb-1"><i class="fas fa-search me-2"></i><?= __('alert_sub_create_heading', [], 'Create Your Property Alert') ?></h4>
                    <p class="mb-0 opacity-75"><?= __('alert_sub_create_subtitle', [], "Fill in your preferences and we'll notify you when matching properties are listed") ?></p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>/property-alerts/subscribe">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <h6 class="text-uppercase text-muted small mb-3"><i class="fas fa-user me-2"></i><?= __('alert_sub_your_info', [], 'Your Information') ?></h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_full_name', [], 'Full Name') ?> *</label>
                                <input type="text" class="form-control" name="name" required
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_email', [], 'Email Address') ?> *</label>
                                <input type="email" class="form-control" name="email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_phone', [], 'Phone (optional)') ?></label>
                                <input type="tel" class="form-control" name="phone" placeholder="+91 98765 43210"
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_frequency', [], 'Alert Frequency') ?> *</label>
                                <select class="form-select" name="frequency" required>
                                    <option value="instant" <?= ($_POST['frequency'] ?? '') === 'instant' ? 'selected' : '' ?>><?= __('alert_sub_instant', [], 'Instant (as soon as listed)') ?></option>
                                    <option value="daily" <?= ($_POST['frequency'] ?? 'daily') === 'daily' ? 'selected' : '' ?>><?= __('alert_sub_daily', [], 'Daily Digest') ?></option>
                                    <option value="weekly" <?= ($_POST['frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>><?= __('alert_sub_weekly', [], 'Weekly Summary') ?></option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted small mb-3"><i class="fas fa-home me-2"></i><?= __('alert_sub_preferences', [], 'Property Preferences') ?></h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_property_type', [], 'Property Type') ?> *</label>
                                <select class="form-select" name="property_type" required>
                                    <option value=""><?= __('alert_sub_any_type', [], 'Any Type') ?></option>
                                    <option value="plot" <?= ($_POST['property_type'] ?? '') === 'plot' ? 'selected' : '' ?>><?= __('alert_sub_plot', [], 'Plot / Land') ?></option>
                                    <option value="house" <?= ($_POST['property_type'] ?? '') === 'house' ? 'selected' : '' ?>><?= __('alert_sub_house', [], 'House') ?></option>
                                    <option value="flat" <?= ($_POST['property_type'] ?? '') === 'flat' ? 'selected' : '' ?>><?= __('alert_sub_flat', [], 'Flat / Apartment') ?></option>
                                    <option value="shop" <?= ($_POST['property_type'] ?? '') === 'shop' ? 'selected' : '' ?>><?= __('alert_sub_shop', [], 'Shop / Commercial') ?></option>
                                    <option value="farmhouse" <?= ($_POST['property_type'] ?? '') === 'farmhouse' ? 'selected' : '' ?>><?= __('alert_sub_farmhouse', [], 'Farmhouse') ?></option>
                                    <option value="villa" <?= ($_POST['property_type'] ?? '') === 'villa' ? 'selected' : '' ?>><?= __('alert_sub_villa', [], 'Villa') ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_listing_type', [], 'Listing Type') ?> *</label>
                                <select class="form-select" name="listing_type" required>
                                    <option value=""><?= __('alert_sub_any', [], 'Any') ?></option>
                                    <option value="sale" <?= ($_POST['listing_type'] ?? '') === 'sale' ? 'selected' : '' ?>><?= __('alert_sub_for_sale', [], 'For Sale') ?></option>
                                    <option value="rent" <?= ($_POST['listing_type'] ?? '') === 'rent' ? 'selected' : '' ?>><?= __('alert_sub_for_rent', [], 'For Rent') ?></option>
                                    <option value="lease" <?= ($_POST['listing_type'] ?? '') === 'lease' ? 'selected' : '' ?>><?= __('alert_sub_for_lease', [], 'For Lease') ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_city', [], 'City') ?></label>
                                <input type="text" class="form-control" name="city" placeholder="e.g. Gorakhpur"
                                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_state', [], 'State') ?></label>
                                <input type="text" class="form-control" name="state" placeholder="e.g. Uttar Pradesh"
                                       value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_min_price', [], 'Min Price (₹)') ?></label>
                                <input type="number" class="form-control" name="min_price" min="0" placeholder="<?= __('alert_sub_no_min', [], 'No min') ?>"
                                       value="<?= htmlspecialchars($_POST['min_price'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('alert_sub_max_price', [], 'Max Price (₹)') ?></label>
                                <input type="number" class="form-control" name="max_price" min="0" placeholder="<?= __('alert_sub_no_max', [], 'No max') ?>"
                                       value="<?= htmlspecialchars($_POST['max_price'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('alert_sub_min_area', [], 'Min Area (sqft)') ?></label>
                                <input type="number" class="form-control" name="min_area_sqft" min="0"
                                       value="<?= htmlspecialchars($_POST['min_area_sqft'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('alert_sub_max_area', [], 'Max Area (sqft)') ?></label>
                                <input type="number" class="form-control" name="max_area_sqft" min="0"
                                       value="<?= htmlspecialchars($_POST['max_area_sqft'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><?= __('alert_sub_bedrooms', [], 'Bedrooms') ?></label>
                                <select class="form-select" name="bedrooms">
                                    <option value=""><?= __('alert_sub_any', [], 'Any') ?></option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($_POST['bedrooms'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?>+</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted small mb-3"><i class="fas fa-bell me-2"></i><?= __('alert_sub_channels', [], 'Notification Channels') ?></h6>
                        <div class="row g-2 mb-4">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_email" id="ne" checked
                                           <?= !empty($_POST['notify_email']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ne">
                                         <i class="fas fa-envelope text-primary me-1"></i> <?= __('alert_sub_email_ch', [], 'Email') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_sms" id="ns"
                                           <?= !empty($_POST['notify_sms']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ns">
                                         <i class="fas fa-sms text-info me-1"></i> <?= __('alert_sub_sms_ch', [], 'SMS') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_whatsapp" id="nw"
                                           <?= !empty($_POST['notify_whatsapp']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="nw">
                                         <i class="fab fa-whatsapp text-success me-1"></i> <?= __('alert_sub_whatsapp_ch', [], 'WhatsApp') ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-bell me-2"></i><?= __('alert_sub_create_btn', [], 'Create Property Alert') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="fas fa-star text-warning me-2"></i><?= __('alert_sub_why_subscribe', [], 'Why Subscribe?') ?></h5>
                    <div class="d-flex align-items-start mb-3">
                        <div class="feature-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= __('alert_sub_realtime', [], 'Real-time Alerts') ?></h6>
                            <p class="text-muted small mb-0"><?= __('alert_sub_realtime_desc', [], 'Get notified the moment a matching property is listed') ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <div class="feature-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= __('alert_sub_smart_filters', [], 'Smart Filters') ?></h6>
                            <p class="text-muted small mb-0"><?= __('alert_sub_smart_filters_desc', [], 'Budget, location, size, type — refine to your needs') ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mb-3">
                        <div class="feature-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= __('alert_sub_multichannel', [], 'Multi-channel') ?></h6>
                            <p class="text-muted small mb-0"><?= __('alert_sub_multichannel_desc', [], 'Email, SMS, or WhatsApp — your choice') ?></p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="feature-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= __('alert_sub_unsubscribe', [], 'Unsubscribe Anytime') ?></h6>
                            <p class="text-muted small mb-0"><?= __('alert_sub_unsubscribe_desc', [], 'No spam — easy one-click unsubscribe') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($logged_in && !empty($subscriptions)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="mb-3"><i class="fas fa-list me-2"></i><?= __('alert_sub_your_active', [], 'Your Active Alerts') ?> (<?= count($subscriptions) ?>)</h6>
                        <?php foreach (array_slice($subscriptions, 0, 3) as $sub): ?>
                            <div class="border-bottom pb-2 mb-2 small">
                                <strong><?= htmlspecialchars($sub['property_type']) ?></strong>
                                <span class="badge bg-<?= $sub['frequency'] === 'instant' ? 'warning' : 'info' ?> ms-1"><?= $sub['frequency'] ?></span>
                                <br>
                                <span class="text-muted"><?= htmlspecialchars($sub['city'] ?? 'any city') ?> · ₹<?= number_format($sub['min_price'] ?? 0) ?> - <?= number_format($sub['max_price'] ?? 0) ?></span>
                                <br>
                                <span class="text-success"><i class="fas fa-paper-plane"></i> <?= $sub['total_notifications'] ?> sent</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>