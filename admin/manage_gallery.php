<?php
// Admin Panel: Manage Gallery
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();
if (!$conn) die('DB connection failed.');
?>

<?php include __DIR__ . '/includes/admin_nav.php'; ?>

// Handle add/edit/delete
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO gallery (image_path, caption, status) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $_POST['image_path'], $_POST['caption'], $_POST['status']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE gallery SET image_path=?, caption=?, status=? WHERE id=?");
        $stmt->bind_param('sssi', $_POST['image_path'], $_POST['caption'], $_POST['status'], $_POST['id']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
    header('Location: manage_gallery.php?updated=1');
    exit;
}
// Fetch all gallery images
$res = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
$gallery = [];
while ($row = $res->fetch_assoc()) $gallery[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Gallery</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input{width:220px;}button{padding:6px 14px;}form.inline{display:inline;}</style>
</head>
<body>
<h2 style="text-align:center">Manage Gallery</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Updated!</p>'; ?>
<table><tr><th>Image Path</th><th>Caption</th><th>Status</th><th>Actions</th></tr>
<?php foreach($gallery as $img): ?>
<tr>
<form class="inline" method="post">
<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$img['id']?>">
<td><input type="text" name="image_path" value="<?=htmlspecialchars($img['image_path'])?>"></td>
<td><input type="text" name="caption" value="<?=htmlspecialchars($img['caption'])?>"></td>
<td><select name="status"><option value="active"<?=$img['status']==='active'?' selected':''?>>Active</option><option value="inactive"<?=$img['status']==='inactive'?' selected':''?>>Inactive</option></select></td>
<td><button type="submit">Save</button></form>
<form class="inline" method="post" onsubmit="return confirm('Delete this image?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$img['id']?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<tr><form method="post"><input type="hidden" name="action" value="add">
<td><input type="text" name="image_path" placeholder="/assets/images/gallery/xyz.jpg"></td>
<td><input type="text" name="caption" placeholder="Caption"></td>
<td><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></td>
<td><button type="submit">Add</button></td>
</form></tr>
</table>
<p style="text-align:center"><a href="manage_site_settings.php">Manage Site Settings</a> | <a href="manage_team.php">Manage Team</a> | <a href="manage_testimonials.php">Manage Testimonials</a></p>
</body></html>
