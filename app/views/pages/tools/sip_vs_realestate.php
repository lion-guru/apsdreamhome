<section class="py-5 text-white" style="background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%)">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_sip_vs_re_title', [], 'SIP vs Real Estate Returns'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_sip_vs_re_subtitle', [], 'Compare returns between Mutual Funds and Real Estate'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('monthly_sip_rs', [], 'Monthly SIP (â‚¹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="sipAmt" value="10000" oninput="calcSIP()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('property_investment_rs', [], 'Property Investment (â‚¹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="propInv" value="5000000" oninput="calcSIP()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('tenure_years', [], 'Tenure (Years)'); ?></label>
                                <input type="number" class="form-control" id="sipTenure" value="10" min="1" max="30" oninput="calcSIP()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('sip_return_pct', [], 'SIP Return (%)'); ?></label>
                                <input type="number" class="form-control" id="sipReturn" value="12" oninput="calcSIP()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('property_appreciation', [], 'Property Appreciation (%)'); ?></label>
                                <input type="number" class="form-control" id="propApp" value="8" oninput="calcSIP()">
                            </div>
                        </div>
                        <div class="row g-3 text-center">
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('sip_future_value', [], 'SIP Future Value'); ?></small>
                                    <h4 class="text-success mb-0" id="sipFV">â‚¹23,00,000</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('property_future_value', [], 'Property Future Value'); ?></small>
                                    <h4 class="text-primary mb-0" id="propFV">â‚¹10,80,000</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcSIP() {
    const sip = parseFloat(document.getElementById('sipAmt').value) || 0;
    const propInv = parseFloat(document.getElementById('propInv').value) || 0;
    const tenure = parseInt(document.getElementById('sipTenure').value) || 10;
    const sipRet = (parseFloat(document.getElementById('sipReturn').value) || 12) / 100;
    const propApp = (parseFloat(document.getElementById('propApp').value) || 8) / 100;
    const r = sipRet / 12;
    const n = tenure * 12;
    const sipFV = sip * ((Math.pow(1 + r, n) - 1) / r) * (1 + r);
    const propFV = propInv * Math.pow(1 + propApp, tenure);
    document.getElementById('sipFV').textContent = '\u20B9' + Math.round(sipFV).toLocaleString('en-IN');
    document.getElementById('propFV').textContent = '\u20B9' + Math.round(propFV).toLocaleString('en-IN');
}
calcSIP();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
