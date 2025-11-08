<?php
include("config.php");
if (!empty($_POST["id"])) {
	 
	
    $id = $_POST['id'];
    $query = "select * from gata_master where site_id=$id";
	
    $result = mysqli_query($con, $query);
    if ($result->num_rows > 0) {
        echo '<option value="">Select Gata</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . htmlspecialchars($row['gata_id']) . '">' . htmlspecialchars($row['gata_no']) . '</option>';
        }
    }

	
} 
if (!empty($_POST['sid'])) {
    $gata_id = $_POST['sid'];

    $query1 = "select * from plot_master where gata_a=$gata_id OR gata_b=$gata_id OR gata_c=$gata_id OR gata_d=$gata_id";
	
    $result1 = mysqli_query($con, $query1);
	
    if ($result1->num_rows > 0) {
        echo '<option value="">Select Plot</option>';
        while ($row = mysqli_fetch_assoc($result1)) 
		{
            echo '<option value="' . htmlspecialchars($row['plot_id']) . '">' . htmlspecialchars($row['plot_no']) . '</option>';
        }
    }
	
}
