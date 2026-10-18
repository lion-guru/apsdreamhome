<?php
$page_title = 'Call Recording Detail';
?>
<div class="container-fluid">
    <h1 class="h3 mb-3">Call Recording Detail</h1>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Lead:</strong> <?= htmlspecialchars($item['lead_name'] ?? '') ?> (<?= htmlspecialchars($item['lead_phone'] ?? '') ?>)</p>
            <p><strong>Agent:</strong> <?= htmlspecialchars($item['agent_name'] ?? '') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($item['status'] ?? '') ?></p>
            <p><strong>Duration:</strong> <?= (int)($item['duration_seconds'] ?? 0) ?> sec</p>
            <p><strong>Response:</strong> <?= htmlspecialchars($item['customer_response'] ?? '') ?></p>
            <p><strong>Interest:</strong> <?= htmlspecialchars($item['interest_level'] ?? '') ?></p>
            <audio controls src="<?= htmlspecialchars($item['recording_url']) ?>" style="width:100%"></audio>
        </div>
    </div>
    <a href="/admin/crm/call-recordings" class="btn btn-secondary">Back</a>
</div>
