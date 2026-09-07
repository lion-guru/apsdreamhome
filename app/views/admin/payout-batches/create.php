ï»¿<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= BASE_URL ?>/admin/payout-batches" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
            <h2 ><i class="fas fa-plus-circle me-2"></i> Create Payout Batch</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/payout-batches/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 ><i class="fas fa-info-circle me-2"></i> Batch Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label >Batch Name *</label>
                                <input type="text" name="batch_name" class="form-control" required
                                       placeholder="e.g. July 2026 Commission Payout"
                                       >
                            </div>
                            <div class="col-md-6">
                                <label >Batch Type</label>
                                <select name="batch_type" class="form-select">
                                    <option value="commission">Commission Payout</option>
                                    <option value="salary">Salary</option>
                                    <option value="bonus">Bonus</option>
                                    <option value="refund">Refund</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label >Period From</label>
                                <input type="date" name="period_from" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label >Period To</label>
                                <input type="date" name="period_to" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label >Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this batch..."
                                      ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Auto-Populate Option -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 >
                            <input type="checkbox" name="auto_populate" value="1" id="autoPopulate" class="me-2">
                            <label for="autoPopulate" >
                                <i class="fas fa-magic me-2"></i> Auto-populate with pending commission entries
                            </label>
                        </h5>
                    </div>
                    <div class="card-body" id="populateOptions" >
                        <div class="row">
                            <div class="col-md-4">
                                <label >Commission Type (optional filter)</label>
                                <select name="populate_type" class="form-select">
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
                                <label >Date From (optional)</label>
                                <input type="date" name="populate_from" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label >Date To (optional)</label>
                                <input type="date" name="populate_to" class="form-control">
                            </div>
                        </div>
                        <small class=" mt-2 d-block">
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
            <div class="card">
                <div class="card-header">
                    <h5 ><i class="fas fa-question-circle me-2"></i> Workflow</h5>
                </div>
                <div class="card-body">
                    <ol >
                        <li class="mb-2"><strong >Draft</strong> — Create batch & add entries</li>
                        <li class="mb-2"><strong >Submit</strong> — Send for admin approval</li>
                        <li class="mb-2"><strong >Approved</strong> — Ready to process</li>
                        <li class="mb-2"><strong >Processing</strong> — Payments initiated</li>
                        <li class="mb-2"><strong >Completed</strong> — All payments done</li>
                    </ol>
                    <hr >
                    <small >
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
