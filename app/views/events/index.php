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

<!-- Publish Event Modal -->
<div class="modal fade" id="publishEventModal" tabindex="-1" aria-labelledby="publishEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishEventModalLabel"><i class="fas fa-paper-plane me-2"></i>Publish Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="publishEventForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label" for="evName">Event Name *</label>
                        <input type="text" class="form-control" id="evName" name="event_name" required maxlength="255" placeholder="e.g. booking.completed">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="evType">Event Type</label>
                            <select class="form-select" id="evType" name="event_type">
                                <option value="user">User</option>
                                <option value="system">System</option>
                                <option value="booking">Booking</option>
                                <option value="payment">Payment</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="evPriority">Priority (1-10)</label>
                            <input type="number" class="form-control" id="evPriority" name="priority" min="1" max="10" value="1">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="evData">Event Data (JSON, optional)</label>
                        <textarea class="form-control" id="evData" name="event_data_raw" rows="3" placeholder='{"key": "value"}'></textarea>
                    </div>
                    <div id="publishEventStatus" class="small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="publishEventBtn"><i class="fas fa-paper-plane me-1"></i>Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var form = document.getElementById('publishEventForm');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('publishEventBtn');
        var statusEl = document.getElementById('publishEventStatus');
        var eventData = {};
        var raw = document.getElementById('evData').value.trim();
        if (raw !== '') {
            try { eventData = JSON.parse(raw); }
            catch (err) { statusEl.innerHTML = '<span class="text-danger">Event Data is not valid JSON.</span>'; return; }
        }
        btn.disabled = true;
        statusEl.innerHTML = '<span class="text-muted">Publishing...</span>';
        var fd = new FormData(form);
        fd.delete('event_data_raw');
        Object.keys(eventData).forEach(function(k) { fd.append('event_data[' + k + ']', eventData[k]); });
        fetch('<?= BASE_URL ?>/admin/events/publish', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.success) {
                statusEl.innerHTML = '<span class="text-success">' + (data.message || 'Published!') + ' Reloading...</span>';
                setTimeout(function() { window.location.reload(); }, 900);
            } else {
                statusEl.innerHTML = '<span class="text-danger">' + ((data && data.message) || 'Publish failed.') + '</span>';
                btn.disabled = false;
            }
        })
        .catch(function() {
            statusEl.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
            btn.disabled = false;
        });
    });
})();
</script>
