<?php
$page_title = 'Feedback Pipeline Detail';
$csrf = \App\Helpers\SecurityHelper::generateCSRFToken();
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Feedback Pipeline Detail</h1>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Source:</strong> <?= htmlspecialchars($item['source_type'] ?? '') ?> #<?= (int)($item['source_id'] ?? 0) ?></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($item['user_name'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($item['user_email'] ?? '') ?></p>
            <p><strong>Channel:</strong> <?= htmlspecialchars($item['channel'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($item['status'] ?? '') ?></p>
            <p><strong>Rating:</strong> <?= htmlspecialchars((string)($item['rating'] ?? '')) ?></p>
            <p><strong>Sent At:</strong> <?= htmlspecialchars($item['sent_at'] ?? '') ?></p>
            <p><strong>Responded At:</strong> <?= htmlspecialchars($item['responded_at'] ?? '') ?></p>
            <p><strong>Comments:</strong> <?= htmlspecialchars($item['description'] ?? '') ?></p>
            <p><strong>Survey Token:</strong> <?= htmlspecialchars($item['survey_token'] ?? '') ?></p>
        </div>
    </div>
    <form method="post" action="/admin/feedback-pipeline/<?= (int)$item['id'] ?>/status">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="sent">Sent</option>
                <option value="responded">Responded</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Admin Response</label>
            <textarea name="admin_response" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
    <a href="/admin/feedback-pipeline" class="btn btn-secondary mt-3">Back</a>
</div>
