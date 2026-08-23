<?php
$base = $base ?? BASE_URL;
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4">Neighborhood Analytics</h1>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Property ID</label>
          <input type="number" id="propertyId" class="form-control" placeholder="Enter property ID..." min="1">
        </div>
        <div class="col-md-3">
          <button class="btn btn-primary w-100" onclick="loadNeighborhood()">
            <i class="fas fa-search me-1"></i> Analyze
          </button>
        </div>
        <div class="col-md-3">
          <select id="quickProperty" class="form-select" onchange="document.getElementById('propertyId').value=this.value">
            <option value="">Quick select...</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div id="results" class="style-24280">
    <div class="row g-3 mb-4" id="statCards"></div>

    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-building text-primary me-2"></i>Nearby Properties</h5>
          </div>
          <div class="card-body" id="nearbyProperties">
            <p class="text-muted mb-0">Loading...</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>Price Trends</h5>
          </div>
          <div class="card-body" id="priceTrends">
            <p class="text-muted mb-0">Loading...</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-map-pin text-warning me-2"></i>Amenities</h5>
          </div>
          <div class="card-body" id="amenities">
            <p class="text-muted mb-0">Loading...</p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-chart-bar text-info me-2"></i>Market Analysis</h5>
          </div>
          <div class="card-body" id="marketAnalysis">
            <p class="text-muted mb-0">Loading...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="loading" class="style-24280" class="text-center py-5">
    <div class="spinner-border text-primary mb-3" role="status"></div>
    <p class="text-muted">Fetching neighborhood data...</p>
  </div>

  <div id="error" class="style-24280" class="alert alert-danger mt-3"></div>

  <div id="empty" class="text-center py-5 text-muted">
    <i class="fas fa-map-marked-alt fa-4x mb-3 d-block"></i>
    <p>Enter a property ID and click Analyze to view neighborhood data</p>
  </div>
</div>

<script>
function loadNeighborhood() {
  const propertyId = document.getElementById('propertyId').value;
  if (!propertyId) { alert('Please enter a property ID'); return; }

  document.getElementById('loading').style.display = 'block';
  document.getElementById('results').style.display = 'none';
  document.getElementById('error').style.display = 'none';
  document.getElementById('empty').style.display = 'none';

  fetch('<?= $base ?>/admin/custom-features/neighborhood/' + propertyId)
    .then(r => r.json())
    .then(data => {
      document.getElementById('loading').style.display = 'none';
      if (!data.success) {
        document.getElementById('error').style.display = 'block';
        document.getElementById('error').textContent = data.message || 'Failed to load data';
        return;
      }
      const d = data.data;
      document.getElementById('results').style.display = 'block';

      document.getElementById('statCards').innerHTML = `
        <div class="col-md-3">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-1 text-primary">${d.nearby_properties ? d.nearby_properties.length : 0}</div>
            <div class="text-muted">Nearby Properties</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-1 text-info">${d.amenities ? d.amenities.length : 0}</div>
            <div class="text-muted">Amenities</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-1 text-success">${d.property ? '₹' + Number(d.property.price || 0).toLocaleString('en-IN') : '-'}</div>
            <div class="text-muted">Property Price</div>
          </div></div>
        </div>
        <div class="col-md-3">
          <div class="card aps-cp-card"><div class="card-body text-center">
            <div class="fs-1 text-warning">${d.property ? (d.property.area_sqft || '-') : '-'}</div>
            <div class="text-muted">Area (sqft)</div>
          </div></div>
        </div>
      `;

      if (d.nearby_properties && d.nearby_properties.length) {
        document.getElementById('nearbyProperties').innerHTML = d.nearby_properties.map(p =>
          `<div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span>${p.title || 'Property'}</span>
            <span class="fw-bold">₹${Number(p.price || 0).toLocaleString('en-IN')}</span>
          </div>`
        ).join('');
      } else {
        document.getElementById('nearbyProperties').innerHTML = '<p class="text-muted mb-0">No nearby properties found</p>';
      }

      if (d.price_trends && d.price_trends.length) {
        document.getElementById('priceTrends').innerHTML = d.price_trends.map(t =>
          `<div class="d-flex justify-content-between border-bottom pb-2 mb-2">
            <span>${t.period || 'N/A'}</span>
            <span class="fw-bold ${t.trend === 'up' ? 'text-success' : t.trend === 'down' ? 'text-danger' : ''}">
              ₹${Number(t.price || 0).toLocaleString('en-IN')}
            </span>
          </div>`
        ).join('');
      } else {
        document.getElementById('priceTrends').innerHTML = '<p class="text-muted mb-0">No trend data available</p>';
      }

      if (d.amenities && d.amenities.length) {
        document.getElementById('amenities').innerHTML = d.amenities.map(a =>
          `<span class="badge bg-secondary me-1 mb-1 fs-6">${a.name || a}</span>`
        ).join('');
      } else {
        document.getElementById('amenities').innerHTML = '<p class="text-muted mb-0">No amenities data</p>';
      }

      if (d.market_analysis && d.market_analysis.length) {
        document.getElementById('marketAnalysis').innerHTML = d.market_analysis.map(m =>
          `<div class="border-bottom pb-2 mb-2">
            <strong>${m.metric || m.label || 'Metric'}:</strong>
            <span class="float-end">${m.value || 'N/A'}</span>
          </div>`
        ).join('');
      } else {
        document.getElementById('marketAnalysis').innerHTML = '<p class="text-muted mb-0">No market data available</p>';
      }
    })
    .catch(err => {
      document.getElementById('loading').style.display = 'none';
      document.getElementById('error').style.display = 'block';
      document.getElementById('error').textContent = 'Error: ' + err.message;
    });
}

fetch('<?= $base ?>/admin/custom-features/stats')
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const sel = document.getElementById('quickProperty');
      if (data.data && data.data.properties) {
        const count = Math.min(data.data.properties, 10);
        for (let i = 1; i <= count; i++) {
          const opt = document.createElement('option');
          opt.value = i;
          opt.textContent = 'Property #' + i;
          sel.appendChild(opt);
        }
      }
    }
  })
  .catch(() => {});
</script>
