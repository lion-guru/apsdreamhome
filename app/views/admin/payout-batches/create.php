<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="/admin/payout-batches" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <h2 style="color:#e0e0e0;"><i class="fas fa-plus-circle me-2" style="color:#28a745;"></i> Create Payout Batch</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="/admin/payout-batches/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                    <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                        <h5 style="color:#28a745;margin:0;"><i class="fas fa-info-circle me-2"></i> Batch Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label style="color:#ccc;">Batch Name *</label>
                                <input type="text" name="batch_name" class="form-control" required
                                       placeholder="e.g. July 2026 Commission Payout"
                                       style="background:#1a1a1a;border:#444;color:#ccc;">
                            </div>
                            <div class="col-md-6">
                                <label style="color:#ccc;">Batch Type</label>
                                <select name="batch_type" class="form-select" style="background:#1a1a1a;border:#444;color:#ccc;">
                                    <option value="commission">Commission Payout</option>
                                    <option value="salary">Salary</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="refund">Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label style="color:#ccc;">Period From</label>
                                <input type="date" name="period_from" class="form-control" style="background:#1a1a1a;border:#444;color:#ccc;">
                            </div>
                            <div class="col-md-6">
                                <label style="color:#ccc;">Period To</label>
                                <input type="date" name="period_to" class="form-control" style="background:#1a1a1a;border:#444;color:#ccc;">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label style="color:#ccc;">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this batch..."
                                      style="background:#1a1a1a;border:#444;color:#ccc;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Auto-Populate Option -->
                <div class="card mb-4" style="background:rgba(40,167,69,0.05);border:1px solid rgba(40,167,69,0.3);">
                    <div class="card-header" style="background:rgba(40,167,69,0.1);border-bottom:1px solid rgba(40,167,69,0.3);">
                        <h5 style="color:#28a745;margin:0;">
                            <input type="checkbox" name="auto_populate" value="1" id="autoPopulate" class="me-2">
                            <label for="autoPopulate" style="color:#28a745;margin:0;cursor:pointer;">
                                <i class="fas fa-magic me-2"></i> Auto-populate with pending commission entries
                            </label>
                        </h5>
                    </div>
                    <div class="card-body" id="populateOptions" style="display:none;">
                        <div class="row">
                            <div class="col-md-4">
                                <label style="color:#ccc;">Commission Type (optional filter)</label>
                                <select name="populate_type" class="form-select" style="background:#1a1a1a;border:#444;color:#ccc;">
                                    <option value="">All Types</option>
                                    <option value="direct_sale">Direct Sale</option>
                                    <option value="override">Override</option>
                                    <option value="rank_bonus">Rank Bonus</option>
                                    <option value="level_bonus">Level Bonus</option>
                                    <option value="matching_bonus">Matching Bonus</option>
                                    <option value="generation_bonus">Generation Bonus</option>
                                    <option value="royalty_pool">Royalty Pool</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="color:#ccc;">Date From (optional)</label>
                                <input type="date" name="populate_from" class="form-control" style="background:#1a1a1a;border:#444;color:#ccc;">
                            </div>
                            <div class="col-md-4">
                                <label style="color:#ccc;">Date To (optional)</label>
                                <input type="date" name="populate_to" class="form-control" style="background:#1a1a1a;border:#444;color:#ccc;">
                            </div>
                        </div>
                        <small style="color:#888;" class="mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>
                            If no date range specified, uses batch period dates. Entries already in other batches are skipped.
                        </small>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-1"></i> Create Batch
                </button>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                    <h5 style="color:#ffc107;margin:0;"><i class="fas fa-question-circle me-2"></i> Workflow</h5>
                </div>
                <div class="card-body">
                    <ol style="color:#aaa;padding-left:20px;">
                        <li class="mb-2"><strong style="color:#ccc;">Draft</strong> — Create batch & add entries</li>
                        <li class="mb-2"><strong style="color:#ccc;">Submit</strong> — Send for admin approval</li>
                        <li class="mb-2"><strong style="color:#ccc;">Approved</strong> — Ready to process</li>
                        <li class="mb-2"><strong style="color:#ccc;">Processing</strong> — Payments initiated</li>
                        <li class="mb-2"><strong style="color:#ccc;">Completed</strong> — All payments done</li>
                    </ol>
                    <hr style="border-color:#444;">
                    <small style="color:#888;">
                        <i class="fas fa-shield-alt me-1"></i>
                        TDS (10%) is automatically deducted. Bank export generates NEFT/RTGS CSV format.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('autoPopulate').addEventListener('change', function() {
    document.getElementById('populateOptions').style.display = this.checked ? 'block' : 'none';
});
</script>
