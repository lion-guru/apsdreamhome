<?php
// Database check script
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo '=== EXISTING DATABASE TABLES ===' . PHP_EOL;
    foreach ($tables as $table) {
        echo '- ' . $table . PHP_EOL;
    }
    echo PHP_EOL . 'Total tables: ' . count($tables) . PHP_EOL;

    // Check for our custom tables
    $ourTables = [
        'employee_documents', 'document_categories', 'document_types', 'document_sharing', 'document_audit_log',
        'employee_shifts', 'shift_types', 'shift_schedules', 'shift_assignments', 'shift_swap_requests', 'time_off_requests',
        'sales_data', 'market_trends', 'predictive_models', 'forecast_results', 'seasonality_patterns', 'analytics_cache',
        'gst_settings', 'hsn_sac_codes', 'gst_invoice_details', 'gst_returns', 'tax_ledgers',
        'invoice_templates', 'invoices', 'invoice_items', 'invoice_payments', 'invoice_reminders', 'recurring_invoices',
        'ocr_documents', 'ocr_templates', 'ocr_extracted_fields', 'ocr_processing_queue', 'document_classification',
        'user_property_preferences', 'property_ratings', 'user_browsing_history', 'recommendation_settings', 'property_similarity', 'user_similarity', 'recommendation_cache'
    ];

    echo PHP_EOL . '=== OUR CUSTOM TABLES STATUS ===' . PHP_EOL;
    $createdTables = 0;
    foreach ($ourTables as $table) {
        if (in_array($table, $tables)) {
            echo '✓ ' . $table . PHP_EOL;
            $createdTables++;
        } else {
            echo '✗ ' . $table . PHP_EOL;
        }
    }
    echo PHP_EOL . 'Created tables: ' . $createdTables . '/' . count($ourTables) . PHP_EOL;

} catch (Exception $e) {
    echo 'Database connection error: ' . $e->getMessage() . PHP_EOL;
}
?>
