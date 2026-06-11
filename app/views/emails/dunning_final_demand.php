<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #7c3aed; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Final Demand Notice</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{{customer_name}}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">This is a <strong>final demand</strong> for your overdue EMI payment. Your account is now <strong style="color: #7c3aed;">{{days_overdue}} days past due</strong> across {{overdue_count}} installment(s).</p>
    
    <div style="background: #faf5ff; border: 2px solid #c4b5fd; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="padding: 6px 0; color: #64748b;">Booking:</td><td style="padding: 6px 0; font-weight: bold; color: #1e293b;">{{booking_number}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Total Overdue Amount:</td><td style="padding: 6px 0; font-weight: bold; color: #7c3aed; font-size: 18px;">₹{{total_overdue}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Penalty Accrued:</td><td style="padding: 6px 0; font-weight: bold; color: #dc2626;">₹{{total_penalty}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Worst Overdue:</td><td style="padding: 6px 0; color: #1e293b;">{{days_overdue}} days</td></tr>
        </table>
    </div>

    <div style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 12px 16px; margin: 16px 0;">
        <p style="margin: 0; color: #991b1b; font-weight: bold;">⚠ IMPORTANT NOTICE</p>
        <p style="margin: 8px 0 0; color: #991b1b; line-height: 1.5; font-size: 14px;">If payment is not received within <strong>15 days</strong>, APS Dream Home reserves the right to:<br>
        1. Cancel your booking and retain amounts paid<br>
        2. Initiate legal proceedings for recovery<br>
        3. Report the default to credit bureaus<br>
        4. Realize the plot and resell it</p>
    </div>

    <p style="color: #475569; line-height: 1.6;">Please settle all dues immediately to avoid these consequences.</p>
    <p style="color: #475569; line-height: 1.6;">Contact us: <strong style="color: #4f46e5;">{{company_phone}}</strong> | <strong style="color: #4f46e5;">finance@apsdreamhome.com</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
