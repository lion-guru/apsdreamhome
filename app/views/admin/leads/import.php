<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Import Leads from CSV</h1>
    <a href="/admin/leads" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left"></i> Back to Leads
    </a>
  </div>

  <?php if ($this->getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $this->getFlash('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($this->getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $this->getFlash('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">CSV Import</h5>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="mb-3">
              <label class="form-label">CSV File *</label>
              <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-upload"></i> Import Leads
            </button>
          </form>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-header">
          <h5 class="mb-0">CSV Format</h5>
        </div>
        <div class="card-body">
          <p class="text-muted">Your CSV file must contain these headers:</p>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>name</th><th>email</th><th>phone</th><th>company</th>
                  <th>city</th><th>source</th><th>status</th><th>priority</th>
                  <th>lead_score</th><th>assigned_to</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>John Doe</td><td>john@example.com</td><td>9876543210</td><td>ABC Corp</td><td>Lucknow</td><td>website</td><td>new</td><td>medium</td><td>50</td><td>2</td></tr>
              </tbody>
            </table>
          </div>
          <ul class="text-muted small mt-2">
            <li><strong>name</strong> and <strong>phone</strong> or <strong>email</strong> are required</li>
            <li>Duplicate detection: leads with matching phone or email will be skipped</li>
            <li>status options: new, contacted, qualified, proposal, negotiation, closed_won, closed_lost, nurture</li>
            <li>priority options: low, medium, high</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
