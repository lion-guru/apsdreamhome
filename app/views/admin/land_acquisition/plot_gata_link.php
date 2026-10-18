<?php
/**
 * S258 Plot-Gata-Kisan Link Desk view.
 * Reuses land_parcels / plots / farmer_land_holdings / land_deals — zero new tables.
 */
$colonies = $colonies ?? [];
$colony = $colony ?? ['name' => ''];
$colony_id = (int)($colony_id ?? 6);
$parcels = $parcels ?? [];
$agg = $agg ?? [];
$holdings = $holdings ?? [];
$fnames = $fnames ?? [];
$deals = $deals ?? [];
$unlinked = $unlinked ?? [];
$orphans = $orphans ?? [];
$nullGata = $null_gata ?? null;
$stats = $stats ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
$pct = ($stats['plots'] ?? 0) > 0 ? round(100 * ($stats['linked'] ?? 0) / $stats['plots']) : 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-link text-success me-2"></i>Plot&ndash;Gata&ndash;Kisan Link</h3>
            <p class="text-muted small mb-0"><?= htmlspecialchars($colony['name'] ?? '') ?> &mdash; kaun plot kis gata me, gata kis kisan ka, kitna paisa gaya / baki</p>
        </div>
        <div>
            <a href="/admin/farmer-acquisitions" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Bainama Desk</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="/admin/land-plot-link" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === $colony_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?><?= empty($c['is_active']) ? ' (hidden)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-7 text-muted small">
                    Gata = khatauni ka gata number &nbsp;|&nbsp; Parcel = land_parcels row &nbsp;|&nbsp; Holding = kisan ka hissa (farmer_land_holdings)
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Gata Parcels</div>
                <div class="fs-3 fw-bold mt-1"><?= (int)($stats['parcels'] ?? 0) ?></div>
                <div class="text-muted small mt-1"><?= number_format((float)($stats['acres'] ?? 0), 2) ?> acres</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Plots Linked</div>
                <div class="fs-3 fw-bold mt-1"><?= (int)($stats['linked'] ?? 0) ?>/<?= (int)($stats['plots'] ?? 0) ?></div>
                <div class="text-muted small mt-1"><?= $pct ?>% gata-mapped</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Bina Gata</div>
                <div class="fs-3 fw-bold mt-1"><?= (int)($nullGata['c'] ?? 0) ?></div>
                <div class="text-muted small mt-1">plots with empty gata</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Zameen Paisa (deals)</div>
                <div class="fs-5 fw-bold text-success mt-1">₹<?= number_format((float)($stats['advance'] ?? 0), 0) ?> paid</div>
                <div class="text-muted small mt-1">₹<?= number_format((float)($stats['balance'] ?? 0), 0) ?> balance baki</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-secondary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Orphan Gata Text</div>
                <div class="fs-3 fw-bold mt-1"><?= count($orphans) ?></div>
                <div class="text-muted small mt-1">plot gata ka parcel row nahi</div>
            </div>
        </div>
    </div>

    <?php if ($deals): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold small">Colony Land Deals &mdash; kitna gaya / kitna baki / registry</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Area sqft</th><th class="text-end">Total</th><th class="text-end">Advance paid</th><th class="text-end">Balance</th><th>Status</th><th>Registry No</th></tr></thead>
                    <tbody>
                    <?php foreach ($deals as $d): ?>
                        <tr>
                            <td><?= (int)$d['id'] ?></td>
                            <td><?= number_format((float)($d['total_area_sqft'] ?? 0), 0) ?></td>
                            <td class="text-end">₹<?= number_format((float)($d['total_consideration'] ?? 0), 0) ?></td>
                            <td class="text-end text-success">₹<?= number_format((float)($d['advance_paid'] ?? 0), 0) ?></td>
                            <td class="text-end text-danger">₹<?= number_format((float)($d['balance_amount'] ?? 0), 0) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($d['status'] ?? '') ?></span></td>
                            <td class="small"><?= htmlspecialchars($d['registration_number'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold small">Gata-wise: plots + kisan + hissa + payment (kisan naam yahin update karo)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>Gata</th><th>Village</th><th class="text-end">Acres / Bigha</th><th>Kisan (owner)</th>
                        <th class="text-end">Plots A/B/S</th><th class="text-end">Plot value</th><th>Kisan hissa + payment</th><th>Mutation</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($parcels as $pc):
                        $g = (string)$pc['khasra_no'];
                        $a = $agg[$g] ?? ['c' => 0, 'sqft' => 0, 'av' => 0, 'bk' => 0, 'so' => 0, 'val' => 0];
                        $hl = $holdings[$g] ?? [];
                    ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($g) ?></td>
                            <td><?= htmlspecialchars($pc['village'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float)($pc['area_acres'] ?? 0), 2) ?> / <?= number_format((float)($pc['area_bigha'] ?? 0), 2) ?></td>
                            <td style="min-width:220px">
                                <form method="POST" action="/admin/land-plot-link/owner" class="d-flex gap-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="colony_id" value="<?= $colony_id ?>">
                                    <input type="hidden" name="parcel_id" value="<?= (int)$pc['id'] ?>">
                                    <input type="text" name="owner_name" class="form-control form-control-sm" placeholder="Kisan naam" value="<?= htmlspecialchars($pc['owner_name'] ?? '') ?>">
                                    <input type="text" name="owner_phone" class="form-control form-control-sm" placeholder="Phone" style="max-width:110px" value="<?= htmlspecialchars($pc['owner_phone'] ?? '') ?>">
                                    <button class="btn btn-sm btn-outline-success" title="Save"><i class="fas fa-check"></i></button>
                                </form>
                            </td>
                            <td class="text-end"><?= (int)$a['c'] ?> <span class="text-muted small">(<?= (int)$a['av'] ?>/<?= (int)$a['bk'] ?>/<?= (int)$a['so'] ?>)</span></td>
                            <td class="text-end">₹<?= number_format((float)$a['val'], 0) ?></td>
                            <td class="small">
                                <?php if (!$hl): ?><span class="text-muted">—</span><?php endif; ?>
                                <?php foreach ($hl as $h):
                                    $fid = (int)($h['farmer_id'] ?? 0);
                                    $nm = $fnames['farmers:' . $fid] ?? $fnames['farmer_profiles:' . $fid] ?? ('#' . $fid);
                                ?>
                                    <div><?= htmlspecialchars($nm) ?> &middot; <?= htmlspecialchars($h['land_area'] ?? '') ?> <?= htmlspecialchars($h['land_area_unit'] ?? '') ?> &middot; <?= htmlspecialchars($h['acquisition_status'] ?? '') ?> &middot; <span class="<?= (($h['payment_status'] ?? '') === 'completed') ? 'text-success' : 'text-warning' ?>"><?= htmlspecialchars($h['payment_status'] ?? '') ?> ₹<?= number_format((float)($h['payment_received'] ?? 0), 0) ?></span></div>
                                <?php endforeach; ?>
                            </td>
                            <td><span class="badge <?= (($pc['mutation_status'] ?? '') === 'completed') ? 'bg-success' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($pc['mutation_status'] ?? '') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$parcels): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Is colony ke liye land_parcels me koi gata nahi — pehle parcel entry karo.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold small">Plot &rarr; Gata link karo (sirf available plots)</div>
                <div class="card-body">
                    <?php if (!$unlinked): ?>
                        <p class="text-muted small mb-0">Sab available plots parcel-linked hain. (Booked/sold ka link freeze hai.)</p>
                    <?php else: ?>
                    <form method="POST" action="/admin/land-plot-link/link-plot" class="row g-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="colony_id" value="<?= $colony_id ?>">
                        <div class="col-md-5">
                            <select name="plot_id" class="form-select form-select-sm" required>
                                <option value="">Plot chuno (<?= count($unlinked) ?>)…</option>
                                <?php foreach ($unlinked as $u): ?>
                                    <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['plot_number']) ?> (<?= number_format((float)$u['area_sqft'], 0) ?> sqft)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="parcel_id" class="form-select form-select-sm" required>
                                <option value="">Gata chuno…</option>
                                <?php foreach ($parcels as $pc): ?>
                                    <option value="<?= (int)$pc['id'] ?>"><?= htmlspecialchars($pc['khasra_no']) ?> — <?= htmlspecialchars($pc['village'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-success btn-sm w-100">Link</button></div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold small">Dhyan do</div>
                <div class="card-body small">
                    <?php if ($orphans): ?>
                        <div class="alert alert-warning py-2 small mb-2">In gata-text ka parcel row nahi hai:
                        <?php foreach ($orphans as $o): ?><span class="badge bg-warning text-dark me-1"><?= htmlspecialchars($o['gata_number'] ?? '') ?> (<?= (int)$o['c'] ?> plots)</span><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (($nullGata['c'] ?? 0) > 0): ?>
                        <div class="alert alert-info py-2 small mb-2"><?= (int)$nullGata['c'] ?> plots me gata khaali hai — upar form se link karo.</div>
                    <?php endif; ?>
                    <?php if (!$orphans && !($nullGata['c'] ?? 0)): ?>
                        <p class="text-muted mb-0">Sab plots gata-mapped. Kisan naam khaali ho to table me seedha likh ke <i class="fas fa-check"></i> dabao.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
