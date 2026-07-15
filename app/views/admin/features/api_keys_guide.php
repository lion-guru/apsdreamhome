<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-key me-2 text-danger"></i>API Keys Guide</h4>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5>Getting Started</h5>
            <p>Use API keys to integrate APS Dream Home with your applications.</p>
            <hr>
            <h6>Authentication</h6>
            <pre class="bg-light p-3 rounded"><code>Authorization: Bearer YOUR_API_KEY</code></pre>
            <h6>Base URL</h6>
            <pre class="bg-light p-3 rounded"><code><?= BASE_URL ?? 'https://apsdreamhome.com' ?>/api/v2</code></pre>
            <h6>Example Request</h6>
            <pre class="bg-light p-3 rounded"><code>curl -H "Authorization: Bearer YOUR_API_KEY" <?= BASE_URL ?? 'https://apsdreamhome.com' ?>/api/v2/properties</code></pre>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Available Endpoints</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Method</th><th>Endpoint</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td>GET</td><td>/properties</td><td>List all properties</td></tr>
                        <tr><td>GET</td><td>/properties/{id}</td><td>Property details</td></tr>
                        <tr><td>GET</td><td>/colonies</td><td>List all colonies</td></tr>
                        <tr><td>GET</td><td>/plots</td><td>List all plots</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>