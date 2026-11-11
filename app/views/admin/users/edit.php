<?php
$user = $user ?? [];
$roles = $roles ?? ['admin', 'manager', 'associate', 'agent', 'customer', 'user'];
$page_title = $page_title ?? 'Edit User';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit User</h2>
                <p class="text-muted mb-0">Update user information</p>
            </div>
            <a href="<?php echo e($base); ?>/admin/users" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
        
        <?php if (!empty($user)): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form id="editUserForm" action="<?php echo e($base); ?>/admin/users/<?php echo $user['id']; ?>/update" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo e($role); ?>" <?php echo ($user['role'] ?? '') === $role ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($role); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="p-3 bg-light rounded mb-3">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                                <?php
                                $addressPrefix = 'user';
                                $addressValues = [
                                    'city' => $user['city'] ?? '',
                                    'pincode' => $user['pincode'] ?? '',
                                    'address_line' => $user['address'] ?? '',
                                ];
                                // Try to load structured address if exists
                                if (!empty($user['id'])) {
                                    try {
                                        $addrRow = (new \App\Services\AddressService())->listForUser((int)$user['id']);
                                        if (!empty($addrRow[0])) {
                                            $addressValues = [
                                                'city' => $addrRow[0]['city'] ?? $addressValues['city'],
                                                'pincode' => $addrRow[0]['pincode'] ?? $addressValues['pincode'],
                                                'address_line' => $addrRow[0]['address_line1'] ?? $addressValues['address_line'],
                                            ];
                                        }
                                    } catch (\Throwable $e) { error_log('Admin user edit address fetch: ' . $e->getMessage()); }
                                }
                                $addressShowMap = false;
                                $addressRequired = false;
                                include __DIR__ . '/../../components/address-form.php';
                                ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo ($user['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                                    <small class="text-muted">Only fill if you want to change password</small>
                                </div>
                            </div>

                            <?php 
                            $isAssociate = ($user['role'] ?? '') === 'associate';
                            $assocData = $associate ?? [];
                            ?>
                            <div class="p-3 rounded mb-3 border <?= $isAssociate ? 'bg-white shadow-sm' : 'bg-light' ?>" id="associateControlsBox" style="<?= $isAssociate ? '' : 'display:none;' ?>">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <h6 class="fw-bold mb-0 text-primary">
                                        <i class="fas fa-user-shield me-2"></i>Associate Operations & Financial Controls (एसोसिएट नियंत्रण)
                                    </h6>
                                    <span class="badge bg-primary text-white">Security & Anti-Fraud</span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="can_offline_book" name="can_offline_book" value="1" <?= !empty($assocData['can_offline_book']) ? 'checked' : '' ?> style="width: 2.5em; height: 1.3em;">
                                            <label class="form-check-label fw-bold" for="can_offline_book">
                                                Allow Direct Offline Plot Booking<br>
                                                <small class="text-muted fw-normal">एसोसिएट को सीधे पोर्टल से ऑफलाइन प्लॉट बुक करने की अनुमति दें</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-switch p-0 d-flex align-items-center gap-3">
                                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="can_collect_cash" name="can_collect_cash" value="1" <?= !empty($assocData['can_collect_cash']) ? 'checked' : '' ?> style="width: 2.5em; height: 1.3em;">
                                            <label class="form-check-label fw-bold" for="can_collect_cash">
                                                Authorize Physical Cash Collection<br>
                                                <small class="text-muted fw-normal">ग्राहक से सीधे नकद राशि (Cash) प्राप्त करने का विशेष अधिकार</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label for="daily_cash_limit" class="form-label fw-bold">Max Daily Cash Collection Limit (₹)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="1000" min="0" class="form-control" id="daily_cash_limit" name="daily_cash_limit" value="<?= htmlspecialchars((string)($assocData['daily_cash_limit'] ?? '0')) ?>" placeholder="0.00">
                                        </div>
                                        <div class="form-text small">अधिकतम दैनिक नकद सीमा। '0' का अर्थ है कोई नकद अनुमति नहीं (ऑनलाइन पेमेंट अनिवार्य)।</div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="alert alert-secondary py-2 px-3 small mb-0">
                                            <i class="fas fa-info-circle me-1 text-primary"></i> <strong>दिशा-निर्देश:</strong> कंपनी की भ्रष्टाचार-रोधी नीति के अनुसार अधिकांश एसोसिएट्स के लिए नकद संग्रह बंद रखें और केवल कंपनी UPI QR / बैंक खाते में सीधे भुगतान की सलाह दें।
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo e($base); ?>/admin/users" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            User not found.
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        const roleSelect = document.getElementById('role');
        const assocBox = document.getElementById('associateControlsBox');
        if (roleSelect && assocBox) {
            roleSelect.addEventListener('change', function() {
                if (this.value === 'associate') {
                    assocBox.style.display = 'block';
                    assocBox.classList.add('bg-white', 'shadow-sm');
                    assocBox.classList.remove('bg-light');
                } else {
                    assocBox.style.display = 'none';
                }
            });
        }

        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            if (typeof showLoader === 'function') showLoader();

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    window.location.href = '<?php echo e($base); ?>/admin/users';
                    return { success: true };
                }
            })
            .then(data => {
                if (data && data.success) {
                    if (typeof showToast === 'function') {
                        showToast('User updated successfully!', 'success');
                    }
                    window.location.href = '<?php echo e($base); ?>/admin/users';
                } else if (data) {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Failed to update user', 'danger');
                    } else {
                        alert(data.message || 'Failed to update user');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.location.href = '<?php echo e($base); ?>/admin/users';
            })
            .finally(() => {
                if (typeof hideLoader === 'function') hideLoader();
            });
        });
    </script>

