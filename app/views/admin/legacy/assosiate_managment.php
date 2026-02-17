<?php
require_once __DIR__ . '/core/init.php';
require_once ABSPATH . '/includes/log_admin_activity.php';

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Associates Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo h($page_title); ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main>
      <section>
          <?php
          $success_message = getSessionget_flash('success');
          $error_message = getSessionget_flash('error');
          if ($success_message): ?>
              <div class="alert alert-success"><?php echo h($success_message); ?></div>
          <?php endif; ?>
          <?php if ($error_message): ?>
              <div class="alert alert-danger"><?php echo h($error_message); ?></div>
          <?php endif; ?>

          <h2>Add New Associate</h2>
          <form action="add_associate.php" method="POST">
              <?php echo getCsrfField(); ?>
              <label for="name">Name:</label>
              <input type="text" id="name" name="name" required>

              <label for="email">Email:</label>
              <input type="email" id="email" name="email" required>

              <label for="phone">Phone:</label>
              <input type="text" id="phone" name="phone" required>

              <label for="sponser_id">Sponsor ID:</label>
              <input type="text" id="sponser_id" name="sponser_id">

              <button type="submit">Add Associate</button>
          </form>
      </section>

      <section>
          <h2>Associate List</h2>
          <table>
              <thead>
                  <tr>
                      <th>Associate ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Sponsor ID</th>
                      <th>Join Date</th>
                  </tr>
              </thead>
              <tbody>
                  <!-- PHP code to fetch and display associates from the database -->
                  <?php
                  // Using singleton database for security
                  $db = \App\Core\App::database();
                  try {
                      $result = $db->fetchAll("SELECT a.*, s.name AS sponsor_name FROM associates a LEFT JOIN associates s ON a.sponser_id = s.uid");
                      foreach ($result as $row) {
                          echo "<tr>
                                  <td>" . h($row['associate_id']) . "</td>
                                  <td>" . h($row['name']) . "</td>
                                  <td>" . h($row['email']) . "</td>
                                  <td>" . h($row['phone']) . "</td>
                                  <td>" . h($row['sponser_id']) . "</td>
                                  <td>" . h($row['join_date']) . "</td>
                                </tr>";
                      }
                  } catch (Exception $e) {
                      echo "<tr><td colspan='6'>Error retrieving associates: " . h($e->getMessage()) . "</td></tr>";
                  }
                  ?>
              </tbody>
          </table>
      </section>
  </main>
  <?php include(ABSPATH . '/includes/templates/new_footer.php'); ?>
</body>
</html>
