<section class="py-5" style="background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-ruler-combined me-2"></i>Plot Area Converter</h1>
            <p class="text-white-50">Square Feet, Square Meter, Acre, Bigha, Gaj, Katha, Marla — all units mein convert karein instantly</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Area Value</label>
                                <input type="number" class="form-control form-control-lg" id="convVal" value="1000" min="0" step="any" placeholder="Enter value">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">From Unit</label>
                                <select class="form-select" id="fromUnit">
                                    <option value="sqft" selected>Sq Ft (Square Feet)</option>
                                    <option value="sqm">Sq M (Square Meter)</option>
                                    <option value="acre">Acre</option>
                                    <option value="hectare">Hectare</option>
                                    <option value="bigha">Bigha (UP)</option>
                                    <option value="bigha_bi">Bigha (Bihar)</option>
                                    <option value="gaj">Gaj / Gajam</option>
                                    <option value="katha">Katha (UP)</option>
                                    <option value="marla">Marla</option>
                                    <option value="guntha">Guntha</option>
                                    <option value="ground">Ground (Chennai)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted">&nbsp;</label>
                                <button class="btn btn-primary w-100 form-control-lg" onclick="swapUnits()"><i class="fas fa-exchange-alt me-1"></i>Swap</button>
                            </div>
                        </div>
                        <div class="bg-dark text-white rounded-4 p-4 text-center mb-4">
                            <small class="text-white-50">Converted Result</small>
                            <h2 class="fw-bold mb-0" id="convResult" style="font-size:2.2rem;">92.90</h2>
                            <small id="convUnitLabel" class="text-white-50">Square Meters</small>
                        </div>

                        <h6 class="fw-bold mb-3"><i class="fas fa-table me-1"></i> All Conversions</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="allConversions">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Unit</th>
                                        <th class="text-end">Value</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <p class="text-muted small mt-2"><i class="fas fa-info-circle me-1"></i>UP standard: 1 Bigha = 27,000 sqft, 1 Katha = 1,361 sqft, 1 Marla = 272.25 sqft, 1 Guntha = 1,089 sqft</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const rates = {
    sqft: 1, sqm: 0.092903, acre: 43560, hectare: 107639,
    bigha: 27000, bigha_bi: 27220, gaj: 9, katha: 1361,
    marla: 272.25, guntha: 1089, ground: 2400
};
const unitLabels = {
    sqft: 'Square Feet', sqm: 'Square Meter', acre: 'Acre',
    hectare: 'Hectare', bigha: 'Bigha (UP)', bigha_bi: 'Bigha (Bihar)',
    gaj: 'Gaj', katha: 'Katha (UP)', marla: 'Marla',
    guntha: 'Guntha', ground: 'Ground (Chennai)'
};
function convertAll() {
    const val = parseFloat(document.getElementById('convVal').value) || 0;
    const from = document.getElementById('fromUnit').value;
    const sqftVal = val * rates[from];
    const result = sqftVal / rates[from];
    document.getElementById('convResult').textContent = val.toLocaleString('en-IN', {maximumFractionDigits: 4});
    document.getElementById('convUnitLabel').textContent = unitLabels[from];
    const tbody = document.querySelector('#allConversions tbody');
    tbody.innerHTML = '';
    for (const [key, rate] of Object.entries(rates)) {
        if (key === from) continue;
        const converted = sqftVal / rate;
        const isActive = key === from ? 'table-primary' : '';
        tbody.innerHTML += '<tr class="'+isActive+'"><td><i class="fas fa-ruler me-1 text-primary"></i>' + unitLabels[key] + '</td><td class="text-end fw-bold">' + converted.toLocaleString('en-IN', {maximumFractionDigits: 4}) + ' <small class="text-muted">' + key.toUpperCase() + '</small></td></tr>';
    }
}
function swapUnits() {
    const sel = document.getElementById('fromUnit');
    const options = sel.options;
    for (let i = 0; i < options.length; i++) {
        if (options[i].selected && i < options.length - 1) {
            options[i].selected = false;
            options[i + 1].selected = true;
        } else if (options[i].selected && i === options.length - 1) {
            options[i].selected = false;
            options[0].selected = true;
        }
    }
    convertAll();
}
document.getElementById('convVal').addEventListener('input', convertAll);
document.getElementById('fromUnit').addEventListener('change', convertAll);
convertAll();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>