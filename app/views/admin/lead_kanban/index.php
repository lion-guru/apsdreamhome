<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-columns text-primary me-2"></i>Lead Pipeline</h1>
                    <p class="text-muted small mb-0">Drag-and-drop kanban view of all leads across pipeline stages</p>
                </div>
                <div class="col-sm-6 text-end">
                    <span class="badge bg-primary fs-6 me-2"><i class="fas fa-users me-1"></i><?php echo number_format($stats['total']); ?> Total</span>
                    <a href="<?php echo BASE_URL; ?>/admin/leads" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>List View
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/leads/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>New Lead
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="kanban-board" style="display:flex; gap:12px; overflow-x:auto; padding-bottom:12px;">
                <?php foreach ($stages as $stage):
                    $sl = $stageLabels[$stage];
                    $stageLeads = $leadsByStage[$stage] ?? [];
                    $count = count($stageLeads);
                    $stageValue = array_sum(array_map(function($l) { return (float)($l['score'] ?? 0); }, $stageLeads));
                ?>
                    <div class="kanban-column" data-stage="<?php echo htmlspecialchars($stage); ?>" style="min-width: 280px; background: #f4f6f9; border-radius: 8px; padding: 12px;">
                        <div class="kanban-header d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="fas <?php echo $sl['icon']; ?> text-<?php echo $sl['color']; ?> me-1"></i>
                                <?php echo htmlspecialchars($sl['label']); ?>
                            </h6>
                            <span class="badge bg-<?php echo $sl['color']; ?>"><?php echo $count; ?></span>
                        </div>
                        <div class="kanban-value text-muted small mb-2">
                            Value: ₹<?php echo number_format($stageValue / 10); ?>
                        </div>
                        <div class="kanban-items" data-stage="<?php echo htmlspecialchars($stage); ?>" style="min-height: 200px;">
                            <?php foreach ($stageLeads as $lead): ?>
                                <div class="kanban-card" draggable="true" data-id="<?php echo (int)$lead['id']; ?>" style="background: #fff; border-radius: 6px; padding: 10px; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: grab; border-left: 3px solid <?php
                                    $score = (int)($lead['score'] ?? 0);
                                    if ($score >= 80) echo '#10b981';
                                    elseif ($score >= 50) echo '#f59e0b';
                                    else echo '#94a3b8';
                                ?>;">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong class="small"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></strong>
                                        <?php if (($lead['score'] ?? 0) > 0): ?>
                                            <span class="badge bg-<?php echo $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'secondary'); ?>" style="font-size:9px;">
                                                <?php echo (int)$lead['score']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($lead['phone'])): ?>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($lead['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($lead['source'])): ?>
                                        <span class="badge bg-light text-muted border mt-1" style="font-size: 9px;"><?php echo htmlspecialchars($lead['source']); ?></span>
                                    <?php endif; ?>
                                    <div class="text-muted mt-1" style="font-size: 10px;">
                                        <i class="far fa-clock me-1"></i><?php echo date('M j', strtotime($lead['created_at'] ?? 'now')); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($stageLeads)): ?>
                                <div class="text-muted text-center small py-3" style="opacity: 0.5;">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                    Drag leads here
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<style>
.kanban-card:active { cursor: grabbing; opacity: 0.5; }
.kanban-card.dragging { opacity: 0.5; transform: rotate(2deg); }
.kanban-items.drag-over { background: rgba(102, 126, 234, 0.1); border-radius: 6px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-items');
    let draggedCard = null;
    cards.forEach(card => {
        card.addEventListener('dragstart', e => {
            draggedCard = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', e => {
            card.classList.remove('dragging');
        });
    });
    columns.forEach(col => {
        col.addEventListener('dragover', e => {
            e.preventDefault();
            col.classList.add('drag-over');
        });
        col.addEventListener('dragleave', e => {
            col.classList.remove('drag-over');
        });
        col.addEventListener('drop', e => {
            e.preventDefault();
            col.classList.remove('drag-over');
            if (!draggedCard) return;
            const newStage = col.getAttribute('data-stage');
            const leadId = draggedCard.getAttribute('data-id');
            col.appendChild(draggedCard);
            draggedCard = null;
            const formData = new FormData();
            formData.append('lead_id', leadId);
            formData.append('status', newStage);
            fetch('<?php echo BASE_URL; ?>/admin/lead-kanban/update-stage', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        const toast = document.createElement('div');
                        toast.style.cssText = 'position:fixed;top:20px;right:20px;background:#10b981;color:#fff;padding:12px 20px;border-radius:6px;z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,0.2);';
                        toast.innerHTML = '<i class="fas fa-check me-2"></i>Lead moved to ' + newStage.replace('_', ' ');
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 2500);
                    } else {
                        alert('Failed to update: ' + (d.error || 'unknown error'));
                        location.reload();
                    }
                })
                .catch(err => { alert('Network error: ' + err.message); location.reload(); });
        });
    });
});
</script>
