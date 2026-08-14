<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Calculator</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-calculator me-3 text-success"></i><?= ($page_title ?? 'Sustainability Calculator') ?></h1>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-success"></i>Calculate Your Property's Sustainability Score</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form id="sustainabilityForm" onsubmit="event.preventDefault(); calculateScore();">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Energy Rating (1-5)</label>
                                <select name="energy_rating" class="form-select" required>
                                    <option value="">Select...</option><option value="1">1 - Poor</option><option value="2">2 - Below Average</option><option value="3">3 - Average</option><option value="4">4 - Good</option><option value="5">5 - Excellent</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Water Efficiency</label>
                                <select name="water_efficiency" class="form-select" required>
                                    <option value="">Select...</option><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sustainable Materials (%)</label>
                                <input type="number" name="sustainable_materials" class="form-control" min="0" max="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Air Quality</label>
                                <select name="air_quality" class="form-select" required>
                                    <option value="">Select...</option><option value="fair">Fair</option><option value="good">Good</option><option value="excellent">Excellent</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Innovative Features (count)</label>
                                <input type="number" name="innovative_features" class="form-control" min="0" max="10" value="0">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-calculator me-2"></i>Calculate Score</button>
                            </div>
                        </div>
                    </form>
                    <div id="scoreResult" class="mt-4" style="display:none;">
                        <hr>
                        <div class="text-center">
                            <h3>Sustainability Score: <span id="scoreValue" class="text-success">0</span>/100</h3>
                            <h4>Rating: <span id="ratingValue" class="badge bg-success fs-5">Certified</span></h4>
                            <div class="progress mt-3" style="height:25px;">
                                <div id="scoreBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width:0%">0%</div>
                            </div>
                            <div id="improvements" class="mt-3 text-start"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function calculateScore() {
    const form = document.getElementById('sustainabilityForm');
    const data = {
        energy_rating: parseInt(form.energy_rating.value),
        water_efficiency: form.water_efficiency.value,
        sustainable_materials: parseInt(form.sustainable_materials.value) || 0,
        air_quality: form.air_quality.value,
        innovative_features: parseInt(form.innovative_features.value) || 0
    };
    const base = '<?= $base ?? BASE_URL ?>';
    fetch(base + 'sustainability/calculator', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
        if (res.success) {
            const d = res.data;
            document.getElementById('scoreValue').textContent = d.percentage;
            document.getElementById('ratingValue').textContent = d.rating;
            document.getElementById('ratingValue').className = 'badge bg-' + (d.percentage >= 80 ? 'success' : d.percentage >= 60 ? 'warning' : 'danger') + ' fs-5';
            document.getElementById('scoreBar').style.width = d.percentage + '%';
            document.getElementById('scoreBar').textContent = d.percentage + '%';
            const impDiv = document.getElementById('improvements');
            impDiv.innerHTML = '<h6>Suggestions:</h6><ul class="list-group">' + (d.improvements || []).map(i => '<li class="list-group-item small">' + i + '</li>').join('') + '</ul>';
            document.getElementById('scoreResult').style.display = 'block';
        } else {
            alert('Error: ' + (res.error || 'Calculation failed'));
        }
    }).catch(e => alert('Request failed: ' + e.message));
}
</script>
