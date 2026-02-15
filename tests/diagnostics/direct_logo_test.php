<?php
// Direct Logo Test with apslogo1.png
$logoPath = 'assets/images/logo/apslogo1.png';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Direct Logo Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #1e40af 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .logo {
            height: 60px;
            width: auto;
            max-width: 80px;
            background: white;
            padding: 8px;
            border-radius: 10px;
            margin: 0 auto;
            display: block;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .info { background: white; padding: 15px; margin: 15px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎨 APS Dream Home - Logo Test</h1>
        <img src="<?php echo $logoPath; ?>" alt="APS Dream Homes Logo" class="logo" onerror="this.style.display='none'; document.getElementById('fallback').style.display='block';">
        <div id="fallback" style="display:none; margin-top:10px;">
            <div style="width:60px; height:60px; background:#f59e0b; border-radius:50%; margin:0 auto; display:flex; align-items:center; justify-content:center; color:white; font-size:24px;">
                🏠
            </div>
        </div>
    </div>

    <div class="info">
        <h3>Logo Information:</h3>
        <p><strong>File:</strong> <?php echo $logoPath; ?></p>
        <p><strong>Size:</strong> <?php echo file_exists($logoPath) ? number_format(filesize($logoPath)) . ' bytes' : 'File not found'; ?></p>
        <p><strong>Exists:</strong> <?php echo file_exists($logoPath) ? '✅ YES' : '❌ NO'; ?></p>
        <p><strong>Modified:</strong> <?php echo file_exists($logoPath) ? date('Y-m-d H:i:s', filemtime($logoPath)) : 'N/A'; ?></p>
    </div>
</body>
</html>
