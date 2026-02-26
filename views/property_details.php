<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details - APS Dream Home</title>
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
                <a class="nav-link" href="<?php echo BASE_URL; ?>contact">Contact</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Property Details</h1>
        <div class="row">
            <div class="col-md-8">
                <img src="https://via.placeholder.com/800x400/1b5fd0/ffffff?text=Property+Image" class="img-fluid" alt="Property">
            </div>
            <div class="col-md-4">
                <h3><?php echo $property['title']; ?></h3>
                <p class="text-primary h4">₹<?php echo number_format($property['price']); ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo $property['city']; ?></p>
                <p><i class="fas fa-home"></i> <?php echo ucfirst($property['type']); ?></p>
                <p><i class="fas fa-bed"></i> <?php echo $property['bedrooms']; ?> Bedrooms</p>
                <p><i class="fas fa-bath"></i> <?php echo $property['bathrooms']; ?> Bathrooms</p>
                <p><i class="fas fa-ruler-combined"></i> <?php echo $property['area']; ?> sqft</p>
                <button class="btn btn-primary btn-lg w-100">Contact Agent</button>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h3>Description</h3>
                <p><?php echo $property['description']; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
