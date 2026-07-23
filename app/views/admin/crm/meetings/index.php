<?php $page_title = $page_title ?? 'Meetings'; $meetings = $meetings ?? []; $stats = $stats ?? []; ?>
<style>.mtg-stat{background:#fff;border-radius:14px;border:1px solid #f0f0f5;padding:16px;text-align:center}.mtg-stat .val{font-size:24px;font-weight:800}.mtg-stat .lbl{font-size:11px;color:#888;text-transform:uppercase}</style>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Meetings</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMeeting"><i class="fas fa-plus me-1"></i>Schedule Meeting</button>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2"><div class="mtg-stat"><div class="val text-primary"><?= $stats['total'] ?? 0 ?></div><div class="lbl">Total</div></div></div>
        <div class="col-md-2"><div class="mtg-stat"><div class="val text-info"><?= $stats['scheduled'] ?? 0 ?></div><div class="lbl">Scheduled</div></div></div>
        <div class="col-md-2"><div class="mtg-stat"><div class="val text-success"><?= $stats['completed'] ?? 0 ?></div><div class="lbl">Completed</div></div></div>
        <div class="col-md-2"><div class="mtg-stat"><div class="val text-danger"><?= $stats['cancelled'] ?? 0 ?></div><div class="lbl">Cancelled</div></div></div>
        <div class="col-md-2"><div class="mtg-stat"><div class="val text-warning"><?= $stats['no_show'] ?? 0 ?></div><div class="lbl">No Show</div></div></div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Title</th><th>Lead</th><th>Agent</th><th>Type</th><th>Date/Time</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php if (empty($meetings)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No meetings scheduled</td></tr>
    <?php else: foreach ($meetings as $m): ?><tr>
        <td class="fw-bold"><?= htmlspecialchars($m['title']) ?></td>
        <td><?= htmlspecialchars($m['lead_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($m['agent_name'] ?? '-') ?></td>
        <td><span class="badge bg-light text-dark"><?= str_replace('_',' ',ucfirst($m['meeting_type'])) ?></span></td>
        <td><?= date('d M Y H:i', strtotime($m['start_time'])) ?></td>
        <td><span class="badge bg-<?= $m['status']==='completed'?'success':($m['status']==='cancelled'?'danger':($m['status']==='no_show'?'warning':'info')) ?>"><?= ucfirst(str_replace('_',' ',$m['status'])) ?></span></td>
        <td>
            <?php if ($m['status']==='scheduled'): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/meetings/<?= $m['id'] ?>/complete" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><input type="hidden" name="outcome" value="completed"><button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button></form>
            <form method="POST" action="<?= BASE_URL ?>/admin/meetings/<?= $m['id'] ?>/cancel" class="d-inline"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>"><button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
            <?php endif; ?>
        </td>
    </tr><?php endforeach; endif; ?>
    </tbody></table></div></div></div>
</div>

<div class="modal fade" id="createMeeting" tabindex="-1"><div class="modal-dialog"><div class="modal-content" style="border-radius:14px">
    <div class="modal-header"><h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Schedule Meeting</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/meetings/store"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <div class="modal-body">
        <div class="mb-3"><label class="form-label fw-bold">Title</label><input type="text" name="title" class="form-control" required></div>
        <div class="row mb-3"><div class="col-6"><label class="form-label fw-bold">Lead ID</label><input type="number" name="lead_id" class="form-control" required></div><div class="col-6"><label class="form-label fw-bold">Agent ID</label><input type="number" name="user_id" class="form-control" required></div></div>
        <div class="mb-3"><label class="form-label fw-bold">Type</label><select name="meeting_type" class="form-select"><option value="site_visit">Site Visit</option><option value="office_call">Office Call</option><option value="phone">Phone</option><option value="video">Video</option></select></div>
        <div class="row mb-3"><div class="col-6"><label class="form-label fw-bold">Start</label><input type="datetime-local" name="start_time" class="form-control" required></div><div class="col-6"><label class="form-label fw-bold">End</label><input type="datetime-local" name="end_time" class="form-control"></div></div>
        <div class="mb-3"><label class="form-label fw-bold">Location</label><input type="text" name="location" class="form-control"></div>
        <div class="mb-3"><label class="form-label fw-bold">Notes</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Schedule</button></div>
    </form>
</div></div></div>
