<?php
// DEPRECATED: This file is nearly identical to customer_footer.php
// Employee footer template - only differs in JavaScript comments
// Use app/views/layouts/customer_footer.php instead and customize as needed
?>
</div>

    <!-- Chart.js for analytics -->
    <?php if (isset($include_charts) && $include_charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <!-- Custom Employee JS -->
    <script>
        // Common functions for employee panel

        // Format currency
        function formatCurrency(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN');
        }

        // Show loading overlay
        function showLoading() {
            if (!$('#loadingOverlay').length) {
                $('body').append(`
                    <div id="loadingOverlay" style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.5);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `);
            }
            $('#loadingOverlay').show();
        }

        // Hide loading overlay
        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        // Confirm action
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }

        // Show alert
        function showAlert(message, type = 'info') {
            const alertClass = `alert-${type}`;
            const alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);

            $('body').append(alert);

            setTimeout(function() {
                alert.fadeOut();
            }, 5000);
        }

        // Format time
        function formatTime(timeString) {
            if (!timeString) return '-';
            return new Date('1970-01-01T' + timeString).toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Calculate hours worked
        function calculateHoursWorked(checkIn, checkOut) {
            if (!checkIn || !checkOut) return 0;

            const checkInTime = new Date('1970-01-01T' + checkIn);
            const checkOutTime = new Date('1970-01-01T' + checkOut);

            const diffMs = checkOutTime - checkInTime;
            const diffHours = diffMs / (1000 * 60 * 60);

            return Math.round(diffHours * 100) / 100;
        }

        // Task completion rate
        function calculateTaskCompletion(tasks) {
            if (!tasks || tasks.length === 0) return 0;

            const completed = tasks.filter(task => task.status === 'completed').length;
            return Math.round((completed / tasks.length) * 100);
        }

        // Attendance percentage
        function calculateAttendancePercentage(attendance) {
            if (!attendance || attendance.length === 0) return 0;

            const present = attendance.filter(record => record.status === 'present').length;
            return Math.round((present / attendance.length) * 100);
        }

        // Performance score color
        function getPerformanceColor(score) {
            if (score >= 90) return 'success';
            if (score >= 75) return 'info';
            if (score >= 60) return 'warning';
            return 'danger';
        }

        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Initialize popovers
        $(function () {
            $('[data-toggle="popover"]').popover();
        });

        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert:not(.alert-permanent)').fadeOut();
            }, 5000);
        });

        // Form validation helper
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;

            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            return isValid;
        }

        // Date picker initialization
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });

        // Time picker initialization
        $(document).ready(function() {
            $('.timepicker').timepicker({
                showMeridian: false,
                defaultTime: '09:00'
            });
        });

        // Search functionality
        function setupSearch(inputId, tableId) {
            const searchInput = document.getElementById(inputId);
            const table = document.getElementById(tableId);

            if (!searchInput || !table) return;

            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toUpperCase();
                const rows = table.getElementsByTagName('tr');

                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let match = false;

                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j].innerText.toUpperCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }

                    rows[i].style.display = match ? '' : 'none';
                }
            });
        }

        // Export table to CSV
        function exportToCSV(tableId, filename) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const rows = table.querySelectorAll('tr');
            const csv = [];

            rows.forEach(row => {
                const cells = row.querySelectorAll('td, th');
                const rowData = [];
                cells.forEach(cell => {
                    rowData.push('"' + cell.textContent.trim() + '"');
                });
                csv.push(rowData.join(','));
            });

            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>

    <!-- Custom styles for employee panel -->
    <style>
        /* Additional styles specific to employee panel */

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid white;
        }

        .employee-footer {
            margin-left: 260px;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .employee-footer {
                margin-left: 0;
            }
        }

        /* Stats cards hover effect */
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Table hover effects */
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.05);
        }

        /* Custom scrollbar for webkit browsers */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        /* Task priority badges */
        .badge-priority-high { background-color: #dc3545; }
        .badge-priority-medium { background-color: #ffc107; }
        .badge-priority-low { background-color: #28a745; }

        /* Status badges */
        .badge-status-pending { background-color: #ffc107; color: #212529; }
        .badge-status-in_progress { background-color: #17a2b8; }
        .badge-status-completed { background-color: #28a745; }
        .badge-status-cancelled { background-color: #6c757d; }

        /* Attendance status */
        .attendance-present { color: #28a745; }
        .attendance-absent { color: #dc3545; }
        .attendance-late { color: #ffc107; }

        /* Performance indicators */
        .performance-excellent { color: #28a745; }
        .performance-good { color: #17a2b8; }
        .performance-average { color: #ffc107; }
        .performance-poor { color: #dc3545; }

        /* Progress bar animations */
        .progress-bar {
            transition: width 0.6s ease;
        }

        /* Card animations */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Button hover effects */
        .btn {
            transition: all 0.3s ease;
        }

        /* Dropdown animations */
        .dropdown-menu {
            animation: fadeInDown 0.3s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile responsive adjustments */
        @media (max-width: 576px) {
            .sidebar {
                width: 100%;
                margin-left: -100%;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                padding: 1rem;
            }

            .card {
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>
