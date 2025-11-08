<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Temporarily Unavailable</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-dark: #0d1642;
            --primary-light: #4a5bbc;
            --secondary-color: #00bcd4;
            --font-main: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-main);
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            max-width: 600px;
            text-align: center;
            background-color: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .logo {
            max-width: 200px;
            margin-bottom: 30px;
        }
        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 18px;
            line-height: 1.6;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 25px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .contact-info {
            margin-top: 30px;
            font-size: 16px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="../assets/images/logo.png" alt="APS Dream Home Logo" class="logo">
        <h1>We'll Be Back Soon!</h1>
        <p>We're currently experiencing technical difficulties with our database connection. Our team has been notified and is working to resolve the issue as quickly as possible.</p>
        <p>Please check back in a few minutes.</p>
        <a href="javascript:location.reload()" class="btn btn-primary">Try Again</a>
        <div class="contact-info">
            <p>If you need immediate assistance, please contact us at:<br>
            <strong>support@apsdreamhome.com</strong> or call <strong>+91 1234567890</strong></p>
        </div>
    </div>
</body>
</html>