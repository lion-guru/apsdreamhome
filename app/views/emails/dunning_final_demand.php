<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-91602">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Final Demand Notice</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{{customer_name}}</strong>,</p>
    <p class="style-10698">This is a <strong>final demand</strong> for your overdue EMI payment. Your account is now <strong class="style-51761">{{days_overdue}} days past due</strong> across {{overdue_count}} installment(s).</p>
    
    <div class="style-37601">
        <table class="style-61075">
            <tr><td class="style-20694">Booking:</td><td class="style-60925">{{booking_number}}</td></tr>
            <tr><td class="style-20694">Total Overdue Amount:</td><td class="style-36562">â‚¹{{total_overdue}}</td></tr>
            <tr><td class="style-20694">Penalty Accrued:</td><td class="style-15627">â‚¹{{total_penalty}}</td></tr>
            <tr><td class="style-20694">Worst Overdue:</td><td class="style-42101">{{days_overdue}} days</td></tr>
        </table>
    </div>

    <div class="style-16633">
        <p class="style-94325">âš  IMPORTANT NOTICE</p>
        <p class="style-17836">If payment is not received within <strong>15 days</strong>, APS Dream Home reserves the right to:<br>
        1. Cancel your booking and retain amounts paid<br>
        2. Initiate legal proceedings for recovery<br>
        3. Report the default to credit bureaus<br>
        4. Realize the plot and resell it</p>
    </div>

    <p class="style-10698">Please settle all dues immediately to avoid these consequences.</p>
    <p class="style-10698">Contact us: <strong class="style-22019">{{company_phone}}</strong> | <strong class="style-22019">finance@apsdreamhome.com</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
