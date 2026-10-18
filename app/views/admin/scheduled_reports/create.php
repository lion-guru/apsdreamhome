<?php
$frequencies = $frequencies ?? [];
$formats = $formats ?? [];
$reportTypes = $reportTypes ?? [];
$daysOfWeek = $daysOfWeek ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Scheduled Report</h1>
        <a href="<?= BASE_URL ?>/admin/scheduled-reports" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form id="srForm" onsubmit="return submitSr(event);">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Weekly Sales Summary">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Report Type <span class="text-danger">*</span></label>
                    <select name="report_type" class="form-select" required>
                        <?php foreach ($reportTypes as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format</label>
                    <select name="format" class="form-select">
                        <?php foreach ($formats as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select" id="frequency" onchange="toggleFrequencyFields()">
                        <?php foreach ($frequencies as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3" id="dayOfWeekField" style="display:none">
                    <label class="form-label">Day of Week</label>
                    <select name="day_of_week" class="form-select">
                        <?php foreach ($daysOfWeek as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3" id="dayOfMonthField" style="display:none">
                    <label class="form-label">Day of Month</label>
                    <input type="number" name="day_of_month" class="form-control" min="1" max="31" value="1">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <label class="form-check-label mb-0"><input type="checkbox" name="status" value="active" checked class="form-check-input me-2"> Active</label>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label">Recipients (comma-separated emails)</label>
                    <input type="text" name="recipients" class="form-control" placeholder="finance@apsdreamhome.com, manager@apsdreamhome.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Schedule</button>
                <a href="<?= BASE_URL ?>/admin/scheduled-reports" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>

<script>
function toggleFrequencyFields() {
    const freq = document.getElementById('frequency').value;
    document.getElementById('dayOfWeekField').style.display = freq === 'weekly' ? '' : 'none';
    document.getElementById('dayOfMonthField').style.display = freq === 'monthly' ? '' : 'none';
}
function submitSr(e) {
    const btn = e.target.querySelector('button[type="submit"]');
    const orig = btn.innerText;
    btn.innerText = 'Creating...'; btn.disabled = true;
    const formData = new FormData(e.target);
    fetch('<?= BASE_URL ?>/admin/scheduled-reports/store', {
        method: 'POST',
        headers: {'X-Requested-With':'XMLHttpRequest'},
        body: formData
    }).then(r => r.json()).then(d => {
        btn.innerText = orig; btn.disabled = false;
        if (d.success) { location.href = d.redirect; }
        else { alert(d.error || 'Failed to create'); }
    }).catch(() => { btn.innerText = orig; btn.disabled = false; alert('Error'); });
    return false;
}
</script>
