<?php
// MLM Team View: Best UX + Export & Table View (phase 1)
require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/functions/mlm_business.php';

// Export logic (Excel/PDF)
if (isset($_GET['export']) && in_array($_GET['export'], ['excel','pdf'])) {
    // --- Export subtree as Excel/PDF (stub, see README for composer install) ---
    // For Excel: composer require phpoffice/phpspreadsheet
    // For PDF: composer require tecnickcom/tcpdf
    header('Content-Type: text/plain; charset=utf-8');
    echo "Export feature: Please install PHPSpreadsheet/TCPDF via Composer.\n";
    echo "Then uncomment the export logic in this file.\n";
    exit;
}

$root_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_sponsor = isset($_GET['search_sponsor']) ? trim($_GET['search_sponsor']) : '';
$search_level = isset($_GET['search_level']) ? intval($_GET['search_level']) : 0;
$search_status = isset($_GET['search_status']) ? trim($_GET['search_status']) : '';
$search_joined = isset($_GET['search_joined']) ? trim($_GET['search_joined']) : '';

function fetchAllDownlineIds($con, $root_id, &$ids) {
    $stmt = $con->prepare("SELECT id FROM associates WHERE parent_id=?");
    $stmt->bind_param('i', $root_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
        fetchAllDownlineIds($con, $row['id'], $ids);
    }
    $stmt->close();
}

function fetchAssociatesFlatList($con, $root_id, $allowed_ids, &$list, $level=1, $filters=[]) {
    $stmt = $con->prepare("SELECT id, name, email, phone, level, status FROM associates WHERE parent_id=?");
    $stmt->bind_param('i', $root_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['id'], $allowed_ids)) continue;
        // Filter by level/status/joined
        if (!empty($filters['level']) && intval($row['level']) !== intval($filters['level'])) continue;
        if (!empty($filters['status']) && strtolower($row['status']) !== strtolower($filters['status'])) continue;
        if (!empty($filters['joined']) && strpos($row['join_date'], $filters['joined']) === false) continue;
        $row['level'] = $level;
        $list[] = $row;
        fetchAssociatesFlatList($con, $row['id'], $allowed_ids, $list, $level+1, $filters);
    }
    $stmt->close();
}

function fetchAssociatesTreeRestricted($con, $root_id, $allowed_ids, $search_name = '', $search_sponsor = '', $filters=[]) {
    $params = [$root_id];
    $sql = "SELECT id, name, email, phone, level, status FROM associates WHERE parent_id=?";
    if ($search_name) {
        $sql .= " AND name LIKE ?";
        $params[] = "%$search_name%";
    }
    if ($search_sponsor) {
        $sql .= " AND parent_id IN (SELECT id FROM associates WHERE id=? OR name LIKE ?)";
        $params[] = $search_sponsor;
        $params[] = "%$search_sponsor%";
    }
    if (!empty($filters['level'])) {
        $sql .= " AND level=?";
        $params[] = $filters['level'];
    }
    if (!empty($filters['status'])) {
        $sql .= " AND status=?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['joined'])) {
        $sql .= " AND join_date LIKE ?";
        $params[] = "%".$filters['joined']."%";
    }
    $stmt = $con->prepare($sql);
    $types = '';
    foreach ($params as $p) $types .= is_int($p) ? 'i' : 's';
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $tree = [];
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['id'], $allowed_ids)) continue;
        $row['children'] = fetchAssociatesTreeRestricted($con, $row['id'], $allowed_ids, $search_name, $search_sponsor, $filters);
        $tree[] = $row;
    }
    $stmt->close();
    return $tree;
}

