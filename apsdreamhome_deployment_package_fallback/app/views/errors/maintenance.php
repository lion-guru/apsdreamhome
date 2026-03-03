<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance in Progress - APS Dream Home</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #2c3e50;
        }
        .maintenance-container {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 2rem;
        }
        h1 {
            color: #3498db;
            margin-bottom: 1.5rem;
            font-size: 2.2rem;
        }
        p {
            color: #7f8c8d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #e8f4fc;
            border-radius: 50px;
            color: #3498db;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .progress-container {
            width: 100%;
            background: #f1f1f1;
            border-radius: 10px;
            margin: 2rem 0;
            height: 10px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            width: 75%;
            background: #3498db;
            border-radius: 10px;
            animation: progress 2s ease-in-out infinite;
            background: linear-gradient(90deg, #3498db, #2ecc71);
        }
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .contact {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        .contact a {
            color: #3498db;
            text-decoration: none;
        }
        .contact a:hover {
            text-decoration: underline;
        }
        .social-links {
            margin-top: 1.5rem;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #7f8c8d;
            font-size: 1.5rem;
            transition: color 0.3s;
        }
        .social-links a:hover {
            color: #3498db;
        }
        @media (max-width: 480px) {
            .maintenance-container {
                padding: 2rem 1.5rem;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <img src="/assets/images/logo.png" alt="APS Dream Home" class="logo">
        <h1>We'll Be Back Soon!</h1>
        <div class="status">Under Maintenance</div>
        
        <p>We're currently performing scheduled maintenance to improve our services. We apologize for the inconvenience and appreciate your patience.</p>
        
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
        <p>Expected completion: <strong>2:00 PM IST</strong></p>
        
        <div class="contact">
            <p>Need immediate assistance? <a href="mailto:support@apsdreamhome.com">Contact our support team</a></p>
            
            <div class="social-links">
                <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>
</html>
