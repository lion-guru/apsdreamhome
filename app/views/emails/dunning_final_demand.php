<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home — Final Demand Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body >
<div >
    <h1 >APS Dream Home</h1>
    <p >Final Demand Notice</p>
</div>
<div >
    <p >Dear <strong>{{customer_name}}</strong>,</p>
    <p >This is a <strong>final demand</strong> for your overdue EMI payment. Your account is now <strong >{{days_overdue}} days past due</strong> across {{overdue_count}} installment(s).</p>
    
    <div >
        <table >
            <tr><td >Booking:</td><td >{{booking_number}}</td></tr>
            <tr><td >Total Overdue Amount:</td><td >₹{{total_overdue}}</td></tr>
            <tr><td >Penalty Accrued:</td><td >₹{{total_penalty}}</td></tr>
            <tr><td >Worst Overdue:</td><td >{{days_overdue}} days</td></tr>
        </table>
    </div>

    <div >
        <p >âš  IMPORTANT NOTICE</p>
        <p >If payment is not received within <strong>15 days</strong>, APS Dream Home reserves the right to:<br>
        1. Cancel your booking and retain amounts paid<br>
        2. Initiate legal proceedings for recovery<br>
        3. Report the default to credit bureaus<br>
        4. Realize the plot and resell it</p>
    </div>

    <p >Please settle all dues immediately to avoid these consequences.</p>
    <p >Contact us: <strong >{{company_phone}}</strong> | <strong >finance@apsdreamhome.com</strong></p>
</div>
<div >
    <p >&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
