<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        p {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .error-details {
            text-align: left;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 200px;
            overflow-y: auto;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin: 0.25rem;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>500 - Internal Server Error</h1>
        <p>Oops! Something went wrong on our end. Our team has been notified and we're working to fix it.</p>
        
        <?php if (defined('SHOW_ERRORS') && SHOW_ERRORS === true && !empty($error)): ?>
            <div class="error-details">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div>
            <a href="/" class="btn">Go to Homepage</a>
            <button onclick="window.history.back()" class="btn btn-secondary">Go Back</button>
        </div>
    </div>
</body>
</html>
