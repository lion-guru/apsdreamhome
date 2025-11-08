<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Internal Server Error | APS Dream Homes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .error-container {
            text-align: center;
            background-color: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 5rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="mb-4">Oops! Something Went Wrong</h1>
            <p class="lead text-muted">
                We're experiencing a temporary technical issue. 
                Our team has been notified and is working to resolve it.
            </p>
            <div class="mt-4">
                <a href="/" class="btn btn-primary">Return to Homepage</a>
                <a href="/contact" class="btn btn-outline-secondary ms-2">Contact Support</a>
            </div>
            <small class="d-block mt-3 text-muted">
                Error ID: <?php echo uniqid(); ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>
