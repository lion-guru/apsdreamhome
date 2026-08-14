<?php
/**
 * Real-Time Analytics Dashboard
 * APS Dream Home â€” Live metrics + Chart.js + WebSocket auto-refresh
 */

$m = $metrics ?? [];
$cd = $chart_data ?? [];
$acts = $activities ?? [];
$updated = date('d M Y, h:i A');

$fmt = fn($v) => 'â‚¹' . number_format((float)$v, 0, '.', ',');
?>

<!-- Title Bar -->
<div class="style-30464">
    <div>
        <h1 class="style-79140">
            <i class="fas fa-chart-line" class="style-78618"></i>Real-Time Analytics
        </h1>
        <p class="style-61566">
            <span id="rt-updated">Last updated: <?= htmlspecialchars($updated) ?></span>
            <span id="rt-ws-status" class="style-56313">
                <i class="fas fa-circle" class="style-338"></i> Connecting&hellip;
            </span>
            <span id="rt-refresh-badge" class="style-14210">
                <i class="fas fa-sync-alt fa-spin"></i> Refreshing&hellip;
            </span>
        </p>
    </div>
    <div class="style-85880">
        <button onclick="rtRefreshAll()" class="btn btn-sm btn-outline-primary" class="style-69165">
            <i class="fas fa-sync-alt"></i> Refresh Now
        </button>
        <a href="<?= BASE_URL ?>/admin/erp" class="btn btn-sm btn-outline-secondary" class="style-69165">
            <i class="fas fa-th-large"></i> ERP Overview
        </a>
    </div>
</div>

<!-- ROW 1 â€” 4 KPI Cards -->
<div id="rt-kpi-cards" class="style-94863">

    <!-- Leads Today -->
    <div class="aps-cp-card" class="style-95460">
        <div class="aps-cp-card-body" class="style-67049">
            <div class="style-67208">
                <div class="style-25782">
                    <i class="fas fa-user-plus" class="style-50292"></i>
                </div>
                <span class="style-82769">Leads Today</span>
            </div>
            <div id="rt-kpi-leads" class="style-89425"><?= (int)($m['leads_today'] ?? 0) ?></div>
            <div class="style-28983">New inquiries received</div>
        </div>
    </div>

    <!-- Bookings This Month -->
    <div class="aps-cp-card" class="style-74913">
        <div class="aps-cp-card-body" class="style-67049">
            <div class="style-67208">
                <div class="style-66150">
                    <i class="fas fa-file-signature" class="style-40926"></i>
                </div>
                <span class="style-88669">Bookings This Month</span>
            </div>
            <div id="rt-kpi-bookings" class="style-89425"><?= (int)($m['bookings_month'] ?? 0) ?></div>
            <div class="style-28983">Active plot bookings</div>
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="aps-cp-card" class="style-24973">
        <div class="aps-cp-card-body" class="style-67049">
            <div class="style-67208">
                <div class="style-57731">
                    <i class="fas fa-rupee-sign" class="style-24030"></i>
                </div>
                <span class="style-39510">Revenue This Month</span>
            </div>
            <div id="rt-kpi-revenue" class="style-89425"><?= $fmt($m['revenue_month'] ?? 0) ?></div>
            <div class="style-28983">Booking value booked</div>
        </div>
    </div>

    <!-- Collections Today -->
    <div class="aps-cp-card" class="style-22499">
        <div class="aps-cp-card-body" class="style-67049">
            <div class="style-67208">
                <div class="style-28637">
                    <i class="fas fa-hand-holding-usd" class="style-15659"></i>
                </div>
                <span class="style-70531">Collections Today</span>
            </div>
            <div id="rt-kpi-collections" class="style-89425"><?= $fmt($m['collections_today'] ?? 0) ?></div>
            <div class="style-28983">Cash receipts collected</div>
        </div>
    </div>
</div>

