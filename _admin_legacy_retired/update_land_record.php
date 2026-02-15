<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $farmer_name = mysqli_real_escape_string($con, $_POST['farmer_name']);
    $farmer_mobile = mysqli_real_escape_string($con, $_POST['farmer_mobile']);
    $bank_name = mysqli_real_escape_string($con, $_POST['bank_name']);
    $account_number = mysqli_real_escape_string($con, $_POST['account_number']);
    $bank_ifsc = mysqli_real_escape_string($con, $_POST['bank_ifsc']);
    $site_name = mysqli_real_escape_string($con, $_POST['site_name']);
    $land_area = floatval($_POST['land_area']);
    $total_land_price = floatval($_POST['total_land_price']);
    $total_paid_amount = floatval($_POST['total_paid_amount']);
    $amount_pending = floatval($_POST['amount_pending']);
    $gata_number = mysqli_real_escape_string($con, $_POST['gata_number']);
    $district = mysqli_real_escape_string($con, $_POST['district']);
    $tehsil = mysqli_real_escape_string($con, $_POST['tehsil']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $gram = mysqli_real_escape_string($con, $_POST['gram']);
    $land_manager_name = mysqli_real_escape_string($con, $_POST['land_manager_name']);
    $land_manager_mobile = mysqli_real_escape_string($con, $_POST['land_manager_mobile']);
    $agreement_status = mysqli_real_escape_string($con, $_POST['agreement_status']);

    $query = "UPDATE kisaan_land_management SET 
        farmer_name='$farmer_name', 
        farmer_mobile='$farmer_mobile', 
        bank_name='$bank_name', 
        account_number='$account_number', 
        bank_ifsc='$bank_ifsc', 
        site_name='$site_name', 
        land_area='$land_area', 
        total_land_price='$total_land_price', 
        total_paid_amount='$total_paid_amount', 
        amount_pending='$amount_pending', 
        gata_number='$gata_number', 
        district='$district', 
        tehsil='$tehsil', 
        city='$city', 
        gram='$gram', 
        land_manager_name='$land_manager_name', 
        land_manager_mobile='$land_manager_mobile', 
        agreement_status='$agreement_status' 
        WHERE id=$id";

    if (mysqli_query($con, $query)) {
        header("Location: land_records.php?msg=Record updated successfully.");
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
}
?>
