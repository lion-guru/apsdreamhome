<?php
$page_title = 'Dispatch Feedback Survey';
$csrf = \App\Helpers\SecurityHelper::generateCSRFToken();
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Dispatch Feedback Survey</h1>
    <form method="post" action="/admin/feedback-pipeline/store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
            <label class="form-label">Source Type</label>
            <input type="text" name="source_type" class="form-control" value="site_visit">
        </div>
        <div class="mb-3">
            <label class="form-label">Source ID</label>
            <input type="number" name="source_id" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">User ID</label>
            <input type="number" name="user_id" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Customer Name</label>
            <input type="text" name="user_name" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Customer Email</label>
            <input type="email" name="user_email" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Customer Phone</label>
            <input type="text" name="user_phone" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Channel</label>
            <select name="channel" class="form-select">
                <option value="email">Email</option>
                <option value="sms">SMS</option>
                <option value="whatsapp">WhatsApp</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Platform</label>
            <input type="text" name="platform" class="form-control" value="web">
        </div>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="Customer Feedback Survey">
        </div>
        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Dispatch</button>
        <a href="/admin/feedback-pipeline" class="btn btn-secondary">Cancel</a>
    </form>
</div>
