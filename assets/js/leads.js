/**
 * Leads Management System - Frontend JavaScript
 * Handles all client-side functionality for the leads management interface
 */

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const leadsTable = document.getElementById('leadsTable');
    const searchInput = document.getElementById('searchQuery');
    const statusFilter = document.getElementById('statusFilter');
    const sourceFilter = document.getElementById('sourceFilter');
    const assignedToFilter = document.getElementById('assignedToFilter');
    const dateRangeFilter = document.getElementById('dateRangeFilter');
    const refreshBtn = document.getElementById('refreshBtn');
    const addLeadBtn = document.getElementById('addLeadBtn');
    const newLeadBtn = document.getElementById('newLeadBtn');
    const exportBtn = document.getElementById('exportBtn');
    
    // State
    let leads = [];
    let currentPage = 1;
    const rowsPerPage = 10;
    let sortColumn = 'date_created';
    let sortDirection = 'desc';
    
    // Initialize the application
    function init() {
        setupEventListeners();
        loadLeads();
        loadTeamMembers();
        setupTooltips();
    }
    
    // Set up event listeners
    function setupEventListeners() {
        // Search and filter events
        searchInput.addEventListener('input', debounce(handleSearch, 300));
        statusFilter.addEventListener('change', handleFilterChange);
        sourceFilter.addEventListener('change', handleFilterChange);
        assignedToFilter.addEventListener('change', handleFilterChange);
        dateRangeFilter.addEventListener('change', handleFilterChange);
        
        // Button events
        refreshBtn.addEventListener('click', loadLeads);
        if (addLeadBtn) addLeadBtn.addEventListener('click', showAddLeadModal);
        if (newLeadBtn) newLeadBtn.addEventListener('click', showAddLeadModal);
        if (exportBtn) exportBtn.addEventListener('click', exportLeads);
        
        // Table header sorting
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const newSortColumn = header.getAttribute('data-column');
                if (sortColumn === newSortColumn) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = newSortColumn;
                    sortDirection = 'asc';
                }
                renderLeadsTable();
                updateSortIndicator();
            });
        });
    }
    
    // Load leads data from the API
    async function loadLeads() {
        try {
            showLoading(true);
            const response = await fetch('/api/leads.php?action=getAll');
            const data = await response.json();
            
            if (data.success) {
                leads = data.data;
                updateStats(leads);
                renderLeadsTable();
            } else {
                showAlert('Error loading leads: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load leads. Please try again.', 'danger');
        } finally {
            showLoading(false);
        }
    }
    
    // Load team members for assignment dropdown
    async function loadTeamMembers() {
        try {
            const response = await fetch('/api/users.php?action=getTeamMembers');
            const data = await response.json();
            
            if (data.success && data.data) {
                const assignedToFilter = document.getElementById('assignedToFilter');
                if (assignedToFilter) {
                    // Clear existing options except the first one
                    while (assignedToFilter.options.length > 3) {
                        assignedToFilter.remove(3);
                    }
                    
                    // Add team members
                    data.data.forEach(member => {
                        const option = document.createElement('option');
                        option.value = member.id;
                        option.textContent = member.name;
                        assignedToFilter.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error loading team members:', error);
        }
    }
    
    // Render the leads table with pagination
    function renderLeadsTable() {
        if (!leadsTable) return;
        
        // Filter leads based on search and filters
        const filteredLeads = filterLeads(leads);
        
        // Sort leads
        const sortedLeads = sortLeads(filteredLeads);
        
        // Pagination
        const totalPages = Math.ceil(sortedLeads.length / rowsPerPage);
        const startIndex = (currentPage - 1) * rowsPerPage;
        const paginatedLeads = sortedLeads.slice(startIndex, startIndex + rowsPerPage);
        
        // Clear table body
        const tbody = leadsTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (paginatedLeads.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td colspan="10" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No leads found matching your criteria
                    </div>
                </td>
            `;
            tbody.appendChild(row);
            return;
        }
        
        // Add rows for each lead
        paginatedLeads.forEach(lead => {
            const row = document.createElement('tr');
            row.dataset.id = lead.id;
            row.innerHTML = `
                <td>
                    <div class="form-check">
                        <input class="form-check-input lead-checkbox" type="checkbox" value="${lead.id}">
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-xs me-2">
                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                ${lead.first_name ? lead.first_name.charAt(0).toUpperCase() : '?'}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">${escapeHtml(lead.first_name + ' ' + (lead.last_name || ''))}</h6>
                            <small class="text-muted">${escapeHtml(lead.company || 'No Company')}</small>
                        </div>
                    </div>
                </td>
                <td>${escapeHtml(lead.email || 'N/A')}</td>
                <td>${escapeHtml(lead.phone || 'N/A')}</td>
                <td>${formatDate(lead.date_created)}</td>
                <td>${formatCurrency(lead.estimated_value)}</td>
                <td>${getStatusBadge(lead.status)}</td>
                <td>${escapeHtml(lead.source || 'N/A')}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-xs me-2">
                            <span class="avatar-title rounded-circle bg-soft-info text-info">
                                ${lead.assigned_to_name ? lead.assigned_to_name.charAt(0).toUpperCase() : 'U'}
                            </span>
                        </div>
                        <span>${escapeHtml(lead.assigned_to_name || 'Unassigned')}</span>
                    </div>
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item view-lead" href="#" data-id="${lead.id}"><i class="bi bi-eye me-2"></i>View</a></li>
                            <li><a class="dropdown-item edit-lead" href="#" data-id="${lead.id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li><a class="dropdown-item change-status" href="#" data-id="${lead.id}"><i class="bi bi-arrow-repeat me-2"></i>Change Status</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger delete-lead" href="#" data-id="${lead.id}"><i class="bi bi-trash me-2"></i>Delete</a></li>
                        </ul>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        // Update pagination
        updatePagination(filteredLeads.length);
        
        // Attach event listeners to action buttons
        attachLeadActionHandlers();
    }
    
    // Filter leads based on search and filter criteria
    function filterLeads(leads) {
        const searchTerm = searchInput.value.toLowerCase();
        const status = statusFilter.value;
        const source = sourceFilter.value;
        const assignedTo = assignedToFilter.value;
        const dateRange = dateRangeFilter.value;
        
        return leads.filter(lead => {
            // Search term filter
            if (searchTerm) {
                const searchableText = [
                    lead.first_name,
                    lead.last_name,
                    lead.email,
                    lead.phone,
                    lead.company,
                    lead.notes
                ].join(' ').toLowerCase();
                
                if (!searchableText.includes(searchTerm)) {
                    return false;
                }
            }
            
            // Status filter
            if (status && lead.status !== status) {
                return false;
            }
            
            // Source filter
            if (source && lead.source !== source) {
                return false;
            }
            
            // Assigned to filter
            if (assignedTo === 'me' && lead.assigned_to_id !== '<?= $user_id ?>') {
                return false;
            } else if (assignedTo === 'unassigned' && lead.assigned_to_id) {
                return false;
            } else if (assignedTo && assignedTo !== 'me' && assignedTo !== 'unassigned' && lead.assigned_to_id !== assignedTo) {
                return false;
            }
            
            // Date range filter
            if (dateRange) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const leadDate = new Date(lead.date_created);
                leadDate.setHours(0, 0, 0, 0);
                
                const diffTime = Math.abs(today - leadDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                switch (dateRange) {
                    case 'today':
                        if (leadDate.getTime() !== today.getTime()) return false;
                        break;
                    case 'yesterday':
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        if (leadDate.getTime() !== yesterday.getTime()) return false;
                        break;
                    case 'this_week':
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        if (leadDate < weekStart) return false;
                        break;
                    case 'last_week':
                        const lastWeekStart = new Date(today);
                        lastWeekStart.setDate(today.getDate() - today.getDay() - 7);
                        const lastWeekEnd = new Date(today);
                        lastWeekEnd.setDate(today.getDate() - today.getDay());
                        if (leadDate < lastWeekStart || leadDate >= lastWeekEnd) return false;
                        break;
                    case 'this_month':
                        if (leadDate.getMonth() !== today.getMonth() || leadDate.getFullYear() !== today.getFullYear()) return false;
                        break;
                    case 'last_month':
                        const lastMonth = new Date(today);
                        lastMonth.setMonth(lastMonth.getMonth() - 1);
                        if (leadDate.getMonth() !== lastMonth.getMonth() || leadDate.getFullYear() !== lastMonth.getFullYear()) return false;
                        break;
                }
            }
            
            return true;
        });
    }
    
    // Sort leads based on current sort column and direction
    function sortLeads(leads) {
        return [...leads].sort((a, b) => {
            let valueA = a[sortColumn];
            let valueB = b[sortColumn];
            
            // Handle potential undefined or null values
            if (valueA === undefined || valueA === null) valueA = '';
            if (valueB === undefined || valueB === null) valueB = '';
            
            // Convert to string for comparison if not already
            if (typeof valueA !== 'string') valueA = String(valueA);
            if (typeof valueB !== 'string') valueB = String(valueB);
            
            // Special handling for dates
            if (sortColumn.includes('date') || sortColumn.includes('created') || sortColumn.includes('updated')) {
                const dateA = new Date(valueA);
                const dateB = new Date(valueB);
                return sortDirection === 'asc' ? dateA - dateB : dateB - dateA;
            }
            
            // Numeric comparison for numeric fields
            if (sortColumn.includes('value') || sortColumn.includes('amount') || sortColumn.includes('price')) {
                const numA = parseFloat(valueA) || 0;
                const numB = parseFloat(valueB) || 0;
                return sortDirection === 'asc' ? numA - numB : numB - numA;
            }
            
            // String comparison for text fields
            return sortDirection === 'asc' 
                ? valueA.localeCompare(valueB) 
                : valueB.localeCompare(valueA);
        });
    }
    
    // Update pagination controls
    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / rowsPerPage);
        const pagination = document.querySelector('.pagination');
        
        if (!pagination) return;
        
        let paginationHtml = `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        // First page
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item ${1 === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Middle pages
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            paginationHtml += `
                <li class="page-item ${totalPages === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        pagination.innerHTML = paginationHtml;
        
        // Add event listeners to pagination links
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.getAttribute('data-page'));
                if (!isNaN(page) && page !== currentPage) {
                    currentPage = page;
                    renderLeadsTable();
                    // Scroll to top of table
                    leadsTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Update items per page dropdown
        const itemsPerPageSelect = document.getElementById('itemsPerPage');
        if (itemsPerPageSelect) {
            itemsPerPageSelect.value = rowsPerPage;
            itemsPerPageSelect.addEventListener('change', (e) => {
                rowsPerPage = parseInt(e.target.value);
                currentPage = 1; // Reset to first page
                renderLeadsTable();
            });
        }
    }
    
    // Update stats cards
    function updateStats(leads) {
        const now = new Date();
        const thisMonth = now.getMonth();
        const thisYear = now.getFullYear();
        
        // Total leads
        document.getElementById('totalLeads').textContent = leads.length;
        
        // New this month
        const newThisMonth = leads.filter(lead => {
            const leadDate = new Date(lead.date_created);
            return leadDate.getMonth() === thisMonth && leadDate.getFullYear() === thisYear;
        }).length;
        document.getElementById('newLeads').textContent = newThisMonth;
        
        // In progress (any status that's not new, closed won, or closed lost)
        const inProgress = leads.filter(lead => 
            lead.status !== 'new' && 
            lead.status !== 'closed_won' && 
            lead.status !== 'closed_lost'
        ).length;
        document.getElementById('inProgressLeads').textContent = inProgress;
        
        // At risk (leads with no activity in the last 7 days)
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(now.getDate() - 7);
        
        const atRisk = leads.filter(lead => {
            if (!lead.last_activity_date) return false;
            const lastActivity = new Date(lead.last_activity_date);
            return lastActivity < sevenDaysAgo && 
                   lead.status !== 'closed_won' && 
                   lead.status !== 'closed_lost';
        }).length;
        document.getElementById('atRiskLeads').textContent = atRisk;
    }
    
    // Handle search input
    function handleSearch() {
        currentPage = 1; // Reset to first page when searching
        renderLeadsTable();
    }
    
    // Handle filter changes
    function handleFilterChange() {
        currentPage = 1; // Reset to first page when filters change
        renderLeadsTable();
    }
    
    // Show add lead modal
    function showAddLeadModal() {
        // This would be implemented to show a modal for adding a new lead
        // The actual implementation would depend on your modal library
        const modal = new bootstrap.Modal(document.getElementById('addLeadModal'));
        modal.show();
        
        // Reset form
        const form = document.getElementById('addLeadForm');
        if (form) form.reset();
        
        // Set today's date as default for date fields
        const today = new Date().toISOString().split('T')[0];
        const dateFields = form.querySelectorAll('input[type="date"]');
        dateFields.forEach(field => {
            if (!field.value) field.value = today;
        });
    }
    
    // Export leads
    function exportLeads() {
        // Filter and prepare data for export
        const filteredLeads = filterLeads(leads);
        const exportData = filteredLeads.map(lead => ({
            'First Name': lead.first_name,
            'Last Name': lead.last_name,
            'Email': lead.email,
            'Phone': lead.phone,
            'Company': lead.company,
            'Status': formatStatus(lead.status),
            'Source': lead.source,
            'Value': formatCurrency(lead.estimated_value),
            'Created Date': formatDate(lead.date_created),
            'Last Contact': lead.last_contact_date ? formatDate(lead.last_contact_date) : 'Never',
            'Assigned To': lead.assigned_to_name || 'Unassigned',
            'Notes': lead.notes || ''
        }));
        
        // Convert to CSV
        const csv = convertToCSV(exportData);
        
        // Create download link
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `leads_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Convert array of objects to CSV
    function convertToCSV(data) {
        if (data.length === 0) return '';
        
        const headers = Object.keys(data[0]);
        const rows = data.map(row => 
            headers.map(fieldName => {
                // Escape double quotes and wrap in quotes
                const value = String(row[fieldName] || '').replace(/"/g, '""');
                return `"${value}"`;
            }).join(',')
        );
        
        return [headers.join(','), ...rows].join('\n');
    }
    
    // Attach event handlers to lead action buttons
    function attachLeadActionHandlers() {
        // View lead
        document.querySelectorAll('.view-lead').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const leadId = btn.getAttribute('data-id');
                viewLead(leadId);
            });
        });
        
        // Edit lead
        document.querySelectorAll('.edit-lead').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const leadId = btn.getAttribute('data-id');
                editLead(leadId);
            });
        });
        
        // Delete lead
        document.querySelectorAll('.delete-lead').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const leadId = btn.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
                    deleteLead(leadId);
                }
            });
        });
        
        // Change status
        document.querySelectorAll('.change-status').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const leadId = btn.getAttribute('data-id');
                showStatusChangeModal(leadId);
            });
        });
    }
    
    // View lead details
    async function viewLead(leadId) {
        try {
            showLoading(true);
            const response = await fetch(`/api/leads.php?action=get&id=${leadId}`);
            const data = await response.json();
            
            if (data.success) {
                const lead = data.data;
                // Populate modal with lead data
                document.getElementById('viewLeadName').textContent = `${lead.first_name} ${lead.last_name || ''}`.trim();
                document.getElementById('viewLeadEmail').textContent = lead.email || 'N/A';
                document.getElementById('viewLeadPhone').textContent = lead.phone || 'N/A';
                document.getElementById('viewLeadCompany').textContent = lead.company || 'N/A';
                document.getElementById('viewLeadStatus').innerHTML = getStatusBadge(lead.status);
                document.getElementById('viewLeadSource').textContent = lead.source || 'N/A';
                document.getElementById('viewLeadValue').textContent = formatCurrency(lead.estimated_value);
                document.getElementById('viewLeadCreated').textContent = formatDate(lead.date_created);
                document.getElementById('viewLeadLastContact').textContent = lead.last_contact_date ? formatDate(lead.last_contact_date) : 'Never';
                document.getElementById('viewLeadAssignedTo').textContent = lead.assigned_to_name || 'Unassigned';
                document.getElementById('viewLeadNotes').textContent = lead.notes || 'No notes available.';
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('viewLeadModal'));
                modal.show();
            } else {
                showAlert('Error loading lead details: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load lead details. Please try again.', 'danger');
        } finally {
            showLoading(false);
        }
    }
    
    // Edit lead
    async function editLead(leadId) {
        try {
            showLoading(true);
            const response = await fetch(`/api/leads.php?action=get&id=${leadId}`);
            const data = await response.json();
            
            if (data.success) {
                const lead = data.data;
                const form = document.getElementById('editLeadForm');
                
                // Populate form fields
                form.elements['id'].value = lead.id;
                form.elements['first_name'].value = lead.first_name || '';
                form.elements['last_name'].value = lead.last_name || '';
                form.elements['email'].value = lead.email || '';
                form.elements['phone'].value = lead.phone || '';
                form.elements['company'].value = lead.company || '';
                form.elements['status'].value = lead.status || 'new';
                form.elements['source'].value = lead.source || '';
                form.elements['estimated_value'].value = lead.estimated_value || '';
                form.elements['assigned_to'].value = lead.assigned_to_id || '';
                form.elements['notes'].value = lead.notes || '';
                
                // Format dates for date inputs
                if (lead.last_contact_date) {
                    const lastContactDate = new Date(lead.last_contact_date);
                    form.elements['last_contact_date'].value = lastContactDate.toISOString().split('T')[0];
                } else {
                    form.elements['last_contact_date'].value = '';
                }
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editLeadModal'));
                modal.show();
                
                // Initialize any form plugins
                if (typeof $.fn.select2 === 'function') {
                    $(form.elements['status']).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#editLeadModal')
                    });
                    
                    $(form.elements['assigned_to']).select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#editLeadModal')
                    });
                }
            } else {
                showAlert('Error loading lead data: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to load lead data. Please try again.', 'danger');
        } finally {
            showLoading(false);
        }
    }
    
    // Delete lead
    async function deleteLead(leadId) {
        try {
            showLoading(true);
            const response = await fetch(`/api/leads.php?action=delete&id=${leadId}`, {
                method: 'DELETE'
            });
            const data = await response.json();
            
            if (data.success) {
                showAlert('Lead deleted successfully!', 'success');
                // Remove the lead from the UI
                const row = document.querySelector(`tr[data-id="${leadId}"]`);
                if (row) row.remove();
                // Reload leads to update stats
                loadLeads();
            } else {
                showAlert('Error deleting lead: ' + data.message, 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Failed to delete lead. Please try again.', 'danger');
        } finally {
            showLoading(false);
        }
    }
    
    // Show status change modal
    function showStatusChangeModal(leadId) {
        const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
        const form = document.getElementById('changeStatusForm');
        
        // Set the lead ID in the form
        form.elements['lead_id'].value = leadId;
        
        // Reset and show the modal
        form.reset();
        modal.show();
    }
    
    // Format status badge
    function getStatusBadge(status) {
        const statusMap = {
            'new': { class: 'bg-soft-primary text-primary', label: 'New' },
            'contacted': { class: 'bg-soft-info text-info', label: 'Contacted' },
            'qualified': { class: 'bg-soft-success text-success', label: 'Qualified' },
            'proposal': { class: 'bg-soft-warning text-warning', label: 'Proposal Sent' },
            'negotiation': { class: 'bg-soft-purple text-purple', label: 'Negotiation' },
            'closed_won': { class: 'bg-soft-success text-success', label: 'Closed Won' },
            'closed_lost': { class: 'bg-soft-danger text-danger', label: 'Closed Lost' }
        };
        
        const statusInfo = statusMap[status] || { class: 'bg-soft-secondary text-secondary', label: status };
        return `<span class="badge ${statusInfo.class} rounded-pill">${statusInfo.label}</span>`;
    }
    
    // Format date
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Format currency
    function formatCurrency(amount) {
        if (amount === null || amount === undefined) return 'N/A';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }
    
    // Format status for display
    function formatStatus(status) {
        const statusMap = {
            'new': 'New',
            'contacted': 'Contacted',
            'qualified': 'Qualified',
            'proposal': 'Proposal Sent',
            'negotiation': 'Negotiation',
            'closed_won': 'Closed Won',
            'closed_lost': 'Closed Lost'
        };
        
        return statusMap[status] || status;
    }
    
    // Show loading state
    function showLoading(show) {
        const loadingElement = document.getElementById('loadingOverlay');
        if (loadingElement) {
            loadingElement.style.display = show ? 'flex' : 'none';
        }
    }
    
    // Show alert message
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Add to alerts container if it exists, otherwise at the top of the page
        const alertsContainer = document.getElementById('alertsContainer') || document.querySelector('.container-fluid');
        if (alertsContainer) {
            alertsContainer.insertAdjacentHTML('afterbegin', alertHtml);
        }
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Setup tooltips
    function setupTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Debounce function to limit how often a function can be called
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
    
    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Initialize the application
    init();
});
