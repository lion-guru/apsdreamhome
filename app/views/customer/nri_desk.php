<?php
/** NRI investment desk — buyer self-service (FIRC + passport KYC). */
$bookings = $bookings ?? [];
$records = $records ?? [];
$flash_ok = $_SESSION['flash_success'] ?? '';
$flash_err = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$badge = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My NRI Desk - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="fas fa-globe me-2"></i>My NRI Desk</h2>
                <p class="text-muted mb-0">FIRC + passport KYC for your cross-border investment (RBI/FEMA)</p>
            </div>
            <a href="<?= BASE_URL ?>/user/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Documents</a>
        </div>

        <?php if ($flash_ok !== ''): ?><div class="alert alert-success"><?= $e($flash_ok) ?></div><?php endif; ?>
        <?php if ($flash_err !== ''): ?><div class="alert alert-danger"><?= $e($flash_err) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm"><div class="card-body">
                    <h5 class="fw-bold mb-3">Submit / Update Compliance</h5>
                    <?php if (empty($bookings)): ?>
                        <p class="text-muted">No bookings on your account yet.</p>
                    <?php else: ?>
                    <form method="POST" action="<?= BASE_URL ?>/user/nri-desk/save" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-2"><label class="form-label small">Booking *</label>
                            <select name="booking_id" class="form-select" required>
                                <?php foreach ($bookings as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= $e($b['booking_number'] ?? '') ?> — Plot <?= $e($b['plot_number'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label small">Country of residence *</label><input type="text" name="country_of_residence" class="form-control" required maxlength="100" placeholder="e.g. UAE"></div>
                            <div class="col-md-6"><label class="form-label small">Passport number *</label><input type="text" name="passport_number" class="form-control" required maxlength="50"></div>
                            <div class="col-md-6"><label class="form-label small">Visa type</label><input type="text" name="visa_type" class="form-control" maxlength="50" placeholder="e.g. Employment"></div>
                            <div class="col-md-6"><label class="form-label small">Bank account</label>
                                <select name="bank_account_type" class="form-select"><option>NRE</option><option>NRO</option><option>FCNR</option></select>
                            </div>
                            <div class="col-12"><label class="form-label small">FIRC number (from your bank)</label><input type="text" name="firc_number" class="form-control" maxlength="100"></div>
                            <div class="col-md-6"><label class="form-label small">FIRC document (pdf/doc/txt ≤5MB)</label><input type="file" name="firc_doc" class="form-control" accept=".pdf,.doc,.docx,.csv,.txt"></div>
                            <div class="col-md-6"><label class="form-label small">Passport scan (pdf/doc/txt ≤5MB)</label><input type="file" name="passport_doc" class="form-control" accept=".pdf,.doc,.docx,.csv,.txt"></div>
                            <div class="col-12 form-check mt-2"><input type="checkbox" name="fema_declaration_signed" value="1" class="form-check-input" id="femaBox" checked><label class="form-check-label small" for="femaBox">I declare the funds comply with FEMA for property purchase in India.</label></div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-paper-plane me-1"></i>Submit for Verification</button>
                    </form>
                    <?php endif; ?>
                </div></div>
            </div>
            <div class="col-lg-7">
                <h5 class="fw-bold mb-3">My Submissions</h5>
                <?php if (empty($records)): ?>
                    <div class="card border-0 bg-light"><div class="card-body text-center py-5">
                        <i class="fas fa-passport fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">No submissions yet</h6>
                    </div></div>
                <?php else: foreach ($records as $r): ?>
                    <div class="card shadow-sm mb-3"><div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><?= $e($r['booking_number'] ?? '') ?> · <?= $e($r['country_of_residence'] ?? '') ?></strong>
                            <span class="badge bg-<?= $badge[$r['verification_status']] ?? 'secondary' ?>"><?= $e($r['verification_status'] ?? '') ?></span>
                        </div>
                        <div class="small text-muted mt-1"><?= $e($r['bank_account_type'] ?? '') ?> · FIRC <?= $e($r['firc_number'] ?? '—') ?> · FEMA <?= !empty($r['fema_declaration_signed']) ? 'signed' : 'pending' ?></div>
                    </div></div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
