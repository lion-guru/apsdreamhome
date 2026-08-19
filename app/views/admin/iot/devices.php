<?php
$devices = $devices ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? ['page'=>1,'pages'=>1];
$csrf = $_SESSION['csrf_token'] ?? '';
$statuses = ['online'=>'Online','offline'=>'Offline','configuring'=>'Configuring','fault'=>'Fault'];
$cats = ['security'=>'Security','energy'=>'Energy','water'=>'Water','climate'=>'Climate','lighting'=>'Lighting','safety'=>'Safety','access'=>'Access','smart'=>'Smart'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-server me-2 text-primary"></i>IoT Devices</h2>
    <a href="<?= BASE_URL ?>/admin/iot/device/form" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Register Device</a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto"><select name="status" class="form-select"><option value="">All Status</option><?php foreach ($statuses as $k=>$l): ?><option value="<?= $k ?>" <?= ($filters['status']??'')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            <div class="col-auto"><select name="category" class="form-select"><option value="">All Categories</option><?php foreach ($cats as $k=>$l): ?><option value="<?= $k ?>" <?= ($filters['category']??'')===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            <div class="col-auto"><button class="btn btn-outline-secondary"><i class="fas fa-filter"></i> Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($devices)): ?>
            <p class="text-muted text-center py-4">No devices registered.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>UID</th><th>Category</th><th>Location</th><th>Status</th><th>Last Seen</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($devices as $d): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['name'] ?? '') ?></strong><?php if (!empty($d['catalog_name'])): ?><br><small class="text-muted"><?= htmlspecialchars($d['catalog_name'] ?? '') ?></small><?php endif; ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($d['device_uid'] ?? '') ?></small></td>
                            <td><span class="badge bg-light text-dark"><?= ucfirst($d['category'] ?? 'smart') ?></span></td>
                            <td><?= htmlspecialchars($d['location'] ?? '') ?></td>
                            <td><span class="badge bg-<?= match($d['status'] ?? 'offline') { 'online'=>'success','fault'=>'danger','configuring'=>'warning',default=>'secondary' } ?>"><?= ucfirst($d['status'] ?? 'offline') ?></span></td>
                            <td><small><?= !empty($d['last_seen_at']) ? date('M d, H:i', strtotime($d['last_seen_at'])) : '' ?></small></td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/admin/iot/device/<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary" title="Details"><i class="fas fa-chart-line"></i></a>
                                <a href="<?= BASE_URL ?>/admin/iot/device/form/<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= BASE_URL ?>/admin/iot/device/delete/<?= $d['id'] ?>" class="d-inline" data-aps-confirm="Delete device?"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <div class="card-footer"><nav><ul class="pagination justify-content-center mb-0">
        <?php for ($i=1;$i<=$pagination['pages'];$i++): ?><li class="page-item <?= $i===$pagination['page']?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?>
    </ul></nav></div>
    <?php endif; ?>
</div>
