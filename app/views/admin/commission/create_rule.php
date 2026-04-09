

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-coins"></i> Create rule</h2>
                <div>
                    <a href="/admin/commission" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Commission
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Create rule Management - Complete Commission System
                    </div>
                    <form method="POST" action="/admin/commission/create-rule">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rule_name" class="form-label">Rule Name *</label>
                                    <input type="text" class="form-control" id="rule_name" name="rule_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="rule_type" class="form-label">Rule Type *</label>
                                    <select class="form-select" id="rule_type" name="rule_type" required>
                                        <option value="">Select Type</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                        <option value="tiered">Tiered</option>
                                        <option value="hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="min_price" class="form-label">Min Price (₹)</label>
                                    <input type="number" class="form-control" id="min_price" name="min_price">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_price" class="form-label">Max Price (₹)</label>
                                    <input type="number" class="form-control" id="max_price" name="max_price">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="property_type" class="form-label">Property Type</label>
                                    <select class="form-select" id="property_type" name="property_type">
                                        <option value="">All Types</option>
                                        <option value="residential">Residential</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="industrial">Industrial</option>
                                        <option value="land">Land</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                                    <input type="number" class="form-control" id="commission_rate" name="commission_rate" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fixed_amount" class="form-label">Fixed Amount (₹)</label>
                                    <input type="number" class="form-control" id="fixed_amount" name="fixed_amount">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tax_deduction" class="form-label">Tax Deduction (%)</label>
                                    <input type="number" class="form-control" id="tax_deduction" name="tax_deduction" step="0.01" value="18.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <input type="number" class="form-control" id="priority" name="priority" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_terms" class="form-label">Payment Terms</label>
                            <textarea class="form-control" id="payment_terms" name="payment_terms" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active Rule
                                </label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/commission" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Rule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

