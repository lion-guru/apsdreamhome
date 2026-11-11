<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus"></i> Add Plot</h2>
                <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="card aps-cp-card">
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>admin/plots" id="plotForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                        <ul class="nav nav-tabs mb-3" id="plotTabs">
                            <li class="nav-item"><a class="nav-link active" href="#basic" data-bs-toggle="tab">Basic Info</a></li>
                            <li class="nav-item"><a class="nav-link" href="#dims" data-bs-toggle="tab">Dimensions & Area</a></li>
                            <li class="nav-item"><a class="nav-link" href="#price" data-bs-toggle="tab">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="#extra" data-bs-toggle="tab">Features</a></li>
                        </ul>

                        <div class="tab-content">
                            <!-- Basic Info -->
                            <div class="tab-pane fade show active" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Colony *</label>
                                            <select class="form-select" name="colony_id" id="colony_id" required onchange="filterGataParcels()">
                                                <option value="">Select Colony</option>
                                                <?php if (empty($colonies)): ?>
                                                    <option value="" disabled>No colonies found</option>
                                                <?php endif; ?>
                                                <?php foreach ($colonies as $colony): ?>
                                                    <option value="<?= $colony['id'] ?? '' ?>">
                                                        <?= htmlspecialchars(($colony['state_name'] ?? '') . ' > ' . ($colony['district_name'] ?? '') . ' > ' . ($colony['colony_name'] ?? $colony['name'] ?? '')) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (empty($colonies)): ?>
                                                <small class="text-warning d-block mt-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    No colonies exist yet.
                                                    <a href="<?= BASE_URL ?>/admin/colonies/create" class="fw-bold">Create Colony First</a>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Plot Number *</label>
                                            <input type="text" class="form-control" name="plot_number" required placeholder="e.g., A-101">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Block</label>
                                            <input type="text" class="form-control" name="block" placeholder="e.g., A, B, C">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Gata / Khasra <span class="text-muted">(zameen source — cutting ke time)</span></label>
                                            <select class="form-select" name="land_parcel_id" id="land_parcel_id">
                                                <option value="">— Bina gata ke (baad me link kar sakte ho) —</option>
                                                <?php foreach (($gata_parcels ?? []) as $gp): ?>
                                                    <option value="<?= (int)$gp['id'] ?>" data-colony="<?= (int)$gp['colony_id'] ?>">
                                                        <?= htmlspecialchars(($gp['khasra_no'] ?? '') . ' — ' . ($gp['village'] ?? '')) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted d-block mt-1">Sirf chuni colony ke gata dikhenge. Kisan naam baad me <a href="<?= BASE_URL ?>/admin/land-plot-link" target="_blank">Plot–Gata–Kisan desk</a> pe update karo.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Gata (free text, agar parcel me na ho)</label>
                                            <input type="text" class="form-control" name="gata_number" id="gata_number" placeholder="e.g., Gata 1206">
                                            <small class="text-muted">Parcel chuna to ye ignore hoga (parcel jeetega).</small>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                function filterGataParcels() {
                                    var cid = document.getElementById('colony_id').value;
                                    var sel = document.getElementById('land_parcel_id');
                                    for (var i = 0; i < sel.options.length; i++) {
                                        var oc = sel.options[i].getAttribute('data-colony');
                                        sel.options[i].style.display = (!oc || oc === cid) ? '' : 'none';
                                    }
                                    if (sel.selectedOptions.length && sel.selectedOptions[0].style.display === 'none') sel.value = '';
                                }
                                document.addEventListener('DOMContentLoaded', filterGataParcels);
                                </script>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Plot Type</label>
                                            <select class="form-select" name="plot_type">
                                                <option value="residential">Residential</option>
                                                <option value="commercial">Commercial</option>
                                                <option value="industrial">Industrial</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="available">Available</option>
                                                <option value="booked">Booked</option>
                                                <option value="hold">Hold</option>
                                                <option value="reserved">Reserved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Facing</label>
                                            <select class="form-select" name="facing">
                                                <option value="">Select</option>
                                                <option value="north">North</option>
                                                <option value="south">South</option>
                                                <option value="east">East</option>
                                                <option value="west">West</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dimensions -->
                            <div class="tab-pane fade" id="dims">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Width (ft)</label>
                                        <input type="number" class="form-control" name="width_ft" step="0.01" id="width_ft" onchange="calcCreateDim()">
                                        <small class="text-muted">e.g., 20 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Length (ft)</label>
                                        <input type="number" class="form-control" name="length_ft" step="0.01" id="length_ft" onchange="calcCreateDim()">
                                        <small class="text-muted">e.g., 40 for 20x40</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Dimension Label</label>
                                        <input type="text" class="form-control" name="dimension_label" id="dimension_label" placeholder="20x40" readonly>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqft) *</label>
                                        <input type="number" class="form-control" name="area_sqft" min="1" step="0.01" required id="area_sqft" onchange="calcCreatePps()">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Area (sqm)</label>
                                        <input type="number" class="form-control" name="area_sqm" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Frontage (ft)</label>
                                        <input type="number" class="form-control" name="frontage_ft" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Depth (ft)</label>
                                        <input type="number" class="form-control" name="depth_ft" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Road Width (ft)</label>
                                        <input type="number" class="form-control" name="road_width_ft" step="0.01" value="0" id="road_width_ft" onchange="calcCreatePps()">
                                    </div>
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Quick Select:</strong>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="setCreateDims(20,40)">20x40</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(20,50)">20x50</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(25,48)">25x48</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(25,60)">25x60</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(30,50)">30x50</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(30,60)">30x60</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setCreateDims(40,60)">40x60</button>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="tab-pane fade" id="price">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Base Price per Sqft *</label>
                                        <input type="number" class="form-control" name="base_price_per_sqft" min="0" step="0.01" id="base_pps" value="1500" onchange="calcCreatePps()">
                                        <small class="text-muted">Standard colony rate</small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Current Price per Sqft *</label>
                                        <input type="number" class="form-control" name="price_per_sqft" min="0" step="0.01" required id="price_per_sqft" onchange="calcCreatePps()">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Total Price</label>
                                        <input type="number" class="form-control" name="total_price" min="0" step="0.01" id="total_price" readonly>
                                        <small class="text-muted" id="plc_breakdown"></small>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Negotiated / Deal Price</label>
                                        <input type="number" class="form-control" name="negotiated_price" step="0.01" placeholder="Only if different from total">
                                    </div>
                                </div>
                                <div class="card border-0 bg-light mt-3">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold"><i class="fas fa-robot text-primary me-2"></i>AI Price Suggestion</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="aiValBtn" onclick="getAIValuation()">
                                                <i class="fas fa-wand-magic-sparkles me-1"></i> Suggest Price
                                            </button>
                                        </div>
                                        <div id="aiValResult" class="text-muted">
                                            Uses colony demand, facing, road width & PLC to suggest a fair price. Click "Suggest Price" — nothing is applied until you click it.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="tab-pane fade" id="extra">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="corner_plot" id="corner_plot" value="1">
                                            <label class="form-check-label" for="corner_plot">Corner Plot</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="park_facing" id="park_facing" value="1">
                                            <label class="form-check-label" for="park_facing">Park Facing</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Description</label>
                                        <button type="button" class="btn btn-sm btn-purple" id="aiDescBtn"
                                                onclick="generateAIDescription()">
                                            <i class="fas fa-wand-magic-sparkles me-1"></i> AI Generate
                                        </button>
                                    </div>
                                    <textarea class="form-control" id="plotDescription" name="description" rows="3" placeholder="Optional plot description, features, or notes"></textarea>
                                    <small class="text-muted"><i class="fas fa-robot me-1"></i>AI generates SEO description from colony, area, facing & price. You can edit before saving.</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>admin/plots" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Plot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calcCreateDim() {
    const w = parseFloat(document.getElementById('width_ft').value) || 0;
    const l = parseFloat(document.getElementById('length_ft').value) || 0;
    if (w > 0 && l > 0) {
        document.getElementById('dimension_label').value = w + 'x' + l;
        document.getElementById('area_sqft').value = (w * l).toFixed(2);
        calcCreatePps();
    }
}
const PLC_RATES = <?= json_encode($plc_rates ?? ['corner_pct' => 10, 'park_pct' => 5, 'wideroad_pct' => 5, 'wideroad_min_ft' => 40]) ?>;
function calcCreatePps() {
    const area = parseFloat(document.getElementById('area_sqft').value) || 0;
    const base = parseFloat(document.getElementById('base_pps').value) || 0;
    const corner = document.getElementById('corner_plot') && document.getElementById('corner_plot').checked;
    const park = document.getElementById('park_facing') && document.getElementById('park_facing').checked;
    const road = parseFloat(document.getElementById('road_width_ft').value) || 0;
    let pct = 0;
    const parts = [];
    if (corner) { pct += parseFloat(PLC_RATES.corner_pct) || 0; parts.push('Corner +' + (parseFloat(PLC_RATES.corner_pct) || 0) + '%'); }
    if (park) { pct += parseFloat(PLC_RATES.park_pct) || 0; parts.push('Park +' + (parseFloat(PLC_RATES.park_pct) || 0) + '%'); }
    if (road >= (parseFloat(PLC_RATES.wideroad_min_ft) || 40)) { pct += parseFloat(PLC_RATES.wideroad_pct) || 0; parts.push('Wide road +' + (parseFloat(PLC_RATES.wideroad_pct) || 0) + '%'); }
    const finalPps = base * (1 + pct / 100);
    document.getElementById('price_per_sqft').value = finalPps.toFixed(2);
    document.getElementById('total_price').value = (area * finalPps).toFixed(2);
    const bd = document.getElementById('plc_breakdown');
    if (bd) bd.textContent = parts.length ? ('Base + PLC: ' + parts.join(', ')) : 'No PLC applied';
}
['corner_plot', 'park_facing'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', calcCreatePps);
});
function setCreateDims(w, l) {
    document.getElementById('width_ft').value = w;
    document.getElementById('length_ft').value = l;
    calcCreateDim();
}

