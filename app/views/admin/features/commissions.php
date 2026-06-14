<?php
$page_title = $page_title ?? 'Commission Engine';
$page_heading = $page_heading ?? 'Commission Engine';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-percentage me-2"></i>Commission Engine</h1>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ar">Agent Rates</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hp">Hybrid Plans</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#fs">Farmer Structures</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mr">MLM Ranks</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="ar">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Rule Type</th><th>Name</th><th>Output</th><th>Priority</th></tr></thead>
          <tbody>
            <?php if (empty($agentRates)): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No rules</td></tr>
            <?php else: foreach ($agentRates as $r): ?>
              <tr>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['rule_type'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($r['rule_name'] ?? '') ?></td>
                <td>₹<?= number_format((float)($r['output_amount'] ?? 0), 2) ?></td>
                <td><?= htmlspecialchars($r['priority'] ?? '') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="hp">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Agent ID</th><th>Fixed</th><th>Variable %</th><th>Threshold</th><th>Valid From</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($hybridPlans)): ?>
              <tr><td colspan="6" class="text-center py-3 text-muted">No plans</td></tr>
            <?php else: foreach ($hybridPlans as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['agent_id'] ?? '') ?></td>
                <td>₹<?= number_format((float)($p['fixed_amount'] ?? 0), 0) ?></td>
                <td><?= htmlspecialchars($p['variable_rate'] ?? '') ?>%</td>
                <td>₹<?= number_format((float)($p['sales_threshold'] ?? 0), 0) ?></td>
                <td><small><?= htmlspecialchars($p['valid_from'] ?? '') ?></small></td>
                <td><span class="badge bg-<?= ($p['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($p['status'] ?? '') ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="fs">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Tier</th><th>Base %</th><th>Bonus</th><th>Min Sales</th></tr></thead>
          <tbody>
            <?php if (empty($farmerStructures)): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No structures</td></tr>
            <?php else: foreach ($farmerStructures as $f): ?>
              <tr>
                <td><span class="badge bg-info"><?= htmlspecialchars($f['tier'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($f['base_rate'] ?? '') ?>%</td>
                <td>₹<?= number_format((float)($f['bonus_rate'] ?? 0), 0) ?></td>
                <td><?= htmlspecialchars($f['min_sales'] ?? 0) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>

    <div class="tab-pane fade" id="mr">
      <div class="card shadow-sm"><div class="card-body p-0">
        <div class="table-responsive"><table class="table mb-0">
          <thead class="table-light"><tr><th>Rank</th><th>Min Downline</th><th>Commission %</th><th>Bonus</th><th>Perks</th></tr></thead>
          <tbody>
            <?php if (empty($mlmRanks)): ?>
              <tr><td colspan="5" class="text-center py-3 text-muted">No ranks</td></tr>
            <?php else: foreach ($mlmRanks as $m): ?>
              <tr>
                <td><strong><?= htmlspecialchars($m['rank'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($m['min_downline'] ?? 0) ?></td>
                <td><?= htmlspecialchars($m['commission_pct'] ?? '') ?>%</td>
                <td>₹<?= number_format((float)($m['bonus_amount'] ?? 0), 0) ?></td>
                <td><small><?= htmlspecialchars(substr($m['perks'] ?? '', 0, 60)) ?></small></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table></div>
      </div></div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/admin/layouts/admin.php';
