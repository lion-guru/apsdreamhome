<?php
/**
 * Generic UPI Pay link page — linked from the customer wallet.
 * Renders the pay QR + deep link without binding to a specific installment.
 * For per-installment payment (EMI QR), use /user/installments flow instead.
 */
$vpa = 'apsdreamhome@icici';
$uri = 'upi://pay?pa=' . urlencode($vpa) . '&pn=' . urlencode('APS Dream Home') . '&cu=INR';
$qr  = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . urlencode($uri);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pay via UPI — APS Dream Home</title>
<link rel="icon" href="<?= BASE_URL ?>/assets/images/favicon.png">
<style>
    body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; background: #0f172a; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0; color: #fff; }
    .card { background: #1e293b; border-radius: 16px; padding: 2rem; text-align: center; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,.5); }
    h1 { margin-top: 0; font-size: 1.4rem; }
    p { color: #cbd5e1; font-size: .95rem; line-height: 1.5; }
    code { background: #0f172a; color: #fbbf24; padding: .35rem .6rem; border-radius: 8px; display: inline-block; margin: .5rem 0; font-size: .9rem; }
    img { background: #fff; border-radius: 12px; padding: .5rem; margin: .75rem 0; }
    .btn { display: inline-block; margin-top: .6rem; padding: .75rem 1.4rem; background: #0d9488; color: #fff; border-radius: 10px; text-decoration: none; font-weight: 700; }
    .back { color: #94a3b8; font-size: .8rem; margin-top: .8rem; }
</style>
</head>
<body>
    <div class="card">
        <h1>Pay APS Dream Home via UPI</h1>
        <p>Scan with any UPI app (GPay, PhonePe, Paytm, BHIM) to pay instantly.</p>
        <img src="<?= htmlspecialchars($qr, ENT_QUOTES) ?>" alt="UPI QR for <?= htmlspecialchars($vpa) ?>" width="260" height="260">
        <div>
            <code><?= htmlspecialchars($vpa) ?></code>
        </div>
        <a class="btn" href="<?= htmlspecialchars($uri, ENT_QUOTES) ?>">Pay with UPI App</a>
        <div class="back"><a href="<?= BASE_URL ?>/user/wallet" style="color:#94a3b8;">&larr; Back to Wallet</a></div>
    </div>
</body>
</html>
