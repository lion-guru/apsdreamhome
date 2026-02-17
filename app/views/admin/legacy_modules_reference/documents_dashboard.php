<?php
require_once __DIR__ . '/core/init.php';
require_role('Document');
require_permission('view_documents_dashboard');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }

$db = \App\Core\App::database();
$total_docs = $db->fetchOne("SELECT COUNT(*) AS c FROM documents")['c'];
$customer_docs = $db->fetchOne("SELECT COUNT(*) AS c FROM documents WHERE owner_type='customer'")['c'];
$property_docs = $db->fetchOne("SELECT COUNT(*) AS c FROM documents WHERE owner_type='property'")['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Documents Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Documents Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Documents</h4><span style="font-size:2rem;"><?= $total_docs ?></span></div></div><div class="col"><div class="card p-3"><h4>Customer Docs</h4><span style="font-size:2rem;"><?= $customer_docs ?></span></div></div><div class="col"><div class="card p-3"><h4>Property Docs</h4><span style="font-size:2rem;"><?= $property_docs ?></span></div></div></div><a href="upload_document.php" class="btn btn-info">Upload Document</a></div></body></html>
