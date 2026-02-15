<?php
// plots-admin.php
// Admin backend for managing plot statuses (sold/booked/available) for each colony
require_once __DIR__ . '/../includes/templates/dynamic_header.php';
// For demo, use session to store data. Replace with DB in production.
session_start();
if (!isset($_SESSION['plots'])) {
    $_SESSION['plots'] = [
        'Suryoday Colony' => [
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
        ],
        // Add more colonies as needed
    ];
}
// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['colony'], $_POST['plot'], $_POST['status'])) {
    $colony = $_POST['colony'];
    $plotIdx = intval($_POST['plot']);
    $status = $_POST['status'];
    if (isset($_SESSION['plots'][$colony][$plotIdx])) {
        $_SESSION['plots'][$colony][$plotIdx]['status'] = $status;
        $msg = "Plot status updated!";
    }
}
$plotsData = $_SESSION['plots'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Manage Plots | APS Dream Homes</title>
    <link rel="stylesheet" href="../assets/css/home.css">
    <style>
        .plot-admin-table th, .plot-admin-table td { text-align: center; }
        .plot-status-available { background: #e8f5e9; color: #388e3c; font-weight: bold; }
        .plot-status-booked { background: #fffde7; color: #fbc02d; font-weight: bold; }
        .plot-status-sold { background: #ffebee; color: #c62828; font-weight: bold; }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4 text-primary fw-bold">Admin: Manage Plots Status</h1>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"> <?=htmlspecialchars($msg)?> </div>
    <?php endif; ?>
    <?php foreach ($plotsData as $colony => $plots): ?>
    <div class="card shadow mb-5">
        <div class="card-header bg-primary text-white fw-bold fs-5"> <?=$colony?> </div>
        <div class="card-body">
            <table class="table table-bordered plot-admin-table">
                <thead class="table-light">
                <tr><th>Plot No.</th><th>Status</th><th>Change Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($plots as $idx=>$p): ?>
                    <tr>
                        <td><?=$p['number']?></td>
                        <td class="plot-status-<?=$p['status']?> text-capitalize"><?=ucfirst($p['status'])?></td>
                        <td>
                            <form method="post" class="d-inline-flex align-items-center gap-1">
                                <input type="hidden" name="colony" value="<?=htmlspecialchars($colony)?>">
                                <input type="hidden" name="plot" value="<?=$idx?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="available" <?=$p['status']==='available'?'selected':''?>>Available</option>
                                    <option value="booked" <?=$p['status']==='booked'?'selected':''?>>Booked</option>
                                    <option value="sold" <?=$p['status']==='sold'?'selected':''?>>Sold</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
