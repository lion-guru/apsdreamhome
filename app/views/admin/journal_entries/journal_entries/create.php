<?php
// Journal Entry Create View
$accounts = $accounts ?? [];
$entryTypes = $entryTypes ?? ['manual', 'system', 'adjustment', 'closing'];
$sourceDocuments = $sourceDocuments ?? ['invoice', 'payment', 'expense', 'transfer', 'adjustment', 'opening'];
$today = $today ?? date('Y-m-d');
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Journal Entry</h1>
        <a href="<?= BASE_URL ?>/admin/journal-entries" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Journal Number</label>
                    <input type="text" name="journal_number" class="form-control" id="journalNumber" value="JE-<?= date('YmdHis') ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                    <input type="date" name="entry_date" class="form-control" required value="<?= $today ?>" max="<?= $today ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entry Type</label>
                    <select name="reference_type" class="form-select" id="entryType">
                        <?php foreach ($entryTypes as $t): ?>
                        <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Source Document</label>
                    <select name="source_document" class="form-select" id="sourceDoc">
                        <?php foreach ($sourceDocuments as $s): ?>
                        <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body">
            <form id="jeForm" method="POST" action="<?= BASE_URL ?>/admin/journal-entries/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g., INV-2026-001, PAY-12345">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference ID (optional)</label>
                        <input type="number" name="reference_id" class="form-control" placeholder="Related record ID">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Narration / Description <span class="text-danger">*</span></label>
                    <textarea name="narration" class="form-control" rows="2" required placeholder="Describe the purpose of this journal entry..."></textarea>
                </div>

                <h5 class="mb-3">Journal Lines (Minimum 2 lines - one Debit, one Credit)</h5>

                <div class="table-responsive">
                    <table class="table table-bordered" id="linesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 30%">Account <span class="text-danger">*</span></th>
                                <th style="width: 15%">Debit <span class="text-danger">*</span></th>
                                <th style="width: 15%">Credit <span class="text-danger">*</span></th>
                                <th style="width: 30%">Description</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Line 1 -->
                            <tr data-line="1">
                                <td class="align-middle text-center">1</td>
                                <td>
                                    <select name="lines[1][account_id]" class="form-select form-select-sm line-account" required>
                                        <option value="">Select Account</option>
                                        <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['id'] ?>" data-type="<?= $acc['account_type'] ?>" data-code="<?= htmlspecialchars($acc['account_code']) ?>" data-name="<?= htmlspecialchars($acc['account_name']) ?>">
                                            <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="lines[1][debit]" class="form-control form-control-sm line-debit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
                                </td>
                                <td>
                                    <input type="number" name="lines[1][credit]" class="form-control form-control-sm line-credit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
                                </td>
                                <td>
                                    <input type="text" name="lines[1][description]" class="form-control form-control-sm" placeholder="Line description">
                                </td>
                                <td class="align-middle text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-line" disabled><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Line 2 -->
                            <tr data-line="2">
                                <td class="align-middle text-center">2</td>
                                <td>
                                    <select name="lines[2][account_id]" class="form-select form-select-sm line-account" required>
                                        <option value="">Select Account</option>
                                        <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['id'] ?>" data-type="<?= $acc['account_type'] ?>" data-code="<?= htmlspecialchars($acc['account_code']) ?>" data-name="<?= htmlspecialchars($acc['account_name']) ?>">
                                            <?= htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="lines[2][debit]" class="form-control form-control-sm line-debit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
                                </td>
                                <td>
                                    <input type="number" name="lines[2][credit]" class="form-control form-control-sm line-credit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
                                </td>
                                <td>
                                    <input type="text" name="lines[2][description]" class="form-control form-control-sm" placeholder="Line description">
                                </td>
                                <td class="align-middle text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-0 py-2">
                            <strong>Total Debit:</strong>
                            <span id="totalDebit" class="fw-bold text-success fs-5">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-0 py-2">
                            <strong>Total Credit:</strong>
                            <span id="totalCredit" class="fw-bold text-danger fs-5">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-<?= 'info' ?> d-flex justify-content-between align-items-center mb-0 py-2" id="balanceAlert">
                            <strong>Balance:</strong>
                            <span id="balance" class="fw-bold fs-5">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-light d-flex justify-content-between align-items-center mb-0 py-2">
                            <strong>Lines:</strong>
                            <span id="lineCount" class="fw-bold">2</span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="<?= BASE_URL ?>/admin/journal-entries" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled><i class="fas fa-save me-2"></i>Create Journal Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let lineCounter = 2;

