<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->yield('title') ?: 'APS Dream Home' ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>favicon.ico">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) to avoid local build) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkfL9VnYxwYI0Y5QX4YkWjWnQNEwG8VS9Kf9ifsC6XwX4C1Nn9Q9KfX1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Existing site styles (if any) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Custom app styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/app.css">

    <?= $this->yield('styles') ?>
</head>
<body class="bg-gray-50 font-[Poppins]">
    <!-- Header -->
    <?php $this->include('partials/header'); ?>

    <!-- Main Content -->
    <main class="min-h-screen">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php $this->include('partials/footer'); ?>

    <!-- Scripts -->
    <script src="<?= BASE_URL ?>js/app.js" defer></script>
    <?= $this->yield('scripts') ?>
</body>
</html>
