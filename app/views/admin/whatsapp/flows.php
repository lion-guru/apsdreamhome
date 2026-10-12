<?php
/**
 * Admin WhatsApp Chatbot Flow Builder
 */
$page_title = $page_title ?? 'WhatsApp Chatbot Flows';
$flows = $flows ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Chatbot Flow Builder
            </h4>
            <small class="text-muted">Configure interactive keywords, quick replies, and automated real estate workflows.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/whatsapp/logs" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-stream me-1"></i>Live Chat Logs
            </a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#newFlowModal">
                <i class="fas fa-plus me-1"></i>New Flow Rule
            </button>
        </div>
    </div>

    <!-- Active Flows Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Priority</th>
                        <th>Flow Title</th>
                        <th>Triggers / Keywords</th>
                        <th>Match Type</th>
                        <th>Action Hook</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($flows)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No flows configured. Add a flow to handle customer inquiries automatically.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($flows as $f): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border">#<?= (int)$f['priority'] ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($f['title']) ?></div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 320px;">
                                        <?= htmlspecialchars(mb_substr($f['reply_message'], 0, 70)) ?>...
                                    </small>
                                </td>
                                <td>
                                    <?php
                                    $kws = explode(',', $f['trigger_keyword']);
                                    foreach (array_slice($kws, 0, 4) as $kw):
                                    ?>
                                        <span class="badge bg-secondary me-1"><?= htmlspecialchars(trim($kw)) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($kws) > 4): ?>
                                        <small class="text-muted">+<?= count($kws) - 4 ?> more</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark font-monospace"><?= htmlspecialchars($f['match_type']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($f['action_hook'])): ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($f['action_hook']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="/admin/whatsapp/flows/toggle/<?= (int)$f['id'] ?>" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                            <?php if ($f['is_active']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-pause me-1"></i>Paused</span>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/admin/whatsapp/flows/delete/<?= (int)$f['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this flow rule?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Flow">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New Flow -->
<div class="modal fade" id="newFlowModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/admin/whatsapp/flows/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fab fa-whatsapp text-success me-2"></i>Create WhatsApp Bot Flow Rule
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Flow Title</label>
                        <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Price Revision Inquiry" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Execution Priority</label>
                        <input type="number" name="priority" class="form-control form-control-sm" value="50">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Trigger Keywords (Comma separated)</label>
                        <input type="text" name="trigger_keyword" class="form-control form-control-sm" placeholder="e.g. price, cost, discount, offer" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Match Logic</label>
                        <select name="match_type" class="form-select form-select-sm">
                            <option value="contains">Contains Any Keyword</option>
                            <option value="exact">Exact Match Only</option>
                            <option value="regex">Regex Expression</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Dynamic Action Hook</label>
                        <select name="action_hook" class="form-select form-select-sm">
                            <option value="">None (Static Reply Only)</option>
                            <option value="fetch_plots">Inject Available Townships List</option>
                            <option value="fetch_passbook">Lookup Customer Active Booking</option>
                            <option value="book_visit">Register Site Visit Lead</option>
                            <option value="create_lead">Register Associate Lead</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">WhatsApp Reply Body (*bold*, _italic_, {{base_url}})</label>
                        <textarea name="reply_message" class="form-control" rows="4" placeholder="Namaste! Our current township offers are..." required></textarea>
                        <small class="text-muted">Use <code>{{base_url}}</code> to automatically insert your website URL.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">
                        <i class="fas fa-save me-1"></i>Save Flow
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
