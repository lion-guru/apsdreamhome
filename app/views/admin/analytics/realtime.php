<?php
/**
 * Real-Time Analytics Dashboard
 * APS Dream Home — Live metrics + Chart.js + WebSocket auto-refresh
 */

$m = $metrics ?? [];
$cd = $chart_data ?? [];
$acts = $activities ?? [];
$updated = date('d M Y, h:i A');

$fmt = fn($v) => '₹' . number_format((float)$v, 0, '.', ',');
?>

<!-- Title Bar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;color:#1e293b;">
            <i class="fas fa-chart-line" style="color:#6366f1;margin-right:8px;"></i>Real-Time Analytics
        </h1>
        <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">
            <span id="rt-updated">Last updated: <?= htmlspecialchars($updated) ?></span>
            <span id="rt-ws-status" style="margin-left:12px;font-size:0.75rem;color:#94a3b8;">
                <i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle;"></i> Connecting&hellip;
            </span>
            <span id="rt-refresh-badge" style="display:none;margin-left:8px;font-size:0.75rem;color:#10b981;font-weight:600;">
                <i class="fas fa-sync-alt fa-spin"></i> Refreshing&hellip;
            </span>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="rtRefreshAll()" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
            <i class="fas fa-sync-alt"></i> Refresh Now
        </button>
        <a href="<?= BASE_URL ?>/admin/erp" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            <i class="fas fa-th-large"></i> ERP Overview
        </a>
    </div>
</div>

<!-- ROW 1 — 4 KPI Cards -->
<div id="rt-kpi-cards" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">

    <!-- Leads Today -->
    <div class="aps-cp-card" style="border-left:4px solid #3b82f6;transition:transform 0.15s;">
        <div class="aps-cp-card-body" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user-plus" style="color:#3b82f6;font-size:1rem;"></i>
                </div>
                <span style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#3b82f6;letter-spacing:0.05em;">Leads Today</span>
            </div>
            <div id="rt-kpi-leads" style="font-size:2rem;font-weight:800;color:#1e293b;"><?= (int)($m['leads_today'] ?? 0) ?></div>
            <div style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">New inquiries received</div>
        </div>
    </div>

    <!-- Bookings This Month -->
    <div class="aps-cp-card" style="border-left:4px solid #10b981;transition:transform 0.15s;">
        <div class="aps-cp-card-body" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-file-signature" style="color:#10b981;font-size:1rem;"></i>
                </div>
                <span style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#10b981;letter-spacing:0.05em;">Bookings This Month</span>
            </div>
            <div id="rt-kpi-bookings" style="font-size:2rem;font-weight:800;color:#1e293b;"><?= (int)($m['bookings_month'] ?? 0) ?></div>
            <div style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">Active plot bookings</div>
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="aps-cp-card" style="border-left:4px solid #14b8a6;transition:transform 0.15s;">
        <div class="aps-cp-card-body" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-rupee-sign" style="color:#14b8a6;font-size:1rem;"></i>
                </div>
                <span style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#14b8a6;letter-spacing:0.05em;">Revenue This Month</span>
            </div>
            <div id="rt-kpi-revenue" style="font-size:2rem;font-weight:800;color:#1e293b;"><?= $fmt($m['revenue_month'] ?? 0) ?></div>
            <div style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">Booking value booked</div>
        </div>
    </div>

    <!-- Collections Today -->
    <div class="aps-cp-card" style="border-left:4px solid #f59e0b;transition:transform 0.15s;">
        <div class="aps-cp-card-body" style="padding:18px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#fffbeb;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-hand-holding-usd" style="color:#f59e0b;font-size:1rem;"></i>
                </div>
                <span style="font-size:0.75rem;font-weight:600;text-transform:uppercase;color:#f59e0b;letter-spacing:0.05em;">Collections Today</span>
            </div>
            <div id="rt-kpi-collections" style="font-size:2rem;font-weight:800;color:#1e293b;"><?= $fmt($m['collections_today'] ?? 0) ?></div>
            <div style="font-size:0.7rem;color:#94a3b8;margin-top:2px;">Cash receipts collected</div>
        </div>
    </div>
</div>

