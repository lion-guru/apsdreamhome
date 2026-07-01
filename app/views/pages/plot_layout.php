<!-- Interactive Plot Layout Map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f1f5f9; overflow: hidden; }
    .map-container { display: flex; height: calc(100vh - 0px); }
    .map-sidebar {
        width: 280px; min-width: 280px; background: #1e293b; color: #e2e8f0;
        display: flex; flex-direction: column; overflow-y: auto; z-index: 10;
    }
    .sidebar-header { padding: 16px 20px; border-bottom: 1px solid #334155; }
    .sidebar-header h2 { font-size: 16px; font-weight: 700; color: #f8fafc; margin-bottom: 2px; }
    .sidebar-header p { font-size: 11px; color: #94a3b8; }
    .colony-tabs { padding: 12px 16px; border-bottom: 1px solid #334155; }
    .colony-tab {
        display: block; width: 100%; padding: 10px 14px; margin-bottom: 4px;
        background: transparent; border: 1px solid transparent; border-radius: 8px;
        color: #cbd5e1; font-size: 13px; text-align: left; cursor: pointer;
        transition: all 0.2s;
    }
    .colony-tab:hover { background: #334155; color: #f8fafc; }
    .colony-tab.active { background: #0d9488; color: #fff; border-color: #6366f1; }
    .colony-tab .tab-stats { font-size: 11px; color: #94a3b8; margin-top: 4px; }
    .colony-tab.active .tab-stats { color: #c7d2fe; }
    .sidebar-legend { padding: 16px 20px; border-bottom: 1px solid #334155; }
    .sidebar-legend h3 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 10px; }
    .legend-item { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 13px; }
    .legend-dot { width: 14px; height: 14px; border-radius: 3px; flex-shrink: 0; }
    .sidebar-chart { padding: 16px 20px; flex: 1; }
    .sidebar-chart h3 { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 10px; }
    .chart-wrap { width: 100%; max-width: 220px; margin: 0 auto; }
    .sidebar-stats { padding: 12px 20px; border-top: 1px solid #334155; }
    .stat-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; }
    .stat-row .label { color: #94a3b8; }
    .stat-row .value { color: #f8fafc; font-weight: 600; }
    .map-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .map-toolbar {
        display: flex; align-items: center; gap: 12px; padding: 10px 20px;
        background: #fff; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;
    }
    .map-toolbar input[type="text"] {
        padding: 8px 14px; border: 1px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; width: 220px; outline: none;
    }
    .map-toolbar input:focus { border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,0.1); }
    .filter-btn {
        padding: 7px 14px; border: 1px solid #e2e8f0; border-radius: 8px;
        background: #fff; font-size: 12px; cursor: pointer; transition: all 0.15s;
    }
    .filter-btn:hover { background: #f1f5f9; }
    .filter-btn.active { background: #0d9488; color: #fff; border-color: #0d9488; }
    .zoom-controls { margin-left: auto; display: flex; gap: 4px; }
    .zoom-btn {
        width: 32px; height: 32px; border: 1px solid #e2e8f0; border-radius: 6px;
        background: #fff; cursor: pointer; font-size: 16px; display: flex;
        align-items: center; justify-content: center;
    }
    .zoom-btn:hover { background: #f1f5f9; }
    .map-viewport { flex: 1; overflow: auto; padding: 20px; position: relative; }
    .svg-wrapper { min-width: 100%; min-height: 100%; display: inline-block; }
    .svg-wrapper svg { display: block; }
    .plot-rect {
        cursor: pointer; transition: opacity 0.15s, stroke-width 0.15s;
    }
    .plot-rect:hover { opacity: 0.85; stroke-width: 2; }
    .plot-rect.dimmed { opacity: 0.2; }
    .plot-label { pointer-events: none; font-weight: 700; fill: #fff; text-anchor: middle; }
    .plot-sublabel { pointer-events: none; font-size: 9px; fill: rgba(255,255,255,0.85); text-anchor: middle; }
    .colony-group-label { font-size: 16px; font-weight: 700; fill: #1e293b; }
    .block-label { font-size: 11px; font-weight: 600; fill: #64748b; text-transform: uppercase; }
    .detail-panel {
        position: fixed; right: -420px; top: 0; width: 400px; height: 100vh;
        background: #fff; box-shadow: -4px 0 20px rgba(0,0,0,0.12); z-index: 100;
        transition: right 0.3s ease; display: flex; flex-direction: column;
    }
    .detail-panel.open { right: 0; }
    .detail-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0;
    }
    .detail-header h3 { font-size: 16px; color: #1e293b; }
    .detail-close { width: 32px; height: 32px; border: none; background: #f1f5f9; border-radius: 6px; cursor: pointer; font-size: 18px; }
    .detail-body { flex: 1; overflow-y: auto; padding: 20px; }
    .detail-field { margin-bottom: 14px; }
    .detail-field .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 3px; }
    .detail-field .value { font-size: 14px; color: #1e293b; font-weight: 500; }
    .detail-status-badge {
        display: inline-block; padding: 4px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 600; color: #fff;
    }
    .detail-footer { padding: 16px 20px; border-top: 1px solid #e2e8f0; flex-shrink: 0; }
    .book-btn {
        display: block; width: 100%; padding: 12px; background: #0d9488; color: #fff;
        border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
        cursor: pointer; text-align: center; text-decoration: none;
    }
    .book-btn:hover { background: #4338ca; }
    .book-btn.disabled { background: #94a3b8; cursor: not-allowed; pointer-events: none; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 90; display: none; }
    .overlay.open { display: block; }
    .empty-colony { display: flex; align-items: center; justify-content: center; height: 300px; color: #94a3b8; font-size: 14px; }
</style>

<div class="map-container">
    <!-- SIDEBAR -->
    <div class="map-sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-map-marked-alt"></i> <?= __('plot_title', [], 'Plot Layout Map') ?></h2>
            <p><?= __('plot_subtitle', [], 'Interactive colony plot grid') ?></p>
        </div>
        <div class="colony-tabs" id="colonyTabs">
            <button class="colony-tab active" data-colony="all" onclick="switchColony('all')">
                <div><?= __('plot_all_colonies', [], 'All Colonies') ?></div>
                <div class="tab-stats"><?= $total_stats['total'] ?> plots &bull; <?= $total_stats['available'] ?> available</div>
            </button>
            <?php foreach ($colonies as $col): ?>
                <button class="colony-tab" data-colony="<?= $col['id'] ?>" onclick="switchColony('<?= $col['id'] ?>')">
                    <div><?= htmlspecialchars($col['name']) ?></div>
                    <div class="tab-stats"><?= $col['stats']['total'] ?> plots &bull; <?= $col['stats']['available'] ?> available</div>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="sidebar-legend">
            <h3><?= __('plot_legend_title', [], 'Status Legend') ?></h3>
            <div class="legend-item"><div class="legend-dot" style="background:#10b981"></div> Available</div>
            <div class="legend-item"><div class="legend-dot" style="background:#f59e0b"></div> Booked / Reserved</div>
            <div class="legend-item"><div class="legend-dot" style="background:#ef4444"></div> Sold</div>
            <div class="legend-item"><div class="legend-dot" style="background:#6b7280"></div> Hold / Other</div>
        </div>
        <div class="sidebar-chart">
            <h3><?= __('plot_distribution', [], 'Distribution') ?></h3>
            <div class="chart-wrap"><canvas id="statusChart"></canvas></div>
        </div>
        <div class="sidebar-stats" id="sidebarStats">
            <div class="stat-row"><span class="label"><?= __('plot_stat_total', [], 'Total Plots') ?></span><span class="value" id="statTotal"><?= $total_stats['total'] ?></span></div>
            <div class="stat-row"><span class="label"><?= __('plot_stat_available', [], 'Available') ?></span><span class="value" style="color:#10b981" id="statAvail"><?= $total_stats['available'] ?></span></div>
            <div class="stat-row"><span class="label"><?= __('plot_stat_booked', [], 'Booked') ?></span><span class="value" style="color:#f59e0b" id="statBooked"><?= $total_stats['booked'] ?></span></div>
            <div class="stat-row"><span class="label"><?= __('plot_stat_sold', [], 'Sold') ?></span><span class="value" style="color:#ef4444" id="statSold"><?= $total_stats['sold'] ?></span></div>
            <div class="stat-row"><span class="label"><?= __('plot_stat_blocked', [], 'Blocked') ?></span><span class="value" style="color:#94a3b8" id="statBlocked"><?= $total_stats['blocked'] ?></span></div>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="map-main">
        <div class="map-toolbar">
            <input type="text" id="searchInput" placeholder="<?= __('plot_search_placeholder', [], 'Search plot number...') ?>" oninput="searchPlots(this.value)">
            <button class="filter-btn active" data-status="all" onclick="filterStatus('all', this)">All</button>
            <button class="filter-btn" data-status="available" onclick="filterStatus('available', this)">Available</button>
            <button class="filter-btn" data-status="booked" onclick="filterStatus('booked', this)">Booked</button>
            <button class="filter-btn" data-status="sold" onclick="filterStatus('sold', this)">Sold</button>
            <button class="filter-btn" data-status="blocked" onclick="filterStatus('blocked', this)">Blocked</button>
            <div class="zoom-controls">
                <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">+</button>
                <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">&minus;</button>
                <button class="zoom-btn" onclick="zoomReset()" title="Reset"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        <div class="map-viewport" id="mapViewport">
            <div class="svg-wrapper" id="svgWrapper"></div>
        </div>
    </div>
</div>

<!-- DETAIL PANEL -->
<div class="overlay" id="overlay" onclick="closeDetail()"></div>
<div class="detail-panel" id="detailPanel">
    <div class="detail-header">
        <h3 id="detailTitle"><?= __('plot_detail_title', [], 'Plot Details') ?></h3>
        <button class="detail-close" onclick="closeDetail()">&times;</button>
    </div>
    <div class="detail-body" id="detailBody"></div>
    <div class="detail-footer" id="detailFooter"></div>
</div>

<script>
// ---- DATA ----
var coloniesData = <?= json_encode($colonies, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
var currentColony = 'all';
var currentStatus = 'all';
var searchTerm = '';
var zoomLevel = 1;
var statusChart = null;

var STATUS_MAP = {
    available: { label: 'Available', color: '#10b981', border: '#059669' },
    booked:    { label: 'Booked',    color: '#f59e0b', border: '#d97706' },
    reserved:  { label: 'Reserved',  color: '#f59e0b', border: '#d97706' },
    sold:      { label: 'Sold',      color: '#ef4444', border: '#dc2626' },
    hold:      { label: 'Hold',      color: '#6b7280', border: '#4b5563' },
    under_construction: { label: 'Under Construction', color: '#6b7280', border: '#4b5563' }
};

function getDisplayStatus(s) {
    return STATUS_MAP[s] || { label: s, color: '#6b7280', border: '#4b5563' };
}

// ---- COLONY SWITCH ----
function switchColony(id) {
    currentColony = id;
    document.querySelectorAll('.colony-tab').forEach(function(t) {
        t.classList.toggle('active', t.getAttribute('data-colony') === id);
    });
    updateStats();
    renderSVG();
}

// ---- STATUS FILTER ----
function filterStatus(status, btn) {
    currentStatus = status;
    document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    renderSVG();
}

// ---- SEARCH ----
function searchPlots(term) {
    searchTerm = term.toLowerCase().trim();
    renderSVG();
}

// ---- ZOOM ----
function zoomIn() { zoomLevel = Math.min(zoomLevel * 1.2, 3); applyZoom(); }
function zoomOut() { zoomLevel = Math.max(zoomLevel / 1.2, 0.3); applyZoom(); }
function zoomReset() { zoomLevel = 1; applyZoom(); }
function applyZoom() {
    document.getElementById('svgWrapper').style.transform = 'scale(' + zoomLevel + ')';
    document.getElementById('svgWrapper').style.transformOrigin = 'top left';
}

// ---- STATS ----
function updateStats() {
    var stats, label;
    if (currentColony === 'all') {
        stats = <?= json_encode($total_stats) ?>;
        label = 'All Colonies';
    } else {
        var col = coloniesData.find(function(c) { return c.id == currentColony; });
        if (!col) return;
        stats = col.stats;
        label = col.name;
    }
    document.getElementById('statTotal').textContent = stats.total;
    document.getElementById('statAvail').textContent = stats.available;
    document.getElementById('statBooked').textContent = stats.booked;
    document.getElementById('statSold').textContent = stats.sold;
    document.getElementById('statBlocked').textContent = stats.blocked;
    updateChart(stats);
}

// ---- CHART ----
function updateChart(stats) {
    var ctx = document.getElementById('statusChart');
    if (statusChart) statusChart.destroy();
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Booked', 'Sold', 'Blocked'],
            datasets: [{
                data: [stats.available, stats.booked, stats.sold, stats.blocked],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#6b7280'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { bodyFont: { size: 12 } }
            }
        }
    });
}

// ---- SVG RENDER ----
function renderSVG() {
    var wrapper = document.getElementById('svgWrapper');
    var html = '';
    var colsToShow = currentColony === 'all' ? coloniesData : coloniesData.filter(function(c) { return c.id == currentColony; });
    var SCALE = 2.2;
    var GAP = 10;
    var PLOT_PAD = 4;
    var runningY = 30;

    colsToShow.forEach(function(colony) {
        var plots = colony.plots;
        if (!plots || plots.length === 0) {
            html += '<div class="empty-colony"><i class="fas fa-map" style="font-size:28px;margin-right:10px"></i> No plots in ' + escHtml(colony.name) + '</div>';
            return;
        }

        // Group by block
        var blocks = {};
        plots.forEach(function(p) {
            var b = p.block || 'A';
            if (!blocks[b]) blocks[b] = [];
            blocks[b].push(p);
        });

        var blockNames = Object.keys(blocks).sort();

        // Colony header
        html += '<text x="20" y="' + runningY + '" class="colony-group-label">' + escHtml(colony.name) + '</text>';
        runningY += 24;

        var maxSvgWidth = 800;

        blockNames.forEach(function(blockName) {
            var blockPlots = blocks[blockName];
            html += '<text x="20" y="' + runningY + '" class="block-label">Block ' + escHtml(blockName) + '</text>';
            runningY += 18;

            var curX = 20;
            var rowMaxH = 0;
            var rowStartX = 20;
            var rowMaxWidth = 1000;

            blockPlots.forEach(function(plot) {
                var w = Math.max((parseFloat(plot.width_ft) || 30) * SCALE, 40);
                var h = Math.max((parseFloat(plot.length_ft) || 40) * SCALE, 40);

                // Wrap row
                if (curX + w > rowMaxWidth && curX > rowStartX) {
                    curX = rowStartX;
                    runningY += rowMaxH + GAP;
                    rowMaxH = 0;
                }

                var status = getDisplayStatus(plot.status);
                var isVisible = matchPlot(plot);
                var opacity = isVisible ? '' : ' style="opacity:0.15"';

                html += '<g class="plot-group" data-id="' + plot.id + '"' + opacity + '>';
                html += '<rect class="plot-rect" x="' + curX + '" y="' + runningY + '" width="' + w + '" height="' + h + '" rx="4" fill="' + status.color + '" stroke="' + status.border + '" stroke-width="1" onclick="showPlotDetail(' + plot.id + ')" data-status="' + plot.status + '" />';

                // Plot number
                var fontSize = Math.max(10, Math.min(13, w / 6));
                html += '<text x="' + (curX + w / 2) + '" y="' + (runningY + h / 2 - 2) + '" class="plot-label" font-size="' + fontSize + '">' + escHtml(plot.plot_number) + '</text>';

                // Area label
                var areaFontSize = Math.max(8, Math.min(10, w / 8));
                var areaText = parseFloat(plot.area_sqft) ? Math.round(plot.area_sqft) + ' sqft' : '';
                if (areaText && h > 30) {
                    html += '<text x="' + (curX + w / 2) + '" y="' + (runningY + h / 2 + 12) + '" class="plot-sublabel" font-size="' + areaFontSize + '">' + areaText + '</text>';
                }

                html += '</g>';
                curX += w + GAP;
                rowMaxH = Math.max(rowMaxH, h);
                if (curX > maxSvgWidth) maxSvgWidth = curX;
            });

            runningY += rowMaxH + GAP + 8;
        });

        runningY += 20;
    });

    var svgWidth = Math.max(maxSvgWidth + 40, 800);
    var svgHeight = runningY + 40;
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + svgWidth + ' ' + svgHeight + '" width="' + svgWidth + '" height="' + svgHeight + '" style="background:#fff;border-radius:12px">' + html + '</svg>';
    wrapper.innerHTML = svg;
    applyZoom();
}

function matchPlot(plot) {
    if (currentStatus !== 'all') {
        var s = plot.status;
        if (currentStatus === 'blocked') {
            if (s !== 'hold' && s !== 'under_construction') return false;
        } else if (currentStatus === 'booked') {
            if (s !== 'booked' && s !== 'reserved') return false;
        } else {
            if (s !== currentStatus) return false;
        }
    }
    if (searchTerm) {
        if ((plot.plot_number || '').toLowerCase().indexOf(searchTerm) === -1 &&
            (plot.block || '').toLowerCase().indexOf(searchTerm) === -1) return false;
    }
    return true;
}

// ---- DETAIL PANEL ----
function showPlotDetail(id) {
    var plot = null;
    coloniesData.forEach(function(c) {
        c.plots.forEach(function(p) { if (p.id == id) plot = p; });
    });
    if (!plot) return;

    var status = getDisplayStatus(plot.status);
    document.getElementById('detailTitle').textContent = 'Plot ' + escHtml(plot.plot_number);

    var body = '<div class="detail-field"><div class="label">Status</div><div class="value"><span class="detail-status-badge" style="background:' + status.color + '">' + status.label + '</span></div></div>';
    body += '<div class="detail-field"><div class="label">Plot Number</div><div class="value">' + escHtml(plot.plot_number) + '</div></div>';
    body += '<div class="detail-field"><div class="label">Colony</div><div class="value">' + escHtml(plot.colony_name) + '</div></div>';
    body += '<div class="detail-field"><div class="label">Block</div><div class="value">' + escHtml(plot.block || '-') + '</div></div>';
    body += '<div class="detail-field"><div class="label">Dimensions</div><div class="value">' + (plot.width_ft || '-') + ' ft x ' + (plot.length_ft || '-') + ' ft</div></div>';
    body += '<div class="detail-field"><div class="label">Area</div><div class="value">' + (parseFloat(plot.area_sqft) ? Math.round(plot.area_sqft) + ' sqft' : '-') + '</div></div>';
    body += '<div class="detail-field"><div class="label">Total Price</div><div class="value" style="color:#0d9488;font-size:16px">&#8377; ' + formatPrice(plot.total_price) + '</div></div>';
    if (plot.facing) body += '<div class="detail-field"><div class="label">Facing</div><div class="value">' + escHtml(plot.facing) + '</div></div>';
    if (plot.corner_plot == 1) body += '<div class="detail-field"><div class="label">Corner Plot</div><div class="value" style="color:#10b981"><i class="fas fa-check-circle"></i> Yes</div></div>';
    if (plot.park_facing == 1) body += '<div class="detail-field"><div class="label">Park Facing</div><div class="value" style="color:#10b981"><i class="fas fa-check-circle"></i> Yes</div></div>';
    if (plot.last_status_change) body += '<div class="detail-field"><div class="label">Last Status Change</div><div class="value">' + escHtml(plot.last_status_change) + '</div></div>';

    document.getElementById('detailBody').innerHTML = body;

    var footer = '';
    if (plot.status === 'available') {
        footer = '<a href="' + BASE_URL + '/plots/' + plot.id + '/book" class="book-btn"><i class="fas fa-bookmark"></i> Book Now</a>';
    } else {
        footer = '<a class="book-btn disabled"><i class="fas fa-ban"></i> Not Available</a>';
    }
    document.getElementById('detailFooter').innerHTML = footer;

    document.getElementById('detailPanel').classList.add('open');
    document.getElementById('overlay').classList.add('open');
}

function closeDetail() {
    document.getElementById('detailPanel').classList.remove('open');
    document.getElementById('overlay').classList.remove('open');
}

// ---- HELPERS ----
function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatPrice(p) {
    var n = parseFloat(p) || 0;
    if (n >= 10000000) return (n / 10000000).toFixed(2) + ' Cr';
    if (n >= 100000) return (n / 100000).toFixed(2) + ' L';
    return n.toLocaleString('en-IN');
}

// ---- INIT ----
document.addEventListener('DOMContentLoaded', function() {
    updateStats();
    renderSVG();
});
</script>
