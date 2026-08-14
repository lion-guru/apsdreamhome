<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">EMI Calculator</h1>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Calculate EMI</h5>
        </div>
        <div class="card-body">
          <form id="emiForm">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="mb-3">
              <label class="form-label">Loan Amount (â‚¹)</label>
              <input type="number" name="principal" class="form-control" value="5000000" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Interest Rate (% per annum)</label>
              <input type="number" name="rate" class="form-control" value="8.5" step="0.1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Tenure (Years)</label>
              <input type="number" name="tenure" class="form-control" value="15" min="1" max="30" required>
            </div>
            <button type="button" class="btn btn-primary w-100" onclick="calculateEMI()">
              <i class="fas fa-calculator"></i> Calculate EMI
            </button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-header">
          <h5 class="mb-0">Results</h5>
        </div>
        <div class="card-body">
          <div id="results" class="style-2248">
            <div class="text-center mb-4">
              <h2 class="text-primary mb-0" id="emiAmount">â‚¹0</h2>
              <small class="text-muted">Monthly EMI</small>
            </div>
            <table class="table table-bordered">
              <tr><th>Total Payment</th><td id="totalPayment">â‚¹0</td></tr>
              <tr><th>Total Interest</th><td id="totalInterest">â‚¹0</td></tr>
              <tr><th>Interest % of Principal</th><td id="interestPercent">0%</td></tr>
            </table>
          </div>
          <div id="noResults" class="text-center text-muted">
            Enter loan details and click Calculate.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function calculateEMI() {
  const p = parseFloat(document.querySelector('[name="principal"]').value);
  const r = parseFloat(document.querySelector('[name="rate"]').value) / 12 / 100;
  const n = parseFloat(document.querySelector('[name="tenure"]').value) * 12;

  const emi = (p * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
  const totalPayment = emi * n;
  const totalInterest = totalPayment - p;
  const interestPercent = (totalInterest / p * 100).toFixed(1);

  document.getElementById('emiAmount').textContent = 'â‚¹' + Math.round(emi).toLocaleString('en-IN');
  document.getElementById('totalPayment').textContent = 'â‚¹' + Math.round(totalPayment).toLocaleString('en-IN');
  document.getElementById('totalInterest').textContent = 'â‚¹' + Math.round(totalInterest).toLocaleString('en-IN');
  document.getElementById('interestPercent').textContent = interestPercent + '%';

  document.getElementById('results').style.display = 'block';
  document.getElementById('noResults').style.display = 'none';
}
</script>
