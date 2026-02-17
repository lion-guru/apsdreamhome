<?php
/**
 * Professional MLM Bonus Manager
 * Advanced bonus and incentive management system
 */

require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

$page_title = "Professional Bonus Manager";
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_bonus'])) {
        // Create new bonus
        $bonus_name = sanitizeInput($_POST['bonus_name']);
        $bonus_type = sanitizeInput($_POST['bonus_type']);
        $bonus_amount = floatval($_POST['bonus_amount']);
        $bonus_percentage = floatval($_POST['bonus_percentage']);
        $qualifying_rank = sanitizeInput($_POST['qualifying_rank']);
        $valid_from = sanitizeInput($_POST['valid_from']);
        $valid_to = sanitizeInput($_POST['valid_to']);
        $description = sanitizeInput($_POST['description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $auto_calculate = isset($_POST['auto_calculate']) ? 1 : 0;
        $frequency = sanitizeInput($_POST['frequency']);
        $target_criteria = sanitizeInput($_POST['target_criteria']);
        $minimum_sales = floatval($_POST['minimum_sales']);
        $minimum_team_sales = floatval($_POST['minimum_team_sales']);
        $minimum_direct_referrals = intval($_POST['minimum_direct_referrals']);
        $is_cumulative = isset($_POST['is_cumulative']) ? 1 : 0;
        $max_payout = floatval($_POST['max_payout']);

        $sql = "INSERT INTO mlm_special_bonuses (
                bonus_name, bonus_type, bonus_amount, bonus_percentage, qualifying_rank,
                valid_from, valid_to, description, is_active, auto_calculate,
                frequency, target_criteria, minimum_sales, minimum_team_sales,
                minimum_direct_referrals, is_cumulative, max_payout, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $bonus_name, $bonus_type, $bonus_amount, $bonus_percentage, $qualifying_rank,
            $valid_from, $valid_to, $description, $is_active, $auto_calculate,
            $frequency, $target_criteria, $minimum_sales, $minimum_team_sales,
            $minimum_direct_referrals, $is_cumulative, $max_payout, $_SESSION['user_id']
        ];

        if ($db->execute($sql, $params)) {
            $success = "Bonus program created successfully!";
        } else {
            $error = "Error creating bonus program.";
        }
    }

    if (isset($_POST['create_reward'])) {
        // Create new reward
        $reward_name = sanitizeInput($_POST['reward_name']);
        $reward_type = sanitizeInput($_POST['reward_type']);
        $reward_value = floatval($_POST['reward_value']);
        $qualifying_rank = sanitizeInput($_POST['reward_qualifying_rank']);
        $minimum_sales = floatval($_POST['reward_minimum_sales']);
        $minimum_team_sales = floatval($_POST['reward_minimum_team_sales']);
        $minimum_direct_referrals = intval($_POST['reward_minimum_direct_referrals']);
        $valid_from = sanitizeInput($_POST['reward_valid_from']);
        $valid_to = sanitizeInput($_POST['reward_valid_to']);
        $description = sanitizeInput($_POST['reward_description']);
        $is_active = isset($_POST['reward_is_active']) ? 1 : 0;
        $auto_distribute = isset($_POST['auto_distribute']) ? 1 : 0;
        $recognition_level = sanitizeInput($_POST['recognition_level']);
        $certificate_template = sanitizeInput($_POST['certificate_template']);

        $sql = "INSERT INTO mlm_rewards_recognition (
                reward_name, reward_type, reward_value, qualifying_rank,
                minimum_sales, minimum_team_sales, minimum_direct_referrals,
                valid_from, valid_to, description, is_active, auto_distribute,
                recognition_level, certificate_template, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $reward_name, $reward_type, $reward_value, $qualifying_rank,
            $minimum_sales, $minimum_team_sales, $minimum_direct_referrals,
            $valid_from, $valid_to, $description, $is_active, $auto_distribute,
            $recognition_level, $certificate_template, $_SESSION['user_id']
        ];

        if ($db->execute($sql, $params)) {
            $success = "Reward program created successfully!";
        } else {
            $error = "Error creating reward program.";
        }
    }
}

