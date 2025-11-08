<?php
// DEPRECATED: This file is nearly identical to customer_footer.php
// Associate footer template - only differs in JavaScript comments
// Use app/views/layouts/customer_footer.php instead and customize as needed
?>
</div>

    <!-- Chart.js for analytics -->
    <?php if (isset($include_charts) && $include_charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <!-- Custom Associate JS -->
    <script>
        // Common functions for associate panel

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

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showAlert('Copied to clipboard!', 'success');
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
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

        // Print page
        function printPage() {
            window.print();
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

        // File upload preview
        function previewFile(input, previewId) {
            const preview = document.getElementById(previewId);
            if (!preview) return;

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;">`;
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

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

        // Pagination helper
        function setupPagination(totalPages, currentPage, callback) {
            const pagination = $('.pagination');
            pagination.empty();

            // Previous button
            pagination.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="${currentPage > 1 ? callback + '(' + (currentPage - 1) + ')' : 'return false'}">Previous</a>
                </li>
            `);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                pagination.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="${callback}(${i})">${i}</a>
                    </li>
                `);
            }

            // Next button
            pagination.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="${currentPage < totalPages ? callback + '(' + (currentPage + 1) + ')' : 'return false'}">Next</a>
                </li>
            `);
        }
    </script>

    <!-- Custom styles for associate panel -->
    <style>
        /* Additional styles specific to associate panel */

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid white;
        }

        .associate-footer {
            margin-left: 260px;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .associate-footer {
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

        /* Custom badges */
        .badge-level-1 { background-color: #007bff; }
        .badge-level-2 { background-color: #28a745; }
        .badge-level-3 { background-color: #ffc107; }
        .badge-level-4 { background-color: #fd7e14; }
        .badge-level-5 { background-color: #dc3545; }

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
