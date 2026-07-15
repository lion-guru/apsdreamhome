<?php
$base = $base ?? BASE_URL;
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Investment Calculator</h1>

  <div class="row g-3">
    <div class="col-md-5">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Enter Details</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Property Price (₹)</label>
            <input type="number" id="propertyPrice" class="form-control" value="5000000" min="100000" step="100000">
          </div>
          <div class="mb-3">
            <label class="form-label">Down Payment (%)</label>
            <input type="range" id="downPayment" class="form-range" min="10" max="50" value="20" oninput="document.getElementById('downPaymentVal').textContent=this.value+'%'">
            <div class="d-flex justify-content-between">
              <small class="text-muted">10%</small>
              <span id="downPaymentVal" class="fw-bold">20%</span>
              <small class="text-muted">50%</small>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Loan Term (years)</label>
            <input type="range" id="loanTerm" class="form-range" min="5" max="30" value="20" oninput="document.getElementById('loanTermVal').textContent=this.value+' yr'">
            <div class="d-flex justify-content-between">
              <small class="text-muted">5 yr</small>
              <span id="loanTermVal" class="fw-bold">20 yr</span>
              <small class="text-muted">30 yr</small>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Interest Rate (% p.a.)</label>
            <input type="range" id="interestRate" class="form-range" min="5" max="15" value="8.5" step="0.1" oninput="document.getElementById('interestRateVal').textContent=this.value+'%'">
            <div class="d-flex justify-content-between">
              <small class="text-muted">5%</small>
              <span id="interestRateVal" class="fw-bold">8.5%</span>
              <small class="text-muted">15%</small>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Expected Monthly Rent (₹)</label>
            <input type="number" id="monthlyRent" class="form-control" value="15000" min="0" step="1000">
          </div>
          <div class="mb-3">
            <label class="form-label">Expected Appreciation (% p.a.)</label>
            <input type="range" id="appreciationRate" class="form-range" min="0" max="20" value="5" step="0.5" oninput="document.getElementById('appreciationVal').textContent=this.value+'%'">
            <div class="d-flex justify-content-between">
              <small class="text-muted">0%</small>
              <span id="appreciationVal" class="fw-bold">5%</span>
              <small class="text-muted">20%</small>
            </div>
          </div>
          <button class="btn btn-success w-100 btn-lg" onclick="calculateInvestment()">
            <i class="fas fa-calculator me-2"></i>Calculate
          </button>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div id="results" style="display:none">
        <div class="row g-3 mb-3" id="resultCards"></div>
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>ROI Analysis</h5>
          </div>
          <div class="card-body" id="roiAnalysis">
            <p class="text-muted mb-0">Loading...</p>
          </div>
        </div>
      </div>

      <div id="loading" style="display:none" class="text-center py-5">
        <div class="spinner-border text-success mb-3" role="status"></div>
        <p class="text-muted">Calculating...</p>
      </div>

      <div id="error" style="display:none" class="alert alert-danger"></div>

      <div id="empty" class="text-center py-5 text-muted">
        <i class="fas fa-calculator fa-4x mb-3 d-block"></i>
        <p>Enter investment details and click Calculate</p>
      </div>
    </div>
  </div>
</div>

<script>
function calculateInvestment() {
  const params = {
    property_price: parseFloat(document.getElementById('propertyPrice').value) || 0,
    down_payment: parseFloat(document.getElementById('downPayment').value) || 20,
    loan_term: parseInt(document.getElementById('loanTerm').value) || 20,
    interest_rate: parseFloat(document.getElementById('interestRate').value) || 8.5,
    monthly_rent: parseFloat(document.getElementById('monthlyRent').value) || 0,
    appreciation_rate: parseFloat(document.getElementById('appreciationRate').value) || 5
  };

  if (params.property_price <= 0) { alert('Enter a valid property price'); return; }

  document.getElementById('loading').style.display = 'block';
  document.getElementById('results').style.display = 'none';
  document.getElementById('error').style.display = 'none';
  document.getElementById('empty').style.display = 'none';

  fetch('<?= $base ?>/admin/custom-features/investment-calculate', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(params)
  })
    .then(r => r.json())
    .then(data => {
      document.getElementById('loading').style.display = 'none';
      if (!data.success) {
        document.getElementById('error').style.display = 'block';
        document.getElementById('error').textContent = data.message || 'Calculation failed';
        return;
      }
      const d = data.data;
      document.getElementById('results').style.display = 'block';

      document.getElementById('resultCards').innerHTML = `
        <div class="col-md-6">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-5 text-muted">Monthly Payment</div>
            <div class="fs-2 text-primary fw-bold">₹${Number(d.monthly_payment || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
          </div></div>
        </div>
        <div class="col-md-6">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-5 text-muted">Down Payment</div>
            <div class="fs-2 text-info fw-bold">₹${Number(d.down_payment_amount || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
          </div></div>
        </div>
        <div class="col-md-6">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-5 text-muted">Loan Amount</div>
            <div class="fs-2 text-warning fw-bold">₹${Number(d.loan_amount || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
          </div></div>
        </div>
        <div class="col-md-6">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-5 text-muted">Total Interest</div>
            <div class="fs-2 text-danger fw-bold">₹${Number(d.total_interest || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
          </div></div>
        </div>
      `;

      if (d.roi_analysis) {
        const roi = d.roi_analysis;
        document.getElementById('roiAnalysis').innerHTML = `
          <div class="row g-3">
            <div class="col-md-4 text-center border-end">
              <div class="fs-1 text-success fw-bold">${roi.roi_percentage || roi.roi || '0'}%</div>
              <small class="text-muted">Expected ROI</small>
            </div>
            <div class="col-md-4 text-center border-end">
              <div class="fs-1 text-info fw-bold">₹${Number(roi.annual_income || roi.annual_return || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
              <small class="text-muted">Annual Return</small>
            </div>
            <div class="col-md-4 text-center">
              <div class="fs-1 text-warning fw-bold">₹${Number(roi.total_return || 0).toLocaleString('en-IN', {maximumFractionDigits:0})}</div>
              <small class="text-muted">Total Return (${params.loan_term} yr)</small>
            </div>
          </div>
        `;
      } else {
        document.getElementById('roiAnalysis').innerHTML = '<p class="text-muted mb-0">ROI data not available</p>';
      }
    })
    .catch(err => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('error').style.display = 'block';
      document.getElementById('error').textContent = 'Error: ' + err.message;
    });
}
</script>
