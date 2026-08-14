<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>virtual-tour">Virtual Tour</a></li>
                    <li class="breadcrumb-item active">AR Furniture</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-couch me-3 text-info"></i><?= ($page_title ?? 'AR Furniture') ?></h1>
        </div>
    </div>

    <?php $furniture_catalog = $furniture_catalog ?? []; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div id="arViewer" style="height:400px;background:linear-gradient(135deg,#0d9488,#0f766e);" class="rounded-top d-flex align-items-center justify-content-center">
                        <div class="text-center text-white">
                            <i class="fas fa-cube fa-5x mb-3 opacity-50"></i>
                            <p class="lead">AR Furniture Placement</p>
                            <button class="btn btn-light btn-lg" onclick="alert('AR camera would initialize')"><i class="fas fa-camera me-2"></i>Open AR Camera</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-th me-2 text-info"></i>Room Dimensions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form id="roomForm" onsubmit="event.preventDefault(); submitRoomData();">
    <?php echo CSRFProtection::csrfField(); ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Width (ft)</label>
                                <input type="number" name="width" class="form-control" value="12" min="5" max="50" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Length (ft)</label>
                                <input type="number" name="length" class="form-control" value="15" min="5" max="50" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Height (ft)</label>
                                <input type="number" name="height" class="form-control" value="10" min="8" max="20" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-info"><i class="fas fa-cube me-2"></i>Visualize Room</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <?php foreach ($furniture_catalog as $room => $items): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white border-0"><h6 class="mb-0"><i class="fas fa-<?= $room === 'living_room' ? 'tv' : ($room === 'bedroom' ? 'bed' : 'utensils') ?> me-2 text-info"></i><?= ucfirst(str_replace('_', ' ', $room)) ?></h6></div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <?php foreach ($items as $item): ?>
                        <div class="col-6">
                            <button class="btn btn-outline-info btn-sm w-100" onclick="alert('Placing <?= ($item['name'] ?? '') ?> in AR view')">
                                <i class="fas fa-cube me-1"></i><?= ($item['name'] ?? 'Item') ?><br>
                                <small>₹<?= number_format($item['price'] ?? 0) ?></small>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($furniture_catalog)): ?>
            <div class="alert alert-info">Furniture catalog is empty.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function submitRoomData() {
    const form = document.getElementById('roomForm');
    const data = {
        room_dimensions: {width: parseFloat(form.width.value) || 0, length: parseFloat(form.length.value) || 0, height: parseFloat(form.height.value) || 0},
        furniture_items: [],
        coordinates: []
    };
    const base = '<?= $base ?? BASE_URL ?>';
    fetch(base + 'virtual-tour/ar-furniture', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(res => {
        if (res.success) {
            alert('Room visualized! AR markers generated: ' + ((res.data.ar_markers?.floor_markers?.length || 0) + (res.data.ar_markers?.wall_markers?.length || 0)));
        } else {
            alert('Error: ' + (res.error || 'Failed'));
        }
    }).catch(() => {
        // Fallback if endpoint is not available
        alert('Room dimensions submitted. AR visualization would render here.');
    });
}
</script>
