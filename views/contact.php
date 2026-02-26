<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - APS Dream Home</title>
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
                <a class="nav-link" href="<?php echo BASE_URL; ?>about">About</a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>contact">Contact</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Contact Us</h1>
        <div class="row">
            <div class="col-md-6">
                <h3>Get in Touch</h3>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Contact Information</h3>
                <p><i class="fas fa-phone"></i> +91-XXXXXXXXXX</p>
                <p><i class="fas fa-envelope"></i> info@apsdreamhome.com</p>
                <p><i class="fas fa-map-marker-alt"></i> Gorakhpur, Uttar Pradesh</p>
            </div>
        </div>
    </div>
</body>
</html>
