<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #2563eb; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Payment Reminder</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{{customer_name}}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">This is a friendly reminder that your upcoming EMI installment is due soon.</p>
    
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin: 16px 0; text-align: center;">
        <p style="margin: 0; font-size: 12px; color: #1d4ed8; text-transform: uppercase; letter-spacing: 1px;">Amount Due</p>
        <p style="margin: 4px 0 0; font-size: 28px; font-weight: bold; color: #1d4ed8;">₹{{amount}}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 14px;">
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Booking Number</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{{booking_number}}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Installment #</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{{installment_no}}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Due Date</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{{due_date}}</td></tr>
        <tr><td style="padding: 10px 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: bold; color: #334155;">Plot</td><td style="padding: 10px 12px; border: 1px solid #e2e8f0; color: #1e293b;">{{plot_number}}, {{colony_name}}</td></tr>
    </table>

    <p style="color: #475569; line-height: 1.6;">Please ensure timely payment to avoid late fees. You can pay online via the customer portal or visit our office.</p>
    <p style="color: #475569; line-height: 1.6;">For any queries, call us at <strong style="color: #0d9488;">{{company_phone}}</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
