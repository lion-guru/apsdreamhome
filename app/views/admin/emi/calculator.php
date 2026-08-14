<?php $page_title = $page_title ?? 'EMI Calculator'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i>EMI Calculator</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/penalties" class="btn btn-outline-primary btn-sm"><i class="fas fa-exclamation-triangle me-1"></i>Penalties</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-sliders-h me-2"></i>Loan Details</div>
                <div class="aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Loan Amount (â‚¹)</label>
                        <input type="number" id="loanAmount" class="form-control" value="2500000" min="100000" step="50000">
                        <div class="form-text">Min â‚¹1,00,000</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Interest Rate (% per annum)</label>
                        <input type="number" id="interestRate" class="form-control" value="8.5" min="1" max="30" step="0.1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Loan Tenure (Months)</label>
                        <input type="number" id="tenureMonths" class="form-control" value="240" min="12" max="360" step="12">
                        <div class="form-text">12 to 360 months (1 to 30 years)</div>
                    </div>
                    <button onclick="calculateEMI()" class="btn btn-primary w-100"><i class="fas fa-calculator me-1"></i>Calculate EMI</button>
                </div>
            </div>

            <div class="aps-cp-card mt-3" id="emiResult" class="style-24280">
                <div class="aps-cp-card-header"><i class="fas fa-chart-pie me-2"></i>EMI Breakdown</div>
                <div class="aps-cp-card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="aps-cp-stat-label">Monthly EMI</div>
                            <div class="aps-cp-stat-value text-primary fs-5" id="monthlyEmi">â‚¹0</div>
                        </div>
                        <div class="col-4">
                            <div class="aps-cp-stat-label">Total Interest</div>
                            <div class="aps-cp-stat-value text-danger fs-5" id="totalInterest">â‚¹0</div>
                        </div>
                        <div class="col-4">
                            <div class="aps-cp-stat-label">Total Payment</div>
                            <div class="aps-cp-stat-value text-success fs-5" id="totalPayment">â‚¹0</div>
                        </div>
                    </div>
                    <canvas id="emiPieChart" height="180" class="mt-3"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-table me-2"></i>Amortization Schedule</div>
                <div class="aps-cp-card-body">
                    <div id="amortizationPlaceholder" class="text-center text-muted py-5">
                        <i class="fas fa-table fa-3x mb-3 opacity-25"></i>
                        <p>Enter loan details and click Calculate to see the amortization schedule</p>
                    </div>
                    <div id="amortizationTable" class="style-24280">
                        <div class="table-responsive" class="style-62230">
                            <table class="table table-sm table-hover mb-0" id="scheduleTable">
                                <thead class="sticky-top bg-dark text-white"><tr>
                                    <th>#</th><th>EMI</th><th>Principal</th><th>Interest</th><th>Balance</th>
                                </tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
let emiChart = null;
function formatINR(n) {
    if (n >= 10000000) return 'â‚¹' + (n/10000000).toFixed(2) + ' Cr';
    if (n >= 100000) return 'â‚¹' + (n/100000).toFixed(2) + ' L';
    return 'â‚¹' + n.toLocaleString('en-IN');
}

function calculateEMI() {
    var P = parseFloat(document.getElementById('loanAmount').value) || 0;
    var annualRate = parseFloat(document.getElementById('interestRate').value) || 0;
    var N = parseInt(document.getElementById('tenureMonths').value) || 0;
    if (P <= 0 || annualRate <= 0 || N <= 0) { alert('Please enter valid values'); return; }
    var r = annualRate / 12 / 100;
    var emi = P * r * Math.pow(1 + r, N) / (Math.pow(1 + r, N) - 1);
    var totalPayment = emi * N;
    var totalInterest = totalPayment - P;

    document.getElementById('monthlyEmi').textContent = formatINR(emi);
    document.getElementById('totalInterest').textContent = formatINR(totalInterest);
    document.getElementById('totalPayment').textContent = formatINR(totalPayment);
    document.getElementById('emiResult').style.display = '';

    if (emiChart) emiChart.destroy();
    emiChart = new Chart(document.getElementById('emiPieChart'), {
        type: 'doughnut',
        data: { labels: ['Principal', 'Interest'], datasets: [{ data: [P, totalInterest], backgroundColor: ['#0d9488', '#ef4444'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    var tbody = document.querySelector('#scheduleTable tbody');
    tbody.innerHTML = '';
    var balance = P;
    for (var i = 1; i <= N; i++) {
        var interestPart = balance * r;
        var principalPart = emi - interestPart;
        balance -= principalPart;
        if (balance < 0) balance = 0;
        var row = '<tr><td>' + i + '</td><td>' + formatINR(emi) + '</td><td>' + formatINR(principalPart) + '</td><td>' + formatINR(interestPart) + '</td><td>' + formatINR(balance) + '</td></tr>';
        tbody.innerHTML += row;
    }
    document.getElementById('amortizationPlaceholder').style.display = 'none';
    document.getElementById('amortizationTable').style.display = '';
}

document.addEventListener('DOMContentLoaded', function() { calculateEMI(); });
</script>
