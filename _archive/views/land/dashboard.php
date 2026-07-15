<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-draw-polygon me-2 text-success"></i>Plotting Dashboard</h4>
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-info text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['total_plots'] ?? 0) ?></h3>
                    <small>Total Plots</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['available'] ?? 0) ?></h3>
                    <small>Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['booked'] ?? 0) ?></h3>
                    <small>Booked</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= (int)($stats['colonies'] ?? 0) ?></h3>
                    <small>Colonies</small>
                </div>
            </div>
        </div>
    </div>
</div>