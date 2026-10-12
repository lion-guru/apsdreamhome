<?php
/** Associate mini-website / lead magnet — public, no login. */
/** @var array $assoc */
$assoc = $assoc ?? [];
$display_name = $display_name ?? 'Associate';
$colonies = $colonies ?? [];
$saved = !empty($saved);
$base = defined('BASE_URL') ? BASE_URL : '';
$code = $assoc['assoc_code'] ?: ($assoc['user_code'] ?? '');
$phone = preg_replace('/\D/', '', (string)($assoc['user_phone'] ?? ''));
$wa = $phone !== '' ? 'https://wa.me/91' . $phone . '?text=' . urlencode('Hi ' . $display_name . ', I found your page on APS Dream Home. I want to know about plots.') : '';
$initials = implode('', array_map(function ($w) { return mb_substr($w, 0, 1); }, array_slice(preg_split('/\s+/', trim($display_name)), 0, 2)));
$img = trim((string)($assoc['profile_image'] ?? ''));
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="container py-4" style="max-width:860px">
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= $e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>
    <?php if ($saved): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i>Request received! <?= $e($display_name) ?> will call you back shortly.</div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex gap-3 align-items-center flex-wrap">
            <?php if ($img !== ''): ?>
                <img src="<?= $e($img) ?>" alt="<?= $e($display_name) ?>" style="width:96px;height:96px;object-fit:cover;border-radius:50%">
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center text-white fw-bold" style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#6B4EE6,#8B5CF6);font-size:32px"><?= $e(mb_strtoupper($initials)) ?></div>
            <?php endif; ?>
            <div class="flex-grow-1">
                <h3 class="mb-1"><?= $e($display_name) ?></h3>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <span class="badge bg-primary"><?= $e($assoc['level'] ?? 'Associate') ?></span>
                    <span class="badge bg-success"><?= (int)($assoc['total_sales'] ?? 0) ?> happy clients</span>
                    <span class="badge bg-secondary">Team <?= (int)($assoc['team_size'] ?? 0) ?></span>
                </div>
                <div class="text-muted small mt-1">Verified APS Dream Home associate · Ref: <?= $e($code) ?></div>
            </div>
            <div class="d-flex flex-column gap-2">
                <?php if ($wa !== ''): ?>
                <a class="btn btn-success" href="<?= $e($wa) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp me-1"></i>Talk to <?= $e(explode(' ', trim($display_name))[0]) ?></a>
                <?php endif; ?>
                <a class="btn btn-primary" href="<?= $base ?>/p/<?= $e($code) ?>/vcard"><i class="fas fa-address-card me-1"></i>📲 Save to Phone Contacts</a>
            </div>
        </div>
    </div>

    <h5 class="mb-3"><i class="fas fa-city me-2"></i>Township Projects</h5>
    <div class="row g-3 mb-4">
        <?php if (empty($colonies)): ?>
            <div class="col-12"><p class="text-muted">Projects listing soon — send an inquiry below.</p></div>
        <?php else: foreach ($colonies as $c): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title"><?= $e($c['name'] ?? '') ?></h6>
                        <p class="mb-0"><span class="badge bg-<?= ((int)($c['avail'] ?? 0) > 0) ? 'success' : 'secondary' ?>"><?= (int)($c['avail'] ?? 0) ?> plots available</span></p>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5><i class="fas fa-calendar-check me-2"></i>Request a Site Visit</h5>
            <form method="POST" action="<?= $base ?>/p/<?= $e($code) ?>/inquire">
                <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_token'] ?? '') ?>">
                <div class="row g-2">
                    <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Your name *" required maxlength="100"></div>
                    <div class="col-md-4"><input type="tel" name="phone" class="form-control" placeholder="10-digit mobile *" required maxlength="15"></div>
                    <div class="col-md-4"><input type="text" name="interest" class="form-control" placeholder="Interested in (colony / budget)" maxlength="255"></div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-paper-plane me-1"></i>Send Request</button>
            </form>
        </div>
    </div>
</div>
