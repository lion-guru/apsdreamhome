<?php
$page_title = $page_title ?? 'AI Calling Training';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-robot me-2"></i><?= htmlspecialchars($page_title ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/admin/ai/hub" class="btn btn-outline-primary"><i class="fas fa-home me-1"></i> AI Hub</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i><?= $_SESSION['error'] ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div><h6 class="text-uppercase small mb-1">Voice Models</h6><h3 class="mb-0"><?= $totalVoiceModels ?? 0 ?></h3></div>
                        <i class="fas fa-microphone fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?= $activeVoiceModels ?? 0 ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div><h6 class="text-uppercase small mb-1">Scripts</h6><h3 class="mb-0"><?= $totalScripts ?? 0 ?></h3></div>
                        <i class="fas fa-comments fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?= $activeScripts ?? 0 ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div><h6 class="text-uppercase small mb-1">Intents</h6><h3 class="mb-0"><?= $totalIntents ?? 0 ?></h3></div>
                        <i class="fas fa-brain fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?= $activeIntents ?? 0 ?> active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div><h6 class="text-uppercase small mb-1">Total Calls</h6><h3 class="mb-0"><?= $perfTotalCalls ?? 0 ?></h3></div>
                        <i class="fas fa-phone fa-2x opacity-50"></i>
                    </div>
                    <small class="opacity-75"><?= $perfAvgDuration ?? 0 ?>s avg duration</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-solid mb-4">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-voice">Voice Models</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-scripts">Scripts</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-intents">Intents</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-perf">Performance</a></li>
    </ul>

    <div class="tab-content">
        <!-- VOICE MODELS TAB -->
        <div class="tab-pane show active" id="tab-voice">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-microphone me-2"></i>Voice Models</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#voiceModelModal" onclick="resetVoiceForm()"><i class="fas fa-plus me-1"></i>Add Model</button>
            </div>
            <?php if (empty($voiceModels)): ?>
                <div class="text-center py-5"><i class="fas fa-microphone fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No voice models yet</h5><p class="text-muted mb-3">Add a voice model to enable AI calling with custom voices.</p><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#voiceModelModal" onclick="resetVoiceForm()"><i class="fas fa-plus me-1"></i>Add Voice Model</button></div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Name</th><th>Language</th><th>Gender</th><th>Provider</th><th>Status</th><th>Calls Used</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($voiceModels as $vm): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($vm['model_name'] ?? '') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($vm['language'] ?? '') ?></span></td>
                                    <td><?= ucfirst($vm['voice_gender']) ?></td>
                                    <td><?= ucfirst($vm['model_provider']) ?></td>
                                    <td><?php $st = $vm['status']; ?><span class="badge bg-<?= $st === 'active' ? 'success' : ($st === 'training' ? 'warning' : 'secondary') ?>"><?= ucfirst($st) ?></span></td>
                                    <td><?= (int)$vm['total_calls_used'] ?></td>
                                    <td><button class="btn btn-sm btn-outline-primary" onclick='editVoiceModel(<?= json_encode($vm) ?>)'><i class="fas fa-edit"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- SCRIPTS TAB -->
        <div class="tab-pane" id="tab-scripts">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Calling Scripts</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scriptModal" onclick="resetScriptForm()"><i class="fas fa-plus me-1"></i>Add Script</button>
            </div>
            <?php if (empty($scripts)): ?>
                <div class="text-center py-5"><i class="fas fa-comments fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No scripts yet</h5><p class="text-muted mb-3">Create conversation scripts for different call scenarios.</p><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scriptModal" onclick="resetScriptForm()"><i class="fas fa-plus me-1"></i>Add Script</button></div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Script</th><th>Code</th><th>Category</th><th>Duration</th><th>Calls</th><th>Connected</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($scripts as $s): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($s['script_name'] ?? '') ?></td>
                                    <td><code class="small"><?= htmlspecialchars($s['script_code'] ?? '') ?></code></td>
                                    <td><span class="badge bg-info"><?= ucfirst($s['category']) ?></span></td>
                                    <td><?= $s['estimated_duration_seconds'] ?>s</td>
                                    <td><?= (int)$s['total_calls_made'] ?></td>
                                    <td><?= (int)$s['total_calls_connected'] ?></td>
                                    <td><span class="badge bg-<?= $s['is_active'] ? 'success' : 'secondary' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td><button class="btn btn-sm btn-outline-primary" onclick='editScript(<?= json_encode($s) ?>)'><i class="fas fa-edit"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- INTENTS TAB -->
        <div class="tab-pane" id="tab-intents">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0"><i class="fas fa-brain me-2"></i>Intent Recognition</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#intentModal" onclick="resetIntentForm()"><i class="fas fa-plus me-1"></i>Add Intent</button>
            </div>
            <?php if (empty($intents)): ?>
                <div class="text-center py-5"><i class="fas fa-brain fa-3x text-muted mb-3 d-block"></i><h5 class="text-muted">No intents configured</h5><p class="text-muted mb-3">Define intents to help the AI understand customer responses.</p><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#intentModal" onclick="resetIntentForm()"><i class="fas fa-plus me-1"></i>Add Intent</button></div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Intent</th><th>Code</th><th>Category</th><th>Priority</th><th>Triggers</th><th>Avg Confidence</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($intents as $i): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($i['intent_name'] ?? '') ?></td>
                                    <td><code class="small"><?= htmlspecialchars($i['intent_code'] ?? '') ?></code></td>
                                    <td><span class="badge bg-<?= $i['category'] === 'interest' ? 'success' : ($i['category'] === 'objection' ? 'warning' : ($i['category'] === 'dnd' ? 'danger' : 'primary') ) ?>"><?= ucfirst(str_replace('_', ' ', $i['category'])) ?></span></td>
                                    <td><span class="badge bg-dark"><?= (int)$i['priority'] ?></span></td>
                                    <td><?= (int)$i['total_triggers'] ?></td>
                                    <td><?= number_format($i['avg_confidence'], 1) ?>%</td>
                                    <td><span class="badge bg-<?= $i['is_active'] ? 'success' : 'secondary' ?>"><?= $i['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td><button class="btn btn-sm btn-outline-primary" onclick='editIntent(<?= json_encode($i) ?>)'><i class="fas fa-edit"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- PERFORMANCE TAB -->
        <div class="tab-pane" id="tab-perf">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-primary mb-0"><?= $perfTotalCalls ?? 0 ?></h3><small class="text-muted">Total Calls</small></div></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-success mb-0"><?= $perfCompletedCalls ?? 0 ?></h3><small class="text-muted">Completed</small></div></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-info mb-0"><?= $perfAvgDuration ?? 0 ?>s</h3><small class="text-muted">Avg Duration</small></div></div></div>
                <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center"><h3 class="text-warning mb-0"><?= $perfInterested ?? 0 ?></h3><small class="text-muted">Interested</small></div></div></div>
            </div>
            <h5 class="mb-3">Script Performance</h5>
            <?php if (empty($scriptPerformance)): ?>
                <div class="text-center py-4"><p class="text-muted">No performance data yet. Calls will appear here once AI calling is active.</p></div>
            <?php else: ?>
                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Script</th><th>Calls Made</th><th>Connected</th><th>Interested</th><th>Conv. Rate</th></tr></thead>
                            <tbody>
                            <?php foreach ($scriptPerformance as $sp): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($sp['script_name'] ?? '') ?></td>
                                    <td><?= (int)$sp['total_calls_made'] ?></td>
                                    <td><?= (int)$sp['total_calls_connected'] ?></td>
                                    <td><?= (int)$sp['total_interested'] ?></td>
                                    <td><span class="badge bg-<?= $sp['conversion_rate'] > 30 ? 'success' : ($sp['conversion_rate'] > 15 ? 'warning' : 'secondary') ?>"><?= number_format($sp['conversion_rate'], 1) ?>%</span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Voice Model Modal -->
<div class="modal fade" id="voiceModelModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-microphone me-2"></i>Voice Model</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/ai-calling/training/save-voice-model">
        <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" id="vm_id">
            <div class="mb-3"><label class="form-label">Model Name *</label><input type="text" name="model_name" id="vm_name" class="form-control" required></div>
            <div class="row mb-3">
                <div class="col"><label class="form-label">Language</label><select name="language" id="vm_language" class="form-select"><option value="hi-IN">Hindi</option><option value="en-US">English</option><option value="hi-EN">Hinglish</option></select></div>
                <div class="col"><label class="form-label">Gender</label><select name="voice_gender" id="vm_gender" class="form-select"><option value="female">Female</option><option value="male">Male</option><option value="neutral">Neutral</option></select></div>
            </div>
            <div class="row mb-3">
                <div class="col"><label class="form-label">Provider</label><select name="model_provider" id="vm_provider" class="form-select"><option value="google">Google TTS</option><option value="espeak">eSpeak</option><option value="azure">Azure Speech</option><option value="custom">Custom</option></select></div>
                <div class="col"><label class="form-label">Status</label><select name="status" id="vm_status" class="form-select"><option value="inactive">Inactive</option><option value="active">Active</option><option value="training">Training</option></select></div>
            </div>
            <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" id="vm_notes" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<!-- Script Modal -->
<div class="modal fade" id="scriptModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-comments me-2"></i>Calling Script</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/ai-calling/training/save-script">
        <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" id="sc_id">
            <div class="row mb-3">
                <div class="col-md-8"><label class="form-label">Script Name *</label><input type="text" name="script_name" id="sc_name" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Script Code</label><input type="text" name="script_code" id="sc_code" class="form-control" placeholder="SCRIPT_001"></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><label class="form-label">Category</label><select name="category" id="sc_category" class="form-select"><option value="sales">Sales</option><option value="follow_up">Follow-up</option><option value="emi_reminder">EMI Reminder</option><option value="appointment">Appointment</option><option value="cold_call">Cold Call</option><option value="survey">Survey</option><option value="support">Support</option><option value="general">General</option></select></div>
                <div class="col-md-4"><label class="form-label">Language</label><select name="language" id="sc_language" class="form-select"><option value="hi-IN">Hindi</option><option value="en-US">English</option><option value="hi-EN">Hinglish</option></select></div>
                <div class="col-md-4"><label class="form-label">Est. Duration (sec)</label><input type="number" name="estimated_duration_seconds" id="sc_duration" class="form-control" value="120"></div>
            </div>
            <div class="mb-3"><label class="form-label">Greeting *</label><textarea name="greeting_text" id="sc_greeting" class="form-control" rows="3" required placeholder="Namaste! Main APS Dream Home se bol raha hoon..."></textarea></div>
            <div class="mb-3"><label class="form-label">Main Body</label><textarea name="main_body" id="sc_body" class="form-control" rows="3" placeholder="Main conversation body..."></textarea></div>
            <div class="mb-3"><label class="form-label">Closing</label><textarea name="closing_text" id="sc_closing" class="form-control" rows="2" placeholder="Thank you script..."></textarea></div>
            <div class="form-check form-switch"><input type="checkbox" name="is_active" id="sc_active" class="form-check-input" value="1" checked><label class="form-check-label" for="sc_active">Active</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<!-- Intent Modal -->
<div class="modal fade" id="intentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-brain me-2"></i>Intent</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="<?= BASE_URL ?>/admin/ai-calling/training/save-intent">
        <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" id="in_id">
            <div class="mb-3"><label class="form-label">Intent Name *</label><input type="text" name="intent_name" id="in_name" class="form-control" required></div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Intent Code</label><input type="text" name="intent_code" id="in_code" class="form-control" placeholder="INT_NAME"></div>
                <div class="col-md-6"><label class="form-label">Category</label><select name="category" id="in_category" class="form-select"><option value="interest">Interest</option><option value="objection">Objection</option><option value="question">Question</option><option value="action">Action</option><option value="sentiment">Sentiment</option><option value="info_request">Info Request</option><option value="callback">Callback</option><option value="not_interested">Not Interested</option><option value="dnd">Do Not Call</option><option value="other">Other</option></select></div>
            </div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="in_desc" class="form-control" rows="2"></textarea></div>
            <div class="row mb-3">
                <div class="col-md-6"><label class="form-label">Priority (1-10)</label><input type="number" name="priority" id="in_priority" class="form-control" value="5" min="1" max="10"></div>
                <div class="col-md-6"><div class="form-check form-switch mt-4"><input type="checkbox" name="is_active" id="in_active" class="form-check-input" value="1" checked><label class="form-check-label">Active</label></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

<script>
function resetVoiceForm() { document.getElementById('vm_id').value=''; document.getElementById('vm_name').value=''; document.getElementById('vm_language').value='hi-IN'; document.getElementById('vm_gender').value='female'; document.getElementById('vm_provider').value='google'; document.getElementById('vm_status').value='inactive'; document.getElementById('vm_notes').value=''; }
function editVoiceModel(d) { document.getElementById('vm_id').value=d.id; document.getElementById('vm_name').value=d.model_name; document.getElementById('vm_language').value=d.language; document.getElementById('vm_gender').value=d.voice_gender; document.getElementById('vm_provider').value=d.model_provider; document.getElementById('vm_status').value=d.status; document.getElementById('vm_notes').value=d.notes||''; new bootstrap.Modal(document.getElementById('voiceModelModal')).show(); }
function resetScriptForm() { document.getElementById('sc_id').value=''; document.getElementById('sc_name').value=''; document.getElementById('sc_code').value=''; document.getElementById('sc_category').value='sales'; document.getElementById('sc_language').value='hi-IN'; document.getElementById('sc_duration').value='120'; document.getElementById('sc_greeting').value=''; document.getElementById('sc_body').value=''; document.getElementById('sc_closing').value=''; document.getElementById('sc_active').checked=true; }
function editScript(d) { document.getElementById('sc_id').value=d.id; document.getElementById('sc_name').value=d.script_name; document.getElementById('sc_code').value=d.script_code; document.getElementById('sc_category').value=d.category; document.getElementById('sc_language').value=d.language; document.getElementById('sc_duration').value=d.estimated_duration_seconds; document.getElementById('sc_greeting').value=d.greeting_text; document.getElementById('sc_body').value=d.main_body; document.getElementById('sc_closing').value=d.closing_text||''; document.getElementById('sc_active').checked=d.is_active==1; new bootstrap.Modal(document.getElementById('scriptModal')).show(); }
function resetIntentForm() { document.getElementById('in_id').value=''; document.getElementById('in_name').value=''; document.getElementById('in_code').value=''; document.getElementById('in_category').value='interest'; document.getElementById('in_desc').value=''; document.getElementById('in_priority').value='5'; document.getElementById('in_active').checked=true; }
function editIntent(d) { document.getElementById('in_id').value=d.id; document.getElementById('in_name').value=d.intent_name; document.getElementById('in_code').value=d.intent_code; document.getElementById('in_category').value=d.category; document.getElementById('in_desc').value=d.description||''; document.getElementById('in_priority').value=d.priority; document.getElementById('in_active').checked=d.is_active==1; new bootstrap.Modal(document.getElementById('intentModal')).show(); }
</script>
