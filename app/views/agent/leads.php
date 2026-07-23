<?php
$leads = $leads ?? [];
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1" style="color:#15803d;font-weight:700;"><i class="fas fa-users me-2"></i>My Leads</h4>
        <p class="text-muted mb-0">Manage your assigned leads and track conversions</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success fs-6"><?= count($leads) ?> Total</span>
    </div>
</div>

<?php if (empty($leads)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-user-plus fa-2x" style="color:#15803d;"></i>
        </div>
        <h5 class="text-muted">No leads assigned yet</h5>
        <p class="text-muted mb-0">Leads assigned to you will appear here</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f0fdf4;">
                    <tr>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Name</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Contact</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Property</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Status</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Date</th>
                        <th class="px-3 py-3" style="color:#15803d;font-weight:600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td class="px-3">
                            <div class="d-flex align-items-center">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#15803d,#22c55e);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:.85rem;margin-right:10px;">
                                    <?= strtoupper(substr($lead['name'] ?? 'U', 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?></strong>
                            </div>
                        </td>
                        <td class="px-3">
                            <div><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($lead['phone'] ?? '-') ?></div>
                            <div><i class="fas fa-envelope me-1 text-muted"></i><small class="text-muted"><?= htmlspecialchars($lead['email'] ?? '-') ?></small></div>
                        </td>
                        <td class="px-3"><small><?= htmlspecialchars($lead['property_title'] ?? '-') ?></small></td>
                        <td class="px-3">
                            <?php
                            $status = $lead['status'] ?? 'new';
                            $statusColors = [
                                'new' => 'bg-primary',
                                'contacted' => 'bg-info',
                                'qualified' => 'bg-warning text-dark',
                                'proposal' => 'bg-purple text-white',
                                'negotiation' => 'bg-orange text-white',
                                'converted' => 'bg-success',
                                'lost' => 'bg-danger',
                            ];
                            $cls = $statusColors[$status] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td class="px-3"><small class="text-muted"><?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></small></td>
                        <td class="px-3">
                            <button class="btn btn-sm btn-outline-primary" title="Update Status" onclick="updateLeadStatus(<?= $lead['id'] ?? 0 ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateLeadStatus(leadId) {
    const status = prompt('Enter new status (new/contacted/qualified/proposal/negotiation/converted/lost):');
    if (!status) return;
    fetch('<?= $base ?>/agent/leads/' + leadId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({status: status})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.message || 'Failed'); })
    .catch(() => alert('Error updating status'));
}
</script>
