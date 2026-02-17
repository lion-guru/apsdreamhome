<?php
require_once __DIR__ . '/core/init.php';
require_permission('view_documents_dashboard');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }

$db = \App\Core\App::database();
$docs = $db->fetchAll("SELECT * FROM documents ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Documents</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="container py-4">
    <h2>All Documents</h2>
    <a href="upload_document.php" class="btn btn-info mb-3">Upload Document</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Owner Type</th>
                <th>Owner ID</th>
                <th>Type</th>
                <th>File</th>
                <th>Drive</th>
                <th>Uploaded By</th>
                <th>Uploaded At</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($docs as $doc): ?>
            <tr>
                <td><?= h($doc['id']) ?></td>
                <td><?= h($doc['owner_type']) ?></td>
                <td><?= h($doc['owner_id']) ?></td>
                <td><?= h($doc['doc_type']) ?></td>
                <td><a href="../<?= h($doc['file_path']) ?>" target="_blank">View</a></td>
                <td>
                    <?php if ($doc['drive_file_id']): ?>
                        <a href="https://drive.google.com/file/d/<?= h($doc['drive_file_id']) ?>/view" target="_blank" title="View on Google Drive">
                            <i class="fab fa-google-drive"></i>
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= h($doc['uploaded_by']) ?></td>
                <td><?= h($doc['uploaded_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
