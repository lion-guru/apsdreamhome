<?php
// app/views/admin/meetings/index.php
/**
 * Meetings Management - Index View
 * Shows meetings list, stats, and filters
 * Data: $meetings, $stats, $page_title
 */
$meetings = $meetings ?? [];
$stats = $stats ?? ['total' => 0, 'scheduled' => 0, 'completed' => 0, 'cancelled' => 0, 'no_show' => 0];
$page_title = $page_title ?? 'Meetings Management';

$today = date('Y-m-d');
$upcomingMeetings = array_filter($meetings, function($m) use ($today) {
    return $m['status'] === 'scheduled' && $m['start_time'] >= $today;
});
$completedMeetings = array_filter($meetings, function($m) {
    return $m['status'] === 'completed';
});
$missedMeetings = array_filter($meetings, function($m) use ($today) {
    return $m['status'] === 'scheduled' && $m['start_time'] < $today;
});

$totalMeetings = count($meetings);
$todayMeetings = count(array_filter($meetings, function($m) use ($today) {
    return $m['start_time'] === $today;
}));

$completionRate = $totalMeetings > 0 ? round((count($completedMeetings) / $totalMeetings) * 100, 1) : 0;
$upcomingCount = count($upcomingMeetings);
$missedCount = count($missedMeetings);
$completedCount = count($completedMeetings);
?>


<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-check me-2"></i>Meetings Management
        </h1>
        <div class="d-flex gap-2">
            <button onclick="location.href='<?= BASE_URL ?>/admin/meetings/schedule'" 
                class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Schedule Meeting
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Total Meetings</h6>
                            <h3 class="mb-0 fw-bold"><?= $totalMeetings ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Today's Meetings</h6>
                            <h3 class="mb-0 fw-bold"><?= $todayMeetings ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-day fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Completed</h6>
                            <h3 class="mb-0 fw-bold text-success"><?= $completedCount ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">Completion Rate</h6>
                            <h3 class="mb-0 fw-bold"><?= $completionRate ?>%</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="scheduled" <?= ($_GET['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="no_show" <?= ($_GET['status'] ?? '') === 'no_show' ? 'selected' : '' ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Agent ID</label>
                    <input type="number" name="user_id" class="form-control" value="<?= htmlspecialchars($_GET['user_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <a href="<?= BASE_URL ?>/admin/meetings" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Meetings List -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>All Meetings
                <span class="badge bg-primary ms-2"><?= $totalMeetings ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($meetings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No meetings found</h4>
                    <p class="text-muted">Try adjusting your search criteria or schedule a new meeting.</p>
                    <a href="<?= BASE_URL ?>/admin/meetings/schedule" class="btn btn-primary mt-2">Schedule First Meeting</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Lead</th>
                                <th>Agent</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Meeting Type</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($meetings as $meeting): ?>
                                <tr>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($meeting['title'] ?? '') ?></div>
                                        <div class="small text-muted">ID: <?= $meeting['id'] ?? '' ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($meeting['lead_name'] ?? 'Walk-in') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($meeting['lead_phone'] ?? '') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($meeting['agent_name'] ?? 'Not assigned') ?></td>
                                    <td>
                                        <div><?= date('M j, Y', strtotime($meeting['start_time'] ?? '')) ?></div>
                                        <div class="small text-muted"><?= date('h:i A', strtotime($meeting['start_time'] ?? '')) ?></div>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $meeting['status'] ?? 'scheduled';
                                        $statusClass = match($status) {
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'no_show' => 'bg-warning',
                                            default => 'bg-primary'
                                        };
                                        $statusLabel = match($status) {
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                            'no_show' => 'No Show',
                                            default => 'Scheduled'
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?> badge-soft">
                                            <?= htmlspecialchars($statusLabel) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $type = $meeting['meeting_type'] ?? 'site_visit';
                                        $typeClass = match($type) {
                                            'site_visit' => 'bg-primary',
                                            'video_call' => 'bg-success',
                                            'phone_call' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        $typeLabel = match($type) {
                                            'site_visit' => 'Site Visit',
                                            'video_call' => 'Video Call',
                                            'phone_call' => 'Phone Call',
                                            default => htmlspecialchars($type ?? '')
                                        };
                                        ?>
                                        <span class="badge <?= $typeClass ?> badge-soft">
                                            <?= htmlspecialchars($typeLabel) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/admin/meetings/show/<?= $meeting['id'] ?>" 
                                                class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($meeting['status'] === 'scheduled'): ?>
                                                <a href="javascript:void(0)" onclick="confirmAction('<?= BASE_URL ?>/admin/meetings?cancel=<?= $meeting['id'] ?>', 'Cancel this meeting?')" 
                                                    class="btn btn-sm btn-outline-danger" title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.badge-soft {
    background-color: rgba(255,255,255,0.7);
    color: inherit;
    font-weight: 500;
    padding: 0.35em 0.6em;
}
</style>

<script>
function confirmAction(url, message) {
    if (confirm(message)) {
        window.location.href = url;
    }
}
</script>

<script>
$(document).ready(function() {
    // Date picker initialization
    $('input[type="date"].form-control').each(function() {
        $(this).attr('type', 'date');
        $(this).attr('data-datepicker', 'true');
    });
});
</script>