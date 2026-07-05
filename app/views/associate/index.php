<?php $pageTitle = __('assoc_index_title'); ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i><?= __('assoc_home') ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= __('assoc_index_title') ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-handshake me-2"></i><?= __('assoc_index_title') ?></h4>
        <a href="<?= BASE_URL ?>/associate/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i><?= __('assoc_index_add') ?></a>
    </div>
    <?php if (!empty($users)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_index_th_hash') ?></th>
                            <th><?= __('assoc_index_th_name') ?></th>
                            <th><?= __('assoc_index_th_email') ?></th>
                            <th><?= __('assoc_index_th_phone') ?></th>
                            <th><?= __('assoc_index_th_properties') ?></th>
                            <th><?= __('assoc_index_th_status') ?></th>
                            <th><?= __('assoc_index_th_joined') ?></th>
                            <th><?= __('assoc_index_th_actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><a href="<?= BASE_URL ?>/associate/show/<?= $a['id'] ?>"><?= htmlspecialchars($a['name'] ?? '') ?></a></td>
                            <td><?= htmlspecialchars($a['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($a['phone'] ?? '') ?></td>
                            <td><?= $a['property_count'] ?? 0 ?></td>
                            <td><span class="badge bg-<?= ($a['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($a['status'] ?? 'active') ?></span></td>
                            <td class="small"><?= htmlspecialchars($a['created_at'] ?? '') ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/associate/show/<?= $a['id'] ?>" class="btn btn-sm btn-outline-info" title="<?= __('assoc_index_view_title') ?>"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/associate/edit/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="<?= __('assoc_index_edit_title') ?>"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
            <h5 class="text-muted"><?= __('assoc_index_no_users') ?></h5>
            <p class="text-muted mb-3"><?= __('assoc_index_start_adding') ?></p>
            <a href="<?= BASE_URL ?>/associate/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('assoc_index_add_first') ?></a>
        </div>
    </div>
    <?php endif; ?>
</div>
