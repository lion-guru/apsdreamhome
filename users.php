<?php

// Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'realestatephp');
define('DB_USER_TBL', 'user');

class User {
  private $dbHost     = DB_HOST;
  private $dbUsername = DB_USERNAME;
  private $dbPassword = DB_PASSWORD;
  private $dbName     = DB_NAME;
  private $userTbl    = DB_USER_TBL;

  function __construct(){
    if(!isset($this->db)){
      // Connect to the database
      $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
      if($conn->connect_error){
        die("Failed to connect with MySQL: " . $conn->connect_error);
      }else{
        $this->db = $conn;
      }
    }
  }

  function checkUser($data = array()){
    if(!empty($data)){
      // Check whether the user already exists in the database
      $checkQuery = "SELECT * FROM ".$this->userTbl." WHERE oauth_provider = '".$data['oauth_provider']."' AND oauth_uid = '".$data['oauth_uid']."'";
      $checkResult = $this->db->query($checkQuery);

      // Add modified time to the data array
      if(!array_key_exists('modified',$data)){
        $data['modified'] = date("Y-m-d H:i:s");
      }

      if($checkResult->num_rows > 0){
        // Prepare column and value format
        $colvalSet = '';
        $i = 0;
        foreach($data as $key=>$val){
          $pre = ($i > 0)?', ':'';
          $colvalSet .= $pre.$key."='".$this->db->real_escape_string($val)."'";
          $i++;
        }
        $whereSql = " WHERE oauth_provider = '".$data['oauth_provider']."' AND oauth_uid = '".$data['oauth_uid']."'";

        // Update user data in the database
        $query = "UPDATE ".$this->userTbl." SET ".$colvalSet.$whereSql;
        $update = $this->db->query($query);
      }else{
        // Add created time to the data array
        if(!array_key_exists('created',$data)){
          $data['created'] = date("Y-m-d H:i:s");
        }

        // Prepare column and value format
        $columns = '';
        $values = '';
        $i = 0;
        foreach($data as $key=>$val){
          $pre = ($i > 0)?', ':'';
          $columns .= $pre.$key;
          $values .= $pre."'".$this->db->real_escape_string($val)."'";
          $i++;
        }

        // Insert user data into the database
        $query = "INSERT INTO ".$this->userTbl." (".$columns.") VALUES (".$values.")";
        $insert = $this->db->query($query);
      }
    }
  }
}

// Facebook configuration
$appId = '522220926942748';
$appSecret = 'f122d38c159768e6332bfb0355958473';
$redirectUrl = 'http://apsdreamhomes.com/login.php';

// Facebook login URL
$fbLoginUrl = 'https://www.facebook.com/dialog/oauth?client_id=' . $appId . '&redirect_uri=' . $redirectUrl . '&scope=email';

// Redirect to Facebook login URL
header('Location: ' . $fbLoginUrl);
exit;

// Get the Facebook access token
$accessTokenUrl = 'https://graph.facebook.com/oauth/access_token?client_id=' . $appId . '&redirect_uri=' . $redirectUrl . '&client_secret=' . $appSecret . '&code=' . $_GET['code'];
$accessTokenResponse = json_decode(file_get_contents($accessTokenUrl), true);
$accessToken = $accessTokenResponse['access_token'];

// Get the Facebook user profile
$graphUrl = 'https://graph.facebook.com/me?access_token=' . $accessToken;
$graphResponse = json_decode(file_get_contents($graphUrl), true);

// Check if the user exists in the database
$data = array(
  'oauth_provider' => 'facebook',
  'oauth_uid' => $graphResponse['id'],
  'first_name' => $graphResponse['first_name'],
  'last_name' => $graphResponse['last_name'],
  'email' => $graphResponse['email'],
  'gender' => $graphResponse['gender'],
  'picture' => $graphResponse['picture']['data']['url'],
  'link' => $graphResponse['link']
);

$user = new User();
$user->checkUser($data);

?>