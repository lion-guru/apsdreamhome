<?php
/**
 * Associate Tools Page
 * Smart calculators and utilities for associates
 */
$page_title = $page_title ?? 'Smart Tools - APS Dream Home';
$current_page = $current_page ?? 'tools';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

$defaultTools = [
    [
        'id' => 'emi',
        'name' => 'EMI Calculator',
        'icon' => 'fa-calculator',
        'description' => 'Calculate monthly EMI, interest, and total payable for land & plot loans.',
        'color' => '#0d9488',
        'fields' => [
            ['name' => 'principal', 'label' => 'Loan Amount (₹)', 'type' => 'number', 'placeholder' => 'e.g. 500000', 'required' => true],
            ['name' => 'rate', 'label' => 'Interest Rate (% p.a.)', 'type' => 'number', 'step' => '0.1', 'placeholder' => 'e.g. 8.5', 'required' => true],
            ['name' => 'tenure', 'label' => 'Tenure (Years)', 'type' => 'number', 'placeholder' => 'e.g. 5', 'required' => true],
        ]
    ],
    [
        'id' => 'stamp-duty',
        'name' => 'Stamp Duty & Registry',
        'icon' => 'fa-file-invoice-dollar',
        'description' => 'Calculate UP registry stamp duty (7% male, 6% female) and registration fees (1%).',
        'color' => '#f59e0b',
        'fields' => [
            ['name' => 'property_value', 'label' => 'Property Value (₹)', 'type' => 'number', 'placeholder' => 'e.g. 1000000', 'required' => true],
            ['name' => 'gender', 'label' => 'Buyer Gender', 'type' => 'select', 'options' => ['male' => 'Male (7%)', 'female' => 'Female (6%)'], 'required' => true],
        ]
    ],
    [
        'id' => 'plot-converter',
        'name' => 'Plot Area Converter',
        'icon' => 'fa-ruler-combined',
        'description' => 'Convert between Sq.Ft, Sq.Yards (Gaj), Bigha, Biswa, and Acres seamlessly.',
        'color' => '#3b82f6',
        'fields' => [
            ['name' => 'value', 'label' => 'Area Value', 'type' => 'number', 'step' => 'any', 'placeholder' => 'e.g. 1000', 'required' => true],
            ['name' => 'from_unit', 'label' => 'From Unit', 'type' => 'select', 'options' => ['sqft' => 'Sq. Feet (sq ft)', 'sqyd' => 'Sq. Yards (Gaj)', 'biswa' => 'Biswa (1350 sqft)', 'bigha' => 'Bigha (27000 sqft)', 'acre' => 'Acre'], 'required' => true],
            ['name' => 'to_unit', 'label' => 'To Unit', 'type' => 'select', 'options' => ['sqyd' => 'Sq. Yards (Gaj)', 'sqft' => 'Sq. Feet (sq ft)', 'biswa' => 'Biswa (1350 sqft)', 'bigha' => 'Bigha (27000 sqft)', 'acre' => 'Acre'], 'required' => true],
        ]
    ],
    [
        'id' => 'commission',
        'name' => 'Commission Estimator',
        'icon' => 'fa-coins',
        'description' => 'Estimate your direct commission based on plot booking value and career rank.',
        'color' => '#10b981',
        'fields' => [
            ['name' => 'booking_amount', 'label' => 'Booking Value (₹)', 'type' => 'number', 'placeholder' => 'e.g. 1000000', 'required' => true],
            ['name' => 'rate', 'label' => 'Commission Slab (%)', 'type' => 'number', 'step' => '0.5', 'placeholder' => 'e.g. 5', 'required' => true],
        ]
    ]
];
$tools = !empty($tools) ? $tools : $defaultTools;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-dark fw-bold"><i class="fas fa-toolbox text-teal me-2" style="color: #0d9488;"></i>Smart Tools</h4>
        <small class="text-muted">Quick calculators & utilities for field associates and site managers</small>
    </div>
    <a href="https://play.google.com/store/apps/details?id=com.apsdreamhome" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fas fa-mobile-alt me-1 text-warning"></i> Get Mobile App
    </a>
</div>

