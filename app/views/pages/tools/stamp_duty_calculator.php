<section class="py-5 text-white" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%)">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i><?php echo __('tool_stamp_duty_calc_title', [], 'Stamp Duty & Registration Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_stamp_duty_calc_subtitle', [], 'Calculate total government charges before buying property'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_value_rs', [], 'Property Value (₹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="propVal" value="5000000" min="0" placeholder="<?php echo __('enter_property_value', [], 'Enter property value'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_type', [], 'Property Type'); ?></label>
                                <select class="form-select" id="propType" onchange="calcStamp()">
                                    <option value="residential"><?php echo __('residential', [], 'Residential'); ?></option>
                                    <option value="commercial"><?php echo __('commercial', [], 'Commercial'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('state', [], 'State'); ?></label>
                                <select class="form-select" id="stateSel" onchange="calcStamp()">
                                    <option value="up" data-stamp="5" data-reg="1" data-regcap="30000">Uttar Pradesh</option>
                                    <option value="bihar" data-stamp="6" data-reg="2" data-regcap="50000">Bihar</option>
                                    <option value="mp" data-stamp="5" data-reg="1" data-regcap="30000">Madhya Pradesh</option>
                                    <option value="rajasthan" data-stamp="6" data-reg="1" data-regcap="30000">Rajasthan</option>
                                    <option value="delhi" data-stamp="5" data-reg="1" data-regcap="30000">Delhi</option>
                                    <option value="maharashtra" data-stamp="6" data-reg="1" data-regcap="30000">Maharashtra</option>
                                    <option value="karnataka" data-stamp="5" data-reg="1" data-regcap="30000">Karnataka</option>
                                    <option value="tamilnadu" data-stamp="7" data-reg="1" data-regcap="30000">Tamil Nadu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('buyer_type', [], 'Buyer Type'); ?></label>
                                <select class="form-select" id="buyerType" onchange="calcStamp()">
                                    <option value="individual"><?php echo __('individual', [], 'Individual'); ?></option>
                                    <option value="female"><?php echo __('woman_buyer_rebate', [], 'Woman Buyer (0.1% rebate in UP)'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 text-center mb-3">
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('stamp_duty', [], 'Stamp Duty'); ?></small>
                                    <h5 class="text-primary mb-0" id="stampDuty">₹2,50,000</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('registration_fee', [], 'Registration Fee'); ?></small>
                                    <h5 class="text-success mb-0" id="regFee">₹30,000</h5>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('property_value', [], 'Property Value'); ?></small>
                                    <h6 class="text-secondary mb-0" id="baseVal">₹50,00,000</h6>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="bg-primary text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('total_cost', [], 'Total Cost'); ?></small>
                                    <h5 class="mb-0" id="totalCost">₹52,80,000</h5>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mt-3">
                            <div class="card-body p-3">
                                <h6 class="fw-bold"><i class="fas fa-receipt me-1"></i><?php echo __('cost_breakdown', [], 'Cost Breakdown'); ?></h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted"><?php echo __('stamp_duty_rate', [], 'Stamp Duty Rate:'); ?></small>
                                        <span class="fw-bold" id="stampRate">5%</span>
                                        <br>
                                        <small class="text-muted"><?php echo __('registration_rate', [], 'Registration Rate:'); ?></small>
                                        <span class="fw-bold" id="regRate">1%</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted"><?php echo __('registration_cap', [], 'Registration Cap:'); ?></small>
                                        <span class="fw-bold" id="regCap">₹30,000</span>
                                        <br>
                                        <small class="text-muted"><?php echo __('woman_rebate', [], 'Woman Rebate:'); ?></small>
                                        <span class="fw-bold text-success" id="rebate">₹0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('stamp_duty_disclaimer', [], 'Rates based on respective State Stamp Acts for residential property. Commercial properties have higher stamp duty. Actual charges may vary. Consult a lawyer for exact figures.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcStamp() {
    const val = parseFloat(document.getElementById('propVal').value) || 0;
    const type = document.getElementById('propType').value;
    const stateOpt = document.getElementById('stateSel').selectedOptions[0];
    let stampRate = parseFloat(stateOpt.dataset.stamp) || 5;
    const regRate = parseFloat(stateOpt.dataset.reg) || 1;
    const regCap = parseFloat(stateOpt.dataset.regcap) || 30000;
    const buyer = document.getElementById('buyerType').value;
    if (type === 'commercial') stampRate += 1;
    let stampDuty = val * stampRate / 100;
    if (buyer === 'female') stampDuty -= val * 0.001;
    let regFee = val * regRate / 100;
    if (regFee > regCap) regFee = regCap;
    const total = val + stampDuty + regFee;
    document.getElementById('stampDuty').textContent = '\u20B9' + Math.round(stampDuty).toLocaleString('en-IN');
    document.getElementById('regFee').textContent = '\u20B9' + Math.round(regFee).toLocaleString('en-IN');
    document.getElementById('baseVal').textContent = '\u20B9' + Math.round(val).toLocaleString('en-IN');
    document.getElementById('totalCost').textContent = '\u20B9' + Math.round(total).toLocaleString('en-IN');
    document.getElementById('stampRate').textContent = stampRate + '%';
    document.getElementById('regRate').textContent = regRate + '%';
    document.getElementById('regCap').textContent = '\u20B9' + regCap.toLocaleString('en-IN');
    document.getElementById('rebate').textContent = buyer === 'female' ? '\u20B9' + Math.round(val * 0.001).toLocaleString('en-IN') : '\u20B90';
}
document.getElementById('propVal').addEventListener('input', calcStamp);
calcStamp();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
