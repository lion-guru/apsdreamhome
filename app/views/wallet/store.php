<?php
$page_title = $page_title ?? 'Wallet Store - APS Dream Home';
$base = BASE_URL ?? ('/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? ''); ?></title>
    <link rel="icon" type="image/png" href="<?= $base ?>/app/views/admin/assets/img/favicon.png">
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .page-content { padding: 28px; }
        .store-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden; }
        .store-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-color: #15803d; }
        .store-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 16px; }
        .store-icon.blue { background: #dbeafe; color: #2563eb; }
        .store-icon.green { background: #dcfce7; color: #15803d; }
        .store-icon.purple { background: #f3e8ff; color: #9333ea; }
        .store-icon.orange { background: #ffedd5; color: #ea580c; }
        .store-price { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 16px 0; }
        .store-title { font-size: 1.1rem; font-weight: 600; color: #334155; margin-bottom: 8px; }
        .store-desc { color: #64748b; font-size: 0.9rem; line-height: 1.5; min-height: 60px; }
        .btn-buy { background: #15803d; color: #fff; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; width: 100%; transition: background 0.2s; }
        .btn-buy:hover { background: #14532d; color: #fff; }
        .wallet-banner { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 16px; padding: 24px; margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; }
        .wallet-balance { font-size: 2rem; font-weight: 700; color: #22c55e; }
    </style>
</head>
<body>
    <div class="container py-4">
        <a href="<?php echo $base; ?>/agent/dashboard" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
        
        <div class="wallet-banner shadow-sm">
            <div>
                <h4 class="mb-1">Wallet E-Commerce Store</h4>
                <p class="mb-0 text-white-50">Purchase leads, marketing materials, and activate your account using your wallet balance.</p>
            </div>
            <div class="text-end">
                <span class="d-block text-white-50 small mb-1">Available Balance</span>
                <span class="wallet-balance">₹<?php echo number_format($wallet['balance'] ?? 0, 2); ?></span>
            </div>
        </div>

        <div id="alert-container"></div>

        <div class="row g-4">
            <?php foreach ($items as $index => $item): 
                $colors = ['blue', 'green', 'purple', 'orange'];
                $color = $colors[$index % 4];
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="store-card">
                    <div class="store-icon <?php echo $color; ?>">
                        <i class="<?php echo $item['icon']; ?>"></i>
                    </div>
                    <h3 class="store-title"><?php echo htmlspecialchars($item['name'] ?? ''); ?></h3>
                    <p class="store-desc"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
                    <div class="store-price">₹<?php echo number_format($item['price']); ?></div>
                    <button class="btn-buy" onclick="purchaseItem('<?php echo $item['id']; ?>', '<?php echo htmlspecialchars($item['name'] ?? ''); ?>', <?php echo $item['price']; ?>)">
                        Purchase Now
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Purchase Confirmation Modal -->
    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">Confirm Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                        <h4 id="modalItemName" class="mb-2">Item Name</h4>
                        <p class="text-muted mb-0">You are about to purchase this item using your wallet balance.</p>
                    </div>
                    
                    <div class="bg-light rounded p-3 mb-4 text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current Balance:</span>
                            <span class="fw-bold">₹<?php echo number_format($wallet['balance'] ?? 0, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Item Cost:</span>
                            <span class="fw-bold text-danger">-₹<span id="modalItemPrice">0</span></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Remaining Balance:</span>
                            <span class="fw-bold text-success" id="modalNewBalance">₹0</span>
                        </div>
                    </div>

                    <form id="purchaseForm" onsubmit="submitPurchase(event)">
                        <input type="hidden" name="item_id" id="modalItemId">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-50" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary w-50" id="btnConfirmPurchase">Confirm Purchase</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentBalance = <?php echo floatval($wallet['balance'] ?? 0); ?>;
        const purchaseModal = new bootstrap.Modal(document.getElementById('purchaseModal'));
        
        function purchaseItem(id, name, price) {
            document.getElementById('modalItemId').value = id;
            document.getElementById('modalItemName').textContent = name;
            document.getElementById('modalItemPrice').textContent = price.toLocaleString('en-IN');
            
            const newBalance = currentBalance - price;
            const newBalanceEl = document.getElementById('modalNewBalance');
            
            if (newBalance < 0) {
                newBalanceEl.textContent = 'Insufficient Balance';
                newBalanceEl.className = 'fw-bold text-danger';
                document.getElementById('btnConfirmPurchase').disabled = true;
            } else {
                newBalanceEl.textContent = '₹' + newBalance.toLocaleString('en-IN', {minimumFractionDigits: 2});
                newBalanceEl.className = 'fw-bold text-success';
                document.getElementById('btnConfirmPurchase').disabled = false;
            }
            
            purchaseModal.show();
        }

        async function submitPurchase(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnConfirmPurchase');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            btn.disabled = true;

            const formData = new FormData(e.target);

            try {
                const response = await fetch('<?php echo $base; ?>/wallet/process-purchase', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                purchaseModal.hide();

                const alertHtml = `
                    <div class="alert alert-${result.success ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                        <i class="fas fa-${result.success ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${result.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                document.getElementById('alert-container').innerHTML = alertHtml;

                if (result.success) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }

            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
