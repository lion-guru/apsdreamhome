<?php
$page_title = $page_title ?? 'Site Content Manager';
$sections   = $sections ?? [];

$sectionInfo = [
    'about'   => ['icon' => 'fa-users',           'color' => 'primary',   'desc' => 'Leader names, photos, roles, bios, company stats'],
    'home'    => ['icon' => 'fa-home',             'color' => 'success',   'desc' => 'Hero banner title, subtitle, CTA button text'],
    'footer'  => ['icon' => 'fa-shoe-prints',      'color' => 'info',      'desc' => 'Company name, address, phone, email, tagline'],
    'contact' => ['icon' => 'fa-envelope',         'color' => 'warning',   'desc' => 'Office address, phone, email, map coordinates'],
    'services'=> ['icon' => 'fa-concierge-bell',   'color' => 'secondary', 'desc' => 'Service titles, descriptions, icons'],
    'career'  => ['icon' => 'fa-briefcase',        'color' => 'danger',    'desc' => 'Job listings, open positions, benefits'],
    'gallery' => ['icon' => 'fa-images',           'color' => 'info',      'desc' => 'Photo gallery sections and labels'],
    'awards'  => ['icon' => 'fa-trophy',           'color' => 'warning',   'desc' => 'Awards, certifications, milestones'],
];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-layer-group text-primary me-2"></i><?= __('admin_site_content') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Site Content</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/site-settings" class="btn btn-outline-secondary btn-sm"><i class="fas fa-cog me-1"></i>Site Settings</a>
        <a href="<?= BASE_URL ?>/admin/pages"          class="btn btn-outline-info btn-sm"><i class="fas fa-file-alt me-1"></i>CMS Pages</a>
        <a href="<?= BASE_URL ?>/admin/gallery"         class="btn btn-outline-secondary btn-sm"><i class="fas fa-images me-1"></i>Gallery</a>
    </div>
</div>

<p class="text-muted mb-4">Edit website section content — hero banners, about page, footer info, and more — without touching code.</p>

<!-- Content Section Cards -->
<div class="row g-4">
    <?php if (empty($sections)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3 opacity-50"></i>
                    <h5 class="text-muted">No content sections configured</h5>
                    <p class="text-muted small">Content sections will appear here once they are set up in the database.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($sections as $s):
            $sec  = $s['section'];
            $info = $sectionInfo[$sec] ?? ['icon' => 'fa-file', 'color' => 'dark', 'desc' => 'Content for ' . $sec];
            $count = (int)($s['item_count'] ?? 0);
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px; transition: transform .15s, box-shadow .15s;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 gap-3">
                        <div class="rounded-3 bg-<?= $info['color'] ?> bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                            <i class="fas <?= $info['icon'] ?> text-<?= $info['color'] ?> fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-capitalize"><?= ucfirst($sec) ?></h5>
                            <small class="text-muted"><?= $count ?> item<?= $count !== 1 ? 's' : '' ?></small>
                        </div>
                        <?php if ($count > 0): ?>
                            <span class="badge bg-success ms-auto">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary ms-auto">Empty</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-0"><?= $info['desc'] ?></p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 px-4 pb-4">
                    <a href="<?= BASE_URL ?>/admin/site-content/edit/<?= htmlspecialchars($sec) ?>" class="btn btn-<?= $info['color'] ?> w-100 btn-sm">
                        <i class="fas fa-edit me-2"></i>Edit <?= ucfirst($sec) ?> Content
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
</style>
