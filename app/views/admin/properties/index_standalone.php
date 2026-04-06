<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin/dashboard">APS Dream Home Admin</a>
            <a class="btn btn-outline-light btn-sm" href="/admin/logout">Logout</a>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="/admin/dashboard" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="/admin/properties" class="list-group-item list-group-item-action active">Properties</a>
                    <a href="/admin/sites" class="list-group-item list-group-item-action">Sites</a>
                    <a href="/admin/plots" class="list-group-item list-group-item-action">Plots</a>
                    <a href="/admin/bookings" class="list-group-item list-group-item-action">Bookings</a>
                    <a href="/admin/leads" class="list-group-item list-group-item-action">Leads</a>
                </div>
            </div>
            
            <div class="col-md-10">
                <h1><i class="fas fa-building me-2"></i>Property Management</h1>
                
                <div class="card mt-4">
                    <div class="card-body">
                        <h5>Properties Overview</h5>
                        <p>Manage all properties in the system.</p>
                        <a href="/admin/properties/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Add Property
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
