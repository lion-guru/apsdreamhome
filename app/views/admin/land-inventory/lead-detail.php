<?php
$lead     = $lead ?? [];
$documents = $documents ?? [];
$visits   = $visits ?? [];
$opinions = $opinions ?? [];
$deal     = $deal ?? null;
$ledger   = $ledger ?? ['data'=>[],'summary'=>['total_amount'=>0,'cleared_amount'=>0]];
$brokers  = $brokers ?? [];

$id = (int)($lead['id'] ?? 0);
$statusColors = [
    'new'             => 'secondary',
    'screening'       => 'info',
    'visit_done'      => 'primary',
    'dd'              => 'warning',
    'negotiation'     => 'warning',
    'legal'           => 'info',
    'sale_agreement'  => 'success',
    'registered'      => 'success',
    'rejected'        => 'danger',
    'dropped'         => 'dark',
];

// Status advance options
$statusAdvance = [
    'new'            => [['screening','Screening'],['rejected','Reject'],['dropped','Drop']],
    'screening'      => [['visit_done','Visit Done'],['rejected','Reject'],['dropped','Drop']],
    'visit_done'     => [['dd','Start DD'],['rejected','Reject'],['dropped','Drop']],
    'dd'             => [['negotiation','Negotiation'],['rejected','Reject'],['dropped','Drop']],
    'negotiation'    => [['legal','Send to Legal'],['rejected','Reject'],['dropped','Drop']],
    'legal'          => [['sale_agreement','Sale Agreement'],['rejected','Reject'],['dropped','Drop']],
    'sale_agreement' => [['registered','Registered'],['rejected','Reject'],['dropped','Drop']],
];
$currentStatus = $lead['status'] ?? 'new';
$nextOptions = $statusAdvance[$currentStatus] ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-mountain text-primary me-2"></i>
                <?= htmlspecialchars($lead['land_owner_name'] ?? 'Lead') ?>
                <code class="ms-2">#<?= $id ?></code>
            </h4>
            <span class="badge bg-<?= $statusColors[$currentStatus] ?? 'secondary' ?>">
                <?= ucwords(str_replace('_',' ',$currentStatus)) ?>
            </span>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/edit" class="btn btn-primary btn-sm">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="<?= BASE_URL ?>/admin/land-inventory/leads" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <small class="text-muted">Expected Price</small>
                    <h5 class="mb-0">₹<?= number_format((float)($lead['expected_price'] ?? 0)) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <small class="text-muted">Area</small>
                    <h5 class="mb-0"><?= number_format((float)($lead['area_sqft'] ?? 0)) ?> sqft</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <small class="text-muted">Documents</small>
                    <h5 class="mb-0"><?= count($documents) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center">
                <div class="aps-cp-card-body">
                    <small class="text-muted">Site Visits</small>
                    <h5 class="mb-0"><?= count($visits) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#documents">Documents (<?= count($documents) ?>)</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#visits">Visits (<?= count($visits) ?>)</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#opinions">Opinions (<?= count($opinions) ?>)</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#acq">Acquisition</a></li>
                    </ul>
                </div>
                <div class="aps-cp-card-body tab-content">
                    <div class="tab-pane fade show active" id="overview">
                        <table class="table table-sm">
                            <tr><th width="200">Owner</th><td><?= htmlspecialchars($lead['land_owner_name'] ?? '—') ?></td></tr>
                            <tr><th>Phone</th><td><?= htmlspecialchars($lead['owner_phone'] ?? '—') ?></td></tr>
                            <tr><th>Email</th><td><?= htmlspecialchars($lead['owner_email'] ?? '—') ?></td></tr>
                            <tr><th>Source</th><td><?= htmlspecialchars(ucfirst($lead['lead_source'] ?? '—')) ?></td></tr>
                            <tr><th>Survey #</th><td><?= htmlspecialchars($lead['survey_number'] ?? '—') ?></td></tr>
                            <tr><th>Village</th><td><?= htmlspecialchars($lead['village'] ?? '—') ?></td></tr>
                            <tr><th>Tehsil</th><td><?= htmlspecialchars($lead['tehsil'] ?? '—') ?></td></tr>
                            <tr><th>District</th><td><?= htmlspecialchars($lead['district'] ?? '—') ?></td></tr>
                            <tr><th>State</th><td><?= htmlspecialchars($lead['state'] ?? '—') ?></td></tr>
                            <tr><th>Pincode</th><td><?= htmlspecialchars($lead['pincode'] ?? '—') ?></td></tr>
                            <tr><th>GPS</th><td><?= htmlspecialchars($lead['gps_lat'] ?? '—') ?>, <?= htmlspecialchars($lead['gps_lng'] ?? '—') ?></td></tr>
                            <tr><th>Area (Acres)</th><td><?= number_format((float)($lead['area_acres'] ?? 0), 2) ?></td></tr>
                            <tr><th>Area (sqft)</th><td><?= number_format((float)($lead['area_sqft'] ?? 0), 2) ?></td></tr>
                            <tr><th>Expected Price</th><td>₹<?= number_format((float)($lead['expected_price'] ?? 0)) ?></td></tr>
                            <tr><th>Notes</th><td><?= nl2br(htmlspecialchars($lead['notes'] ?? '—')) ?></td></tr>
                            <tr><th>Created</th><td><?= htmlspecialchars($lead['created_at'] ?? '—') ?></td></tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="documents">
                        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/documents" class="btn btn-primary btn-sm mb-3">
                            <i class="fas fa-upload me-1"></i>Manage Documents
                        </a>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Type</th><th>Doc #</th><th>Status</th><th>Date</th></tr></thead>
                                <tbody>
                                <?php foreach ($documents as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(ucwords(str_replace('_',' ', $d['doc_type'] ?? ''))) ?></td>
                                        <td><?= htmlspecialchars($d['doc_number'] ?? '—') ?></td>
                                        <td>
                                            <span class="badge bg-<?= ($d['verification_status'] ?? '') === 'verified' ? 'success' : (($d['verification_status'] ?? '') === 'rejected' ? 'danger' : 'warning') ?>">
                                                <?= htmlspecialchars($d['verification_status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($d['doc_date'] ?? '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($documents)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No documents uploaded yet.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="visits">
                        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/visits" class="btn btn-primary btn-sm mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i>Manage Visits
                        </a>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>Risk</th><th>Encroach</th><th>Observations</th></tr></thead>
                                <tbody>
                                <?php foreach ($visits as $v): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($v['visit_date'] ?? '') ?></td>
                                        <td><span class="badge bg-<?= ($v['risk_rating'] ?? '') === 'high' ? 'danger' : (($v['risk_rating'] ?? '') === 'medium' ? 'warning' : 'success') ?>"><?= htmlspecialchars($v['risk_rating'] ?? 'low') ?></span></td>
                                        <td><?= !empty($v['encroachment_found']) ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-success">No</span>' ?></td>
                                        <td><?= htmlspecialchars(mb_substr($v['observations'] ?? '', 0, 80)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($visits)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No site visits recorded.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="opinions">
                        <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/opinions" class="btn btn-primary btn-sm mb-3">
                            <i class="fas fa-gavel me-1"></i>Add Legal Opinion
                        </a>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>Advocate</th><th>Status</th><th>Title Chain</th><th>Encumbrance</th></tr></thead>
                                <tbody>
                                <?php foreach ($opinions as $o): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($o['opinion_date'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($o['advocate_name'] ?? '—') ?></td>
                                        <td><span class="badge bg-<?= ($o['status'] ?? '') === 'clear' ? 'success' : (($o['status'] ?? '') === 'not_clear' ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ', $o['status'] ?? ''))) ?></span></td>
                                        <td><?= !empty($o['title_verified_chain']) ? '✓' : '—' ?></td>
                                        <td><?= !empty($o['encumbrance_review']) ? '✓' : '—' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($opinions)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No legal opinions recorded.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="acq">
                        <?php if ($deal): ?>
                            <div class="alert alert-success">
                                <strong>Deal Closed!</strong> Deal #<?= (int)$deal['id'] ?>
                                <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= (int)$deal['id'] ?>" class="btn btn-sm btn-success float-end">
                                    <i class="fas fa-external-link-alt"></i> Open Deal
                                </a>
                            </div>
                            <table class="table table-sm">
                                <tr><th>Total Consideration</th><td>₹<?= number_format((float)($deal['total_consideration'] ?? 0)) ?></td></tr>
                                <tr><th>Advance Paid</th><td>₹<?= number_format((float)($deal['advance_paid'] ?? 0)) ?></td></tr>
                                <tr><th>Balance</th><td>₹<?= number_format((float)($deal['balance_amount'] ?? 0)) ?></td></tr>
                                <tr><th>Status</th><td><?= htmlspecialchars($deal['status'] ?? '—') ?></td></tr>
                                <tr><th>Registration Date</th><td><?= htmlspecialchars($deal['registration_date'] ?? '—') ?></td></tr>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No closed deal yet. Advance the lead to "Sale Agreement" then close the deal from the Deals page.</p>
                            <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/acquisitions" class="row g-2 align-items-end">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="lead_id" value="<?= $id ?>">
                                <div class="col-md-4">
                                    <label class="form-label small">Total Consideration (₹)</label>
                                    <input type="number" step="0.01" name="total_consideration" class="form-control form-control-sm" value="<?= (float)($lead['expected_price'] ?? 0) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Advance Paid (₹)</label>
                                    <input type="number" step="0.01" name="advance_paid" class="form-control form-control-sm" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Sale Agreement Date</label>
                                    <input type="date" name="sale_agreement_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>Use "Close Deal" from Deals page</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-forward me-2"></i>Advance Status</div>
                <div class="aps-cp-card-body">
                    <?php if (!empty($nextOptions)): ?>
                        <form method="post" action="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/advance">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <div class="d-grid gap-2">
                                <?php foreach ($nextOptions as $opt): ?>
                                    <button type="submit" name="new_status" value="<?= $opt[0] ?>"
                                            class="btn btn-sm btn-<?= $opt[0] === 'rejected' ? 'outline-danger' : ($opt[0] === 'dropped' ? 'outline-dark' : 'primary') ?>">
                                        <?= $opt[1] ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-muted mb-0"><i class="fas fa-check-circle text-success me-1"></i>Terminal status — no further actions.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-link me-2"></i>Quick Links</div>
                <div class="aps-cp-card-body d-grid gap-2">
                    <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/visits" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-map-marker-alt me-1"></i>Site Visits
                    </a>
                    <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/documents" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-alt me-1"></i>Documents
                    </a>
                    <a href="<?= BASE_URL ?>/admin/land-inventory/leads/<?= $id ?>/opinions" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-gavel me-1"></i>Legal Opinions
                    </a>
                    <?php if ($deal): ?>
                        <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= (int)$deal['id'] ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-file-contract me-1"></i>View Deal
                        </a>
                        <a href="<?= BASE_URL ?>/admin/land-inventory/acquisitions/<?= (int)$deal['id'] ?>/payments" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-money-bill-wave me-1"></i>Payment Ledger
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
