<?php
// Admin Panel: Manage Testimonials
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/db_config.php';
$conn = getDbConnection();
if (!$conn) die('DB connection failed.');
?>

<?php include __DIR__ . '/includes/admin_nav.php'; ?>

// Handle add/edit/delete
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO testimonials (client_name, testimonial, client_photo, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $_POST['client_name'], $_POST['testimonial'], $_POST['client_photo'], $_POST['status']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE testimonials SET client_name=?, testimonial=?, client_photo=?, status=? WHERE id=?");
        $stmt->bind_param('ssssi', $_POST['client_name'], $_POST['testimonial'], $_POST['client_photo'], $_POST['status'], $_POST['id']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
    header('Location: manage_testimonials.php?updated=1');
    exit;
}
// Fetch all testimonials
$res = $conn->query("SELECT * FROM testimonials ORDER BY id DESC");
$testimonials = [];
while ($row = $res->fetch_assoc()) $testimonials[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Testimonials</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input,textarea{width:220px;}button{padding:6px 14px;}form.inline{display:inline;}</style>
</head>
<body>
<h2 style="text-align:center">Manage Testimonials</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Updated!</p>'; ?>
<table><tr><th>Client Name</th><th>Testimonial</th><th>Photo</th><th>Status</th><th>Actions</th></tr>
<?php foreach($testimonials as $t): ?>
<tr>
<form class="inline" method="post">
<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$t['id']?>">
<td><input type="text" name="client_name" value="<?=htmlspecialchars($t['client_name'])?>"></td>
<td><textarea name="testimonial"><?=htmlspecialchars($t['testimonial'])?></textarea></td>
<td><input type="text" name="client_photo" value="<?=htmlspecialchars($t['client_photo'])?>"></td>
<td><select name="status"><option value="active"<?=$t['status']==='active'?' selected':''?>>Active</option><option value="inactive"<?=$t['status']==='inactive'?' selected':''?>>Inactive</option></select></td>
<td><button type="submit">Save</button></form>
<form class="inline" method="post" onsubmit="return confirm('Delete this testimonial?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$t['id']?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<tr><form method="post"><input type="hidden" name="action" value="add">
<td><input type="text" name="client_name" placeholder="Client Name"></td>
<td><textarea name="testimonial" placeholder="Testimonial"></textarea></td>
<td><input type="text" name="client_photo" placeholder="/assets/images/testimonials/xyz.jpg"></td>
<td><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></td>
<td><button type="submit">Add</button></td>
</form></tr>
</table>
<p style="text-align:center"><a href="manage_site_settings.php">Manage Site Settings</a> | <a href="manage_team.php">Manage Team</a> | <a href="manage_gallery.php">Manage Gallery</a></p>
</body></html>
