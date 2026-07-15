<section class="py-5" style="background: linear-gradient(135deg, #0f172a, #1e3a5f, #1e293b);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-hard-hat me-2"></i><?php echo __('tool_construction_cost_title', [], 'Construction Cost Estimator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_construction_cost_subtitle', [], 'Know the estimated cost of building a house'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('built_area_sqft', [], 'Built-up Area (sq ft)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="builtArea" value="1500" oninput="calcConstr()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('construction_quality', [], 'Construction Quality'); ?></label>
                                <select class="form-select" id="quality" onchange="calcConstr()">
                                    <option value="basic"><?php echo __('basic', [], 'Basic (₹1,400/sqft)'); ?></option>
                                    <option value="standard" selected><?php echo __('standard', [], 'Standard (₹1,800/sqft)'); ?></option>
                                    <option value="premium"><?php echo __('premium', [], 'Premium (₹2,400/sqft)'); ?></option>
                                    <option value="luxury"><?php echo __('luxury', [], 'Luxury (₹3,200/sqft)'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('num_floors', [], 'Number of Floors'); ?></label>
                                <select class="form-select" id="floors" onchange="calcConstr()">
                                    <option value="1"><?php echo __('single', [], 'Single Floor'); ?></option>
                                    <option value="2" selected><?php echo __('double', [], 'Double Floor'); ?></option>
                                    <option value="3"><?php echo __('triple', [], 'Triple Floor'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('finish_level', [], 'Finish Level'); ?></label>
                                <select class="form-select" id="finish" onchange="calcConstr()">
                                    <option value="0.85"><?php echo __('basic_finish', [], 'Basic'); ?></option>
                                    <option value="1" selected><?php echo __('standard_finish', [], 'Standard'); ?></option>
                                    <option value="1.3"><?php echo __('premium_finish', [], 'Premium'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('location_factor', [], 'Location Factor'); ?></label>
                                <select class="form-select" id="locFac" onchange="calcConstr()">
                                    <option value="0.9"><?php echo __('tier3', [], 'Tier-3 City / Village'); ?></option>
                                    <option value="1" selected><?php echo __('tier2', [], 'Tier-2 City'); ?></option>
                                    <option value="1.15"><?php echo __('tier1', [], 'Tier-1 City'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('construction_cost', [], 'Construction Cost'); ?></small>
                                    <h5 class="text-primary mb-0" id="constrCost">₹48,60,000</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('material_cost', [], 'Material Cost'); ?></small>
                                    <h5 class="text-warning mb-0" id="materialCost">₹24,30,000</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('labor_cost', [], 'Labor Cost'); ?></small>
                                    <h5 class="text-info mb-0" id="laborCost">₹19,44,000</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-primary text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('total_estimated', [], 'Total Estimated'); ?></small>
                                    <h5 class="mb-0" id="totalConstr">₹92,34,000</h5>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('construction_cost_disclaimer', [], 'Estimates are indicative and based on current market rates in UP region. Actual costs vary by location, material quality, contractor rates, and local regulations. Consult a registered architect for accurate quotations.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcConstr() {
    const area = parseFloat(document.getElementById('builtArea').value) || 0;
    const qualityRates = { basic: 1400, standard: 1800, premium: 2400, luxury: 3200 };
    const quality = document.getElementById('quality').value;
    const floors = parseInt(document.getElementById('floors').value) || 1;
    const finish = parseFloat(document.getElementById('finish').value) || 1;
    const loc = parseFloat(document.getElementById('locFac').value) || 1;
    const rate = (qualityRates[quality] || 1800) * finish * loc;
    const totalArea = area * floors;
    const constrCost = totalArea * rate;
    const materialCost = constrCost * 0.5;
    const laborCost = constrCost * 0.4;
    const miscCost = constrCost * 0.1;
    const total = materialCost + laborCost + miscCost;
    document.getElementById('constrCost').textContent = '\u20B9' + Math.round(constrCost).toLocaleString('en-IN');
    document.getElementById('materialCost').textContent = '\u20B9' + Math.round(materialCost).toLocaleString('en-IN');
    document.getElementById('laborCost').textContent = '\u20B9' + Math.round(laborCost).toLocaleString('en-IN');
    document.getElementById('totalConstr').textContent = '\u20B9' + Math.round(total).toLocaleString('en-IN');
}
calcConstr();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
