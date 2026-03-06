
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="bi bi-key-fill me-2"></i>API Key Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                        <i class="bi bi-plus-circle me-1"></i>Add New Key
                    </button>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Keys</h5>
                                <h2 id="totalKeys">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Keys</h5>
                                <h2 id="activeKeys">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Services</h5>
                                <h2 id="totalServices">-</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Usage</h5>
                                <h2 id="totalUsage">-</h2>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Keys Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Stored API Keys</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="keysTable">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Key Name</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Usage</th>
                                        <th>Last Used</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="keysTableBody">
                                    <!-- Keys will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Key Modal -->
    <div class="modal fade" id="addKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addKeyForm">
                        <div class="mb-3">
                            <label for="keyName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="keyName" required>
                        </div>
                        <div class="mb-3">
                            <label for="keyValue" class="form-label">Key Value</label>
                            <input type="password" class="form-control" id="keyValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="keyType" class="form-label">Key Type</label>
                            <select class="form-select" id="keyType" required>
                                <option value="api_key">API Key</option>
                                <option value="token">Token</option>
                                <option value="password">Password</option>
                                <option value="certificate">Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="serviceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addKey()">Add Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load keys from server
        function loadKeys() {
            fetch("api_keys.php?action=list")
                .then(response => response.json())
                .then(data => {
                    updateStats(data.stats);
                    updateTable(data.keys);
                })
                .catch(error => console.error("Error loading keys:", error));
        }
        
        function updateStats(stats) {
            document.getElementById("totalKeys").textContent = stats.totalKeys || 0;
            document.getElementById("activeKeys").textContent = stats.activeKeys || 0;
            document.getElementById("totalServices").textContent = stats.totalServices || 0;
            document.getElementById("totalUsage").textContent = stats.totalUsage || 0;
        }
        
        function updateTable(keys) {
            const tbody = document.getElementById("keysTableBody");
            tbody.innerHTML = "";
            
            keys.forEach(key => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${key.service_name}</td>
                    <td><code>${key.key_name}</code></td>
                    <td><span class="badge bg-info">${key.key_type}</span></td>
                    <td>${key.description || "-"}</td>
                    <td>
                        <span class="badge bg-${key.is_active ? "success" : "danger"}">
                            ${key.is_active ? "Active" : "Inactive"}
                        </span>
                    </td>
                    <td>${key.usage_count || 0}</td>
                    <td>${key.last_used_at ? new Date(key.last_used_at).toLocaleString() : "Never"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey('${key.key_name}')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editKey('${key.key_name}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deactivateKey('${key.key_name}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function addKey() {
            const form = document.getElementById("addKeyForm");
            const formData = new FormData(form);
            
            fetch("api_keys.php?action=add", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addKeyModal")).hide();
                    form.reset();
                    loadKeys();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error adding key:", error));
        }
        
        // Load keys on page load
        document.addEventListener("DOMContentLoaded", loadKeys);
    </script>
</body>
</html>