        </main><!-- End of .page-content -->
    </div><!-- End of .main-content -->

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle Sidebar
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Toggle User Dropdown
        document.getElementById('userDropdown').addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('dropdownMenu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdownMenu = document.getElementById('dropdownMenu');
            if (!e.target.closest('#userDropdown')) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Toggle Submenus
        document.querySelectorAll('.has-submenu > .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                const parent = this.parentElement;
                
                // Close other open submenus
                document.querySelectorAll('.has-submenu').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('show');
                        item.querySelector('.submenu').style.display = 'none';
                    }
                });
                
                // Toggle current submenu
                parent.classList.toggle('show');
                if (parent.classList.contains('show')) {
                    submenu.style.display = 'block';
                } else {
                    submenu.style.display = 'none';
                }
            });
        });
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const fade = new bootstrap.Alert(alert);
                setTimeout(() => {
                    fade.close();
                }, 5000);
            });
        }, 100);
        
        // Active menu highlighting
        const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    </script>
    
    <!-- Page-specific scripts -->
    <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php'): ?>
    <script>
        // Dashboard Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                        datasets: [{
                            label: 'Revenue',
                            data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            borderColor: 'rgba(67, 97, 238, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
            
            // Property Status Doughnut
            const propertyCtx = document.getElementById('propertyStatusChart');
            if (propertyCtx) {
                new Chart(propertyCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Available', 'Booked', 'Sold', 'Under Construction'],
                        datasets: [{
                            data: [30, 20, 15, 35],
                            backgroundColor: [
                                'rgba(40, 167, 69, 0.8)',
                                'rgba(23, 162, 184, 0.8)',
                                'rgba(108, 117, 125, 0.8)',
                                'rgba(255, 193, 7, 0.8)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
