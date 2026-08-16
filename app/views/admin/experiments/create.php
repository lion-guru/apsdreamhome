<?php
$pageTitle = $page_title ?? 'Create A/B Experiment';
$baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
$csrf = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="text-muted mb-0">Define variants and traffic allocation. Experiment starts running immediately.</p>
        </div>
        <a href="<?= $baseUrl ?>/admin/experiments" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form method="POST" action="<?= $baseUrl ?>/admin/experiments/store" id="experiment-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf ?? '') ?>">

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h5 class="mb-0">Basic Settings</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Experiment Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required pattern="[a-zA-Z0-9_\-]+" maxlength="100"
                                   placeholder="homepage_cta" autocomplete="off">
                            <div class="form-text">Slug-style — alphanumeric, underscore, dash. Used in code and URLs.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="description" rows="2" maxlength="500"
                                      placeholder="What hypothesis are you testing?"></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Traffic Allocation: <span id="traffic-display">100</span>%</label>
                            <input type="range" class="form-range" name="traffic_allocation" min="1" max="100" value="100"
                                   oninput="document.getElementById('traffic-display').textContent = this.value">
                            <div class="form-text">% of users to include in the experiment (rest see the default).</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Variants <span class="text-danger">*</span></h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addVariant()">
                            <i class="fas fa-plus me-1"></i> Add Variant
                        </button>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle me-1"></i> Need at least 2 variants. Weights are relative — e.g. 50 & 50 = even split, 70 & 30 = 70% / 30%.
                        </div>
                        <div id="variants-container"></div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-play me-1"></i> Create & Start Experiment
                    </button>
                    <a href="<?= $baseUrl ?>/admin/experiments" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-lightbulb me-1"></i> Tips</h6></div>
                    <div class="card-body small">
                        <ul class="mb-0 ps-3">
                            <li>Use <code>control</code> for the existing experience, <code>treatment</code> for the new one.</li>
                            <li>Variant assignment is sticky — same user always gets the same variant.</li>
                            <li>Track <code>view</code> + <code>conversion</code> events to compute lift.</li>
                            <li>The framework computes a <strong>chi-square significance test</strong> — wait for <code>p &lt; 0.05</code> before declaring a winner.</li>
                            <li>Lower traffic allocation = safer rollout (e.g. 10% canary).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="variant-template">
    <div class="row g-2 mb-2 align-items-center variant-row">
        <div class="col-md-6">
            <input type="text" class="form-control" name="variants[__INDEX__][name]" placeholder="variant name (e.g. control)" pattern="[a-zA-Z0-9_\-]+" maxlength="50" required>
        </div>
        <div class="col-md-4">
            <div class="input-group">
                <input type="number" class="form-control" name="variants[__INDEX__][weight]" placeholder="weight" min="1" max="1000" value="50" required>
                <span class="input-group-text">w</span>
            </div>
        </div>
        <div class="col-md-2 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeVariant(this)" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</template>

<script>
let variantIndex = 0;

function addVariant(name = '', weight = 50) {
    const tpl = document.getElementById('variant-template').innerHTML.replace(/__INDEX__/g, variantIndex++);
    const wrap = document.createElement('div');
    wrap.innerHTML = tpl;
    const row = wrap.firstElementChild;
    if (name)   row.querySelector('input[name$="[name]"]').value   = name;
    if (weight) row.querySelector('input[name$="[weight]"]').value = weight;
    document.getElementById('variants-container').appendChild(row);
}

function removeVariant(btn) {
    const container = document.getElementById('variants-container');
    if (container.querySelectorAll('.variant-row').length <= 2) {
        alert('At least 2 variants required.');
        return;
    }
    btn.closest('.variant-row').remove();
}

// Seed with default control/treatment
addVariant('control', 50);
addVariant('treatment', 50);

document.getElementById('experiment-form').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.variant-row');
    if (rows.length < 2) {
        e.preventDefault();
        alert('Add at least 2 variants.');
    }
});
</script>
