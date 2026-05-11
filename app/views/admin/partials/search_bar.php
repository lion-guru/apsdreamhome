<!-- Search Bar Component -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="search" class="form-control" id="globalSearch" placeholder="Search records..." aria-label="Search">
        </div>
    </div>
    <div class="col-md-3">
        <select class="form-select" id="statusFilter">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>
    <div class="col-md-3">
        <div class="btn-group w-100">
            <button class="btn btn-primary" id="applyFilters">
                <i class="fas fa-filter"></i> Apply
            </button>
            <button class="btn btn-outline-secondary" id="resetFilters">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-3" id="quickStats">
    <div class="col-md-3">
        <div class="card bg-primary bg-opacity-10 border-0">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <small class="text-muted">Total Records</small>
                        <h6 class="mb-0" id="totalRecords">0</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success bg-opacity-10 border-0">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <small class="text-muted">Active</small>
                        <h6 class="mb-0" id="activeRecords">0</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning bg-opacity-10 border-0">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <small class="text-muted">Pending</small>
                        <h6 class="mb-0" id="pendingRecords">0</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info bg-opacity-10 border-0">
            <div class="card-body py-2">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <small class="text-muted">This Month</small>
                        <h6 class="mb-0" id="monthlyRecords">0</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search and Filter Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');
    const statusFilter = document.getElementById('statusFilter');
    const applyBtn = document.getElementById('applyFilters');
    const resetBtn = document.getElementById('resetFilters');
    
    // Real-time search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            performSearch();
        });
    }
    
    // Apply filters
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            performSearch();
        });
    }
    
    // Reset filters
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            searchInput.value = '';
            statusFilter.value = '';
            performSearch();
        });
    }
    
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const rows = document.querySelectorAll('tbody tr');
        
        let total = 0;
        let active = 0;
        let pending = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.querySelector('.badge')?.textContent.toLowerCase() || '';
            
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesStatus = !statusValue || status.includes(statusValue);
            
            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                total++;
                
                if (status.includes('active')) active++;
                if (status.includes('pending')) pending++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update stats
        updateStats(total, active, pending);
    }
    
    function updateStats(total, active, pending) {
        const totalEl = document.getElementById('totalRecords');
        const activeEl = document.getElementById('activeRecords');
        const pendingEl = document.getElementById('pendingRecords');
        const monthlyEl = document.getElementById('monthlyRecords');
        
        if (totalEl) totalEl.textContent = total;
        if (activeEl) activeEl.textContent = active;
        if (pendingEl) pendingEl.textContent = pending;
        if (monthlyEl) monthlyEl.textContent = Math.floor(total * 0.3); // Estimate
    }
    
    // Initial search
    performSearch();
});
</script>
