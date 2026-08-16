<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_stamp_duty_title', [], 'Stamp Duty & Registration Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_stamp_duty_subtitle', [], 'Calculate total cost before buying property'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('property_value_rs', [], 'Property Value (₹)'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="propVal" value="5000000" oninput="calcStamp()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('state', [], 'State'); ?></label>
                            <select class="form-select" id="stateSel" onchange="calcStamp()">
                                <option value="7"><?php echo __('up_stamp', [], 'Uttar Pradesh (7% Stamp Duty)'); ?></option>
                                <option value="6"><?php echo __('bihar', [], 'Bihar'); ?> (6%)</option>
                                <option value="5"><?php echo __('mp', [], 'Madhya Pradesh'); ?> (5%)</option>
                                <option value="6"><?php echo __('rajasthan', [], 'Rajasthan'); ?> (6%)</option>
                                <option value="5"><?php echo __('delhi', [], 'Delhi'); ?> (5%)</option>
                                <option value="6"><?php echo __('maharashtra', [], 'Maharashtra'); ?> (6%)</option>
                            </select>
                        </div>
                        <div class="row g-3 text-center" id="stampResults">
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted"><?php echo __('stamp_duty', [], 'Stamp Duty'); ?></small>
                                    <h5 class="text-primary mb-0" id="stampDuty">₹3,50,000</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted"><?php echo __('registration_1pct', [], 'Registration (1%)'); ?></small>
                                    <h5 class="text-success mb-0" id="regFee">₹50,000</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted"><?php echo __('total_cost', [], 'Total Cost'); ?></small>
                                    <h5 class="text-danger mb-0" id="totalCost">₹55,50,000</h5>
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
function calcStamp() {
    const val = parseFloat(document.getElementById('propVal').value) || 0;
    const rate = parseFloat(document.getElementById('stateSel').value) || 7;
    const stamp = val * rate / 100;
    const reg = val * 0.01;
    document.getElementById('stampDuty').textContent = '₹' + Math.round(stamp).toLocaleString('en-IN');
    document.getElementById('regFee').textContent = '₹' + Math.round(reg).toLocaleString('en-IN');
    document.getElementById('totalCost').textContent = '₹' + Math.round(val + stamp + reg).toLocaleString('en-IN');
}
calcStamp();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
<?php $extraCss = '.card-body h5 { font-size: 1.25rem; }'; ?>
