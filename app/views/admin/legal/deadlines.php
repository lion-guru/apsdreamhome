<?php
$page_title = $page_title ?? 'Legal Deadlines';
$deadlines = $deadlines ?? [];
$total = $total ?? 0;
$overdue = $overdue ?? 0;
$upcoming = $upcoming ?? 0;
$now = date('Y-m-d');
$weekLater = date('Y-m-d', strtotime('+7 days'));
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Legal Deadlines</h1>
            <p class="text-muted">Track critical legal deadlines and dates</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Deadlines</h6>
                            <h3 class="mb-0"><?php echo $total; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Overdue</h6>
                            <h3 class="mb-0 text-danger"><?php echo $overdue; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Due Within 7 Days</h6>
                            <h3 class="mb-0 text-warning"><?php echo $upcoming; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Deadlines</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Deadline</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($deadlines)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-times fa-3x d-block mb-3"></i>
                                        No deadlines found
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($deadlines as $i => $d):
                                    $dlDate = $d['deadline_date'] ?? '';
                                    $isOverdue = $dlDate < $now;
                                    $isUpcoming = !$isOverdue && $dlDate <= $weekLater;
                                    $rowClass = $isOverdue ? 'table-danger' : ($isUpcoming ? 'table-warning' : '');
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="ps-4"><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo $d['title'] ?? ''; ?></strong>
                                        <?php if (!empty($d['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($d['description'], 0, 80)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?php echo $d['legal_type'] ?? '-'; ?></span></td>
                                    <td>
                                        <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ($isUpcoming ? 'text-warning fw-bold' : ''); ?>">
                                            <i class="fas fa-calendar-day me-1"></i><?php echo $dlDate ? date('d M Y', strtotime($dlDate)) : '-'; ?>
                                        </span>
                                        <?php if ($isOverdue): ?>
                                            <br><small class="text-danger fw-bold"><i class="fas fa-exclamation-circle"></i> Overdue</small>
                                        <?php elseif ($isUpcoming): ?>
                                            <br><small class="text-warning fw-bold"><i class="fas fa-clock"></i> Due soon</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $d['assigned_name'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($d['status'] ?? '') === 'completed' ? 'success' : (($d['status'] ?? '') === 'missed' ? 'danger' : 'warning'); ?>-subtle text-<?php echo ($d['status'] ?? '') === 'completed' ? 'success' : (($d['status'] ?? '') === 'missed' ? 'danger' : 'warning'); ?> rounded-pill px-3">
                                            <?php echo ucfirst($d['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add Deadline</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/legal/deadlines/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Legal Type</label>
                            <select name="legal_type" class="form-select">
                                <option value="filing">Filing</option>
                                <option value="hearing">Hearing</option>
                                <option value="documentation">Documentation</option>
                                <option value="compliance">Compliance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deadline Date <span class="text-danger">*</span></label>
                            <input type="date" name="deadline_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">Unassigned</option>
                                <?php $users = $users ?? []; foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo $u['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Add Deadline</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
