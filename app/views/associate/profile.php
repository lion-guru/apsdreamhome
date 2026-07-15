<?php
$page_title = $page_title ?? __('assoc_profile_title', [], 'My Profile');
$user = $user ?? [];
?>
<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <?php
                    $userId = (int)($user['id'] ?? $_SESSION['user_id'] ?? 0);
                    $photoUrl = !empty($user['profile_image']) ? BASE_URL . '/' . $user['profile_image'] : null;
                    $userName = $user['name'] ?? ($_SESSION['associate_name'] ?? 'Associate');
                    $size = 'lg';
                    include __DIR__ . '/../shared/profile_photo_upload.php';
                    ?>
                    <h5 class="mt-3"><?php echo htmlspecialchars($user['name'] ?? ($_SESSION['associate_name'] ?? 'Associate')); ?></h5>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email'] ?? ($_SESSION['associate_email'] ?? '')); ?></p>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['phone'] ?? ($_SESSION['associate_phone'] ?? '')); ?></p>
                    <span class="badge bg-primary mt-2"><?= __('assoc_rank_associate', [], 'Associate') ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-edit text-primary me-2"></i><?= __('assoc_profile_details', [], 'Profile Details') ?></h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/associate/profile">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_profile_full_name', [], 'Full Name') ?></label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ($_SESSION['associate_name'] ?? '')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_profile_email', [], 'Email') ?></label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ($_SESSION['associate_email'] ?? '')); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_profile_phone', [], 'Phone') ?></label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ($_SESSION['associate_phone'] ?? '')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><?= __('assoc_profile_member_since', [], 'Member Since') ?></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['created_at'] ?? date('Y-m-d')); ?>" disabled>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i><?= __('assoc_profile_update', [], 'Update Profile') ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
