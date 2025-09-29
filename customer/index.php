<?php
session_start();
include '../config.php';
include '../includes/base_template.php';

if (!isset($_SESSION['customer_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

$customer_id = $_SESSION['customer_id'];

// Fetch bookings using prepared statement
$stmt = $conn->prepare("SELECT * FROM bookings WHERE customer_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();

// Fetch documents using prepared statement
$stmt = $conn->prepare("SELECT * FROM customer_documents WHERE customer_id=? ORDER BY uploaded_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$docs = $stmt->get_result();
$stmt->close();

// Fetch payments using prepared statement
$stmt = $conn->prepare("SELECT * FROM payments WHERE customer_id=? ORDER BY paid_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$payments = $stmt->get_result();
$stmt->close();

// Prepare content
ob_start();
?>
<div class='customer-dashboard'>
    <h2 class='mb-4'>Welcome to Your Customer Portal</h2>
    
    <div class='row'>
        <div class='col-md-4'>
            <div class='card mb-4'>
                <div class='card-header'>Your Bookings</div>
                <div class='card-body'>
                    <table class='table'>
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($b = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['property_name']) ?></td>
                                <td><?= htmlspecialchars($b['status']) ?></td>
                                <td><?= $b['created_at'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class='col-md-4'>
            <div class='card mb-4'>
                <div class='card-header'>Your Documents</div>
                <div class='card-body'>
                    <table class='table'>
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($d = $docs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['doc_name']) ?></td>
                                <td><?= htmlspecialchars($d['status']) ?></td>
                                <td><?= $d['uploaded_at'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class='col-md-4'>
            <div class='card mb-4'>
                <div class='card-header'>Your Payments</div>
                <div class='card-body'>
                    <table class='table'>
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($p = $payments->fetch_assoc()): ?>
                            <tr>
                                <td>₹<?= number_format($p['amount'],2) ?></td>
                                <td><?= htmlspecialchars($p['status']) ?></td>
                                <td><?= $p['paid_at'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add interactive features
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        table.addEventListener('mouseover', function(e) {
            if (e.target.closest('tr')) {
                e.target.closest('tr').classList.add('table-active');
            }
        });
        table.addEventListener('mouseout', function(e) {
            if (e.target.closest('tr')) {
                e.target.closest('tr').classList.remove('table-active');
            }
        });
    });
});
</script>
<?php 
$content = ob_get_clean();
render_base_template('Customer Dashboard', $content, ['modern-ui.css'], ['performance-optimizer.js']);
?>
