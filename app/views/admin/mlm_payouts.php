<?php
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">MLM Payout Batches</h1>
            <p class="text-muted">Automate approvals and disbursements for MLM commissions.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBatchModal">
            <i class="fas fa-plus-circle me-2"></i>Create Payout Batch
        </button>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="batchFilters" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="filterStatus">Status</label>
                    <select id="filterStatus" name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending_approval">Pending Approval</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filterFrom">Date From</label>
                    <input type="date" class="form-control" id="filterFrom" name="date_from">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filterTo">Date To</label>
                    <input type="date" class="form-control" id="filterTo" name="date_to">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="fas fa-filter me-1"></i>Apply
                    </button>
                    <button class="btn btn-outline-secondary" type="button" id="resetFilters">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Payout Batches</h5>
            <div>
                <select id="batchLimit" class="form-select form-select-sm" style="width:auto; display:inline-block;">
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="batchesTable">
                    <thead class="table-light">
                        <tr>
                            <th>Batch Ref</th>
                            <th>Status</th>
                            <th>Approvals</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Records</th>
                            <th>Created</th>
                            <th>Approved</th>
                            <th>Processed</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Batch Modal -->
<div class="modal fade" id="createBatchModal" tabindex="-1" aria-labelledby="createBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createBatchForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBatchModalLabel">Create Payout Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="batchReference">Batch Reference</label>
                            <input type="text" class="form-control" id="batchReference" name="batch_reference" placeholder="Optional custom reference">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batchDateFrom">Date From</label>
                            <input type="date" class="form-control" id="batchDateFrom" name="date_from">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="batchDateTo">Date To</label>
                            <input type="date" class="form-control" id="batchDateTo" name="date_to">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="batchMinAmount">Minimum Total Amount (₹)</label>
                            <input type="number" class="form-control" id="batchMinAmount" name="min_amount" min="0" step="0.01" placeholder="0 for no minimum">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="batchMaxItems">Max Commissions per Batch</label>
                            <input type="number" class="form-control" id="batchMaxItems" name="max_items" min="1" placeholder="Leave blank for unlimited">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="batchRequiredApprovals">Required Approvals</label>
                            <input type="number" class="form-control" id="batchRequiredApprovals" name="required_approvals" min="1" value="1">
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Only commissions with status <strong>approved</strong> are included. Batches start in <strong>pending approval</strong> and require the configured number of approvers before moving to processing.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Actions Modal -->
<div class="modal fade" id="batchDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Batch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="batchSummary" class="mb-4"></div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="batchItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Commission ID</th>
                                <th>Beneficiary</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-info" id="exportBatchBtn"><i class="fas fa-file-export me-1"></i>Export CSV</button>
                <button type="button" class="btn btn-outline-danger" id="cancelBatchBtn"><i class="fas fa-ban me-1"></i>Cancel Batch</button>
                <button type="button" class="btn btn-outline-primary" id="approveBatchBtn"><i class="fas fa-check-circle me-1"></i>Approve</button>
                <button type="button" class="btn btn-success" id="disburseBatchBtn"><i class="fas fa-hand-holding-usd me-1"></i>Record Disbursement</button>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approvalForm">
                <div class="modal-header">
                    <h5 class="modal-title">Record Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="approvalModalSummary" class="alert alert-light border mb-3"></div>
                    <div class="mb-3">
                        <label class="form-label">Decision</label>
                        <div class="btn-group w-100" role="group">
                            <input class="btn-check" type="radio" name="decision" id="decisionApprove" value="approved" checked>
                            <label class="btn btn-outline-success" for="decisionApprove"><i class="fas fa-check me-1"></i>Approve</label>
                            <input class="btn-check" type="radio" name="decision" id="decisionReject" value="rejected">
                            <label class="btn btn-outline-danger" for="decisionReject"><i class="fas fa-times me-1"></i>Reject</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="approvalQuickNote">Quick note</label>
                        <select id="approvalQuickNote" class="form-select">
                            <option value="">Select a quick note...</option>
                            <option value="Approved after verifying ledger entries.">Approved after verifying ledger entries.</option>
                            <option value="Approved – pending finance reconciliation.">Approved – pending finance reconciliation.</option>
                            <option value="Rejected – discrepancies found, please review." >Rejected – discrepancies found, please review.</option>
                            <option value="Rejected – awaiting supporting documents from finance.">Rejected – awaiting supporting documents from finance.</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="approvalNotes">Notes</label>
                        <textarea id="approvalNotes" class="form-control" name="notes" rows="3" placeholder="Optional notes for audit trail"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="approvalSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const filtersForm = document.getElementById('batchFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const batchesTableBody = document.querySelector('#batchesTable tbody');
    const batchLimit = document.getElementById('batchLimit');
    const createForm = document.getElementById('createBatchForm');
    const batchModal = new bootstrap.Modal(document.getElementById('batchDetailModal'));
    const approveBtn = document.getElementById('approveBatchBtn');
    const approvalModalElement = document.getElementById('approvalModal');
    const approvalModal = approvalModalElement ? new bootstrap.Modal(approvalModalElement) : null;
    const approvalForm = document.getElementById('approvalForm');
    const approvalSummaryEl = document.getElementById('approvalModalSummary');
    const approvalQuickNote = document.getElementById('approvalQuickNote');
    const approvalNotesField = document.getElementById('approvalNotes');
    const approvalSubmitBtn = document.getElementById('approvalSubmitBtn');
    const exportBtn = document.getElementById('exportBatchBtn');

    let currentBatchId = null;
    let currentBatch = null;

    function formatCurrency(value) {
        return '₹' + Number(value || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function statusBadge(status) {
        const map = { pending_approval: 'warning', processing: 'primary', completed: 'success', cancelled: 'danger' };
        return `<span class="badge bg-${map[status] || 'secondary'}">${status}</span>`;
    }

    function approvalsBadge(batch) {
        const required = Number(batch.required_approvals ?? 1);
        const approved = Number(batch.approval_count ?? 0);
        const color = approved >= required ? 'success' : 'secondary';
        return `<span class="badge bg-${color}">${approved}/${required}</span>`;
    }

    function filtersQuery(extra = {}) {
        const params = new URLSearchParams(new FormData(filtersForm));
        Object.entries(extra).forEach(([key, value]) => params.set(key, value));
        return params.toString();
    }

    function loadBatches() {
        fetch('<?php echo BASE_URL; ?>admin/payouts/list?' + filtersQuery({ limit: batchLimit.value }))
            .then(r => r.json())
            .then(data => {
                batchesTableBody.innerHTML = '';
                if (!data.success || !data.records.length) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = '<td colspan="8" class="text-center text-muted py-4">No payout batches found.</td>';
                    batchesTableBody.appendChild(tr);
                    return;
                }

                data.records.forEach(batch => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${batch.batch_reference}</td>
                        <td>${statusBadge(batch.status)}</td>
                        <td>${approvalsBadge(batch)}</td>
                        <td class="text-end">${formatCurrency(batch.total_amount)}</td>
                        <td class="text-end">${batch.total_records}</td>
                        <td>${formatDateTime(batch.created_at)}</td>
                        <td>${formatDateTime(batch.approved_at)}</td>
                        <td>${formatDateTime(batch.processed_at)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-batch="${batch.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>`;
                    batchesTableBody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load batches');
            });
    }

    function formatDateTime(value) {
        if (!value) return '—';
        return new Date(value).toLocaleString();
    }

    filtersForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadBatches();
    });

    resetFiltersBtn.addEventListener('click', () => {
        filtersForm.reset();
        loadBatches();
    });

    batchLimit.addEventListener('change', loadBatches);

    createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(createForm);
        fetch('<?php echo BASE_URL; ?>admin/payouts/create', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                createForm.reset();
                bootstrap.Modal.getInstance(document.getElementById('createBatchModal')).hide();
                loadBatches();
            } else {
                alert(data.message || 'Failed to create batch');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to create batch');
        });
    });

    batchesTableBody.addEventListener('click', function(e) {
        const button = e.target.closest('button[data-batch]');
        if (!button) return;
        currentBatchId = button.getAttribute('data-batch');
        loadBatchDetails(currentBatchId);
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            if (!currentBatchId) return;
            const url = new URL('<?php echo BASE_URL; ?>admin/payouts/export');
            url.searchParams.set('batch_id', currentBatchId);
            url.searchParams.set('format', 'csv');
            window.open(url.toString(), '_blank');
        });
    }

    document.getElementById('approveBatchBtn').addEventListener('click', handleApprovalDecision);
    document.getElementById('disburseBatchBtn').addEventListener('click', () => {
        const reference = prompt('Enter disbursement reference (optional):');
        const notes = prompt('Enter notes (optional):');
        batchAction('disburse', { reference, notes });
    });
    document.getElementById('cancelBatchBtn').addEventListener('click', () => {
        if (confirm('Are you sure you want to cancel this batch?')) {
            const reason = prompt('Reason for cancellation (optional):');
            batchAction('cancel', { reason });
        }
    });

    function loadBatchDetails(batchId) {
        fetch('<?php echo BASE_URL; ?>admin/payouts/items?batch_id=' + batchId)
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Failed to load batch items');

                currentBatch = data.batch;
                renderBatchSummary(currentBatch);
                updateApprovalButtonState();

                const tableBody = document.querySelector('#batchItemsTable tbody');
                tableBody.innerHTML = '';
                if (!data.records.length) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items found.</td></tr>';
                } else {
                    data.records.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${item.commission_id}</td>
                            <td>
                                <div class="fw-semibold">${escapeHtml(item.beneficiary_name || 'N/A')}</div>
                                <div class="text-muted small">${escapeHtml(item.beneficiary_email || '')}</div>
                            </td>
                            <td class="text-end">${formatCurrency(item.amount)}</td>
                            <td>${statusBadge(item.status)}</td>
                            <td>${formatDateTime(item.created_at)}</td>`;
                        tableBody.appendChild(tr);
                    });
                }
                batchModal.show();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to load batch details');
                currentBatch = null;
                updateApprovalButtonState();
            });
    }

    function renderBatchSummary(batch) {
        const summaryEl = document.getElementById('batchSummary');
        if (!batch) {
            summaryEl.innerHTML = '<div class="alert alert-warning">Batch summary unavailable.</div>';
            return;
        }

        const required = Number(batch.required_approvals ?? 1);
        const approved = Number(batch.approval_count ?? 0);
        const statusHtml = `${statusBadge(batch.status)} <span class="ms-2">${approvalsBadge(batch)}</span>`;

        let approvalsHtml = '<p class="text-muted mb-0">No approvals recorded yet.</p>';
        if (Array.isArray(batch.approvals) && batch.approvals.length) {
            approvalsHtml = '<ul class="list-group list-group-flush">' + batch.approvals.map(record => {
                const state = statusBadge(record.status);
                const name = escapeHtml(record.approver_name || ('User #' + record.approver_user_id));
                const email = escapeHtml(record.approver_email || '');
                const note = record.notes ? `<div class="small text-muted">${escapeHtml(record.notes)}</div>` : '';
                const date = record.updated_at || record.created_at;
                return `<li class="list-group-item d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">${name}</div>
                        ${email ? `<div class="small text-muted">${email}</div>` : ''}
                        ${note}
                    </div>
                    <div class="text-end">
                        ${state}
                        <div class="small text-muted">${date ? formatDateTime(date) : ''}</div>
                    </div>
                </li>`;
            }).join('') + '</ul>';
        }

        summaryEl.innerHTML = `
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">Batch ${escapeHtml(batch.batch_reference || '#' + batch.id)}</h5>
                            <div>${statusHtml}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">₹${Number(batch.total_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            <div class="text-muted">${batch.total_records || 0} commission(s)</div>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Remaining Approvals</label>
                            <div class="fw-semibold">${Math.max(required - approved, 0)}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Approved At</label>
                            <div>${batch.approved_at ? formatDateTime(batch.approved_at) : '—'}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Processed At</label>
                            <div>${batch.processed_at ? formatDateTime(batch.processed_at) : '—'}</div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-2">Approval Log</h6>
                    ${approvalsHtml}
                </div>
            </div>`;
    }

    function handleApprovalDecision() {
        if (!currentBatchId || !currentBatch || !approvalModal) return;
        if (currentBatch.status !== 'pending_approval') {
            alert('Batch is not awaiting approvals.');
            return;
        }

        approvalForm.reset();
        document.getElementById('decisionApprove').checked = true;
        approvalNotesField.value = '';
        if (approvalQuickNote) {
            approvalQuickNote.value = '';
        }
        approvalSubmitBtn.disabled = false;

        const required = Number(currentBatch.required_approvals ?? 1);
        const approved = Number(currentBatch.approval_count ?? 0);
        const remaining = Math.max(required - approved, 0);
        const reference = escapeHtml(currentBatch.batch_reference || '#' + currentBatch.id);

        approvalSummaryEl.innerHTML = `
            <div class="fw-semibold">Batch ${reference}</div>
            <div>Approvals recorded: <strong>${approved}</strong> / ${required}</div>
            <div>Remaining approvals required: <strong>${remaining}</strong></div>
        `;

        approvalModal.show();
        setTimeout(() => approvalNotesField?.focus(), 200);
    }

    function updateApprovalButtonState() {
        if (!approveBtn) return;
        const isPending = !!(currentBatch && currentBatch.status === 'pending_approval');
        approveBtn.disabled = !isPending;
        approveBtn.classList.toggle('btn-outline-primary', isPending);
        approveBtn.classList.toggle('btn-outline-secondary', !isPending);
    }

    function batchAction(action, extra = {}, callbacks = {}) {
        if (!currentBatchId) return Promise.resolve();
        const formData = new FormData();
        formData.append('batch_id', currentBatchId);
        Object.entries(extra).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                formData.append(key, value);
            }
        });

        return fetch('<?php echo BASE_URL; ?>admin/payouts/' + action, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadBatches();
                loadBatchDetails(currentBatchId);
                if (typeof callbacks.onSuccess === 'function') {
                    callbacks.onSuccess(data);
                }
            } else {
                alert(data.message || 'Action failed');
                if (typeof callbacks.onError === 'function') {
                    callbacks.onError(data);
                }
            }
            return data;
        })
        .catch(err => {
            console.error(err);
            alert('Action failed');
            if (typeof callbacks.onError === 'function') {
                callbacks.onError(err);
            }
            throw err;
        })
        .finally(() => {
            if (typeof callbacks.onFinally === 'function') {
                callbacks.onFinally();
            }
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"]+/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[s]);
    }

    if (approvalForm) {
        approvalForm.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!currentBatchId) return;

            const formData = new FormData(approvalForm);
            const decision = formData.get('decision') || 'approved';
            const notes = (approvalNotesField.value || '').trim();

            approvalSubmitBtn.disabled = true;
            batchAction('approve', { decision, notes }, {
                onSuccess: () => {
                    approvalModal?.hide();
                },
                onFinally: () => {
                    approvalSubmitBtn.disabled = false;
                }
            });
        });
    }

    if (approvalQuickNote) {
        approvalQuickNote.addEventListener('change', () => {
            const note = approvalQuickNote.value;
            if (!note) return;
            const existing = approvalNotesField.value.trim();
            approvalNotesField.value = existing ? `${existing}\n${note}` : note;
            approvalNotesField.focus();
            approvalQuickNote.value = '';
        });
    }

    loadBatches();
    updateApprovalButtonState();
})();
</script>

<?php
require_once __DIR__ . '/../../includes/admin_footer.php';
