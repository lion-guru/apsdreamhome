<?php
/**
 * AI Lead Scoring - Powered by Gemini
 */
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($mlSupport->translate('AI Lead Scoring')); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Lead Scoring')); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if (isset($error_msg) && $error_msg): ?>
        <div class="alert alert-danger">
            <?= h($error_msg) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($success_msg) && $success_msg): ?>
        <div class="alert alert-success">
            <?= h($success_msg) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <form method="post" class="d-flex align-items-center gap-3">
                        <input type="hidden" name="csrf_token" value="<?= $this->getCsrfToken() ?>">
                        <button type="submit" name="action" value="score_leads" class="btn btn-primary">
                            <i class="fas fa-magic me-2"></i> <?= h($mlSupport->translate('Score Unprocessed Leads')) ?>
                        </button>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?= h($mlSupport->translate('Scores up to 10 pending leads using Gemini AI')) ?>
                        </small>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h4 class="card-title mb-0"><?= h($mlSupport->translate('Recently Scored Leads')) ?></h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><?= h($mlSupport->translate('Name')) ?></th>
                                    <th><?= h($mlSupport->translate('Status')) ?></th>
                                    <th><?= h($mlSupport->translate('Score')) ?></th>
                                    <th><?= h($mlSupport->translate('AI Summary')) ?></th>
                                    <th><?= h($mlSupport->translate('Action')) ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($scored_leads)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <?= h($mlSupport->translate('No scored leads found.')) ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($scored_leads as $lead): ?>
                                        <tr>
                                            <td><?= h($lead['name']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= h($lead['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $score = $lead['ai_score'];
                                                $badgeClass = 'bg-secondary';
                                                if ($score >= 80) $badgeClass = 'bg-success';
                                                elseif ($score >= 50) $badgeClass = 'bg-warning text-dark';
                                                elseif ($score > 0) $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= h($score) ?>/100</span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= h(substr($lead['ai_summary'] ?? '', 0, 100)) ?>...</small>
                                            </td>
                                            <td>
                                                <a href="/admin/leads/view/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