// AI Description Generator — calls POST /ai/content/description
function generateAIDescription() {
    const btn = document.getElementById('aiDescBtn');
    const descBox = document.getElementById('plotDescription');
    if (!descBox || !btn) return;
    const colonySel = document.getElementById('colony_id');
    const colonyName = colonySel && colonySel.selectedIndex > 0 ? colonySel.options[colonySel.selectedIndex].text : '';
    const area = document.getElementById('area_sqft') ? document.getElementById('area_sqft').value : '';
    const price = document.getElementById('total_price') ? document.getElementById('total_price').value : '';
    const typeSel = document.querySelector('[name="plot_type"]');
    const plotType = typeSel ? typeSel.value : 'plot';

    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';

    fetch('/apsdreamhome/ai/content/description', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            title: 'Plot ' + (document.querySelector('[name="plot_number"]') || {}).value,
            location: colonyName,
            colony: colonyName,
            area_sqft: area,
            price: price,
            type: plotType,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.description) {
            descBox.value = data.description;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Generated!';
            setTimeout(() => { btn.disabled = false; btn.innerHTML = orig; }, 2000);
        } else {
            btn.disabled = false;
            btn.innerHTML = orig;
            alert('AI generation failed: ' + (data.error || 'unknown error'));
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
        alert('AI generation failed (network error)');
    });
}

