<?php $page_title = 'Referral Details'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-share-alt me-2"></i>Referral Details</h2>
    <?php if (!$referral): ?>
        <div class="alert alert-danger">Referral not found.</div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row">
                    <div class="col-md-3"><small class="text-muted">Referrer</small><br><strong><?= htmlspecialchars($referral['referrer_name'] ?? 'Unknown') ?></strong></div>
                    <div class="col-md-3"><small class="text-muted">Email</small><br><?= htmlspecialchars($referral['referrer_email'] ?? '') ?></div>
                    <div class="col-md-3"><small class="text-muted">Referred Email</small><br><?= htmlspecialchars($referral['referred_email']) ?></div>
                    <div class="col-md-3"><small class="text-muted">Code</small><br><code><?= htmlspecialchars($referral['referral_code'] ?? '') ?></code></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-3"><small class="text-muted">Status</small><br><span class="badge bg-<?= ($referral['status'] ?? 'pending')==='converted'?'success':(($referral['status'] ?? 'pending')==='rejected'?'danger':'warning') ?>"><?= ucfirst($referral['status'] ?? 'pending') ?></span></div>
                    <div class="col-md-3"><small class="text-muted">Created</small><br><?= date('d M Y H:i', strtotime($referral['created_at'])) ?></div>
                    <div class="col-md-3"><small class="text-muted">Converted At</small><br><?= $referral['converted_at'] ? date('d M Y H:i', strtotime($referral['converted_at'])) : 'N/A' ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/admin/referrals" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