<!-- ROW 2 â€” Charts (2Ã—2 grid) -->
<div class="style-38908">

    <!-- Chart 1: Leads over last 7 days (Line) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" class="style-48741">
            <span><i class="fas fa-chart-area" class="style-9981"></i>Leads â€” Last 7 Days</span>
            <span class="badge bg-primary-subtle text-primary-emphasis" class="style-56522">LINE</span>
        </div>
        <div class="aps-cp-card-body" class="style-47072">
            <canvas id="rtChartLeads7d"></canvas>
        </div>
    </div>

    <!-- Chart 2: Revenue by Colony (Bar) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" class="style-48741">
            <span><i class="fas fa-chart-bar" class="style-28560"></i>Revenue by Colony</span>
            <span class="badge bg-success-subtle text-success-emphasis" class="style-56522">BAR</span>
        </div>
        <div class="aps-cp-card-body" class="style-47072">
            <canvas id="rtChartRevenueColony"></canvas>
        </div>
    </div>

    <!-- Chart 3: Lead Sources (Doughnut) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" class="style-48741">
            <span><i class="fas fa-chart-pie" class="style-22590"></i>Lead Sources Breakdown</span>
            <span class="badge bg-purple-subtle text-purple-emphasis" class="style-56522">DOUGHNUT</span>
        </div>
        <div class="aps-cp-card-body" class="style-47072">
            <canvas id="rtChartLeadSources"></canvas>
        </div>
    </div>

    <!-- Chart 4: Booking Trend 30 days (Line) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" class="style-48741">
            <span><i class="fas fa-chart-line" class="style-39559"></i>Booking Trend â€” Last 30 Days</span>
            <span class="badge bg-warning-subtle text-warning-emphasis" class="style-56522">LINE</span>
        </div>
        <div class="aps-cp-card-body" class="style-47072">
            <canvas id="rtChartBookings30d"></canvas>
        </div>
    </div>
</div>

<!-- ROW 3 â€” Live Activity Feed -->
<div class="aps-cp-card" class="style-99970">
    <div class="aps-cp-card-header" class="style-48741">
        <span><i class="fas fa-stream" class="style-26991"></i>Live Activity Feed</span>
        <span class="badge bg-light text-dark" class="style-56522" id="rt-activity-count"><?= count($acts) ?> events</span>
    </div>
    <div class="aps-cp-card-body" class="style-86260" id="rt-activity-list">
        <?php if (empty($acts)): ?>
            <p class="style-2934">No recent activity.</p>
        <?php else: ?>
            <?php foreach ($acts as $act): ?>
                <?php
                    $actType = htmlspecialchars($act['type'] ?? 'event');
                    $actDesc = htmlspecialchars($act['description'] ?? '-');
                    $actTime = htmlspecialchars($act['created_at'] ?? '');
                    $actIcon = $act['icon'] ?? 'fa-circle';
                    $actColor = $act['color'] ?? '#64748b';
                ?>
                <div class="style-78578">
                    <div class="style-79572">
                        <i class="fas <?= $actIcon ?>" class="style-59362"></i>
                    </div>
                    <div class="style-65975">
                        <div class="style-36189"><?= $actType ?></div>
                        <div class="style-57020"><?= $actDesc ?></div>
                    </div>
                    <div class="style-38661"><?= $actTime ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>

