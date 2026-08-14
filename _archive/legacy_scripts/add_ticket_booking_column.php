<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    
    // Add booking_id to support_tickets
    try {
        $db->exec("ALTER TABLE support_tickets ADD COLUMN booking_id bigint(20) unsigned DEFAULT NULL AFTER assigned_to");
        echo "Added booking_id to support_tickets\n";
    } catch (PDOException $e) {
        echo "booking_id already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Create support_ticket_replies table
    $db->exec("CREATE TABLE IF NOT EXISTS support_ticket_replies (
        id int(11) NOT NULL AUTO_INCREMENT,
        ticket_id int(11) NOT NULL,
        user_id bigint(20) unsigned DEFAULT NULL,
        message text NOT NULL,
        is_admin tinyint(1) DEFAULT 0,
        created_at timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (id),
        KEY idx_ticket_id (ticket_id),
        KEY idx_ticket_replies_user (user_id),
        CONSTRAINT fk_ticket_replies_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    echo "Created support_ticket_replies table\n";
    
    echo "Done.\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}?>