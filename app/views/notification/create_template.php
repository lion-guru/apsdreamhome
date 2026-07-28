
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1 class="h2 mb-4">
                    <i class="fas fa-plus"></i> Create Template
                </h1>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/notification-management/templates/store">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="template_name" class="form-control" required placeholder="e.g. Welcome Email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Channel</label>
                                <select name="channel" class="form-select" required>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="push">Push Notification</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Template Code</label>
                                <input type="text" name="template_code" class="form-control" required placeholder="e.g. welcome_email">
                                <small class="text-muted">Unique identifier for this template</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-select">
                                    <option value="en">English</option>
                                    <option value="hi">Hindi</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="Email subject line">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Body</label>
                            <textarea name="body" class="form-control" rows="8" required placeholder="Template body. Use {{variable}} for dynamic content."></textarea>
                            <small class="text-muted">Available variables: {{name}}, {{email}}, {{phone}}, {{city}}, {{budget}}, {{date}}, {{otp}}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Variables (comma-separated)</label>
                            <input type="text" name="variables" class="form-control" placeholder="e.g. name, email, otp">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Template</button>
                            <a href="/admin/notification-management/templates" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
