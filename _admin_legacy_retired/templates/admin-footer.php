</div><!-- End of content-container -->
            
            <!-- Admin Footer -->
            <footer class="admin-footer">
                <div class="footer-content">
                    <p>&copy; <?php echo date('Y'); ?> APS Dream Homes. All rights reserved.</p>
                    <p>Version 2.0</p>
                </div>
            </footer>
        </div><!-- End of main-content -->
    </div><!-- End of admin-wrapper -->
    
    <!-- jQuery -->
    <script src="<?php echo get_asset_url('jquery/jquery.min.js', 'vendor'); ?>"></script>
    
    <!-- Bootstrap Bundle JS -->
    <script src="<?php echo get_asset_url('bootstrap/js/bootstrap.bundle.min.js', 'vendor'); ?>"></script>
    
    <!-- DataTables JS -->
    <script src="<?php echo get_asset_url('datatables/js/jquery.dataTables.min.js', 'vendor'); ?>"></script>
    <script src="<?php echo get_asset_url('datatables/js/dataTables.bootstrap5.min.js', 'vendor'); ?>"></script>
    
    <!-- Admin JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        
        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Store sidebar state in localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
        
        // Apply sidebar state from localStorage
        const storedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (storedSidebarState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        
        if (sidebarCollapseBtn) {
            sidebarCollapseBtn.addEventListener('click', toggleSidebar);
        }
        
        // Initialize DataTables if present
        const dataTables = document.querySelectorAll('.datatable');
        if (dataTables.length > 0) {
            dataTables.forEach(table => {
                $(table).DataTable({
                    responsive: true,
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search..."
                    }
                });
            });
        }
        
        // Tooltips initialization
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
    
    <!-- Additional JS -->
    <?php if(isset($additional_js)) echo $additional_js; ?>
</body>
</html>