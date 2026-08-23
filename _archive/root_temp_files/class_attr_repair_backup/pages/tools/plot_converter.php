<section class="py-5" class="style-30941">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><?php echo __('tool_plot_converter_title', [], 'Plot Size Converter'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_plot_converter_subtitle', [], 'Convert between Square feet, Gaj, Yard, and Bigha'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('enter_value', [], 'Enter Value'); ?></label>
                            <input type="number" class="form-control form-control-lg" id="convVal" value="1000" oninput="convertAll()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('select_unit', [], 'Select Unit'); ?></label>
                            <select class="form-select" id="convFrom" onchange="convertAll()">
                                <option value="sqft"><?php echo __('sqft', [], 'Square Feet (sq ft)'); ?></option>
                                <option value="gaj"><?php echo __('gaj', [], 'Gaj'); ?></option>
                                <option value="yard"><?php echo __('sq_yard', [], 'Square Yard'); ?></option>
                                <option value="meter"><?php echo __('sq_meter', [], 'Square Meter'); ?></option>
                                <option value="bigha"><?php echo __('bigha', [], 'Bigha'); ?></option>
                                <option value="katha"><?php echo __('katha', [], 'Katha'); ?></option>
                                <option value="marla"><?php echo __('marla', [], 'Marla'); ?></option>
                            </select>
                        </div>
                        <hr>
                        <div class="row text-center" id="convResults">
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('sqft', [], 'Square Feet'); ?></small>
                                    <h6 class="text-primary mb-0" id="rSqft">1,000</h6>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('gaj', [], 'Gaj'); ?></small>
                                    <h6 class="text-success mb-0" id="rGaj">111</h6>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('sq_yard', [], 'Square Yard'); ?></small>
                                    <h6 class="text-info mb-0" id="rYard">111</h6>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('sq_meter', [], 'Square Meter'); ?></small>
                                    <h6 class="text-warning mb-0" id="rMeter">93</h6>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('bigha', [], 'Bigha'); ?></small>
                                    <h6 class="text-danger mb-0" id="rBigha">0.67</h6>
                                </div>
                            </div>
                            <div class="col-4 mb-3">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted"><?php echo __('katha', [], 'Katha'); ?></small>
                                    <h6 class="text-secondary mb-0" id="rKatha">5.33</h6>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('plot_converter_note', [], '1 Gaj = 9 sq ft, 1 Bigha = 1500 sq ft (UP), 1 Katha = 187.5 sq ft (varies by state)'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function convertAll() {
    const val = parseFloat(document.getElementById('convVal').value) || 0;
    const from = document.getElementById('convFrom').value;
    const toSqft = { sqft: 1, gaj: 9, yard: 9, meter: 10.764, bigha: 1500, katha: 187.5, marla: 272.25 };
    const sqft = val * (toSqft[from] || 1);
    document.getElementById('rSqft').textContent = Math.round(sqft).toLocaleString('en-IN');
    document.getElementById('rGaj').textContent = (sqft / 9).toFixed(1).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('rYard').textContent = (sqft / 9).toFixed(1).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('rMeter').textContent = (sqft / 10.764).toFixed(1).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('rBigha').textContent = (sqft / 1500).toFixed(4);
    document.getElementById('rKatha').textContent = (sqft / 187.5).toFixed(2);
}
convertAll();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
