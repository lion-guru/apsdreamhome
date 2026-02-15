<?php
/**
 * Property Booking/Sale Form with MLM Commission Integration
 * This form handles property sales and automatically distributes MLM commissions
 */

require_once __DIR__ . '/init.php';
require_once 'includes/functions/property_sales_mlm_integration.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id === 0) {
    header('Location: properties.php');
    exit();
}

$db = \App\Core\App::database();

// Get property details
$property = $db->fetch("SELECT * FROM properties WHERE id = ?", [$property_id]);

if (!$property) {
    header('Location: properties.php');
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $sale_amount = floatval($_POST['sale_amount']);
    $buyer_id = $_SESSION['user_id'];
    $agent_id = isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;

    // Process the sale with MLM commissions
    $result = processPropertySaleMLMCommissions($property_id, $buyer_id, $sale_amount, $agent_id);

    if ($result['success']) {
        // Redirect to success page
        header('Location: property_sale_success.php?id=' . $property_id);
        exit();
    } else {
        $error_message = $result['message'];
    }
}

// Get available agents for dropdown
$agents = $db->query("SELECT id, name, email FROM users WHERE type = 'agent' AND status = 'active'")->fetchAll(\PDO::FETCH_ASSOC);

// Check if user is MLM associate
$associate = $db->fetch("SELECT * FROM associates WHERE user_id = ?", [$_SESSION['user_id']]);
$is_associate = $associate ? true : false;

$page_title = "Property Purchase - " . h($property['title']);
include 'includes/templates/header.php';
?>

<style>
.property-purchase-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.property-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.property-image {
    height: 300px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.property-details {
    padding: 2rem;
}

.property-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 1rem;
}

.property-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-label {
    font-weight: 600;
    color: #666;
}

.info-value {
    color: #333;
}

.booking-form {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.commission-info {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.commission-info h4 {
    margin-bottom: 1rem;
    font-weight: 600;
}

.commission-benefits {
    list-style: none;
    padding: 0;
}

.commission-benefits li {
    padding: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.commission-benefits li::before {
    content: "✓";
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
}

.btn-purchase {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    width: 100%;
}

.btn-purchase:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.alert {
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.price-display {
    font-size: 2.5rem;
    font-weight: 700;
    color: #28a745;
    margin: 1rem 0;
}

.commission-display {
    font-size: 1.2rem;
    color: #666;
}

@media (max-width: 768px) {
    .property-purchase-container {
        margin: 1rem auto;
    }

    .property-title {
        font-size: 1.5rem;
    }

    .price-display {
        font-size: 2rem;
    }
}
</style>

<div class="property-purchase-container">
    <!-- Property Details Card -->
    <div class="property-card">
        <div class="property-image">
            🏠
        </div>
        <div class="property-details">
            <h1 class="property-title"><?php echo h($property['title']); ?></h1>

            <div class="property-info">
                <div class="info-item">
                    <span class="info-label">Type:</span>
                    <span class="info-value"><?php echo h($property['property_type'] ?? 'Residential'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location:</span>
                    <span class="info-value"><?php echo h($property['location'] ?? 'Gorakhpur'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><?php echo h($property['status'] ?? 'Available'); ?></span>
                </div>
            </div>

            <div class="price-display">
                ₹<?php echo number_format($property['price'] ?? 5000000, 0); ?>
            </div>

            <div class="commission-display">
                Estimated Commission: ₹<?php echo number_format(($property['price'] ?? 5000000) * 0.07, 0); ?> (7%)
            </div>
        </div>
    </div>

    <!-- MLM Commission Info (for associates) -->
    <?php if ($is_associate): ?>
    <div class="commission-info">
        <h4>🎯 MLM Commission Benefits</h4>
        <p>As an MLM Associate, you'll earn commissions on this purchase:</p>
        <ul class="commission-benefits">
            <li>7% commission distributed to your upline</li>
            <li>Your sponsor network earns from your purchase</li>
            <li>Build your team to earn from their purchases</li>
            <li>Track commissions in your MLM dashboard</li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Booking Form -->
    <div class="booking-form">
        <h2>Complete Your Purchase</h2>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo h($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

            <div class="form-group">
                <label class="form-label">Property</label>
                <input type="text" class="form-control" value="<?php echo h($property['title']); ?>" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Purchase Amount (₹)</label>
                <input type="number" name="sale_amount" class="form-control"
                       value="<?php echo h($property['price'] ?? 5000000); ?>"
                       min="100000" step="10000" required>
                <small>Enter the final purchase amount</small>
            </div>

            <div class="form-group">
                <label class="form-label">Your Information</label>
                <input type="text" class="form-control"
                       value="<?php echo h($_SESSION['user_name'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Your Email</label>
                <input type="email" class="form-control"
                       value="<?php echo h($_SESSION['user_email'] ?? ''); ?>" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Preferred Agent (Optional)</label>
                <select name="agent_id" class="form-control">
                    <option value="">Select an agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>">
                            <?php echo h($agent['name'] . ' - ' . $agent['email']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Choose an agent to assist with your purchase</small>
            </div>

            <div class="form-group">
                <label class="form-label">Special Requirements</label>
                <textarea name="requirements" class="form-control" rows="4"
                          placeholder="Any special requirements or notes for this purchase..."></textarea>
            </div>

            <?php if ($is_associate): ?>
            <div class="alert alert-info">
                <strong>MLM Associate Notice:</strong> This purchase will generate commissions for your upline network. You can earn commissions by referring others to make property purchases.
            </div>
            <?php endif; ?>

            <button type="submit" name="submit_booking" class="btn-purchase">
                Complete Purchase 🏠
            </button>
        </form>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>
