<?php
include("config.php");
if (!empty($_POST['site_id'])) {
    $site_id = $_POST['site_id'];

	$query3 = "select * from plot_master where site_id=$site_id";
	
	$result3 = mysqli_query($con, $query3);
    
	if ($result3->num_rows > 0) {
        echo '<option value="">Select plot</option>';
        while ($row3 = mysqli_fetch_assoc($result3)) 
		{
            echo '<option value="' . htmlspecialchars($row3['plot_id']) . '">' . htmlspecialchars($row3['plot_no']) . '</option>';
        }
    }
}

?>
