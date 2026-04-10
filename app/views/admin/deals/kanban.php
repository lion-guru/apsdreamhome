<?php
/**
 * Deal Pipeline - Kanban View
 */

$page_title = 'Deal Pipeline - APS Dream Home';
$stageColors = [
    'lead' => 'secondary',
    'qualified' => 'info',
    'proposal' => 'primary',
    'negotiation' => 'warning'
];

$stageIcons = [
    'lead' => 'user',
    'qualified' => 'check-circle',
    'proposal' => 'file-alt',
    'negotiation' => 'handshake'
];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-columns me-2"></i>Deal Pipeline</h1>
            <p class="text-muted">Drag and drop deals to move them through the pipeline</p>
        </div>
        <div class="btn-group">
            <a href="/admin/deals/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Deal
            </a>
            <a href="/admin/deals" class="btn btn-outline-secondary">
                <i class="fas fa-list me-2"></i>List View
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Leads</h6>
                    <h4 class="mb-0"><?= count($deals['lead'] ?? []) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Qualified</h6>
                    <h4 class="mb-0"><?= count($deals['qualified'] ?? []) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Proposal</h6>
                    <h4 class="mb-0"><?= count($deals['proposal'] ?? []) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Negotiation</h6>
                    <h4 class="mb-0"><?= count($deals['negotiation'] ?? []) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Won</h6>
                    <h4 class="mb-0"><?= $stats['won_count'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center p-2">
                    <h6 class="mb-1">Lost</h6>
                    <h4 class="mb-0"><?= $stats['lost_count'] ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="row kanban-board">
        <?php foreach ($stages as $stage):
            if (in_array($stage['id'], ['won', 'lost'])) continue;
            $stageDeals = $deals[$stage['id']] ?? [];
        ?>
        <div class="col-md-3">
            <div class="card kanban-column" data-stage="<?= $stage['id'] ?>">
                <div class="card-header bg-<?= $stage['color'] ?> text-white py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-<?= $stageIcons[$stage['id']] ?? 'circle' ?> me-2"></i><?= $stage['name'] ?></span>
                        <span class="badge bg-white text-<?= $stage['color'] ?>"><?= count($stageDeals) ?></span>
                    </div>
                </div>
                <div class="card-body kanban-dropzone p-2" style="min-height: 400px; background: #f8f9fa;">
                    <?php foreach ($stageDeals as $deal): ?>
                    <div class="card kanban-card mb-2" data-deal-id="<?= $deal['id'] ?>" draggable="true">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1"><?= htmlspecialchars(deal['lead_name'] ?? '') ?></h6>
                            <p class="card-text small text-muted mb-1">
                                <i class="fas fa-home me-1"></i><?= htmlspecialchars($deal['property_title'] ?? 'No property') ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">₹<?= number_format(floatval(deal['deal_value'] ?? 0), 0) ?>L</span>
                                <small class="text-muted"><?= date('M d', strtotime($deal['expected_close_date'] ?? 'now')) ?></small>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted"><i class="fas fa-user me-1"></i><?= htmlspecialchars($deal['assigned_to_name'] ?? 'Unassigned') ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.kanban-card {
    cursor: grab;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.kanban-card.dragging {
    opacity: 0.5;
    cursor: grabbing;
}
.kanban-dropzone.drag-over {
    background: #e9ecef !important;
    border: 2px dashed #6c757d;
}
</style>

<script>
let draggedCard = null;

// Initialize drag and drop
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', function(e) {
        draggedCard = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    card.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        draggedCard = null;
    });
});

document.querySelectorAll('.kanban-dropzone').forEach(zone => {
    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });

    zone.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });

    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');

        if (draggedCard) {
            const dealId = draggedCard.getAttribute('data-deal-id');
            const newStage = this.closest('.kanban-column').getAttribute('data-stage');
            
            // Move card visually
            this.appendChild(draggedCard);
            
            // Update server
            updateDealStage(dealId, newStage);
        }
    });
});

function updateDealStage(dealId, stage) {
    fetch('/admin/deals/' + dealId + '/stage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'stage=' + stage
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update deal stage');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        location.reload();
    });
}
</script>


