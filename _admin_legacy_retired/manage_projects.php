<?php
// Admin Panel: Manage Projects
define('IN_ADMIN', true);
require_once __DIR__ . '/includes/config/config.php';
global $con;
$conn = $con;
if (!$conn) die('DB connection failed.');

// Handle add/edit/delete
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO projects (name, location, description, status, project_name, start_date, budget) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssd', $_POST['name'], $_POST['location'], $_POST['description'], $_POST['status'], $_POST['project_name'], $_POST['start_date'], $_POST['budget']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE projects SET name=?, location=?, description=?, status=?, project_name=?, start_date=?, budget=? WHERE id=?");
        $stmt->bind_param('ssssssdi', $_POST['name'], $_POST['location'], $_POST['description'], $_POST['status'], $_POST['project_name'], $_POST['start_date'], $_POST['budget'], $_POST['id']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM projects WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
    header('Location: manage_projects.php?updated=1');
    exit;
}
// Fetch all projects
$res = $conn->query("SELECT * FROM projects ORDER BY id DESC");
$projects = [];
while ($row = $res->fetch_assoc()) $projects[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Projects</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input,textarea{width:180px;}button{padding:6px 14px;}form.inline{display:inline;}</style>
</head>
<body>
<?php include __DIR__ . '/includes/admin_nav.php'; ?>
<h2 style="text-align:center">Manage Projects</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Updated!</p>'; ?>
<table><tr><th>Name</th><th>Location</th><th>Description</th><th>Status</th><th>Project Name</th><th>Start Date</th><th>Budget</th><th>Actions</th></tr>
<?php foreach($projects as $p): ?>
<tr>
<form class="inline" method="post">
<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$p['id']?>">
<td><input type="text" name="name" value="<?=htmlspecialchars($p['name'])?>"></td>
<td><input type="text" name="location" value="<?=htmlspecialchars($p['location'])?>"></td>
<td><textarea name="description"><?=htmlspecialchars($p['description'])?></textarea></td>
<td><input type="text" name="status" value="<?=htmlspecialchars($p['status'])?>"></td>
<td><input type="text" name="project_name" value="<?=htmlspecialchars($p['project_name'])?>"></td>
<td><input type="date" name="start_date" value="<?=htmlspecialchars($p['start_date'])?>"></td>
<td><input type="number" step="0.01" name="budget" value="<?=htmlspecialchars($p['budget'])?>"></td>
<td><button type="submit">Save</button></form>
<form class="inline" method="post" onsubmit="return confirm('Delete this project?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<tr><form method="post"><input type="hidden" name="action" value="add">
<td><input type="text" name="name" placeholder="Name"></td>
<td><input type="text" name="location" placeholder="Location"></td>
<td><textarea name="description" placeholder="Description"></textarea></td>
<td><input type="text" name="status" placeholder="Status"></td>
<td><input type="text" name="project_name" placeholder="Project Name"></td>
<td><input type="date" name="start_date"></td>
<td><input type="number" step="0.01" name="budget" placeholder="Budget"></td>
<td><button type="submit">Add</button></td>
</form></tr>
</table>
<p style="text-align:center"><a href="dashboard.php">Dashboard</a> | <a href="manage_site_settings.php">Site Settings</a> | <a href="manage_team.php">Team</a> | <a href="manage_gallery.php">Gallery</a> | <a href="manage_testimonials.php">Testimonials</a></p>
</body></html>
