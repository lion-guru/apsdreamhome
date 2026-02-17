<?php
// Database connection parameters
include("config.php"); // Using your existing connection

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Function to execute SQL queries
function executeSqlQuery($con, $sql) {
    if (mysqli_query($con, $sql)) {
        return true;
    } else {
        echo "Error executing query: " . mysqli_error($con) . "<br>";
        return false;
    }
}

// Function to check if a record exists before inserting
function insertIfNotExists($con, $table, $id_field, $id_value, $insert_sql) {
    $check_sql = "SELECT COUNT(*) as count FROM $table WHERE $id_field = $id_value";
    $result = mysqli_query($con, $check_sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row['count'] > 0) {
            echo "Record with ID $id_value already exists in table $table. Skipping insertion.<br>";
            return true;
        } else {
            return executeSqlQuery($con, $insert_sql);
        }
    } else {
        echo "Error checking for existing record: " . mysqli_error($con) . "<br>";
        return false;
    }
}

// SQL statements from your dump file
$sql_statements = [
    // About table
    "CREATE TABLE IF NOT EXISTS `about` (
      `id` int(10) NOT NULL,
      `title` varchar(100) NOT NULL,
      `content` longtext NOT NULL,
      `image` varchar(300) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
];

// Insert statements that need to check for duplicates
$insert_statements = [
    // About table insert
    [
        'table' => 'about',
        'id_field' => 'id',
        'id_value' => 10,
        'sql' => "INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
        (10, 'About Us', '<div id=\"pgc-w5d0dcc3394ac1-0-0\" class=\"panel-grid-cell\">
        <div id=\"panel-w5d0dcc3394ac1-0-0-0\" class=\"so-panel widget widget_sow-editor panel-first-child panel-last-child\" data-index=\"0\">
        <div class=\"so-widget-sow-editor so-widget-sow-editor-base\">
        <div class=\"siteorigin-widget-tinymce textwidget\">
        <p class=\"text_all_p_tag_css\"> 
        <br>
        APS Dream Homes Private Limited is a prestigious real estate Company registered under the Companies Act, 2013, launched on 26 April 2022. It is a company with the scope of providing services To carry on the business of buying, selling, construction, maintenance, development, advertising, and marketing any real estate projects, lands, villas, houses, flats, apartments, bungalows, farmhouses, resorts, other properties, etc.. we strive to create an environment of trust and faith between our sales associates and customers. We strongly believe that we play a vital role in shaping the land of our great nation through following its core values of delivering quality and excellent real estate spaces ensuring customer satisfaction.
        </p>
        
        </div>
        </div>
        </div>
        </div>', 'condos-pool.png');"
    ],
    // Bookings table insert
    [
        'table' => 'bookings',
        'id_field' => 'id',
        'id_value' => 1,
        'sql' => "INSERT INTO `bookings` (`id`, `customer_name`, `property_type`, `installment_plan`) VALUES
        (1, 'gfggf', 1, '36'),
        (2, 'hffhfhv', 1, '25');"
    ]
];

// Additional CREATE TABLE statements
$additional_tables = [
    // Admin table
    "CREATE TABLE IF NOT EXISTS `admin` (
      `aid` int(10) NOT NULL,
      `auser` varchar(50) NOT NULL,
      `aemail` varchar(50) NOT NULL,
      `apass` varchar(255) NOT NULL,
      `adob` date NOT NULL,
      `aphone` varchar(15) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    // Associates table
    "CREATE TABLE IF NOT EXISTS `associates` (
      `associate_id` int(50) NOT NULL,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `phone` varchar(20) NOT NULL,
      `sponser_id` varchar(50) DEFAULT NULL,
      `join_date` timestamp NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    // Bookings table
    "CREATE TABLE IF NOT EXISTS `bookings` (
      `id` int(11) NOT NULL,
      `customer_name` varchar(255) NOT NULL,
      `property_type` int(11) NOT NULL,
      `installment_plan` varchar(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
    
    // Career applications table
    "CREATE TABLE IF NOT EXISTS `career_applications` (
      `id` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `phone` varchar(20) NOT NULL,
      `email` varchar(255) NOT NULL,
      `file_name` varchar(255) NOT NULL,
      `file_type` varchar(50) NOT NULL,
      `file_size` int(11) NOT NULL,
      `comments` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      `file_data` blob NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;"
];

// Combine all CREATE TABLE statements
$sql_statements = array_merge($sql_statements, $additional_tables);

// Execute each SQL statement
$success_count = 0;
$total_queries = count($sql_statements) + count($insert_statements);

echo "<h2>Database Import Progress</h2>";
echo "<div style='font-family: Arial, sans-serif; padding: 20px;'>";

// Execute CREATE TABLE statements
foreach ($sql_statements as $index => $sql) {
    echo "Executing query " . ($index + 1) . " of $total_queries... ";
    if (executeSqlQuery($con, $sql)) {
        echo "<span style='color: green;'>Success</span><br>";
        $success_count++;
    } else {
        echo "<span style='color: red;'>Failed</span><br>";
    }
}

// Execute INSERT statements with duplicate checking
foreach ($insert_statements as $index => $insert) {
    $query_number = count($sql_statements) + $index + 1;
    echo "Executing query " . $query_number . " of $total_queries... ";
    if (insertIfNotExists($con, $insert['table'], $insert['id_field'], $insert['id_value'], $insert['sql'])) {
        echo "<span style='color: green;'>Success</span><br>";
        $success_count++;
    } else {
        echo "<span style='color: red;'>Failed</span><br>";
    }
}

echo "<br><strong>Import Summary:</strong><br>";
echo "Total queries: $total_queries<br>";
echo "Successful queries: $success_count<br>";
echo "Failed queries: " . ($total_queries - $success_count) . "<br>";

if ($success_count == $total_queries) {
    echo "<p style='color: green; font-weight: bold;'>Database import completed successfully!</p>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>Database import completed with some errors. Please check the output above.</p>";
}

echo "</div>";

// Close connection
mysqli_close($con);
?>