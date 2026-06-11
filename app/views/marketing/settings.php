<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? 'Marketing Settings') ?></h1>
        <a href="<?= $base ?? BASE_URL ?>/marketing/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="<?= $base ?? BASE_URL ?>/marketing/settings">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">General Settings</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Default Campaign Budget (INR)</label>
                            <input type="number" name="default_budget" class="form-control" value="<?= (int)($settings['default_budget'] ?? 50000) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lead Generation Target (Monthly)</label>
                            <input type="number" name="monthly_lead_target" class="form-control" value="<?= (int)($settings['monthly_lead_target'] ?? 100) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attribution Window (Days)</label>
                            <select name="attribution_window" class="form-select">
                                <?php foreach ([1,7,14,30,60,90] as $d): ?>
                                    <option value="<?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?>" <?= ((int)($settings['attribution_window'] ?? 30) === $d) ? 'selected' : '' ?>><?= htmlspecialchars($d, ENT_QUOTES, 'UTF-8') ?> days</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">Email Marketing</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Sender Name</label>
                            <input type="text" name="email_sender_name" class="form-control" value="<?= htmlspecialchars($settings['email_sender_name'] ?? 'APS Dream Home') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sender Email</label>
                            <input type="email" name="email_sender_email" class="form-control" value="<?= htmlspecialchars($settings['email_sender_email'] ?? 'noreply@apsdreamhome.com') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Emails Per Day</label>
                            <input type="number" name="max_emails_per_day" class="form-control" value="<?= (int)($settings['max_emails_per_day'] ?? 500) ?>">
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-bottom"><h5 class="mb-0">Social Media</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" name="auto_post_facebook" class="form-check-input" id="fbAuto" value="1" <?= !empty($settings['auto_post_facebook']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="fbAuto">Auto-post new properties to Facebook</label>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input type="checkbox" name="auto_post_instagram" class="form-check-input" id="igAuto" value="1" <?= !empty($settings['auto_post_instagram']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="igAuto">Auto-post new properties to Instagram</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Settings</button>
            </form>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Stats</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0"><span>Total Campaigns</span><strong><?= (int)($quickStats['total_campaigns'] ?? 0) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between px-0"><span>Active Now</span><strong><?= (int)($quickStats['active_campaigns'] ?? 0) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between px-0"><span>Leads This Month</span><strong><?= (int)($quickStats['leads_this_month'] ?? 0) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between px-0"><span>Avg. Cost/Lead</span><strong>₹<?= number_format((int)($quickStats['avg_cost_per_lead'] ?? 0)) ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
