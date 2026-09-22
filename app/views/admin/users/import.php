<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>

<!-- Users Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Import Users</h1>
        <p class="text-muted mb-0">Bulk import users from CSV file</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
        <a href="<?= BASE_URL ?>/admin/users/export" class="btn btn-outline-primary">
            <i class="fas fa-file-csv me-2"></i>Export Template CSV
        </a>
    </div>
</div>

<!-- Instructions Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>CSV Format Instructions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Required Columns (exact names):</h6>
                <ul class="mb-0">
                    <li><code>name</code> - Full name</li>
                    <li><code>email</code> - Valid email address (unique)</li>
                    <li><code>password</code> - Initial password</li>
                    <li><code>role</code> - One of: customer, associate, agent, employee, telecaller, user</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Optional Columns:</h6>
                <ul class="mb-0">
                    <li><code>phone</code> - Phone number</li>
                    <li><code>city</code> - City</li>
                    <li><code>occupation</code> - Occupation</li>
                </ul>
            </div>
        </div>
        <hr>
        <h6>Example CSV:</h6>
        <pre class="bg-light p-3 rounded small mb-0"><code>name,email,password,role,phone,city,occupation
"John Doe","john@example.com","Pass@123","customer","9876543210","Mumbai","Engineer"
"Jane Smith","jane@example.com","Pass@123","associate","9876543211","Delhi","Realtor"
"Bob Wilson","bob@example.com","Pass@123","agent","9876543212","Bangalore","Agent"</code></pre>
    </div>
</div>

<!-- Import Form -->
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload CSV File</h5>
    </div>
    <div class="card-body">
        <form id="importForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            
            <div class="mb-3">
                <label class="form-label">Select CSV File</label>
                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                <div class="form-text">Maximum file size: 2MB. UTF-8 encoding recommended.</div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skip_duplicates" value="1" checked>
                    <label class="form-check-label" for="skip_duplicates">
                        Skip duplicate emails (don't fail on existing emails)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="send_welcome" id="send_welcome" value="1">
                    <label class="form-check-label" for="send_welcome">
                        Send welcome email to imported users
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="importBtn">
                    <i class="fas fa-upload me-2"></i>Import Users
                </button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Progress & Results -->
<div id="importProgress" class="card border-0 shadow-sm mt-3 d-none">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-spinner fa-spin me-2"></i>Importing...</h5>
    </div>
    <div class="card-body">
        <div class="progress mb-3" style="height: 8px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 0%"></div>
        </div>
        <p class="text-muted small mb-0" id="progressText">Processing...</p>
    </div>
</div>

<div id="importResults" class="card border-0 shadow-sm mt-3 d-none">
    <div class="card-header" id="resultsHeader">
        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Import Complete</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4 text-center">
                <div class="display-6 text-success" id="importedCount">0</div>
                <small class="text-muted">Imported</small>
            </div>
            <div class="col-md-4 text-center">
                <div class="display-6 text-danger" id="errorsCount">0</div>
                <small class="text-muted">Errors</small>
            </div>
            <div class="col-md-4 text-center">
                <div class="display-6 text-info" id="totalRows">0</div>
                <small class="text-muted">Total Rows</small>
            </div>
        </div>
        <div id="errorsList" class="d-none">
            <h6>Errors:</h6>
            <ul class="small text-danger" id="errorsUl"></ul>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-primary">View Users</a>
            <button class="btn btn-outline-secondary" onclick="location.reload()">Import More</button>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';

document.getElementById('importForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('csv_file');
    if (!fileInput.files.length) {
        showToast('Please select a CSV file', 'warning');
        return;
    }
    
    const formData = new FormData(this);
    const btn = document.getElementById('importBtn');
    const progressCard = document.getElementById('importProgress');
    const resultsCard = document.getElementById('importResults');
    const formCard = this.closest('.card');
    
    // Show progress
    btn.disabled = true;
    formCard.style.opacity = '0.5';
    progressCard.classList.remove('d-none');
    resultsCard.classList.add('d-none');
    
    try {
        const response = await fetch(BASE_URL + '/admin/users/import', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        progressCard.classList.add('d-none');
        formCard.style.opacity = '1';
        
        if (data.success) {
            resultsCard.classList.remove('d-none');
            document.getElementById('resultsHeader').className = 'card-header bg-success text-white';
            
            document.getElementById('importedCount').textContent = data.imported || 0;
            document.getElementById('errorsCount').textContent = data.errors?.length || 0;
            document.getElementById('totalRows').textContent = (data.imported || 0) + (data.errors?.length || 0);
            
            if (data.errors?.length) {
                document.getElementById('errorsList').classList.remove('d-none');
                const ul = document.getElementById('errorsUl');
                ul.innerHTML = '';
                data.errors.slice(0, 20).forEach(err => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    ul.appendChild(li);
                });
                if (data.errors.length > 20) {
                    const li = document.createElement('li');
                    li.textContent = `... and ${data.errors.length - 20} more errors`;
                    li.className = 'text-muted';
                    ul.appendChild(li);
                }
            }
            
            showToast(data.message, 'success');
        } else {
            resultsCard.classList.remove('d-none');
            document.getElementById('resultsHeader').className = 'card-header bg-danger text-white';
            document.getElementById('importedCount').textContent = '0';
            document.getElementById('errorsCount').textContent = '1';
            document.getElementById('totalRows').textContent = '1';
            document.getElementById('errorsList').classList.remove('d-none');
            document.getElementById('errorsUl').innerHTML = '<li>' + (data.message || 'Import failed') + '</li>';
            showToast(data.message || 'Import failed', 'danger');
        }
    } catch (error) {
        progressCard.classList.add('d-none');
        formCard.style.opacity = '1';
        showToast('Network error: ' + error.message, 'danger');
    } finally {
        btn.disabled = false;
    }
});
</script>