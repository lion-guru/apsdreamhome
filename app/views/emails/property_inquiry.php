<?php $baseUrl = rtrim(BASE_URL, '/'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Property Inquiry</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .property-card { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .inquiry-details { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>ðŸ“§ New Property Inquiry</h2>
            <p>Action required for property: <?= htmlspecialchars($property_name ?? 'Unknown Property') ?></p>
        </div>

        <div class="content">
            <div class="property-card">
                <h3><?= htmlspecialchars($property_name ?? 'Property Details') ?></h3>
                <p><strong>Property URL:</strong> <a href="<?= $baseUrl ?>/properties"><?= $baseUrl ?>/properties</a></p>
            </div>

            <div class="inquiry-details">
                <h4>ðŸ‘¤ Customer Details:</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($customer_name ?? 'Unknown') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($customer_email ?? 'unknown@example.com') ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($customer_phone ?? 'N/A') ?></p>
                <p><strong>Message:</strong></p>
                <p><?= nl2br(htmlspecialchars($message ?? 'No message provided')) ?></p>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Contact the customer within 24 hours</li>
                <li>Schedule a property viewing if requested</li>
                <li>Update inquiry status in admin panel</li>
                <li>Follow up after property visit</li>
            </ol>

            <div class="style-69368">
                <a href="<?= $baseUrl ?>/admin" class="button">View in Admin Panel</a>
                <a href="mailto:<?= htmlspecialchars($customer_email ?? '') ?>" class="button" class="style-4360">Reply to Customer</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from APS Dream Home CRM system.</p>
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</body>
</html>