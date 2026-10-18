<?php
/**
 * Create IVR Flow
 * S347
 */
$page_title = $page_title ?? 'Create IVR Flow';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-plus me-2 text-primary"></i>Create IVR Flow</h4>
            <a href="/admin/ivr-flows" class="btn btn-secondary">Back to Flows</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Flow Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/ivr-flows/store">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Flow Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Welcome IVR, Sales Qualification, Support Routing">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Describe what this IVR flow does..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Welcome Message</label>
                        <textarea class="form-control" name="welcome_message" rows="3" placeholder="Message played when call connects..."></textarea>
                        <div class="form-text">TTS or recorded message played at the start of the call</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active (can receive calls)</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Flow
                    </button>
                    <a href="/admin/ivr-flows" class="btn btn-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-1"></i>IVR Flow Tips</h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Start with a clear welcome message</li>
                    <li>Use menu steps for routing (Press 1 for Sales, 2 for Support)</li>
                    <li>Add timeout handling for no input</li>
                    <li>Test with a real call before going live</li>
                    <li>Monitor call logs to improve flow</li>
                </ul>
            </div>
        </div>
    </div>
</div>