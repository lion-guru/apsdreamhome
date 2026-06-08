<?php
/**
 * Unified ERP Overview Dashboard
 * Shows ALL 5 modules on one page: Land, Sales, Money, MLM, Backoffice
 * APS Dream Home — ERP Home
 */

$s = $stats ?? [];
$recent = $recent_activity ?? [];
$cashFlow = $cash_flow_chart ?? [];
$pipeline = $lead_pipeline_chart ?? [];
$updated = $updated_at ?? date('d M Y, h:i A');

$fmt = fn($v) => '₹' . number_format((float)$v, 0, '.', ',');
?>

<!-- Title Bar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-size:1.6rem;font-weight:700;color:#1e293b;">ERP Overview — APS Dream Home</h1>
        <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Last updated: <?= htmlspecialchars($updated) ?></p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= BASE_URL ?>/admin/sales/dashboard" class="btn btn-sm btn-outline-primary">Sales Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/finance/dashboard" class="btn btn-sm btn-outline-success">Finance Dashboard</a>
    </div>
</div>

<!-- ROW 1 — 5 Module KPI Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:28px;">

    <!-- Module 1: Land -->
    <div class="aps-cp-card" style="border-left:4px solid #10b981;">
        <div class="aps-cp-card-body" style="padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:1.4rem;">&#127968;</span>
                <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;color:#10b981;letter-spacing:0.05em;">Module 1: Land</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['land_active_leads'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Active Leads</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['land_acquisitions'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Acquisitions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module 2: Sales -->
    <div class="aps-cp-card" style="border-left:4px solid #3b82f6;">
        <div class="aps-cp-card-body" style="padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:1.4rem;">&#128203;</span>
                <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;color:#3b82f6;letter-spacing:0.05em;">Module 2: Sales</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['sales_active_bookings'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Active Bookings</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.2rem;font-weight:700;color:#1e293b;"><?= $fmt($s['sales_booking_value'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Total Value</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module 3: Money -->
    <div class="aps-cp-card" style="border-left:4px solid #8b5cf6;">
        <div class="aps-cp-card-body" style="padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:1.4rem;">&#128176;</span>
                <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;color:#8b5cf6;letter-spacing:0.05em;">Module 3: Money</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <div style="font-size:1.2rem;font-weight:700;color:#10b981;"><?= $fmt($s['money_today_collections'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Today Collections</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.2rem;font-weight:700;color:#ef4444;"><?= $fmt($s['money_today_payments'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Today Payments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module 4: MLM -->
    <div class="aps-cp-card" style="border-left:4px solid #f59e0b;">
        <div class="aps-cp-card-body" style="padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:1.4rem;">&#127942;</span>
                <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;color:#f59e0b;letter-spacing:0.05em;">Module 4: MLM</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['mlm_commissions_paid'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Commissions Paid</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.5rem;font-weight:700;color:#ef4444;"><?= (int)($s['mlm_pending_payouts'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Pending Payouts</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module 5: Backoffice -->
    <div class="aps-cp-card" style="border-left:4px solid #ef4444;">
        <div class="aps-cp-card-body" style="padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                <span style="font-size:1.4rem;">&#128188;</span>
                <span style="font-size:0.8rem;font-weight:600;text-transform:uppercase;color:#ef4444;letter-spacing:0.05em;">Module 5: Backoffice</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <div>
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['backoffice_active_leads'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Active Leads</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.5rem;font-weight:700;color:#1e293b;"><?= (int)($s['backoffice_present_today'] ?? 0) ?></div>
                    <div style="font-size:0.75rem;color:#64748b;">Present Today</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EMI Dunning Alert Banner (Phase 30) -->
<?php if (($s['emi_overdue_count'] ?? 0) > 0): ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <div style="background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:16px 20px;text-align:center;">
        <div style="font-size:1.8rem;font-weight:700;color:#dc2626;"><?= (int)($s['emi_overdue_count'] ?? 0) ?></div>
        <div style="font-size:0.75rem;color:#991b1b;font-weight:600;">OVERDUE INSTALLMENTS</div>
        <a href="<?= BASE_URL ?>/admin/finance/penalties" style="font-size:0.7rem;color:#dc2626;text-decoration:underline;">View & Apply Penalties &rarr;</a>
    </div>
    <div style="background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:16px 20px;text-align:center;">
        <div style="font-size:1.8rem;font-weight:700;color:#dc2626;"><?= $fmt($s['emi_overdue_amount'] ?? 0) ?></div>
        <div style="font-size:0.75rem;color:#991b1b;font-weight:600;">OVERDUE AMOUNT</div>
    </div>
    <div style="background:#fff7ed;border:2px solid #fed7aa;border-radius:12px;padding:16px 20px;text-align:center;">
        <div style="font-size:1.8rem;font-weight:700;color:#ea580c;"><?= $fmt($s['emi_total_penalties'] ?? 0) ?></div>
        <div style="font-size:0.75rem;color:#9a3412;font-weight:600;">PENALTIES ACCRUED</div>
    </div>
    <div style="background:#faf5ff;border:2px solid #c4b5fd;border-radius:12px;padding:16px 20px;text-align:center;">
        <div style="font-size:1.8rem;font-weight:700;color:#7c3aed;"><?= (int)($s['emi_defaulted_count'] ?? 0) ?></div>
        <div style="font-size:0.75rem;color:#5b21b6;font-weight:600;">DEFAULTED BOOKINGS</div>
    </div>
</div>
<?php endif; ?>

<!-- ROW 2 — Quick Actions + Recent Activity -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

    <!-- LEFT: 5 Module Quick-Access Cards -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">Quick Actions</div>
        <div class="aps-cp-card-body" style="padding:16px;">
            <div style="display:flex;flex-direction:column;gap:10px;">
                <a href="<?= BASE_URL ?>/admin/land-inventory/leads" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#ecfdf5;color:#065f46;text-decoration:none;transition:transform 0.15s;">
                    <span style="font-size:1.3rem;">&#127968;</span>
                    <div>
                        <div style="font-weight:600;">Land Inventory</div>
                        <div style="font-size:0.75rem;opacity:0.8;">Manage land leads, acquisitions & mapping</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/sales/bookings" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#eff6ff;color:#1e40af;text-decoration:none;transition:transform 0.15s;">
                    <span style="font-size:1.3rem;">&#128203;</span>
                    <div>
                        <div style="font-weight:600;">Sales Bookings</div>
                        <div style="font-size:0.75rem;opacity:0.8;">View bookings, EMI schedule & commissions</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/finance/penalties" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#fef2f2;color:#991b1b;text-decoration:none;transition:transform 0.15s;<?php if (($s['emi_overdue_count'] ?? 0) > 0) echo 'border:2px solid #fecaca;'; ?>">
                    <span style="font-size:1.3rem;">&#9888;</span>
                    <div>
                        <div style="font-weight:600;">EMI Dunning & Penalties</div>
                        <div style="font-size:0.75rem;opacity:0.8;"><?= ($s['emi_overdue_count'] ?? 0) ?> overdue installments</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/finance/cash-book" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#f5f3ff;color:#5b21b6;text-decoration:none;transition:transform 0.15s;">
                    <span style="font-size:1.3rem;">&#128176;</span>
                    <div>
                        <div style="font-weight:600;">Finance Hub</div>
                        <div style="font-size:0.75rem;opacity:0.8;">Cash book, cheques, TDS, GST & expenses</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/mlm/commissions" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#fffbeb;color:#92400e;text-decoration:none;transition:transform 0.15s;">
                    <span style="font-size:1.3rem;">&#127942;</span>
                    <div>
                        <div style="font-weight:600;">MLM Network</div>
                        <div style="font-size:0.75rem;opacity:0.8;">Commissions, payouts & network tree</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/backoffice" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:10px;background:#fef2f2;color:#991b1b;text-decoration:none;transition:transform 0.15s;">
                    <span style="font-size:1.3rem;">&#128188;</span>
                    <div>
                        <div style="font-weight:600;">Backoffice Ops</div>
                        <div style="font-size:0.75rem;opacity:0.8;">Attendance, leaves, payslips & operations</div>
                    </div>
                    <span style="margin-left:auto;font-size:0.8rem;opacity:0.6;">&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- RIGHT: Recent Activity Feed -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">Recent Activity</div>
        <div class="aps-cp-card-body" style="padding:16px;max-height:380px;overflow-y:auto;">
            <?php if (empty($recent)): ?>
                <p style="color:#94a3b8;text-align:center;padding:20px 0;">No recent activity found.</p>
            <?php else: ?>
                <?php foreach ($recent as $item): ?>
                    <?php
                        $src = $item['source'] ?? 'operation';
                        $type = ucfirst(htmlspecialchars($item['type'] ?? '-'));
                        $desc = htmlspecialchars($item['description'] ?? '-');
                        $date = htmlspecialchars($item['activity_date'] ?? '');
                        $st = htmlspecialchars($item['status'] ?? '');
                        $badge = $src === 'finance' ? 'bg-purple-subtle text-purple-emphasis' : 'bg-success-subtle text-success-emphasis';
                    ?>
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                        <span class="badge <?= $badge ?>" style="font-size:0.65rem;white-space:nowrap;"><?= $src === 'finance' ? 'FIN' : 'OPS' ?></span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:0.82rem;color:#1e293b;font-weight:500;"><?= $type ?></div>
                            <div style="font-size:0.75rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $desc ?></div>
                        </div>
                        <div style="font-size:0.7rem;color:#94a3b8;white-space:nowrap;"><?= $date ?></div>
                        <?php if ($st): ?>
                            <span class="badge bg-light text-dark" style="font-size:0.6rem;"><?= $st ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ROW 3 — Charts -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

    <!-- LEFT: Cash Flow Bar Chart -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">Cash Flow (Last 7 Days)</div>
        <div class="aps-cp-card-body" style="padding:16px;height:280px;">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>

    <!-- RIGHT: Lead Pipeline Donut -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header" style="font-weight:700;">Lead Pipeline</div>
        <div class="aps-cp-card-body" style="padding:16px;height:280px;">
            <canvas id="leadPipelineChart"></canvas>
        </div>
    </div>
</div>

<!-- ROW 4 — Alerts -->
<?php
$alerts = [];
if (($s['money_bounced_cheques'] ?? 0) > 0) {
    $alerts[] = ['warning', '<strong>Bounced Cheques:</strong> ' . (int)$s['money_bounced_cheques'] . ' bounced cheque(s) require attention.', BASE_URL . '/admin/finance/cheque-register'];
}
if (($s['money_pending_tds'] ?? 0) > 0) {
    $alerts[] = ['info', '<strong>Pending TDS:</strong> ' . (int)$s['money_pending_tds'] . ' TDS deduction(s) pending filing.', BASE_URL . '/admin/finance/tds'];
}
if (($s['mlm_pending_payouts'] ?? 0) > 0) {
    $alerts[] = ['warning', '<strong>Pending Payouts:</strong> ' . (int)$s['mlm_pending_payouts'] . ' MLM payout(s) awaiting processing.', BASE_URL . '/admin/mlm/payouts'];
}
if (($s['backoffice_pending_leaves'] ?? 0) > 0) {
    $alerts[] = ['info', '<strong>Pending Leaves:</strong> ' . (int)$s['backoffice_pending_leaves'] . ' leave request(s) need approval.', BASE_URL . '/admin/backoffice/leaves'];
}
?>
<?php if (!empty($alerts)): ?>
<div style="margin-bottom:28px;">
    <?php foreach ($alerts as [$type, $msg, $url]): ?>
        <div class="alert alert-<?= $type ?> d-flex align-items-center justify-content-between" role="alert" style="margin-bottom:8px;border-radius:10px;">
            <span><?= $msg ?></span>
            <a href="<?= $url ?>" class="btn btn-sm btn-outline-<?= $type ?>">View &rarr;</a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cash Flow Bar Chart
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
                    {
                        label: 'Receipts',
                        data: cashReceipts,
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderRadius: 4
                    },
                    {
                        label: 'Payments',
                        data: cashPayments,
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(v) { return '₹' + v.toLocaleString(); } } }
                }
            }
        });
    }

    // Lead Pipeline Donut Chart
    var pipeData = <?= json_encode($pipeline, JSON_HEX_TAG) ?>;
    var pipeLabels = pipeData.map(function(r) { return r.status || 'Unknown'; });
    var pipeValues = pipeData.map(function(r) { return parseInt(r.cnt || 0); });
    var pipeColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16'];

    if (pipeLabels.length > 0) {
        new Chart(document.getElementById('leadPipelineChart'), {
            type: 'doughnut',
            data: {
                labels: pipeLabels,
                datasets: [{
                    data: pipeValues,
                    backgroundColor: pipeColors.slice(0, pipeLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { padding: 12 } }
                }
            }
        });
    } else {
        document.getElementById('leadPipelineChart').parentElement.innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;">No pipeline data available</div>';
    }
});
</script>
