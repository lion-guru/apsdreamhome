<?php
chdir(dirname(__DIR__));
$d = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome','root','');
$tables = ['chat_sessions','chat_messages','chat_quick_replies','chat_widget_settings','ocr_documents','ocr_templates','ocr_extracted_fields','document_classification','tenants','job_batches','queue_jobs','queue_workers','batch_jobs'];
foreach ($tables as $t) {
    try {
        $cols = $d->query("SHOW COLUMNS FROM $t")->fetchAll(PDO::FETCH_COLUMN);
        echo "$t: " . (in_array('tenant_id', $cols) ? 'HAS tenant_id' : 'NO tenant_id') . "\n";
    } catch (Exception $e) {
        echo "$t: NOT FOUND\n";
    }
}
