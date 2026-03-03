
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Key Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="bi bi-key-fill me-2"></i>Unified Key Management</h1>
                    <div>
                        <button class="btn btn-success me-2" onclick="loadStats()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMcpKeyModal">
                            <i class="bi bi-plus-circle me-1"></i>Add MCP Key
                        </button>
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addUserKeyModal">
                            <i class="bi bi-person-plus me-1"></i>Create User API Key
                        </button>
                    </div>
                </div>
                
                <!-- System Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Keys</h5>
                                <h2 id="totalKeys">-</h2>
                                <small>All system keys</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Keys</h5>
                                <h2 id="activeKeys">-</h2>
                                <small>Currently active</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">MCP Keys</h5>
                                <h2 id="mcpKeys">-</h2>
                                <small>Service keys</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">User Keys</h5>
                                <h2 id="userKeys">-</h2>
                                <small>API access keys</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="keyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="mcp-tab" data-bs-toggle="tab" data-bs-target="#mcp-keys" type="button">
                            <i class="bi bi-gear me-1"></i>MCP/Service Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-keys" type="button">
                            <i class="bi bi-person me-1"></i>User API Keys
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="integration-tab" data-bs-toggle="tab" data-bs-target="#integration" type="button">
                            <i class="bi bi-link-45deg me-1"></i>Integration Status
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="keyTabsContent">
                    <!-- MCP Keys Tab -->
                    <div class="tab-pane fade show active" id="mcp-keys" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">MCP/Service Keys</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="mcpKeysTable">
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
                                        <tbody id="mcpKeysTableBody">
                                            <!-- MCP keys will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Keys Tab -->
                    <div class="tab-pane fade" id="user-keys" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">User API Keys</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="userKeysTable">
                                        <thead>
                                            <tr>
                                                <th>API Key</th>
                                                <th>Name</th>
                                                <th>User ID</th>
                                                <th>Permissions</th>
                                                <th>Rate Limit</th>
                                                <th>Status</th>
                                                <th>Last Used</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userKeysTableBody">
                                            <!-- User keys will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Integration Tab -->
                    <div class="tab-pane fade" id="integration" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">System Integration Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>MCP Servers</h6>
                                        <div id="mcpServersStatus">
                                            <!-- MCP server status will be loaded here -->
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>API System</h6>
                                        <div id="apiSystemStatus">
                                            <!-- API system status will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add MCP Key Modal -->
    <div class="modal fade" id="addMcpKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add MCP/Service Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMcpKeyForm">
                        <div class="mb-3">
                            <label for="mcpKeyName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="mcpKeyName" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpKeyValue" class="form-label">Key Value</label>
                            <input type="password" class="form-control" id="mcpKeyValue" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpKeyType" class="form-label">Key Type</label>
                            <select class="form-select" id="mcpKeyType" required>
                                <option value="api_key">API Key</option>
                                <option value="token">Token</option>
                                <option value="password">Password</option>
                                <option value="certificate">Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mcpServiceName" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="mcpServiceName" required>
                        </div>
                        <div class="mb-3">
                            <label for="mcpDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="mcpDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addMcpKey()">Add Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Key Modal -->
    <div class="modal fade" id="addUserKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create User API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserKeyForm">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Key Name</label>
                            <input type="text" class="form-control" id="userName" required>
                        </div>
                        <div class="mb-3">
                            <label for="userId" class="form-label">User ID</label>
                            <input type="number" class="form-control" id="userId" required>
                        </div>
                        <div class="mb-3">
                            <label for="userRateLimit" class="form-label">Rate Limit (per hour)</label>
                            <input type="number" class="form-control" id="userRateLimit" value="1000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="properties" id="perm_properties">
                                <label class="form-check-label" for="perm_properties">Properties</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="leads" id="perm_leads">
                                <label class="form-check-label" for="perm_leads">Leads</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="analytics" id="perm_analytics">
                                <label class="form-check-label" for="perm_analytics">Analytics</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createUserKey()">Create Key</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load system data
        function loadStats() {
            fetch("unified_keys_api.php?action=stats")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStats(data.stats);
                        loadMcpKeys();
                        loadUserKeys();
                        loadIntegrationStatus();
                    }
                })
                .catch(error => console.error("Error loading stats:", error));
        }
        
        function updateStats(stats) {
            document.getElementById("totalKeys").textContent = stats.total_keys || 0;
            document.getElementById("activeKeys").textContent = stats.active_keys || 0;
            document.getElementById("mcpKeys").textContent = stats.mcp_keys?.total || 0;
            document.getElementById("userKeys").textContent = stats.user_keys?.total || 0;
        }
        
        function loadMcpKeys() {
            fetch("unified_keys_api.php?action=mcp_keys")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMcpKeysTable(data.keys);
                    }
                })
                .catch(error => console.error("Error loading MCP keys:", error));
        }
        
        function loadUserKeys() {
            fetch("unified_keys_api.php?action=user_keys")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateUserKeysTable(data.keys);
                    }
                })
                .catch(error => console.error("Error loading user keys:", error));
        }
        
        function loadIntegrationStatus() {
            fetch("unified_keys_api.php?action=integration")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateIntegrationStatus(data.integration);
                    }
                })
                .catch(error => console.error("Error loading integration status:", error));
        }
        
        function updateMcpKeysTable(keys) {
            const tbody = document.getElementById("mcpKeysTableBody");
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
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey('${key.key_name}', 'mcp')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editKey('${key.key_name}', 'mcp')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function updateUserKeysTable(keys) {
            const tbody = document.getElementById("userKeysTableBody");
            tbody.innerHTML = "";
            
            keys.forEach(key => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><code>${key.api_key}</code></td>
                    <td>${key.name}</td>
                    <td>${key.user_id}</td>
                    <td>${key.permissions ? JSON.parse(key.permissions).join(", ") : "-"}</td>
                    <td>${key.rate_limit || "-"}</td>
                    <td>
                        <span class="badge bg-${key.status === "active" ? "success" : "danger"}">
                            ${key.status}
                        </span>
                    </td>
                    <td>${key.last_used_at ? new Date(key.last_used_at).toLocaleString() : "Never"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewKey('${key.api_key}', 'user')">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="revokeKey('${key.api_key}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
        
        function updateIntegrationStatus(integration) {
            const mcpStatus = document.getElementById("mcpServersStatus");
            mcpStatus.innerHTML = integration.mcp_servers.map(server => 
                `<div class="mb-2">
                    <span class="badge bg-${server.status === "active" ? "success" : "secondary"} me-2">
                        ${server.name}
                    </span>
                    <small>${server.description}</small>
                </div>`
            ).join("");
            
            const apiStatus = document.getElementById("apiSystemStatus");
            apiStatus.innerHTML = integration.api_system.map(component => 
                `<div class="mb-2">
                    <span class="badge bg-${component.status === "active" ? "success" : "warning"} me-2">
                        ${component.name}
                    </span>
                    <small>${component.description}</small>
                </div>`
            ).join("");
        }
        
        function addMcpKey() {
            const form = document.getElementById("addMcpKeyForm");
            const formData = new FormData(form);
            
            fetch("unified_keys_api.php?action=add_mcp_key", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addMcpKeyModal")).hide();
                    form.reset();
                    loadStats();
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error adding MCP key:", error));
        }
        
        function createUserKey() {
            const form = document.getElementById("addUserKeyForm");
            const formData = new FormData(form);
            
            // Add permissions
            const permissions = [];
            document.querySelectorAll("#addUserKeyModal input[type=checkbox]:checked").forEach(cb => {
                permissions.push(cb.value);
            });
            formData.append("permissions", JSON.stringify(permissions));
            
            fetch("unified_keys_api.php?action=create_user_key", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById("addUserKeyModal")).hide();
                    form.reset();
                    loadStats();
                    alert("API Key created: " + data.api_key);
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error creating user key:", error));
        }
        
        // Load data on page load
        document.addEventListener("DOMContentLoaded", loadStats);
    </script>
</body>
</html>