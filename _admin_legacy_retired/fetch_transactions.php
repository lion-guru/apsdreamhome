<?php
// Include database connection
include("config.php");
session_start();

// Check if kisan_id is set
if (isset($_GET['kisan_id'])) {
    $kisan_id = intval($_GET['kisan_id']);

    // Prepare SQL statement to fetch transactions
    $stmt = $con->prepare("SELECT * FROM transactions WHERE kisaan_id = ?");
    $stmt->bind_param("i", $kisan_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='table'>";
        echo "<thead><tr><th>Transaction ID</th><th>Amount</th><th>Date</th><th>Description</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['transaction_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No transactions found.</p>";
    }
    $stmt->close();
} else {
    echo "<p>Invalid request.</p>";
}
?>
