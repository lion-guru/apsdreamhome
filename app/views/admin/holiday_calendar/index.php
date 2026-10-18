<?php
/**
 * Holiday Calendar Index
*/

$page_title = $page_title ?? 'Holiday Calendar';
$holidays = $holidays ?? [];
$stats = $stats ?? [];
$holiday_types = $holiday_types ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.hol-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.hol-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
.hol-national { border-left: 4px solid #dc2626; }
.hol-state { border-left: 4px solid #2563eb; }
.hol-company { border-left: 4px solid #7c2d12; }
.hol-regional { border-left: 4px solid #0891b2; }
.hol-religious { border-left: 4px solid #9333ea; }
.hol-optional { border-left: 4px solid #64748b; }
.hol-inactive { opacity: 0.5; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Holiday Calendar</h4>
            <p class="text-muted mb-0 small">Manage company holidays and regional observances</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/holiday-calendar/bulk-import" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-upload me-1"></i> Bulk Import
            </a>
            <a href="<?= BASE_URL ?>/admin/holiday-calendar/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Holiday
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Holidays</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['active'] ?? 0 ?></div>
                    <div class="text-white-50 small">Active</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['this_year'] ?? 0 ?></div>
                    <div class="text-white-50 small">This Year</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= count(array_filter($stats['by_type'] ?? [], fn($t) => $t['type'] === 'national')) ?></div>
                    <div class="text-white-50 small">National</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($holiday_types as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Region</label>
                    <input type="text" name="region" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['region'] ?? '') ?>" placeholder="e.g., IN, US">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">All Years</option>
                        <?php for ($y = date('Y') + 2; $y >= date('Y') - 5; $y--): ?>
                            <option value="<?= $y ?>" <?= ($filters['year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($filters['month'] ?? '') == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Holidays List -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($holidays)): ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Holidays Found</h5>
                <p class="text-muted small mb-3">Add your first holiday or adjust filters</p>
                <a href="<?= BASE_URL ?>/admin/holiday-calendar/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Holiday
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="holidaysTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Region</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holidays as $h): 
                            $typeClass = 'hol-' . $h['type'];
                            $isActive = (bool)$h['is_active'];
                        ?>
                        <tr class="hol-card <?= $typeClass ?> <?= !$isActive ? 'hol-inactive' : '' ?>">
                            <td>
                                <strong><?= date('d M Y', strtotime($h['date'])) ?></strong>
                                <?php if ($h['is_half_day']): ?>
                                    <span class="badge bg-info ms-1">Half Day</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($h['name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= 
                                    $h['type'] === 'national' ? 'danger' :
                                    ($h['type'] === 'state' ? 'primary' :
                                    ($h['type'] === 'company' ? 'warning' :
                                    ($h['type'] === 'regional' ? 'info' :
                                    ($h['type'] === 'religious' ? 'purple' : 'secondary')))) ?>">
                        <?= ucfirst($h['type']) ?>
                    </span>
                            </td>
                            <td><?= htmlspecialchars($h['region'] ?? 'All') ?></td>
                            <td class="text-muted small" style="max-width: 200px;">
                                <?= htmlspecialchars($h['description'] ?? '-') ?>
                            </td>
                            <td>
                                <?php if ($h['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/holiday-calendar/<?= $h['id'] ?>" class="btn btn-outline-primary btn-sm" title="View/Edit">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/holiday-calendar/<?= $h['id'] ?>/edit" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/holiday-calendar/<?= $h['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this holiday?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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