function fetchAssociateById($con, $id) {
    $stmt = $con->prepare("SELECT id, name, email, phone, level, status, commission_percent, parent_id, created_at FROM associates WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

function fetchDirectDownlineCount($con, $id, $allowed_ids) {
    $stmt = $con->prepare("SELECT id FROM associates WHERE parent_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['id'], $allowed_ids)) $count++;
    }
    $stmt->close();
    return $count;
}

function fetchSummaryStats($con, $root_id, $allowed_ids) {
    $total = count($allowed_ids) - 1;
    $direct = fetchDirectDownlineCount($con, $root_id, $allowed_ids);
    $maxLevel = 1;
    $queue = [[$root_id, 1]];
    while ($queue) {
        list($cur, $level) = array_shift($queue);
        $stmt = $con->prepare("SELECT id FROM associates WHERE parent_id=?");
        $stmt->bind_param('i', $cur);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $maxLevel = max($maxLevel, $level+1);
            $queue[] = [$row['id'], $level+1];
        }
        $stmt->close();
    }
    return ['total'=>$total, 'direct'=>$direct, 'max_level'=>$maxLevel];
}

function renderAssociatesTreeRestricted($tree, $con, $allowed_ids, $search_name = '') {
    echo "<ul>";
    foreach ($tree as $node) {
        $directCount = fetchDirectDownlineCount($con, $node['id'], $allowed_ids);
        $highlight = ($search_name && stripos($node['name'], $search_name) !== false) ? 'background:#ffe066;' : '';
        // Get business and reward info
        $biz = getAssociateBusinessSummary($con, $node['id']);
        echo '<li><div class="node collapsible" style="'.$highlight.'" data-id="'.$node['id'].'">'.
            '<span class="toggle-btn" onclick="toggleNode(this)">[+]</span> '.
            htmlspecialchars($node['name']).
            ' <span style="color:#888;font-size:12px;">(Direct: '.intval($directCount).')</span><br>'.
            '<small>'.htmlspecialchars($node['email']).'<br>'.htmlspecialchars($node['phone']).'</small>';
        // Show business and reward info
        echo '<br><span style="color:#008800;font-size:12px;">Business: ₹'.number_format($biz['total_business']).' | Reward: '.htmlspecialchars($biz['reward_tier']).'</span>';
        echo '<br><a href="?id=' . $node['id'] . '" class="team-link">View Team</a>';
        echo '</div>';
        if (!empty($node['children'])) {
            echo '<div class="children">';
            renderAssociatesTreeRestricted($node['children'], $con, $allowed_ids, $search_name);
            echo '</div>';
        }
        echo "</li>";
    }
    echo "</ul>";
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Team (Best Tree View)</title>
    <link rel="stylesheet" href="associates_tree_view.css">
    <link rel="stylesheet" href="assets/css/datatables.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f7fbfd; }
        h1 { color: #2c3e50; margin: 0 0 18px 0; padding: 20px 0 0 0; text-align: center; }
        .stats-bar { background: #f5faff; border: 1px solid #cbe6f5; border-radius: 6px; padding: 10px 18px; margin-bottom: 18px; color: #2980b9; font-size: 16px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 8px #eaf6fb; }
        .search-bar { margin-bottom: 18px; text-align: center; }
        .search-bar input, .search-bar select { padding: 5px 8px; font-size: 15px; border-radius: 4px; border: 1px solid #ccc; }
        .search-bar button { padding: 5px 12px; font-size: 15px; border-radius: 4px; background: #2980b9; color: #fff; border: none; }
        .export-bar { margin-bottom: 18px; text-align: right; max-width:900px; margin-left:auto; margin-right:auto; }
        .export-btn { padding: 5px 16px; font-size: 15px; border-radius: 4px; background: #1abc9c; color: #fff; border: none; margin-left:8px; }
        .export-btn.pdf { background: #e67e22; }
        .export-btn:hover { background: #16a085; }
        .export-btn.pdf:hover { background: #d35400; }
        .toggle-view { float:left; margin-left:8px; background:#eaf6fb; color:#2980b9; border:1px solid #cbe6f5; border-radius:4px; font-size:15px; padding:5px 12px; }
        .toggle-view.active { background:#2980b9; color:#fff; }
        .tree { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 16px #eaf6fb; padding: 24px; }
        ul { list-style: none; padding-left: 28px; }
        .node { margin-bottom: 10px; padding: 8px 12px; border-radius: 6px; border: 1px solid #eaf6fb; background: #fafdff; position: relative; }
        .node .team-link { display:inline-block; margin-top:6px; padding:2px 8px; background:#eaf6fb; color:#2980b9; border-radius:4px; text-decoration:none; font-size:13px; border:1px solid #cbe6f5; }
        .node .team-link:hover { background:#d4f0ff; border-color:#2980b9; }
        .toggle-btn { cursor:pointer; color:#2980b9; font-weight:bold; margin-right:8px; }
        .children { margin-left: 18px; display: none; }
        .table-view { display:none; max-width:900px; margin:0 auto; background:#fff; border-radius:10px; box-shadow:0 2px 16px #eaf6fb; padding:24px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:8px 10px; border-bottom:1px solid #eaf6fb; }
        th { background:#fafdff; color:#2c3e50; }
        tr.highlight { background:#ffe066 !important; }
        @media (max-width:600px) {
            .tree, .table-view { padding: 8px; }
            .stats-bar { font-size: 13px; padding: 6px 4px; }
        }
    </style>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script>
    function toggleNode(btn) {
        var node = btn.parentElement.parentElement;
        var children = node.querySelector('.children');
        if (!children) return;
        if (children.style.display === 'block') {
            children.style.display = 'none';
            btn.textContent = '[+]';
        } else {
            children.style.display = 'block';
            btn.textContent = '[-]';
        }
    }
    window.onload = function() {
        var nodes = document.querySelectorAll('.tree > ul > li > .node .toggle-btn');
        nodes.forEach(function(btn){ btn.click(); });
        document.getElementById('toggleTree').onclick = function(){
            document.querySelector('.tree').style.display = '';
            document.querySelector('.table-view').style.display = 'none';
            this.classList.add('active');
            document.getElementById('toggleTable').classList.remove('active');
        };
        document.getElementById('toggleTable').onclick = function(){
            document.querySelector('.tree').style.display = 'none';
            document.querySelector('.table-view').style.display = '';
            this.classList.add('active');
            document.getElementById('toggleTree').classList.remove('active');
        };
        if (window.jQuery && $('#associatesTable').length) {
            $('#associatesTable').DataTable();
        }
    };
    setInterval(function(){ window.location.reload(); }, 60000);
    </script>
</head>
<body>
<h1>My Team (Best Tree View)</h1>
<div class="stats-bar">
<?php
$me = fetchAssociateById($con, $root_id);
$allowed_ids = [$root_id];
fetchAllDownlineIds($con, $root_id, $allowed_ids);
$stats = fetchSummaryStats($con, $root_id, $allowed_ids);
echo "<button id='toggleTree' class='toggle-view active'>Tree View</button> <button id='toggleTable' class='toggle-view'>Table View</button> ";
echo "<span style='float:right'>Directs: <b>{$stats['direct']}</b> | Total Team: <b>{$stats['total']}</b> | Max Level: <b>{$stats['max_level']}</b></span>";
?>
</div>
<div class="export-bar">
    <a href="?id=<?php echo $root_id; ?>&export=excel" class="export-btn">Export Excel</a>
    <a href="?id=<?php echo $root_id; ?>&export=pdf" class="export-btn pdf">Export PDF</a>
</div>
<div class="search-bar">
    <form method="get" action="">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($root_id); ?>">
        <input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name); ?>" placeholder="Search by name...">
        <input type="text" name="search_sponsor" value="<?php echo htmlspecialchars($search_sponsor); ?>" placeholder="Search by sponsor id or name...">
        <select name="search_level"><option value="">Level</option><?php for($i=1;$i<=7;$i++){echo '<option value="'.$i.'"'.($search_level==$i?' selected':'').'>'.$i.'</option>'; } ?></select>
        <select name="search_status"><option value="">Status</option><option value="active"<?php if($search_status=='active')echo' selected';?>>Active</option><option value="inactive"<?php if($search_status=='inactive')echo' selected';?>>Inactive</option></select>
        <input type="text" name="search_joined" value="<?php echo htmlspecialchars($search_joined); ?>" placeholder="Joined (YYYY-MM or date)">
        <button type="submit">Search</button>
    </form>
</div>
<div class="tree">
<?php
if ($me) {
    echo '<div class="node" style="margin-bottom:15px;">'.
        htmlspecialchars($me['name']).'<br><small>'.
        htmlspecialchars($me['email']).'<br>'.htmlspecialchars($me['phone']).'</small></div>';
    $tree = fetchAssociatesTreeRestricted($con, $me['id'], $allowed_ids, $search_name, $search_sponsor, [
        'level'=>$search_level,
        'status'=>$search_status,
        'joined'=>$search_joined
    ]);
    renderAssociatesTreeRestricted($tree, $con, $allowed_ids, $search_name);
} else {
    echo '<div style="color:red;">Associate not found.</div>';
}
?>
</div>
<div class="table-view">
    <table id="associatesTable" class="display">
        <thead><tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Level</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php
        $flatList = [];
        fetchAssociatesFlatList($con, $root_id, $allowed_ids, $flatList, 1, [
            'level'=>$search_level,
            'status'=>$search_status,
            'joined'=>$search_joined
        ]);
        foreach ($flatList as $row) {
            $highlight = ($search_name && stripos($row['name'], $search_name) !== false) ? 'highlight' : '';
            echo '<tr class="'.$highlight.'">';
            echo '<td>'.htmlspecialchars($row['name']).'</td>';
            echo '<td>'.htmlspecialchars($row['email']).'</td>';
            echo '<td>'.htmlspecialchars($row['phone']).'</td>';
            echo '<td>'.intval($row['level']).'</td>';
            echo '<td>'.htmlspecialchars($row['status']).'</td>';
            echo '<td><a href="?id='.$row['id'].'" class="team-link">View Team</a></td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
