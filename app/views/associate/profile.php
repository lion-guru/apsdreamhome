<?php
$page_title = $page_title ?? 'My Profile - APS Dream Home';
$user = $user ?? [];
?>
<div class="container-fluid px-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-3">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <h5><?php echo htmlspecialchars($user['name'] ?? ($_SESSION['associate_name'] ?? 'Associate')); ?></h5>
                    <p class="text-muted mb-1"><?php echo htmlspecialchars($user['email'] ?? ($_SESSION['associate_email'] ?? '')); ?></p>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($user['phone'] ?? ($_SESSION['associate_phone'] ?? '')); ?></p>
                    <span class="badge bg-primary mt-2">Associate</span>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-edit text-primary me-2"></i>Profile Details</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?php echo BASE_URL; ?>/associate/profile">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ($_SESSION['associate_name'] ?? '')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ($_SESSION['associate_email'] ?? '')); ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ($_SESSION['associate_phone'] ?? '')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['created_at'] ?? date('Y-m-d')); ?>" disabled>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