<script>
(function() {
    'use strict';

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Chart Instances â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */
    let chartLeads7d    = null;
    let chartRevenue    = null;
    let chartSources    = null;
    let chartBookings30 = null;

    const COLORS = {
        primary:   '#3b82f6',
        success:   '#10b981',
        purple:    '#14b8a6',
        warning:   '#f59e0b',
        danger:    '#ef4444',
        teal:      '#06b6d4',
        pink:      '#ec4899',
        lime:      '#84cc16',
        palette:   ['#3b82f6','#10b981','#f59e0b','#ef4444','#14b8a6','#06b6d4','#ec4899','#84cc16']
    };

    function fmt(n) { return 'â‚¹' + Number(n).toLocaleString('en-IN', {maximumFractionDigits:0}); }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Chart Renderers â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    function renderLeads7d(data) {
        const ctx = document.getElementById('rtChartLeads7d');
        if (!ctx) return;

        // Fill missing dates
        const filled = fillDateSeries(data, 7);
        const labels = filled.map(r => r.label);
        const values = filled.map(r => r.value);

        if (chartLeads7d) {
            chartLeads7d.data.labels = labels;
            chartLeads7d.data.datasets[0].data = values;
            chartLeads7d.update('none');
        } else {
            chartLeads7d = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Leads',
                        data: values,
                        borderColor: COLORS.primary,
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: COLORS.primary,
                        borderWidth: 2.5
                    }]
                },
                options: chartOpts('Leads')
            });
        }
    }

    function renderRevenueByColony(data) {
        const ctx = document.getElementById('rtChartRevenueColony');
        if (!ctx) return;

        const labels = data.map(r => r.colony || 'Unknown');
        const values = data.map(r => parseFloat(r.revenue || 0));

        if (chartRevenue) {
            chartRevenue.data.labels = labels;
            chartRevenue.data.datasets[0].data = values;
            chartRevenue.update('none');
        } else {
            chartRevenue = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: values,
                        backgroundColor: COLORS.palette.slice(0, labels.length).map(c => c + 'cc'),
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    ...chartOpts('Revenue'),
                    indexAxis: labels.length > 4 ? 'y' : 'x',
                    plugins: {
                        ...chartOpts('Revenue').plugins,
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return fmt(ctx.parsed.y || ctx.parsed.x); }
                            }
                        }
                    }
                }
            });
        }
    }

    function renderLeadSources(data) {
        const ctx = document.getElementById('rtChartLeadSources');
        if (!ctx) return;

        const labels = data.map(r => r.source || 'Other');
        const values = data.map(r => parseInt(r.cnt || 0));

        if (chartSources) {
            chartSources.data.labels = labels;
            chartSources.data.datasets[0].data = values;
            chartSources.update('none');
        } else {
            chartSources = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: COLORS.palette.slice(0, labels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { padding: 12, font: { size: 12 } } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                                    var pct = total ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                    return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function renderBookings30d(data) {
        const ctx = document.getElementById('rtChartBookings30d');
        if (!ctx) return;

        const filled = fillDateSeries(data, 30);
        const labels = filled.map(r => r.label);
        const values = filled.map(r => r.value);

        if (chartBookings30) {
            chartBookings30.data.labels = labels;
            chartBookings30.data.datasets[0].data = values;
            chartBookings30.update('none');
        } else {
            chartBookings30 = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Bookings',
                        data: values,
                        borderColor: COLORS.warning,
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: COLORS.warning,
                        borderWidth: 2.5
                    }]
                },
                options: chartOpts('Bookings')
            });
        }
    }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Helpers â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    function chartOpts(yLabel) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, maxRotation: 0 } },
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 11 }, stepSize: 1 },
                    title: { display: true, text: yLabel, font: { size: 12, weight: '600' } }
                }
            }
        };
    }

    function fillDateSeries(rows, days) {
        const map = {};
        rows.forEach(function(r) {
            var key = (r.dt || r.date || '').substring(0, 10);
            map[key] = parseInt(r.cnt || r.count || 0);
        });
        var result = [];
        var now = new Date();
        for (var i = days - 1; i >= 0; i--) {
            var d = new Date(now);
            d.setDate(d.getDate() - i);
            var key = d.toISOString().substring(0, 10);
            var label = d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short' });
            result.push({ label: label, value: map[key] || 0 });
        }
        return result;
    }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� KPI Updater â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    function updateKPIs(m) {
        animateNumber('rt-kpi-leads', m.leads_today);
        animateNumber('rt-kpi-bookings', m.bookings_month);
        document.getElementById('rt-kpi-revenue').textContent = fmt(m.revenue_month);
        document.getElementById('rt-kpi-collections').textContent = fmt(m.collections_today);
        document.getElementById('rt-updated').textContent = 'Last updated: ' + new Date().toLocaleString('en-IN');
    }

    function animateNumber(id, target) {
        var el = document.getElementById(id);
        if (!el) return;
        var current = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
        if (current === target) return;
        el.textContent = target;
        el.style.transition = 'transform 0.3s';
        el.style.transform = 'scale(1.15)';
        setTimeout(function() { el.style.transform = 'scale(1)'; }, 300);
    }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Activity Feed Updater â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    function updateActivityFeed(activities) {
        var list = document.getElementById('rt-activity-list');
        var count = document.getElementById('rt-activity-count');
        if (!list) return;

        if (!activities || activities.length === 0) {
            list.innerHTML = '<p class="style-2934">No recent activity.</p>';
            if (count) count.textContent = '0 events';
            return;
        }

        var html = '';
        activities.forEach(function(a) {
            var type  = (a.type || 'event').replace(/</g, '&lt;');
            var desc  = (a.description || '-').replace(/</g, '&lt;');
            var time  = (a.created_at || '').replace(/</g, '&lt;');
            var icon  = a.icon  || 'fa-circle';
            var color = a.color || '#64748b';
            html += '<div class="style-78578">'
                + '<div class="style-80599">'
                + '<i class="fas '+icon+'" class="style-54303"></i></div>'
                + '<div class="style-65975"><div class="style-36189">'+type+'</div>'
                + '<div class="style-57020">'+desc+'</div></div>'
                + '<div class="style-38661">'+time+'</div></div>';
        });
        list.innerHTML = html;
        if (count) count.textContent = activities.length + ' events';
    }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Data Fetchers â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    function showRefreshBadge() {
        var badge = document.getElementById('rt-refresh-badge');
        if (badge) badge.style.display = 'inline';
    }
    function hideRefreshBadge() {
        var badge = document.getElementById('rt-refresh-badge');
        if (badge) { setTimeout(function(){ badge.style.display = 'none'; }, 600); }
    }

    async function fetchMetrics() {
        try {
            var res = await fetch('<?= BASE_URL ?>/admin/analytics/realtime/metrics', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                var data = await res.json();
                updateKPIs(data);
            }
        } catch(e) { /* silent */ }
    }

    async function fetchChartData() {
        try {
            var res = await fetch('<?= BASE_URL ?>/admin/analytics/realtime/chart-data', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                var data = await res.json();
                renderLeads7d(data.leads_7d || []);
                renderRevenueByColony(data.revenue_by_colony || []);
                renderLeadSources(data.lead_sources || []);
                renderBookings30d(data.bookings_30d || []);
            }
        } catch(e) { /* silent */ }
    }

    function rtRefreshAll() {
        showRefreshBadge();
        Promise.all([fetchMetrics(), fetchChartData()]).then(hideRefreshBadge).catch(hideRefreshBadge);
    }

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� WebSocket â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    var ws = null;
    var wsReconnectAttempts = 0;
    var wsReconnectMax = 10;

    function connectAnalyticsWS() {
        if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) return;

        var protocol = location.protocol === 'https:' ? 'wss:' : 'ws:';
        var url = protocol + '//' + location.hostname + ':8080';

        try {
            ws = new WebSocket(url);
        } catch(e) { return; }

        ws.onopen = function() {
            wsReconnectAttempts = 0;
            updateWSStatus(true);
            // Subscribe to analytics channel
            ws.send(JSON.stringify({ type: 'subscribe', channel: 'analytics_global' }));
        };

        ws.onmessage = function(event) {
            try {
                var msg = JSON.parse(event.data);
                if (msg.type === 'notification' && msg.data && msg.data.channel_name === 'analytics_global') {
                    // Immediate refresh on analytics broadcast
                    rtRefreshAll();
                } else if (msg.type === 'pong' || msg.type === 'connection') {
                    // heartbeat / connection ack â€” ignore
                }
            } catch(e) { /* ignore parse errors */ }
        };

        ws.onclose = function() {
            updateWSStatus(false);
            if (wsReconnectAttempts < wsReconnectMax) {
                wsReconnectAttempts++;
                var delay = Math.min(1000 * Math.pow(2, wsReconnectAttempts), 30000);
                setTimeout(connectAnalyticsWS, delay);
            }
        };

        ws.onerror = function() { /* onclose will fire */ };
    }

    function updateWSStatus(connected) {
        var el = document.getElementById('rt-ws-status');
        if (!el) return;
        if (connected) {
            el.innerHTML = '<i class="fas fa-circle" class="style-81605"></i> <span class="style-75447">Live</span>';
        } else {
            el.innerHTML = '<i class="fas fa-circle" class="style-8418"></i> <span class="style-27277">Reconnecting&hellip;</span>';
        }
    }

    // Heartbeat every 30s
    setInterval(function() {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
        }
    }, 30000);

    /* â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� Init â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•� */

    document.addEventListener('DOMContentLoaded', function() {
        // Initial render from server-side data
        renderLeads7d(<?= json_encode($cd['leads_7d'] ?? [], JSON_HEX_TAG) ?>);
        renderRevenueByColony(<?= json_encode($cd['revenue_by_colony'] ?? [], JSON_HEX_TAG) ?>);
        renderLeadSources(<?= json_encode($cd['lead_sources'] ?? [], JSON_HEX_TAG) ?>);
        renderBookings30d(<?= json_encode($cd['bookings_30d'] ?? [], JSON_HEX_TAG) ?>);

        // Auto-refresh every 30 seconds
        setInterval(function() {
            fetchMetrics();
            fetchChartData();
        }, 30000);

        // Connect WebSocket for instant updates
        connectAnalyticsWS();
    });

    // Expose for manual refresh button
    window.rtRefreshAll = rtRefreshAll;

})();
</script>
