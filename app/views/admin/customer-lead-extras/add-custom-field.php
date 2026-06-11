<?php
// Session started by controller
$page_title = 'Add Custom Field';
$page_description = 'Create a new custom field for lead tracking';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Add Custom Field</h1>
            <p class="text-muted">Create a new custom field for lead tracking</p>
            <a href="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields" class="btn btn-outline-primary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to List
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Create New Custom Field</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="POST" action="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Field Name *</label>
                            <input type="text" class="form-control" name="field_name" placeholder="e.g., lead_source" required>
                            <small class="text-muted">Use lowercase letters, numbers, and underscores only. This will be used in the database.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Field Label *</label>
                            <input type="text" class="form-control" name="field_label" placeholder="e.g., Lead Source" required>
                            <small class="text-muted">This is the label that will be shown to users.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Field Type</label>
                            <select class="form-select" name="field_type" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="select">Dropdown (Select)</option>
                                <option value="checkbox">Checkbox</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Field Group</label>
                            <input type="text" class="form-control" name="field_group" placeholder="e.g., Contact Information, Property Details" value="general">
                            <small class="text-muted">Group similar fields together for better organization.</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Default Value</label>
                            <input type="text" class="form-control" name="default_value" placeholder="Default value for new leads">
                            <small class="text-muted">Optional: Set a default value that will be pre-filled for new leads.</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_required" id="is_required">
                                <label class="form-check-label" for="is_required">Required Field</label>
                            </div>
                            <small class="text-muted">If checked, leads must have a value for this field.</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active Field</label>
                            </div>
                            <small class="text-muted">If unchecked, this field will be hidden but data will be preserved.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Validation Rules (Optional)</label>
                            <input type="text" class="form-control" name="validation_rules" placeholder="e.g., required|email|max:100">
                            <small class="text-muted">Leave blank for no validation. Use pipe (|) to separate multiple rules.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" min="0" value="0">
                            <small class="text-muted">Lower numbers appear first. Leave as 0 for default ordering.</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-2"></i> Create Field
                    </button>
                    <a href="<?php echo BASE_URL; ?>/admin/customer-lead/custom-fields" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>