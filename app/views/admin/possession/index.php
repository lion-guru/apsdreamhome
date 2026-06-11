<?php
$page_title = $page_title ?? 'Possession Handover';
$active_page = 'possession';

$possessionLabels = [
    'not_due' => ['label' => 'Not Due', 'class' => 'secondary', 'icon' => 'fa-clock'],
    'ready' => ['label' => 'Ready', 'class' => 'success', 'icon' => 'fa-check-circle'],
    'scheduled' => ['label' => 'Scheduled', 'class' => 'primary', 'icon' => 'fa-calendar-check'],
    'handed_over' => ['label' => 'Handed Over', 'class' => 'info', 'icon' => 'fa-home'],
    'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fa-exclamation-triangle'],
];

$defectLabels = [
    'open' => ['label' => 'Open', 'class' => 'danger'],
    'in_progress' => ['label' => 'In Progress', 'class' => 'warning'],
    'resolved' => ['label' => 'Resolved', 'class' => 'success'],
    'closed' => ['label' => 'Closed', 'class' => 'secondary'],
];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-key"></i> Possession Handover</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/possession/dashboard" class="btn btn-outline-primary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (isset($stats) && !empty($stats)): ?>
<div class="row mb-4">
    <?php foreach ($possessionLabels as $key => $info): ?>
        <div class="col-md mb-3">
            <div class="card bg-<?= $info['class'] ?> text-white h-100">
                <div class="card-body text-center py-3">
                    <div class="fs-1"><i class="fas <?= $info['icon'] ?>"></i></div>
                    <h3 class="mb-0"><?= intval($stats[$key . '_count'] ?? 0) ?></h3>
                    <p class="mb-0 small"><?= $info['label'] ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (isset($defect_stats) && !empty($defect_stats)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-bug"></i> Defect Reports Summary</h5></div>
            <div class="card-body aps-cp-card-body">
                <div class="row text-center">
                    <div class="col-3"><span class="badge bg-danger fs-6"><?= intval($defect_stats['open_defects'] ?? 0) ?></span><br><small>Open</small></div>
                    <div class="col-3"><span class="badge bg-warning fs-6"><?= intval($defect_stats['in_progress_defects'] ?? 0) ?></span><br><small>In Progress</small></div>
                    <div class="col-3"><span class="badge bg-success fs-6"><?= intval($defect_stats['resolved_defects'] ?? 0) ?></span><br><small>Resolved</small></div>
                    <div class="col-3"><span class="badge bg-secondary fs-6"><?= intval($defect_stats['closed_defects'] ?? 0) ?></span><br><small>Closed</small></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/possession">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Booking #, Customer, Property">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Possession Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($possessionLabels as $key => $info): ?>
                            <option value="<?= $key ?>" <?= ($filters['status'] ?? '') == $key ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        <a href="<?= BASE_URL ?>/admin/possession" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list"></i> Possession Records</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Property / Plot</th>
                        <th>Customer</th>
                        <th>Possession Status</th>
                        <th>Possession Date</th>
                        <th>Letter #</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="7" class="text-center">No possession records found</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <?php $ps = $b['possession_status'] ?? 'not_due'; $pi = $possessionLabels[$ps] ?? ['label' => ucfirst($ps), 'class' => 'secondary']; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['booking_number'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($b['property_title'] ?? 'N/A') ?><br><small class="text-muted">Plot: <?= htmlspecialchars($b['plot_number'] ?? 'N/A') ?>, <?= htmlspecialchars($b['colony_name'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?><br><small class="text-muted"><?= htmlspecialchars($b['customer_email'] ?? '') ?></small></td>
                                <td><span class="badge bg-<?= $pi['class'] ?> fs-6"><?= $pi['label'] ?></span></td>
                                <td><?= !empty($b['possession_date']) ? date('d M Y', strtotime($b['possession_date'])) : '-' ?></td>
                                <td><small><?= htmlspecialchars($b['possession_letter_number'] ?? '-') ?></small></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/possession/show/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if ($ps === 'handed_over' && !empty($b['possession_letter_number'])): ?>
                                        <a href="<?= BASE_URL ?>/admin/possession/letter/<?= $b['id'] ?>" class="btn btn-sm btn-outline-success" title="Download Letter" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
