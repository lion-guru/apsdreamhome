<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Events</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-calendar-alt me-3 text-danger"></i><?= ($page_title ?? 'Virtual Events') ?></h1>
        </div>
    </div>

    <?php $upcoming_events = $upcoming_events ?? []; $past_events = $past_events ?? []; ?>

    <h3 class="mb-4"><i class="fas fa-clock me-2 text-success"></i>Upcoming Events</h3>
    <div class="row g-4 mb-5">
        <?php if (empty($upcoming_events)): ?>
        <div class="col-12"><div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No upcoming events.</div></div>
        <?php else: ?>
        <?php foreach ($upcoming_events as $event): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title"><?= ($event['title'] ?? 'Untitled Event') ?></h5>
                        <span class="badge bg-success">Upcoming</span>
                    </div>
                    <p class="card-text text-muted"><?= ($event['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-map-marker-alt me-1"></i><?= ($event['venue'] ?? 'Virtual') ?></span>
                        <span><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($event['date'] ?? 'now')) ?></span>
                        <span><i class="fas fa-user me-1"></i><?= ($event['attendees'] ?? 0) ?>/<?= ($event['max_attendees'] ?? '∞') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h3 class="mb-4"><i class="fas fa-history me-2 text-muted"></i>Past Events</h3>
    <div class="row g-4">
        <?php if (empty($past_events)): ?>
        <div class="col-12"><div class="alert alert-secondary"><i class="fas fa-info-circle me-2"></i>No past events.</div></div>
        <?php else: ?>
        <?php foreach ($past_events as $event): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 opacity-75">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title"><?= ($event['title'] ?? 'Untitled Event') ?></h5>
                        <span class="badge bg-secondary">Past</span>
                    </div>
                    <p class="card-text text-muted"><?= ($event['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-map-marker-alt me-1"></i><?= ($event['venue'] ?? 'Virtual') ?></span>
                        <span><i class="fas fa-calendar me-1"></i><?= date('d M Y', strtotime($event['date'] ?? 'now')) ?></span>
                        <?php if ($event['recordings_available'] ?? false): ?>
                        <span class="text-success"><i class="fas fa-video me-1"></i>Recording Available</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
