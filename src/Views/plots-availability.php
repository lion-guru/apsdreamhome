<?php
// plots-availability.php
// Modern interactive plot status page for APS Dream Homes
require_once __DIR__ . '/includes/templates/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plots Availability | APS Dream Homes</title>
    <link rel="stylesheet" href="css/home.css">
    <style>
        .plot-status-table th, .plot-status-table td { text-align: center; }
        .plot-status-available { background: #e8f5e9; color: #388e3c; font-weight: bold; }
        .plot-status-booked { background: #fffde7; color: #fbc02d; font-weight: bold; }
        .plot-status-sold { background: #ffebee; color: #c62828; font-weight: bold; }
        .plot-map-img { max-width: 100%; border-radius: 12px; box-shadow: 0 2px 16px #0001; }
        .legend-dot { display: inline-block; width: 16px; height: 16px; border-radius: 50%; margin-right: 6px; }
        .legend-available { background: #66bb6a; }
        .legend-booked { background: #ffd54f; }
        .legend-sold { background: #e57373; }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4 text-center text-primary fw-bold">Plots Availability</h1>
    <p class="lead text-center mb-5">Check real-time status of plots in our colonies. See which plots are available, booked, or sold out.</p>
    <div class="mb-4 text-center">
        <span class="legend-dot legend-available"></span> Available
        <span class="legend-dot legend-booked ms-3"></span> Booked
        <span class="legend-dot legend-sold ms-3"></span> Sold
    </div>
    <!-- Example for Suryoday Colony -->
    <div class="card shadow-lg mb-5">
        <div class="card-header bg-primary text-white fw-bold fs-5">Suryoday Colony</div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5>Plot Status Summary</h5>
                    <?php
                    // Sample plot data for Suryoday Colony
                    $plots = [
                        [ 'number' => '1',  'status' => 'sold' ],
                        [ 'number' => '2',  'status' => 'sold' ],
                        [ 'number' => '3',  'status' => 'booked' ],
                        [ 'number' => '4',  'status' => 'available' ],
                        [ 'number' => '5',  'status' => 'available' ],
                        [ 'number' => '6',  'status' => 'sold' ],
                        [ 'number' => '7',  'status' => 'booked' ],
                        [ 'number' => '8',  'status' => 'available' ],
                        [ 'number' => '9',  'status' => 'sold' ],
                        [ 'number' => '10', 'status' => 'available' ],
                        // ... add more as needed
                    ];
                    $summary = ['sold'=>0, 'booked'=>0, 'available'=>0];
                    foreach ($plots as $p) $summary[$p['status']]++;
                    ?>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-available"></span>Available</span>
                            <span class="fw-bold text-success"><?=$summary['available']?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-booked"></span>Booked</span>
                            <span class="fw-bold text-warning"><?=$summary['booked']?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><span class="legend-dot legend-sold"></span>Sold</span>
                            <span class="fw-bold text-danger"><?=$summary['sold']?></span>
                        </li>
                    </ul>
                    <h6 class="mb-2">Plots Status Table</h6>
                    <table class="table table-bordered plot-status-table">
                        <thead class="table-light">
                        <tr><th>Plot No.</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($plots as $p): ?>
                            <tr>
                                <td><?=$p['number']?></td>
                                <td class="plot-status-<?=$p['status']?> text-capitalize"><?=ucfirst($p['status'])?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <h5 class="mb-3">Colony Map (Sample)</h5>
                    <img src="assets/img/suryoday-colony-map-sample.jpg" alt="Suryoday Colony Map" class="plot-map-img mb-2">
                    <div class="small text-muted">*Map numbering matches table above. For interactive map, contact admin.</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Repeat above block for other colonies/projects as needed -->
</div>
<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
</body>
</html>
