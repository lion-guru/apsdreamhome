<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $basePath);
}
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
$d = $state['form_data'] ?? [];
$selectedAmenities = $d['amenities'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(__current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 5: Amenities | List Property | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; min-height: 100vh; font-family: 'Inter', sans-serif; padding: 20px 0; }
        .wizard-card { border: none; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); }
        .amenity-chip { cursor: pointer; user-select: none; }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
<div class="container">
    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>List Your Property</h2>
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/progress.php'; ?></div>
        <div class="col-lg-9">
            <div class="wizard-card card">
                <div class="card-body p-4">
                    <h4 class="mb-1">Step 5: Amenities</h4>
                    <p class="text-muted">Select what's available with your property.</p>

                    <form method="POST" action="<?= BASE_URL ?>/list-property/step5">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $amenitiesList = [
                                'parking' => 'fa-car', 'lift' => 'fa-arrow-up', 'power_backup' => 'fa-bolt',
                                'water_supply' => 'fa-tint', 'security' => 'fa-shield-alt', 'gym' => 'fa-dumbbell',
                                'swimming_pool' => 'fa-swimming-pool', 'garden' => 'fa-leaf', 'club_house' => 'fa-home',
                                'kids_play_area' => 'fa-child', 'cctv' => 'fa-video', 'fire_safety' => 'fa-fire-extinguisher',
                                'rainwater_harvesting' => 'fa-cloud-rain', 'waste_management' => 'fa-recycle',
                                'internet' => 'fa-wifi', 'air_conditioning' => 'fa-snowflake',
                            ];
                            foreach ($amenitiesList as $key => $icon):
                                $isChecked = in_array($key, $selectedAmenities, true);
                            ?>
                                <input type="checkbox" class="btn-check" name="amenities[]" id="am-<?= $key ?>" value="<?= $key ?>" <?= $isChecked ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary amenity-chip" for="am-<?= $key ?>">
                                    <i class="fas <?= $icon ?> me-1"></i><?= ucwords(str_replace('_', ' ', $key)) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/list-property/step4" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                            <button type="submit" class="btn btn-primary px-4">Next <i class="fas fa-arrow-right ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
