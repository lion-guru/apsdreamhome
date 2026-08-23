<?php
$page_title = $page_title ?? 'Meeting Details';
$meeting = $meeting ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-1"><i class="fas fa-calendar-check me-2 text-primary"></i>Meeting Details</h2>
        <div>
            <a href="<?php echo e($base); ?>/admin/meetings" class="btn btn-outline-secondary">Back to Meetings</a>
        </div>
    </div>

    <?php if (!empty($meeting)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><?php echo htmlspecialchars($meeting['title'] ?? 'Meeting'); ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted" class="style-17160">Type</td>
                                <td><span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($meeting['meeting_type'] ?? 'meeting')); ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td><span class="badge bg-<?php echo ($meeting['status'] ?? '') === 'completed' ? 'success' : (($meeting['status'] ?? '') === 'cancelled' ? 'danger' : 'warning'); ?>"><?php echo ucfirst(htmlspecialchars($meeting['status'] ?? 'scheduled')); ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date & Time</td>
                                <td>
                                    <?php if (!empty($meeting['start_time'])): ?>
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('D, M d, Y \a\t h:i A', strtotime($meeting['start_time'])); ?>
                                        <?php if (!empty($meeting['end_time'])): ?>
                                            — <?php echo date('h:i A', strtotime($meeting['end_time'])); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Not scheduled
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Location</td>
                                <td><?php echo htmlspecialchars($meeting['location'] ?? 'Not set'); ?></td>
                            </tr>
                            <?php if (!empty($meeting['lead_id'])): ?>
                            <tr>
                                <td class="text-muted">Lead</td>
                                <td><a href="<?php echo e($base); ?>/admin/leads/<?php echo $meeting['lead_id']; ?>">Lead #<?php echo $meeting['lead_id']; ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($meeting['user_id'])): ?>
                            <tr>
                                <td class="text-muted">Assigned To</td>
                                <td>User #<?php echo e($meeting['user_id']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <?php if (!empty($meeting['description'])): ?>
                            <h6 class="text-muted mt-3">Description</h6>
                            <p><?php echo nl2br(htmlspecialchars($meeting['description'] ?? '')); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($meeting['notes'])): ?>
                            <h6 class="text-muted mt-3">Notes</h6>
                            <p><?php echo nl2br(htmlspecialchars($meeting['notes'] ?? '')); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($meeting['outcome'])): ?>
                            <h6 class="text-muted mt-3">Outcome</h6>
                            <p><?php echo nl2br(htmlspecialchars($meeting['outcome'] ?? '')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0">Quick Info</h6></div>
                    <div class="card-body">
                        <p class="mb-2"><small class="text-muted">Created:</small><br><?php echo isset($meeting['created_at']) ? date('M d, Y h:i A', strtotime($meeting['created_at'])) : '-'; ?></p>
                        <?php if (!empty($meeting['updated_at'])): ?>
                            <p class="mb-0"><small class="text-muted">Last Updated:</small><br><?php echo date('M d, Y h:i A', strtotime($meeting['updated_at'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (($meeting['status'] ?? '') !== 'completed' && ($meeting['status'] ?? '') !== 'cancelled'): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Actions</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo e($base); ?>/admin/meetings/<?php echo $meeting['id']; ?>/update" class="mb-2">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success w-100 btn-sm"><i class="fas fa-check me-2"></i>Mark Completed</button>
                        </form>
                        <form method="POST" action="<?php echo e($base); ?>/admin/meetings/<?php echo $meeting['id']; ?>/update">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline-danger w-100 btn-sm" data-aps-confirm="Cancel this meeting?"><i class="fas fa-times me-2"></i>Cancel Meeting</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">Meeting not found.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
