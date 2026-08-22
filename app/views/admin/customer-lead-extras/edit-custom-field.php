<?php
// Session started by controller
$page_title = 'Edit Custom Field';
$page_description = 'Edit existing custom field for lead tracking';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Edit Custom Field</h1>
            <p class="text-muted">Edit existing custom field for lead tracking</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <?php if ($customField): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Edit Custom Field</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields/update/<?php echo e($customField['id']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Field Name *</label>
                                <input type="text" class="form-control" name="field_name" value="<?php echo htmlspecialchars($customField['field_name'] ?? ''); ?>" required>
                                <small class="text-muted">Use lowercase letters, numbers, and underscores only. This will be used in the database.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Field Label *</label>
                                <input type="text" class="form-control" name="field_label" value="<?php echo htmlspecialchars($customField['field_label'] ?? ''); ?>" required>
                                <small class="text-muted">This is the label that will be shown to users.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Field Type</label>
                                <select class="form-select" name="field_type" required>
                                    <option value="text" <?php echo $customField['field_type'] === 'text' ? 'selected' : ''; ?>>Text</option>
                                    <option value="number" <?php echo $customField['field_type'] === 'number' ? 'selected' : ''; ?>>Number</option>
                                    <option value="date" <?php echo $customField['field_type'] === 'date' ? 'selected' : ''; ?>>Date</option>
                                    <option value="select" <?php echo $customField['field_type'] === 'select' ? 'selected' : ''; ?>>Dropdown (Select)</option>
                                    <option value="checkbox" <?php echo $customField['field_type'] === 'checkbox' ? 'selected' : ''; ?>>Checkbox</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Field Group</label>
                                <input type="text" class="form-control" name="field_group" value="<?php echo htmlspecialchars($customField['field_group'] ?? 'general'); ?>">
                                <small class="text-muted">Group similar fields together for better organization.</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Default Value</label>
                                <input type="text" class="form-control" name="default_value" value="<?php echo htmlspecialchars($customField['default_value'] ?? ''); ?>">
                                <small class="text-muted">Optional: Set a default value that will be pre-filled for new leads.</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_required" id="is_required" <?php echo $customField['is_required'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_required">Required Field</label>
                                </div>
                                <small class="text-muted">If checked, leads must have a value for this field.</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" <?php echo $customField['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active Field</label>
                                </div>
                                <small class="text-muted">If unchecked, this field will be hidden but data will be preserved.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Validation Rules (Optional)</label>
                                <input type="text" class="form-control" name="validation_rules" value="<?php echo htmlspecialchars($customField['validation_rules'] ?? ''); ?>">
                                <small class="text-muted">Leave blank for no validation. Use pipe (|) to separate multiple rules.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" min="0" value="<?php echo (int)$customField['sort_order']; ?>">
                                <small class="text-muted">Lower numbers appear first. Leave as 0 for default ordering.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i> Update Field
                        </button>
                        <a href="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <h4>Custom Field Not Found</h4>
            <p>The requested custom field could not be found.</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    <?php endif; ?>
</div>