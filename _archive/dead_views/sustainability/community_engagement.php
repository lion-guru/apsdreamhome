<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Community</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-users me-3 text-success"></i><?= ($page_title ?? 'Community Engagement') ?></h1>
        </div>
    </div>

    <?php $cp = $community_programs ?? []; ?>

    <div class="row g-4">
        <?php foreach ($cp as $key => $prog): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-<?= $key === 'tree_plantation' ? 'tree' : ($key === 'education_workshops' ? 'chalkboard-teacher' : 'briefcase') ?> fa-3x text-success mb-3"></i>
                    <h5><?= ($prog['program'] ?? ucfirst(str_replace('_', ' ', $key))) ?></h5>
                    <hr>
                    <?php if ($key === 'tree_plantation'): ?>
                    <div class="d-flex justify-content-between mb-2"><span>Trees Planted</span><strong><?= number_format($prog['trees_planted'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Participants</span><strong><?= number_format($prog['participants'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>CO2 Offset</span><strong class="text-success"><?= ($prog['carbon_offset'] ?? '') ?></strong></div>
                    <?php elseif ($key === 'education_workshops'): ?>
                    <div class="d-flex justify-content-between mb-2"><span>Workshops</span><strong><?= ($prog['workshops_conducted'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Participants</span><strong><?= number_format($prog['participants'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Improvement</span><strong><?= ($prog['knowledge_improvement'] ?? '0%') ?></strong></div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between mb-2"><span>Participants</span><strong><?= number_format($prog['participants'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Jobs Created</span><strong class="text-success"><?= ($prog['jobs_created'] ?? 0) ?></strong></div>
                    <small class="text-muted">Skills: <?= implode(', ', $prog['skills_developed'] ?? []) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($cp)): ?><div class="col-12"><div class="alert alert-info">No community programs available.</div></div><?php endif; ?>
    </div>
</div>
