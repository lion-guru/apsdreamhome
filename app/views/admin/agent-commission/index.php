<?php
$topAgents = $topAgents ?? [];
$recentCommissions = $recentCommissions ?? [];
$agentListings = $agentListings ?? [];
$allAgents = $allAgents ?? [];
$allProperties = $allProperties ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.ac-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.ac-card h5 { color: #f8fafc; margin-bottom: 16px; font-size: 15px; }
.ac-stat { background: linear-gradient(135deg, #1e3a5f, #0f172a); border-radius: 10px; padding: 18px 16px; color: white; text-align: center; }
.ac-stat .num { font-size: 28px; font-weight: 700; }
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
.ac-badge-inactive { background: #64748b20; color: #94a3b8; border: 1px solid #64748b40; }
.ac-badge-commission_paid { background: #8b5cf620; color: #8b5cf6; border: 1px solid #8b5cf640; }
.ac-form select, .ac-form input { background: #0f172a; border: 1px solid #475569; color: #f8fafc; padding: 8px 12px; border-radius: 6px; width: 100%; font-size: 13px; }
.ac-form label { color: #94a3b8; font-size: 12px; margin-bottom: 4px; display: block; }
.ac-rank { display: inline-flex; align-items: center; gap: 4px; }
.ac-rank-1 { color: #fbbf24; }
.ac-rank-2 { color: #94a3b8; }
.ac-rank-3 { color: #cd7f32; }
.ac-money { font-weight: 600; color: #10b981; }
.ac-empty { color: #64748b; text-align: center; padding: 30px; font-size: 13px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="style-76816"><i class="fas fa-handshake me-2"></i>Agent Commission Dashboard</h4>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-2">
            <div class="ac-stat">
                <div class="num"><?= (int)($totalAgents ?? 0) ?></div>
                <div class="lbl">Total Agents</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ac-stat" class="style-68340">
                <div class="num"><?= (int)($activeListings ?? 0) ?></div>
                <div class="lbl">Active Listings</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ac-stat" class="style-1293">
                <div class="num">₹<?= number_format((float)($totalCommission ?? 0)) ?></div>
                <div class="lbl">Total Commission Paid</div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="ac-stat" class="style-41761">
                <div class="num"><?= (int)($totalSales ?? 0) ?></div>
                <div class="lbl">Total Sales</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Agents -->
        <div class="col-md-7">
            <div class="ac-card">
                <h5><i class="fas fa-trophy me-2" class="style-60246"></i>Top Agents by Commission</h5>
                <?php if (!empty($topAgents)): ?>
                <div class="style-10754">
                    <table class="ac-table">
                        <thead>
                            <tr>
                                <th class="style-38862">#</th>
                                <th>Agent</th>
                                <th>Email</th>
                                <th class="style-64867">Sales</th>
                                <th class="style-64867">Total Earned</th>
                                <th class="style-8021"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topAgents as $i => $a): ?>
                            <tr>
                                <td>
                                    <?php if ($i == 0): ?>
                                        <span class="ac-rank ac-rank-1"><i class="fas fa-crown"></i></span>
                                    <?php elseif ($i == 1): ?>
                                        <span class="ac-rank ac-rank-2"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($i == 2): ?>
                                        <span class="ac-rank ac-rank-3"><i class="fas fa-medal"></i></span>
                                    <?php else: ?>
                                        <?= $i + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td class="style-24039"><?= htmlspecialchars($a['name'] ?? 'N/A') ?></td>
                                <td class="style-27277"><?= htmlspecialchars($a['email'] ?? '') ?></td>
                                <td class="style-64867"><?= (int)$a['sale_count'] ?></td>
                                <td class="style-64867"><span class="ac-money">₹<?= number_format((float)$a['total_earned']) ?></span></td>
                                <td><a href="<?= $base ?>/admin/agent-commission/agent/<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-primary" class="style-10792">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="ac-empty">No agents with commissions yet.</div>
                <?php endif; ?>
            </div>

            <!-- Agent Listings -->
            <div class="ac-card">
                <h5><i class="fas fa-list me-2" class="style-75937"></i>Agent Property Listings</h5>
                <?php if (!empty($agentListings)): ?>
                <div class="style-10754">
                    <table class="ac-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Agent</th>
                                <th>Commission %</th>
                                <th>Status</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agentListings as $al): ?>
                            <tr>
                                <td>
                                    <div class="style-24039"><?= htmlspecialchars($al['property_name'] ?? 'N/A') ?></div>
                                    <div class="style-63117"><?= htmlspecialchars($al['property_location'] ?? '') ?></div>
                                </td>
                                <td><?= htmlspecialchars($al['agent_name'] ?? 'N/A') ?></td>
                                <td><?= (float)$al['commission_pct'] ?>%</td>
                                <td><span class="ac-badge ac-badge-<?= $al['status'] ?>"><?= ucfirst($al['status']) ?></span></td>
                                <td class="style-4937"><?= date('d M Y', strtotime($al['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="ac-empty">No agent listings yet. Assign an agent below.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-5">
            <!-- Recent Commissions -->
            <div class="ac-card">
                <h5><i class="fas fa-coins me-2" class="style-54781"></i>Recent Commissions</h5>
                <?php if (!empty($recentCommissions)): ?>
                <div class="style-32146">
                    <?php foreach ($recentCommissions as $rc): ?>
                    <div class="style-86554">
                        <div>
                            <div class="style-91674"><?= htmlspecialchars($rc['agent_name'] ?? 'Unknown') ?></div>
                            <div class="style-63117"><?= date('d M Y, h:i A', strtotime($rc['created_at'])) ?></div>
                        </div>
                        <div class="style-64867">
                            <div class="ac-money">₹<?= number_format((float)$rc['amount']) ?></div>
                            <span class="ac-badge ac-badge-<?= $rc['status'] ?>"><?= ucfirst($rc['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <div class="ac-empty">No commissions recorded yet.</div>
                <?php endif; ?>
            </div>

            <!-- Assign Agent Form -->
            <div class="ac-card">
                <h5><i class="fas fa-user-plus me-2" class="style-22437"></i>Assign Agent to Property</h5>
                <form method="POST" action="<?= $base ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">/admin/agent-commission/assign" class="ac-form">
    <?php echo CSRFProtection::csrfField(); ?>
                    <div class="mb-3">
                        <label>Select Agent</label>
                        <select name="agent_user_id" required>
                            <option value="">-- Choose Agent --</option>
                            <?php foreach ($allAgents as $ag): ?>
                            <option value="<?= (int)$ag['id'] ?>"><?= htmlspecialchars($ag['name'] ?? '') ?> (<?= htmlspecialchars($ag['email'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Select Property</label>
                        <select name="property_id" required>
                            <option value="">-- Choose Property --</option>
                            <?php foreach ($allProperties as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name'] ?? '') ?> — <?= htmlspecialchars($p['location'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Commission %</label>
                        <input type="number" name="commission_pct" step="0.01" min="0" max="100" value="5.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-link me-1"></i>Assign Agent
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
