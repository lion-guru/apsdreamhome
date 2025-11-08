
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .payment-card { background: white; padding: 25px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 15px rgba(0,0,0,0.1); border-left: 5px solid #28a745; }
        .amount { font-size: 2rem; font-weight: bold; color: #28a745; }
        .button { display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; margin: 15px 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Payment Successful!</h1>
            <p>Your payment has been processed successfully</p>
        </div>

        <div class="content">
            <div class="payment-card">
                <h3>Payment Details</h3>
                <p><strong>Order ID:</strong> #N/A</p>
                <p><strong>Amount Paid:</strong> <span class="amount">₹0</span></p>
                <p><strong>Payment Method:</strong> Unknown</p>
                <p><strong>Transaction ID:</strong> N/A</p>
                <p><strong>Payment Date:</strong> 20 Oct 2025, 10:24 PM</p>
            </div>

            <h3>Hi Valued Customer!</h3>

            <p>Thank you for your payment! Your transaction has been successfully processed.</p>

            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p><strong>What happens next?</strong></p>
                <ul>
                    <li>You will receive a confirmation SMS shortly</li>
                    <li>Your booking/agent will contact you within 24 hours</li>
                    <li>You can download your receipt anytime</li>
                    <li>All transaction details are saved in your account</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="http://localhost/apsdreamhomefinal/payment/receipt" class="button">Download Receipt</a>
                <a href="http://localhost/apsdreamhomefinal/dashboard" class="button" style="background: #6c757d;">View Dashboard</a>
            </div>

            <p>If you have any questions about your payment, please contact our support team:</p>
            <p>📧 Email: support@apsdreamhome.com<br>
               📞 Phone: +91-9876543210</p>
        </div>

        <div class="footer">
            <p>Payment processed by APS Dream Home Payment Gateway</p>
            <p>Transaction ID: N/A</p>
            <p>&copy; 2025 APS Dream Home. All rights reserved.</p>
        </div>
    </div>
</body>
</html>