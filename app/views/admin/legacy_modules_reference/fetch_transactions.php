<?php
require_once __DIR__ . '/core/init.php';

// Check if kisan_id is set
if (isset($_GET['kisan_id'])) {
    $kisan_id = intval($_GET['kisan_id']);

    // Fetch transactions
    $results = \App\Core\App::database()->fetchAll("SELECT * FROM transactions WHERE kisaan_id = ?", [$kisan_id]);

    if (!empty($results)) {
        echo "<table class='table'>";
        echo "<thead><tr><th>Transaction ID</th><th>Amount</th><th>Date</th><th>Description</th></tr></thead>";
        echo "<tbody>";
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>" . h($row['transaction_id']) . "</td>";
            echo "<td>" . h($row['amount']) . "</td>";
            echo "<td>" . h($row['date']) . "</td>";
            echo "<td>" . h($row['description']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No transactions found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
