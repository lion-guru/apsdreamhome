<?php
require_once __DIR__ . '/lead_scoring.php';

$scoring = new LeadScoringSystem();
$result = $scoring->calculateLeadScore(1);

echo json_encode($result, JSON_PRETTY_PRINT);
?>