<?php
/**
 * Associate Tools Page
 * Smart calculators and utilities for associates
 */
$page_title = $page_title ?? 'Smart Tools - APS Dream Home';
$current_page = $current_page ?? 'tools';
$tools = $tools ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-toolbox text-warning me-2"></i>Smart Tools</h2>
            <p class="text-muted mb-0">Quick calculators & utilities for your daily work</p>
        </div>
        <a href="https://play.google.com/store/apps/details?id=com.apsdreamhome" target="_blank" class="btn btn-outline-warning">
            <i class="fas fa-mobile-alt me-1"></i> Get Mobile App
        </a>
    </div>

    <!-- Tool Cards Grid -->
    <div class="row g-3">
        <?php foreach ($tools as $tool): ?>
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card tool-card h-100 border-0 shadow-sm" data-tool-id="<?= $tool['id'] ?>">
                <div class="card-header bg-white border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="tool-icon-wrapper" style="background: linear-gradient(135deg, <?= $tool['color'] ?>, <?= $tool['color'] ?>cc);">
                            <i class="fas <?= $tool['icon'] ?> text-white fs-4"></i>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-2">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($tool['name']) ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($tool['description']) ?></p>
                    
                    <!-- Tool Form (collapsible) -->
                    <div class="tool-form" style="display: none;">
                        <form id="tool-form-<?= $tool['id'] ?>" class="tool-calc-form">
                            <?php foreach ($tool['fields'] as $field): ?>
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($field['label']) ?>
                                    <?php if (isset($field['required']) && $field['required']): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>
                                <?php if (($field['type'] ?? 'text') === 'select'): ?>
                                <select class="form-control form-control-sm" name="<?= $field['name'] ?>" 
                                    <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>>
                                    <option value="">Select...</option>
                                    <?php foreach (($field['options'] ?? []) as $val => $label): ?>
                                        <option value="<?= $val ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                <input type="<?= $field['type'] ?? 'text' ?>" class="form-control form-control-sm" 
                                    name="<?= $field['name'] ?>" 
                                    placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                    step="<?= $field['step'] ?? '1' ?>"
                                    <?php echo isset($field['required']) && $field['required'] ? 'required' : ''; ?>>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="fas fa-calculator me-1"></i> Calculate
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary close-tool-form">
                                    <i class="fas fa-times me-1"></i> Close
                                </button>
                            </div>
                            <div class="tool-result mt-3 p-3 bg-light rounded" style="display: none;"></div>
                        </form>
                    </div>
                    
                    <!-- Quick Action Button -->
                    <button type="button" class="btn btn-sm btn-outline-warning w-100 open-tool-form mt-2" 
                        data-tool="<?= $tool['id'] ?>">
                        <i class="fas fa-play me-1"></i> Open Calculator
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Links -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-link text-warning me-2"></i>Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= $base ?>/calc" target="_blank" class="btn btn-outline-primary w-100 py-2">
                                <i class="fas fa-calculator me-1"></i> Full EMI Calculator
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= $base ?>/stamp-duty-calculator" target="_blank" class="btn btn-outline-danger w-100 py-2">
                                <i class="fas fa-file-contract me-1"></i> Stamp Duty Calculator
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= $base ?>/plot-size-converter" target="_blank" class="btn btn-outline-info w-100 py-2">
                                <i class="fas fa-ruler-combined me-1"></i> Plot Size Converter
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= $base ?>/tools-hub" target="_blank" class="btn btn-outline-warning w-100 py-2">
                                <i class="fas fa-toolbox me-1"></i> All Tools Hub
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tool form toggle
document.querySelectorAll('.open-tool-form').forEach(btn => {
    btn.addEventListener('click', function() {
        const toolId = this.dataset.tool;
        const card = document.querySelector(`.tool-card[data-tool-id="${toolId}"]`);
        const form = card.querySelector('.tool-form');
        const result = card.querySelector('.tool-result');
        
        if (form.style.display === 'none') {
            form.style.display = 'block';
            this.innerHTML = '<i class="fas fa-chevron-up me-1"></i> Hide Calculator';
            this.classList.remove('btn-outline-warning');
            this.classList.add('btn-warning');
        } else {
            form.style.display = 'none';
            result.style.display = 'none';
            this.innerHTML = '<i class="fas fa-play me-1"></i> Open Calculator';
            this.classList.remove('btn-warning');
            this.classList.add('btn-outline-warning');
        }
    });
});

