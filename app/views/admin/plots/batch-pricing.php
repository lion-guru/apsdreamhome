<?php
/** @var array $colonies */
/** @var array $params */
/** @var array $preview */
$colonies = $colonies ?? [];
$params = $params ?? ['colony_id' => 0, 'status' => 'available', 'mode' => 'flat', 'value' => 0];
$preview = $preview ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = $_SESSION['csrf_token'] ?? '';
$statuses = ['available' => 'Available', 'hold' => 'Hold', 'reserved' => 'Reserved', 'booked' => 'Booked', 'sold' => 'Sold', 'all' => 'All statuses'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-tags me-2"></i>Batch Price Revision</h4>
        <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>All Plots</a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/plots/batch-pricing" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Colony *</label>
                    <select name="colony_id" class="form-select" required>
                        <option value="">Select colony</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ((int)$params['colony_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statuses as $k => $v): ?>
                            <option value="<?= $k ?>" <?= ((string)$params['status'] === $k) ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Adjustment</label>
                    <select name="mode" class="form-select">
                        <option value="flat" <?= ((string)$params['mode'] === 'flat') ? 'selected' : '' ?>>+ ₹ per sq.ft</option>
                        <option value="pct" <?= ((string)$params['mode'] === 'pct') ? 'selected' : '' ?>>+ % hike</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Value</label>
                    <input type="number" step="0.01" name="value" class="form-control" value="<?= htmlspecialchars((string)$params['value']) ?>" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-eye me-1"></i>Preview Changes</button>
                </div>
            </form>
            <small class="text-muted">Hike applies to the BASE rate; PLC (corner/park/road) is recomputed per plot so the breakdown stays consistent.</small>
        </div>
    </div>

    <?php if (!empty($params['colony_id']) && (float)$params['value'] != 0): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Preview — <?= count($preview) ?> plot(s)</h5>
            <?php if (!empty($preview)): ?>
            <form method="POST" action="<?= BASE_URL ?>/admin/plots/batch-pricing/apply" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="colony_id" value="<?= (int)$params['colony_id'] ?>">
                <input type="hidden" name="status" value="<?= htmlspecialchars((string)$params['status']) ?>">
                <input type="hidden" name="mode" value="<?= htmlspecialchars((string)$params['mode']) ?>">
                <input type="hidden" name="value" value="<?= htmlspecialchars((string)$params['value']) ?>">
                <button type="submit" class="btn btn-success" data-aps-confirm="Apply price revision to <?= count($preview) ?> plots? This runs in one atomic transaction.">
                    <i class="fas fa-check me-1"></i>Apply to <?= count($preview) ?> Plots
                </button>
            </form>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($preview)): ?>
                <div class="text-center py-4 text-muted">No plots match the selected colony/status.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Plot</th><th class="text-end">Old Rate</th><th class="text-end">New Rate</th><th class="text-end">Old Total</th><th class="text-end">New Total</th><th class="text-end">Diff</th></tr>
                        </thead>
                        <tbody>
                            <?php $tOld = 0; $tNew = 0; foreach ($preview as $r): $tOld += $r['old_total']; $tNew += $r['new_total']; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['plot_number']) ?></strong></td>
                                <td class="text-end">₹<?= number_format($r['old_pps']) ?></td>
                                <td class="text-end text-primary fw-bold">₹<?= number_format($r['new_final_pps']) ?></td>
                                <td class="text-end">₹<?= number_format($r['old_total']) ?></td>
                                <td class="text-end text-primary fw-bold">₹<?= number_format($r['new_total']) ?></td>
                                <td class="text-end <?= ($r['new_total'] - $r['old_total']) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= ($r['new_total'] - $r['old_total']) >= 0 ? '+' : '' ?>₹<?= number_format($r['new_total'] - $r['old_total']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr><th colspan="3" class="text-end">Totals (<?= count($preview) ?> plots)</th><th class="text-end">₹<?= number_format($tOld) ?></th><th class="text-end">₹<?= number_format($tNew) ?></th><th class="text-end">₹<?= number_format($tNew - $tOld) ?></th></tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
