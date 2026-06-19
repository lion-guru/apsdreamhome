<div class="container mt-4">
    <h1 class="mb-4"><?php echo $page_title ?? __('events_title'); ?></h1>
    <?php if (!empty($events)): ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body aps-cp-card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title'] ?? ''); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-calendar"></i> <?php echo $event['event_date'] ?? ''; ?><br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location'] ?? ''); ?>
                            </p>
                            <p class="card-text"><?php echo htmlspecialchars(substr($event['description'] ?? '', 0, 150)); ?></p>
                            <a href="<?php echo BASE_URL; ?>/event-calendar/<?php echo $event['id']; ?>" class="btn btn-outline-primary"><?= __('featured_view_details') ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info"><?= __('events_empty') ?></div>
    <?php endif; ?>
</div>
