<?php
$agents = $agents ?? [];
$properties = $properties ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<style>
.aag-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.aag-card h5 { color: #f8fafc; margin-bottom: 16px; font-size: 15px; }
.aag-form label { color: #94a3b8; font-size: 12px; margin-bottom: 4px; display: block; font-weight: 600; }
.aag-form select, .aag-form input, .aag-form textarea {
    background: #0f172a; border: 1px solid #475569; color: #f8fafc; padding: 10px 12px;
    border-radius: 6px; width: 100%; font-size: 13px; margin-bottom: 12px;
}
.aag-form select:focus, .aag-form input:focus, .aag-form textarea:focus {
    border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px #3b82f620;
}
.aag-form textarea { min-height: 200px; font-family: monospace; resize: vertical; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="color: #f8fafc; margin:0;"><i class="fas fa-file-signature me-2"></i>Create Agent Agreement</h4>
        <a href="<?= $base ?>/admin/agent-agreements" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="aag-card">
        <form method="POST" action="<?= $base ?>/admin/agent-agreements/store" class="aag-form">
    <?php echo CSRFProtection::csrfField(); ?>
            <div class="row">
                <div class="col-md-6">
                    <label>Agent *</label>
                    <select name="agent_id" required>
                        <option value="">-- Select Agent --</option>
                        <?php foreach ($agents as $ag): ?>
                        <option value="<?= (int)$ag['id'] ?>"><?= htmlspecialchars($ag['name']) ?> (<?= htmlspecialchars($ag['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Property (Optional)</label>
                    <select name="property_id">
                        <option value="">-- General Agreement --</option>
                        <?php foreach ($properties as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?> — <?= htmlspecialchars($p['location'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <label>Title *</label>
                    <input type="text" name="title" value="Agent Listing Agreement" required>
                </div>
                <div class="col-md-4">
                    <label>Commission % *</label>
                    <input type="number" name="commission_pct" step="0.01" min="0" max="100" value="5.00" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label>End Date *</label>
                    <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime('+1 year')) ?>" required>
                </div>
            </div>

            <label>Agreement Content (leave empty for auto-generated)</label>
            <textarea name="content" placeholder="Leave empty to auto-generate terms and conditions..."></textarea>

            <label>Notes (Internal)</label>
            <textarea name="notes" style="min-height:60px;" placeholder="Optional internal notes..."></textarea>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Create Agreement
                </button>
            </div>
        </form>
    </div>
</div>
