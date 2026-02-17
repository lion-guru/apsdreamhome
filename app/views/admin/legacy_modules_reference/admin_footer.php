<?php
/**
 * Admin Footer for APS Dream Homes
 * Contains footer elements shared across all admin pages
 */
?>

            </div>
            <!-- /Page Wrapper -->
        </div>
        <!-- /Main Wrapper -->
        
        <!-- jQuery -->
        <script src="<?php echo get_admin_asset_url('jquery.min.js', 'js'); ?>"></script>
        
        <!-- Bootstrap Core JS -->
        <script src="<?php echo get_admin_asset_url('popper.min.js', 'js'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('bootstrap.min.js', 'js'); ?>"></script>
        
        <!-- Slimscroll JS -->
        <script src="<?php echo get_admin_asset_url('slimscroll/jquery.slimscroll.min.js', 'plugins'); ?>"></script>
        
        <!-- Toastr JS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        <script>
        // Global Toastr Configuration
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        <?php if(isset($_SESSION['success'])): ?>
            toastr.success(<?php echo json_encode($_SESSION['success']); ?>);
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            toastr.error(<?php echo json_encode($_SESSION['error']); ?>);
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        </script>
        
        <?php if(isset($include_datatables) && $include_datatables): ?>
        <!-- Datatables JS -->
        <script src="<?php echo get_admin_asset_url('datatables/jquery.dataTables.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/dataTables.bootstrap4.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/dataTables.responsive.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/responsive.bootstrap4.min.js', 'plugins'); ?>"></script>
        
        <script src="<?php echo get_admin_asset_url('datatables/dataTables.select.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/dataTables.buttons.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/buttons.bootstrap4.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/buttons.html5.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/buttons.flash.min.js', 'plugins'); ?>"></script>
        <script src="<?php echo get_admin_asset_url('datatables/buttons.print.min.js', 'plugins'); ?>"></script>
        <?php endif; ?>
        
        <!-- Custom JS -->
        <script src="<?php echo get_admin_asset_url('script.js', 'js'); ?>"></script>
        
        <script>
        $(document).ready(function() {
            // 1. Dark Mode Toggle Logic
            const darkModeBtn = $('#dark-mode-toggle');
            const body = $('body');
            
            // Check for saved preference
            if (localStorage.getItem('admin-theme') === 'dark') {
                body.addClass('dark-mode');
                darkModeBtn.find('i').removeClass('fe-moon').addClass('fe-sun');
            }
            
            darkModeBtn.on('click', function() {
                body.toggleClass('dark-mode');
                const isDark = body.hasClass('dark-mode');
                localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
                $(this).find('i').toggleClass('fe-moon fe-sun');
            });

            // 2. AI Insights Loader for Dashboard
            if ($('#ai-insights-text').length > 0) {
                $.ajax({
                    url: '<?php echo ADMIN_URL; ?>/../ai_chatbot_api.php',
                    method: 'POST',
                    data: JSON.stringify({ 
                        message: "Give me a very short 1-sentence strategic insight or tip for the admin dashboard based on current real estate trends." 
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success && response.reply) {
                            $('#ai-insights-text').html('<i class="fas fa-lightbulb text-warning mr-1"></i> ' + response.reply);
                        } else {
                            $('#ai-insights-text').text("Focus on customer engagement and property inventory today.");
                        }
                    },
                    error: function() {
                        $('#ai-insights-text').text("Ready for a productive day! Check your recent bookings.");
                    }
                });
            }

            // 3. Dashboard Charts Logic
            if ($('#salesTrendsChart').length > 0) {
                // Sales & Bookings Trend
                $.getJSON('<?php echo ADMIN_URL; ?>/ajax/get-chart-data.php?chart=sales_overview', function(res) {
                    if (res.status === 'success') {
                        const ctx = document.getElementById('salesTrendsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: res.data.map(d => d.month),
                                datasets: [{
                                    label: 'Bookings',
                                    data: res.data.map(d => d.bookings),
                                    borderColor: '#4e73df',
                                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                    fill: true,
                                    tension: 0.3
                                }, {
                                    label: 'Revenue (₹)',
                                    data: res.data.map(d => d.sales),
                                    borderColor: '#1cc88a',
                                    backgroundColor: 'rgba(28, 200, 138, 0.05)',
                                    fill: true,
                                    tension: 0.3,
                                    yAxisID: 'y1'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y1: {
                                        position: 'right',
                                        grid: { drawOnChartArea: false }
                                    }
                                }
                            }
                        });
                    }
                });

                // Inventory Status
                $.getJSON('<?php echo ADMIN_URL; ?>/ajax/get-chart-data.php?chart=inventory_status', function(res) {
                    if (res.status === 'success') {
                        const ctx = document.getElementById('inventoryStatusChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: res.data.map(d => d.label),
                                datasets: [{
                                    data: res.data.map(d => d.value),
                                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    }
                });
            }
        });
        </script>

        <style>
        /* Dark Mode Minimal CSS */
        body.dark-mode {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
        body.dark-mode .card {
            background-color: #2d2d2d;
            border-color: #444;
            color: #fff;
        }
        body.dark-mode .header {
            background-color: #212121;
        }
        body.dark-mode .sidebar {
            background-color: #212121;
        }
        body.dark-mode .page-title, body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4, body.dark-mode h5, body.dark-mode h6 {
            color: #fff;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);
        }
        </style>
        
        <?php if(isset($page_specific_js)): ?>
        <!-- Page Specific JS -->
        <?php echo $page_specific_js; ?>
        <?php endif; ?>
    </body>
</html>
