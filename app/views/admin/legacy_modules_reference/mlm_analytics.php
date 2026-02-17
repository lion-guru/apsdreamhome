<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-Dxr7n0ANKPO/tUMGAfJOyrUo9qeycGQ21MCH2RKDWEUtNdz/BPZt6r9Ga6IpiObOqYkbKx2+Y8Oob+ST3VkOSA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0">MLM Commission Analytics</h1>
            <p class="text-muted mb-0">Real-time visibility into commission performance, payout stages, and referrer impact.</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>admin/mlm-analytics/export?format=csv" class="btn btn-outline-primary" id="exportCsvBtn">
                <i class="fas fa-file-export me-2"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filtersForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending,approved,paid" selected>Pending + Approved + Paid</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="commission_type" class="form-label">Commission Type</label>
                    <select class="form-select" id="commission_type" name="commission_type">
                        <option value="" selected>All Types</option>
                        <option value="property_sale">Property Sale</option>
                        <option value="referral_bonus">Referral Bonus</option>
                        <option value="team_bonus">Team Bonus</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="text" class="form-control" id="date_from" name="date_from" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="text" class="form-control" id="date_to" name="date_to" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-sync-alt me-1"></i> Apply
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4" id="summaryCards">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Total Commission</div>
                    <div class="display-6 fw-semibold" id="totalCommission">₹0</div>
                    <div class="text-muted small">Across selected filters</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Pending</div>
                    <div class="display-6 fw-semibold text-warning" id="pendingCommission">₹0</div>
                    <div class="text-muted small">Awaiting approval/payment</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Approved</div>
                    <div class="display-6 fw-semibold text-primary" id="approvedCommission">₹0</div>
                    <div class="text-muted small">Ready for payout</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted text-uppercase small">Paid</div>
                    <div class="display-6 fw-semibold text-success" id="paidCommission">₹0</div>
                    <div class="text-muted small">Disbursed to users</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Commission Timeline</h5>
                    <select class="form-select form-select-sm w-auto" id="timelineGroup">
                        <option value="day" selected>Daily</option>
                        <option value="week">Weekly</option>
                        <option value="month">Monthly</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" height="160"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Level Breakdown</h5>
                </div>
                <div class="card-body">
                    <canvas id="levelChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Beneficiaries</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="beneficiariesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Pending</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Referrers</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="referrersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-end">Direct Referrals</th>
                                    <th class="text-end">Total Commission</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Detailed Ledger</h5>
            <div>
                <select class="form-select form-select-sm" id="ledgerLimit" style="width: auto; display: inline-block;">
                    <option value="25">25 rows</option>
                    <option value="50" selected>50 rows</option>
                    <option value="100">100 rows</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0" id="ledgerTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Beneficiary</th>
                            <th>Source</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Level</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-OMoNlsLwDyZaG0/1q/sEem2sr7WzMwP2KVd8UQ0BXpDE2NZkJqcMl3DB3diEFyPZ8s9tfwGBrnrZ0H/Tyuod3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" integrity="sha256-Qomr90oCbGyF/F7fs/3Gzdh0dX8GZFODdgNpTi27Czk=" crossorigin="anonymous"></script>
