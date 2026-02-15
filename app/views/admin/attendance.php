<?php

/**
 * Attendance Records - Standardized Version
 */

require_once __DIR__ . '/core/init.php';

// Check permissions
if (function_exists('require_permission')) {
    require_permission('manage_attendance');
}

$db = \App\Core\App::database();

// Handle actions
$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken()) {
        $message = $mlSupport->translate("Security validation failed. Please try again.");
        $message_type = "danger";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        if ($db->execute("DELETE FROM attendance WHERE id = ?", [$id])) {
            $message = $mlSupport->translate("Attendance record deleted successfully.");
            $message_type = "success";
            logAdminActivity("Attendance Deleted", "Deleted attendance record ID: $id");
        } else {
            $message = $mlSupport->translate("Error deleting record.");
            $message_type = "danger";
        }
    }
}

// Fetch attendance records with employee names
$sql = "SELECT a.*, e.name as employee_name
        FROM attendance a
        LEFT JOIN employees e ON a.employee_id = e.id
        ORDER BY a.date DESC, e.name ASC";
$attendance = $db->fetchAll($sql);

$page_title = $mlSupport->translate("Attendance Management");
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate("Attendance Management")); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo h($mlSupport->translate("Dashboard")); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate("Attendance")); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <a href="mark_attendance.php" class="btn btn-primary rounded-pill px-4">
                        <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate("Mark Attendance")); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= h($message_type) ?> alert-dismissible fade show">
                <?= h($message) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= h($mlSupport->translate($_GET['msg'])) ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-nowrap custom-table mb-0 datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate("Employee")); ?></th>
                                        <th><?php echo h($mlSupport->translate("Date")); ?></th>
                                        <th><?php echo h($mlSupport->translate("Status")); ?></th>
                                        <th><?php echo h($mlSupport->translate("Remarks")); ?></th>
                                        <th class="text-right"><?php echo h($mlSupport->translate("Actions")); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($attendance)): ?>
                                        <?php foreach ($attendance as $record): ?>
                                            <tr>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="employee_profile.php?id=<?= h($record['employee_id']) ?>">
                                                            <?= h($mlSupport->translate($record['employee_name'] ?? 'Unknown Employee')) ?>
                                                        </a>
                                                    </h2>
                                                </td>
                                                <td><?= h(date('d M Y', strtotime($record['date']))) ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'bg-inverse-warning';
                                                    if ($record['status'] === 'present') $status_class = 'bg-inverse-success';
                                                    elseif ($record['status'] === 'absent') $status_class = 'bg-inverse-danger';
                                                    ?>
                                                    <span class="badge <?= h($status_class) ?>">
                                                        <?= h($mlSupport->translate(ucfirst($record['status']))) ?>
                                                    </span>
                                                </td>
                                                <td><?= h($record['remarks'] ?? '-') ?></td>
                                                <td class="text-end">
                                                    <div class="dropdown dropdown-action">
                                                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <a class="dropdown-item" href="edit_attendance.php?id=<?= h($record['id']) ?>"><i class="fas fa-pencil-alt m-r-5"></i> <?php echo h($mlSupport->translate("Edit")); ?></a>
                                                            <a class="dropdown-item delete-btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" data-id="<?= h($record['id']) ?>" data-info="<?= h($record['employee_name']) ?> - <?= h(date('d M Y', strtotime($record['date']))) ?>"><i class="fas fa-trash-alt m-r-5"></i> <?php echo h($mlSupport->translate("Delete")); ?></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center"><?php echo h($mlSupport->translate("No attendance records found.")); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="form-header">
                    <h3><?php echo h($mlSupport->translate("Delete Attendance Record")); ?></h3>
                    <p><?php echo h($mlSupport->translate("Are you sure you want to delete attendance for")); ?> <strong id="delete_info"></strong>?</p>
                </div>
                <div class="modal-btn delete-action">
                    <form method="POST">
                        <?= getCsrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <div class="row">
                            <div class="col-6">
                                <button type="submit" class="btn btn-primary continue-btn w-100"><?php echo h($mlSupport->translate("Delete")); ?></button>
                            </div>
                            <div class="col-6">
                                <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-primary cancel-btn w-100"><?php echo h($mlSupport->translate("Cancel")); ?></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtns = document.querySelectorAll('.delete-btn');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('delete_id').value = this.dataset.id;
                document.getElementById('delete_info').textContent = this.dataset.info;
            });
        });
    });
</script>

<?php include 'admin_footer.php'; ?>
