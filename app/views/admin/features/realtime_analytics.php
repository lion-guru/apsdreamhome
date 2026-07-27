<?php
$page_title = $page_title ?? 'Real-Time Analytics';
$page_heading = $page_heading ?? 'Real-Time Analytics';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chart-area me-2"></i>Real-Time Analytics</h1>
    <div>
      <span class="badge bg-success" id="liveStatus"><i class="fas fa-circle me-1"></i> Live</span>
      <small class="text-muted ms-2" id="lastUpdate">Loading...</small>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white">
      <div class="card-body aps-cp-card-body">
        <small class="opacity-75">Leads (30d)</small>
        <h3 class="mb-0" id="kpiLeads">—</h3>
        <small><i class="fas fa-arrow-up"></i> <span id="kpiLeadsDelta">0</span>% vs prev</small>
      </div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white">
      <div class="card-body aps-cp-card-body">
        <small class="opacity-75">Bookings (30d)</small>
        <h3 class="mb-0" id="kpiBookings">—</h3>
        <small>₹<span id="kpiRevenue">0</span>L revenue</small>
      </div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-info text-white">
      <div class="card-body aps-cp-card-body">
        <small class="opacity-75">Active Users</small>
        <h3 class="mb-0" id="kpiUsers">—</h3>
        <small>Total customers</small>
      </div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-white">
      <div class="card-body aps-cp-card-body">
        <small class="opacity-75">Conversion Rate</small>
        <h3 class="mb-0" id="kpiConv">—</h3>
        <small>Lead → Booking</small>
      </div>
    </div></div>
  </div>

  <div class="row mb-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Leads Over Time (Last 30 Days)</h5></div>
        <div class="card-body aps-cp-card-body"><canvas id="leadsChart" height="80"></canvas></div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Lead Sources</h5></div>
        <div class="card-body aps-cp-card-body"><canvas id="sourcesChart"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Pipeline Stages</h5></div>
        <div class="card-body aps-cp-card-body"><canvas id="pipelineChart" height="120"></canvas></div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0">Property Type Distribution</h5></div>
        <div class="card-body aps-cp-card-body"><canvas id="propertyTypeChart" height="120"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-robot me-2"></i>AI Insights</h5>
          <button class="btn btn-sm btn-outline-primary" id="refreshAI"><i class="fas fa-sync"></i> Refresh</button>
        </div>
        <div class="card-body aps-cp-card-body" id="aiInsights">
          <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading insights...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let leadsChart, sourcesChart, pipelineChart, propertyTypeChart;

async function loadAnalytics() {
  try {
    const res = await fetch('<?= BASE_URL ?>/api/v2/analytics/dashboard', { credentials: 'same-origin' });
    const data = await res.json();
    if (data.error) return;
    renderKPIs(data);
    renderLeadsChart(data.leads_by_day || []);
    renderSourcesChart(data.leads_by_source || {});
    renderPipelineChart(data.pipeline || {});
    renderPropertyTypeChart(data.property_types || {});
    document.getElementById('lastUpdate').textContent = 'Updated ' + new Date().toLocaleTimeString();
  } catch (e) { console.error(e); }
}

function renderKPIs(d) {
  document.getElementById('kpiLeads').textContent = d.leads_30d || 0;
  document.getElementById('kpiLeadsDelta').textContent = d.leads_delta || 0;
  document.getElementById('kpiBookings').textContent = d.bookings_30d || 0;
  document.getElementById('kpiRevenue').textContent = ((d.revenue_30d || 0) / 100000).toFixed(1);
  document.getElementById('kpiUsers').textContent = d.total_customers || 0;
  const conv = d.leads_30d > 0 ? ((d.bookings_30d / d.leads_30d) * 100).toFixed(1) : '0.0';
  document.getElementById('kpiConv').textContent = conv + '%';
}

function renderLeadsChart(rows) {
  const ctx = document.getElementById('leadsChart');
  if (leadsChart) leadsChart.destroy();
  leadsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: rows.map(r => r.date),
      datasets: [{
        label: 'Leads', data: rows.map(r => r.count),
        borderColor: 'rgb(75, 192, 192)', backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.3, fill: true
      }]
    },
    options: { responsive: true, maintainAspectRatio: true }
  });
}

function renderSourcesChart(data) {
  const ctx = document.getElementById('sourcesChart');
  if (sourcesChart) sourcesChart.destroy();
  sourcesChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: Object.keys(data),
      datasets: [{ data: Object.values(data), backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
}

function renderPipelineChart(data) {
  const ctx = document.getElementById('pipelineChart');
  if (pipelineChart) pipelineChart.destroy();
  pipelineChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: Object.keys(data),
      datasets: [{ label: 'Count', data: Object.values(data), backgroundColor: 'rgba(54, 185, 204, 0.7)' }]
    },
    options: { indexAxis: 'y', responsive: true }
  });
}

function renderPropertyTypeChart(data) {
  const ctx = document.getElementById('propertyTypeChart');
  if (propertyTypeChart) propertyTypeChart.destroy();
  propertyTypeChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: Object.keys(data),
      datasets: [{ data: Object.values(data), backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
}

async function loadAIInsights() {
  try {
    const res = await fetch('<?= BASE_URL ?>/api/v2/analytics/insights', { credentials: 'same-origin' });
    const data = await res.json();
    const c = document.getElementById('aiInsights');
    if (data.insights && data.insights.length) {
      c.innerHTML = data.insights.map(i => `<div class="alert alert-${i.type || 'info'} mb-2"><strong>${i.title || 'Insight'}:</strong> ${i.message || ''}</div>`).join('');
    } else {
      c.innerHTML = '<div class="alert alert-secondary mb-0">No AI insights available yet. The system is still learning your data patterns.</div>';
    }
  } catch (e) { console.error(e); }
}

document.getElementById('refreshAI').addEventListener('click', loadAIInsights);

loadAnalytics();
loadAIInsights();
setInterval(loadAnalytics, 60000);
</script>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
