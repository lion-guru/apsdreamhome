<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Events</h5>
                    <a href="<?php echo BASE_URL; ?>/admin/events/list/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Event
                    </a>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events ?? [] as $event): ?>
                                <tr>
                                    <td><?php echo e($event['id']); ?></td>
                                    <td><?php echo htmlspecialchars($event['title'] ?? ''); ?></td>
                                    <td><?php echo $event['event_date'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($event['location'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (($event['status'] ?? '') == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/events/list/<?php echo e($event['id']); ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        <a href="<?php echo BASE_URL; ?>/admin/events/list/<?php echo e($event['id']); ?>/edit" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($events ?? [])): ?>
                                <tr><td colspan="6" class="text-center">No events found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
