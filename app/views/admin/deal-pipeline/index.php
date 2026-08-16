<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Deal Pipeline - Kanban Board</h1>
        <a href="<?= BASE_URL ?>/admin/deal-pipeline/create" class="btn btn-primary"><i class="fas fa-plus"></i> New Deal</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (!empty($stats)): ?>
    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white p-3"><h5>Total Deals</h5><h2><?= $stats['total_deals'] ?? 0 ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>Won</h5><h2><?= $stats['won_deals'] ?? 0 ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white p-3"><h5>Lost</h5><h2><?= $stats['lost_deals'] ?? 0 ?></h2></div></div>
        <div class="col-md-3"><div class="card bg-info text-white p-3"><h5>Total Value</h5><h2>₹<?= number_format($stats['total_value'] ?? 0) ?></h2></div></div>
    </div>
    <?php endif; ?>

    <div class="row" class="style-18288">
        <?php
        $stageLabels = ['lead' => 'Lead', 'qualified' => 'Qualified', 'site_visit' => 'Site Visit', 'negotiation' => 'Negotiation',
            'booking' => 'Booking', 'agreement' => 'Agreement', 'closed_won' => 'Closed Won', 'closed_lost' => 'Closed Lost'];
        $stageColors = ['lead' => 'secondary', 'qualified' => 'info', 'site_visit' => 'primary', 'negotiation' => 'warning',
            'booking' => 'success', 'agreement' => 'dark', 'closed_won' => 'success', 'closed_lost' => 'danger'];
        foreach ($deals as $stage => $stageDeals): ?>
        <div class="card me-3" class="style-59834">
            <div class="card-header bg-<?= $stageColors[$stage] ?? 'secondary' ?> text-white">
                <strong><?= $stageLabels[$stage] ?? ucfirst($stage) ?></strong>
                <span class="badge bg-light text-dark float-end"><?= count($stageDeals) ?></span>
            </div>
            <div class="card-body p-2" class="style-96503">
                <?php foreach ($stageDeals as $deal): ?>
                <div class="card mb-2 border">
                    <div class="card-body p-2">
                        <h6 class="mb-1"><?= htmlspecialchars($deal['deal_number'] ?? '') ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($deal['customer_name'] ?? 'N/A') ?></small><br>
                        <small>₹<?= number_format($deal['deal_value'] ?? 0) ?></small>
                        <span class="badge bg-<?= $deal['priority'] === 'urgent' ? 'danger' : ($deal['priority'] === 'high' ? 'warning' : 'info') ?> float-end">
                            <?= ucfirst($deal['priority'] ?? 'medium') ?>
                        </span>
                        <?php if ($stage !== 'closed_won' && $stage !== 'closed_lost'): ?>
                        <div class="mt-2">
                            <a href="<?= BASE_URL ?>/admin/deal-pipeline/<?= $deal['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($stageDeals)): ?>
                <p class="text-muted text-center small my-3">No deals</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
