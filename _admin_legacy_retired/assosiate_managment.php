<?php include(__DIR__ . '/../includes/templates/dynamic_header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Associates Page</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main>
      <section>
          <h2>Add New Associate</h2>
          <form action="add_associate.php" method="POST">
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
                  include(__DIR__.'/config.php');
                  // Using prepared statement for security
$stmt = $con->prepare("SELECT a.*, s.name AS sponsor_name FROM associates a LEFT JOIN associates s ON a.sponser_id = s.uid");
$stmt->execute();
$result = $stmt->get_result();

if(!$result) {
    die("Error retrieving associates: " . $con->error);
}
                  while ($row = mysqli_fetch_assoc($result)) {
                      echo "<tr>
                              <td>{$row['associate_id']}</td>
                              <td>{$row['name']}</td>
                              <td>{$row['email']}</td>
                              <td>{$row['phone']}</td>
                              <td>{$row['sponser_id']}</td>
                              <td>{$row['join_date']}</td>
                            </tr>";
                  }
                  ?>
              </tbody>
          </table>
      </section>
  </main>
  <?php include(__DIR__ . '/../includes/templates/new_footer.php'); ?>
</body>
</html>