<?php
$page_title = 'Registry Management';
$active_page = 'registry';

$registryLabels = [
    'not_started' => ['label' => 'Not Started', 'class' => 'secondary'],
    'documents_pending' => ['label' => 'Documents Pending', 'class' => 'warning'],
    'stamp_duty_pending' => ['label' => 'Stamp Duty Pending', 'class' => 'info'],
    'appointment_scheduled' => ['label' => 'Appt. Scheduled', 'class' => 'primary'],
    'registered' => ['label' => 'Registered', 'class' => 'success'],
    'mutation_pending' => ['label' => 'Mutation Pending', 'class' => 'dark'],
    'completed' => ['label' => 'Completed', 'class' => 'success'],
    'cancelled' => ['label' => 'Cancelled', 'class' => 'danger'],
];
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-signature"></i> Registry Management</h1>
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

<div class="row mb-4">
    <?php foreach ($registryLabels as $key => $info): ?>
        <div class="col-md-3 mb-3">
            <div class="card bg-<?= $info['class'] ?> text-white h-100">
                <div class="card-body text-center">
                    <h3><?= intval($status_counts[$key] ?? 0) ?></h3>
                    <p class="mb-0 small"><?= $info['label'] ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card mb-4">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/registry">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Booking #, Customer, Property">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Registry Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <?php foreach ($registryLabels as $key => $info): ?>
                            <option value="<?= $key ?>" <?= ($filters['status'] ?? '') == $key ? 'selected' : '' ?>><?= $info['label'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                        <a href="<?= BASE_URL ?>/admin/registry" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card aps-cp-card">
    <div class="card-header aps-cp-card-header">
        <h5 class="card-title mb-0"><i class="fas fa-list"></i> Registry List</h5>
    </div>
    <div class="card-body aps-cp-card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Property</th>
                        <th>Customer</th>
                        <th>Registry Status</th>
                        <th>Mutation</th>
                        <th>Booked On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-file-signature fa-3x text-muted mb-3" style="opacity:0.2"></i>
                                <h5 class="text-muted">No registry records found</h5>
                                <p class="text-muted mb-3">Registry records are created automatically from confirmed bookings. Start by creating a property booking.</p>
                                <a href="<?= BASE_URL ?>/admin/bookings" class="btn btn-primary">
                                    <i class="fas fa-calendar-check me-1"></i> View Bookings
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['booking_number'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($b['property_title'] ?? 'N/A') ?><br><small class="text-muted"><?= htmlspecialchars($b['property_location'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?><br><small class="text-muted"><?= htmlspecialchars($b['customer_email'] ?? '') ?></small></td>
                                <td>
                                    <?php $rs = $b['registry_status'] ?? 'not_started'; $ri = $registryLabels[$rs] ?? ['label' => ucfirst($rs), 'class' => 'secondary']; ?>
                                    <span class="badge bg-<?= $ri['class'] ?>"><?= $ri['label'] ?></span>
                                </td>
                                <td>
                                    <?php $ms = $b['mutation_status'] ?? 'pending'; ?>
                                    <span class="badge bg-<?= $ms === 'completed' ? 'success' : ($ms === 'in_progress' ? 'warning' : 'secondary') ?>"><?= ucfirst($ms) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($b['created_at'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/registry/show/<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Registry"><i class="fas fa-eye"></i></a>
                                    <?php if (($b['registry_status'] ?? '') === 'registered' || ($b['registry_status'] ?? '') === 'completed'): ?>
                                        <a href="<?= BASE_URL ?>/admin/registry/certificate/<?= $b['id'] ?>" class="btn btn-sm btn-outline-success" title="Download Certificate" target="_blank"><i class="fas fa-file-pdf"></i></a>
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
