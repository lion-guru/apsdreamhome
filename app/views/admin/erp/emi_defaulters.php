<?php
// Overdue EMI aging tracker. Vars: $rows, $buckets, $total_due, $csrf_token. Layout-rendered.
$rows = $rows ?? [];
$buckets = $buckets ?? ['grace' => 0, 'warning' => 0, 'late' => 0, 'critical' => 0];
$total_due = $total_due ?? 0;
$csrf = $csrf_token ?? '';
$badge = ['grace' => 'success', 'warning' => 'warning', 'late' => 'orange', 'critical' => 'danger'];
?>
<style>
.def-card { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.bg-orange { background: #fd7e14 !important; }
.text-orange { color: #fd7e14 !important; }
.border-orange { border-color: #fd7e14 !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Overdue EMI Tracker</h4>
    <a href="<?php echo BASE_URL; ?>/admin/erp/colony-pnl" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-line me-1"></i>Colony P&amp;L</a>
</div>

<div class="row g-3 mb-4">
    <?php
    $chips = [
        ['1–30 Days · Gentle Reminder', $buckets['grace'] ?? 0, 'success', 'fa-smile'],
        ['31–60 Days · Warning', $buckets['warning'] ?? 0, 'warning', 'fa-bell'],
        ['61–90 Days · Late Fee', $buckets['late'] ?? 0, 'orange', 'fa-clock'],
        ['90+ Days · Critical', $buckets['critical'] ?? 0, 'danger', 'fa-fire'],
    ];
    foreach ($chips as $c): ?>
    <div class="col-md-3 col-6">
        <div class="card def-card border-start border-4 border-<?php echo $c[2]; ?>">
            <div class="card-body py-3 text-center">
                <div class="fs-3 fw-bold"><?php echo (int)$c[1]; ?></div>
                <div class="text-muted small text-uppercase"><i class="fas <?php echo $c[3]; ?> me-1"></i><?php echo $c[0]; ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="alert alert-info">Total overdue outstanding: <strong>₹<?php echo number_format($total_due, 2); ?></strong> across <?php echo count($rows); ?> schedule(s).</div>
<div id="wa-status" class="alert alert-secondary d-none"></div>

<div class="card def-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Customer</th><th>Booking / Plot</th><th>EMI #</th><th>Due Date</th><th class="text-end">Due</th><th class="text-end">Penalty</th><th>Aging</th><th>Reminders</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No overdue EMIs. All clear!</td></tr>
                    <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><strong><?php echo e($r['customer_name'] ?? '—'); ?></strong><br><small class="text-muted"><?php echo e($r['customer_phone'] ?? ''); ?></small></td>
                            <td><?php echo e($r['booking_number'] ?? ('#' . ($r['booking_id'] ?? ''))); ?><br><small class="text-muted"><?php echo e($r['plot_number'] ?? ''); ?> · <?php echo e($r['colony_name'] ?? ''); ?></small></td>
                            <td>#<?php echo (int)($r['installment_no'] ?? 0); ?></td>
                            <td><?php echo e($r['due_date'] ?? ''); ?><br><small class="text-muted"><?php echo (int)($r['overdue_days'] ?? 0); ?> days ago</small></td>
                            <td class="text-end"><strong>₹<?php echo number_format($r['due_amount'] ?? 0, 2); ?></strong></td>
                            <td class="text-end text-danger">₹<?php echo number_format($r['penalty'] ?? 0, 2); ?></td>
                            <td><span class="badge bg-<?php echo $badge[$r['bucket'] ?? 'grace']; ?>"><?php echo e($r['bucket_label'] ?? ''); ?></span></td>
                            <td><?php echo (int)($r['reminder_count'] ?? 0); ?></td>
                            <td><button class="btn btn-sm btn-success" onclick="sendWaReminder(<?php echo (int)($r['schedule_id'] ?? 0); ?>, this)">📲 WhatsApp Reminder</button></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function sendWaReminder(scheduleId, btn) {
    if (!confirm('Send WhatsApp EMI reminder for schedule #' + scheduleId + '?')) return;
    btn.disabled = true;
    var box = document.getElementById('wa-status');
    fetch('<?php echo BASE_URL; ?>/admin/erp/send-emi-reminder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?php echo $csrf; ?>', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ schedule_id: scheduleId, csrf_token: '<?php echo $csrf; ?>' })
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
        btn.disabled = false;
        box.classList.remove('d-none');
        if (d.success && d.whatsapp_url) {
            box.className = 'alert alert-success';
            box.textContent = 'Reminder ready (' + (d.api_status || 'ok') + '). Opening WhatsApp…';
            window.open(d.whatsapp_url, '_blank');
            setTimeout(function () { location.reload(); }, 1500);
        } else {
            box.className = 'alert alert-danger';
            box.textContent = 'Failed: ' + (d.error || 'unknown error');
        }
    })
    .catch(function (e) {
        btn.disabled = false;
        box.classList.remove('d-none');
        box.className = 'alert alert-danger';
        box.textContent = 'Request failed: ' + e.message;
    });
}
</script>