// AI Price Suggestion — calls POST /api/ai-valuation/calculator
function getAIValuation() {
    const btn = document.getElementById('aiValBtn');
    const box = document.getElementById('aiValResult');
    if (!btn || !box) return;

    const colonySel = document.getElementById('colony_id');
    const colonyId = colonySel ? colonySel.value : '';
    const area = document.getElementById('area_sqft') ? document.getElementById('area_sqft').value : '';
    const facingEl = document.querySelector('[name="facing"]');
    const facing = facingEl ? facingEl.value : '';
    const road = document.getElementById('road_width_ft') ? document.getElementById('road_width_ft').value : '';
    const corner = document.getElementById('corner_plot') ? document.getElementById('corner_plot').value : '';
    const park = document.getElementById('park_facing') ? document.getElementById('park_facing').value : '';
    const colonyName = colonySel && colonySel.selectedIndex > 0 ? colonySel.options[colonySel.selectedIndex].text : '';

    if (!colonyId || !area || parseFloat(area) <= 0) {
        box.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Select colony and enter area first.</span>';
        return;
    }

    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Valuing...';

    const payload = new URLSearchParams({
        colony_id: colonyId,
        area: area,
        location: colonyName,
        facing: facing,
        road_width: road,
        corner_plot: corner || '',
        park_facing: park || '',
    });

    fetch('/apsdreamhome/api/ai-valuation/calculator', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload.toString(),
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = orig;
        const v = data.valuation || {};
        if (data.success && v.estimated_value) {
            const inr = n => '₹' + Number(n || 0).toLocaleString('en-IN');
            const range = v.price_range || {};
            let factorsHtml = '';
            if (Array.isArray(v.factors) && v.factors.length) {
                factorsHtml = '<div class="mt-2 small">' + v.factors.map(f =>
                    '<div class="d-flex justify-content-between border-top pt-1"><span>' + (f.name || '') + '</span><span class="' + (parseFloat(f.impact) >= 0 ? 'text-success' : 'text-danger') + '">' + (parseFloat(f.impact) >= 0 ? '+' : '') + (parseFloat(f.impact) * 100).toFixed(1) + '%</span></div>'
                ).join('') + '</div>';
            }
            box.innerHTML =
                '<div class="d-flex flex-wrap gap-4">' +
                '<div><div class="small text-muted">Suggested value</div><div class="fs-5 fw-bold text-dark">' + inr(v.estimated_value) + '</div></div>' +
                '<div><div class="small text-muted">Per sqft</div><div class="fs-5 fw-bold text-primary">' + inr(v.price_per_sqft) + '</div></div>' +
                '<div><div class="small text-muted">Confidence</div><div class="fs-5">' + (v.confidence_level || '') + ' <small class="text-muted">(' + Math.round((v.confidence || 0) * 100) + '%)</small></div></div>' +
                '</div>' +
                (range.low && range.high ? '<div class="small text-muted mt-1">Fair range: ' + inr(range.low) + ' – ' + inr(range.high) + '</div>' : '') +
                '<button type="button" class="btn btn-sm btn-primary mt-2" onclick="applyAIValuation(' + v.price_per_sqft + ')"><i class="fas fa-check me-1"></i>Use this price per sqft</button>' +
                factorsHtml;
        } else {
            box.innerHTML = '<span class="text-danger">AI valuation failed: ' + (data.error || 'unknown error') + '</span>';
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
        box.innerHTML = '<span class="text-danger">AI valuation failed (network error)</span>';
    });
}

function applyAIValuation(pps) {
    const el = document.getElementById('price_per_sqft');
    if (el && pps > 0) {
        el.value = parseFloat(pps).toFixed(2);
        calcCreatePps();
    }
}
</script>
