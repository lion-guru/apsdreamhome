<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body class="style-26942">
<div class="style-10137">
    <h1 class="style-85930">APS Dream Home</h1>
    <p class="style-8849">Booking Defaulted — Action Required</p>
</div>
<div class="style-38030">
    <p class="style-33752">Dear <strong>{{customer_name}}</strong>,</p>
    <p class="style-10698">We regret to inform you that your EMI plan for <strong>{{booking_number}}</strong> has been <strong class="style-31031">DEFAULTED</strong> due to non-payment of {{overdue_count}} consecutive installments.</p>
    
    <div class="style-20329">
        <table class="style-61075">
            <tr><td class="style-20694">Plot:</td><td class="style-60925">{{plot_number}}, {{colony_name}}</td></tr>
            <tr><td class="style-20694">Total Overdue Amount:</td><td class="style-12547">₹{{total_overdue}}</td></tr>
            <tr><td class="style-20694">Total Penalty Accrued:</td><td class="style-15627">₹{{total_penalty}}</td></tr>
            <tr><td class="style-20694">Outstanding Installments:</td><td class="style-42101">{{overdue_count}}</td></tr>
        </table>
    </div>

    <div class="style-16633">
        <p class="style-94325">CONSEQUENCES OF DEFAULT</p>
        <p class="style-17836">As per the terms of your booking agreement, default may result in:<br>
        1. Cancellation of your booking<br>
        2. Forfeiture of amounts already paid<br>
        3. Legal proceedings for recovery of outstanding dues<br>
        4. The plot being re-sold to recover losses</p>
    </div>

    <p class="style-10698">You are requested to clear all outstanding dues within <strong>15 days</strong> to avoid cancellation of your booking.</p>
    <p class="style-10698">Contact us: <strong class="style-22019">{{company_phone}}</strong> | <strong class="style-22019">finance@apsdreamhome.com</strong></p>
</div>
<div class="style-1322">
    <p class="style-85082">&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
