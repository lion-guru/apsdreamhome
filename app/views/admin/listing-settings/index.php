<?php
$settings = $settings ?? [];
$packages = $packages ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.listing-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.listing-card h5 { color: #f8fafc; margin-bottom: 16px; }
.stat-mini { background: linear-gradient(135deg, #1e3a5f, #0f172a); border-radius: 10px; padding: 16px; color: white; text-align: center; }
.stat-mini .num { font-size: 28px; font-weight: bold; }
.stat-mini .lbl { font-size: 12px; opacity: 0.8; margin-top: 4px; }
.setting-row { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #334155; }
.setting-row label { color: #94a3b8; min-width: 200px; font-size: 14px; }
.setting-row input, .setting-row select { background: #0f172a; border: 1px solid #475569; color: #f8fafc; padding: 8px 12px; border-radius: 6px; flex: 1; }
.setting-row small { color: #64748b; font-size: 11px; }
.pkg-table { width: 100%; border-collapse: collapse; color: #f8fafc; }
.pkg-table th { background: #0f172a; padding: 10px; text-align: left; font-size: 13px; color: #94a3b8; }
.pkg-table td { padding: 10px; border-bottom: 1px solid #334155; font-size: 13px; }
.pkg-table input { background: #0f172a; border: 1px solid #475569; color: #f8fafc; padding: 6px 8px; border-radius: 4px; width: 100%; }
.pkg-table .badge-featured { background: #f59e0b; color: #000; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
.pkg-table .badge-premium { background: #8b5cf6; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
.pkg-table .badge-urgent { background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
</style>

<div class="container-fluid py-4">
    <h4 style="color: #f8fafc;"><i class="fas fa-cog me-2"></i>Listing Settings</h4>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-2"><div class="stat-mini"><div class="num"><?= $totalListings ?></div><div class="lbl">Total Listings</div></div></div>
        <div class="col-md-2"><div class="stat-mini" style="background: linear-gradient(135deg, #854d0e, #78350f);"><div class="num"><?= $featuredListings ?></div><div class="lbl">Featured</div></div></div>
        <div class="col-md-2"><div class="stat-mini" style="background: linear-gradient(135deg, #581c87, #3b0764);"><div class="num"><?= $premiumListings ?></div><div class="lbl">Premium</div></div></div>
        <div class="col-md-2"><div class="stat-mini" style="background: linear-gradient(135deg, #065f46, #064e3b);"><div class="num"><?= $totalInquiries ?></div><div class="lbl">Inquiries</div></div></div>
        <div class="col-md-2"><div class="stat-mini" style="background: linear-gradient(135deg, #1e40af, #1e3a8a);"><div class="num"><?= $totalMessages ?></div><div class="lbl">Messages</div></div></div>
    </div>

    <div class="row">
        <!-- Settings Form -->
        <div class="col-md-7">
            <div class="listing-card">
                <h5><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                <form method="POST" action="<?= $base ?>/admin/listing-settings/update">
                    <?php foreach ($settings as $s): ?>
                    <div class="setting-row">
                        <label><?= htmlspecialchars($s['description'] ?? $s['setting_key']) ?></label>
                        <input type="text" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]" value="<?= htmlspecialchars($s['setting_value']) ?>">
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($settings)): ?>
                    <p style="color: #64748b;">No settings configured yet. Settings will appear here once listing_settings table is seeded.</p>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
                </form>
            </div>
        </div>

        <!-- Packages -->
        <div class="col-md-5">
            <div class="listing-card">
                <h5><i class="fas fa-box me-2"></i>Listing Packages</h5>
                <?php foreach ($packages as $pkg): ?>
                <form method="POST" action="<?= $base ?>/admin/listing-settings/package/update" style="margin-bottom: 12px; padding: 12px; background: #0f172a; border-radius: 8px;">
                    <input type="hidden" name="id" value="<?= $pkg['id'] ?>">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <strong style="color: #f8fafc;"><?= htmlspecialchars($pkg['name']) ?></strong>
                        <?php if ($pkg['is_featured']): ?><span class="badge-featured">Featured</span><?php endif; ?>
                        <?php if ($pkg['is_premium']): ?><span class="badge-premium">Premium</span><?php endif; ?>
                        <?php if ($pkg['is_urgent']): ?><span class="badge-urgent">Urgent</span><?php endif; ?>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <small style="color: #64748b;">Price (₹)</small>
                            <input type="number" name="price" value="<?= $pkg['price'] ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <small style="color: #64748b;">Duration (days)</small>
                            <input type="number" name="duration_days" value="<?= $pkg['duration_days'] ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-4">
                            <small style="color: #64748b;">Boost Score</small>
                            <input type="number" name="boost_score" value="<?= $pkg['boost_score'] ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                        </div>
                    </div>
                </form>
                <?php endforeach; ?>
                <?php if (empty($packages)): ?>
                <p style="color: #64748b;">No packages configured yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
