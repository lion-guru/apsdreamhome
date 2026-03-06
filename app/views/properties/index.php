<?php
$page_title = "Page";
$page_description = "APS Dream Home Page";
include __DIR__ . "/../layouts/base_new.php";
?>

<div class="container-fluid">
    <div class="container">
        <h1><?php echo $page_title; ?></h1>
        <p><?php echo $page_description; ?></p>
    </div>
</div>
<?php include __DIR__ . "/../layouts/footer_new.php"; ?>