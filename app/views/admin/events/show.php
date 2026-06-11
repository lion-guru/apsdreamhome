<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title'] ?? 'Event Details'); ?></h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>/admin/events/list/<?php echo $event['id'] ?? ''; ?>/edit" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                        <a href="<?php echo BASE_URL; ?>/admin/events/list" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3"><strong>Date:</strong> <?php echo $event['event_date'] ?? '-'; ?></div>
                    <div class="mb-3"><strong>Location:</strong> <?php echo htmlspecialchars($event['location'] ?? '-'); ?></div>
                    <div class="mb-3"><strong>Status:</strong>
                        <?php if (($event['status'] ?? '') == 'active'): ?><span class="badge bg-success">Active</span>
                        <?php else: ?><span class="badge bg-secondary">Inactive</span><?php endif; ?>
                    </div>
                    <hr>
                    <div><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
