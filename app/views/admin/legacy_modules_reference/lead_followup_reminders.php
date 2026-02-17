<?php
// lead_followup_reminders.php: List leads needing follow-up and allow admin to mark as contacted/followed-up
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Mark lead as followed up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['followup']) && is_numeric($_POST['followup'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: lead_followup_reminders.php?msg=Security+validation+failed.');
        exit();
    }

    $lead_id = intval($_POST['followup']);
    $status = 'Contacted';
    $db->execute('UPDATE leads SET status = :status, updated_at = NOW() WHERE id = :id', [
        'status' => $status,
        'id' => $lead_id
    ]);
    header('Location: lead_followup_reminders.php?msg=Lead+marked+as+Contacted.');
    exit();
}

// Find leads that are new or have not been followed up (status = 'New' or 'Qualified')
$leads = $db->fetchAll("SELECT * FROM leads WHERE status IN ('New', 'Qualified') ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Lead Follow-up Reminders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        .main-content {
            margin-left: 220px;
            padding: 2rem 1rem;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Leads Needing Follow-up</h1>
        </div>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= h($_GET['msg']) ?></div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leads)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No leads need follow-up.</td>
                                </tr>
                                <?php else: foreach ($leads as $lead): ?>
                                    <tr>
                                        <td><?= h($lead['id']) ?></td>
                                        <td><?= h($lead['name']) ?></td>
                                        <td><?= h($lead['email']) ?></td>
                                        <td><?= h($lead['phone']) ?></td>
                                        <td><?= h($lead['status']) ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Mark this lead as Contacted?');">
                                                <?= getCsrfField() ?>
                                                <input type="hidden" name="followup" value="<?= h($lead['id']) ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Mark as Contacted
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>