<script>
    (function() {
        const form = document.getElementById('filtersForm');
        const resetBtn = document.getElementById('resetFilters');
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        const timelineGroup = document.getElementById('timelineGroup');
        const ledgerLimit = document.getElementById('ledgerLimit');
        const exportBtn = document.getElementById('exportCsvBtn');

        const timelineCtx = document.getElementById('timelineChart').getContext('2d');
        const levelCtx = document.getElementById('levelChart').getContext('2d');
        let timelineChart, levelChart;

        flatpickr([dateFrom, dateTo], {
            dateFormat: 'Y-m-d'
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchAll();
        });

        resetBtn.addEventListener('click', function() {
            document.getElementById('status').value = 'pending,approved,paid';
            document.getElementById('commission_type').value = '';
            dateFrom.value = '<?php echo date('Y-m-01'); ?>';
            dateTo.value = '<?php echo date('Y-m-d'); ?>';
            fetchAll();
        });

        timelineGroup.addEventListener('change', fetchAll);
        ledgerLimit.addEventListener('change', loadLedger);

        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const query = new URLSearchParams(new FormData(form));
            window.location = '<?php echo BASE_URL; ?>admin/mlm-analytics/export?format=csv&' + query.toString();
        });

        function fetchAll() {
            loadSummary();
            loadTables();
            loadCharts();
            loadLedger();
        }

        function filtersQuery(extra = {}) {
            const params = new URLSearchParams(new FormData(form));
            params.set('group_by', timelineGroup.value);
            Object.entries(extra).forEach(([key, value]) => params.set(key, value));
            return params.toString();
        }

        function loadSummary() {
            fetch('<?php echo BASE_URL; ?>admin/mlm-analytics/data?' + filtersQuery())
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.error || 'Failed to load summary');

                    const totals = {
                        pending: 0,
                        approved: 0,
                        paid: 0
                    };
                    data.summary.forEach(row => {
                        totals[row.status] = parseFloat(row.total_amount);
                    });
                    const total = Object.values(totals).reduce((a, b) => a + b, 0);

                    document.getElementById('totalCommission').innerText = formatCurrency(total);
                    document.getElementById('pendingCommission').innerText = formatCurrency(totals.pending || 0);
                    document.getElementById('approvedCommission').innerText = formatCurrency(totals.approved || 0);
                    document.getElementById('paidCommission').innerText = formatCurrency(totals.paid || 0);
                })
                .catch(showError);
        }

        function loadCharts() {
            fetch('<?php echo BASE_URL; ?>admin/mlm-analytics/data?' + filtersQuery())
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.error || 'Failed to load charts');

                    const labels = data.timeline.map(item => item.bucket);
                    const amounts = data.timeline.map(item => item.total_amount);

                    if (timelineChart) timelineChart.destroy();
                    timelineChart = new Chart(timelineCtx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Commission Amount',
                                data: amounts,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                tension: 0.3,
                                fill: true,
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    const levelLabels = data.level_breakdown.map(item => 'Level ' + item.level);
                    const levelAmounts = data.level_breakdown.map(item => item.total_amount);

                    if (levelChart) levelChart.destroy();
                    levelChart = new Chart(levelCtx, {
                        type: 'doughnut',
                        data: {
                            labels: levelLabels,
                            datasets: [{
                                data: levelAmounts,
                                backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#fd7e14', '#20c997']
                            }]
                        }
                    });
                })
                .catch(showError);
        }

        function loadTables() {
            fetch('<?php echo BASE_URL; ?>admin/mlm-analytics/data?' + filtersQuery({
                    limit: 8
                }))
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.error || 'Failed to load tables');

                    populateTable('beneficiariesTable', data.top_beneficiaries, row => `
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.name)}</div>
                        <div class="text-muted small">${escapeHtml(row.email)}</div>
                    </td>
                    <td class="text-end">${formatCurrency(row.total_amount)}</td>
                    <td class="text-end">${formatCurrency(row.total_paid)}</td>
                    <td class="text-end">${formatCurrency(row.total_pending)}</td>
                `);

                    populateTable('referrersTable', data.top_referrers, row => `
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.name)}</div>
                        <div class="text-muted small">${escapeHtml(row.email)}</div>
                    </td>
                    <td class="text-end">${row.direct_referrals ?? 0}</td>
                    <td class="text-end">${formatCurrency(row.total_amount)}</td>
                `);
                })
                .catch(showError);
        }

        function loadLedger() {
            fetch('<?php echo BASE_URL; ?>admin/mlm-analytics/ledger?' + filtersQuery({
                    limit: ledgerLimit.value
                }))
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.error || 'Failed to load ledger');

                    populateTable('ledgerTable', data.records, row => `
                    <td>${row.id}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.beneficiary_name ?? 'N/A')}</div>
                        <div class="text-muted small">${escapeHtml(row.beneficiary_email ?? '')}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(row.source_name ?? '—')}</div>
                        <div class="text-muted small">${escapeHtml(row.source_email ?? '')}</div>
                    </td>
                    <td>${escapeHtml(row.commission_type)}</td>
                    <td class="text-end">${formatCurrency(row.amount)}</td>
                    <td>${row.level ?? '—'}</td>
                    <td><span class="badge bg-${statusColor(row.status)}">${escapeHtml(row.status)}</span></td>
                    <td>${formatDateTime(row.created_at)}</td>
                `);
                })
                .catch(showError);
        }

        function populateTable(tableId, data, rowTemplate) {
            const tbody = document.querySelector(`#${tableId} tbody`);
            if (!tbody) return;
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="6" class="text-center text-muted py-4">No data for selected filters</td>';
                tbody.appendChild(tr);
                return;
            }

            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = rowTemplate(item);
                tbody.appendChild(tr);
            });
        }

        function formatCurrency(value) {
            return '₹' + Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDateTime(value) {
            if (!value) return '—';
            return new Date(value).toLocaleString();
        }

        function statusColor(status) {
            switch (status) {
                case 'paid':
                    return 'success';
                case 'approved':
                    return 'primary';
                case 'pending':
                    return 'warning';
                case 'cancelled':
                    return 'secondary';
                default:
                    return 'light';
            }
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"]+/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            })[s]);
        }

        function showError(err) {
            console.error(err);
            alert('Failed to load analytics data. Check console for details.');
        }

        fetchAll();
    })();
</script>
