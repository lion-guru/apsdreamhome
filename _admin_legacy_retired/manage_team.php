<?php
// Admin Panel: Manage Team Members
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/config.php';
global $con;
$conn = $con;
if (!$conn) die('DB connection failed.');
?>

<?php include __DIR__ . '/includes/admin_nav.php'; ?>

// Handle add/edit/delete
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO team (name, designation, bio, photo, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $_POST['name'], $_POST['designation'], $_POST['bio'], $_POST['photo'], $_POST['status']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE team SET name=?, designation=?, bio=?, photo=?, status=? WHERE id=?");
        $stmt->bind_param('sssssi', $_POST['name'], $_POST['designation'], $_POST['bio'], $_POST['photo'], $_POST['status'], $_POST['id']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM team WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
    header('Location: manage_team.php?updated=1');
    exit;
}
// Fetch all team members
$res = $conn->query("SELECT * FROM team ORDER BY id DESC");
$team = [];
while ($row = $res->fetch_assoc()) $team[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Team</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input,textarea{width:220px;}button{padding:6px 14px;}form.inline{display:inline;}</style>
</head>
<body>
<h2 style="text-align:center">Manage Team Members</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Updated!</p>'; ?>
<table><tr><th>Name</th><th>Designation</th><th>Bio</th><th>Photo</th><th>Status</th><th>Actions</th></tr>
<?php foreach($team as $m): ?>
<tr>
<form class="inline" method="post">
<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$m['id']?>">
<td><input type="text" name="name" value="<?=htmlspecialchars($m['name'])?>"></td>
<td><input type="text" name="designation" value="<?=htmlspecialchars($m['designation'])?>"></td>
<td><textarea name="bio"><?=htmlspecialchars($m['bio'])?></textarea></td>
<td><input type="text" name="photo" value="<?=htmlspecialchars($m['photo'])?>"></td>
<td><select name="status"><option value="active"<?=$m['status']==='active'?' selected':''?>>Active</option><option value="inactive"<?=$m['status']==='inactive'?' selected':''?>>Inactive</option></select></td>
<td><button type="submit">Save</button></form>
<form class="inline" method="post" onsubmit="return confirm('Delete this member?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$m['id']?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<tr><form method="post"><input type="hidden" name="action" value="add">
<td><input type="text" name="name" placeholder="Name"></td>
<td><input type="text" name="designation" placeholder="Designation"></td>
<td><textarea name="bio" placeholder="Bio"></textarea></td>
<td><input type="text" name="photo" placeholder="/assets/images/team/xyz.jpg"></td>
<td><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></td>
<td><button type="submit">Add</button></td>
</form></tr>
</table>
<p style="text-align:center"><a href="manage_site_settings.php">Manage Site Settings</a> | <a href="manage_gallery.php">Manage Gallery</a> | <a href="manage_testimonials.php">Manage Testimonials</a></p>
</body></html>
