<?php

/**
 * Telecaller Daily Tasks - APS Dream Home Admin
 */
$page_title = 'Telecaller Management';
$page_description = 'Manage telecaller daily tasks and performance';

$todayStats = $todayStats ?? ['total_calls' => 0, 'connected' => 0, 'converted' => 0, 'pending' => 0];
$tasks = $tasks ?? [];
$telecallers = $telecallers ?? [];
$dateFilter = $dateFilter ?? date('Y-m-d');
$telecallerFilter = $telecallerFilter ?? '';

?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Telecaller Management</h1>
            <p class="text-muted">Manage daily tasks and track performance</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-phone fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Today's Calls Made</h6>
                            <h3 class="mb-0"><?php echo number_format($todayStats['total_calls'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-phone-alt fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Connected</h6>
                            <h3 class="mb-0"><?php echo number_format($todayStats['connected'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Converted</h6>
                            <h3 class="mb-0"><?php echo number_format($todayStats['converted'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 text-danger rounded p-3">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending Calls</h6>
                            <h3 class="mb-0"><?php echo number_format($todayStats['pending'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/telecaller" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($dateFilter ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telecaller</label>
                    <select name="telecaller_id" class="form-select">
                        <option value="">All Telecallers</option>
                        <?php foreach ($telecallers as $tc): ?>
                            <option value="<?php echo e($tc['id']); ?>" <?php echo ($telecallerFilter == $tc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tc['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo BASE_URL; ?>/admin/telecaller" class="btn btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Task Modal Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Daily Task Records</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus me-1"></i>Add Daily Task
        </button>
    </div>

    <!-- Tasks Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Telecaller</th>
                            <th>Date</th>
                            <th>Leads</th>
                            <th>Calls Made</th>
                            <th>Connected</th>
                            <th>Converted</th>
                            <th>Callback</th>
                            <th>Pending</th>
                            <th>Target</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <i class="fas fa-phone-alt fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No daily tasks found</h5>
                                    <p class="text-muted mb-3">Assign daily calling tasks to your telecallers to track lead outreach, follow-ups, and conversions.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                        <i class="fas fa-plus me-1"></i> Add Daily Task
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $t): ?>
                                <tr>
                                    <td><?php echo e($t['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($t['telecaller_name'] ?? 'N/A'); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($t['email'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($t['task_date'] ?? ''); ?></td>
                                    <td><?php echo number_format($t['total_leads_assigned'] ?? 0); ?></td>
                                    <td><?php echo number_format($t['calls_made'] ?? 0); ?></td>
                                    <td><?php echo number_format($t['calls_connected'] ?? 0); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo number_format($t['leads_converted'] ?? 0); ?></span>
                                    </td>
                                    <td><?php echo number_format($t['leads_callback'] ?? 0); ?></td>
                                    <td><?php echo number_format($t['pending_calls'] ?? 0); ?></td>
                                    <td><?php echo number_format($t['target_calls'] ?? 0); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/telecaller/task/<?php echo e($t['id']); ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
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

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/telecaller/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add Daily Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Telecaller <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Select Telecaller</option>
                                <?php foreach ($telecallers as $tc): ?>
                                    <option value="<?php echo $tc['id']; ?>"><?php echo htmlspecialchars($tc['name'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="task_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Leads Assigned</label>
                            <input type="number" name="total_leads_assigned" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Calls Made</label>
                            <input type="number" name="calls_made" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Calls Connected</label>
                            <input type="number" name="calls_connected" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Leads Converted</label>
                            <input type="number" name="leads_converted" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Callbacks</label>
                            <input type="number" name="leads_callback" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Not Interested</label>
                            <input type="number" name="leads_not_interested" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pending Calls</label>
                            <input type="number" name="pending_calls" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target Calls</label>
                            <input type="number" name="target_calls" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
