<?php
require_once(__DIR__ . '/core/init.php');

// Check authentication
adminAccessControl(['super_admin', 'superadmin', 'admin']);

$db = \App\Core\App::database();
require_once('config/ai_tools_config.php');

// Get AI tools statistics
function getAIToolsStats()
{
    $db = \App\Core\App::database();
    $stats = [];

    // Chatbot stats
    $stats['chatbot'] = $db->fetchOne(
        "
        SELECT
            COUNT(*) as total_chats,
            AVG(satisfaction_score) as avg_satisfaction,
            AVG(response_time) as avg_response_time
        FROM " . CHATBOT_TABLE
    );

    // Property description stats
    $stats['property_desc'] = $db->fetchOne(
        "
        SELECT
            COUNT(*) as total_descriptions,
            AVG(word_count) as avg_word_count
        FROM " . PROPERTY_DESC_TABLE
    );

    return $stats;
}

// Get current AI tools settings
function getAIToolsSettings()
{
    $db = \App\Core\App::database();
    $settings = [];

    $rows = $db->fetchAll("SELECT tool_name, settings FROM " . AI_SETTINGS_TABLE);
    foreach ($rows as $row) {
        $settings[$row['tool_name']] = json_decode($row['settings'], true);
    }

    return $settings;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }

    $tool = $_POST['tool'] ?? '';
    $settings = $_POST['settings'] ?? [];

    if (!empty($tool) && !empty($settings)) {
        try {
            $db->execute(
                "UPDATE " . AI_SETTINGS_TABLE . " SET settings = ? WHERE tool_name = ?",
                [json_encode($settings), $tool]
            );
            $success_message = 'सेटिंग्स सफलतापूर्वक अपडेट की गईं';
        } catch (Exception $e) {
            $error_message = 'सेटिंग्स अपडेट करने में त्रुटि हुई';
            error_log($e->getMessage());
        }
    }
}

$stats = getAIToolsStats();
$current_settings = getAIToolsSettings();

?>
<!DOCTYPE html>
<html lang="hi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Tools Dashboard | APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .ai-tool-card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            border-radius: 1rem;
            margin-bottom: 2rem;
            padding: 2rem;
            background: #fff;
        }

        .ai-stats {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .ai-stat {
            background: #f8f9fa;
            border-radius: .5rem;
            padding: 1rem 2rem;
            text-align: center;
            min-width: 180px;
        }

        @media (max-width: 767px) {
            .ai-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4"><i class="fa-solid fa-robot"></i> AI टूल्स डैशबोर्ड</h1>
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row g-4">
            <!-- Chatbot Card -->
            <div class="col-lg-6 col-12">
                <div class="ai-tool-card">
                    <h3><i class="fa-solid fa-comments"></i> चैटबॉट</h3>
                    <p>AI चैटबॉट सेटिंग्स और आँकड़े</p>
                    <div class="ai-stats">
                        <div class="ai-stat">
                            <div class="fs-3 fw-bold text-primary"><?php echo $stats['chatbot']['total_chats'] ?? 0; ?></div>
                            <div>कुल चैट्स</div>
                        </div>
                        <div class="ai-stat">
                            <div class="fs-3 fw-bold text-success"><?php echo number_format($stats['chatbot']['avg_satisfaction'] ?? 0, 1); ?></div>
                            <div>औसत संतुष्टि</div>
                        </div>
                        <div class="ai-stat">
                            <div class="fs-3 fw-bold text-warning"><?php echo number_format($stats['chatbot']['avg_response_time'] ?? 0, 2); ?>s</div>
                            <div>औसत प्रतिक्रिया समय</div>
                        </div>
                    </div>
                    <form class="mt-4" method="POST" action="">
                        <?php echo getCsrfField(); ?>
                        <input type="hidden" name="tool" value="chatbot">
                        <div class="mb-3">
                            <label class="form-label">चैटबॉट भाषा</label>
                            <select class="form-select" name="settings[language]">
                                <option value="hi" <?php echo ($current_settings['chatbot']['language'] ?? '') === 'hi' ? 'selected' : ''; ?>>हिंदी</option>
                                <option value="en" <?php echo ($current_settings['chatbot']['language'] ?? '') === 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="both" <?php echo ($current_settings['chatbot']['language'] ?? '') === 'both' ? 'selected' : ''; ?>>दोनों</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> सेटिंग्स सेव करें</button>
                    </form>
                </div>
            </div>
            <!-- Property Description Generator Card -->
            <div class="col-lg-6 col-12">
                <div class="ai-tool-card">
                    <h3><i class="fa-solid fa-file-alt"></i> प्रॉपर्टी डिस्क्रिप्शन जनरेटर</h3>
                    <p>AI द्वारा प्रॉपर्टी विवरण तैयार करें</p>
                    <div class="ai-stats">
                        <div class="ai-stat">
                            <div class="fs-3 fw-bold text-primary"><?php echo $stats['property_desc']['total_descriptions'] ?? 0; ?></div>
                            <div>कुल विवरण</div>
                        </div>
                        <div class="ai-stat">
                            <div class="fs-3 fw-bold text-success"><?php echo number_format($stats['property_desc']['avg_word_count'] ?? 0, 1); ?></div>
                            <div>औसत शब्द संख्या</div>
                        </div>
                    </div>
                    <form class="mt-4" method="POST" action="">
                        <?php echo getCsrfField(); ?>
                        <input type="hidden" name="tool" value="property_description">
                        <div class="mb-3">
                            <label class="form-label">फोकस पॉइंट्स</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[focus_points][location_highlights]" value="1" <?php echo ($current_settings['property_description']['focus_points']['location_highlights'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label">लोकेशन हाइलाइट्स</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[focus_points][amenities]" value="1" <?php echo ($current_settings['property_description']['focus_points']['amenities'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label">अमेनिटीज</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[focus_points][investment_potential]" value="1" <?php echo ($current_settings['property_description']['focus_points']['investment_potential'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label">इन्वेस्टमेंट पोटेंशियल</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> सेटिंग्स सेव करें</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <?php include __DIR__ . '/includes/admin_footer.php'; ?>
</body>

</html>
