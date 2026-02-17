<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= $mlSupport->translate('EMI Foreclosure Reports') ?></h1>
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> <?= $mlSupport->translate('Print') ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBtn">
                    <i class="fas fa-sync-alt me-1"></i> <?= $mlSupport->translate('Refresh') ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= $mlSupport->translate('Total Foreclosures') ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalForeclosures">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= $mlSupport->translate('Total Amount') ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalAmount">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= $mlSupport->translate('Average Amount') ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgAmount">Loading...</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?= $mlSupport->translate('Pending Requests') ?></div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row">
        <!-- Area Chart -->
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><?= $mlSupport->translate('Foreclosure Trends (Last 12 Months)') ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="foreclosureChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><?= $mlSupport->translate('Detailed Foreclosure Report') ?></h6>
            
            <!-- Filters -->
            <form id="filterForm" class="d-flex align-items-center gap-2">
                <input type="date" class="form-control form-control-sm" name="start_date" placeholder="Start Date">
                <input type="date" class="form-control form-control-sm" name="end_date" placeholder="End Date">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter"></i>
                </button>
                <button type="button" id="resetFilters" class="btn btn-secondary btn-sm">
                    <i class="fas fa-undo"></i>
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?= $mlSupport->translate('Date') ?></th>
                            <th><?= $mlSupport->translate('Customer') ?></th>
                            <th><?= $mlSupport->translate('Property') ?></th>
                            <th><?= $mlSupport->translate('Amount') ?></th>
                            <th><?= $mlSupport->translate('Processed By') ?></th>
                            <th><?= $mlSupport->translate('Status') ?></th>
                            <th><?= $mlSupport->translate('Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize DataTables
        const table = $('#dataTable').DataTable({
            processing: true,
            serverSide: false, // Client-side for now as we fetch all report data
            ajax: {
                url: '/admin/emi/foreclosure-data',
                dataSrc: ''
            },
            columns: [
                { data: 'attempted_at', render: function(data) { return new Date(data).toLocaleDateString(); } },
                { data: 'customer_name' },
                { data: 'property_title' },
                { data: 'foreclosure_amount', render: function(data) { return '₹' + parseFloat(data).toLocaleString(); } },
                { data: 'admin_name' },
                { 
                    data: 'foreclosure_status',
                    render: function(data) {
                        return `<span class="badge bg-${data === 'success' ? 'success' : 'danger'}">${data.toUpperCase()}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<a href="/admin/emi/${row.emi_plan_id}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>`;
                    }
                }
            ],
            order: [[0, 'desc']]
        });

        // Fetch Statistics
        async function fetchStats() {
            try {
                const response = await fetch('/admin/emi/foreclosure-stats');
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('totalForeclosures').innerText = data.total_attempts;
                    document.getElementById('totalAmount').innerText = '₹' + parseFloat(data.total_foreclosure_amount).toLocaleString();
                    document.getElementById('avgAmount').innerText = '₹' + parseFloat(data.average_foreclosure_amount).toLocaleString();
                }
            } catch (error) {
                console.error('Error fetching stats:', error);
            }
        }

        // Fetch Chart Data
        async function fetchChartData() {
            try {
                const response = await fetch('/admin/emi/foreclosure-trend');
                const data = await response.json();
                
                const labels = data.map(item => item.month);
                const values = data.map(item => item.total_foreclosure_amount);
                const counts = data.map(item => item.total_attempts);

                const ctx = document.getElementById('foreclosureChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Foreclosure Amount (₹)',
                            data: values,
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            yAxisID: 'y',
                        }, {
                            label: 'Number of Foreclosures',
                            data: counts,
                            borderColor: '#1cc88a',
                            borderDash: [5, 5],
                            yAxisID: 'y1',
                            type: 'line'
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Amount (₹)' }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Count' }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error fetching chart data:', error);
            }
        }

        // Filter Form Handling
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            
            table.ajax.url('/admin/emi/foreclosure-data?' + params).load();
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterForm').reset();
            table.ajax.url('/admin/emi/foreclosure-data').load();
        });

        document.getElementById('refreshBtn').addEventListener('click', function() {
            fetchStats();
            table.ajax.reload();
        });

        // Initial Load
        fetchStats();
        fetchChartData();
    });
</script>