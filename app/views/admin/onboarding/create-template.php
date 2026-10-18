<?php
/**
 * Onboarding Template Create/Edit
*/

$page_title = $page_title ?? ($mode === 'edit' ? 'Edit Onboarding Template' : 'Create Onboarding Template');
$mode = $mode ?? 'create';
$template = $template ?? ['name' => '', 'description' => '', 'items' => []];
$categories = ['document', 'access', 'training', 'equipment', 'compliance', 'other'];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.item-row { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; background: #fafafa; transition: all 0.2s; }
.item-row:hover { border-color: #7c2d12; background: #fefcf6; }
.item-row .drag-handle { cursor: grab; color: #7c2d12; }
.item-row.dragging { opacity: 0.5; background: #fff3e0; }
.item-row .category-badge { font-size: 0.65rem; padding: 0.15rem 0.5rem; border-radius: 20px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i><?= $page_title ?></h4>
            <p class="text-muted mb-0 small">Create a reusable onboarding template with checklist items</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/onboarding" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Onboarding
        </a>
    </div>

    <form method="POST" id="templateForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
        <?php endif; ?>

        <!-- Basic Info -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Template Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($template['name'] ?? '') ?>" placeholder="e.g., Standard Employee Onboarding">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <option value="general" <?= ($template['category'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                            <option value="technical" <?= ($template['category'] ?? '') === 'technical' ? 'selected' : '' ?>>Technical</option>
                            <option value="sales" <?= ($template['category'] ?? '') === 'sales' ? 'selected' : '' ?>>Sales</option>
                            <option value="hr" <?= ($template['category'] ?? '') === 'hr' ? 'selected' : '' ?>>HR</option>
                            <option value="custom" <?= ($template['category'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe this template..."><?= htmlspecialchars($template['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checklist Items -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-list-check me-2"></i>Checklist Items</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <div id="itemsContainer">
                    <?php 
                    $items = $template['items'] ?? [];
                    if (empty($items)) {
                        $items = [['item_name' => '', 'description' => '', 'category' => 'document', 'is_mandatory' => true, 'assigned_to_role' => '', 'due_days' => '']];
                    }
                    foreach ($items as $index => $item): 
                    ?>
                    <div class="item-row" data-index="<?= $index ?>">
                        <div class="row g-3 align-items-end">
                            <div class="col-auto">
                                <div class="drag-handle p-2 text-muted" title="Drag to reorder">
                                    <i class="fas fa-grip-lines"></i>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="items[<?= $index ?>][item_name]" class="form-control" required value="<?= htmlspecialchars($item['item_name'] ?? '') ?>" placeholder="e.g., Collect signed offer letter">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Category</label>
                                <select name="items[<?= $index ?>][category]" class="form-select form-select-sm">
                                    <?php foreach (['document' => 'Document', 'access' => 'Access', 'training' => 'Training', 'equipment' => 'Equipment', 'compliance' => 'Compliance', 'other' => 'Other'] as $val => $label): ?>
                                        <option value="<?= $val ?>" <?= ($item['category'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Due (days)</label>
                                <input type="number" min="0" max="365" name="items[<?= $index ?>][due_days]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['due_days'] ?? '') ?>" placeholder="Days after start">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Assigned Role</label>
                                <input type="text" name="items[<?= $index ?>][assigned_to_role]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['assigned_to_role'] ?? '') ?>" placeholder="e.g., HR, IT, Manager">
                            </div>
                            <div class="col-md-1">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" name="items[<?= $index ?>][is_mandatory]" value="1" id="mandatory_<?= $index ?>" <?= !empty($item['is_mandatory']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="mandatory_<?= $index ?>">Mandatory</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea name="items[<?= $index ?>][description]" class="form-control form-control-sm" rows="2" placeholder="Optional details..."><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                    <i class="fas fa-trash me-1"></i> Remove
                                </button>
                            </div>
                            <hr class="my-2">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= BASE_URL ?>/admin/onboarding" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-1"></i> Save Template
            </button>
        </div>
    </form>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
let itemIndex = <?= count($template['items'] ?? []) ?>;

function addItem() {
    const container = document.getElementById('itemsContainer');
    const index = itemIndex++;
    const html = `
        <div class="item-row" data-index="${index}">
            <div class="row g-3 align-items-end">
                <div class="col-auto">
                    <div class="drag-handle p-2 text-muted" title="Drag to reorder">
                        <i class="fas fa-grip-lines"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[${index}][item_name]" class="form-control" required placeholder="e.g., Collect signed offer letter">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select name="items[${index}][category]" class="form-select form-select-sm">
                        <option value="document">Document</option>
                        <option value="access">Access</option>
                        <option value="training">Training</option>
                        <option value="equipment">Equipment</option>
                        <option value="compliance">Compliance</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Due (days)</label>
                    <input type="number" min="0" max="365" name="items[${index}][due_days]" class="form-control form-control-sm" placeholder="Days after start">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Assigned Role</label>
                    <input type="text" name="items[${index}][assigned_to_role]" class="form-control form-control-sm" placeholder="e.g., HR, IT">
                </div>
                <div class="col-md-1">
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="items[${index}][is_mandatory]" value="1" id="mandatory_${index}" checked>
                        <label class="form-check-label small" for="mandatory_${index}">Mandatory</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small">Description</label>
                    <textarea name="items[${index}][description]" class="form-control form-control-sm" rows="2" placeholder="Optional details..."></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                        <i class="fas fa-trash me-1"></i> Remove
                    </button>
                </div>
                <hr class="my-2">
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    }

    function removeItem(btn) {
        if (confirm('Remove this item?')) {
            btn.closest('.item-row').remove();
        }
    }

    // Form validation
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
</script>