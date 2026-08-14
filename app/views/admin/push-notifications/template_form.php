<div class="container-fluid py-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications" class="style-75937">Push Notifications</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/push-notifications/templates" class="style-75937">Templates</a></li>
                <li class="breadcrumb-item active" class="style-27277"><?= $template ? 'Edit' : 'Create' ?></li>
            </ol>
        </nav>
        <h1 class="h3 mb-1 fw-bold"><?= $template ? 'Edit Template' : 'Create Template' ?></h1>
        <p class="mb-0" class="style-54585"><?= $template ? 'Update template content and variables' : 'Design a reusable notification template' ?></p>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/push-notifications/templates/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <?php if ($template): ?>
                    <input type="hidden" name="id" value="<?= $template['id'] ?>">
                <?php endif; ?>

                <div class="card border-0 shadow-sm" class="style-52634">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Template Name <span class="style-62247">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($template['name'] ?? '') ?>"
                                   placeholder="e.g. Property Alert, Payment Reminder"
                                   class="style-30479">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Channel</label>
                                <select name="channel" class="form-select"
                                        class="style-30479">
                                    <?php
                                        $channels = ['push' => 'Push Notification', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'];
                                        $current = $template['channel'] ?? 'push';
                                    ?>
                                    <?php foreach ($channels as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" class="style-96443">Subject (Email/SMS)</label>
                                <input type="text" name="subject" class="form-control"
                                       value="<?= htmlspecialchars($template['subject'] ?? '') ?>"
                                       placeholder="Email subject line"
                                       class="style-30479">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Title</label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= htmlspecialchars($template['title'] ?? '') ?>"
                                   placeholder="Notification title (shown in header)"
                                   class="style-30479">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" class="style-96443">Body <span class="style-62247">*</span></label>
                            <textarea name="body" class="form-control" rows="5" required
                                      placeholder="Notification body text. Use {{variable}} for dynamic content."
                                      class="style-30479"><?= htmlspecialchars($template['body'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" class="style-96443">Variables (comma-separated)</label>
                            <input type="text" name="variables" class="form-control"
                                   value="<?= htmlspecialchars($template['variables'] ?? '') ?>"
                                   placeholder="e.g. user_name, property_name, amount"
                                   class="style-30479">
                            <small class="style-54585">Use <code class="style-75937">{{variable_name}}</code> in body to insert dynamic values at send time.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> <?= $template ? 'Update Template' : 'Create Template' ?>
                    </button>
                    <a href="<?= BASE_URL ?>/admin/push-notifications/templates" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" class="style-52634">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" class="style-96443">
                        <i class="fas fa-info-circle me-1" class="style-75937"></i> Template Tips
                    </h6>
                    <ul class="small mb-0" class="style-78105">
                        <li class="mb-2">Templates save time when sending repeated notification types</li>
                        <li class="mb-2">Use <code class="style-75937">{{user_name}}</code> to personalize with recipient's name</li>
                        <li class="mb-2">Variables are replaced at send time from user data</li>
                        <li class="mb-2">Push titles should be under 50 characters</li>
                        <li class="mb-0">Body supports up to 300 characters for push, unlimited for email</li>
                    </ul>
                </div>
            </div>

            <?php if ($template): ?>
                <div class="card border-0 shadow-sm mt-3" class="style-52634">
                    <div class="card-body">
                        <h6 class="fw-bold mb-2" class="style-96443">Preview</h6>
                        <div class="p-3 rounded" class="style-1278">
                            <div class="fw-semibold mb-1" class="style-72530">
                                <?= htmlspecialchars($template['title'] ?? 'No title') ?>
                            </div>
                            <div class="style-37628">
                                <?= nl2br(htmlspecialchars($template['body'] ?? '')) ?>
                            </div>
                        </div>
                        <small class="style-42047">
                            Used <?= (int)($template['usage_count'] ?? 0) ?> times Â· Created <?= date('d M Y', strtotime($template['created_at'] ?? 'now')) ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
