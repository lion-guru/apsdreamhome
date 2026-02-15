<?php
include("config.php");
if (!empty($_POST['sid'])) {
    $gata_id = $_POST['sid'];

	$query2 = "select * from kissan_master where gata_a=? OR gata_b=? OR gata_c=? OR gata_d=?";
	$stmt = $con->prepare($query2);
	$stmt->bind_param("iiii", $gata_id, $gata_id, $gata_id, $gata_id);
	$stmt->execute();
	$result2 = $stmt->get_result();
    
	if ($result2->num_rows > 0) {
        echo '<option value="">Select Farmer</option>';
        while ($row2 = $result2->fetch_assoc()) 
		{
            echo '<option value="' . htmlspecialchars($row2['kissan_id']) . '">' . htmlspecialchars($row2['k_name']) . '</option>';
        }
    }
}
if (!empty($_POST['site_id'])) {
    $site_id = $_POST['site_id'];

	$query3 = "select * from kissan_master where site_id=?";
	$stmt = $con->prepare($query3);
	$stmt->bind_param("i", $site_id);
	$stmt->execute();
	$result3 = $stmt->get_result();
    
	if ($result3->num_rows > 0) {
        echo '<option value="">Select Farmer</option>';
        while ($row3 = $result3->fetch_assoc()) 
		{
            echo '<option value="' . htmlspecialchars($row3['kissan_id']) . '">' . htmlspecialchars($row3['k_name']) . '</option>';
        }
    }
}

?>