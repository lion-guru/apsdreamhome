<?php
/**
 * Department Request Detail View
 */
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Request #<?= $request['id'] ?> — <?= htmlspecialchars($request['title']) ?></h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/department-requests" class="btn btn-outline-secondary btn-sm">Back to List</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>Request Details</span>
                <span class="badge bg-<?= $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : ($request['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                    Priority: <?= ucfirst($request['priority']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong>Type:</strong> <?= ucfirst($request['request_type']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Department:</strong> <?= htmlspecialchars($request['department_name'] ?? $request['department_code']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> 
                        <span class="badge bg-<?= $request['status'] === 'open' ? 'secondary' : ($request['status'] === 'in_progress' ? 'warning' : ($request['status'] === 'resolved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'info'))) ?>">
                            <?= ucfirst($request['status']) ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Assigned To:</strong> <?= htmlspecialchars($request['assignee_name'] ?? 'Unassigned') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Requester:</strong> <?= htmlspecialchars($request['requester_name'] ?? $request['requester_name_full'] ?? '') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong> <?= date('M j, Y g:i a', strtotime($request['created_at'])) ?>
                    </div>
                    <?php if ($request['due_date']): ?>
                    <div class="col-12">
                        <strong>Due Date:</strong> <?= date('M j, Y', strtotime($request['due_date'])) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['related_entity_type']): ?>
                    <div class="col-12">
                        <strong>Related:</strong> <?= ucfirst($request['related_entity_type']) ?> #<?= $request['related_entity_id'] ?>
                    </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <strong>Description:</strong>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($request['description'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Form -->
        <div class="card mb-3">
            <div class="card-header">Update Status</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/department-requests/<?= $request['id'] ?>/status">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <select class="form-select" name="status" required>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="rejected">Rejected</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="comment" placeholder="Add a comment (optional)">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assign Form -->
        <div class="card mb-3">
            <div class="card-header">Assign Request</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/department-requests/<?= $request['id'] ?>/assign">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="number" class="form-control" name="user_id" placeholder="User ID">
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="role" placeholder="Role (e.g. legal_head)">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Assign</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Comments -->
        <div class="card">
            <div class="card-header">Comments & Activity</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/department-requests/<?= $request['id'] ?>/comment" class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="row g-2">
                        <div class="col-12">
                            <textarea class="form-control" name="comment" rows="2" placeholder="Add a comment..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-check">
                                <input type="checkbox" name="is_internal" value="1" class="form-check-input">
                                <label class="form-check-label">Internal comment (not visible to requester)</label>
                            </label>
                            <button type="submit" class="btn btn-sm btn-primary">Add Comment</button>
                        </div>
                    </div>
                </form>

                <?php if (empty($comments)): ?>
                <p class="text-muted text-center py-3">No comments yet</p>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($comments as $comment): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong><?= htmlspecialchars($comment['commenter_name']) ?></strong>
                            <small class="text-muted"><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
                        </div>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                        <?php if ($comment['is_internal']): ?>
                        <span class="badge bg-warning mt-1">Internal</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Quick Actions</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/admin/department-requests/submit" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                    <a href="<?= BASE_URL ?>/admin/department-requests/my-requests" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i> My Requests
                    </a>
                    <a href="<?= BASE_URL ?>/admin/department-requests/list?department=<?= $request['department_name'] ?? '' ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-building"></i> Same Department
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>