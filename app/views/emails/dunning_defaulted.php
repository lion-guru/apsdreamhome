<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Booking Defaulted — Action Required</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{{customer_name}}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">We regret to inform you that your EMI plan for <strong>{{booking_number}}</strong> has been <strong style="color: #dc2626;">DEFAULTED</strong> due to non-payment of {{overdue_count}} consecutive installments.</p>
    
    <div style="background: #fef2f2; border: 2px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="padding: 6px 0; color: #64748b;">Plot:</td><td style="padding: 6px 0; font-weight: bold; color: #1e293b;">{{plot_number}}, {{colony_name}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Total Overdue Amount:</td><td style="padding: 6px 0; font-weight: bold; color: #dc2626; font-size: 18px;">₹{{total_overdue}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Total Penalty Accrued:</td><td style="padding: 6px 0; font-weight: bold; color: #dc2626;">₹{{total_penalty}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Outstanding Installments:</td><td style="padding: 6px 0; color: #1e293b;">{{overdue_count}}</td></tr>
        </table>
    </div>

    <div style="background: #fef2f2; border-left: 4px solid #dc2626; padding: 12px 16px; margin: 16px 0;">
        <p style="margin: 0; color: #991b1b; font-weight: bold;">CONSEQUENCES OF DEFAULT</p>
        <p style="margin: 8px 0 0; color: #991b1b; line-height: 1.5; font-size: 14px;">As per the terms of your booking agreement, default may result in:<br>
        1. Cancellation of your booking<br>
        2. Forfeiture of amounts already paid<br>
        3. Legal proceedings for recovery of outstanding dues<br>
        4. The plot being re-sold to recover losses</p>
    </div>

    <p style="color: #475569; line-height: 1.6;">You are requested to clear all outstanding dues within <strong>15 days</strong> to avoid cancellation of your booking.</p>
    <p style="color: #475569; line-height: 1.6;">Contact us: <strong style="color: #4f46e5;">+91 92771 21112</strong> | <strong style="color: #4f46e5;">finance@apsdreamhome.com</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
