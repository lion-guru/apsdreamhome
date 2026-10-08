<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow">
                <div class="card-body aps-cp-card-body">
                    <div class="text-center mb-4">
                        <h2 class="h3 mb-3">
                            <i class="fas fa-home"></i> APS Dream Home
                        </h2>
                        <p class="text-muted"><?= __('auth_verify_email') ?></p>
                    </div>
                    <?php
                    $veError = $_SESSION['error'] ?? null;
                    $veSuccess = $_SESSION['success'] ?? null;
                    unset($_SESSION['error'], $_SESSION['success']);
                    ?>
                    <?php if (!empty($veError)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($veError ?? '') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($veSuccess)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($veSuccess ?? '') ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/verify-email">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label class="form-label" for="veEmail">Email *</label>
                            <input type="email" class="form-control" name="email" id="veEmail" required
                                value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" placeholder="you@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="veToken">Verification code *</label>
                            <input type="text" class="form-control" name="token" id="veToken" required
                                value="<?= htmlspecialchars($_GET['token'] ?? '') ?>" placeholder="Paste the code from your email">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verify Email</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/login" class="text-muted">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
<?php include __DIR__ . "/../layouts/footer.php"; ?>