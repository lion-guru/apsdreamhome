<!-- Export Buttons Component -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Management</h5>
            <div class="btn-group">
                <button class="btn btn-outline-primary" id="exportCSV" onclick="exportData('csv')">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <button class="btn btn-outline-success" id="exportExcel" onclick="exportData('excel')">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-outline-danger" id="exportPDF" onclick="exportData('pdf')">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-outline-info" id="printReport" onclick="printReport()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="btn btn-outline-secondary" id="refreshData" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Export Functionality
function exportData(format) {
    const table = document.querySelector('table');
    if (!table) {
        showNotification('No table data found to export', 'warning');
        return;
    }
    
    const fileName = getExportFileName(format);
    
    switch(format) {
        case 'csv':
            exportToCSV(table, fileName);
            break;
        case 'excel':
            exportToExcel(table, fileName);
            break;
        case 'pdf':
            exportToPDF(table, fileName);
            break;
        default:
            showNotification('Export format not supported', 'error');
    }
}

function getExportFileName(format) {
    const pageName = document.title.replace(/[^a-zA-Z0-9]/g, '_');
    const date = new Date().toISOString().split('T')[0];
    const time = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
    
    switch(format) {
        case 'csv':
            return `${pageName}_${date}_${time}.csv`;
        case 'excel':
            return `${pageName}_${date}_${time}.xlsx`;
        case 'pdf':
            return `${pageName}_${date}_${time}.pdf`;
        default:
            return `export_${date}_${time}.${format}`;
    }
}

function exportToCSV(table, filename) {
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    // Get headers
    const headers = Array.from(rows[0].querySelectorAll('th, td')).map(cell => 
        cell.textContent.trim().replace(/,/g, '')
    );
    csv.push(headers.join(','));
    
    // Get data rows
    for (let i = 1; i < rows.length; i++) {
        const row = Array.from(rows[i].querySelectorAll('td, th')).map(cell => 
            cell.textContent.trim().replace(/,/g, '').replace(/\n/g, ' ')
        );
        csv.push(row.join(','));
    }
    
    downloadFile(csv.join('\n'), filename, 'text/csv');
}

function exportToExcel(table, filename) {
    // Simple Excel export using CSV format with BOM
    let csv = '\ufeff'; // BOM for Excel UTF-8
    const rows = table.querySelectorAll('tr');
    
    // Get headers
    const headers = Array.from(rows[0].querySelectorAll('th, td')).map(cell => 
        cell.textContent.trim().replace(/,/g, '')
    );
    csv += headers.join(',') + '\n';
    
    // Get data rows
    for (let i = 1; i < rows.length; i++) {
        const row = Array.from(rows[i].querySelectorAll('td, th')).map(cell => 
            cell.textContent.trim().replace(/,/g, '').replace(/\n/g, ' ')
        );
        csv += row.join(',') + '\n';
    }
    
    downloadFile(csv, filename, 'application/vnd.ms-excel');
}

function exportToPDF(table, filename) {
    // Simple PDF export using window.print
    const originalContent = document.body.innerHTML;
    const tableHTML = table.outerHTML;
    
    document.body.innerHTML = `
        <html>
            <head>
                <title>${document.title}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .header { text-align: center; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>${document.title}</h1>
                    <p>Generated on: ${new Date().toLocaleString()}</p>
                </div>
                ${tableHTML}
            </body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
    location.reload(); // Reload to restore original content
}

function printReport() {
    const originalContent = document.body.innerHTML;
    const mainContent = document.querySelector('main') || document.querySelector('.container-fluid');
    
    if (mainContent) {
        document.body.innerHTML = `
            <html>
                <head>
                    <title>${document.title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .no-print { display: none; }
                        @media print {
                            .no-print { display: none !important; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${document.title}</h1>
                        <p>Generated on: ${new Date().toLocaleString()}</p>
                    </div>
                    ${mainContent.innerHTML}
                </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    } else {
        window.print();
    }
}

function refreshData() {
    // Show loading state
    const refreshBtn = document.getElementById('refreshData');
    const originalHTML = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;
    
    // Simulate data refresh
    setTimeout(() => {
        refreshBtn.innerHTML = originalHTML;
        refreshBtn.disabled = false;
        showNotification('Data refreshed successfully', 'success');
        
        // Trigger any existing refresh functions
        if (typeof refreshPageData === 'function') {
            refreshPageData();
        }
    }, 1500);
}

function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>
