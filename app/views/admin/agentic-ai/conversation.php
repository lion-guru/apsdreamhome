ï»¿<?php
$_conv = $conversation ?? null;
$_msgs = $messages ?? [];
$_ag = $agents ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-comment-dots style-64047"></i> Conversation #<?= $_conv['id'] ?? '?' ?></h1>
                    <small class="text-muted">
                        Lead: <?= htmlspecialchars($_conv['lead_name'] ?? 'Unknown') ?> | Channel: <?= htmlspecialchars($_conv['channel'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </small>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="claimConv()" class="btn btn-sm btn-outline-warning"><i class="fas fa-hand-paper"></i> Claim</button>
                    <button onclick="resolveConv()" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i> Resolve</button>
                    <a href="<?= BASE_URL ?>/admin/agentic-ai/conversations" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-outline">
                        <div class="card-body style-55767" id="msgContainer">
                            <?php if (empty($_msgs)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-comment-dots fa-3x mb-3 opacity-25"></i>
                                <p>No messages yet</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($_msgs as $m): ?>
                            <div class="d-flex mb-3 <?= $m['sender_type'] === 'customer' ? '' : 'flex-row-reverse' ?>">
                                <div class="style-70085">
                                    <div class="p-2 rounded <?= $m['sender_type'] === 'customer' ? 'bg-light' : 'bg-primary text-white' ?>">
                                        <?= nl2br(htmlspecialchars($m['message'] ?? '')) ?>
                                    </div>
                                    <small class="text-muted"><?= $m['sender_type'] === 'customer' ? 'Customer' : 'Agent' ?> Â· <?= date('H:i', strtotime($m['created_at'])) ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if (($_conv['status'] ?? '') === 'active'): ?>
                        <div class="card-footer">
                            <div class="input-group">
                                <input type="text" class="form-control" id="msgInput" placeholder="Type a message..." onkeypress="if(event.key==='Enter')sendMsg()">
                                <button class="btn btn-primary" onclick="sendMsg()" aria-label="Send"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-outline">
                        <div class="card-header"><h3 class="card-title">Details</h3></div>
                        <div class="card-body">
                            <p><strong>Status:</strong> <?= htmlspecialchars($_conv['status'] ?? 'unknown', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Lead:</strong> <?= htmlspecialchars($_conv['lead_name'] ?? 'Unknown') ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($_conv['lead_phone'] ?? '') ?></p>
                            <p><strong>Channel:</strong> <?= htmlspecialchars($_conv['channel'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Created:</strong> <?= htmlspecialchars($_conv['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function sendMsg(){
    var input=document.getElementById('msgInput');
    var msg=input.value.trim();
    if(!msg)return;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/agentic-ai/api/send',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({conversation_id:<?= $_conv['id'] ?? 0 ?>,message:msg})
    }).then(function(r){return r.json()}).then(function(d){
        if(d.success){input.value='';location.reload();}
        else{showToast('Failed: '+(d.error||'Unknown error'), 'danger');}
    ).finally(() => hideLoader());
    .catch(err => console.error('Request failed:', err));
}
function claimConv(){
    showLoader();
    fetch('<?= BASE_URL ?>/admin/agentic-ai/api/claim',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({conversation_id:<?= $_conv['id'] ?? 0 ?>})
    }).then(function(r){return r.json()}).then(function(d){if(d.success){location.reload();}).finally(() => hideLoader());
}
function resolveConv(){
    showLoader();
    fetch('<?= BASE_URL ?>/admin/agentic-ai/api/resolve',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({conversation_id:<?= $_conv['id'] ?? 0 ?>})
    }).then(function(r){return r.json()}).then(function(d){if(d.success){location.reload();}).finally(() => hideLoader());
}
</script>
