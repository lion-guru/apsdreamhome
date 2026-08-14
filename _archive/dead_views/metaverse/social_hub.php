<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Social Hub</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-comments me-3 text-info"></i><?= ($page_title ?? 'Social Hub') ?></h1>
        </div>
    </div>

    <?php $active_users = $active_users ?? []; $social_activities = $social_activities ?? []; $user_avatars = $user_avatars ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-rss me-2 text-info"></i>Activity Feed</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($social_activities)): ?>
                    <p class="text-muted text-center py-3">No recent activity.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($social_activities as $activity): ?>
                        <li class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php $icon = ($activity['type'] ?? '') === 'property_view' ? 'fa-eye' : (($activity['type'] ?? '') === 'space_joined' ? 'fa-sign-in-alt' : 'fa-shopping-cart'); ?>
                                    <i class="fas <?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> fa-2x text-info opacity-75 me-3"></i>
                                </div>
                                <div>
                                    <strong><?= ($activity['user'] ?? 'Someone') ?></strong>
                                    <?php if (($activity['type'] ?? '') === 'property_view'): ?>
                                        viewed <a href="#"><?= ($activity['property'] ?? 'a property') ?></a>
                                    <?php elseif (($activity['type'] ?? '') === 'space_joined'): ?>
                                        joined <a href="#"><?= ($activity['space'] ?? 'a space') ?></a>
                                    <?php else: ?>
                                        purchased <?= ($activity['item'] ?? 'an item') ?>
                                    <?php endif; ?>
                                    <small class="d-block text-muted"><?= ($activity['timestamp'] ?? 'Just now') ?></small>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-smile me-2 text-info"></i>Available Avatars</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php foreach ($user_avatars as $key => $avatar): ?>
                        <div class="col-md-3 text-center">
                             src="<?= BASE_URL ? loading="lazy">/<?= htmlspecialchars($avatar['preview'] ?? 'assets/img/avatar.png') ?>" alt="" class="rounded-circle mb-2 border" class="style-10719" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/hero.svg'">
                            <h6 class="small"><?= ($avatar['name'] ?? $key) ?></h6>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="fas fa-user-friends me-2 text-success"></i>Active Users (<?= count($active_users) ?>)</h5>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (empty($active_users)): ?>
                    <p class="text-muted text-center py-3">No users online.</p>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($active_users as $user): ?>
                        <li class="list-group-item px-0 d-flex align-items-center">
                            <span class="position-relative">
                                 src="<?= BASE_URL ? loading="lazy">/<?= htmlspecialchars($user['avatar'] ?? 'assets/img/avatar.png') ?>" alt="" class="rounded-circle me-3" class="style-25739" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/hero.svg'">
                                <span class="position-absolute bottom-0 start-50 translate-middle p-1 bg-success border border-light rounded-circle"><span class="visually-hidden">Online</span></span>
                            </span>
                            <div>
                                <strong><?= ($user['name'] ?? 'User') ?></strong><br>
                                <small class="text-muted"><?= ($user['location'] ?? '') ?> &middot; <?= ($user['status'] ?? '') ?></small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
