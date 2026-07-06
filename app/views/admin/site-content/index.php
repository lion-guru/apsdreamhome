<?php
$page_title = $page_title ?? 'Site Content Manager';
$sections = $sections ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i><?= __('admin_site_content') ?></h1>
    </div>



    <div class="row">
        <?php
        $sectionInfo = [
            'about'   => ['icon' => 'fa-users', 'color' => 'primary', 'desc' => 'Leader names, photos, roles, bios, company stats'],
            'home'    => ['icon' => 'fa-home', 'color' => 'success', 'desc' => 'Hero banner title, subtitle, CTA button text'],
            'footer'  => ['icon' => 'fa-shoe-prints', 'color' => 'info', 'desc' => 'Company name, address, phone, email, tagline'],
            'contact' => ['icon' => 'fa-envelope', 'color' => 'warning', 'desc' => 'Office address, phone, email, map coordinates'],
            'services'=> ['icon' => 'fa-concierge-bell', 'color' => 'secondary', 'desc' => 'Service titles, descriptions, icons'],
        ];
        foreach ($sections as $s):
            $sec = $s['section'];
            $info = $sectionInfo[$sec] ?? ['icon' => 'fa-file', 'color' => 'dark', 'desc' => 'Content for ' . $sec];
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-<?= $info['color'] ?> bg-opacity-10 p-3 me-3">
                            <i class="fas <?= $info['icon'] ?> text-<?= $info['color'] ?> fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= ucfirst($sec) ?></h5>
                            <small class="text-muted"><?= $s['item_count'] ?> items</small>
                        </div>
                    </div>
                    <p class="text-muted small"><?= $info['desc'] ?></p>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="<?= BASE_URL ?>/admin/site-content/edit/<?= $sec ?>" class="btn btn-<?= $info['color'] ?> w-100">
                        <i class="fas fa-edit me-1"></i> Edit Content
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
