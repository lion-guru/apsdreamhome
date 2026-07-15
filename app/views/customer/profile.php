<?php
$user = $user ?? [];
$page_title = $page_title ?? 'My Profile';
?>
<?php include __DIR__ . "/../layouts/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-user"></i> <?= __('profile_title', [], 'My Profile') ?>
                </h1>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card aps-cp-card">
                <div class="card-body text-center py-4">
                    <?php
                    $userId = (int)($user['id'] ?? $_SESSION['user_id'] ?? 0);
                    $photoUrl = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : null;
                    $userName = $user['name'] ?? $_SESSION['user_name'] ?? 'Customer';
                    $size = 'lg';
                    include __DIR__ . '/../shared/profile_photo_upload.php';
                    ?>
                    <h5 class="mt-3 mb-1"><?= htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'Customer') ?></h5>
                    <p class="text-muted mb-1"><?= htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? '') ?></p>
                    <p class="text-muted mb-2"><?= htmlspecialchars($user['phone'] ?? $_SESSION['user_phone'] ?? '') ?></p>
                    <span class="badge bg-primary"><?= __('profile_role_customer', [], 'Customer') ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <h5 class="card-title"><i class="fas fa-edit me-2"></i><?= __('profile_details', [], 'Profile Details') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/customer/profile">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('profile_full_name', [], 'Full Name') ?></label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('profile_email', [], 'Email') ?></label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? $_SESSION['user_email'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('profile_phone', [], 'Phone') ?></label>
                                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? $_SESSION['user_phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('profile_member_since', [], 'Member Since') ?></label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['created_at'] ?? date('Y-m-d')) ?>" disabled>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?= __('profile_update', [], 'Update Profile') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layouts/footer.php"; ?>