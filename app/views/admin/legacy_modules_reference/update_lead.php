<?php
require_once 'core/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lead_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_msg'] = 'Invalid CSRF token!';
        header('Location: leads.php');
        exit();
    }

    require_once __DIR__ . '/../includes/functions/lead_functions.php';

    $db = \App\Core\App::database();
    $lead_id = (int)$_POST['lead_id'];
    
    // Fetch old status and assignment for activity logging
    $old_lead = $db->fetchOne("SELECT status, assigned_to FROM leads WHERE id = :id", ['id' => $lead_id]);
    $old_status = $old_lead['status'] ?? '';
    $old_assigned_to = $old_lead['assigned_to'] ?? null;

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;

    $sql = "UPDATE leads SET 
                name = :name,
                email = :email,
                phone = :phone,
                address = :address,
                source = :source,
                status = :status,
                notes = :notes,
                assigned_to = :assigned_to
              WHERE id = :id";

    $params = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'source' => $source,
        'status' => $status,
        'notes' => $notes,
        'assigned_to' => $assigned_to,
        'id' => $lead_id
    ];

    if ($db->execute($sql, $params)) {
        // Log status change if it changed
        if ($old_status !== $status) {
            logLeadActivity($lead_id, 'status_change', "Status updated from $old_status to $status", $old_status, $status, 'Lead Status Updated');
        }
        
        // Log assignment change if it changed
        if ($old_assigned_to != $assigned_to) {
            $old_admin_name = 'Unassigned';
            $new_admin_name = 'Unassigned';
            
            if ($old_assigned_to) {
                $adm_res = $db->fetchOne("SELECT auser FROM admin WHERE id = :id", ['id' => $old_assigned_to]);
                $old_admin_name = $adm_res['auser'] ?? 'Unknown';
            }
            
            if ($assigned_to) {
                $adm_res = $db->fetchOne("SELECT auser FROM admin WHERE id = :id", ['id' => $assigned_to]);
                $new_admin_name = $adm_res['auser'] ?? 'Unknown';
                
                // Add notification for the new assignee
                require_once __DIR__ . '/../includes/notification_manager.php';
                $nm = new NotificationManager($db);
                $nm->send([
                    'user_id' => $assigned_to,
                    'template' => 'LEAD_ASSIGNED',
                    'data' => [
                        'name' => $name
                    ],
                    'channels' => ['db', 'email']
                ]);
            }
            
            logLeadActivity($lead_id, 'lead_assigned', "Lead reassigned from $old_admin_name to $new_admin_name", $old_admin_name, $new_admin_name, 'Lead Assignment Changed');
        }

        if ($old_status === $status && $old_assigned_to == $assigned_to) {
            logLeadActivity($lead_id, 'info_update', "Lead information updated", null, null, 'Lead Details Updated');
        }
        $_SESSION['success_msg'] = 'Lead updated successfully!';
    } else {
        $_SESSION['error_msg'] = 'Error updating lead.';
    }
} else {
    $_SESSION['error_msg'] = 'Invalid request!';
}

header('Location: leads.php');
exit();
?>

