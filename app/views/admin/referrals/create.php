<?php $page_title = 'Create Referral'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Create Referral</h2>
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/referrals/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Referrer *</label>
                            <select name="referrer_id" class="form-select" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name'] ?? '') ?> (<?= htmlspecialchars($u['email'] ?? '') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Referred Email *</label>
                            <input type="email" name="referred_email" class="form-control" required placeholder="friend@email.com">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create</button>
                        <a href="<?= BASE_URL ?>/admin/referrals" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
