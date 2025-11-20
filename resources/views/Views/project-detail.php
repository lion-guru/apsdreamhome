<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions/common-functions.php';
$page_title = "Project Details - APS Dream Homes";
$additional_css = '<link rel="stylesheet" href="' . get_asset_url('css/home.css') . '">';
$additional_js = '';
require_once __DIR__ . '/includes/templates/dynamic_header.php';

// Fetch project details from DB
$project = null;
if (isset($_GET['city']) && isset($_GET['project'])) {
    $city = mysqli_real_escape_string($conn, ucwords(strtolower($_GET['city'])));
    $project_url = str_replace('-', ' ', strtolower($_GET['project']));
    $sql = "SELECT * FROM project_master WHERE city = '$city' AND LOWER(REPLACE(name, ' ', '-')) = '" . mysqli_real_escape_string($conn, strtolower($_GET['project'])) . "' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $project = mysqli_fetch_assoc($result);
    }
}

if (!$project) {
    echo '<div class="container py-5"><div class="alert alert-danger">Project not found.</div></div>';
    require_once __DIR__ . '/includes/templates/footer.php';
    exit;
}

// Prepare amenities and images
$amenities = array_map('trim', explode(',', $project['amenities']));
$images = array_map('trim', explode(',', $project['images']));
?>

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 mb-2"><?php echo htmlspecialchars($project['name']); ?> <span class="badge bg-primary fs-6 ms-2"><?php echo htmlspecialchars($project['city']); ?></span></h1>
            <p class="text-muted mb-1"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($project['location']); ?></p>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <!-- Image Gallery -->
            <div class="row g-2 mb-3">
                <?php foreach ($images as $img): ?>
                    <div class="col-4">
                        <img src="<?php echo htmlspecialchars($img); ?>" class="img-fluid rounded shadow-sm" alt="Project Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Video -->
            <?php if (!empty($project['video'])): ?>
            <div class="ratio ratio-16x9 mb-3">
                <iframe src="<?php echo htmlspecialchars($project['video']); ?>" title="Project Video" allowfullscreen></iframe>
            </div>
            <?php endif; ?>
            <!-- Layout Map -->
            <?php if (!empty($project['layout_map'])): ?>
            <div class="mb-3">
                <h5>Layout Map</h5>
                <img src="<?php echo htmlspecialchars($project['layout_map']); ?>" class="img-fluid rounded shadow-sm" alt="Layout Map">
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">About Project</h5>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                    <h6 class="mt-4">Amenities</h6>
                    <ul class="list-unstyled ms-2">
                        <?php foreach ($amenities as $a): ?>
                            <li><i class="fas fa-check-circle text-success me-2"></i> <?php echo htmlspecialchars($a); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <!-- Optional: Google Map Embed -->
            <div class="card">
                <div class="card-body p-2">
                    <iframe src="https://www.google.com/maps?q=<?php echo urlencode($project['location']); ?>&output=embed" width="100%" height="180" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/templates/footer.php'; ?>
