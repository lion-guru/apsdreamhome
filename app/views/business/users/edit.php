<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Business Associate</h4>
        <a href="<?= BASE_URL ?>/business/users" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?= $_SERVER['REQUEST_URI'] ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sponsor</label>
                        <input type="text" name="sponsor" class="form-control" value="<?= htmlspecialchars($user['sponsor_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select">
                            <option value="bronze" <?= ($user['level'] ?? '') === 'bronze' ? 'selected' : '' ?>>Bronze</option>
                            <option value="silver" <?= ($user['level'] ?? '') === 'silver' ? 'selected' : '' ?>>Silver</option>
                            <option value="gold" <?= ($user['level'] ?? '') === 'gold' ? 'selected' : '' ?>>Gold</option>
                            <option value="platinum" <?= ($user['level'] ?? '') === 'platinum' ? 'selected' : '' ?>>Platinum</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($user['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="suspended" <?= ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" class="form-control" step="0.01" min="0" max="100" value="<?= htmlspecialchars($user['commission_rate'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Amount (₹)</label>
                        <input type="number" name="target_amount" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($user['target_amount'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Associate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
