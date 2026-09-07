<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home — Payment Reminder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><meta charset="utf-8"></head>
<body >
<div >
    <h1 >APS Dream Home</h1>
    <p >Payment Reminder</p>
</div>
<div >
    <p >Dear <strong>{{customer_name}}</strong>,</p>
    <p >This is a friendly reminder that your upcoming EMI installment is due soon.</p>
    
    <div >
        <p >Amount Due</p>
        <p >₹{{amount}}</p>
    </div>

    <table >
        <tr><td >Booking Number</td><td >{{booking_number}}</td></tr>
        <tr><td >Installment #</td><td >{{installment_no}}</td></tr>
        <tr><td >Due Date</td><td >{{due_date}}</td></tr>
        <tr><td >Plot</td><td >{{plot_number}}, {{colony_name}}</td></tr>
    </table>

    <p >Please ensure timely payment to avoid late fees. You can pay online via the customer portal or visit our office.</p>
    <p >For any queries, call us at <strong >{{company_phone}}</strong></p>
</div>
<div >
    <p >&copy; APS Dream Home. All rights reserved.</p>
</div>
</body>
</html>
