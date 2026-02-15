<?php
session_start();
require("config.php");

// Check if plot_id is set in the POST request
if (isset($_POST['plot_id'])) {
    $plotId = intval($_POST['plot_id']); // Sanitize input

    // Prepare the SQL query to fetch plot details
    $stmt = $con->prepare("SELECT * FROM plot_master WHERE plot_id = ?");
    $stmt->bind_param("i", $plotId); // Bind the plot ID as an integer

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any results were returned
    if ($result->num_rows > 0) {
        $plotDetails = $result->fetch_assoc();
        echo json_encode(array_map('htmlspecialchars', $plotDetails)); // Return the plot details as JSON with htmlspecialchars
    } else {
        echo json_encode([]); // Return an empty array if no results found
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode([]); // Return an empty array if plot_id is not set
}

// Close the database connection
$con->close();
?>
