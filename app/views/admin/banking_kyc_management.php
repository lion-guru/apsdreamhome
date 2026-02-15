<?php
/**
 * Banking & KYC Management
 * Allows admins to review and verify banking and KYC details.
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../app/Helpers/BankingSecurity.php';

use App\Helpers\BankingSecurity;

if (!isAuthenticated() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Verification
if (isset($_POST['verify_action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $type = $_POST['type']; // 'banking' or 'kyc' or 'document'
        $id = intval($_POST['id']);
        $status = $_POST['status']; // 'verified' or 'rejected'
        $reason = $_POST['reason'] ?? '';

        if ($type === 'banking') {
            if ($db->execute("UPDATE banking_details SET verification_status = ?, verified_at = NOW() WHERE id = ?", [$status, $id])) {
                $user_id = $db->fetch("SELECT user_id FROM banking_details WHERE id = ?", [$id])['user_id'];
                BankingSecurity::logAction($user_id, 'VERIFY_BANK_DETAILS', null, ['status' => $status], getAuthUserId(), 'admin');
                $msg = "Banking verification updated.";
            } else {
                $error = "Update failed.";
            }
        } elseif ($type === 'kyc') {
            if ($db->execute("UPDATE kyc_details SET overall_status = ?, rejection_reason = ?, verified_at = NOW() WHERE id = ?", [$status, $reason, $id])) {
                $user_id = $db->fetch("SELECT user_id FROM kyc_details WHERE id = ?", [$id])['user_id'];
                BankingSecurity::logAction($user_id, 'VERIFY_KYC_DETAILS', null, ['status' => $status], getAuthUserId(), 'admin');
                $msg = "KYC verification updated.";
            } else {
                $error = "Update failed.";
            }
        } elseif ($type === 'document') {
            if ($db->execute("UPDATE kyc_documents SET verification_status = ?, rejection_reason = ?, verified_at = NOW() WHERE id = ?", [$status, $reason, $id])) {
                $row = $db->fetch("SELECT user_id, doc_type FROM kyc_documents WHERE id = ?", [$id]);
                BankingSecurity::logAction($row['user_id'], 'VERIFY_KYC_DOCUMENT', null, ['status' => $status, 'doc_type' => $row['doc_type']], getAuthUserId(), 'admin');
                $msg = "Document verification updated.";
            } else {
                $error = "Update failed.";
            }
        }
    }
}

// Fetch data
$pending_banking = $db->fetchAll("SELECT bd.*, u.uname FROM banking_details bd JOIN user u ON bd.user_id = u.uid WHERE bd.verification_status = 'pending' ORDER BY bd.created_at DESC");
$pending_kyc = $db->fetchAll("SELECT kd.*, u.uname FROM kyc_details kd JOIN user u ON kd.user_id = u.uid WHERE kd.overall_status = 'pending' ORDER BY kd.created_at DESC");
$pending_docs = $db->fetchAll("SELECT doc.*, u.uname FROM kyc_documents doc JOIN user u ON doc.user_id = u.uid WHERE doc.verification_status = 'pending' ORDER BY doc.uploaded_at DESC");

$page_title = "Banking & KYC Management";
include('admin_header.php');
include('admin_sidebar.php');
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Banking & KYC</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo h($msg); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo h($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Pending Banking Details -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Pending Banking Verifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Bank Name</th>
                                        <th>Account Holder</th>
                                        <th>IFSC</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pending_banking)): ?>
                                        <?php foreach($pending_banking as $row): ?>
                                            <tr>
                                                <td><?php echo h($row['uname']); ?></td>
                                                <td><?php echo h($row['bank_name']); ?></td>
                                                <td><?php echo h($row['account_holder_name']); ?></td>
                                                <td><?php echo h($row['ifsc_code']); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCSRFToken()); ?>">
                                                        <input type="hidden" name="type" value="banking">
                                                        <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">
                                                        <button type="submit" name="verify_action" value="1" class="btn btn-sm btn-success">
                                                            <input type="hidden" name="status" value="verified"> Verify
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal" data-type="banking" data-id="<?php echo h($row['id']); ?>">Reject</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">No pending banking verifications</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending KYC Details -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Pending KYC Verifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>PAN Status</th>
                                        <th>Aadhaar Status</th>
                                        <th>Submitted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pending_kyc)): ?>
                                        <?php foreach($pending_kyc as $row): ?>
                                            <tr>
                                                <td><?php echo h($row['uname']); ?></td>
                                                <td><span class="badge badge-warning"><?php echo h($row['pan_status']); ?></span></td>
                                                <td><span class="badge badge-warning"><?php echo h($row['aadhaar_status']); ?></span></td>
                                                <td><?php echo h($row['created_at']); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCSRFToken()); ?>">
                                                        <input type="hidden" name="type" value="kyc">
                                                        <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">
                                                        <button type="submit" name="verify_action" value="1" class="btn btn-sm btn-success">
                                                            <input type="hidden" name="status" value="verified"> Verify
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal" data-type="kyc" data-id="<?php echo h($row['id']); ?>">Reject</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">No pending KYC verifications</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Documents -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">Pending Document Verifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Doc Type</th>
                                        <th>View</th>
                                        <th>Uploaded At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($pending_docs)): ?>
                                        <?php foreach($pending_docs as $row): ?>
                                            <tr>
                                                <td><?php echo h($row['uname']); ?></td>
                                                <td><?php echo h($row['doc_type']); ?></td>
                                                <td><a href="../<?php echo h($row['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Doc</a></td>
                                                <td><?php echo h($row['uploaded_at']); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo h(generateCSRFToken()); ?>">
                                                        <input type="hidden" name="type" value="document">
                                                        <input type="hidden" name="id" value="<?php echo h($row['id']); ?>">
                                                        <button type="submit" name="verify_action" value="1" class="btn btn-sm btn-success">
                                                            <input type="hidden" name="status" value="verified"> Verify
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal" data-type="document" data-id="<?php echo h($row['id']); ?>">Reject</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">No pending document verifications</td></tr>
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Verification</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="type" id="modal_type">
                    <input type="hidden" name="id" id="modal_id">
                    <input type="hidden" name="status" value="rejected">
                    <div class="form-group">
                        <label>Reason for Rejection</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="verify_action" class="btn btn-danger">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#rejectModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var type = button.data('type');
        var id = button.data('id');
        var modal = $(this);
        modal.find('#modal_type').val(type);
        modal.find('#modal_id').val(id);
    });
});
</script>

<?php include('admin_footer.php'); ?>

