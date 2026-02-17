<?php

/**
 * Professional MLM Performance Manager
 * Advanced performance tracking and management system
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check admin authentication
if (!isLoggedIn() || !isAdmin()) {
    redirectTo('login.php');
}

$page_title = "Professional Performance Manager";
include 'includes/header.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid security token.";
        redirectTo('professional_performance_manager.php');
    }
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_performance':
                $associate_id = (int)$_POST['associate_id'];
                $month_year = $_POST['month_year'];
                $total_sales = (float)$_POST['total_sales'];
                $target_achieved = (float)$_POST['target_achieved'];
                $notes = $_POST['notes'];

                $sql = "
                    INSERT INTO mlm_performance (associate_id, month_year, total_sales, target_achieved, notes, updated_at)
                    VALUES (:associate_id, :month_year, :total_sales, :target_achieved, :notes, NOW())
                    ON DUPLICATE KEY UPDATE
                    total_sales = VALUES(total_sales),
                    target_achieved = VALUES(target_achieved),
                    notes = VALUES(notes),
                    updated_at = NOW()
                ";

                if ($db->execute($sql, [
                    'associate_id' => $associate_id,
                    'month_year' => $month_year,
                    'total_sales' => $total_sales,
                    'target_achieved' => $target_achieved,
                    'notes' => $notes
                ])) {
                    $_SESSION['success'] = "Performance updated successfully!";
                } else {
                    $_SESSION['error'] = "Failed to update performance.";
                }
                break;

            case 'add_bonus':
                $associate_id = (int)$_POST['associate_id'];
                $bonus_type = $_POST['bonus_type'];
                $bonus_amount = (float)$_POST['bonus_amount'];
                $bonus_month = $_POST['bonus_month'];
                $description = $_POST['description'];

                $sql = "
                    INSERT INTO mlm_special_bonuses (associate_id, bonus_type, bonus_amount, bonus_month, description, status, created_at)
                    VALUES (:associate_id, :bonus_type, :bonus_amount, :bonus_month, :description, 'active', NOW())
                ";

                if ($db->execute($sql, [
                    'associate_id' => $associate_id,
                    'bonus_type' => $bonus_type,
                    'bonus_amount' => $bonus_amount,
                    'bonus_month' => $bonus_month,
                    'description' => $description
                ])) {
                    $_SESSION['success'] = "Special bonus added successfully!";
                } else {
                    $_SESSION['error'] = "Failed to add special bonus.";
                }
                break;

            case 'promote_rank':
                $associate_id = (int)$_POST['associate_id'];
                $new_rank_id = (int)$_POST['new_rank_id'];
                $promotion_date = $_POST['promotion_date'];
                $business_achieved = (float)$_POST['business_achieved'];
                $team_size_achieved = (int)$_POST['team_size_achieved'];
                $direct_referrals_achieved = (int)$_POST['direct_referrals_achieved'];
                $promotion_bonus = (float)$_POST['promotion_bonus'];
                $is_fast_track = isset($_POST['is_fast_track']) ? 1 : 0;
                $fast_track_bonus = $is_fast_track ? (float)$_POST['fast_track_bonus'] : 0;
                $recognition_award = $_POST['recognition_award'];

                // Get current rank
                $current_rank = $db->fetch("SELECT rank FROM associates WHERE id = :id", ['id' => $associate_id]);
                $current_rank_id = $db->fetch("SELECT level_id FROM mlm_levels WHERE level_name = :rank", ['rank' => $current_rank['rank']])['level_id'];

                // Insert rank advancement
                $sql = "
                    INSERT INTO mlm_rank_advancements (associate_id, from_rank_id, to_rank_id, business_achieved,
                    team_size_achieved, direct_referrals_achieved, promotion_bonus, is_fast_track, fast_track_bonus,
                    recognition_award, promotion_date, created_at)
                    VALUES (:associate_id, :from_rank_id, :to_rank_id, :business_achieved,
                    :team_size_achieved, :direct_referrals_achieved, :promotion_bonus, :is_fast_track, :fast_track_bonus,
                    :recognition_award, :promotion_date, NOW())
                ";

                if ($db->execute($sql, [
                    'associate_id' => $associate_id,
                    'from_rank_id' => $current_rank_id,
                    'to_rank_id' => $new_rank_id,
                    'business_achieved' => $business_achieved,
                    'team_size_achieved' => $team_size_achieved,
                    'direct_referrals_achieved' => $direct_referrals_achieved,
                    'promotion_bonus' => $promotion_bonus,
                    'is_fast_track' => $is_fast_track,
                    'fast_track_bonus' => $fast_track_bonus,
                    'recognition_award' => $recognition_award,
                    'promotion_date' => $promotion_date
                ])) {
                    // Update associate rank
                    $new_rank_name = $db->fetch("SELECT level_name FROM mlm_levels WHERE level_id = :level_id", ['level_id' => $new_rank_id])['level_name'];
                    $db->execute("UPDATE associates SET rank = :rank WHERE id = :id", ['rank' => $new_rank_name, 'id' => $associate_id]);

                    $_SESSION['success'] = "Rank promoted successfully!";
                } else {
                    $_SESSION['error'] = "Failed to promote rank.";
                }
                break;
        }

        redirectTo('professional_performance_manager.php');
    }
}

// Get performance data
$current_month = date('Y-m');
$performance_data = $db->fetchAll("
    SELECT
        p.id,
        p.associate_id,
        a.name as associate_name,
        a.rank as current_rank,
        p.month_year,
        p.total_sales,
        p.total_commission,
        p.target_achieved,
        p.notes,
        p.updated_at,
        ml.monthly_target,
        ml.level_name as rank_name
    FROM mlm_performance p
    JOIN associates a ON p.associate_id = a.id
    JOIN mlm_levels ml ON a.rank = ml.level_name
    WHERE p.month_year = :month_year
    ORDER BY p.total_sales DESC
", ['month_year' => $current_month]);

// Get associates for dropdown
$associates = $db->fetchAll("SELECT id, name, rank FROM associates ORDER BY name");

// Get ranks for promotion
$ranks = $db->fetchAll("SELECT level_id, level_name FROM mlm_levels ORDER BY level_order");

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-6 text-primary mb-0">
                <i class="fas fa-chart-line me-2"></i>Professional Performance Manager
            </h1>
            <p class="text-muted">Advanced performance tracking and management system</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo h($_SESSION['success']);
                                                    unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo h($_SESSION['error']);
                                                            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-plus fa-2x mb-2"></i>
                    <h6>Update Performance</h6>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#updatePerformanceModal">
                        Update Now
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-gift fa-2x mb-2"></i>
                    <h6>Add Special Bonus</h6>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addBonusModal">
                        Add Bonus
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up fa-2x mb-2"></i>
                    <h6>Promote Rank</h6>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#promoteRankModal">
                        Promote
                    </button>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                    <h6>View Analytics</h6>
                    <a href="professional_team_analytics.php" class="btn btn-light btn-sm">
                        View Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Month Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i><?php echo date('F Y'); ?> Performance Data
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Associate</th>
                                    <th>Current Rank</th>
                                    <th>Monthly Target</th>
                                    <th>Total Sales</th>
                                    <th>Commission</th>
                                    <th>Target Achievement</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($performance_data as $perf):
                                    $achievement_percentage = ($perf['monthly_target'] > 0) ? ($perf['total_sales'] / $perf['monthly_target']) * 100 : 0;
                                    $status_class = $achievement_percentage >= 100 ? 'success' : ($achievement_percentage >= 80 ? 'warning' : 'danger');
                                    $status_text = $achievement_percentage >= 100 ? 'Target Achieved' : ($achievement_percentage >= 80 ? 'Near Target' : 'Below Target');
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo h($perf['associate_name']); ?></strong>
                                            <small class="text-muted d-block">ID: <?php echo (int)$perf['associate_id']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo h($perf['rank_name']); ?></span>
                                        </td>
                                        <td>₹<?php echo number_format((float)$perf['monthly_target']); ?></td>
                                        <td>₹<?php echo number_format((float)$perf['total_sales']); ?></td>
                                        <td>₹<?php echo number_format((float)$perf['total_commission']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?php echo h($status_class); ?>"
                                                    role="progressbar"
                                                    style="width: <?php echo min((float)$achievement_percentage, 100); ?>%">
                                                    <?php echo round((float)$achievement_percentage); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo h($status_class); ?>"><?php echo h($status_text); ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Edit Performance"
                                                    onclick="editPerformance(<?php echo (int)$perf['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-success" title="View Details"
                                                    onclick="viewPerformanceDetails(<?php echo (int)$perf['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
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
    </div>
</div>

<!-- Update Performance Modal -->
<div class="modal fade" id="updatePerformanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Update Performance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_performance">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Associate</label>
                            <select name="associate_id" class="form-select" required>
                                <option value="">Select Associate</option>
                                <?php foreach ($associates as $assoc): ?>
                                    <option value="<?php echo (int)$assoc['id']; ?>">
                                        <?php echo h($assoc['name']); ?> (<?php echo h($assoc['rank']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month/Year</label>
                            <input type="month" name="month_year" class="form-control"
                                value="<?php echo date('Y-m'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Sales (₹)</label>
                            <input type="number" name="total_sales" class="form-control"
                                step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Achievement (%)</label>
                            <input type="number" name="target_achieved" class="form-control"
                                step="0.01" min="0" max="200" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Performance notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Performance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Special Bonus Modal -->
<div class="modal fade" id="addBonusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Add Special Bonus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_bonus">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Associate</label>
                            <select name="associate_id" class="form-select" required>
                                <option value="">Select Associate</option>
                                <?php
                                foreach ($associates as $assoc): ?>
                                    <option value="<?php echo (int)$assoc['id']; ?>">
                                        <?php echo h($assoc['name']); ?> (<?php echo h($assoc['rank']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bonus Type</label>
                            <select name="bonus_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="welcome">Welcome Bonus</option>
                                <option value="fast_start">Fast Start Bonus</option>
                                <option value="leadership">Leadership Bonus</option>
                                <option value="loyalty">Loyalty Bonus</option>
                                <option value="performance">Performance Bonus</option>
                                <option value="seasonal">Seasonal Bonus</option>
                                <option value="anniversary">Anniversary Bonus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bonus Amount (₹)</label>
                            <input type="number" name="bonus_amount" class="form-control"
                                step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bonus Month</label>
                            <input type="month" name="bonus_month" class="form-control"
                                value="<?php echo date('Y-m'); ?>" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Bonus description..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Bonus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Promote Rank Modal -->
<div class="modal fade" id="promoteRankModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Promote Rank</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="action" value="promote_rank">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Associate</label>
                            <select name="associate_id" class="form-select" required>
                                <option value="">Select Associate</option>
                                <?php
                                foreach ($associates as $assoc): ?>
                                    <option value="<?php echo (int)$assoc['id']; ?>">
                                        <?php echo h($assoc['name']); ?> (<?php echo h($assoc['rank']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">New Rank</label>
                            <select name="new_rank_id" class="form-select" required>
                                <option value="">Select New Rank</option>
                                <?php foreach ($ranks as $rank): ?>
                                    <option value="<?php echo (int)$rank['level_id']; ?>">
                                        <?php echo h($rank['level_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Business Achieved (₹)</label>
                            <input type="number" name="business_achieved" class="form-control"
                                step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team Size Achieved</label>
                            <input type="number" name="team_size_achieved" class="form-control"
                                min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Direct Referrals Achieved</label>
                            <input type="number" name="direct_referrals_achieved" class="form-control"
                                min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Promotion Bonus (₹)</label>
                            <input type="number" name="promotion_bonus" class="form-control"
                                step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Promotion Date</label>
                            <input type="date" name="promotion_date" class="form-control"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_fast_track" class="form-check-input"
                                    id="is_fast_track" value="1">
                                <label class="form-check-label" for="is_fast_track">Fast Track Promotion</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3" id="fast_track_bonus_div" style="display: none;">
                            <label class="form-label">Fast Track Bonus (₹)</label>
                            <input type="number" name="fast_track_bonus" class="form-control"
                                step="0.01" min="0" value="0">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Recognition Award</label>
                            <textarea name="recognition_award" class="form-control" rows="2"
                                placeholder="Recognition details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Promote Rank</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Show/hide fast track bonus field
    document.getElementById('is_fast_track').addEventListener('change', function() {
        document.getElementById('fast_track_bonus_div').style.display = this.checked ? 'block' : 'none';
    });

    function editPerformance(id) {
        // Implementation for editing performance
        alert('Edit performance for ID: ' + id);
    }

    function viewPerformanceDetails(id) {
        // Implementation for viewing performance details
        alert('View details for ID: ' + id);
    }
</script>