<?php $sc = $content ?? []; ?>
<div class="mb-4">
    <h1 class="h3 mb-1">About Page Content</h1>
    <p class="text-muted mb-0">Manage leaders, stats, vision/mission, and registration details</p>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/admin/about-cms/update">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <!-- Leadership Team -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Leadership Team</h5></div>
        <div class="card-body">
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div class="row mb-4 pb-4 <?= $i < 3 ? 'border-bottom' : '' ?>">
                <div class="col-12"><h6 class="text-muted mb-3">Leader <?= $i ?></h6></div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small fw-semibold">Name</label>
                    <input type="text" class="form-control" name="leader_<?= $i ?>_name" value="<?= htmlspecialchars($sc["leader_{$i}"]["leader_{$i}_name"] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small fw-semibold">Role</label>
                    <input type="text" class="form-control" name="leader_<?= $i ?>_role" value="<?= htmlspecialchars($sc["leader_{$i}"]["leader_{$i}_role"] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small fw-semibold">Experience</label>
                    <input type="text" class="form-control" name="leader_<?= $i ?>_exp" value="<?= htmlspecialchars($sc["leader_{$i}"]["leader_{$i}_exp"] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small fw-semibold">Photo</label>
                    <div class="d-flex align-items-center gap-2">
                        <?php $photoVal = $sc["leader_{$i}"]["leader_{$i}_photo"] ?? ''; ?>
                        <?php if (!empty($photoVal)): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($photoVal) ?>" class="rounded" class="style-25739">
                        <?php endif; ?>
                        <input type="text" class="form-control form-control-sm" name="leader_<?= $i ?>_photo" value="<?= htmlspecialchars($photoVal) ?>" placeholder="assets/images/team/...">
                    </div>
                </div>
                <div class="col-12 mb-2">
                    <label class="form-label small fw-semibold">Bio</label>
                    <textarea class="form-control" name="leader_<?= $i ?>_bio" rows="2"><?= htmlspecialchars($sc["leader_{$i}"]["leader_{$i}_bio"] ?? '') ?></textarea>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Stats -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics</h5></div>
        <div class="card-body">
            <div class="row">
                <?php
                $stats = ['stat_properties' => 'Properties Sold', 'stat_families' => 'Happy Families', 'stat_projects' => 'Projects Completed', 'stat_years' => 'Years Experience'];
                foreach ($stats as $key => $label): ?>
                <div class="col-md-3 mb-3">
                    <label class="form-label small fw-semibold"><?= $label ?></label>
                    <input type="text" class="form-control" name="<?= $key ?>" value="<?= htmlspecialchars($sc['stats'][$key] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Vision & Mission -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-eye me-2"></i>Vision & Mission</h5></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Vision</label>
                <textarea class="form-control" name="vision_text" rows="3"><?= htmlspecialchars($sc['vision_mission']['vision_text'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Mission</label>
                <textarea class="form-control" name="mission_text" rows="3"><?= htmlspecialchars($sc['vision_mission']['mission_text'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Registration -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Company Registration</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-semibold">Registration Number</label>
                    <input type="text" class="form-control" name="reg_number" value="<?= htmlspecialchars($sc['registration']['reg_number'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save All Changes</button>
    </div>
</form>
