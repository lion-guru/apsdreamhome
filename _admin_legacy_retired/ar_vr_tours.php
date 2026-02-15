<?php
session_start();
include 'config.php';
require_role('Admin');
$properties = $conn->query("SELECT * FROM properties ORDER BY id DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AR/VR Property Tours</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AR/VR Property Tours Management</h2><form method='post' enctype='multipart/form-data'><div class='mb-3'><label>Property</label><select name='property_id' class='form-control'><?php while($p = $properties->fetch_assoc()): ?><option value='<?= $p['id'] ?>'><?= htmlspecialchars($p['name']) ?></option><?php endwhile; ?></select></div><div class='mb-3'><label>Upload 360° Image/3D Model/Video</label><input type='file' name='tour_asset' class='form-control'></div><button class='btn btn-success'>Upload</button></form><p class='mt-3'>*Supports 360° images, 3D models (GLB/GLTF), and panoramic videos. Integrates with customer portal for virtual tours.</p></div></body></html>
