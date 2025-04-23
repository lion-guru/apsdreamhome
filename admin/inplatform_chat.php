<?php
session_start();
include 'config.php';
require_role(['Admin','Partner','Customer','Agent']);

// --- AJAX endpoint for chat messages ---
if (isset($_GET['action']) && $_GET['action']==='fetch') {
    include '../config.php';
    $msgs = $conn->query("SELECT * FROM chat_messages ORDER BY created_at ASC LIMIT 100");
    $data = [];
    while($m = $msgs->fetch_assoc()) $data[] = $m;
    echo json_encode($data);
    exit;
}
if (isset($_GET['action']) && $_GET['action']==='send' && $_SERVER['REQUEST_METHOD']==='POST') {
    include '../config.php';
    $msg = trim($_POST['message'] ?? '');
    $email = $_SESSION['email'] ?? 'Unknown';
    if ($msg) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_email, message) VALUES (?, ?)");
        $stmt->bind_param('ss', $email, $msg);
        $stmt->execute();
    }
    echo json_encode(['status'=>'ok']);
    exit;
}
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>In-Platform Chat & Collaboration</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'>
<style>.chat-box{height:350px;overflow-y:auto;background:#f8f9fa;padding:1rem;border-radius:8px;}</style></head>
<body><div class='container py-4'><h2>In-Platform Chat & Collaboration</h2><div class='chat-box mb-3' id='chat-box'></div><form id='chat-form'><div class='input-group'><input type='text' id='chat-msg' class='form-control' placeholder='Type a message...'><button class='btn btn-primary' type='submit'>Send</button></div></form></div><script>
const box=document.getElementById('chat-box');
const form=document.getElementById('chat-form');
function fetchChat(){
  fetch('inplatform_chat.php?action=fetch').then(r=>r.json()).then(msgs=>{
    box.innerHTML=msgs.map(m=>`<div><b>${m.sender_email}:</b> ${m.message}</div>`).join('');
    box.scrollTop=box.scrollHeight;
  });
}
form.onsubmit=e=>{
  e.preventDefault();
  const msg=document.getElementById('chat-msg').value;
  if(msg.trim()==='')return;
  fetch('inplatform_chat.php?action=send',{method:'POST',body:new URLSearchParams({message:msg})}).then(()=>{
    document.getElementById('chat-msg').value='';
    fetchChat();
  });
};
setInterval(fetchChat,2000);fetchChat();
</script></body></html>
