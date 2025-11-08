<?php
session_start();
include '../config.php';
// List available virtual tours
$tours = $conn->query("SELECT t.*, p.name as property_name FROM ar_vr_tours t JOIN properties p ON t.property_id = p.id ORDER BY t.id DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Virtual Property Tours</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Virtual Property Tours</h2><div class='row'><?php while($t = $tours->fetch_assoc()): ?><div class='col-md-4'><div class='card mb-3'><div class='card-header'><?= htmlspecialchars($t['property_name']) ?></div><div class='card-body'><a href='<?= htmlspecialchars($t['asset_url']) ?>' target='_blank'>View Tour</a></div></div></div><?php endwhile; ?></div><p class='mt-3'>*Supports 360° images, 3D models, and panoramic videos. For best experience, use a VR headset or compatible browser.</p></div></body></html>
