<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold"><i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Marketing</h2>
            <p class="text-muted">Broadcast messages, send reminders, and engage with your leads via WhatsApp Business API.</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBroadcastModal">
                <i class="fas fa-paper-plane me-1"></i> New Broadcast
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Message Templates</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($templates as $template): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start py-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1 fw-bold"><?php echo h($template['name']); ?></h6>
                                <span class="badge bg-success">Approved</span>
                            </div>
                            <p class="mb-1 text-muted small"><?php echo h($template['content']); ?></p>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-primary me-2">Use Template</button>
                                <button class="btn btn-sm btn-link text-decoration-none">Edit</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Recent Broadcasts</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Campaign</th>
                                    <th>Sent</th>
                                    <th>Delivered</th>
                                    <th>Read</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 fw-bold">Project Launch Jan 2026</td>
                                    <td>1,240</td>
                                    <td>1,210</td>
                                    <td>845</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold">New Year Offer</td>
                                    <td>500</td>
                                    <td>498</td>
                                    <td>320</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">API Configuration</h6>
                    <div class="mb-3">
                        <label class="form-label small">WhatsApp Business Account ID</label>
                        <input type="text" class="form-control form-control-sm" value="WA_847293847" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Phone Number ID</label>
                        <input type="text" class="form-control form-control-sm" value="PH_192837465" readonly>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-sm btn-outline-secondary">Manage API Keys</button>
                    </div>
                </div>
            </div>

            <div class="card bg-success text-white border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">Did you know?</h6>
                    <p class="small mb-0">Personalized messages have a 40% higher read rate compared to generic broadcasts. Use placeholders like {name} to personalize your messages.</p>
                </div>
            </div>
        </div>
    </div>
</div>
