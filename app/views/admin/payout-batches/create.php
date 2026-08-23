ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= BASE_URL ?>/admin/payout-batches" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <h2 class="style-48283"><i class="fas fa-plus-circle me-2 style-56943"></i> Create Payout Batch</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="card mb-4 style-62867">
                    <div class="card-header style-10528">
                        <h5 class="style-37492"><i class="fas fa-info-circle me-2"></i> Batch Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="style-96386">Batch Name *</label>
                                <input type="text" name="batch_name" class="form-control" required
                                       placeholder="e.g. July 2026 Commission Payout"
                                       class="style-62452">
                            </div>
                            <div class="col-md-6">
                                <label class="style-96386">Batch Type</label>
                                <select name="batch_type" class="form-select style-62452">
                                    <option value="commission">Commission Payout</option>
                                    <option value="salary">Salary</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="refund">Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="style-96386">Period From</label>
                                <input type="date" name="period_from" class="form-control style-62452">
                            </div>
                            <div class="col-md-6">
                                <label class="style-96386">Period To</label>
                                <input type="date" name="period_to" class="form-control style-62452">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="style-96386">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this batch..."
                                      class="style-62452"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Auto-Populate Option -->
                <div class="card mb-4 style-76801">
                    <div class="card-header style-92050">
                        <h5 class="style-37492">
                            <input type="checkbox" name="auto_populate" value="1" id="autoPopulate" class="me-2">
                            <label for="autoPopulate" class="style-52721">
                                <i class="fas fa-magic me-2"></i> Auto-populate with pending commission entries
                            </label>
                        </h5>
                    </div>
                    <div class="card-body" id="populateOptions" class="style-2248">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="style-96386">Commission Type (optional filter)</label>
                                <select name="populate_type" class="form-select style-62452">
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
                                <label class="style-96386">Date From (optional)</label>
                                <input type="date" name="populate_from" class="form-control style-62452">
                            </div>
                            <div class="col-md-4">
                                <label class="style-96386">Date To (optional)</label>
                                <input type="date" name="populate_to" class="form-control style-62452">
                            </div>
                        </div>
                        <small class="style-77712 mt-2 d-block">
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
            <div class="card style-62867">
                <div class="card-header style-10528">
                    <h5 class="style-11295"><i class="fas fa-question-circle me-2"></i> Workflow</h5>
                </div>
                <div class="card-body">
                    <ol class="style-43707">
                        <li class="mb-2"><strong class="style-96386">Draft</strong> — Create batch & add entries</li>
                        <li class="mb-2"><strong class="style-96386">Submit</strong> — Send for admin approval</li>
                        <li class="mb-2"><strong class="style-96386">Approved</strong> — Ready to process</li>
                        <li class="mb-2"><strong class="style-96386">Processing</strong> — Payments initiated</li>
                        <li class="mb-2"><strong class="style-96386">Completed</strong> — All payments done</li>
                    </ol>
                    <hr class="style-96118">
                    <small class="style-77712">
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
