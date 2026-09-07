<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home — Booking Defaulted</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body >
<div >
    <h1 >APS Dream Home</h1>
    <p >Booking Defaulted — Action Required</p>
</div>
<div >
    <p >Dear <strong>{{customer_name}}</strong>,</p>
    <p >We regret to inform you that your EMI plan for <strong>{{booking_number}}</strong> has been <strong >DEFAULTED</strong> due to non-payment of {{overdue_count}} consecutive installments.</p>
    
    <div >
        <table >
            <tr><td >Plot:</td><td >{{plot_number}}, {{colony_name}}</td></tr>
            <tr><td >Total Overdue Amount:</td><td >₹{{total_overdue}}</td></tr>
            <tr><td >Total Penalty Accrued:</td><td >₹{{total_penalty}}</td></tr>
            <tr><td >Outstanding Installments:</td><td >{{overdue_count}}</td></tr>
        </table>
    </div>

    <div >
        <p >CONSEQUENCES OF DEFAULT</p>
        <p >As per the terms of your booking agreement, default may result in:<br>
        1. Cancellation of your booking<br>
        2. Forfeiture of amounts already paid<br>
        3. Legal proceedings for recovery of outstanding dues<br>
        4. The plot being re-sold to recover losses</p>
    </div>

    <p >You are requested to clear all outstanding dues within <strong>15 days</strong> to avoid cancellation of your booking.</p>
    <p >Contact us: <strong >{{company_phone}}</strong> | <strong >finance@apsdreamhome.com</strong></p>
</div>
<div >
    <p >&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
