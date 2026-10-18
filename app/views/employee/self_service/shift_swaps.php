<?php
/**
 * Shift Swaps Dashboard
 */

$page_title = $page_title ?? 'Shift Swaps';
$swaps = $swaps ?? [];
$pending_count = $pending_count ?? 0;
$completed_count = $completed_count ?? 0;
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-exchange-alt me-2 text-primary"></i>Shift Swaps</h4>
                    <p class="text-muted mb-0 small">Request and manage shift exchanges with colleagues</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestSwapModal">
                    <i class="fas fa-plus me-1"></i> Request Shift Swap
                </button>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-primary"><?= count($swaps) ?></div>
                            <div class="small text-muted">Total Requests</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-warning"><?= $pending_count ?></div>
                            <div class="small text-muted">Pending</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-success"><?= $completed_count ?></div>
                            <div class="small text-muted">Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-danger"><?= count(array_filter($swaps, fn($s) => $s['status'] === 'rejected')) ?></div>
                            <div class="small text-muted">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Swap Rules -->
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Shift Swap Rules</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Allowed</h6>
                            <ul class="small">
                                <li>Swap with same department colleague</li>
                                <li>Same shift type (day/night)</li>
                                <li>Max 4 swaps per month</li>
                                <li>Advance notice: 48 hours</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-danger"><i class="fas fa-times-circle me-2"></i>Not Allowed</h6>
                            <ul class="small">
                                <li>Cross-department swaps</li>
                                <li>Different shift types</li>
                                <li>During critical operations</li>
                                <li>More than 2 swaps/week</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-info"><i class="fas fa-info-circle me-2"></i>Process</h6>
                            <ul class="small">
                                <li>Submit request</li>
                                <li>Colleague accepts/rejects</li>
                                <li>Manager approves</li>
                                <li>Auto-updates schedule</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Swaps List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>My Shift Swap Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($swaps)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Shift Swap Requests</h5>
                            <p class="text-muted">Request a shift swap with a colleague.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestSwapModal">
                                <i class="fas fa-plus me-1"></i> Request Shift Swap
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>My Shift</th>
                                        <th>Colleague</th>
                                        <th>Colleague's Shift</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($swaps as $s): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($s['swap_date'] ?? 'now')) ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($s['my_shift_type'] ?? '') === 'night' ? 'dark' : 'primary' ?>">
                                                <?= ucfirst($s['my_shift_type'] ?? 'day') ?>
                                            </span>
                                            <?= date('H:i', strtotime($s['my_start_time'] ?? '09:00')) ?> - 
                                            <?= date('H:i', strtotime($s['my_end_time'] ?? '18:00')) ?>
                                        </td>
                                        <td><?= htmlspecialchars($s['colleague_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($s['colleague_shift_type'] ?? '') === 'night' ? 'dark' : 'primary' ?>">
                                                <?= ucfirst($s['colleague_shift_type'] ?? 'day') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $s['type'] === 'requested' ? 'bg-info' : 'bg-secondary' ?>">
                                                <?= ucfirst($s['type'] ?? 'swap') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $s['status'] === 'pending' ? 'bg-warning text-dark' : ($s['status'] === 'completed' ? 'bg-success' : ($s['status'] === 'rejected' ? 'bg-danger' : 'bg-secondary')) ?>">
                                                <?= ucfirst($s['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (($s['status'] ?? '') === 'pending' && ($s['type'] ?? '') === 'received'): ?>
                                            <a href="<?= BASE_URL ?>/employee/self-service/shift-swaps/<?= $s['id'] ?>/respond" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check me-1"></i> Respond
                                            </a>
                                            <?php elseif (($s['status'] ?? '') === 'pending' && ($s['type'] ?? '') === 'requested'): ?>
                                            <a href="<?= BASE_URL ?>/employee/self-service/shift-swaps/<?= $s['id'] ?>/respond" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <?php else: ?>
                                            <a href="<?= BASE_URL ?>/employee/self-service/shift-swaps/<?= $s['id'] ?>/respond" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <?php endif; ?>
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
    </div>
</div>

<!-- Request Shift Swap Modal -->
<div class="modal fade" id="requestSwapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/shift-swaps/request" id="requestSwapForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Request Shift Swap</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="swap_date" class="col-sm-3 col-form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="swap_date" name="swap_date" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required min="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                            <div class="form-text">Minimum 48 hours notice required.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="target_employee_id" class="col-sm-3 col-form-label fw-bold">Colleague <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-select" id="target_employee_id" name="target_employee_id" required>
                                <option value="">Select colleague</option>
                                <?php foreach ($colleagues ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= htmlspecialchars($c['name'] ?? '') ?> - <?= htmlspecialchars($c['designation'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select a colleague from your department.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">My Shift <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" name="my_shift_type" required>
                                        <option value="day">Day Shift</option>
                                        <option value="night">Night Shift</option>
                                        <option value="evening">Evening Shift</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" name="my_start_time" value="09:00" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" name="my_end_time" value="18:00" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Colleague's Shift <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-select" name="colleague_shift_type" required>
                                        <option value="day">Day Shift</option>
                                        <option value="night">Night Shift</option>
                                        <option value="evening">Evening Shift</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" name="colleague_start_time" value="09:00" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" name="colleague_end_time" value="18:00" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="reason" class="col-sm-3 col-form-label fw-bold">Reason <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Explain why you need this shift swap..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>