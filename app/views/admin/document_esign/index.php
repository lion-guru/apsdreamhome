<?php use App\Core\Database\Database; ?>
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-lg font-semibold">Document E-Sign Management</h3>
                <div class="flex gap-2">
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Document
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Document Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documents)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-gray-500">No documents found.</td></tr>
                            <?php endif; ?>
                            <?php foreach (($documents ?? []) as $doc): ?>
                            <?php $docStatus = $doc['status'] ?? 'pending'; ?>
                            <tr>
                                <td>#<?= (int)$doc['id'] ?></td>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($doc['document_type'] ?? '') ?></span>
                                </td>
                                <td class="font-medium"><?= htmlspecialchars($doc['title'] ?? '') ?></td>
                                <td>
                                    <?php if ($docStatus === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                    <?php elseif ($docStatus === 'signed'): ?>
                                    <span class="badge badge-success">Signed</span>
                                    <?php elseif ($docStatus === 'cancelled'): ?>
                                    <span class="badge badge-error">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($doc['created_by_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($doc['created_at'] ?? '') ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= BASE_URL ?>/admin/document-esign/<?= (int)$doc['id'] ?>" class="btn btn-sm btn-info" title="View" aria-label="View"><i class="fas fa-eye"></i></a>
                                        <?php if ($docStatus === 'pending'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/document-esign/<?= (int)$doc['id'] ?>/sign" class="inline-form">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="signature_data" value="">
                                            <button type="submit" class="btn btn-sm btn-success" title="Sign" aria-label="Sign"><i class="fas fa-signature"></i></button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/document-esign/<?= (int)$doc['id'] ?>/cancel" class="inline-form" onsubmit="return confirm('Cancel this document?')">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                            <button type="submit" class="btn btn-sm btn-error" title="Cancel" aria-label="Cancel"><i class="fas fa-times"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1050;" onclick="if(event.target===this)this.style.display='none'">
                    <div class="card" style="max-width:640px; margin:8vh auto;">
                        <div class="card-header flex justify-between items-center">
                            <h3 class="text-lg font-semibold">Create Document</h3>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('createModal').style.display='none'">&times;</button>
                        </div>
                        <form method="POST" action="<?= BASE_URL ?>/admin/document-esign/store">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <div class="card-body space-y-3">
                                <div>
                                    <label class="text-sm font-medium">Document Type</label>
                                    <select name="document_type" class="w-full border rounded p-2" required aria-label="Document Type">
                                        <option value="agreement">Agreement</option>
                                        <option value="allotment">Allotment Letter</option>
                                        <option value="booking_terms">Booking Terms</option>
                                        <option value="noc">NOC</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium">Title</label>
                                    <input type="text" name="title" class="w-full border rounded p-2" required maxlength="255" aria-label="Title">
                                </div>
                                <div>
                                    <label class="text-sm font-medium">Content</label>
                                    <textarea name="content" rows="8" class="w-full border rounded p-2" required aria-label="Content"></textarea>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                function openCreateModal() {
                    document.getElementById('createModal').style.display = 'block';
                }
                </script>
            </div>
        </div>
    </div>
</div>
