<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }
        .icon {
            font-size: 5em;
            color: #ffc107;
            margin-bottom: 20px;
        }
        h1 {
            color: #d9534f;
            font-size: 2.8em;
            margin-bottom: 0.5em;
        }
        p {
            font-size: 1.2em;
            line-height: 1.7;
            margin-bottom: 1em;
        }
        .message {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 2em;
        }
        .footer {
            font-size: 0.9em;
            color: #777;
            margin-top: 2em;
        }
        /* Simple animation for the icon */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .icon.animated {
            animation: spin 4s linear infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon animated">&#x1F6A7;</div> <!-- Construction emoji -->
        <h1>Site Under Maintenance</h1>
        <p>We are currently performing scheduled maintenance to improve our services.</p>
        <p class="message">The site will be back online shortly. We apologize for any inconvenience.</p>
        <div class="footer">
            &copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.
        </div>
    </div>
</body>
</html>
