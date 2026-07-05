<?php $page_title = $page_title ?? 'Verify KYC'; $result = $result ?? ['pan'=>null,'aadhaar'=>null]; ?>
<div class="container-fluid px-4 py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-id-card me-2 text-primary"></i>Verify KYC</h4>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-header bg-primary text-white" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-credit-card me-1"></i>PAN Verification</h6></div>
                <div class="card-body">
                    <form method="POST" action="/admin/crm/kyc/verify"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3"><label class="form-label">PAN Number</label><input type="text" name="pan_number" class="form-control" placeholder="ABCDE1234F" maxlength="10" required style="text-transform:uppercase"></div>
                        <div class="mb-3"><label class="form-label">Name on PAN</label><input type="text" name="pan_name" class="form-control"></div>
                        <button type="submit" name="verify_pan" class="btn btn-primary"><i class="fas fa-search me-1"></i>Verify PAN</button>
                    </form>
                    <?php if ($result['pan']): ?>
                    <div class="alert <?= $result['pan']['valid'] ? 'alert-success' : 'alert-danger' ?> mt-3"><i class="fas fa-<?= $result['pan']['valid'] ? 'check-circle' : 'exclamation-circle' ?> me-1"></i><?= $result['pan']['message'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius:14px"><div class="card-header bg-success text-white" style="border-radius:14px 14px 0 0"><h6 class="mb-0"><i class="fas fa-fingerprint me-1"></i>Aadhaar Verification</h6></div>
                <div class="card-body">
                    <form method="POST" action="/admin/crm/kyc/verify"><input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3"><label class="form-label">Aadhaar Number</label><input type="text" name="aadhaar_number" class="form-control" placeholder="1234 5678 9012" maxlength="14" required></div>
                        <div class="mb-3"><label class="form-label">Name on Aadhaar</label><input type="text" name="aadhaar_name" class="form-control"></div>
                        <button type="submit" name="verify_aadhaar" class="btn btn-success"><i class="fas fa-search me-1"></i>Verify Aadhaar</button>
                    </form>
                    <?php if ($result['aadhaar']): ?>
                    <div class="alert <?= $result['aadhaar']['valid'] ? 'alert-success' : 'alert-danger' ?> mt-3"><i class="fas fa-<?= $result['aadhaar']['valid'] ? 'check-circle' : 'exclamation-circle' ?> me-1"></i><?= $result['aadhaar']['message'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
