<?php
/**
 * Call Recordings Index
 */

$page_title = $page_title ?? 'Call Recordings';
$recordings = $recordings ?? [];
$stats = $stats ?? ['total' => 0, 'total_duration' => 0, 'this_month' => 0, 'avg_duration' => 0];
$filters = $filters ?? [];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-phone-volume text-primary me-2"></i>Call Recordings</h4>
            <small class="text-muted">Manage call recordings for your leads</small>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-primary text-white mx-auto mb-2"><i class="fas fa-file-audio"></i></div>
                    <div class="fw-bold fs-4"><?= $stats['total'] ?></div>
                    <div class="small text-muted">Total Recordings</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-success text-white mx-auto mb-2"><i class="fas fa-clock"></i></div>
                    <div class="fw-bold fs-4"><?= gmdate('H:i:s', $stats['total_duration']) ?></div>
                    <div class="small text-muted">Total Duration</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-info text-white mx-auto mb-2"><i class="fas fa-calendar-check"></i></div>
                    <div class="fw-bold fs-4"><?= $stats['this_month'] ?></div>
                    <div class="small text-muted">This Month</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-warning text-dark mx-auto mb-2"><i class="fas fa-chart-line"></i></div>
                    <div class="fw-bold fs-4"><?= gmdate('H:i:s', $stats['avg_duration']) ?></div>
                    <div class="small text-muted">Avg Duration</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label">Lead</label>
                    <input type="text" class="form-control" name="search" placeholder="Search name, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Min Duration (min)</label>
                    <input type="number" class="form-control" name="duration_min" min="0" value="<?= htmlspecialchars($filters['duration_min'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/associate/call-recordings" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Recordings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Recordings</h6>
            <span class="badge bg-primary"><?= count($recordings) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recordings)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-audio fa-4x text-muted mb-3"></i>
                <p class="text-muted mb-2">No Call Recordings</p>
                <p class="text-muted small">Recordings from your calls will appear here.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Direction</th>
                            <th>Duration</th>
                            <th>Caller</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recordings as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['lead_name'] ?? 'Unknown') ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['lead_number'] ?? '') ?></small>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $r['call_type'] === 'video' ? 'info' : ($r['call_type'] === 'conference' ? 'warning' : 'primary') ?>">
                                    <i class="fas fa-<?= $r['call_type'] === 'video' ? 'video' : ($r['call_type'] === 'conference' ? 'users' : 'phone') ?> me-1"></i>
                                    <?= ucfirst($r['call_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $r['direction'] === 'inbound' ? 'success' : ($r['direction'] === 'inbound' ? 'success' : ($r['direction'] === 'outbound' ? 'primary' : 'secondary')) ?>">
                                    <i class="fas fa-<?= $r['direction'] === 'inbound' ? 'phone-alt' : ($r['direction'] === 'outbound' ? 'phone' : 'exchange-alt') ?> me-1"></i>
                                    <?= ucfirst($r['direction']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= gmdate('H:i:s', $r['duration']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($r['caller_number'] ?? '-') ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/associate/call-recordings/<?= $r['id'] ?>/play" class="btn btn-outline-primary" title="Play">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/associate/call-recordings/<?= $r['id'] ?>/download" class="btn btn-outline-secondary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/associate/call-recordings/<?= $r['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this recording?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
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