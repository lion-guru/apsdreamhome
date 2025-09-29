<?php
// Site Header for APS Dream Home (shared across pages like /leads/index.php)
// Outputs DOCTYPE, <head> with CSS, a top navbar, and opens <body>.

$title = $title ?? ($pageTitle ?? 'APS Dream Home');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f7f8fa; }
    .navbar-brand { font-weight: 600; }
    .badge-status { font-size: 0.75rem; }
  </style>
  <?php
  // If page provides extra head content
  if (function_exists('page_extra_head')) {
      page_extra_head();
  }
  ?>
  </head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">APS Dream Home</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link<?php echo ($activeNav ?? '') === 'dashboard' ? ' active' : ''; ?>" href="/">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link<?php echo ($activeNav ?? '') === 'leads' ? ' active' : ''; ?>" href="/leads/">Leads</a></li>
          <li class="nav-item"><a class="nav-link" href="/customer/">Customers</a></li>
          <li class="nav-item"><a class="nav-link" href="/properties/">Properties</a></li>
        </ul>
        <div class="d-flex">
          <a class="btn btn-outline-light btn-sm" href="/auth/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>
