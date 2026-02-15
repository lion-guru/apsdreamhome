<?php include 'includes/db.php'; ?>
<?php include 'includes/header.php'; ?>

<?php require_permission('process_payout'); ?>

<h2>Payouts</h2>

<form method="POST" action="">
    <input type="number" name="payout_amount" placeholder="Payout Amount" required>
    <select name="user_id">
        <?php
        $stmt = $pdo->query("SELECT id, name FROM users");
        while ($row = $stmt->fetch()) {
            echo "<option value='{$row['id']}'>{$row['name']}</option>";
        }
        ?>
    </select>
    <button type="submit">Add Payout</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payout_amount = $_POST['payout_amount'];
    $user_id = $_POST['user_id'];

    $stmt = $pdo->prepare("INSERT INTO payouts (user_id, payout_amount) VALUES (?, ?)");
    $stmt->execute([$user_id, $payout_amount]);
    require_once __DIR__ . '/../includes/functions/notification_util.php';
    addNotification($pdo, 'Payout', 'Payout processed for an associate.', $_SESSION['auser'] ?? null);
    echo "Payout added successfully!";
}
?>

<h3>Payout List</h3>
<table>
    <tr>
        <th>ID</th>
        <th>User</th>
        <th>Payout Amount</th>
        <th>Date</th>
    </tr>
    <?php
    $stmt = $pdo->query("SELECT payouts.id, users.name, payouts.payout_amount, payouts.payout_date FROM payouts JOIN users ON payouts.user_id = users.id");
    while ($row = $stmt->fetch()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td>{$row['payout_amount']}</td>
                <td>{$row['payout_date']}</td>
              </tr>";
    }
    ?>
</table>

<?php include 'includes/footer.php'; ?>
