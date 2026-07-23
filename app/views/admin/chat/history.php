<?php
/** @var array $conversations List of conversations */
/** @var int $total Total count */
/** @var int $page Current page */
/** @var int $totalPages Total pages */
/** @var string $statusFilter Current status filter */
/** @var string $actionFilter Current action filter */
/** @var array $actionLabels Action label map */
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$statusColors = [
    'active' => 'primary',
    'confirm' => 'warning',
    'completed' => 'success',
    'cancelled' => 'secondary',
    'expired' => 'danger',
];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📋 Chat History</h2>
            <p class="text-muted mb-0"><?=$total?> conversations found</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?=$base?>/admin/chat-analytics" class="btn btn-outline-primary btn-sm">📊 Analytics</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?=$base?>/admin/chat-history" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['active','confirm','completed','cancelled','expired'] as $s): ?>
                        <option value="<?=$s?>" <?=$statusFilter===$s?'selected':''?>><?=ucfirst($s)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($actionLabels as $key => $label): ?>
                        <option value="<?=$key?>" <?=$actionFilter===$key?'selected':''?>><?=$label?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?=$base?>/admin/chat-history" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversations Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($conversations)): ?>
            <p class="text-muted text-center py-5">No conversations found.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Step</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conversations as $c): ?>
                        <?php $collected = json_decode($c['collected_data'] ?? '{}', true) ?: []; ?>
                        <tr>
                            <td><?=$c['id']?></td>
                            <td><code class="small"><?=htmlspecialchars(substr($c['session_id'], 0, 12))?>...</code></td>
                            <td><?=$c['user_name'] ?? ($c['user_id'] ? 'User #'.$c['user_id'] : 'Guest')?></td>
                            <td><span class="badge bg-light text-dark"><?=ucfirst($c['user_role'] ?? 'guest')?></span></td>
                            <td><?=$actionLabels[$c['current_action']] ?? $c['current_action']?></td>
                            <td class="text-center">
                                <?php if ($c['status'] === 'confirm'): ?>
                                <span class="badge bg-warning">Confirm</span>
                                <?php else: ?>
                                <?=intval($c['current_step'])+1?>/<?=intval($c['step_count'])?>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?=$statusColors[$c['status']] ?? 'secondary'?>"><?=ucfirst($c['status'])?></span></td>
                            <td class="small"><?=date('d M Y, g:i a', strtotime($c['created_at']))?></td>
                        </tr>
                        <?php if (!empty($collected)): ?>
                        <tr>
                            <td colspan="8" class="small text-muted" style="background:#f8f9fa">
                                <?php foreach ($collected as $k => $v): ?>
                                <span class="me-3"><strong><?=$k?>:</strong> <?=htmlspecialchars(is_null($v) ? '(skip)' : (string)$v)?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-center">
                    <?php for ($i = max(1, $page-3); $i <= min($totalPages, $page+3); $i++): ?>
                    <li class="page-item <?=$i===$page?'active':''?>">
                        <a class="page-link" href="<?=$base?>/admin/chat-history?page=<?=$i?>&status=<?=$statusFilter?>&action=<?=$actionFilter?>"><?=$i?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
