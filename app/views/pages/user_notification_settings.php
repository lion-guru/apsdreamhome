<div class="content-area p-4">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($flash_success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($flash_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card profile-card">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i>Notification Preferences</h4>
        </div>
        <div class="card-body aps-cp-card-body">
            <p class="text-muted mb-4">Choose how you want to be notified for each type of activity.</p>

            <form method="POST" action="<?php echo BASE_URL; ?>/user/notification-settings">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:140px;">Notification Type</th>
                                <th class="text-center"><i class="fas fa-envelope"></i><br><small>Email</small></th>
                                <th class="text-center"><i class="fas fa-mobile-alt"></i><br><small>SMS</small></th>
                                <th class="text-center"><i class="fab fa-whatsapp"></i><br><small>WhatsApp</small></th>
                                <th class="text-center"><i class="fas fa-bell"></i><br><small>Push</small></th>
                                <th class="text-center"><i class="fas fa-globe"></i><br><small>In-App</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $typeLabels = [
                                'booking' => ['Booking Updates', 'When a plot or property is booked or status changes'],
                                'payment' => ['Payment Confirmations', 'When payments are received or due'],
                                'agreement' => ['Agreement Updates', 'When agreement is generated or signed'],
                                'registry' => ['Registry Alerts', 'When registry is scheduled or completed'],
                                'possession' => ['Possession Updates', 'When possession or handover is scheduled'],
                                'marketing' => ['Marketing & Offers', 'New projects, offers, and promotional updates'],
                            ];
                            foreach ($typeLabels as $type => $label):
                                $p = $prefs[$type] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($label[0]); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($label[1]); ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo $type; ?>][]" value="email" id="email_<?php echo $type; ?>" <?php echo ($p['email'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo $type; ?>][]" value="sms" id="sms_<?php echo $type; ?>" <?php echo ($p['sms'] ?? false) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo $type; ?>][]" value="whatsapp" id="whatsapp_<?php echo $type; ?>" <?php echo ($p['whatsapp'] ?? false) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo $type; ?>][]" value="push" id="push_<?php echo $type; ?>" <?php echo ($p['push'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" disabled checked>
                                        <small class="d-block text-muted">Always On</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-clock me-2 text-info"></i>Quiet Hours</h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="quiet_hours_start" class="form-control" value="<?php echo htmlspecialchars($prefs['booking']['quiet_hours_start'] ?? ''); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="quiet_hours_end" class="form-control" value="<?php echo htmlspecialchars($prefs['booking']['quiet_hours_end'] ?? ''); ?>">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">No notifications will be sent during this time.</small>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-tachometer-alt me-2 text-success"></i>Default Frequency</h5>
                        <select name="frequency" class="form-select">
                            <?php
                            $freqs = ['immediate' => 'Immediate', 'hourly' => 'Hourly Digest', 'daily' => 'Daily Digest', 'weekly' => 'Weekly Digest', 'never' => 'Never'];
                            $currentFreq = $prefs['booking']['frequency'] ?? 'immediate';
                            foreach ($freqs as $val => $lab):
                            ?>
                            <option value="<?php echo $val; ?>" <?php echo $currentFreq === $val ? 'selected' : ''; ?>><?php echo htmlspecialchars($lab); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1">How often you receive non-urgent notifications.</small>
                    </div>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <a href="<?php echo BASE_URL; ?>/user/dashboard" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
.table th { font-weight: 600; font-size: 0.85rem; }
.table td { vertical-align: middle; }
.form-switch .form-check-input { cursor: pointer; width: 2.5em; height: 1.25em; margin: 0; }
</style>