document.querySelectorAll('.close-tool-form').forEach(btn => {
    btn.addEventListener('click', function() {
        const card = this.closest('.tool-card');
        const form = card.querySelector('.tool-form');
        const result = card.querySelector('.tool-result');
        const openBtn = card.querySelector('.open-tool-form');
        
        form.style.display = 'none';
        result.style.display = 'none';
        openBtn.innerHTML = '<i class="fas fa-play me-1"></i> Open Calculator';
        openBtn.classList.remove('btn-warning');
        openBtn.classList.add('btn-outline-warning');
    });
});

// Tool form submissions
document.querySelectorAll('.tool-calc-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const toolId = this.id.replace('tool-form-', '');
        const resultDiv = this.closest('.tool-card').querySelector('.tool-result');
        
        // Show loading
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 small">Calculating...</p></div>';
        
        const formData = new FormData(this);
        const endpoint = `/associate/${toolId}-calculator`;
        
        try {
            const response = await fetch('<?= $base ?>' + endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                let html = '<div class="alert alert-success mb-0">';
                html += '<h6 class="mb-2"><i class="fas fa-check-circle me-1"></i> Result</h6>';
                
                if (toolId === 'emi') {
                    html += `<p class="mb-1"><strong>Monthly EMI:</strong> ₹${data.emi.toLocaleString('en-IN')}</p>`;
                    html += `<p class="mb-1"><strong>Total Interest:</strong> ₹${data.total_interest.toLocaleString('en-IN')}</p>`;
                    html += `<p class="mb-0"><strong>Total Payable:</strong> ₹${data.total_payable.toLocaleString('en-IN')}</p>`;
                } else if (toolId === 'stamp-duty') {
                    html += `<p class="mb-1"><strong>Stamp Duty:</strong> ₹${data.stamp_duty.toLocaleString('en-IN')}</p>`;
                    html += `<p class="mb-1"><strong>Registration (1%):</strong> ₹${data.registration.toLocaleString('en-IN')}</p>`;
                    html += `<p class="mb-0"><strong>Total:</strong> ₹${data.total.toLocaleString('en-IN')}</p>`;
                    html += `<small class="text-muted">Rate used: ${data.rate_used}</small>`;
                } else if (toolId === 'plot-converter') {
                    html += `<p class="mb-0"><strong>${data.input} ${data.from} = ${data.result.toLocaleString('en-IN')} ${data.to}</strong></p>`;
                } else if (toolId === 'commission') {
                    html += `<p class="mb-1"><strong>Commission:</strong> ₹${data.commission.toLocaleString('en-IN')}</p>`;
                    html += `<p class="mb-0"><strong>Effective Rate:</strong> ${data.effective_rate}%</p>`;
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i> ${data.message}</div>`;
            }
        } catch (error) {
            resultDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i> Error calculating. Please try again.</div>';
        }
    });
});
</script>

<style>
.tool-card {
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}
.tool-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #c2410c;
}
.tool-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tool-card .card-title {
    font-size: 1rem;
    font-weight: 600;
}
.tool-card .form-control-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.85rem;
}
.tool-card .form-label {
    font-size: 0.8rem;
}
.tool-result .alert {
    border-radius: 8px;
    font-size: 0.9rem;
}
.tool-result p {
    margin-bottom: 0.5rem;
}
</style>