<?php
$p = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$tables = ['chat_sessions','chat_messages','chat_queue','chat_transfers','agent_availability','message_queue','tasks','support_tickets','support_ticket_replies','push_subscriptions','push_notifications','notifications','realtime_notifications','notification_settings','notification_templates','sms_templates','email_tracking','whatsapp_messages','whatsapp_lead_shares'];
foreach ($tables as $t) {
    try {
        $s = $p->query("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'");
        echo $t . ': ' . ($s->rowCount() > 0 ? 'HAS tenant_id' : 'NO tenant_id') . PHP_EOL;
    } catch(Exception $e) {
        echo $t . ': TABLE_NOT_FOUND (' . $e->getMessage() . ')' . PHP_EOL;
    }
}?>