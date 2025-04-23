<?php require_once(__DIR__.'/../config/base_url.php'); ?>
<!-- Static Fallback Header (No DB Required) -->
<header class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $base_url; ?>">
      <img src="<?php echo $base_url; ?>assets/images/logo/apslogo.png" alt="APS Dream Homes" style="height:40px;">
      <span class="fw-bold ms-2">APS Dream Homes</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>feature.php">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>gallary.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>contact.php">Contact</a></li>
      </ul>
    </div>
  </div>
</header>