<!-- ROW 2 — Charts (2×2 grid) -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

    <!-- Chart 1: Leads over last 7 days (Line) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">
            <span><i class="fas fa-chart-area" style="color:#3b82f6;margin-right:6px;"></i>Leads — Last 7 Days</span>
            <span class="badge bg-primary-subtle text-primary-emphasis" style="font-size:0.65rem;">LINE</span>
        </div>
        <div class="aps-cp-card-body" style="padding:16px;height:260px;">
            <canvas id="rtChartLeads7d"></canvas>
        </div>
    </div>

    <!-- Chart 2: Revenue by Colony (Bar) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">
            <span><i class="fas fa-chart-bar" style="color:#10b981;margin-right:6px;"></i>Revenue by Colony</span>
            <span class="badge bg-success-subtle text-success-emphasis" style="font-size:0.65rem;">BAR</span>
        </div>
        <div class="aps-cp-card-body" style="padding:16px;height:260px;">
            <canvas id="rtChartRevenueColony"></canvas>
        </div>
    </div>

    <!-- Chart 3: Lead Sources (Doughnut) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">
            <span><i class="fas fa-chart-pie" style="color:#14b8a6;margin-right:6px;"></i>Lead Sources Breakdown</span>
            <span class="badge bg-purple-subtle text-purple-emphasis" style="font-size:0.65rem;">DOUGHNUT</span>
        </div>
        <div class="aps-cp-card-body" style="padding:16px;height:260px;">
            <canvas id="rtChartLeadSources"></canvas>
        </div>
    </div>

    <!-- Chart 4: Booking Trend 30 days (Line) -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">
            <span><i class="fas fa-chart-line" style="color:#f59e0b;margin-right:6px;"></i>Booking Trend — Last 30 Days</span>
            <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:0.65rem;">LINE</span>
        </div>
        <div class="aps-cp-card-body" style="padding:16px;height:260px;">
            <canvas id="rtChartBookings30d"></canvas>
        </div>
    </div>
</div>

<!-- ROW 3 — Live Activity Feed -->
<div class="aps-cp-card" style="margin-bottom:28px;">
    <div class="aps-cp-card-header" style="font-weight:700;">
        <span><i class="fas fa-stream" style="color:#6366f1;margin-right:6px;"></i>Live Activity Feed</span>
        <span class="badge bg-light text-dark" style="font-size:0.65rem;" id="rt-activity-count"><?= count($acts) ?> events</span>
    </div>
    <div class="aps-cp-card-body" style="padding:16px;max-height:340px;overflow-y:auto;" id="rt-activity-list">
        <?php if (empty($acts)): ?>
            <p style="color:#94a3b8;text-align:center;padding:24px 0;">No recent activity.</p>
        <?php else: ?>
            <?php foreach ($acts as $act): ?>
                <?php
                    $actType = htmlspecialchars($act['type'] ?? 'event');
                    $actDesc = htmlspecialchars($act['description'] ?? '-');
                    $actTime = htmlspecialchars($act['created_at'] ?? '');
                    $actIcon = $act['icon'] ?? 'fa-circle';
                    $actColor = $act['color'] ?? '#64748b';
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="width:34px;height:34px;border-radius:8px;background:<?= $actColor ?>15;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas <?= $actIcon ?>" style="color:<?= $actColor ?>;font-size:0.8rem;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:0.82rem;font-weight:600;color:#1e293b;text-transform:capitalize;"><?= $actType ?></div>
                        <div style="font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $actDesc ?></div>
                    </div>
                    <div style="font-size:0.7rem;color:#94a3b8;white-space:nowrap;"><?= $actTime ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function() {
    'use strict';

    /* ════════════════════════ Chart Instances ════════════════════════ */
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

    function fmt(n) { return '₹' + Number(n).toLocaleString('en-IN', {maximumFractionDigits:0}); }

    /* ════════════════════════ Chart Renderers ════════════════════════ */

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

    /* ════════════════════════ Helpers ════════════════════════ */

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

    /* ════════════════════════ KPI Updater ════════════════════════ */

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

    /* ════════════════════════ Activity Feed Updater ════════════════════════ */

    function updateActivityFeed(activities) {
        var list = document.getElementById('rt-activity-list');
        var count = document.getElementById('rt-activity-count');
        if (!list) return;

        if (!activities || activities.length === 0) {
            list.innerHTML = '<p style="color:#94a3b8;text-align:center;padding:24px 0;">No recent activity.</p>';
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
            html += '<div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9;">'
                + '<div style="width:34px;height:34px;border-radius:8px;background:'+color+'15;display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
                + '<i class="fas '+icon+'" style="color:'+color+';font-size:0.8rem;"></i></div>'
                + '<div style="flex:1;min-width:0;"><div style="font-size:0.82rem;font-weight:600;color:#1e293b;text-transform:capitalize;">'+type+'</div>'
                + '<div style="font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+desc+'</div></div>'
                + '<div style="font-size:0.7rem;color:#94a3b8;white-space:nowrap;">'+time+'</div></div>';
        });
        list.innerHTML = html;
        if (count) count.textContent = activities.length + ' events';
    }

    /* ════════════════════════ Data Fetchers ════════════════════════ */

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

    /* ════════════════════════ WebSocket ════════════════════════ */

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
                    // heartbeat / connection ack — ignore
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
            el.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle;color:#10b981;"></i> <span style="color:#10b981;font-weight:600;">Live</span>';
        } else {
            el.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle;color:#94a3b8;"></i> <span style="color:#94a3b8;">Reconnecting&hellip;</span>';
        }
    }

    // Heartbeat every 30s
    setInterval(function() {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
        }
    }, 30000);

    /* ════════════════════════ Init ════════════════════════ */

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
