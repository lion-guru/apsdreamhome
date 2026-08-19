<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-robot text-teal"></i> Auto-Reply Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/agentic-ai">Agentic AI</a></li>
                        <li class="breadcrumb-item active">Auto-Reply</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php unset($_SESSION['flash_success']); endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <form method="POST">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="card card-outline card-teal">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-cog"></i> Configuration</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="auto_reply_enabled" name="auto_reply_enabled" <?= !empty($settings['auto_reply_enabled']) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="auto_reply_enabled"><strong>Enable Auto-Reply</strong></label>
                                    </div>
                                    <small class="text-muted">When enabled, the AI will automatically respond to incoming messages.</small>
                                </div>

                                <div class="form-group">
                                    <label>Greeting Message</label>
                                    <textarea name="greeting_message" class="form-control" rows="3" placeholder="Namaste! APS Dream Homes mein aapka swagat hai..."><?= htmlspecialchars($settings['greeting_message'] ?? 'Namaste! APS Dream Homes mein aapka swagat hai. Main aapki kya madad kar sakta hoon?') ?></textarea>
                                    <small class="text-muted">First message sent to new customers.</small>
                                </div>

                                <div class="form-group">
                                    <label>Away Message (After Hours)</label>
                                    <textarea name="away_message" class="form-control" rows="3" placeholder="Abhi hamare agents busy hain..."><?= htmlspecialchars($settings['away_message'] ?? 'Abhi hamare agents busy hain. Ham jald hi aapse sampark karenge.') ?></textarea>
                                    <small class="text-muted">Sent when message received outside business hours.</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Business Hours Start</label>
                                            <input type="time" name="business_hours_start" class="form-control" value="<?= htmlspecialchars($settings['business_hours_start'] ?? '09:00') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Business Hours End</label>
                                            <input type="time" name="business_hours_end" class="form-control" value="<?= htmlspecialchars($settings['business_hours_end'] ?? '19:00') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>AI Model</label>
                                            <select name="ai_model" class="form-control">
                                                <option value="chatgpt" <?= ($settings['ai_model'] ?? '') === 'chatgpt' ? 'selected' : '' ?>>ChatGPT (via SmartAI)</option>
                                                <option value="gemini" <?= ($settings['ai_model'] ?? '') === 'gemini' ? 'selected' : '' ?>>Gemini</option>
                                                <option value="local" <?= ($settings['ai_model'] ?? '') === 'local' ? 'selected' : '' ?>>Local Pattern Matching</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Max Auto-Replies Before Human Handoff</label>
                                            <input type="number" name="max_auto_replies" class="form-control" value="<?= (int)($settings['max_auto_replies'] ?? 5) ?>" min="1" max="20">
                                            <small class="text-muted">After this many AI replies, conversation is flagged for human agent.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-teal"><i class="fas fa-save"></i> Save Settings</button>
                                <a href="<?= BASE_URL ?>/admin/agentic-ai" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4">
                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> How It Works</h3></div>
                        <div class="card-body">
                            <ol class="small mb-0">
                                <li class="mb-2"><strong>Customer messages</strong> via chatbot or WhatsApp</li>
                                <li class="mb-2"><strong>Auto-reply AI</strong> responds using project data + RBAC context</li>
                                <li class="mb-2"><strong>After max replies</strong> — flagged for human agent takeover</li>
                                <li class="mb-2"><strong>Agent logs in</strong> and takes over the conversation</li>
                                <li class="mb-2"><strong>All logged</strong> — full audit trail in database</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-check-circle"></i> Active Channels</h3></div>
                        <div class="card-body">
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-comment-dots text-teal"></i>
                                    <span>Website Chatbot</span>
                                    <span class="badge badge-success ml-auto">Active</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fab fa-whatsapp text-success"></i>
                                    <span>WhatsApp</span>
                                    <span class="badge badge-success ml-auto">Active</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-phone-volume text-info"></i>
                                    <span>SIM Calling</span>
                                    <span class="badge badge-warning ml-auto">Configure</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-teal{color:#0d9488!important}
.btn-teal{background:#0d9488;color:#fff;border-color:#0d9488}
.btn-teal:hover{background:#0f766e;color:#fff}
</style>
