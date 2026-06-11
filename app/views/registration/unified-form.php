<?php $pageTitle = $pageTitle ?? $page_title ?? "Registration"; $base = $base ?? BASE_URL; $fields = $fields ?? ["name", "email", "phone", "password"]; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card aps-cp-card"><div class="card-body p-4">
                <h4 class="mb-4"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
                <form method="post" action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>register">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php foreach ($fields as $f): ?>
                    <div class="mb-3"><label class="form-label"><?= ucfirst($f) ?></label><input type="<?= $f === "password" ? "password" : "text" ?>" class="form-control" name="<?= htmlspecialchars($f, ENT_QUOTES, 'UTF-8') ?>" required></div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
            </div></div>
        </div>
    </div>
</div>