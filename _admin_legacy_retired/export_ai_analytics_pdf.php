<?php
// Export filtered AI analytics summary as PDF (scaffold)
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit();
}
require_once(__DIR__ . '/../src/Database/Database.php');
require_once(__DIR__ . '/../includes/classes/tcpdf/tcpdf.php');

// Get filters from GET params
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$to = $_GET['to'] ?? date('Y-m-d');
$role_filter = isset($_GET['role']) && $_GET['role'] ? $_GET['role'] : null;
$date_sql = "created_at >= '$from 00:00:00' AND created_at <= '$to 23:59:59'";
$role_sql = $role_filter ? "AND role='".mysqli_real_escape_string($con, $role_filter)."'" : '';

// Fetch summary stats
$db = new Database();
$con = $db->getConnection();
$summary = [ 'total' => 0, 'likes' => 0, 'dislikes' => 0, 'views' => 0 ];
$res = mysqli_query($con, "SELECT action, COUNT(*) as cnt FROM ai_interactions WHERE $date_sql $role_sql GROUP BY action");
while($row = mysqli_fetch_assoc($res)) {
    if ($row['action'] === 'like') $summary['likes'] = $row['cnt'];
    if ($row['action'] === 'dislike') $summary['dislikes'] = $row['cnt'];
    if ($row['action'] === 'view') $summary['views'] = $row['cnt'];
    $summary['total'] += $row['cnt'];
}

// Fetch top 5 suggestions
$top = [];
$res = mysqli_query($con, "SELECT suggestion_text, SUM(action='like') AS likes, SUM(action='dislike') AS dislikes FROM ai_interactions WHERE $date_sql $role_sql GROUP BY suggestion_text ORDER BY likes DESC, dislikes DESC LIMIT 5");
while($row = mysqli_fetch_assoc($res)) {
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
    $html .= '<tr><td>' . htmlspecialchars($row['suggestion_text']) . '</td><td>' . $row['likes'] . '</td><td>' . $row['dislikes'] . '</td></tr>';
}
$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

if ($pdf->Output('ai_analytics_summary.pdf', 'I')) {
    header("Location: ai_feedback_analytics.php?msg=".urlencode('PDF export successful.'));
    exit();
} else {
    echo "Error: " . htmlspecialchars($pdf->getLastError());
}
exit;