function validateLine(input) {
    const row = input.closest('tr');
    const debit = parseFloat(row.querySelector('.line-debit').value) || 0;
    const credit = parseFloat(row.querySelector('.line-credit').value) || 0;
    
    if (debit > 0 && credit > 0) {
        input.value = input.classList.contains('line-debit') ? debit : credit;
        if (input.classList.contains('line-debit')) {
            row.querySelector('.line-credit').value = '';
        } else {
            row.querySelector('.line-debit').value = '';
        }
    }
    updateTotals();
}

function updateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    let validLines = 0;
    
    document.querySelectorAll('#linesTable tbody tr').forEach(row => {
        const account = row.querySelector('.line-account').value;
        const debit = parseFloat(row.querySelector('.line-debit').value) || 0;
        const credit = parseFloat(row.querySelector('.line-credit').value) || 0;
        
        if (account && (debit > 0 || credit > 0)) {
            validLines++;
            totalDebit += debit;
            totalCredit += credit;
        }
    });
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    document.getElementById('balance').textContent = (totalDebit - totalCredit).toFixed(2);
    document.getElementById('lineCount').textContent = validLines;
    
    const balanceAlert = document.getElementById('balanceAlert');
    const submitBtn = document.getElementById('submitBtn');
    
    if (Math.abs(totalDebit - totalCredit) < 0.01 && validLines >= 2) {
        balanceAlert.className = 'alert alert-success d-flex justify-content-between align-items-center mb-0 py-2';
        balanceAlert.innerHTML = '<strong>Balance:</strong><span id="balance" class="fw-bold fs-5 text-success">Balanced ✓</span>';
        submitBtn.disabled = false;
    } else {
        balanceAlert.className = 'alert alert-danger d-flex justify-content-between align-items-center mb-0 py-2';
        balanceAlert.innerHTML = '<strong>Balance:</strong><span id="balance" class="fw-bold fs-5 text-danger">Unbalanced: ' + (totalDebit - totalCredit).toFixed(2) + '</span>';
        submitBtn.disabled = true;
    }
}

document.getElementById('linesTable').addEventListener('click', function(e) {
    if (e.target.closest('.remove-line') && !e.target.closest('.remove-line').disabled) {
        const row = e.target.closest('tr');
        const tbody = document.getElementById('linesTable').querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        if (rows.length <= 2) {
            alert('Minimum 2 lines required');
            return;
        }
        
        row.remove();
        renumberLines();
    }
});

function renumberLines() {
    let idx = 1;
    document.querySelectorAll('#linesTable tbody tr').forEach(row => {
        row.dataset.line = idx;
        row.querySelector('td:first-child').textContent = idx;
        
        // Update name attributes
        row.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/lines\[\d+\]/, 'lines[' + idx + ']');
        });
        
        // Update remove button state
        const removeBtn = row.querySelector('.remove-line');
        if (idx === rows.length) {
            removeBtn.disabled = false;
        } else {
            removeBtn.disabled = idx === 1;
        }
        idx++;
    });
    lineCounter = idx - 1;
    updateTotals();
}

document.getElementById('addLineBtn')?.addEventListener('click', function() {
    lineCounter++;
    const tbody = document.getElementById('linesTable').querySelector('tbody');
    const newRow = document.createElement('tr');
    newRow.dataset.line = lineCounter;
    newRow.innerHTML = `
        <td class="align-middle text-center">${lineCounter}</td>
        <td>
            <select name="lines[${lineCounter}][account_id]" class="form-select form-select-sm line-account" required>
                <option value="">Select Account</option>
                ${Array.from(document.querySelector('.line-account').options).slice(1).map(opt => 
                    `<option value="${opt.value}" data-type="${opt.dataset.type}" data-code="${opt.dataset.code}" data-name="${opt.dataset.name}">${opt.text}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="number" name="lines[${lineCounter}][debit]" class="form-control form-control-sm line-debit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
        </td>
        <td>
            <input type="number" name="lines[${lineCounter}][credit]" class="form-control form-control-sm line-credit" step="0.01" min="0" placeholder="0.00" oninput="validateLine(this)">
        </td>
        <td>
            <input type="text" name="lines[${lineCounter}][description]" class="form-control form-control-sm" placeholder="Line description">
        </td>
        <td class="align-middle text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="fas fa-trash"></i></button>
        </td>
    `;
    tbody.appendChild(newRow);
    
    // Update previous last row's remove button
    const rows = tbody.querySelectorAll('tr');
    rows[rows.length - 2].querySelector('.remove-line').disabled = false;
    
    updateTotals();
});

// Add "Add Line" button after the table
document.addEventListener('DOMContentLoaded', function() {
    const tableContainer = document.querySelector('#linesTable').parentElement;
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.id = 'addLineBtn';
    addBtn.className = 'btn btn-sm btn-outline-primary mt-2';
    addBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Add Line';
    tableContainer.appendChild(addBtn);
    
    updateTotals();
});
</script>