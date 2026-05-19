<section class="py-5" style="background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Plot Size Converter</h1>
            <p class="text-white-50">Square Feet, Square Meter, Acre, Bigha, Gaj — sabhi units mein convert karein</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Value</label>
                            <input type="number" class="form-control form-control-lg" id="convVal" value="1000" oninput="convertFrom()">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">From</label>
                                <select class="form-select" id="fromUnit" onchange="convertFrom()">
                                    <option value="sqft">Square Feet (sq ft)</option>
                                    <option value="sqm">Square Meter (sq m)</option>
                                    <option value="acre">Acre</option>
                                    <option value="hectare">Hectare</option>
                                    <option value="bigha">Bigha (UP)</option>
                                    <option value="bigha_bi">Bigha (Bihar)</option>
                                    <option value="gaj">Gaj / Gajam</option>
                                    <option value="marla">Marla</option>
                                    <option value="gunta">Gunta</option>
                                    <option value="kanal">Kanal</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To</label>
                                <select class="form-select" id="toUnit" onchange="convertFrom()">
                                    <option value="sqm">Square Meter (sq m)</option>
                                    <option value="sqft">Square Feet (sq ft)</option>
                                    <option value="acre">Acre</option>
                                    <option value="hectare">Hectare</option>
                                    <option value="bigha">Bigha (UP)</option>
                                    <option value="bigha_bi">Bigha (Bihar)</option>
                                    <option value="gaj">Gaj / Gajam</option>
                                    <option value="marla">Marla</option>
                                    <option value="gunta">Gunta</option>
                                    <option value="kanal">Kanal</option>
                                </select>
                            </div>
                        </div>
                        <div class="bg-dark text-white rounded-4 p-4 text-center">
                            <small class="text-white-50">Converted Value</small>
                            <h2 class="fw-bold mb-0" id="convResult">92.90</h2>
                            <small id="convUnitLabel">Square Meters</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const rates = {
    sqft: 1, sqm: 10.764, acre: 43560, hectare: 107639,
    bigha: 27000, bigha_bi: 27220, gaj: 9, marla: 272.25, gunta: 1089, kanal: 5445
};
const unitLabels = {
    sqft: 'Square Feet', sqm: 'Square Meters', acre: 'Acres',
    hectare: 'Hectares', bigha: 'Bigha (UP)', bigha_bi: 'Bigha (Bihar)',
    gaj: 'Gaj', marla: 'Marla', gunta: 'Gunta', kanal: 'Kanal'
};
function convertFrom() {
    const val = parseFloat(document.getElementById('convVal').value) || 0;
    const from = document.getElementById('fromUnit').value;
    const to = document.getElementById('toUnit').value;
    const sqft = val * rates[from];
    const result = sqft / rates[to];
    document.getElementById('convResult').textContent = result.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    document.getElementById('convUnitLabel').textContent = unitLabels[to] || to;
}
convertFrom();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
