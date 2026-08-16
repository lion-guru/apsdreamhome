<?php
$s = $stats ?? [];
$recent = $recent_activity ?? [];
$cashFlow = $cash_flow_chart ?? [];
$pipeline = $lead_pipeline_chart ?? [];
$updated = $updated_at ?? date('d M Y, h:i A');
$fmt = fn($v) => '₹' . number_format((float)$v, 0, '.', ',');
?>

<style>
.erp-modern-header{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0d9488 100%);border-radius:16px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden}
.erp-modern-header::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M20 20h20v20H20z'/%3E%3C/g%3E%3C/svg%3E")}
.erp-modern-header .hdr-content{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.erp-modern-header h1{margin:0;font-size:1.5rem;font-weight:800;color:#fff;letter-spacing:-0.3px}
.erp-modern-header p{margin:4px 0 0;font-size:0.85rem;color:rgba(255,255,255,0.7)}
.erp-modern-header .hdr-actions{display:flex;gap:10px}
.erp-modern-header .hdr-actions a{padding:8px 18px;border-radius:10px;font-size:0.82rem;font-weight:600;text-decoration:none;transition:all 0.2s;border:1px solid rgba(255,255,255,0.2);color:#fff;background:rgba(255,255,255,0.1);backdrop-filter:blur(8px)}
.erp-modern-header .hdr-actions a:hover{background:rgba(255,255,255,0.2);transform:translateY(-1px)}

.erp-module-card{background:#fff;border-radius:16px;padding:20px 22px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border:none;position:relative;overflow:hidden;transition:all 0.3s ease}
.erp-module-card:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,0.08)}
.erp-module-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0}
.erp-module-card.mc-land::before{background:linear-gradient(90deg,#10b981,#34d399)}
.erp-module-card.mc-sales::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.erp-module-card.mc-money::before{background:linear-gradient(90deg,#14b8a6,#5eead4)}
.erp-module-card.mc-mlm::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.erp-module-card.mc-backoffice::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.erp-module-card .mc-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.erp-module-card .mc-icon.icon-land{background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#10b981}
.erp-module-card .mc-icon.icon-sales{background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#3b82f6}
.erp-module-card .mc-icon.icon-money{background:linear-gradient(135deg,#f5f3ff,#ede9fe);color:#14b8a6}
.erp-module-card .mc-icon.icon-mlm{background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#f59e0b}
.erp-module-card .mc-icon.icon-backoffice{background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#ef4444}
.erp-module-card .mc-label{font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:12px}
.erp-module-card .mc-stats{display:flex;justify-content:space-between;align-items:flex-end}
.erp-module-card .mc-val{font-size:1.6rem;font-weight:800;color:#1e293b;line-height:1}
.erp-module-card .mc-lbl{font-size:0.72rem;color:#94a3b8;margin-top:2px}

.erp-alert-card{border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;transition:transform 0.2s;margin-bottom:12px}
.erp-alert-card:hover{transform:translateX(4px)}
.erp-alert-card .alert-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.erp-alert-card .alert-content{flex:1;margin:0 16px}
.erp-alert-card .alert-val{font-size:1.4rem;font-weight:800;line-height:1}
.erp-alert-card .alert-lbl{font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px}
.erp-alert-card .alert-link{font-size:0.78rem;font-weight:600;text-decoration:none;white-space:nowrap;padding:6px 14px;border-radius:8px;transition:all 0.2s}

.erp-quick-action{display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:14px;border:1.5px solid #f1f5f9;background:#fff;text-decoration:none;color:inherit;transition:all 0.25s ease}
.erp-quick-action:hover{transform:translateX(6px);box-shadow:0 4px 16px rgba(0,0,0,0.06);border-color:#e2e8f0;background:#fafbfc}
.erp-quick-action .qa-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;transition:transform 0.2s}
.erp-quick-action:hover .qa-icon{transform:scale(1.1)}
.erp-quick-action .qa-text{flex:1}
.erp-quick-action .qa-title{font-weight:600;color:#1e293b;font-size:0.9rem}
.erp-quick-action .qa-desc{font-size:0.75rem;color:#94a3b8;margin-top:1px}
.erp-quick-action .qa-arrow{color:#cbd5e1;font-size:0.8rem;transition:transform 0.2s}
.erp-quick-action:hover .qa-arrow{transform:translateX(4px);color:#94a3b8}

.erp-activity-item{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f1f5f9;transition:background 0.15s}
.erp-activity-item:last-child{border-bottom:none}
.erp-activity-item:hover{background:#f8fafc;border-radius:8px;margin:0 -8px;padding:12px 8px}
.erp-activity-item .ai-badge{padding:4px 10px;border-radius:8px;font-size:0.65rem;font-weight:700;white-space:nowrap;letter-spacing:0.3px}
.erp-activity-item .ai-type{font-size:0.82rem;color:#1e293b;font-weight:600}
.erp-activity-item .ai-desc{font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px}
.erp-activity-item .ai-date{font-size:0.7rem;color:#94a3b8;white-space:nowrap}

.erp-chart-card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);overflow:hidden}
.erp-chart-card .chart-header{padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between}
.erp-chart-card .chart-header h6{margin:0;font-weight:700;color:#1e293b;font-size:0.95rem}
.erp-chart-card .chart-body{padding:20px 24px;height:280px;position:relative}

@media(max-width:991px){
.erp-modern-header .hdr-content{flex-direction:column;align-items:flex-start}
.erp-module-card{margin-bottom:8px}
.erp-alert-card{flex-direction:column;align-items:flex-start;gap:12px}
}
</style>

<!-- Modern Header -->
<div class="erp-modern-header">
    <div class="hdr-content">
        <div>
            <h1><i class="fas fa-cogs me-2"></i>ERP Overview</h1>
            <p>Last updated: <?= htmlspecialchars($updated) ?></p>
        </div>
        <div class="hdr-actions">
            <a href="<?= BASE_URL ?>/admin/sales/dashboard"><i class="fas fa-chart-line me-1"></i> Sales</a>
            <a href="<?= BASE_URL ?>/admin/finance/dashboard"><i class="fas fa-wallet me-1"></i> Finance</a>
            <a href="<?= BASE_URL ?>/admin/mlm/commissions"><i class="fas fa-sitemap me-1"></i> MLM</a>
        </div>
    </div>
</div>

<!-- Module KPI Cards -->
<div class="style-18727">
    <div class="erp-module-card mc-land scroll-reveal" class="style-80893">
        <div class="style-21882">
            <div class="mc-icon icon-land"><i class="fas fa-map-marked-alt"></i></div>
            <div class="mc-label" class="style-2154">Module 1: Land</div>
        </div>
        <div class="mc-stats">
            <div><div class="mc-val"><?= (int)($s['land_active_leads'] ?? 0) ?></div><div class="mc-lbl">Active Leads</div></div>
            <div class="style-59292"><div class="mc-val" class="style-88257"><?= (int)($s['land_acquisitions'] ?? 0) ?></div><div class="mc-lbl">Acquisitions</div></div>
        </div>
    </div>

    <div class="erp-module-card mc-sales scroll-reveal" class="style-2970">
        <div class="style-21882">
            <div class="mc-icon icon-sales"><i class="fas fa-chart-line"></i></div>
            <div class="mc-label" class="style-64047">Module 2: Sales</div>
        </div>
        <div class="mc-stats">
            <div><div class="mc-val"><?= (int)($s['sales_active_bookings'] ?? 0) ?></div><div class="mc-lbl">Active Bookings</div></div>
            <div class="style-59292"><div class="mc-val" class="style-27526"><?= $fmt($s['sales_booking_value'] ?? 0) ?></div><div class="mc-lbl">Total Value</div></div>
        </div>
    </div>

    <div class="erp-module-card mc-money scroll-reveal" class="style-42587">
        <div class="style-21882">
            <div class="mc-icon icon-money"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="mc-label" class="style-92996">Module 3: Money</div>
        </div>
        <div class="mc-stats">
            <div><div class="mc-val" class="style-63303"><?= $fmt($s['money_today_collections'] ?? 0) ?></div><div class="mc-lbl">Today In</div></div>
            <div class="style-59292"><div class="mc-val" class="style-87593"><?= $fmt($s['money_today_payments'] ?? 0) ?></div><div class="mc-lbl">Today Out</div></div>
        </div>
    </div>

    <div class="erp-module-card mc-mlm scroll-reveal" class="style-13180">
        <div class="style-21882">
            <div class="mc-icon icon-mlm"><i class="fas fa-sitemap"></i></div>
            <div class="mc-label" class="style-62735">Module 4: MLM</div>
        </div>
        <div class="mc-stats">
            <div><div class="mc-val"><?= (int)($s['mlm_commissions_paid'] ?? 0) ?></div><div class="mc-lbl">Paid (MTD)</div></div>
            <div class="style-59292"><div class="mc-val" class="style-78822"><?= (int)($s['mlm_pending_payouts'] ?? 0) ?></div><div class="mc-lbl">Pending</div></div>
        </div>
    </div>

    <div class="erp-module-card mc-backoffice scroll-reveal" class="style-68814">
        <div class="style-21882">
            <div class="mc-icon icon-backoffice"><i class="fas fa-building"></i></div>
            <div class="mc-label" class="style-78822">Module 5: Backoffice</div>
        </div>
        <div class="mc-stats">
            <div><div class="mc-val"><?= (int)($s['backoffice_active_leads'] ?? 0) ?></div><div class="mc-lbl">Active Leads</div></div>
            <div class="style-59292"><div class="mc-val"><?= (int)($s['backoffice_present_today'] ?? 0) ?></div><div class="mc-lbl">Present Today</div></div>
        </div>
    </div>
</div>

<!-- EMI Dunning Alerts -->
<?php if (($s['emi_overdue_count'] ?? 0) > 0): ?>
<div class="style-73917">
    <div class="erp-alert-card" class="style-16457">
        <div class="alert-icon" class="style-11364"><i class="fas fa-exclamation-circle"></i></div>
        <div class="alert-content">
            <div class="alert-val" class="style-51061"><?= (int)($s['emi_overdue_count'] ?? 0) ?></div>
            <div class="alert-lbl" class="style-98858">Overdue Installments</div>
        </div>
        <a href="<?= BASE_URL ?>/admin/finance/penalties" class="alert-link" class="style-11364">View <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="erp-alert-card" class="style-16457">
        <div class="alert-icon" class="style-11364"><i class="fas fa-rupee-sign"></i></div>
        <div class="alert-content">
            <div class="alert-val" class="style-51061"><?= $fmt($s['emi_overdue_amount'] ?? 0) ?></div>
            <div class="alert-lbl" class="style-98858">Overdue Amount</div>
        </div>
    </div>
    <div class="erp-alert-card" class="style-14265">
        <div class="alert-icon" class="style-35274"><i class="fas fa-gavel"></i></div>
        <div class="alert-content">
            <div class="alert-val" class="style-91406"><?= $fmt($s['emi_total_penalties'] ?? 0) ?></div>
            <div class="alert-lbl" class="style-94711">Penalties Accrued</div>
        </div>
    </div>
    <div class="erp-alert-card" class="style-53605">
        <div class="alert-icon" class="style-47041"><i class="fas fa-user-slash"></i></div>
        <div class="alert-content">
            <div class="alert-val" class="style-69871"><?= (int)($s['emi_defaulted_count'] ?? 0) ?></div>
            <div class="alert-lbl" class="style-3510">Defaulted Bookings</div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions + Activity -->
<div class="style-69948">
    <!-- Quick Actions -->
    <div class="style-87107">
        <div class="style-52154">
            <h6 class="style-75784"><i class="fas fa-bolt me-2" class="style-62735"></i>Quick Actions</h6>
        </div>
        <div class="style-13726">
            <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="erp-quick-action">
                <div class="qa-icon" class="style-28848"><i class="fas fa-map-marked-alt"></i></div>
                <div class="qa-text"><div class="qa-title">Land Inventory</div><div class="qa-desc">Leads, acquisitions & mapping</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
            <a href="<?= BASE_URL ?>/admin/sales/bookings" class="erp-quick-action">
                <div class="qa-icon" class="style-54968"><i class="fas fa-chart-line"></i></div>
                <div class="qa-text"><div class="qa-title">Sales Bookings</div><div class="qa-desc">Bookings, EMI & commissions</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
            <a href="<?= BASE_URL ?>/admin/finance/penalties" class="erp-quick-action" <?php if (($s['emi_overdue_count'] ?? 0) > 0) echo 'class="style-57175"'; ?>>
                <div class="qa-icon" class="style-97392"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="qa-text"><div class="qa-title">EMI Dunning</div><div class="qa-desc"><?= ($s['emi_overdue_count'] ?? 0) ?> overdue installments</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="erp-quick-action">
                <div class="qa-icon" class="style-56789"><i class="fas fa-wallet"></i></div>
                <div class="qa-text"><div class="qa-title">Finance Hub</div><div class="qa-desc">Cash book, cheques, TDS & GST</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
            <a href="<?= BASE_URL ?>/admin/mlm/commissions" class="erp-quick-action">
                <div class="qa-icon" class="style-14896"><i class="fas fa-sitemap"></i></div>
                <div class="qa-text"><div class="qa-title">MLM Network</div><div class="qa-desc">Commissions, payouts & tree</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
            <a href="<?= BASE_URL ?>/admin/backoffice" class="erp-quick-action">
                <div class="qa-icon" class="style-58592"><i class="fas fa-briefcase"></i></div>
                <div class="qa-text"><div class="qa-title">Backoffice Ops</div><div class="qa-desc">Attendance, leaves & payslips</div></div>
                <i class="fas fa-chevron-right qa-arrow"></i>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="style-87107">
        <div class="style-81894">
            <h6 class="style-75784"><i class="fas fa-clock me-2" class="style-64047"></i>Recent Activity</h6>
            <span class="style-42122">Last 10 entries</span>
        </div>
        <div class="style-24350">
            <?php if (empty($recent)): ?>
                <div class="style-47612">
                    <i class="fas fa-inbox fa-2x mb-2" class="style-91271"></i>
                    <p class="style-15051">No recent activity found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent as $item): ?>
                    <?php
                        $src = $item['source'] ?? 'operation';
                        $type = ucfirst(htmlspecialchars($item['type'] ?? '-'));
                        $desc = htmlspecialchars($item['description'] ?? '-');
                        $date = htmlspecialchars($item['activity_date'] ?? '');
                        $st = htmlspecialchars($item['status'] ?? '');
                    ?>
                    <div class="erp-activity-item">
                        <span class="ai-badge" class="style-64486"><?= $src === 'finance' ? 'FIN' : 'OPS' ?></span>
                        <div class="style-61884">
                            <div class="ai-type"><?= $type ?></div>
                            <div class="ai-desc"><?= $desc ?></div>
                        </div>
                        <div class="ai-date"><?= $date ?></div>
                        <?php if ($st): ?>
                            <span class="style-69175"><?= $st ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="style-69948">
    <div class="erp-chart-card">
        <div class="chart-header">
            <h6><i class="fas fa-chart-bar me-2" class="style-2154"></i>Cash Flow (Last 7 Days)</h6>
        </div>
        <div class="chart-body"><canvas id="cashFlowChart"></canvas></div>
    </div>
    <div class="erp-chart-card">
        <div class="chart-header">
            <h6><i class="fas fa-chart-pie me-2" class="style-92996"></i>Lead Pipeline</h6>
        </div>
        <div class="chart-body"><canvas id="leadPipelineChart"></canvas></div>
    </div>
</div>

<!-- System Alerts -->
<?php
$alerts = [];
if (($s['money_bounced_cheques'] ?? 0) > 0) $alerts[] = ['warning', '<strong>Bounced Cheques:</strong> ' . (int)$s['money_bounced_cheques'] . ' require attention.', BASE_URL . '/admin/finance/cheque-register'];
if (($s['money_pending_tds'] ?? 0) > 0) $alerts[] = ['info', '<strong>Pending TDS:</strong> ' . (int)$s['money_pending_tds'] . ' pending filing.', BASE_URL . '/admin/finance/tds'];
if (($s['mlm_pending_payouts'] ?? 0) > 0) $alerts[] = ['warning', '<strong>Pending Payouts:</strong> ' . (int)$s['mlm_pending_payouts'] . ' MLM payouts awaiting.', BASE_URL . '/admin/mlm/payouts'];
if (($s['backoffice_pending_leaves'] ?? 0) > 0) $alerts[] = ['info', '<strong>Pending Leaves:</strong> ' . (int)$s['backoffice_pending_leaves'] . ' need approval.', BASE_URL . '/admin/backoffice/leaves'];
if (($s['dept_high_priority'] ?? 0) > 0) $alerts[] = ['warning', '<strong>High-Priority Requests:</strong> ' . (int)$s['dept_high_priority'] . ' department requests need urgent attention.', BASE_URL . '/admin/department-requests'];
if (($s['dept_overdue'] ?? 0) > 0) $alerts[] = ['error', '<strong>Overdue Requests:</strong> ' . (int)$s['dept_overdue'] . ' department requests are overdue.', BASE_URL . '/admin/department-requests'];
?>
<?php if (!empty($alerts)): ?>
<div class="style-84720">
    <?php foreach ($alerts as [$type, $msg, $url]): ?>
        <div class="style-98615">
            <span class="style-84233"><?= $msg ?></span>
            <a href="<?= $url ?>" class="style-86806">View <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cashData = <?= json_encode($cashFlow, JSON_HEX_TAG) ?>;
    var cashLabels = cashData.map(function(r) { return r.transaction_date || ''; });
    var cashReceipts = cashData.map(function(r) { return parseFloat(r.receipts || 0); });
    var cashPayments = cashData.map(function(r) { return parseFloat(r.payments || 0); });
    if (cashLabels.length > 0) {
        new Chart(document.getElementById('cashFlowChart'), {
            type: 'bar',
            data: {
                labels: cashLabels,
                datasets: [
                    { label: 'Receipts', data: cashReceipts, backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 6, borderSkipped: false },
                    { label: 'Payments', data: cashPayments, backgroundColor: 'rgba(239,68,68,0.75)', borderRadius: 6, borderSkipped: false }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 12 } } } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: function(v) { return '₹' + v.toLocaleString(); }, font: { size: 11 } } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } }
            }
        });
    }
    var pipeData = <?= json_encode($pipeline, JSON_HEX_TAG) ?>;
    var pipeLabels = pipeData.map(function(r) { return r.status || 'Unknown'; });
    var pipeValues = pipeData.map(function(r) { return parseInt(r.cnt || 0); });
    var pipeColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#14b8a6','#06b6d4','#ec4899','#84cc16'];
    if (pipeLabels.length > 0) {
        new Chart(document.getElementById('leadPipelineChart'), {
            type: 'doughnut',
            data: { labels: pipeLabels, datasets: [{ data: pipeValues, backgroundColor: pipeColors.slice(0, pipeLabels.length), borderWidth: 3, borderColor: '#fff' }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'circle', padding: 14, font: { size: 12 } } } }
            }
        });
    } else {
        document.getElementById('leadPipelineChart').parentElement.innerHTML = '<div class="style-76227"><i class="fas fa-chart-pie me-2"></i>No pipeline data available</div>';
    }
});
</script>
