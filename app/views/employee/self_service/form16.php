<?php
/**
 * Form 16 Download
*/

$page_title = $page_title ?? 'Form 16 Download';
$form16_list = $form16_list ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-file-download me-2"></i>Form 16 - TDS Certificate</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Form 16 Download</h6>
                        <p class="mb-0">Form 16 is your TDS certificate issued under Section 203 of the Income Tax Act. It contains details of salary paid and tax deducted. You need this for filing your Income Tax Return.</p>
                    </div>

                    <!-- Generate New Form 16 -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Generate New Form 16</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="col-md-6">
                                    <label class="form-label">Financial Year</label>
                                    <select name="financial_year" class="form-select" required>
                                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?= $y ?>"><?= $y ?>-<?= $y + 1 ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-magic me-2"></i>Generate Form 16
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Available Form 16s -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Available Form 16s</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($form16_list)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Form 16 Generated Yet</h5>
                                <p class="text-muted">Generate your first Form 16 using the form above</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Financial Year</th>
                                            <th>Status</th>
                                            <th>Generated On</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($form16_list as $f16): ?>
                                        <tr>
                                            <td>
                                                <strong><?= $f16['financial_year'] ?>-<?= $f16['financial_year'] + 1 ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($f16['available']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Available</span>
                                                <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $f16['generated_at'] ? date('d M Y H:i', strtotime($f16['generated_at'])) : '-' ?></td>
                                            <td class="text-center">
                                                <?php if ($f16['available']): ?>
                                                <a href="<?= BASE_URL ?>/employee/self-service/form16/download/<?= $f16['financial_year'] ?>" 
                                                    class="btn btn-info btn-sm" title="Download PDF">
                                                    <i class="fas fa-download me-1"></i> Download
                                                </a>
                                                <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="financial_year" value="<?= $f16['financial_year'] ?>">
                                                    <button type="submit" class="btn btn-outline-info btn-sm" title="Generate">
                                                        <i class="fas fa-sync me-1"></i> Generate
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- What's in Form 16 -->
                    <div class="card mt-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>What's in Form 16?</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Part A - Employer & Employee Details</h6>
                                    <ul class="small">
                                        <li>Employer PAN, TAN, Address</li>
                                        <li>Employee PAN, Name, Address</li>
                                        <li>Assessment Year</li>
                                        <li>Period of Employment</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Part B - Salary & Tax Details</h6>
                                    <ul class="small">
                                        <li>Gross Salary (Breakup)</li>
                                        <li>Exemptions (HRA, LTA, etc.)</li>
                                        <li>Deductions (80C, 80D, etc.)</li>
                                        <li>Taxable Income & TDS</li>
                                        <li>Education Cess</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> Form 16 is generated based on salary paid and TDS deducted. If you have investment declarations pending, they won't reflect in Form 16. Ensure all declarations are submitted before generation.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.table th { border-top: none; font-weight: 600; color: #495057; }
.badge { font-size: 0.75rem; }
</style>