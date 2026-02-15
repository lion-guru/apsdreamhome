<?php
// Extends layouts/base.php usually
?>

<main class="user-main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0 text-gray-800">Notifications</h2>
                    <form action="<?= BASE_URL ?>notifications" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Mark all as read
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
        <?php endif; ?>

        <!-- Notifications List -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">No notifications yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $note): ?>
                            <div class="list-group-item list-group-item-action p-4 border-0 <?= $note['is_read'] ? '' : 'bg-light' ?>">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <?php
                                        $icon = 'fa-bell';
                                        $color = 'primary';
                                        switch($note['type']) {
                                            case 'payment': $icon = 'fa-receipt'; $color = 'success'; break;
                                            case 'booking': $icon = 'fa-calendar-check'; $color = 'info'; break;
                                            case 'alert': $icon = 'fa-exclamation-triangle'; $color = 'warning'; break;
                                        }
                                        ?>
                                        <div class="bg-<?= $color ?>-subtle text-<?= $color ?> p-3 rounded-circle">
                                            <i class="fas <?= $icon ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="fw-bold mb-0 text-dark"><?= h($note['title']) ?></h6>
                                            <small class="text-muted"><?= date('d M, h:i A', strtotime($note['created_at'])) ?></small>
                                        </div>
                                        <p class="mb-0 text-secondary"><?= h($note['message']) ?></p>
                                        <?php if (!empty($note['action_url'])): ?>
                                            <a href="<?= h($note['action_url']) ?>" class="btn btn-link btn-sm p-0 mt-2 text-decoration-none">
                                                View Details <i class="fas fa-chevron-right ms-1 small"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!$note['is_read']): ?>
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-primary rounded-circle p-1"><span class="visually-hidden">New</span></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
