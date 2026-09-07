<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home — Payment Overdue Notice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body >
<div >
    <h1 >APS Dream Home</h1>
    <p >Payment Overdue — Immediate Action Required</p>
</div>
<div >
    <p >Dear <strong>{{customer_name}}</strong>,</p>
    <p >Your EMI installment is now <strong >{{days_overdue}} days overdue</strong>. Immediate payment is required to avoid further penalties and potential action on your booking.</p>
    
    <div >
        <table >
            <tr><td >Booking:</td><td >{{booking_number}}</td></tr>
            <tr><td >Installment:</td><td >#{{installment_no}}</td></tr>
            <tr><td >Original Amount:</td><td >₹{{amount}}</td></tr>
            <tr><td >Penalty Accrued:</td><td >₹{{penalty}}</td></tr>
            <tr><td >Total Due Now:</td><td >₹{{total_due}}</td></tr>
        </table>
    </div>

    <p >Late payment charges accrue at <strong>18% per annum</strong> on the overdue amount. Please pay immediately to avoid further escalation.</p>
    <p >Pay online: <a href="{{payment_url}}" >Click Here to Pay</a></p>
    <p >For queries, call <strong >{{company_phone}}</strong></p>
</div>
<div >
    <p >&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
