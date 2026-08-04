<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Generate Property Valuation</h1>
    <a href="/admin/property-valuations" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to Reports
    </a>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">AI Property Valuation Generator</h5>
        </div>
        <div class="card-body">
          <form id="valuationForm" method="POST" action="/admin/property-valuations/generate">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="mb-3">
              <label class="form-label">Property ID</label>
              <input type="number" name="property_id" class="form-control" placeholder="Enter property ID (e.g., 1)" required>
              <div class="form-text">The plot property ID to generate valuation for.</div>
            </div>

            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              The AI engine will analyze the property using location, type, area, market trends, and comparable sales data.
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-magic"></i> Generate Valuation Report
            </button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-header">
          <h5 class="mb-0">How It Works</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item">
              <strong>1. Base Price:</strong> Uses location-based market rates
            </li>
            <li class="list-group-item">
              <strong>2. Location Multiplier:</strong> Demand index per city/colony
            </li>
            <li class="list-group-item">
              <strong>3. Market Trend:</strong> Current market trend adjustment
            </li>
            <li class="list-group-item">
              <strong>4. Condition Score:</strong> Property condition multiplier
            </li>
            <li class="list-group-item">
              <strong>5. AI Analysis:</strong> Comparable property analysis & recommendations
            </li>
            <li class="list-group-item">
              <strong>6. Income Approach:</strong> Rental yield-based valuation
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('valuationForm').addEventListener('submit', function(e) {
  const btn = this.querySelector('button[type="submit"]');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating Valuation...';
});
</script>