<!-- Tool Cards Grid -->
<div class="row g-3">
    <?php foreach ($tools as $tool): ?>
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="card tool-card h-100 border-0 shadow-sm" data-tool-id="<?= $tool['id'] ?>">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="tool-icon-wrapper" style="background: <?= $tool['color'] ?? '#0d9488' ?>;">
                        <i class="fas <?= $tool['icon'] ?> text-white fs-4"></i>
                    </div>
                </div>
            </div>
            <div class="card-body px-4 pb-3">
                <h5 class="card-title mb-1"><?= htmlspecialchars($tool['name'] ?? '') ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($tool['description'] ?? '') ?></p>
                
                <!-- Tool Form (collapsible) -->
                <div class="tool-form" style="display: none;">
                    <form id="tool-form-<?= $tool['id'] ?>" class="tool-calc-form">
                        <?php foreach (($tool['fields'] ?? []) as $field): ?>
                        <div class="mb-2">
                            <label class="form-label small fw-bold"><?= htmlspecialchars($field['label'] ?? '') ?>
                                <?php if (!empty($field['required'])): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <?php if (($field['type'] ?? 'text') === 'select'): ?>
                            <select class="form-control form-control-sm" name="<?= $field['name'] ?>" 
                                <?= !empty($field['required']) ? 'required' : '' ?>>
                                <option value="">Select...</option>
                                <?php foreach (($field['options'] ?? []) as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= htmlspecialchars($label ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php else: ?>
                            <input aria-label="field" type="<?= $field['type'] ?? 'text' ?>" class="form-control form-control-sm" 
                                name="<?= $field['name'] ?>" 
                                placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                step="<?= $field['step'] ?? '1' ?>"
                                <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-sm btn-primary fw-bold text-white" style="background: <?= $tool['color'] ?? '#0d9488' ?>; border-color: <?= $tool['color'] ?? '#0d9488' ?>;">
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
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 open-tool-form mt-2 rounded-pill" 
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
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-link text-teal me-2" style="color: #0d9488;"></i>Dedicated Calculators & Full Tools</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="<?= $base ?>/associate/emi-calculator" class="btn btn-outline-primary w-100 py-2 rounded-3">
                            <i class="fas fa-calculator me-1"></i> Full EMI Calculator
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= $base ?>/associate/stamp-duty-calculator" class="btn btn-outline-danger w-100 py-2 rounded-3">
                            <i class="fas fa-file-contract me-1"></i> Stamp Duty Calculator
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= $base ?>/associate/plot-converter" class="btn btn-outline-info w-100 py-2 rounded-3">
                            <i class="fas fa-ruler-combined me-1"></i> Plot Size Converter
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?= $base ?>/associate/commission-calculator" class="btn btn-outline-success w-100 py-2 rounded-3">
                            <i class="fas fa-coins me-1"></i> Commission Matrix
                        </a>
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
        
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            this.innerHTML = '<i class="fas fa-chevron-up me-1"></i> Hide Calculator';
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-secondary');
        } else {
            form.style.display = 'none';
            result.style.display = 'none';
            this.innerHTML = '<i class="fas fa-play me-1"></i> Open Calculator';
            this.classList.remove('btn-secondary');
            this.classList.add('btn-outline-secondary');
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
        openBtn.classList.remove('btn-secondary');
        openBtn.classList.add('btn-outline-secondary');
    });
});

// Tool form submissions with instant local calculations
document.querySelectorAll('.tool-calc-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const toolId = this.id.replace('tool-form-', '');
        const resultDiv = this.closest('.tool-card').querySelector('.tool-result');
        const formData = new FormData(this);
        
        resultDiv.style.display = 'block';
        
        try {
            let html = '<div class="alert alert-success mb-0">';
            html += '<h6 class="mb-2 fw-bold"><i class="fas fa-check-circle me-1"></i> Result</h6>';
            
            if (toolId === 'emi') {
                const P = parseFloat(formData.get('principal') || 0);
                const r = (parseFloat(formData.get('rate') || 8.5) / 100) / 12;
                const n = parseInt(formData.get('tenure') || 5) * 12;
                if (P <= 0 || r <= 0 || n <= 0) throw new Error('Please enter valid positive numbers');
                const emi = Math.round((P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1));
                const totalPayable = emi * n;
                const totalInterest = totalPayable - P;
                
                html += `<p class="mb-1"><strong>Monthly EMI:</strong> ₹${emi.toLocaleString('en-IN')}</p>`;
                html += `<p class="mb-1"><strong>Total Interest:</strong> ₹${totalInterest.toLocaleString('en-IN')}</p>`;
                html += `<p class="mb-0"><strong>Total Payable:</strong> ₹${totalPayable.toLocaleString('en-IN')}</p>`;
            } else if (toolId === 'stamp-duty') {
                const val = parseFloat(formData.get('property_value') || 0);
                const gender = formData.get('gender') || 'male';
                if (val <= 0) throw new Error('Property value must be greater than 0');
                const rate = (gender === 'female' ? 6 : 7);
                const stampDuty = Math.round(val * (rate / 100));
                const registration = Math.round(val * 0.01);
                const total = stampDuty + registration;
                
                html += `<p class="mb-1"><strong>Stamp Duty (${rate}%):</strong> ₹${stampDuty.toLocaleString('en-IN')}</p>`;
                html += `<p class="mb-1"><strong>Registration (1%):</strong> ₹${registration.toLocaleString('en-IN')}</p>`;
                html += `<p class="mb-0"><strong>Total Govt. Charges:</strong> ₹${total.toLocaleString('en-IN')}</p>`;
            } else if (toolId === 'plot-converter') {
                const val = parseFloat(formData.get('value') || 0);
                const from = formData.get('from_unit') || 'sqft';
                const to = formData.get('to_unit') || 'sqyd';
                const units = { sqft: 1, sqyd: 9, biswa: 1350, bigha: 27000, acre: 43560 };
                if (val <= 0) throw new Error('Value must be greater than 0');
                const res = Math.round(((val * units[from]) / units[to]) * 1000) / 1000;
                
                html += `<p class="mb-0 fs-6"><strong>${val} ${from} = ${res.toLocaleString('en-IN')} ${to}</strong></p>`;
            } else if (toolId === 'commission') {
                const val = parseFloat(formData.get('booking_amount') || 0);
                const rate = parseFloat(formData.get('rate') || 5);
                if (val <= 0 || rate <= 0) throw new Error('Please enter valid amount and rate');
                const comm = Math.round(val * (rate / 100));
                
                html += `<p class="mb-1"><strong>Estimated Commission:</strong> ₹${comm.toLocaleString('en-IN')}</p>`;
                html += `<p class="mb-0"><small class="text-muted">Effective Rate: ${rate}%</small></p>`;
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
        } catch (err) {
            resultDiv.innerHTML = `<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle me-1"></i> ${err.message || 'Error calculating.'}</div>`;
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
}
.tool-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
