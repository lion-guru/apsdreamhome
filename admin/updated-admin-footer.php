<?php
/**
 * Updated Admin Footer for APS Dream Homes
 * Contains footer elements shared across all admin pages
 */
?>

            </div>
            <!-- /Page Wrapper -->
        </div>
        <!-- /Main Wrapper -->
        
        <!-- jQuery -->
        <script src="js/jquery-3.2.1.min.js"></script>
        
        <!-- Bootstrap Core JS -->
        <script src="js/popper.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        
        <!-- Slimscroll JS -->
        <script src="assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
        
        <?php if(isset($include_datatables) && $include_datatables): ?>
        <!-- Datatables JS -->
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
        <script src="assets/plugins/datatables/dataTables.responsive.min.js"></script>
        <script src="assets/plugins/datatables/responsive.bootstrap4.min.js"></script>
        
        <script src="assets/plugins/datatables/dataTables.select.min.js"></script>
        <script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/plugins/datatables/buttons.html5.min.js"></script>
        <script src="assets/plugins/datatables/buttons.flash.min.js"></script>
        <script src="assets/plugins/datatables/buttons.print.min.js"></script>
        <?php endif; ?>
        
        <!-- Custom JS -->
        <script src="js/script.js"></script>
        
        <?php if(isset($page_specific_js)): ?>
        <!-- Page Specific JS -->
        <?php echo $page_specific_js; ?>
        <?php endif; ?>
    </body>
</html>