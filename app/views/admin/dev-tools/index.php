<?php $pageTitle = 'Developer Tools'; ?>
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Dev Tools</li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white"><i class="fas fa-code"></i> Developer Tools</div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/logs" class="btn btn-outline-secondary w-100 p-3"><i class="fas fa-list-alt"></i> System Logs</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/cache" class="btn btn-outline-secondary w-100 p-3"><i class="fas fa-database"></i> Cache Manager</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/routes" class="btn btn-outline-secondary w-100 p-3"><i class="fas fa-route"></i> Route List</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/phpinfo" class="btn btn-outline-secondary w-100 p-3"><i class="fab fa-php"></i> PHP Info</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/migrations" class="btn btn-outline-secondary w-100 p-3"><i class="fas fa-arrow-up"></i> Migrations</a></div>
                        <div class="col-md-4"><a href="<?= BASE_URL ?>admin/dev-tools/queue" class="btn btn-outline-secondary w-100 p-3"><i class="fas fa-tasks"></i> Queue Manager</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
