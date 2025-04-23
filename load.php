<?php
function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="abhay3007";
	$dbpass="abhay@128128";
	$dbname="realestatephp";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

	$sql = "select sponsor_id as memberId, sponsored_by as parentId  ,uname as otherInfo from user";	
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($wines);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
?>
