<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Education</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-graduation-cap me-3 text-success"></i><?= ($page_title ?? 'Sustainability Education') ?></h1>
        </div>
    </div>

    <?php $programs = $education_programs ?? []; ?>

    <div class="row g-4">
        <?php foreach ($programs as $key => $prog): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-<?= $key === 'employee_training' ? 'users' : ($key === 'customer_education' ? 'user-graduate' : 'industry') ?> fa-3x text-primary mb-3"></i>
                    <h5><?= ($prog['program'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Participants</span><strong><?= number_format($prog['participants'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Completion</span><strong class="text-success"><?= ($prog['completion_rate'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Improvement</span><strong><?= ($prog['knowledge_improvement'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Behavior Change</span><strong class="text-success"><?= ($prog['behavior_change'] ?? '0%') ?></strong></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($programs)): ?><div class="col-12"><div class="alert alert-info">No education programs available.</div></div><?php endif; ?>
    </div>
</div>
