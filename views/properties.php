<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>"><i class="fas fa-home me-2"></i>APS Dream Home</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>properties">Properties</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>projects">Projects</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>about">About</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>contact">Contact</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Our Properties</h1>
        <p>Browse our selection of premium properties</p>
        
        <div class="row">
            <?php foreach ($properties as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo $property['title']; ?></h5>
                        <p class="text-primary">₹<?php echo number_format($property['price']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo $property['city']; ?></p>
                        <p><i class="fas fa-home"></i> <?php echo ucfirst($property['type']); ?></p>
                        <a href="<?php echo BASE_URL; ?>properties/<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
