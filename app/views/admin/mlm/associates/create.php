

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users"></i> Create</h2>
                <div>
                    <a href="/admin/mlm" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Create Management - Complete MLM Associate System with 7 Levels
                    </div>
                    <form method="POST" action="/admin/mlm/associates/create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Associate Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="level_id" class="form-label">Level *</label>
                                    <select class="form-select" id="level_id" name="level_id" required>
                                        <option value="">Select Level</option>
                                        <option value="1">Associate (10%)</option>
                                        <option value="2">Senior Associate (12%)</option>
                                        <option value="3">Team Leader (15%)</option>
                                        <option value="4">Senior Team Leader (18%)</option>
                                        <option value="5">Manager (22%)</option>
                                        <option value="6">Senior Manager (25%)</option>
                                        <option value="7">Director (30%)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sponsor_id" class="form-label">Sponsor</label>
                                    <select class="form-select" id="sponsor_id" name="sponsor_id">
                                        <option value="">No Sponsor</option>
                                        <option value="1">John Doe</option>
                                        <option value="2">Jane Smith</option>
                                        <option value="3">Bob Johnson</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="joining_date" class="form-label">Joining Date *</label>
                                    <input type="date" class="form-control" id="joining_date" name="joining_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/admin/mlm/associates" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Associate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

