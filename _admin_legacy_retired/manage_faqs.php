<?php
// Admin Panel: Manage FAQs
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/config.php';
global $con;
$conn = $con;
if (!$conn) die('DB connection failed.');

// Create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS faqs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle add/edit/delete
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO faqs (question, answer, status) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $_POST['question'], $_POST['answer'], $_POST['status']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'edit') {
        $stmt = $conn->prepare("UPDATE faqs SET question=?, answer=?, status=? WHERE id=?");
        $stmt->bind_param('sssi', $_POST['question'], $_POST['answer'], $_POST['status'], $_POST['id']);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM faqs WHERE id=?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
    }
    header('Location: manage_faqs.php?updated=1');
    exit;
}
// Fetch all faqs
$res = $conn->query("SELECT * FROM faqs ORDER BY id DESC");
$faqs = [];
while ($row = $res->fetch_assoc()) $faqs[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage FAQs</title>
<style>body{font-family:sans-serif;background:#f8f9fa;}table{background:#fff;margin:2em auto;border-radius:8px;box-shadow:0 2px 8px #ccc;padding:2em;}th,td{padding:8px;}input,textarea{width:220px;}button{padding:6px 14px;}form.inline{display:inline;}</style>
</head>
<body>
<?php include __DIR__ . '/includes/admin_nav.php'; ?>
<h2 style="text-align:center">Manage FAQs</h2>
<?php if(isset($_GET['updated'])) echo '<p style="color:green;text-align:center">Updated!</p>'; ?>
<table><tr><th>Question</th><th>Answer</th><th>Status</th><th>Actions</th></tr>
<?php foreach($faqs as $f): ?>
<tr>
<form class="inline" method="post">
<input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?=$f['id']?>">
<td><input type="text" name="question" value="<?=htmlspecialchars($f['question'])?>"></td>
<td><textarea name="answer"><?=htmlspecialchars($f['answer'])?></textarea></td>
<td><select name="status"><option value="active"<?=$f['status']==='active'?' selected':''?>>Active</option><option value="inactive"<?=$f['status']==='inactive'?' selected':''?>>Inactive</option></select></td>
<td><button type="submit">Save</button></form>
<form class="inline" method="post" onsubmit="return confirm('Delete this FAQ?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$f['id']?>"><button type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<tr><form method="post"><input type="hidden" name="action" value="add">
<td><input type="text" name="question" placeholder="Question"></td>
<td><textarea name="answer" placeholder="Answer"></textarea></td>
<td><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></td>
<td><button type="submit">Add</button></td>
</form></tr>
</table>
<p style="text-align:center"><a href="dashboard.php">Dashboard</a> | <a href="manage_site_settings.php">Site Settings</a> | <a href="manage_team.php">Team</a> | <a href="manage_gallery.php">Gallery</a> | <a href="manage_testimonials.php">Testimonials</a> | <a href="manage_projects.php">Projects</a></p>
</body></html>
