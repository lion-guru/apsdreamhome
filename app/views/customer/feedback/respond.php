<?php
$page_title = 'Customer Feedback';
$csrf = \App\Helpers\SecurityHelper::generateCSRFToken();
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Customer Feedback</h1>
    <?php if ($item): ?>
    <form method="post" action="/feedback/respond/<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <div class="mb-3">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-select">
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="comments" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
    <?php else: ?>
    <div class="alert alert-warning">Feedback survey not found.</div>
    <?php endif; ?>
</div>
