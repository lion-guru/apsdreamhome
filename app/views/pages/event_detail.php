<div class="container mt-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>"><?= __('breadcrumb_home') ?></a></li>
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/event-calendar"><?= __('nav_events') ?></a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($event['title'] ?? ''); ?></li>
    </ol></nav>
    <h1><?php echo htmlspecialchars($event['title'] ?? ''); ?></h1>
    <p class="text-muted">
        <i class="fas fa-calendar"></i> <?php echo $event['event_date'] ?? ''; ?>
        <?php if (!empty($event['location'])): ?>| <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?><?php endif; ?>
    </p>
    <div class="mt-4"><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></div>
    <a href="<?php echo BASE_URL; ?>/event-calendar" class="btn btn-secondary mt-3">&larr; <?= __('back_to_events') ?></a>
</div>
