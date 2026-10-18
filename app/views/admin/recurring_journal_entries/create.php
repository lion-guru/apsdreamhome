<?php
$accounts = $accounts ?? [];
$today = $today ?? date('Y-m-d');
$frequencies = $frequencies ?? [];
$entryTypes = $entryTypes ?? [];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Recurring Journal Entry</h1>
        <a href="<?= BASE_URL ?>/admin/recurring-journal-entries" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form id="rjeForm" onsubmit="return submitRje(event);">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input type="text" name="template_name" class="form-control" required placeholder="e.g. Monthly Rent Accrual">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entry Type</label>
                    <select name="entry_type" class="form-select">
                        <?php foreach ($entryTypes as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Frequency</label>
                    <select name="frequency" class="form-select" required>
                        <?php foreach ($frequencies as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $today ?>" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3"><label class="form-label">End Date (optional)</label><input type="date" name="end_date" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Max Executions (optional)</label><input type="number" name="max_executions" class="form-control" min="1"></div>
                <div class="col-md-2 d-flex align-items-end"><label class="form-check-label mb-0"><input type="checkbox" name="is_active" value="1" checked class="form-check-input me-2"> Active</label></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Purpose of this recurring entry"></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Journal Lines <span class="text-danger">*</span></label>
                <div class="table-responsive">
                    <table class="table table-bordered" id="linesTable">
                        <thead>
                        <tr>
                            <th>Account</th><th>Debit</th><th>Credit</th><th>Line Description</th><th style="width:60px">Order</th><th style="width:60px">Action</th>
                        </tr>
                        </thead>
                        <tbody id="linesBody">
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addLine()"><i class="fas fa-plus me-2"></i>Add Line</button>
                <div class="form-text">Debits and credits must balance before saving.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Template</button>
                <a href="<?= BASE_URL ?>/admin/recurring-journal-entries" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div></div>
</div>

<script>
let lineCounter = 0;
let accounts = <?= json_encode(array_map(function($a){ return ['id'=>$a['id'], 'code'=>$a['account_code'], 'name'=>$a['account_name'], 'type'=>$a['account_type']]; }, $accounts)) ?>;

function addLine() {
    const tbody = document.getElementById('linesBody');
    const idx = lineCounter++;
    const row = document.createElement('tr');
    row.id = 'line-' + idx;
    const accOptions = accounts.map(a => '<option value="' + a.id + '">' + a.code + ' - ' + a.name + ' (' + a.type + ')</option>').join('');
    row.innerHTML = `
        <td><select name="lines[${idx}][account_id]" class="form-select form-select-sm" required>${accOptions}</select></td>
        <td><input type="number" name="lines[${idx}][debit_amount]" class="form-control form-control-sm" step="0.01" min="0" value="0" oninput="calcTotal()"></td>
        <td><input type="number" name="lines[${idx}][credit_amount]" class="form-control form-control-sm" step="0.01" min="0" value="0" oninput="calcTotal()"></td>
        <td><input type="text" name="lines[${idx}][description]" class="form-control form-control-sm"></td>
        <td><input type="number" name="lines[${idx}][line_order]" class="form-control form-control-sm" value="${idx+1}" min="1"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(${idx})"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
    calcTotal();
}
function removeLine(idx) {
    document.getElementById('line-' + idx).remove();
    calcTotal();
}
function calcTotal() {
    let dr = 0, cr = 0;
    document.querySelectorAll('#linesBody tr').forEach(tr => {
        dr += parseFloat(tr.querySelector('input[name*="[debit_amount]"]').value) || 0;
        cr += parseFloat(tr.querySelector('input[name*="[credit_amount]"]').value) || 0;
    });
    document.getElementById('linesBody').closest('table').previousElementSibling;
    // show balance in a small badge
    let el = document.getElementById('balanceBadge');
    if (!el) {
        el = document.createElement('span');
        el.id = 'balanceBadge';
        el.className = 'ms-2 badge';
        document.querySelector('.mb-4 .fw-bold').appendChild(el);
    }
    const balanced = Math.abs(dr - cr) < 0.01;
    el.textContent = 'Dr ₹' + dr.toFixed(2) + ' / Cr ₹' + cr.toFixed(2) + (balanced ? ' ✓' : ' ⚠');
    el.className = 'ms-2 badge ' + (balanced ? 'bg-success' : 'bg-warning');
}
function submitRje(e) {
    const dr = parseFloat(document.querySelector('input[name*="[debit_amount]"]:last-of-type') ? 0 : 0);
    // basic client check: at least one line
    if (document.querySelectorAll('#linesBody tr').length === 0) {
        alert('Add at least one journal line.');
        return false;
    }
    const btn = e.target.querySelector('button[type="submit"]');
    const orig = btn.innerText;
    btn.innerText = 'Creating...'; btn.disabled = true;
    const formData = new FormData(e.target);
    fetch('<?= BASE_URL ?>/admin/recurring-journal-entries/store', {
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
addLine();
</script>
