<?php
$prompts = $prompts ?? [];
$categories = $categories ?? [];
$customers = $customers ?? [];
$merge_fields = $merge_fields ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-robot me-2 text-primary"></i>AI Document Composer</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/ai-prompts" class="btn btn-outline-info btn-sm me-1"><i class="fas fa-brain me-1"></i>Manage Prompts</a>
            <a href="<?= BASE_URL ?>/admin/legal/templates" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file me-1"></i>Templates</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-7">
            <form method="POST" action="<?= BASE_URL ?>/admin/legal/ai-generate" id="aiForm">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-magic me-2"></i>1. Choose Template or Write Custom Prompt</div>
                    <div class="aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">AI Prompt Template</label>
                            <select name="prompt_id" class="form-select" id="promptSelect" onchange="loadPrompt()">
                                <option value="0">Custom Prompt (write your own below)</option>
                                <?php foreach ($prompts as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-category="<?= htmlspecialchars($p['document_category'] ?? '') ?>"><?= htmlspecialchars($p['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Custom Prompt</label>
                            <textarea name="custom_prompt" id="customPrompt" class="form-control font-monospace" rows="10" placeholder="Write a custom prompt or select a template above... Use {{customer_name}}, {{plot_no}} etc."></textarea>
                            <small class="text-muted">Available merge fields: <?php $all = []; foreach ($merge_fields as $g => $fs) { foreach ($fs as $k => $l) { $all[] = $k; } } echo implode(', ', $all); ?></small>
                        </div>
                    </div>
                </div>

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-sliders-h me-2"></i>2. AI Settings</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Temperature</label>
                                <input type="range" name="temperature" class="form-range" min="0" max="1" step="0.05" value="0.30" oninput="this.nextElementSibling.textContent=this.value">
                                <span class="small text-muted">0.30</span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Tokens</label>
                                <select name="max_tokens" class="form-select">
                                    <option value="1024">1,024</option>
                                    <option value="2048" selected>2,048</option>
                                    <option value="4096">4,096</option>
                                    <option value="8192">8,192</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Language</label>
                                <select name="language" class="form-select">
                                    <option value="en">English</option>
                                    <option value="hi">Hindi</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="aps-cp-card mb-3">
                    <div class="aps-cp-card-header"><i class="fas fa-link me-2"></i>3. Merge Data & Entity</div>
                    <div class="aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">None</option><?php foreach ($customers as $cu): ?><option value="<?= $cu['id'] ?>" data-name="<?= htmlspecialchars($cu['name'] ?? '') ?>" data-phone="<?= htmlspecialchars($cu['phone'] ?? '') ?>" data-email="<?= htmlspecialchars($cu['email'] ?? '') ?>"><?= htmlspecialchars($cu['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-6"><label class="form-label">Entity Type</label><select name="entity_type" class="form-select"><option value="general">General</option><option value="booking">Booking</option><option value="customer">Customer</option><option value="associate">Associate</option><option value="colony">Colony</option><option value="plot">Plot</option><option value="loan">Loan</option></select></div>
                            <div class="col-12">
                                <label class="form-label">Additional Context (optional)</label>
                                <textarea name="context" class="form-control" rows="2" placeholder="Any specific instructions for the AI..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg" id="generateBtn"><i class="fas fa-wand-magic-sparkles me-1"></i>Generate Document</button>
                </div>
            </form>
        </div>

        <div class="col-md-5">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-lightbulb me-2"></i>Available Prompt Templates</div>
                <div class="aps-cp-card-body p-0">
                    <div class="list-group list-group-flush style-23214">
                        <?php foreach ($prompts as $p): ?>
                            <div class="list-group-item list-group-item-action p-2" onclick="selectPrompt(<?= $p['id'] ?>)">
                                <div class="d-flex justify-content-between">
                                    <strong class="small"><?= htmlspecialchars($p['name'] ?? '') ?></strong>
                                    <span class="badge bg-light text-dark"><?= htmlspecialchars($p['document_category'] ?? '') ?></span>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 100)) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Quick Insert Merge Fields</div>
                <div class="aps-cp-card-body">
                    <div class="row g-1">
                        <?php foreach ($merge_fields as $group => $fields): ?>
                            <div class="col-12"><small class="text-muted fw-bold"><?= ucfirst($group) ?></small></div>
                            <?php foreach ($fields as $key => $label): ?>
                                <div class="col-6"><button class="btn btn-sm btn-outline-secondary w-100 mb-1 small" onclick="insertPrompt('<?= $key ?>')" title="<?= htmlspecialchars($label ?? '') ?>"><?= htmlspecialchars($key ?? '') ?></button></div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var prompts = <?= json_encode($prompts) ?>;

function selectPrompt(id) {
    document.getElementById('promptSelect').value = id;
    loadPrompt();
}

function loadPrompt() {
    var id = parseInt(document.getElementById('promptSelect').value);
    var ta = document.getElementById('customPrompt');
    if (id === 0) return;
    var p = prompts.find(function(x) { return x.id == id; });
    if (p) ta.value = p.prompt_template;
}

function insertPrompt(text) {
    var ta = document.getElementById('customPrompt');
    if (!ta) return;
    if (ta.selectionStart || ta.selectionStart === 0) {
        var s = ta.selectionStart, e = ta.selectionEnd;
        ta.value = ta.value.substring(0, s) + text + ta.value.substring(e);
        ta.selectionStart = ta.selectionEnd = s + text.length;
    } else { ta.value += text; }
    ta.focus();
}

document.getElementById('aiForm').addEventListener('submit', function() {
    document.getElementById('generateBtn').disabled = true;
    document.getElementById('generateBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
});
</script>
