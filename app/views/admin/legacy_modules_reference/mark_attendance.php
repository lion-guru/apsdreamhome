<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

$employees = $db->fetchAll("SELECT id, name FROM employees WHERE status='active' ORDER BY name");

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = $mlSupport->translate("Invalid CSRF token. Please try again.");
    } else {
        $employee_id = intval($_POST['employee_id']);
        $date = $_POST['date'];
        $status = $_POST['status'];
        $remarks = $_POST['remarks'];
        
        $sql = "INSERT INTO attendance (employee_id, date, status, remarks) VALUES (:employee_id, :date, :status, :remarks)";
        
        if ($db->execute($sql, [
            'employee_id' => $employee_id,
            'date' => $date,
            'status' => $status,
            'remarks' => $remarks
        ])) {
            // Fetch employee name for notification
            $e_data = $db->fetchOne("SELECT name FROM employees WHERE id = :id", ['id' => $employee_id]);
            $e_name = $e_data['name'] ?? "Employee #$employee_id";

            require_once __DIR__ . '/../includes/notification_manager.php';
            require_once __DIR__ . '/../includes/email_service.php';
            $nm = new NotificationManager(null, new EmailService());
            $nm->send([
                'user_id' => 1,
                'template' => 'ATTENDANCE_MARKED',
                'data' => [
                    'employee_name' => $e_name,
                    'status' => ucfirst($status),
                    'date' => $date,
                    'admin_name' => $_SESSION['auser'] ?? 'Admin'
                ],
                'channels' => ['db']
            ]);
            
            header('Location: attendance.php?msg=' . urlencode($mlSupport->translate('Attendance marked successfully.')));
            exit();
        } else {
            $error = $mlSupport->translate("Error saving attendance.");
        }
    }
}

$page_title = $mlSupport->translate("Mark Attendance");
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo h($mlSupport->translate("Mark Attendance")); ?></h6>
                    <a href="attendance.php" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo h($mlSupport->translate("Back to Attendance")); ?>
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= h($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php echo getCsrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate("Employee")); ?></label>
                            <select name="employee_id" class="form-select" required>
                                <option value=""><?php echo h($mlSupport->translate("Select Employee")); ?></option>
                                <?php foreach($employees as $e): ?>
                                    <option value="<?= $e['id'] ?>"><?= h($e['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate("Date")); ?></label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate("Status")); ?></label>
                            <select name="status" class="form-select">
                                <option value="present"><?php echo h($mlSupport->translate("Present")); ?></option>
                                <option value="absent"><?php echo h($mlSupport->translate("Absent")); ?></option>
                                <option value="leave"><?php echo h($mlSupport->translate("Leave")); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><?php echo h($mlSupport->translate("Remarks")); ?></label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="<?php echo h($mlSupport->translate("Optional remarks")); ?>"></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo h($mlSupport->translate("Mark Attendance")); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>


