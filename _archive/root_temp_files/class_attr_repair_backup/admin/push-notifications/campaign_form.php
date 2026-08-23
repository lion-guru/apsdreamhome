<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications" class="style-75937">Push Notifications</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications/campaigns" class="style-75937">Campaigns</a></li>
                <li class="breadcrumb-item active" class="style-27277"><?= $campaign ? 'Edit' : 'Create' ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-1 fw-bold"><?= $campaign ? 'Edit Campaign' : 'Create Campaign' ?></h1>
        <p class="mb-0" class="style-54585">Set up targeting, schedule, and message content</p>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error'] ?? ''); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/campaigns/store">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <?php if ($campaign): ?>
            <input type="hidden" name="id" value="<?= $campaign['id'] ?>">
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4" class="style-52634">
                    <div class="card-header" class="style-52852">
                        <h6 class="mb-0 fw-bold" class="style-96443">Campaign Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Campaign Name <span class="style-62247">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($campaign['name'] ?? '') ?>"
                                   placeholder="e.g. Diwali Property Offer, EMI Reminder"
                                   class="style-30479">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Internal note about this campaign"
                                      class="style-30479"><?= htmlspecialchars($campaign['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Notification Title <span class="style-62247">*</span></label>
                            <input type="text" name="title" class="form-control" required maxlength="100"
                                   value="<?= htmlspecialchars($campaign['title'] ?? '') ?>"
                                   placeholder="Push notification title"
                                   class="style-30479">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Notification Body <span class="style-62247">*</span></label>
                            <textarea name="body" class="form-control" rows="4" required maxlength="300"
                                      placeholder="Notification message body"
                                      class="style-30479"><?= htmlspecialchars($campaign['body'] ?? '') ?></textarea>
                            <small class="style-54585">Max 300 characters for push notifications.</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" class="style-52634">
                    <div class="card-header" class="style-52852">
                        <h6 class="mb-0 fw-bold" class="style-96443">Targeting</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Channel</label>
                                <select name="channel" class="form-select"
                                        class="style-30479">
                                    <?php
                                        $channels = ['push' => 'Push Notification', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'all' => 'All Channels'];
                                        $current = $campaign['channel'] ?? 'push';
                                    ?>
                                    <?php foreach ($channels as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Target Type</label>
                                <select name="target_type" class="form-select"
                                        class="style-30479">
                                    <?php
                                        $targets = ['all_users' => 'All Users', 'role' => 'Role-Based', 'segment' => 'Segment', 'individual' => 'Individual User'];
                                        $current = $campaign['target_type'] ?? 'all_users';
                                    ?>
                                    <?php foreach ($targets as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Target Value</label>
                                <div id="targetValueWrapper">
                                    <select name="target_value" id="target_value_select" class="form-select"
                                            class="style-30479">
                                        <option value="">Not applicable for All Users</option>
                                    </select>
                                    <input type="text" name="target_value" id="target_value_input" class="form-control"
                                           value="<?= htmlspecialchars($campaign['target_value'] ?? '') ?>"
                                           placeholder="User ID or segment name"
                                           class="style-14861">
                                </div>
                                <small class="style-54585">Leave empty for "All Users". Enter user ID for individual targeting.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Schedule</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control"
                                       value="<?= !empty($campaign['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($campaign['scheduled_at'])) : '' ?>"
                                       class="style-30479">
                                <small class="style-54585">Leave empty for draft. Set a future time to schedule.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Template (optional)</label>
                                <select name="template_id" class="form-select"
                                        class="style-30479">
                                    <option value="">No template</option>
                                    <?php foreach ($templates as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($campaign['template_id'] ?? '') == $t['id'] ? 'selected' : '' ?>
                                                data-title="<?= htmlspecialchars($t['title'] ?? '') ?>"
                                                data-body="<?= htmlspecialchars($t['body'] ?? '') ?>">
                                            <?= htmlspecialchars($t['name'] ?? '') ?> (<?= strtoupper($t['channel']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3" class="style-52634">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3" class="style-96443">
                            <i class="fas fa-info-circle me-1" class="style-75937"></i> How Campaigns Work
                        </h6>
                        <ul class="small mb-0" class="style-78105">
                            <li class="mb-2">Draft campaigns can be edited and launched later</li>
                            <li class="mb-2">Launching creates queue entries for each target user</li>
                            <li class="mb-2">Queue is processed in batches of 50 per run</li>
                            <li class="mb-2">Failed items are retried up to 3 times</li>
                            <li class="mb-0">Running campaigns can be paused (cancels pending items)</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" class="style-52634">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-save me-1"></i> <?= $campaign ? 'Update Campaign' : 'Save as Draft' ?>
                            </button>
                            <a href="<?= BASE_URL ?>/admin/push-notifications/campaigns" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const targetType = document.querySelector('select[name="target_type"]');
    const roleSelect = document.getElementById('target_value_select');
    const roleInput = document.getElementById('target_value_input');
    const roles = <?= json_encode($roles) ?>;

    function updateTarget() {
        const val = targetType.value;
        if (val === 'role') {
            roleSelect.style.display = '';
            roleInput.style.display = 'none';
            roleSelect.name = 'target_value';
            roleInput.name = 'target_value_disabled';
            roleSelect.innerHTML = '<option value="">Select role...</option>';
            roles.forEach(r => {
                roleSelect.innerHTML += '<option value="' + r + '">' + r.charAt(0).toUpperCase() + r.slice(1) + '</option>';
            });
        } else if (val === 'individual' || val === 'segment') {
            roleSelect.style.display = 'none';
            roleInput.style.display = '';
            roleInput.name = 'target_value';
            roleInput.placeholder = val === 'individual' ? 'Enter user ID' : 'Enter segment name';
        } else {
            roleSelect.style.display = 'none';
            roleInput.style.display = 'none';
            roleSelect.name = 'target_value_disabled';
            roleInput.name = 'target_value_disabled';
        }
    }

    targetType.addEventListener('change', updateTarget);
    updateTarget();

    const templateSelect = document.querySelector('select[name="template_id"]');
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value && opt.dataset.title) {
                document.querySelector('input[name="title"]').value = opt.dataset.title;
                document.querySelector('textarea[name="body"]').value = opt.dataset.body;
            }
        });
    }
})();
</script>
