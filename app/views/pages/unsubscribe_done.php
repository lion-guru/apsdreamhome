<?php $page_title = 'Unsubscribed'; ?>
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 560px;">
        <div class="card-body p-4">
            <h1 class="h4 mb-3 text-success">You are unsubscribed</h1>
            <p class="text-muted">Future <?= htmlspecialchars($channel ?? '', ENT_QUOTES, 'UTF-8') ?> campaigns will no longer be sent to this address. You can subscribe again any time.</p>
            <a href="<?= BASE_URL ?>/" class="btn btn-primary">Back to home</a>
        </div>
    </div>
</div>
