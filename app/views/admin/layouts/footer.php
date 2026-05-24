        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    setTimeout(function(){
        document.querySelectorAll('.alert').forEach(function(alert){
            try { var bsAlert = new bootstrap.Alert(alert); bsAlert.close(); } catch(e) {}
        });
    }, 5000);
    </script>
    <?php if (!empty($extra_js)): ?>
    <script src="<?php echo $extra_js; ?>"></script>
    <?php endif; ?>
</body>
</html>
