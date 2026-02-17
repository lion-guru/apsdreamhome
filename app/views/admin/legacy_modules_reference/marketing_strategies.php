<?php
// Simple admin page to manage marketing strategies
require_once __DIR__ . '/core/init.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }
    
    if (isset($_POST['add'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $img = $_POST['image_url'];
        $active = isset($_POST['active']) ? 1 : 0;
        \App\Core\App::database()->execute('INSERT INTO marketing_strategies (title, description, image_url, active) VALUES (?, ?, ?, ?)', [$title, $desc, $img, $active]);
    }
    if (isset($_POST['delete'])) {
        $id = intval($_POST['delete']);
        \App\Core\App::database()->execute('DELETE FROM marketing_strategies WHERE id=?', [$id]);
    }
}
$strategies = \App\Core\App::database()->fetchAll('SELECT * FROM marketing_strategies ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Marketing Strategies</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4">Marketing Strategies</h2>
  <form method="post" class="mb-4 bg-white p-3 rounded shadow-sm">
    <?php echo getCsrfField(); ?>
    <div class="mb-2">
      <input type="text" name="title" class="form-control" placeholder="Title" required>
    </div>
    <div class="mb-2">
      <textarea name="description" class="form-control" placeholder="Description" required></textarea>
    </div>
    <div class="mb-2">
      <input type="text" name="image_url" class="form-control" placeholder="Image URL (optional)">
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="active" id="active" checked>
      <label class="form-check-label" for="active">Active</label>
    </div>
    <button type="submit" name="add" class="btn btn-primary">Add Strategy</button>
  </form>
  <table class="table table-bordered bg-white shadow-sm">
    <thead>
      <tr><th>Title</th><th>Description</th><th>Image</th><th>Active</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php foreach($strategies as $row): ?>
      <tr>
        <td><?php echo h($row['title']); ?></td>
        <td><?php echo h($row['description']); ?></td>
        <td><?php if($row['image_url']): ?><img src="<?php echo h($row['image_url']); ?>" alt="" width="60"><?php endif; ?></td>
        <td><?php echo $row['active'] ? 'Yes' : 'No'; ?></td>
        <td>
          <form method="post" style="display:inline;">
            <?php echo getCsrfField(); ?>
            <button type="submit" name="delete" value="<?php echo h($row['id']); ?>" class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
