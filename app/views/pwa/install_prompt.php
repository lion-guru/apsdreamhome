<?php $pageTitle = $pageTitle ?? $page_title ?? "PWA Install"; $base = $base ?? BASE_URL; ?>
<div class="container py-5 text-center">
    <i class="fas fa-download fa-4x text-primary mb-3"></i>
    <h4>Install APS Dream Home App</h4>
    <p class="text-muted">Install our app for a faster experience with offline access.</p>
    <button id="installBtn" class="btn btn-primary btn-lg" class="style-24280"><i class="fas fa-download me-2"></i>Install App</button>
    <p id="installInfo" class="text-muted small mt-3">Open this page in Chrome/Safari and add to home screen.</p>
</div>
<script>
let deferredPrompt;
window.addEventListener("beforeinstallprompt", (e) => { e.preventDefault(); deferredPrompt = e; document.getElementById("installBtn").style.display = "inline-block"; });
document.getElementById("installBtn").addEventListener("click", async () => { if (deferredPrompt) { deferredPrompt.prompt(); await deferredPrompt.userChoice; deferredPrompt = null; document.getElementById("installBtn").style.display = "none"; } });
</script>