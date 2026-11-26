<?php $page_title = 'Unsubscribe'; ?>
<div class="container py-5">
    <div class="card mx-auto" style="max-width: 560px;">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Unsubscribe from <?= htmlspecialchars($channel ?? '', ENT_QUOTES, 'UTF-8') ?> updates</h1>
            <p class="text-muted">You are about to opt out for <strong><?= htmlspecialchars($target ?? '', ENT_QUOTES, 'UTF-8') ?></strong>. This takes effect immediately for future campaigns.</p>
            <form method="POST" action="<?= BASE_URL ?>/unsubscribe">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="channel" value="<?= htmlspecialchars($channel ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="target" value="<?= htmlspecialchars($target ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-danger">Confirm unsubscribe</button>
                <a href="<?= BASE_URL ?>/" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
</div>
