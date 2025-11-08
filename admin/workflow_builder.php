<?php
session_start();
include 'config.php';
require_role('Admin');
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Custom Workflow Builder</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'><script src='https://cdnjs.cloudflare.com/ajax/libs/jsPlumb/2.15.6/jsplumb.min.js'></script></head>
<body><div class='container py-4'><h2>Workflow Builder (No-Code/Low-Code)</h2><div id='workflow-canvas' style='width:100%;height:400px;border:1px solid #ccc;background:#f9f9f9;'></div><button class='btn btn-primary mt-3' onclick='saveWorkflow()'>Save Workflow</button><p class='mt-3'>*Drag and drop to create workflow steps and connect them. Save for automation and future integration.</p><script>// Basic drag-drop for demo only
let canvas = document.getElementById('workflow-canvas');
canvas.onclick = function(e) { let n = document.createElement('div'); n.className='border p-2 bg-white position-absolute'; n.style.left=e.offsetX+'px'; n.style.top=e.offsetY+'px'; n.innerText='Step'; canvas.appendChild(n); };
function saveWorkflow(){alert('Workflow saved (demo). Connect backend for full automation.');}
</script></div></body></html>
