<?php
$c = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = [
    'farmers','documents','land_allocations','land_plots','farmer_activities',
    'farmer_commissions','farmer_commission_structures',
    'ai_calling_schedule','leads','ai_call_sessions','ai_calling_agents',
    'ai_call_extracted_leads','lead_activities',
    'mlm_commission_ledger','mlm_payout_batches','mlm_payout_batch_items',
    'mlm_payout_batch_approvals'
];
foreach ($tables as $t) {
    try {
        $s = $c->query("SHOW COLUMNS FROM `$t` LIKE 'tenant_id'");
        $r = $s->fetch(PDO::FETCH_ASSOC);
        echo $t . ': ' . ($r ? 'HAS tenant_id (' . $r['Type'] . ')' : 'NO tenant_id') . "\n";
    } catch (Exception $e) {
        echo $t . ': TABLE NOT FOUND - ' . $e->getMessage() . "\n";
    }
}?>