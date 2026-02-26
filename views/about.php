<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-2"></i>APS Dream Home</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>properties">Properties</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>projects">Projects</a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>about">About</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>contact">Contact</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>About APS Dream Home</h1>
        <p>Your trusted real estate partner in Uttar Pradesh with over 15 years of experience.</p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Our Mission</h3>
                <p>To help you find your dream property with transparency, trust, and exceptional service.</p>
            </div>
            <div class="col-md-6">
                <h3>Our Vision</h3>
                <p>To be the most trusted real estate platform in Uttar Pradesh.</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-12">
                <h3>MVC Architecture</h3>
                <p>This website is built using the Model-View-Controller (MVC) pattern:</p>
                <ul>
                    <li><strong>Models:</strong> Property, User, Project models for data management</li>
                    <li><strong>Views:</strong> Template files for presentation (this page)</li>
                    <li><strong>Controllers:</strong> HomeController, AdminController for business logic</li>
                    <li><strong>Routing:</strong> Clean URL routing system</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
