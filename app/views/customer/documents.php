<?php
/**
 * Customer Document Locker View
 * Displays all customer documents: Allotment Letter, Payment Passbook, Agreement to Sell
 */
$page_title = 'My Documents - APS Dream Home';
$base = BASE_URL ?? '';
$user = $user ?? [];
$userDocuments = $userDocuments ?? [];
$bookingDocuments = $bookingDocuments ?? [];
$agreementDocuments = $agreementDocuments ?? [];
$base = BASE_URL ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'My Documents') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .document-card { transition: all 0.3s ease; }
        .document-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .document-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .doc-type-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; }
        .badge-allotment { background: #dbeafe; color: #1e40af; }
        .badge-passbook { background: #dcfce7; color: #166534; }
        .badge-agreement { background: #fef3c7; color: #92400e; }
        .document-actions .btn { transition: all 0.2s ease; }
        .document-actions .btn:hover { transform: translateY(-1px); }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="fas fa-folder-open me-2"></i>My Documents</h2>
                        <p class="text-muted mb-0">Manage and download your important documents</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>/user/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Allotment Letters -->
            <div class="col-12 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-file-contract text-primary me-2"></i> Allotment Letters
                </h5>
                <div class="row g-3">
                    <?php if (empty($allotmentDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Allotment Letters Found</h6>
                                    <p class="text-muted small">Your allotment letters will appear here once a plot is allotted.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($allotmentDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-primary bg-opacity-10 text-primary">
                                                <i class="fas fa-file-contract"></i>
                                            </div>
                                            <span class="doc-type-badge badge-allotment">Allotment Letter</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Allotment Letter') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-building me-1"></i>
                                            <?= htmlspecialchars($doc['colony_name'] ?? 'N/A') ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/user/documents/download/allotment/<?= $doc['id'] ?>" class="btn btn-sm btn-primary w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Passbooks -->
            <div class="col-12 mb-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-book text-success me-2"></i> Payment Passbooks
                </h5>
                <div class="row g-3">
                    <?php if (empty($passbookDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Passbooks Found</h6>
                                    <p class="text-muted small">Payment passbooks will appear here after payments are made.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($passbookDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <span class="doc-type-badge badge-passbook">Passbook</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Payment Passbook') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-rupee-sign me-1"></i>
                                            <?= number_format($doc['amount'] ?? 0) ?> paid
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/user/documents/download/passbook/<?= $doc['id'] ?>" class="btn btn-sm btn-success w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Passbook
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Agreements -->
            <div class="col-12">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-file-signature text-warning me-2"></i> Agreements
                </h5>
                <div class="row g-3">
                    <?php if (empty($agreementDocuments)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Agreements Found</h6>
                                    <p class="text-muted small">Agreement documents will appear here after booking confirmation.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($agreementDocuments as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-warning bg-opacity-10 text-warning">
                                                <i class="fas fa-file-signature"></i>
                                            </div>
                                            <span class="doc-type-badge badge-agreement">Agreement</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['title'] ?? 'Agreement') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($doc['created_at'] ?? '')) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-file-signature me-1"></i>
                                            <?= htmlspecialchars($doc['type'] ?? 'Agreement') ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/user/documents/download/agreement/<?= $doc['id'] ?>" class="btn btn-sm btn-warning w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Agreement
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Title Search Certificates (bank-loan ready) -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-shield-halved text-success me-2"></i> Title Search Certificates
                    <small class="text-muted fw-normal ms-2">— attach straight into your bank loan file</small>
                </h5>
                <div class="row g-3">
                    <?php $titleCertificates = $titleCertificates ?? []; ?>
                    <?php if (empty($titleCertificates)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-shield-halved fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Title Certificates Yet</h6>
                                    <p class="text-muted small">Issued after legal verification of your plot — check back soon.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($titleCertificates as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-success bg-opacity-10 text-success">
                                                <i class="fas fa-shield-halved"></i>
                                            </div>
                                            <span class="doc-type-badge" style="background:#dcfce7;color:#166534">Title Certificate</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['cert_no'] ?? 'Title Certificate') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-pin me-1"></i>
                                            Plot <?= htmlspecialchars($doc['plot_number'] ?? '') ?> · <?= htmlspecialchars($doc['colony_name'] ?? '') ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= !empty($doc['created_at']) ? date('d M Y', strtotime($doc['created_at'])) : '' ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/user/documents/download/title-certificate/<?= $doc['id'] ?>" class="btn btn-sm btn-success w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Joint Ownerships (female stamp-duty concession) -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-users text-primary me-2"></i> Joint Ownerships
                    <small class="text-muted fw-normal ms-2">— co-owners on your bookings</small>
                </h5>
                <div class="row g-3">
                    <?php $jointOwnerships = $jointOwnerships ?? []; ?>
                    <?php $hasJoint = false; foreach ($jointOwnerships as $jo) { if (!empty($jo['co_owners'])) { $hasJoint = true; break; } } ?>
                    <?php if (!$hasJoint): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Sole Allotments Only</h6>
                                    <p class="text-muted small">Ask the office to add a co-owner (e.g. spouse) — joint registration with a female co-owner lowers UP stamp duty.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jointOwnerships as $jo): if (empty($jo['co_owners'])) continue; ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-primary bg-opacity-10 text-primary">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <span class="doc-type-badge" style="background:#dbeafe;color:#1e40af">Joint</span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($jo['booking_number'] ?? '') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-pin me-1"></i>
                                            Plot <?= htmlspecialchars($jo['plot_number'] ?? '') ?> · <?= htmlspecialchars($jo['colony_name'] ?? '') ?>
                                        </p>
                                        <p class="small mb-2">
                                            <?php foreach ($jo['co_owners'] as $co): ?>
                                                <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($co['full_name'] ?? '') ?> (<?= htmlspecialchars($co['gender'] ?? '') ?>, <?= number_format((float)($co['share_percentage'] ?? 0), 0) ?>%)</span>
                                            <?php endforeach; ?>
                                        </p>
                                        <?php if ($jo['stamp_saving'] !== null && (float)$jo['stamp_saving'] > 0): ?>
                                            <p class="small text-success fw-semibold mb-0"><i class="fas fa-badge-percent me-1"></i>Female concession active — saving approx ₹<?= number_format((float)$jo['stamp_saving']) ?> on stamp duty.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Legal Nominee per booking -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-user-shield text-info me-2"></i> Legal Nominee (वारिसदार)
                    <small class="text-muted fw-normal ms-2">— who inherits your plot</small>
                </h5>
                <div class="row g-3">
                    <?php $nomineeRows = $jointOwnerships ?? []; ?>
                    <?php if (empty($nomineeRows)): ?>
                        <div class="col-12"><div class="card border-0 bg-light"><div class="card-body text-center py-4">
                            <h6 class="text-muted">No bookings yet</h6>
                        </div></div></div>
                    <?php else: ?>
                        <?php foreach ($nomineeRows as $nr): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($nr['booking_number'] ?? '') ?></h6>
                                        <p class="text-muted small mb-2">Plot <?= htmlspecialchars($nr['plot_number'] ?? '') ?> · <?= htmlspecialchars($nr['colony_name'] ?? '') ?></p>
                                        <?php if (!empty($nr['nominee_name'])): ?>
                                            <p class="small mb-1"><strong><?= htmlspecialchars($nr['nominee_name']) ?></strong> (<?= htmlspecialchars($nr['nominee_relation'] ?? '') ?>)</p>
                                            <?php if (!empty($nr['guardian_name'])): ?><p class="small text-muted mb-1">Guardian: <?= htmlspecialchars($nr['guardian_name']) ?></p><?php endif; ?>
                                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="<?= BASE_URL ?>/user/bookings/<?= (int)($nr['booking_id'] ?? 0) ?>/nominee-sheet"><i class="fas fa-print me-1"></i>Succession Sheet</a>
                                        <?php else: ?>
                                            <p class="text-muted small mb-2">No nominee recorded.</p>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-secondary mt-1" data-bs-toggle="modal" data-bs-target="#nomineeModal<?= (int)($nr['booking_id'] ?? 0) ?>"><?= !empty($nr['nominee_name']) ? 'Update' : 'Add' ?> Nominee</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="nomineeModal<?= (int)($nr['booking_id'] ?? 0) ?>" tabindex="-1">
                                <div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="<?= BASE_URL ?>/user/bookings/<?= (int)($nr['booking_id'] ?? 0) ?>/nominee">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <div class="modal-header"><h6 class="modal-title">Legal Nominee — <?= htmlspecialchars($nr['booking_number'] ?? '') ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body row g-2">
                                            <div class="col-12"><label class="form-label small">Full name *</label><input type="text" name="nominee_name" class="form-control" required maxlength="150" value="<?= htmlspecialchars($nr['nominee_name'] ?? '') ?>"></div>
                                            <div class="col-6"><label class="form-label small">Relation *</label><select name="nominee_relation" class="form-select"><?php foreach (['Spouse','Son','Daughter','Mother','Father','Brother','Sister'] as $rel): ?><option <?= ($nr['nominee_relation'] ?? '') === $rel ? 'selected' : '' ?>><?= $rel ?></option><?php endforeach; ?></select></div>
                                            <div class="col-6"><label class="form-label small">DOB</label><input type="date" name="nominee_dob" class="form-control" value="<?= htmlspecialchars($nr['nominee_dob'] ?? '') ?>"></div>
                                            <div class="col-6"><label class="form-label small">Aadhaar</label><input type="text" name="nominee_aadhaar" class="form-control" maxlength="20" value="<?= htmlspecialchars($nr['nominee_aadhaar'] ?? '') ?>"></div>
                                            <div class="col-6"><label class="form-label small">Phone</label><input type="text" name="nominee_phone" class="form-control" maxlength="20" value="<?= htmlspecialchars($nr['nominee_phone'] ?? '') ?>"></div>
                                            <div class="col-12"><label class="form-label small">Guardian (if nominee is minor)</label><input type="text" name="guardian_name" class="form-control" maxlength="150" value="<?= htmlspecialchars($nr['guardian_name'] ?? '') ?>"></div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Nominee</button></div>
                                    </form>
                                </div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Maintenance Dues -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-building-shield text-danger me-2"></i> Maintenance Dues
                    <small class="text-muted fw-normal ms-2">— clear before registry NOC</small>
                </h5>
                <div class="row g-3">
                    <?php $maintenanceDues = $maintenanceDues ?? []; ?>
                    <?php if (empty($maintenanceDues)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-4">
                                    <h6 class="text-success"><i class="fas fa-check-circle me-1"></i>No Pending Dues</h6>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($maintenanceDues as $due):
                            $amt = max(0, (float)($due['total_due'] ?? 0) - (float)($due['paid_amount'] ?? 0));
                            $upi = 'upi://pay?' . http_build_query(['pa' => 'apsdreamhome@icici', 'pn' => 'APS Dream Home', 'am' => number_format($amt, 2, '.', ''), 'cu' => 'INR', 'tn' => substr('Maintenance ' . ($due['booking_number'] ?? ''), 0, 80)]);
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($due['booking_number'] ?? '') ?> · <?= str_pad((string)($due['bill_month'] ?? ''), 2, '0', STR_PAD_LEFT) ?>/<?= htmlspecialchars($due['bill_year'] ?? '') ?></h6>
                                        <p class="text-muted small mb-2">Plot <?= htmlspecialchars($due['plot_number'] ?? '') ?> · <?= htmlspecialchars($due['colony_name'] ?? '') ?></p>
                                        <p class="fw-bold mb-2">Due: ₹<?= number_format($amt) ?></p>
                                        <a class="btn btn-sm btn-success w-100" href="<?= htmlspecialchars($upi) ?>">💳 Pay ₹<?= number_format($amt) ?> via UPI</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tracked Possession Certificates -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-key text-warning me-2"></i> Possession Certificates
                    <small class="text-muted fw-normal ms-2">— issued handovers with pillar proof</small>
                </h5>
                <div class="row g-3">
                    <?php $possessionCerts = $possessionCerts ?? []; ?>
                    <?php if (empty($possessionCerts)): ?>
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No Possession Certificates Yet</h6>
                                    <p class="text-muted small">Issued after physical handover of your plot — check back soon.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($possessionCerts as $doc): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card document-card h-100 border-0 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-3">
                                            <div class="document-icon bg-warning bg-opacity-10 text-warning">
                                                <i class="fas fa-key"></i>
                                            </div>
                                            <span class="doc-type-badge" style="background:#fef3c7;color:#92400e"><?= htmlspecialchars($doc['handover_status'] ?? 'final') ?></span>
                                        </div>
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['certificate_no'] ?? 'Possession Certificate') ?></h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-pin me-1"></i>
                                            Plot <?= htmlspecialchars($doc['plot_number'] ?? '') ?> · <?= htmlspecialchars($doc['colony_name'] ?? '') ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= htmlspecialchars($doc['handover_date'] ?? '') ?>
                                            <?= !empty($doc['pillar_photo_path']) ? ' · <a href="' . BASE_URL . '/' . htmlspecialchars($doc['pillar_photo_path']) . '" target="_blank">pillar photo</a>' : '' ?>
                                        </p>
                                        <div class="mt-auto document-actions">
                                            <a href="<?= BASE_URL ?>/user/possession-certificate/<?= (int)($doc['booking_id'] ?? 0) ?>" class="btn btn-sm btn-warning w-100" target="_blank">
                                                <i class="fas fa-download me-1"></i> Download Certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Plot Resale & Ownership Transfer NOC Desk -->
            <div class="col-12 mt-4">
                <h5 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fas fa-exchange-alt text-primary me-2"></i> Plot Resale & Transfer NOC Desk
                        <small class="text-muted fw-normal ms-2">— transfer rights, resell, or gift to family</small>
                    </div>
                    <a href="<?= BASE_URL ?>/user/plot-transfers" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>Apply for Transfer
                    </a>
                </h5>
                <div class="card border-0 bg-light p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="fw-semibold">Looking to resell or transfer allotment rights?</span>
                            <div class="small text-muted">Verify eligibility, submit buyer details, and download official QR-verified Transfer NOC.</div>
                        </div>
                        <a href="<?= BASE_URL ?>/user/plot-transfers" class="btn btn-sm btn-primary">
                            Manage Plot Transfers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// Add download tracking if needed
document.querySelectorAll('.document-card a[target="_blank"]').forEach(link => {
    link.addEventListener('click', function() {
        console.log('Document download initiated:', this.href);
    });
});
</script>
</body>
</html>