ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-coins"></i> Commission Management System</h2>
                <div>
                    <a href="<?= BASE_URL ?>/admin/commission/agent-rates" class="btn btn-sm btn-outline-primary"><i class="fas fa-dollar-sign"></i> Agent Rates</a>
                    <a href="<?= BASE_URL ?>/admin/commission/associate/structure" class="btn btn-sm btn-outline-success"><i class="fas fa-layer-group"></i> Structure</a>
                    <a href="<?= BASE_URL ?>/admin/commission/bonuses" class="btn btn-sm btn-outline-warning"><i class="fas fa-gift"></i> Bonuses</a>
                    <a href="<?= BASE_URL ?>/admin/commission/mlm/analytics" class="btn btn-sm btn-outline-info"><i class="fas fa-chart-bar"></i> MLM Analytics</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h6>Agent Rates</h6>
                    <h3><?= (int)($stats['agent_rates_count'] ?? 0) ?></h3>
                    <small>Commission rate cards</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h6>Structure Levels</h6>
                    <h3><?= count($stats['structure_levels'] ?? []) ?></h3>
                    <small>Active levels</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <h6>Pending Comm.</h6>
                    <h3>&#8377;<?= number_format((float)($stats['calc_stats']['pending_total'] ?? 0)) ?></h3>
                    <small>Associate pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h6>MLM Levels</h6>
                    <h3><?= (int)($stats['mlm_levels_count'] ?? 0) ?></h3>
                    <small>Commission levels</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <h6>MLM Records</h6>
                    <h3>&#8377;<?= number_format((float)($stats['mlm_records_stats']['total'] ?? 0)) ?></h3>
                    <small><?= (int)($stats['mlm_records_stats']['c'] ?? 0) ?> records</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h6>MLM Analytics</h6>
                    <h3>&#8377;<?= number_format((float)($stats['mlm_analytics_stats']['earned'] ?? 0)) ?></h3>
                    <small>Total earned</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body text-center">
                    <h6>Revenue Comm.</h6>
                    <h3>&#8377;<?= number_format((float)($stats['revenue_stats']['comm'] ?? 0)) ?></h3>
                    <small>Daily revenue share</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card aps-cp-card" class="style-3150">
                <div class="card-body text-center">
                    <h6>Telecaller</h6>
                    <h3>&#8377;<?= number_format((float)($stats['tc_comm_stats']['pending'] ?? 0)) ?></h3>
                    <small>Pending payouts</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-dollar-sign"></i> Agent Commission Rates</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead><tr><th>Range (sqft)</th><th>Per sqft</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($stats['agent_rates'] ?? [])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="fas fa-dollar-sign fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No agent rates found</h5>
                                    <p class="text-muted mb-3">Commission rate cards for agents have not been configured yet.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach (array_slice($stats['agent_rates'] ?? [], 0, 5) as $r): ?>
                            <tr>
                                <td><?= (int)$r['min_sqft'] ?>-<?= (int)$r['max_sqft'] ?></td>
                                <td>&#8377;<?= number_format((float)$r['commission_per_sqft'],2) ?></td>
                                <td><span class="badge bg-<?= $r['status']=='active'?'success':'secondary' ?>"><?= $r['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
                <div class="card-footer"><a href="<?= BASE_URL ?>/admin/commission/agent-rates" class="btn btn-sm btn-primary">Manage Rates</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-layer-group"></i> Associate Structure</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead><tr><th>Level</th><th>Name</th><th>%</th></tr></thead>
                        <tbody>
                            <?php if (empty($stats['structure_levels'] ?? [])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No structure levels found</h5>
                                    <p class="text-muted mb-3">Associate hierarchy levels have not been set up yet.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach (array_slice($stats['structure_levels'] ?? [], 0, 5) as $l): ?>
                            <tr>
                                <td><?= (int)$l['level_number'] ?></td>
                                <td><?= htmlspecialchars($l['level_name']) ?></td>
                                <td><?= (float)$l['commission_percentage'] ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
                <div class="card-footer"><a href="<?= BASE_URL ?>/admin/commission/associate/structure" class="btn btn-sm btn-success">Manage Structure</a></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-phone"></i> Telecaller Rules</div>
                <div class="card-body p-0">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <thead><tr><th>Rule</th><th>Type</th><th>Amount</th></tr></thead>
                        <tbody>
                            <?php if (empty($tc_rules ?? [])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <i class="fas fa-phone fa-3x text-muted mb-3" class="style-82835"></i>
                                    <h5 class="text-muted">No telecaller rules found</h5>
                                    <p class="text-muted mb-3">Telecaller commission rules have not been defined yet.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach (($tc_rules ?? []) as $r):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($r['rule_name'] ?? '') ?></td>
                                <td><span class="badge bg-info"><?= $r['commission_type'] ?? '' ?></span></td>
                                <td>&#8377;<?= number_format((float)($r['amount'] ?? 0),2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
                <div class="card-footer"><a href="<?= BASE_URL ?>/admin/commission/telecaller/rules" class="btn btn-sm" class="style-3150">Manage Rules</a></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header"><i class="fas fa-link"></i> Quick Access</div>
                <div class="card-body aps-cp-card-body">
                    <div class="row text-center">
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/associate/calculations" class="btn btn-outline-primary btn-sm w-100">Associate Calculations</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/bonuses" class="btn btn-outline-warning btn-sm w-100">Bonuses</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/commission-calculations" class="btn btn-outline-info btn-sm w-100">Resell Calculations</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/mlm/levels" class="btn btn-outline-success btn-sm w-100">MLM Levels</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/mlm/records" class="btn btn-outline-secondary btn-sm w-100">MLM Records</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/mlm/analytics" class="btn btn-outline-dark btn-sm w-100">MLM Analytics</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/mlm/ledger-legacy" class="btn btn-outline-danger btn-sm w-100">Legacy Ledger</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/revenue/daily" class="btn btn-outline-primary btn-sm w-100">Revenue Daily</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/telecaller/rules" class="btn btn-outline-purple btn-sm w-100" class="style-2592">TC Rules</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/telecaller/commissions" class="btn btn-outline-purple btn-sm w-100" class="style-2592">TC Commissions</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/rules" class="btn btn-outline-info btn-sm w-100">Old Rules</a></div>
                        <div class="col-6 col-md-2 mb-2"><a href="<?= BASE_URL ?>/admin/commission/reports" class="btn btn-outline-info btn-sm w-100">Reports</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
