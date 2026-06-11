<?php $level = $level ?? []; ?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-edit text-primary me-2"></i>Edit Level: <?php echo htmlspecialchars($level['level_name'] ?? ''); ?></h4>

    <form method="POST" action="<?php echo BASE_URL; ?>/admin/mlm-settings/levels/update/<?php echo $level['id'] ?? 0; ?>" class="row g-4">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Basic Info</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Level Name</label>
                        <input name="level_name" class="form-control" value="<?php echo htmlspecialchars($level['level_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level Number</label>
                        <input name="level_number" type="number" class="form-control" value="<?php echo $level['level_number'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Joining Fee (₹)</label>
                        <input name="joining_fee" type="number" step="0.01" class="form-control" value="<?php echo $level['joining_fee'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monthly Maintenance (₹)</label>
                        <input name="monthly_maintenance" type="number" step="0.01" class="form-control" value="<?php echo $level['monthly_maintenance'] ?? ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Commission Percentages</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Direct Commission (%)</label>
                        <input name="direct_commission_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['direct_commission_percentage'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Team Commission (%)</label>
                        <input name="team_commission_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['team_commission_percentage'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level Difference (%)</label>
                        <input name="level_difference_commission_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['level_difference_commission_percentage'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Matching Bonus (%)</label>
                        <input name="matching_bonus_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['matching_bonus_percentage'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Leadership Bonus (%)</label>
                        <input name="leadership_bonus_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['leadership_bonus_percentage'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Performance Bonus (%)</label>
                        <input name="performance_bonus_percentage" type="number" step="0.01" class="form-control" value="<?php echo $level['performance_bonus_percentage'] ?? ''; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Qualification Requirements</h6></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Team Size Required</label>
                            <input name="team_size_required" type="number" class="form-control" value="<?php echo $level['team_size_required'] ?? 0; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Direct Referrals Required</label>
                            <input name="direct_referrals_required" type="number" class="form-control" value="<?php echo $level['direct_referrals_required'] ?? 0; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Monthly Target (₹)</label>
                            <input name="monthly_target" type="number" step="0.01" class="form-control" value="<?php echo $level['monthly_target'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Changes</button>
            <a href="<?php echo BASE_URL; ?>/admin/mlm-settings/levels" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
