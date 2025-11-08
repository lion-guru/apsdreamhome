<?php
session_start();
require_once(__DIR__ . '/includes/config/session_check.php');
require_once(__DIR__ . '/includes/classes/UserSession.php');

// Check if user is logged in
$userSession = new UserSession();
if (!$userSession->isLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Property Suggestions | APS Dream Homes</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/assets/css/homepage-modern.css">
    <link rel="stylesheet" href="/assets/css/ai_suggestions.css">
</head>
<body>
    <?php include_once(__DIR__ . '/includes/components/navbar.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="ai-suggestion-container">
                    <h2 class="text-center mb-4">AI Property Suggestions</h2>
                    
                    <form id="ai-suggestion-form" class="ai-suggestion-form">
                        <div class="form-group">
                            <label for="property-type">Property Type</label>
                            <select id="property-type" class="form-control" required>
                                <option value="">Select Property Type</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="house">House</option>
                                <option value="plot">Plot</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="budget">Budget (₹)</label>
                            <input type="number" id="budget" class="form-control" placeholder="Enter your budget" required>
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" class="form-control" placeholder="Enter desired location" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block mt-3">
                            Generate AI Suggestions
                        </button>
                    </form>

                    <div id="ai-loading-indicator" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Generating AI suggestions...</p>
                    </div>

                    <div id="ai-error-container" class="alert alert-danger"></div>

                    <div id="ai-suggestion-results" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AI Suggestions Script -->
    <script src="/assets/js/ai_suggestions.js"></script>
</body>
</html>
