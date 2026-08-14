<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-10137">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Payment Overdue â€” Immediate Action Required</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{{customer_name}}</strong>,</p>
    <p class="style-10698">Your EMI installment is now <strong class="style-31031">{{days_overdue}} days overdue</strong>. Immediate payment is required to avoid further penalties and potential action on your booking.</p>
    
    <div class="style-20329">
        <table class="style-61075">
            <tr><td class="style-20694">Booking:</td><td class="style-60925">{{booking_number}}</td></tr>
            <tr><td class="style-20694">Installment:</td><td class="style-42101">#{{installment_no}}</td></tr>
            <tr><td class="style-20694">Original Amount:</td><td class="style-42101">â‚¹{{amount}}</td></tr>
            <tr><td class="style-20694">Penalty Accrued:</td><td class="style-15627">â‚¹{{penalty}}</td></tr>
            <tr><td class="style-24404">Total Due Now:</td><td class="style-77436">â‚¹{{total_due}}</td></tr>
        </table>
    </div>

    <p class="style-10698">Late payment charges accrue at <strong>18% per annum</strong> on the overdue amount. Please pay immediately to avoid further escalation.</p>
    <p class="style-10698">Pay online: <a href="{{payment_url}}" class="style-69054">Click Here to Pay</a></p>
    <p class="style-10698">For queries, call <strong class="style-22019">{{company_phone}}</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
