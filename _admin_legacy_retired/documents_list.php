<?php
session_start();
include 'config.php';
require_permission('view_documents_dashboard');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }

$docs = $conn->query("SELECT * FROM documents ORDER BY id DESC");
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
        <?php while($doc = $docs->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($doc['id']) ?></td>
                <td><?= htmlspecialchars($doc['owner_type']) ?></td>
                <td><?= htmlspecialchars($doc['owner_id']) ?></td>
                <td><?= htmlspecialchars($doc['doc_type']) ?></td>
                <td><a href="../<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">View</a></td>
                <td>
                    <?php if (!empty($doc['drive_file_id'])): ?>
                        <a href="https://drive.google.com/file/d/<?= htmlspecialchars($doc['drive_file_id']) ?>/view" target="_blank" title="View on Google Drive">
                            <i class="fab fa-google-drive text-success"></i>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($doc['uploaded_by']) ?></td>
                <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
