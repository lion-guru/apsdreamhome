<?php
$page_title = $page_title ?? 'Notification Preferences - APS Dream Home';
$current_page = $current_page ?? 'settings';
$user = $user ?? [];
$prefs = $prefs ?? [];
$types = $types ?? [];
$channels = $channels ?? ['email', 'sms', 'whatsapp', 'push'];
$flash_success = $flash_success ?? '';
$flash_error = $flash_error ?? '';
$csrf_token = $csrf_token ?? '';

$channelMeta = [
    'email'    => ['label' => 'Email',    'icon' => 'fas fa-envelope',          'color' => 'primary'],
    'sms'      => ['label' => 'SMS',      'icon' => 'fas fa-mobile-alt',        'color' => 'info'],
    'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp',          'color' => 'success'],
    'push'     => ['label' => 'Push',     'icon' => 'fas fa-bell',              'color' => 'warning'],
];

$frequency = $prefs['booking']['frequency'] ?? 'immediate';
$frequencyOptions = [
    'immediate' => __('notif_freq_immediate', null, 'Immediate'),
    'hourly'    => __('notif_freq_hourly', null, 'Hourly Digest'),
    'daily'     => __('notif_freq_daily', null, 'Daily Digest'),
    'weekly'    => __('notif_freq_weekly', null, 'Weekly Digest'),
    'never'     => __('notif_freq_never', null, 'Never'),
];
?>

<?php if ($flash_success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash_success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($flash_error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($flash_error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1"><i class="fas fa-bell text-primary me-2"></i><?= __('notif_page_title', null, 'Notification Preferences') ?></h4>
                <p class="text-muted mb-0 small"><?= __('notif_page_subtitle', null, 'Choose how and when you want to be notified for each type of activity.') ?></p>
            </div>
            <span class="badge bg-light text-dark border">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'Customer') ?>
            </span>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/user/notification-preferences" id="notif-prefs-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

        <div class="card-body aps-cp-card-body">
            <!-- Communication Channels summary -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-broadcast-tower me-2 text-info"></i><?= __('notif_channels_title', null, 'Communication Channels') ?></h5>
                <p class="text-muted small mb-3"><?= __('notif_channels_desc', null, "Pick the delivery channels you'd like to receive notifications on. All channels are enabled by default - toggle any off to opt out.") ?></p>
                <div class="row g-3">
                    <?php foreach ($channels as $ch):
                        $meta = $channelMeta[$ch] ?? ['label' => ucfirst($ch), 'icon' => 'fas fa-circle', 'color' => 'secondary'];
                        // A channel is "on" by default if every type has it enabled - if the user has not yet saved, default is on.
                        $anyOn = false;
                        $allOn = true;
                        foreach (array_keys($types) as $t) {
                            if (!empty($prefs[$t][$ch])) $anyOn = true;
                            else $allOn = false;
                        }
                        $isOn = $allOn || ($anyOn && count($prefs) === 0);
                    ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 border-<?= $meta['color'] ?> border-opacity-25">
                            <div class="card-body text-center py-3">
                                <div class="mb-2">
                                    <i class="<?= $meta['icon'] ?> fa-2x text-<?= $meta['color'] ?>"></i>
                                </div>
                                <h6 class="mb-0"><?= htmlspecialchars($meta['label']) ?></h6>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="my-4">

            <!-- Notification Types matrix -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-list-check me-2 text-success"></i><?= __('notif_types_title', null, 'Notification Types') ?></h5>
                <p class="text-muted small mb-3"><?= __('notif_types_desc', null, 'For each notification type, toggle the channels you want to receive it on.') ?></p>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="style-85607"><?= __('notif_th_type', null, 'Notification Type') ?></th>
                                <?php foreach ($channels as $ch):
                                    $meta = $channelMeta[$ch] ?? ['label' => ucfirst($ch), 'icon' => 'fas fa-circle'];
                                ?>
                                <th class="text-center" class="style-94101">
                                    <i class="<?= $meta['icon'] ?>"></i><br>
                                    <small><?= htmlspecialchars($meta['label']) ?></small>
                                </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($types as $type => $meta):
                                $p = $prefs[$type] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($meta[0]) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($meta[1]) ?></small>
                                </td>
                                <?php foreach ($channels as $ch): ?>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input
                                            class="form-check-input notif-toggle"
                                            type="checkbox"
                                            name="channels[<?= htmlspecialchars($type) ?>][]"
                                            value="<?= htmlspecialchars($ch) ?>"
                                            id="toggle_<?= htmlspecialchars($type) ?>_<?= htmlspecialchars($ch) ?>"
                                            <?= !empty($p[$ch]) ? 'checked' : '' ?>
                                            aria-label="<?= htmlspecialchars($meta[0]) ?> via <?= htmlspecialchars($ch) ?>"
                                        >
                                    </div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="my-4">

            <!-- Delivery Frequency -->
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-clock me-2 text-warning"></i><?= __('notif_freq_title', null, 'Delivery Frequency') ?></h5>
                    <p class="text-muted small mb-2"><?= __('notif_freq_desc', null, 'How often would you like non-urgent notifications grouped together?') ?></p>
                    <select name="frequency" class="form-select" id="frequency-select">
                        <?php foreach ($frequencyOptions as $val => $label): ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $frequency === $val ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block mt-1">
                        <i class="fas fa-info-circle"></i>
                        <?= __('notif_freq_help', null, '"Immediate" sends each notification right away. Digest modes bundle notifications into a single message.') ?>
                    </small>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-shield-alt me-2 text-success"></i><?= __('notif_privacy_title', null, 'Your Privacy') ?></h5>
                    <ul class="small text-muted mb-0 ps-3">
                        <li><?= __('notif_privacy_share', null, 'We never share your contact details with third parties.') ?></li>
                        <li><?= __('notif_privacy_inapp', null, 'In-app notifications are always delivered regardless of these settings.') ?></li>
                        <li><?= __('notif_privacy_anytime', null, 'You can change these preferences at any time.') ?></li>
                        <li><?= __('notif_privacy_security', null, 'Critical security alerts (e.g. password changes) cannot be disabled.') ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
            <a href="<?= BASE_URL ?>/user/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i><?= __('notif_back_dashboard', null, 'Back to Dashboard') ?>
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning" id="reset-defaults">
                    <i class="fas fa-undo me-1"></i><?= __('notif_btn_reset', null, 'Reset to Defaults') ?>
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i><?= __('notif_btn_save', null, 'Save Preferences') ?>
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.notif-toggle { cursor: pointer; width: 2.5em; height: 1.25em; }
.card-footer { border-top: 1px solid #e2e8f0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const resetBtn = document.getElementById('reset-defaults');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (!confirm('Reset all notification preferences to their defaults (all channels enabled)?')) {
                return;
            }
            document.querySelectorAll('.notif-toggle').forEach(function (el) {
                el.checked = true;
            });
            const freq = document.getElementById('frequency-select');
            if (freq) freq.value = 'immediate';
        });
    }

    const form = document.getElementById('notif-prefs-form');
    if (form) {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            }
        });
    }
});
</script>
