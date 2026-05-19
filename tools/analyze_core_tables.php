<?php
/**
 * Project Core Analysis
 * APS Dream Home - Real Estate ERP System
 */

require 'config/bootstrap.php';
$db = App\Core\Database\Database::getInstance();

// 1. Check all tables related to core business
$tables = $db->query("SHOW TABLES")->fetchAll();
echo "=== ALL DATABASE TABLES (" . count($tables) . ") ===\n\n";

$categories = [
    'colony' => [],
    'plot' => [],
    'customer' => [],
    'mlm' => [],
    'finance' => [],
    'employee' => [],
    'lead' => [],
    'other' => []
];

foreach ($tables as $t) {
    $tbl = array_values($t)[0];
    $lower = strtolower($tbl);
    
    if (strpos($lower, 'colony') !== false || strpos($lower, 'site') !== false || strpos($lower, 'project') !== false) {
        $categories['colony'][] = $tbl;
    } elseif (strpos($lower, 'plot') !== false) {
        $categories['plot'][] = $tbl;
    } elseif (strpos($lower, 'customer') !== false || strpos($lower, 'user') !== false || strpos($lower, 'member') !== false) {
        $categories['customer'][] = $tbl;
    } elseif (strpos($lower, 'mlm') !== false || strpos($lower, 'associate') !== false || strpos($lower, 'network') !== false || strpos($lower, 'commission') !== false || strpos($lower, 'payout') !== false) {
        $categories['mlm'][] = $tbl;
    } elseif (strpos($lower, 'payment') !== false || strpos($lower, 'invoice') !== false || strpos($lower, 'transaction') !== false || strpos($lower, 'expense') !== false || strpos($lower, 'emi') !== false || strpos($lower, 'account') !== false) {
        $categories['finance'][] = $tbl;
    } elseif (strpos($lower, 'employee') !== false || strpos($lower, 'salary') !== false || strpos($lower, 'attendance') !== false || strpos($lower, 'leave') !== false || strpos($lower, 'hrm') !== false || strpos($lower, 'payroll') !== false) {
        $categories['employee'][] = $tbl;
    } elseif (strpos($lower, 'lead') !== false || strpos($lower, 'inquiry') !== false || strpos($lower, 'enquiry') !== false) {
        $categories['lead'][] = $tbl;
    } else {
        $categories['other'][] = $tbl;
    }
}

foreach ($categories as $cat => $items) {
    if (!empty($items)) {
        echo "=== " . strtoupper($cat) . " (" . count($items) . ") ===\n";
        foreach ($items as $i) echo "  - $i\n";
        echo "\n";
    }
}