// Get existing bonuses and rewards
$bonuses = $db->fetchAll("
    SELECT * FROM mlm_special_bonuses
    ORDER BY created_at DESC
");

$rewards = $db->fetchAll("
    SELECT * FROM mlm_rewards_recognition
    ORDER BY created_at DESC
");

// Get MLM levels for dropdowns
$levels = $db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_order");

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 text-primary mb-0">
                <i class="fas fa-gift me-2"></i>Professional Bonus Manager
            </h1>
            <p class="text-muted">Advanced bonus and incentive management system</p>
        </div>
    </div>

    <?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="bonusTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="bonuses-tab" data-bs-toggle="tab" data-bs-target="#bonuses" type="button" role="tab">
                <i class="fas fa-money-bill-wave me-2"></i>Special Bonuses
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rewards-tab" data-bs-toggle="tab" data-bs-target="#rewards" type="button" role="tab">
                <i class="fas fa-award me-2"></i>Rewards & Recognition
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="create-bonus-tab" data-bs-toggle="tab" data-bs-target="#create-bonus" type="button" role="tab">
                <i class="fas fa-plus-circle me-2"></i>Create Bonus
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="create-reward-tab" data-bs-toggle="tab" data-bs-target="#create-reward" type="button" role="tab">
                <i class="fas fa-plus-square me-2"></i>Create Reward
            </button>
        </li>
    </ul>

    <div class="tab-content" id="bonusTabContent">
        <!-- Special Bonuses Tab -->
        <div class="tab-pane fade show active" id="bonuses" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Special Bonus Programs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Bonus Name</th>
                                    <th>Type</th>
                                    <th>Amount/Percentage</th>
                                    <th>Qualifying Rank</th>
                                    <th>Valid Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bonuses as $bonus): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($bonus['bonus_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo h($bonus['description']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst($bonus['bonus_type']); ?></span>
                                        <?php if($bonus['auto_calculate']): ?>
                                        <span class="badge bg-success">Auto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        ₹<?php echo number_format($bonus['bonus_amount']); ?>
                                        <?php if($bonus['bonus_percentage'] > 0): ?>
                                        (<?php echo $bonus['bonus_percentage']; ?>%)
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $bonus['qualifying_rank']; ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($bonus['valid_from'])); ?>
                                        - <?php echo date('M d, Y', strtotime($bonus['valid_to'])); ?>
                                    </td>
                                    <td>
                                        <?php if($bonus['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Activate/Deactivate">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rewards Tab -->
        <div class="tab-pane fade" id="rewards" role="tabpanel">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-award me-2"></i>Rewards & Recognition Programs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Reward Name</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Qualifying Rank</th>
                                    <th>Valid Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($rewards as $reward): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($reward['reward_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo h($reward['description']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?php echo ucfirst($reward['reward_type']); ?></span>
                                        <?php if($reward['auto_distribute']): ?>
                                        <span class="badge bg-success">Auto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?php echo number_format($reward['reward_value']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $reward['qualifying_rank']; ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($reward['valid_from'])); ?>
                                        - <?php echo date('M d, Y', strtotime($reward['valid_to'])); ?>
                                    </td>
                                    <td>
                                        <?php if($reward['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Distribute">
                                                <i class="fas fa-gift"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Bonus Tab -->
        <div class="tab-pane fade" id="create-bonus" role="tabpanel">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create New Bonus Program</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="createBonusForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bonus_name" class="form-label">Bonus Name *</label>
                                    <input type="text" class="form-control" id="bonus_name" name="bonus_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bonus_type" class="form-label">Bonus Type *</label>
                                    <select class="form-select" id="bonus_type" name="bonus_type" required>
                                        <option value="welcome">Welcome Bonus</option>
                                        <option value="fast_start">Fast Start Bonus</option>
                                        <option value="leadership">Leadership Bonus</option>
                                        <option value="loyalty">Loyalty Bonus</option>
                                        <option value="performance">Performance Bonus</option>
                                        <option value="seasonal">Seasonal Bonus</option>
                                        <option value="anniversary">Anniversary Bonus</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bonus_amount" class="form-label">Bonus Amount (₹)</label>
                                    <input type="number" class="form-control" id="bonus_amount" name="bonus_amount" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bonus_percentage" class="form-label">Bonus Percentage (%)</label>
                                    <input type="number" class="form-control" id="bonus_percentage" name="bonus_percentage" min="0" max="100" step="0.1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="qualifying_rank" class="form-label">Qualifying Rank *</label>
                                    <select class="form-select" id="qualifying_rank" name="qualifying_rank" required>
                                        <?php foreach($levels as $level): ?>
                                        <option value="<?php echo $level['level_name']; ?>"><?php echo $level['level_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frequency" class="form-label">Frequency</label>
                                    <select class="form-select" id="frequency" name="frequency">
                                        <option value="one_time">One Time</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_from" class="form-label">Valid From *</label>
                                    <input type="date" class="form-control" id="valid_from" name="valid_from" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valid_to" class="form-label">Valid To *</label>
                                    <input type="date" class="form-control" id="valid_to" name="valid_to" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minimum_sales" class="form-label">Minimum Sales (₹)</label>
                                    <input type="number" class="form-control" id="minimum_sales" name="minimum_sales" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minimum_team_sales" class="form-label">Minimum Team Sales (₹)</label>
                                    <input type="number" class="form-control" id="minimum_team_sales" name="minimum_team_sales" min="0" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minimum_direct_referrals" class="form-label">Minimum Direct Referrals</label>
                                    <input type="number" class="form-control" id="minimum_direct_referrals" name="minimum_direct_referrals" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_payout" class="form-label">Maximum Payout (₹)</label>
                                    <input type="number" class="form-control" id="max_payout" name="max_payout" min="0" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="auto_calculate" name="auto_calculate">
                                    <label class="form-check-label" for="auto_calculate">Auto Calculate</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" name="create_bonus" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Create Bonus Program
                            </button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Reward Tab -->
        <div class="tab-pane fade" id="create-reward" role="tabpanel">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-plus-square me-2"></i>Create New Reward Program</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="createRewardForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_name" class="form-label">Reward Name *</label>
                                    <input type="text" class="form-control" id="reward_name" name="reward_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_type" class="form-label">Reward Type *</label>
                                    <select class="form-select" id="reward_type" name="reward_type" required>
                                        <option value="gadget">Gadgets & Electronics</option>
                                        <option value="cash">Cash Rewards</option>
                                        <option value="travel">Travel Packages</option>
                                        <option value="jewelry">Jewelry & Watches</option>
                                        <option value="car">Car Rewards</option>
                                        <option value="house">House Rewards</option>
                                        <option value="recognition">Recognition Awards</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_value" class="form-label">Reward Value (₹) *</label>
                                    <input type="number" class="form-control" id="reward_value" name="reward_value" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_qualifying_rank" class="form-label">Qualifying Rank *</label>
                                    <select class="form-select" id="reward_qualifying_rank" name="reward_qualifying_rank" required>
                                        <?php foreach($levels as $level): ?>
                                        <option value="<?php echo $level['level_name']; ?>"><?php echo $level['level_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reward_minimum_sales" class="form-label">Minimum Sales (₹)</label>
                                    <input type="number" class="form-control" id="reward_minimum_sales" name="reward_minimum_sales" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reward_minimum_team_sales" class="form-label">Minimum Team Sales (₹)</label>
                                    <input type="number" class="form-control" id="reward_minimum_team_sales" name="reward_minimum_team_sales" min="0" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reward_minimum_direct_referrals" class="form-label">Minimum Direct Referrals</label>
                                    <input type="number" class="form-control" id="reward_minimum_direct_referrals" name="reward_minimum_direct_referrals" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_valid_from" class="form-label">Valid From *</label>
                                    <input type="date" class="form-control" id="reward_valid_from" name="reward_valid_from" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reward_valid_to" class="form-label">Valid To *</label>
                                    <input type="date" class="form-control" id="reward_valid_to" name="reward_valid_to" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="recognition_level" class="form-label">Recognition Level</label>
                                    <select class="form-select" id="recognition_level" name="recognition_level">
                                        <option value="bronze">Bronze</option>
                                        <option value="silver">Silver</option>
                                        <option value="gold">Gold</option>
                                        <option value="platinum">Platinum</option>
                                        <option value="diamond">Diamond</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="certificate_template" class="form-label">Certificate Template</label>
                                    <input type="text" class="form-control" id="certificate_template" name="certificate_template" placeholder="Certificate template name">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reward_description" class="form-label">Description</label>
                            <textarea class="form-control" id="reward_description" name="reward_description" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="reward_is_active" name="reward_is_active" checked>
                                    <label class="form-check-label" for="reward_is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="auto_distribute" name="auto_distribute">
                                    <label class="form-check-label" for="auto_distribute">Auto Distribute</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" name="create_reward" class="btn btn-warning">
                                <i class="fas fa-plus me-2"></i>Create Reward Program
                            </button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    const nextYearStr = nextYear.toISOString().split('T')[0];

    document.getElementById('valid_from').value = today;
    document.getElementById('valid_to').value = nextYearStr;
    document.getElementById('reward_valid_from').value = today;
    document.getElementById('reward_valid_to').value = nextYearStr;
});
</script>
