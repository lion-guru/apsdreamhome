<?php
require_once(__DIR__ . '/../app/bootstrap.php');
$db = \App\Core\App::database();

// Fetch all admins
date_default_timezone_set('Asia/Kolkata');
try {
    $sql = "SELECT aid, auser, aemail, apass, role, status, adob, aphone FROM admin ORDER BY aid DESC";
    $admins = $db->fetch($sql);
} catch (Exception $e) {
    error_log("Error fetching admin list: " . $e->getMessage());
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin List</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .modern-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(41, 98, 255, 0.08), 0 1.5px 8px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s cubic-bezier(.4, 2, .6, 1), transform 0.2s;
            background: linear-gradient(135deg, #f8fafc 60%, #e3f0ff 100%);
        }

        .modern-card:hover {
            box-shadow: 0 8px 32px rgba(41, 98, 255, 0.18), 0 3px 16px rgba(0, 0, 0, 0.08);
            transform: translateY(-3px) scale(1.015);
        }

        .modern-table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(41, 98, 255, 0.07);
            overflow: hidden;
        }

        .modern-table thead th {
            background: linear-gradient(90deg, #2962ff 0%, #00bcd4 100%);
            color: #fff;
            font-weight: 600;
            border: none;
        }

        .modern-table tbody tr {
            transition: background 0.2s;
        }

        .modern-table tbody tr:hover {
            background: #e3f0ff;
        }

        .modern-table td,
        .modern-table th {
            vertical-align: middle;
            padding: 12px 16px;
        }

        .btn {
            border-radius: 8px !important;
            font-weight: 600;
        }
    </style>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include("header.php"); ?>
    <div class="container-fluid px-3 py-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0" style="font-weight:600;">Admin List</h5>
            </div>
            <div class="card-body p-3">
                <table id="adminTable" class="modern-table table table-striped">
                    <thead>
                        <tr style="background:linear-gradient(90deg,#4e73df 0%,#1cc88a 100%);color:#fff;">
                            <th>aid</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Password Hash</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date of Birth</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['aid']) ?></td>
                                    <td><?= htmlspecialchars($row['auser']) ?></td>
                                    <td><?= htmlspecialchars($row['aemail']) ?></td>
                                    <td style="font-size:12px;word-break:break-all;max-width:180px;">
                                        <?= htmlspecialchars($row['apass']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['adob']) ?></td>
                                    <td><?= htmlspecialchars($row['aphone']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS (fixes DataTable error) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable) {
                $('#adminTable').DataTable();
            }
        });
    </script>
</body>

</html>