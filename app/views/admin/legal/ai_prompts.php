<?php
$prompts = $prompts ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-brain me-2 text-primary"></i>AI Prompt Templates</h2>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPromptModal"><i class="fas fa-plus me-1"></i>New Prompt</button>
    </div>

    <?php if (empty($prompts)): ?>
        <div class="text-center text-muted py-5"><i class="fas fa-brain fa-3x mb-3"></i><p>No AI prompts found. Create one to start generating documents with AI.</p></div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($prompts as $p): ?>
                <div class="col-md-6">
                    <div class="aps-cp-card h-100">
                        <div class="aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><i class="fas fa-magic me-2 text-primary"></i><?= htmlspecialchars($p['name'] ?? '') ?></h6>
                                <span class="badge bg-light text-dark"><?= htmlspecialchars($p['document_category'] ?? 'general') ?></span>
                            </div>
                            <p class="small text-muted mb-2"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 150)) ?></p>
                            <div class="small bg-light p-2 rounded mb-2 font-monospace" style="max-height:80px;overflow-y:auto;font-size:11px;"><?= htmlspecialchars(substr($p['prompt_template'] ?? '', 0, 200)) ?>...</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Model: <?= htmlspecialchars($p['model'] ?? 'gemini') ?> | Temp: <?= $p['temperature'] ?? 0.30 ?> | Max: <?= (int)($p['max_tokens'] ?? 2048) ?></small>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPromptModal<?= $p['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/legal/ai-prompts/<?= $p['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Deactivate this prompt?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editPromptModal<?= $p['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/ai-prompts/<?= $p['id'] ?>/update">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <div class="modal-header"><h5 class="modal-title">Edit Prompt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name'] ?? '') ?>" required></div>
                                        <div class="col-md-6"><label class="form-label">Document Category</label><select name="document_category" class="form-select"><option value="">None</option><option value="booking-docs" <?= ($p['document_category'] ?? '') === 'booking-docs' ? 'selected' : '' ?>>Booking Docs</option><option value="associate-agreements" <?= ($p['document_category'] ?? '') === 'associate-agreements' ? 'selected' : '' ?>>Associate Agreements</option><option value="policies-terms" <?= ($p['document_category'] ?? '') === 'policies-terms' ? 'selected' : '' ?>>Policies & Terms</option><option value="colony-documents" <?= ($p['document_category'] ?? '') === 'colony-documents' ? 'selected' : '' ?>>Colony Documents</option><option value="loan-documents" <?= ($p['document_category'] ?? '') === 'loan-documents' ? 'selected' : '' ?>>Loan Documents</option><option value="legal-notices" <?= ($p['document_category'] ?? '') === 'legal-notices' ? 'selected' : '' ?>>Legal Notices</option><option value="forms-applications" <?= ($p['document_category'] ?? '') === 'forms-applications' ? 'selected' : '' ?>>Forms & Applications</option></select></div>
                                        <div class="col-md-6"><label class="form-label">Model</label><select name="model" class="form-select"><option value="gemini" <?= ($p['model'] ?? '') === 'gemini' ? 'selected' : '' ?>>Gemini Flash</option><option value="openai" <?= ($p['model'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option></select></div>
                                        <div class="col-md-3"><label class="form-label">Temperature</label><input type="number" name="temperature" class="form-control" step="0.05" min="0" max="1" value="<?= $p['temperature'] ?? 0.30 ?>"></div>
                                        <div class="col-md-3"><label class="form-label">Max Tokens</label><input type="number" name="max_tokens" class="form-control" value="<?= (int)($p['max_tokens'] ?? 2048) ?>"></div>
                                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>
                                        <div class="col-12"><label class="form-label">Prompt Template</label><textarea name="prompt_template" class="form-control font-monospace" rows="8" required><?= htmlspecialchars($p['prompt_template'] ?? '') ?></textarea></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createPromptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/ai-prompts/create">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header"><h5 class="modal-title">New AI Prompt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required placeholder="e.g. Booking T&C Generator"></div>
                        <div class="col-md-6"><label class="form-label">Document Category</label><select name="document_category" class="form-select"><option value="">None</option><option value="booking-docs">Booking Docs</option><option value="associate-agreements">Associate Agreements</option><option value="policies-terms">Policies & Terms</option><option value="colony-documents">Colony Documents</option><option value="loan-documents">Loan Documents</option><option value="legal-notices">Legal Notices</option><option value="forms-applications">Forms & Applications</option></select></div>
                        <div class="col-md-4"><label class="form-label">Model</label><select name="model" class="form-select"><option value="gemini">Gemini Flash</option></select></div>
                        <div class="col-md-4"><label class="form-label">Temperature</label><input type="number" name="temperature" class="form-control" step="0.05" min="0" max="1" value="0.30"></div>
                        <div class="col-md-4"><label class="form-label">Max Tokens</label><input type="number" name="max_tokens" class="form-control" value="2048"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <div class="col-12"><label class="form-label">Prompt Template (use {{merge_fields}})</label><textarea name="prompt_template" class="form-control font-monospace" rows="8" required placeholder="Generate a... for {{customer_name}}..."></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
