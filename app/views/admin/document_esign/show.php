<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-8">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-lg font-semibold">Document Details</h3>
                <a href="<?= BASE_URL ?>/admin/document-esign" class="btn btn-sm btn-secondary">&larr; Back to list</a>
            </div>
            <div class="card-body">
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Document ID:</span>
                        <span class="font-mono"><?= (int)$document['id'] ?></span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Document Type:</span>
                        <span class="badge badge-info"><?= htmlspecialchars($document['document_type'] ?? '') ?></span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Title:</span>
                        <span class="font-medium"><?= htmlspecialchars($document['title'] ?? '') ?></span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <?php $docStatus = $document['status'] ?? 'pending'; ?>
                        <?php if ($docStatus === 'pending'): ?>
                        <span class="badge badge-warning">Pending</span>
                        <?php elseif ($docStatus === 'signed'): ?>
                        <span class="badge badge-success">Signed</span>
                        <?php elseif ($docStatus === 'cancelled'): ?>
                        <span class="badge badge-error">Cancelled</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Created By:</span>
                        <span><?= htmlspecialchars($document['created_by_name'] ?? 'N/A') ?></span>
                    </div>

                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Created At:</span>
                        <span><?= htmlspecialchars($document['created_at'] ?? '') ?></span>
                    </div>

                    <?php if (!empty($document['signed_at'])): ?>
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Signed At:</span>
                        <span><?= htmlspecialchars($document['signed_at']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($document['signed_by'])): ?>
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-sm font-medium text-gray-600">Signed By:</span>
                        <span><?= htmlspecialchars($document['signed_by_name'] ?? 'N/A') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($docStatus === 'pending'): ?>
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h4 class="font-medium text-yellow-800 mb-2">Pending Actions</h4>
                    <p class="text-sm text-yellow-700">This document requires a digital signature to proceed.</p>
                    <form method="POST" action="<?= BASE_URL ?>/admin/document-esign/<?= (int)$document['id'] ?>/sign" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="signature_data" value="">
                        <button type="submit" class="btn btn-success"><i class="fas fa-signature"></i> Sign Document</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($docStatus === 'signed'): ?>
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2">Document Signed</h4>
                    <p class="text-sm text-green-700">This document has been digitally signed and verified.</p>
                    <div class="mt-2 p-2 bg-green-100 rounded text-xs font-mono">
                        Verification Code: <?= htmlspecialchars($document['verification_code'] ?? '') ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-4">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Document Content</h3>
            </div>
            <div class="card-body">
                <div style="white-space: pre-wrap;">
                    <?= nl2br(htmlspecialchars($document['content'] ?? '')) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($document['signature_data'])): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="text-lg font-semibold">Signature Preview</h3>
            </div>
            <div class="card-body">
                <div class="bg-gray-50 p-4 rounded border">
                    <img src="data:image/png;base64,<?= htmlspecialchars($document['signature_data']) ?>" alt="Signature" class="max-w-full h-auto" />
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
