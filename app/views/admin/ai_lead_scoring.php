<?php
/**
 * AI Lead Scoring - Powered by Gemini
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../app/services/GeminiService.php';

if (!hasRole("Admin")) {
    header("location:index.php?error=access_denied");
    exit();
}

use App\Services\GeminiService;

$gemini = new GeminiService();
$success_msg = '';
$error_msg = '';
$db = \App\Core\App::database();

// Handle AI Scoring Request
if (isset($_POST['action']) && $_POST['action'] === 'score_leads') {
    // CSRF Protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_msg = "Invalid CSRF token.";
    } else {
        try {
            $leads_to_score = $db->fetchAll("SELECT id, name, status, notes, source, budget, assigned_to FROM leads WHERE ai_score IS NULL OR status != 'Converted' LIMIT 10");

            if (empty($leads_to_score)) {
                $success_msg = "All eligible leads are already scored.";
            } else {
                $scored_count = 0;
                foreach ($leads_to_score as $lead) {
                    $prompt = "Analyze this real estate lead and provide a score from 0 to 100 based on their potential to convert.
                    Lead Details:
                    Name: {$lead['name']}
                    Status: {$lead['status']}
                    Source: {$lead['source']}
                    Notes: {$lead['notes']}

                    Respond ONLY in JSON format: {\"score\": 85, \"summary\": \"High interest shown in premium properties...\"}";

                    $ai_response = $gemini->generateText($prompt);

                    // Extract JSON if AI wrapped it in markdown code blocks
                    if (preg_match('/```json\s*(.*?)\s*```/s', $ai_response, $matches)) {
                        $json_content = $matches[1];
                    } else {
                        $json_content = $ai_response;
                    }

                    $data = json_decode($json_content, true);

                    if ($data && isset($data['score'])) {
                        $db->execute("UPDATE leads SET ai_score = :score, ai_summary = :summary WHERE id = :id", [
                            'score' => $data['score'],
                            'summary' => $data['summary'],
                            'id' => $lead['id']
                        ]);

                        // Trigger alert for high score leads
                        if ($data['score'] >= 80) {
                            require_once __DIR__ . '/../includes/notification_manager.php';
                            $nm = new NotificationManager($db->getConnection());

                            // Notify assigned user or all admins
                            $notify_user_id = $lead['assigned_to'];
                            if (!$notify_user_id) {
                                // If not assigned, find a default admin or skip for now
                                // For simplicity, we'll try to notify the current admin
                                $notify_user_id = $_SESSION['uid'] ?? null;
                            }

                            if ($notify_user_id) {
                                $nm->createTemplatedNotification($notify_user_id, 'HOT_LEAD_ALERT', [
                                    'name' => $lead['name'],
                                    'score' => $data['score'],
                                    'budget' => $lead['budget'] ?? 'Not specified'
                                ]);
                            }
                        }

                        $scored_count++;
                    }
                }
                $success_msg = "Successfully scored $scored_count leads using AI.";
            }
        } catch (Exception $e) {
            $error_msg = "AI Scoring Error: " . $e->getMessage();
        }
    }
}

// Fetch scored leads for display
$scored_leads = $db->fetchAll("SELECT * FROM leads WHERE ai_score IS NOT NULL ORDER BY ai_score DESC LIMIT 50");

$page_title = "AI Lead Scoring";
include __DIR__ . '/admin_header.php';
?>

<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><?php echo h($page_title); ?></h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">AI Scoring</li>
                </ul>
            </div>
            <div class="col-auto">
                <form method="POST">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="score_leads">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-robot mr-1"></i> Start AI Analysis
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo h($success_msg); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo h($error_msg); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">AI Prioritized Leads</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Lead Name</th>
                                    <th>AI Score</th>
                                    <th>Status</th>
                                    <th>AI Summary</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scored_leads as $lead): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo h($lead['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo h($lead['source']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-xs mr-2" style="width: 60px;">
                                                <div class="progress-bar <?php
                                                    if($lead['ai_score'] >= 75) echo 'bg-success';
                                                    elseif($lead['ai_score'] >= 40) echo 'bg-warning';
                                                    else echo 'bg-danger';
                                                ?>" style="width: <?php echo h($lead['ai_score']); ?>%"></div>
                                            </div>
                                            <span class="badge <?php
                                                if($lead['ai_score'] >= 75) echo 'badge-success';
                                                elseif($lead['ai_score'] >= 40) echo 'badge-warning';
                                                else echo 'badge-danger';
                                            ?>"><?php echo h($lead['ai_score']); ?>%</span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-light"><?php echo h($lead['status']); ?></span></td>
                                    <td><small><?php echo h($lead['ai_summary']); ?></small></td>
                                    <td class="text-right">
                                        <a href="leads.php?id=<?php echo h($lead['id']); ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
