<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="APS Dream Homes Pvt Ltd - Leading real estate developer in Gorakhpur with 8+ years of excellence">
    <meta name="keywords" content="real estate, property, Gorakhpur, apartments, villas, plots, commercial, APS Dream Homes Pvt Ltd">
    <meta name="author" content="APS Dream Homes Pvt Ltd">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">

    <title>APS Dream Homes Pvt Ltd</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: white !important;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .footer {
            background: var(--dark-color);
            color: white;
            padding: 50px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-home me-2"></i>APS Dream Homes
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="projectsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Projects
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="projectsDropdown">
                            <li><a class="dropdown-item" href="projects.php">All Projects</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">By Location</h6></li>
                            <li><a class="dropdown-item" href="projects.php?location=Gorakhpur">Gorakhpur</a></li>
                            <li><a class="dropdown-item" href="projects.php?location=Lucknow">Lucknow</a></li>
                            <li><a class="dropdown-item" href="projects.php?location=Varanasi">Varanasi</a></li>
                            <li><a class="dropdown-item" href="projects.php?location=Allahabad">Allahabad</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">By Status</h6></li>
                            <li><a class="dropdown-item" href="projects.php?status=upcoming">Upcoming Projects</a></li>
                            <li><a class="dropdown-item" href="projects.php?status=ongoing">Ongoing Projects</a></li>
                            <li><a class="dropdown-item" href="projects.php?status=completed">Completed Projects</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_template.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact_template.php">Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="tel:+917007444842" class="btn btn-outline-success me-2">
                        <i class="fas fa-phone me-1"></i>+91-7007444842
                    </a>
                    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="registration.php" class="btn btn-success">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 80px;">
