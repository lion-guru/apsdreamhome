<?php
/**
 * AI Agent - Standardized Version
 */
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('AI Agent')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('AI Agent')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($message) && $message): ?>
        <div class="alert alert-success">
            <?= h($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                        <button name="action" value="run" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i> <?= h($mlSupport->translate('Run Strategy')) ?>
                        </button>
                        <button name="action" value="report" class="btn btn-secondary">
                            <i class="fas fa-file-alt me-2"></i> <?= h($mlSupport->translate('Generate Report')) ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0"><?= h($mlSupport->translate('Recent Logs')) ?></h4>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($logs)): ?>
                            <li class="list-group-item text-center text-muted"><?= h($mlSupport->translate('No logs found.')) ?></li>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                                <li class="list-group-item small text-muted">
                                    <i class="fas fa-terminal me-2"></i> <?= h($l) ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
