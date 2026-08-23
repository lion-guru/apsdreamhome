ï»¿<?php $page_title = $page_title ?? 'Voice Call'; $lead = $lead ?? []; ?>
<style>.voice-call-ui{max-width:500px;margin:0 auto;text-align:center;padding:40px 20px}.lead-avatar-lg{width:120px;height:120px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:700;color:#fff;margin:0 auto 20px}.call-btn{width:70px;height:70px;border-radius:50%;border:none;font-size:24px;color:#fff;cursor:pointer;transition:.3s}.call-btn:hover{transform:scale(1.1)}.call-btn.end{background:#ef4444}.call-btn.mute{background:#6b7280}.call-btn.note{background:#3b82f6}</style>

<div class="container-fluid px-4 py-4">
    <a href="<?= BASE_URL ?>/admin/crm/voice" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Voice CRM</a>

    <div class="voice-call-ui">
        <?php $initials = strtoupper(substr($lead['name'] ?? 'L', 0, 1)); ?>
        <div class="lead-avatar-lg style-43228"><?= $initials ?></div>
        <h3 class="fw-bold"><?= htmlspecialchars($lead['name'] ?? 'Unknown Lead') ?></h3>
        <p class="text-muted mb-1"><?= htmlspecialchars($lead['phone'] ?? 'No phone') ?></p>
        <p class="text-muted mb-4"><?= htmlspecialchars($lead['email'] ?? '') ?></p>

        <div id="call-status" class="mb-4"><span class="badge bg-secondary fs-6" id="status-badge">Ready to Call</span></div>

        <div class="mb-4">
            <button class="call-btn end me-2" id="btn-call" onclick="startCall()" title="Start Call" aria-label="Call"><i class="fas fa-phone"></i></button>
            <button class="call-btn mute me-2" id="btn-mute" onclick="toggleMute()" title="Mute" disabled aria-label="Call"><i class="fas fa-microphone-slash"></i></button>
            <button class="call-btn end" id="btn-end" onclick="endCall()" title="End Call" disabled aria-label="Call"><i class="fas fa-phone-slash"></i></button>
        </div>

        <div class="card border-0 shadow-sm mb-3 style-56956"><div class="card-body text-start">
            <h6 class="fw-bold"><i class="fas fa-sticky-note me-1"></i>Voice Note</h6>
            <div class="mb-2"><button class="btn btn-sm btn-outline-primary" id="btn-dictate" onclick="startDictation()"><i class="fas fa-microphone me-1"></i>Start Dictating</button></div>
            <textarea id="note-text" class="form-control" rows="3" placeholder="Or type your note here..."></textarea>
            <button class="btn btn-primary btn-sm mt-2" onclick="saveNote()"><i class="fas fa-save me-1"></i>Save Note</button>
        </div></div>

        <div class="card border-0 shadow-sm style-56956"><div class="card-body text-start">
            <h6 class="fw-bold"><i class="fas fa-microphone me-1"></i>Voice Commands</h6>
            <div class="input-group"><input type="text" id="voice-cmd" class="form-control" placeholder="Type or speak a command (Hindi/English)"><button class="btn btn-primary" onclick="sendCommand()" aria-label="Call"><i class="fas fa-paper-plane"></i></button></div>
            <div id="cmd-result" class="mt-2"></div>
        </div></div>
    </div>
</div>

<script>
let recognition, callTimer, callSeconds=0, muted=false;
const leadId = <?= $lead['id'] ?? 0 ?>;

function startCall() {
    document.getElementById('status-badge').className = 'badge bg-success fs-6';
    document.getElementById('status-badge').textContent = 'Calling...';
    document.getElementById('btn-call').disabled = true;
    document.getElementById('btn-end').disabled = false;
    document.getElementById('btn-mute').disabled = false;
    callTimer = setInterval(()=>{callSeconds++;let m=Math.floor(callSeconds/60),s=callSeconds%60;document.getElementById('status-badge').textContent='In Call: '+m+':'+(s<10?'0':'')+s;},1000);
}

function endCall() {
    clearInterval(callTimer);
    document.getElementById('status-badge').className = 'badge bg-secondary fs-6';
    document.getElementById('status-badge').textContent = 'Call ended ('+callSeconds+'s)';
    document.getElementById('btn-call').disabled = false;
    document.getElementById('btn-end').disabled = true;
    document.getElementById('btn-mute').disabled = true;
    if(callSeconds>0){fetch('<?= BASE_URL ?>/admin/crm/voice/note',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({lead_id:leadId,transcript:'Call duration: '+callSeconds+' seconds'})});}
    callSeconds=0;
}

function toggleMute(){muted=!muted;document.getElementById('btn-mute').style.background=muted?'#ef4444':'#6b7280';}

function startDictation(){
    if(!('webkitSpeechRecognition' in window||'SpeechRecognition' in window)){showToast('Speech recognition not supported', 'info');return;}
    const SR=window.SpeechRecognition||window.webkitSpeechRecognition;recognition=new SR();recognition.lang='hi-IN';recognition.interimResults=true;
    recognition.onresult=e=>{document.getElementById('note-text').value=Array.from(e.results).map(r=>r[0].transcript).join('');};
    recognition.start();document.getElementById('btn-dictate').innerHTML='<i class="fas fa-spinner fa-spin me-1"></i>Listening...';
    recognition.onend=()=>{document.getElementById('btn-dictate').innerHTML='<i class="fas fa-microphone me-1"></i>Start Dictating';};
}

function saveNote(){
    const text=document.getElementById('note-text').value.trim();if(!text)return;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/crm/voice/note',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({lead_id:leadId,transcript:text})})
    .then(r=>r.json()).then(d=>{if(d.success){document.getElementById('note-text').value='';showToast('Note saved!', 'success');}}).finally(() => hideLoader());
}

function sendCommand(){
    const cmd=document.getElementById('voice-cmd').value.trim();if(!cmd)return;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/crm/voice/command',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({command:cmd})})
    .then(r=>r.json()).then(d=>{document.getElementById('cmd-result').innerHTML='<div class="alert alert-info mb-0">'+d.message+'</div>';}).finally(() => hideLoader());
}
</script>
