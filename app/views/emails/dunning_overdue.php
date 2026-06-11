<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f1f5f9;">
<div style="background: #dc2626; color: white; padding: 24px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="margin: 0; font-size: 24px;">APS Dream Home</h1>
    <p style="margin: 6px 0 0; font-size: 14px; opacity: 0.9;">Payment Overdue — Immediate Action Required</p>
</div>
<div style="padding: 24px; background: #ffffff;">
    <p style="font-size: 16px; color: #1e293b;">Dear <strong>{{customer_name}}</strong>,</p>
    <p style="color: #475569; line-height: 1.6;">Your EMI installment is now <strong style="color: #dc2626;">{{days_overdue}} days overdue</strong>. Immediate payment is required to avoid further penalties and potential action on your booking.</p>
    
    <div style="background: #fef2f2; border: 2px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 14px;">
            <tr><td style="padding: 6px 0; color: #64748b;">Booking:</td><td style="padding: 6px 0; font-weight: bold; color: #1e293b;">{{booking_number}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Installment:</td><td style="padding: 6px 0; color: #1e293b;">#{{installment_no}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Original Amount:</td><td style="padding: 6px 0; color: #1e293b;">₹{{amount}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b;">Penalty Accrued:</td><td style="padding: 6px 0; font-weight: bold; color: #dc2626;">₹{{penalty}}</td></tr>
            <tr><td style="padding: 6px 0; color: #64748b; border-top: 2px solid #fecaca; padding-top: 10px; font-weight: bold;">Total Due Now:</td><td style="padding: 6px 0; border-top: 2px solid #fecaca; padding-top: 10px; font-weight: bold; color: #dc2626; font-size: 18px;">₹{{total_due}}</td></tr>
        </table>
    </div>

    <p style="color: #475569; line-height: 1.6;">Late payment charges accrue at <strong>18% per annum</strong> on the overdue amount. Please pay immediately to avoid further escalation.</p>
    <p style="color: #475569; line-height: 1.6;">Pay online: <a href="{{payment_url}}" style="color: #4f46e5; font-weight: bold;">Click Here to Pay</a></p>
    <p style="color: #475569; line-height: 1.6;">For queries, call <strong style="color: #4f46e5;">{{company_phone}}</strong></p>
</div>
<div style="background: #1e293b; color: #94a3b8; padding: 16px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px;">
    <p style="margin: 0;">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
