<?php $settings = $settings ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Voice Agent Settings</h4>
</div>
<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header">Voice Provider Configuration</div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Provider</label>
                        <select name="provider" class="form-select">
                            <option value="twilio" <?= ($settings['provider'] ?? 'twilio') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                            <option value="vapi" <?= ($settings['provider'] ?? '') === 'vapi' ? 'selected' : '' ?>>Vapi</option>
                            <option value="plivo" <?= ($settings['provider'] ?? '') === 'plivo' ? 'selected' : '' ?>>Plivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="password" name="api_key" class="form-control" value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Secret</label>
                        <input type="password" name="api_secret" class="form-control" value="<?= htmlspecialchars($settings['api_secret'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Number</label>
                        <input type="text" name="from_number" class="form-control" value="<?= htmlspecialchars($settings['from_number'] ?? '') ?>" placeholder="+1234567890">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header">Agent Parameters</div>
                <div class="card-body aps-cp-card-body">
                    <div class="mb-3">
                        <label class="form-label">Max Call Duration (seconds)</label>
                        <input type="number" name="max_duration" class="form-control" value="<?= $settings['max_duration'] ?? 300 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Retries</label>
                        <input type="number" name="max_retries" class="form-control" value="<?= $settings['max_retries'] ?? 3 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Retry Interval (minutes)</label>
                        <input type="number" name="retry_interval" class="form-control" value="<?= $settings['retry_interval'] ?? 30 ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Concurrent Calls</label>
                        <input type="number" name="concurrent_calls" class="form-control" value="<?= $settings['concurrent_calls'] ?? 5 ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header aps-cp-card-header">TTS / STT Settings</div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">TTS Engine</label>
                            <select name="tts_engine" class="form-select">
                                <option value="google" <?= ($settings['tts_engine'] ?? 'google') === 'google' ? 'selected' : '' ?>>Google TTS</option>
                                <option value="azure" <?= ($settings['tts_engine'] ?? '') === 'azure' ? 'selected' : '' ?>>Azure TTS</option>
                                <option value="amazon" <?= ($settings['tts_engine'] ?? '') === 'amazon' ? 'selected' : '' ?>>Amazon Polly</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">STT Engine</label>
                            <select name="stt_engine" class="form-select">
                                <option value="google" <?= ($settings['stt_engine'] ?? 'google') === 'google' ? 'selected' : '' ?>>Google STT</option>
                                <option value="azure" <?= ($settings['stt_engine'] ?? '') === 'azure' ? 'selected' : '' ?>>Azure STT</option>
                                <option value="deepgram" <?= ($settings['stt_engine'] ?? '') === 'deepgram' ? 'selected' : '' ?>>Deepgram</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Voice</label>
                            <select name="voice" class="form-select">
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
</form>
