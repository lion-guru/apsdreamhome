<?php $baseUrl = rtrim(BASE_URL, '/'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #dc3545, #e83e8c); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .notification-card { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 5px solid #dc3545; }
        .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
        .priority-high { border-left-color: #dc3545; }
        .priority-medium { border-left-color: #ffc107; }
        .priority-low { border-left-color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>ðŸš¨ Admin Notification</h2>
            <p><?= htmlspecialchars($notification_type ?? 'System') ?> - Action Required</p>
        </div>

        <div class="content">
            <div class="notification-card priority-high">
                <h3>ðŸ"‹ Notification Details</h3>
                <p><strong>Type:</strong> <?= htmlspecialchars($notification_type ?? 'System') ?></p>
                <p><strong>Message:</strong> <?= htmlspecialchars($notification_message ?? '') ?></p>
                <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
            </div>

            <p><strong>Recommended Actions:</strong></p>
            <ol>
                <li>Review the notification details above</li>
                <li>Take appropriate action based on the type</li>
                <li>Update status if applicable</li>
                <li>Monitor for similar notifications</li>
            </ol>

            <div class="style-69368">
                <a href="<?= $baseUrl ?>/admin" class="button">Open Admin Panel</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from APS Dream Home admin system.</p>
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>