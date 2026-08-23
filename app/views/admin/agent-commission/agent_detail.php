<?php
$agent = $agent ?? [];
$commissions = $commissions ?? [];
$listings = $listings ?? [];
$totalEarned = $totalEarned ?? 0;
$totalListings = $totalListings ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.ac-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.ac-card h5 { color: #f8fafc; margin-bottom: 16px; font-size: 15px; }
.ac-stat { background: linear-gradient(135deg, #1e3a5f, #0f172a); border-radius: 10px; padding: 18px 16px; color: white; text-align: center; }
.ac-stat .num { font-size: 26px; font-weight: 700; }
.ac-stat .lbl { font-size: 12px; opacity: 0.8; margin-top: 4px; }
.ac-table { width: 100%; border-collapse: collapse; color: #f8fafc; }
.ac-table th { background: #0f172a; padding: 10px 12px; text-align: left; font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
.ac-table td { padding: 10px 12px; border-bottom: 1px solid #334155; font-size: 13px; }
.ac-table tr:hover td { background: rgba(255,255,255,0.03); }
.ac-badge { padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; }
.ac-badge-pending { background: #f59e0b20; color: #f59e0b; border: 1px solid #f59e0b40; }
.ac-badge-approved { background: #10b98120; color: #10b981; border: 1px solid #10b98140; }
.ac-badge-paid { background: #3b82f620; color: #3b82f6; border: 1px solid #3b82f640; }
.ac-badge-cancelled { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }
.ac-badge-active { background: #10b98120; color: #10b981; border: 1px solid #10b98140; }
.ac-badge-commission_paid { background: #8b5cf620; color: #8b5cf6; border: 1px solid #8b5cf640; }
.ac-money { font-weight: 600; color: #10b981; }
.ac-empty { color: #64748b; text-align: center; padding: 30px; font-size: 13px; }
.ac-avatar { width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; color: #fff; }
</style>

<div class="container-fluid py-4">
    <!-- Back link -->
    <a href="<?= $base ?>/admin/agent-commission" class="style-83847">
        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
    </a>

    <!-- Agent Header -->
    <div class="ac-card mt-3">
        <div class="d-flex align-items-center gap-4">
            <div class="ac-avatar"><?= strtoupper(substr($agent['name'] ?? 'A', 0, 1)) ?></div>
            <div>
                <h4 class="style-53765"><?= htmlspecialchars($agent['name'] ?? 'N/A') ?></h4>
                <div class="style-45569"><?= htmlspecialchars($agent['email'] ?? '') ?></div>
                <div class="style-35629">
                    <span class="ac-badge ac-badge-active"><?= ucfirst($agent['role'] ?? 'agent') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-2">
            <div class="ac-stat">
                <div class="num ac-money">₹<?= number_format((float)$totalEarned) ?></div>
                <div class="lbl">Total Earned</div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="ac-stat style-32886">
                <div class="num"><?= count($commissions) ?></div>
                <div class="lbl">Total Sales</div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="ac-stat style-21945">
                <div class="num"><?= $totalListings ?></div>
                <div class="lbl">Active Listings</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Commission History -->
        <div class="col-md-7">
            <div class="ac-card">
                <h5><i class="fas fa-history me-2 style-54781"></i>Commission History</h5>
                <?php if (!empty($commissions)): ?>
                <div class="style-10754">
                    <table class="ac-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($commissions as $c): ?>
                            <tr>
                                <td>#<?= (int)$c['id'] ?></td>
                                <td><span class="ac-money">₹<?= number_format((float)$c['amount']) ?></span></td>
                                <td><span class="ac-badge ac-badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                                <td class="style-4937"><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="ac-empty">No commission records for this agent.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Agent Listings -->
        <div class="col-md-5">
            <div class="ac-card">
                <h5><i class="fas fa-building me-2 style-75937"></i>Assigned Properties</h5>
                <?php if (!empty($listings)): ?>
                <div class="style-43942">
                    <?php foreach ($listings as $l): ?>
                    <div class="style-88188">
                        <div class="style-91674"><?= htmlspecialchars($l['property_name'] ?? 'N/A') ?></div>
                        <div class="style-63117"><?= htmlspecialchars($l['property_location'] ?? '') ?></div>
                        <div class="style-27644">
                            <span class="ac-badge ac-badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
                            <span class="style-4937"><?= (float)$l['commission_pct'] ?>% commission</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div class="ac-empty">No properties assigned to this agent.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
