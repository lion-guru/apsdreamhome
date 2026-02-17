<?php
/**
 * Professional MLM Settings Manager
 * Advanced settings and configuration management
 */

require_once 'core/init.php';

use App\Core\Database;
$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Professional MLM Settings";
include 'includes/header.php';

// Handle settings updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'update_commission_settings':
                    $commission_types = ['direct_commission', 'team_commission', 'level_commission', 'matching_bonus', 'leadership_bonus', 'performance_bonus'];
                    
                    $db->beginTransaction();
                    foreach ($commission_types as $type) {
                        $enabled = isset($_POST[$type . '_enabled']) ? 1 : 0;
                        $percentage = (float)$_POST[$type . '_percentage'];
                        $max_amount = (float)$_POST[$type . '_max_amount'];
                        
                        $db->execute("
                            INSERT INTO mlm_commission_settings (setting_name, is_enabled, percentage, max_amount, updated_at) 
                            VALUES (?, ?, ?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE 
                            is_enabled = VALUES(is_enabled), 
                            percentage = VALUES(percentage), 
                            max_amount = VALUES(max_amount), 
                            updated_at = NOW()
                        ", [$type, $enabled, $percentage, $max_amount]);
                    }
                    $db->commit();
                    setSessionget_flash('success', "Commission settings updated successfully!");
                    break;
                    
                case 'update_rank_settings':
                    $level_id = (int)$_POST['level_id'];
                    $joining_fee = (float)$_POST['joining_fee'];
                    $monthly_maintenance = (float)$_POST['monthly_maintenance'];
                    $team_size_required = (int)$_POST['team_size_required'];
                    $direct_referrals = (int)$_POST['direct_referrals'];
                    $monthly_target = (float)$_POST['monthly_target'];
                    
                    $db->execute("
                        UPDATE mlm_levels SET 
                        joining_fee = ?, 
                        monthly_maintenance = ?, 
                        team_size_required = ?, 
                        direct_referrals = ?, 
                        monthly_target = ? 
                        WHERE id = ?
                    ", [$joining_fee, $monthly_maintenance, $team_size_required, $direct_referrals, $monthly_target, $level_id]);
                    
                    setSessionget_flash('success', "Rank settings updated successfully!");
                    break;
                    
                case 'update_bonus_settings':
                    $bonus_types = ['welcome_bonus', 'fast_start_bonus', 'leadership_bonus', 'loyalty_bonus', 'performance_bonus', 'seasonal_bonus', 'anniversary_bonus'];
                    
                    $db->beginTransaction();
                    foreach ($bonus_types as $type) {
                        $enabled = isset($_POST[$type . '_enabled']) ? 1 : 0;
                        $amount = (float)$_POST[$type . '_amount'];
                        $validity_days = (int)$_POST[$type . '_validity_days'];
                        $qualification_criteria = $_POST[$type . '_criteria'];
                        
                        $db->execute("
                            INSERT INTO mlm_bonus_settings (bonus_type, is_enabled, bonus_amount, validity_days, qualification_criteria, updated_at) 
                            VALUES (?, ?, ?, ?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE 
                            is_enabled = VALUES(is_enabled), 
                            bonus_amount = VALUES(bonus_amount), 
                            validity_days = VALUES(validity_days), 
                            qualification_criteria = VALUES(qualification_criteria), 
                            updated_at = NOW()
                        ", [$type, $enabled, $amount, $validity_days, $qualification_criteria]);
                    }
                    $db->commit();
                    setSessionget_flash('success', "Bonus settings updated successfully!");
                    break;
                    
                case 'update_system_settings':
                    $settings = [
                        'mlm_enabled' => isset($_POST['mlm_enabled']) ? 1 : 0,
                        'auto_rank_promotion' => isset($_POST['auto_rank_promotion']) ? 1 : 0,
                        'commission_calculation_frequency' => $_POST['commission_calculation_frequency'],
                        'payout_frequency' => $_POST['payout_frequency'],
                        'minimum_payout_amount' => (float)$_POST['minimum_payout_amount'],
                        'maximum_payout_amount' => (float)$_POST['maximum_payout_amount'],
                        'tax_percentage' => (float)$_POST['tax_percentage'],
                        'maintenance_fee_percentage' => (float)$_POST['maintenance_fee_percentage']
                    ];
                    
                    $db->beginTransaction();
                    foreach ($settings as $key => $value) {
                        $db->execute("
                            INSERT INTO mlm_system_settings (setting_name, setting_value, updated_at) 
                            VALUES (?, ?, NOW()) 
                            ON DUPLICATE KEY UPDATE 
                            setting_value = VALUES(setting_value), 
                            updated_at = NOW()
                        ", [$key, $value]);
                    }
                    $db->commit();
                    setSessionget_flash('success', "System settings updated successfully!");
                    break;
            }
        } catch (Exception $e) {
            if ($db->isInTransaction()) {
                $db->rollBack();
            }
            setSessionget_flash('error', "Error updating settings: " . $e->getMessage());
        }
        
        header('Location: professional_mlm_settings.php');
        exit();
    }
}

// Get current settings
$commission_settings = [];
$rows = $db->fetchAll("SELECT * FROM mlm_commission_settings");
foreach ($rows as $row) {
    $commission_settings[$row['setting_name']] = $row;
}

$bonus_settings = [];
$rows = $db->fetchAll("SELECT * FROM mlm_bonus_settings");
foreach ($rows as $row) {
    $bonus_settings[$row['bonus_type']] = $row;
}

$system_settings = [];
$rows = $db->fetchAll("SELECT * FROM mlm_system_settings");
foreach ($rows as $row) {
    $system_settings[$row['setting_name']] = $row['setting_value'];
}

$mlm_levels = $db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_order");

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 text-primary mb-0">
                <i class="fas fa-cog me-2"></i>Professional MLM Settings
            </h1>
            <p class="text-muted">Advanced settings and configuration management</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="commission-tab" data-bs-toggle="pill" 
                            data-bs-target="#commission-settings" type="button" role="tab">
                        <i class="fas fa-percentage me-2"></i>Commission Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rank-tab" data-bs-toggle="pill" 
                            data-bs-target="#rank-settings" type="button" role="tab">
                        <i class="fas fa-layer-group me-2"></i>Rank Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bonus-tab" data-bs-toggle="pill" 
                            data-bs-target="#bonus-settings" type="button" role="tab">
                        <i class="fas fa-gift me-2"></i>Bonus Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="pill" 
                            data-bs-target="#system-settings" type="button" role="tab">
                        <i class="fas fa-cogs me-2"></i>System Settings
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">
                <!-- Commission Settings -->
                <div class="tab-pane fade show active" id="commission-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Commission Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_commission_settings">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Commission Type</th>
                                                <th>Enabled</th>
                                                <th>Percentage (%)</th>
                                                <th>Maximum Amount (₹)</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $commission_types = [
                                                'direct_commission' => 'Direct Commission - Paid on personal sales',
                                                'team_commission' => 'Team Commission - Paid on team sales',
                                                'level_commission' => 'Level Commission - Paid on downline levels',
                                                'matching_bonus' => 'Matching Bonus - Matching team commissions',
                                                'leadership_bonus' => 'Leadership Bonus - Leadership performance bonus',
                                                'performance_bonus' => 'Performance Bonus - Monthly performance bonus'
                                            ];
                                            
                                            foreach ($commission_types as $type => $description): 
                                                $setting = $commission_settings[$type] ?? ['is_enabled' => 1, 'percentage' => 5, 'max_amount' => 50000];
                                            ?>
                                            <tr>
                                                <td><strong><?php echo ucwords(str_replace('_', ' ', $type)); ?></strong></td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="<?php echo $type; ?>_enabled" 
                                                               <?php echo $setting['is_enabled'] ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="<?php echo $type; ?>_percentage" 
                                                           value="<?php echo $setting['percentage']; ?>" 
                                                           step="0.01" min="0" max="100">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="<?php echo $type; ?>_max_amount" 
                                                           value="<?php echo $setting['max_amount']; ?>" 
                                                           step="0.01" min="0">
                                                </td>
                                                <td><small class="text-muted"><?php echo $description; ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Commission Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Rank Settings -->
                <div class="tab-pane fade" id="rank-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Rank Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="rankAccordion">
                                <?php while($rank = $mlm_levels->fetch_assoc()): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="rank<?php echo $rank['level_id']; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo $rank['level_id']; ?>">
                                            <strong><?php echo h($rank['level_name']); ?></strong>
                                            <span class="ms-auto">
                                                <small class="text-muted">Monthly Target: ₹<?php echo number_format($rank['monthly_target']); ?></small>
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $rank['level_id']; ?>" class="accordion-collapse collapse" 
                                         data-bs-parent="#rankAccordion">
                                        <div class="accordion-body">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="update_rank_settings">
                                                <input type="hidden" name="level_id" value="<?php echo $rank['level_id']; ?>">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Joining Fee (₹)</label>
                                                        <input type="number" class="form-control" 
                                                               name="joining_fee" 
                                                               value="<?php echo $rank['joining_fee']; ?>" 
                                                               step="0.01" min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Monthly Maintenance (₹)</label>
                                                        <input type="number" class="form-control" 
                                                               name="monthly_maintenance" 
                                                               value="<?php echo $rank['monthly_maintenance']; ?>" 
                                                               step="0.01" min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Team Size Required</label>
                                                        <input type="number" class="form-control" 
                                                               name="team_size_required" 
                                                               value="<?php echo $rank['team_size_required']; ?>" 
                                                               min="0">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Direct Referrals Required</label>
                                                        <input type="number" class="form-control" 
                                                               name="direct_referrals" 
                                                               value="<?php echo $rank['direct_referrals']; ?>" 
                                                               min="0">
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label class="form-label">Monthly Target (₹)</label>
                                                        <input type="number" class="form-control" 
                                                               name="monthly_target" 
                                                               value="<?php echo $rank['monthly_target']; ?>" 
                                                               step="0.01" min="0">
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-save me-2"></i>Update Rank
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bonus Settings -->
                <div class="tab-pane fade" id="bonus-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-gift me-2"></i>Special Bonus Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_bonus_settings">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Bonus Type</th>
                                                <th>Enabled</th>
                                                <th>Amount (₹)</th>
                                                <th>Validity (Days)</th>
                                                <th>Qualification Criteria</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $bonus_types = [
                                                'welcome_bonus' => 'Welcome bonus for new associates',
                                                'fast_start_bonus' => 'Bonus for achieving targets in first month',
                                                'leadership_bonus' => 'Bonus for leadership development',
                                                'loyalty_bonus' => 'Bonus for long-term associates',
                                                'performance_bonus' => 'Bonus for exceptional performance',
                                                'seasonal_bonus' => 'Bonus during special seasons',
                                                'anniversary_bonus' => 'Bonus for membership anniversaries'
                                            ];
                                            
                                            foreach ($bonus_types as $type => $description): 
                                                $setting = $bonus_settings[$type] ?? ['is_enabled' => 1, 'bonus_amount' => 1000, 'validity_days' => 30, 'qualification_criteria' => 'Active membership'];
                                            ?>
                                            <tr>
                                                <td><strong><?php echo ucwords(str_replace('_', ' ', $type)); ?></strong></td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="<?php echo $type; ?>_enabled" 
                                                               <?php echo $setting['is_enabled'] ? 'checked' : ''; ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="<?php echo $type; ?>_amount" 
                                                           value="<?php echo $setting['bonus_amount']; ?>" 
                                                           step="0.01" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="<?php echo $type; ?>_validity_days" 
                                                           value="<?php echo $setting['validity_days']; ?>" 
                                                           min="1">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           name="<?php echo $type; ?>_criteria" 
                                                           value="<?php echo h($setting['qualification_criteria']); ?>">
                                                </td>
                                                <td><small class="text-muted"><?php echo $description; ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fas fa-save me-2"></i>Update Bonus Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="tab-pane fade" id="system-settings" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>System Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_system_settings">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="mlm_enabled" 
                                                   <?php echo ($system_settings['mlm_enabled'] ?? '1') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">MLM System Enabled</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="auto_rank_promotion" 
                                                   <?php echo ($system_settings['auto_rank_promotion'] ?? '1') ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Auto Rank Promotion</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Commission Calculation Frequency</label>
                                        <select name="commission_calculation_frequency" class="form-select">
                                            <option value="daily" <?php echo ($system_settings['commission_calculation_frequency'] ?? 'monthly') == 'daily' ? 'selected' : ''; ?>>Daily</option>
                                            <option value="weekly" <?php echo ($system_settings['commission_calculation_frequency'] ?? 'monthly') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                            <option value="monthly" <?php echo ($system_settings['commission_calculation_frequency'] ?? 'monthly') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Payout Frequency</label>
                                        <select name="payout_frequency" class="form-select">
                                            <option value="weekly" <?php echo ($system_settings['payout_frequency'] ?? 'monthly') == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                            <option value="bi-weekly" <?php echo ($system_settings['payout_frequency'] ?? 'monthly') == 'bi-weekly' ? 'selected' : ''; ?>>Bi-Weekly</option>
                                            <option value="monthly" <?php echo ($system_settings['payout_frequency'] ?? 'monthly') == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Minimum Payout Amount (₹)</label>
                                        <input type="number" class="form-control" 
                                               name="minimum_payout_amount" 
                                               value="<?php echo $system_settings['minimum_payout_amount'] ?? 1000; ?>" 
                                               step="0.01" min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Maximum Payout Amount (₹)</label>
                                        <input type="number" class="form-control" 
                                               name="maximum_payout_amount" 
                                               value="<?php echo $system_settings['maximum_payout_amount'] ?? 500000; ?>" 
                                               step="0.01" min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tax Percentage (%)</label>
                                        <input type="number" class="form-control" 
                                               name="tax_percentage" 
                                               value="<?php echo $system_settings['tax_percentage'] ?? 10; ?>" 
                                               step="0.01" min="0" max="100">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Maintenance Fee Percentage (%)</label>
                                        <input type="number" class="form-control" 
                                               name="maintenance_fee_percentage" 
                                               value="<?php echo $system_settings['maintenance_fee_percentage'] ?? 2; ?>" 
                                               step="0.01" min="0" max="100">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Update System Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
