<?php
$page_title = $page_title ?? 'Visit Confirmed';
$page_heading = $page_heading ?? 'Visit Confirmed';
$content = $content ?? '';
$visit = $visit ?? null;
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="display-1 text-success mb-3"><i class="fas fa-check-circle"></i></div>
                    <h1 class="mb-3">Visit Booked Successfully!</h1>
                    <p class="lead text-muted mb-4">Your site visit is confirmed. We'll contact you shortly to verify the details.</p>

                    <?php if ($visit): ?>
                        <div class="card bg-light mb-4">
                            <div class="card-body aps-cp-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Visit ID</small>
                                        <strong>#<?= $visit['id'] ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Property</small>
                                        <strong><?= htmlspecialchars($visit['property_title'] ?? 'Property') ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Date & Time</small>
                                        <strong><?= date('D, M j Y', strtotime($visit['visit_date'])) ?> at <?= date('h:i A', strtotime($visit['visit_time'])) ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Type</small>
                                        <strong><?= ucfirst(str_replace('_', ' ', $visit['visit_type'])) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="<?= BASE_URL ?>/visit/my-visits" class="btn btn-primary"><i class="fas fa-list me-1"></i> My Visits</a>
                        <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> Browse More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>