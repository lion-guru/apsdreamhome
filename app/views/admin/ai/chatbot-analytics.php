<?php
$stats = $stats ?? [];
$popular_questions = $popular_questions ?? [];
$satisfaction_data = $satisfaction_data ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Chatbot Analytics</h2>
                <p class="text-muted mb-0">Usage metrics and insights</p>
            </div>
            <a href="<?php echo $base; ?>/admin/chatbot" class="btn btn-outline-secondary">Back</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><h3><?php echo $stats['total_conversations'] ?? 0; ?></h3><small class="text-muted">Total Conversations</small></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><h3><?php echo $stats['total_queries'] ?? 0; ?></h3><small class="text-muted">Total Queries</small></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><h3><?php echo ($stats['avg_satisfaction'] ?? 0) . '/5'; ?></h3><small class="text-muted">Avg Satisfaction</small></div></div>
            <div class="col-md-3"><div class="card border-0 shadow-sm text-center p-3"><h3><?php echo ($stats['resolution_rate'] ?? 0) . '%'; ?></h3><small class="text-muted">Resolution Rate</small></div></div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Popular Questions</h5></div>
                    <div class="card-body">
                        <?php if (!empty($popular_questions)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($popular_questions as $q): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($q['question'] ?? ''); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $q['count'] ?? 0; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-muted text-center py-3">No data yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Satisfaction Trend</h5></div>
                    <div class="card-body">
                        <canvas id="satisfactionChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('satisfactionChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($satisfaction_data['labels'] ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']); ?>,
                datasets: [{ label: 'Satisfaction', data: <?php echo json_encode($satisfaction_data['scores'] ?? [0,0,0,0,0,0,0]); ?>, borderColor: '#0d6efd', tension: 0.3 }]
            },
            options: { scales: { y: { min: 0, max: 5 } } }
        });
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
