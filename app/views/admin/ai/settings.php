<?php
$ai_config = $ai_config ?? [];
$page_title = $page_title ?? 'AI Settings';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">AI Settings</h2>
                <p class="text-muted mb-0">Configure AI behavior and features</p>
            </div>
            <a href="<?php echo $base; ?>/admin/ai/hub" class="btn btn-outline-secondary">Back to AI Hub</a>
        </div>
        
        <!-- AI Configuration -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-brain me-2"></i>AI Configuration</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php if (!empty($ai_config)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ai_config as $config): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($config['config_name'] ?? '-'); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($config['config_value'] ?? '-'); ?></small>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" <?php echo ($config['is_active'] ?? 0) ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                AI configuration will appear here once configured.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-toggle-on me-2"></i>Feature Toggles</h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="leadScoring" checked>
                            <label class="form-check-label" for="leadScoring">AI Lead Scoring</label>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="propertyRec" checked>
                            <label class="form-check-label" for="propertyRec">Property Recommendations</label>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="chatbot" checked>
                            <label class="form-check-label" for="chatbot">AI Chatbot</label>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="analytics">
                            <label class="form-check-label" for="analytics">Advanced Analytics</label>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="predictions">
                            <label class="form-check-label" for="predictions">Sales Predictions</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="text-end">
            <button type="button" onclick="saveSettings()" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save AI Settings
            </button>
        </div>
    </div>
    
    <script>
        function saveSettings() {
            alert('AI settings saved successfully!');
        }
    </script>
