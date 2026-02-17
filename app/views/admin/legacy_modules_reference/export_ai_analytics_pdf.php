<?php
/**
 * Export filtered AI analytics summary as PDF - Secured version
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/SecurityUtility.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

// Access control
if (!SecurityUtility::hasRole(['admin', 'superadmin'])) {
    header('Location: ../index.php');
    exit();
}

// Get filters from GET params and sanitize
$from = SecurityUtility::sanitizeInput($_GET['from'] ?? date('Y-m-d', strtotime('-30 days')), 'string');
$to = SecurityUtility::sanitizeInput($_GET['to'] ?? date('Y-m-d'), 'string');
$role_filter = isset($_GET['role']) && $_GET['role'] !== '' ? SecurityUtility::sanitizeInput($_GET['role'], 'string') : null;

// Validate date formats to prevent injection
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');

// Fetch summary stats
$summary = ['total' => 0, 'likes' => 0, 'dislikes' => 0, 'views' => 0];

$sql_summary = "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE created_at >= ? AND created_at <= ? ";
$params = [$from . ' 00:00:00', $to . ' 23:59:59'];
$types = "ss";

if ($role_filter) {
    $sql_summary .= " AND role = ?";
    $params[] = $role_filter;
    $types .= "s";
}
$sql_summary .= " GROUP BY action";

$db = \App\Core\App::database();
$res_summary = $db->fetchAll($sql_summary, $params);

foreach ($res_summary as $row) {
    if ($row['action'] === 'like') $summary['likes'] = $row['cnt'];
    if ($row['action'] === 'dislike') $summary['dislikes'] = $row['cnt'];
    if ($row['action'] === 'view') $summary['views'] = $row['cnt'];
    $summary['total'] += $row['cnt'];
}

// Fetch top 5 suggestions
$top = [];
$sql_top = "SELECT suggestion_text, SUM(action='like') AS likes, SUM(action='dislike') AS dislikes 
            FROM ai_interactions 
            WHERE created_at >= ? AND created_at <= ? ";
if ($role_filter) {
    $sql_top .= " AND role = ?";
}
$sql_top .= " GROUP BY suggestion_text ORDER BY likes DESC, dislikes DESC LIMIT 5";

$res_top = $db->fetchAll($sql_top, $params);

foreach ($res_top as $row) {
    $top[] = $row;
}

// --- Generate PDF using TCPDF ---
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, 'APS Dream Homes - AI Analytics Summary');
$pdf->Ln(10);

// Summary Table
$html = '<h3>Summary</h3><table border="1" cellpadding="4"><tr><th>Type</th><th>Count</th></tr>';
foreach ($summary as $k => $v) {
    $html .= '<tr><td>' . ucfirst($k) . '</td><td>' . $v . '</td></tr>';
}
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Top Suggestions Table
$html = '<h3>Top 5 Suggestions</h3><table border="1" cellpadding="4"><tr><th>Suggestion</th><th>Likes</th><th>Dislikes</th></tr>';
foreach ($top as $row) {
    $html .= '<tr><td>' . h($row['suggestion_text']) . '</td><td>' . $row['likes'] . '</td><td>' . $row['dislikes'] . '</td></tr>';
}
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('ai_analytics_summary.pdf', 'I');
exit;
?>
