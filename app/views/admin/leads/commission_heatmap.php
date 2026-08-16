<?php $this->layout = 'layouts/admin'; ?>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Commission Performance Heatmap</h1>
  </div>

  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">Top 20 Earners</h5></div>
        <div class="card-body">
          <table class="table table-sm">
            <thead><tr><th>#</th><th>Name</th><th>Total Earned</th><th>TXNs</th></tr></thead>
            <tbody>
              <?php foreach ($topEarners as $i => $e): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($e['name'] ?? '') ?></td>
                <td>₹<?= number_format($e['total_earned']) ?></td>
                <td><?= $e['transactions'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">By Commission Type</h5></div>
        <div class="card-body">
          <canvas id="commissionChart" height="200"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">Last 12 Months</h5></div>
        <div class="card-body">
          <canvas id="monthlyChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js" defer></script>
<script>
const ctx1 = document.getElementById('commissionChart').getContext('2d');
new Chart(ctx1, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_column($byCommissionType, 'type')) ?>,
    datasets: [{
      data: <?= json_encode(array_column($byCommissionType, 'total')) ?>,
      backgroundColor: ['#4f46e5','#7c3aed','#a855f7','#c084fc','#a78bfa']
    }]
  }
});

const ctx2 = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx2, {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($byMonth, 'month')) ?>,
    datasets: [{
      label: 'Total (₹)',
      data: <?= json_encode(array_column($byMonth, 'total')) ?>,
      borderColor: '#4f46e5',
      fill: false
    }]
  }
});
</script>
