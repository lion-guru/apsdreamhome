<?php
$curl = curl_init();
$ifsc_chk = "SBIN0005608";
$url_chk = 'https://bank-apis.justinclicks.com/API/V1/IFSC/'.$ifsc_chk.'/';
//echo $url_chk;
//exit();
curl_setopt_array($curl, array(
  CURLOPT_URL => $url_chk,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);
$ifsc = json_decode($response,true);
//print_r($ifsc);
echo"</br>";
echo "Bank Name : ".$ifsc["BANK"];
echo"</br>";
echo "IFSC : ". $ifsc["IFSC"];
echo"</br>";
echo "MICR : ". $ifsc["MICR"];
echo"</br>";
echo "BRANCH Name : ". $ifsc["BRANCH"];
echo"</br>";
echo "Bank Address : ". $ifsc["ADDRESS"];
echo"</br>";
echo "Bank City : ". $ifsc["CITY"];
echo"</br>";
echo "Bank Distrcit : ". $ifsc["DISTRICT"];
echo"</br>";
echo "Bank State : ". $ifsc["STATE"];


curl_close($curl);
//echo $response;
?>