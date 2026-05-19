<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i><?= ($page_title ?? 'Events') ?></h4>
        <p class="text-muted mb-0 small"><?= ($page_description ?? 'Manage events and subscriptions') ?></p>
    </div>

    <?php if (!empty($stats ?? [])): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= ($stats['total_events'] ?? 0) ?></h3>
                    <small>Total Events</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= ($stats['active_subscriptions'] ?? 0) ?></h3>
                    <small>Subscriptions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= ($stats['today_events'] ?? 0) ?></h3>
                    <small>Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= ($stats['pending_events'] ?? 0) ?></h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Recent Events</h6>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishEventModal"><i class="fas fa-plus me-1"></i>Publish Event</button>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($recent_events ?? [])): ?>
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover align-middle mb-0 table-responsive">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Type</th><th>Priority</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach (($recent_events ?? []) as $e): ?>
                        <tr>
                            <td><?= ($e['id'] ?? '#') ?></td>
                            <td><?= htmlspecialchars($e['event_name'] ?? $e['name'] ?? '') ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($e['event_type'] ?? $e['type'] ?? 'general') ?></span></td>
                            <td><span class="badge bg-<?= ($e['priority'] ?? 1) > 5 ? 'danger' : 'secondary' ?>"><?= ($e['priority'] ?? 1) ?></span></td>
                            <td><?= htmlspecialchars($e['created_at'] ?? $e['date'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                <p>No events recorded yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
