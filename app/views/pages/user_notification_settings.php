<div class="content-area p-4">
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($flash_success ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($flash_error ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card profile-card">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="fas fa-bell me-2 text-primary"></i><?php echo __('notif_pref_heading', [], 'Notification Preferences'); ?></h4>
        </div>
        <div class="card-body aps-cp-card-body">
            <p class="text-muted mb-4"><?php echo __('notif_pref_subtitle', [], 'Choose how you want to be notified for each type of activity.'); ?></p>

            <form method="POST" action="<?php echo BASE_URL; ?>/user/notification-settings">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="style-38624"><?php echo __('notif_pref_type', [], 'Notification Type'); ?></th>
                                <th class="text-center"><i class="fas fa-envelope"></i><br><small><?php echo __('notif_pref_email', [], 'Email'); ?></small></th>
                                <th class="text-center"><i class="fas fa-mobile-alt"></i><br><small><?php echo __('notif_pref_sms', [], 'SMS'); ?></small></th>
                                <th class="text-center"><i class="fab fa-whatsapp"></i><br><small><?php echo __('notif_pref_whatsapp', [], 'WhatsApp'); ?></small></th>
                                <th class="text-center"><i class="fas fa-bell"></i><br><small><?php echo __('notif_pref_push', [], 'Push'); ?></small></th>
                                <th class="text-center"><i class="fas fa-globe"></i><br><small><?php echo __('notif_pref_inapp', [], 'In-App'); ?></small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $typeLabels = [
                                'booking' => [__('notif_type_booking', [], 'Booking Updates'), __('notif_type_booking_desc', [], 'When a plot or property is booked or status changes')],
                                'payment' => [__('notif_type_payment', [], 'Payment Confirmations'), __('notif_type_payment_desc', [], 'When payments are received or due')],
                                'agreement' => [__('notif_type_agreement', [], 'Agreement Updates'), __('notif_type_agreement_desc', [], 'When agreement is generated or signed')],
                                'registry' => [__('notif_type_registry', [], 'Registry Alerts'), __('notif_type_registry_desc', [], 'When registry is scheduled or completed')],
                                'possession' => [__('notif_type_possession', [], 'Possession Updates'), __('notif_type_possession_desc', [], 'When possession or handover is scheduled')],
                                'marketing' => [__('notif_type_marketing', [], 'Marketing & Offers'), __('notif_type_marketing_desc', [], 'New projects, offers, and promotional updates')],
                            ];
                            foreach ($typeLabels as $type => $label):
                                $p = $prefs[$type] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($label[0] ?? ''); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($label[1] ?? ''); ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo e($type); ?>][]" value="email" id="email_<?php echo e($type); ?>" <?php echo ($p['email'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo e($type); ?>][]" value="sms" id="sms_<?php echo e($type); ?>" <?php echo ($p['sms'] ?? false) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo e($type); ?>][]" value="whatsapp" id="whatsapp_<?php echo e($type); ?>" <?php echo ($p['whatsapp'] ?? false) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" name="channels[<?php echo e($type); ?>][]" value="push" id="push_<?php echo e($type); ?>" <?php echo ($p['push'] ?? true) ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" disabled checked>
                                        <small class="d-block text-muted"><?php echo __('notif_pref_always_on', [], 'Always On'); ?></small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-clock me-2 text-info"></i><?php echo __('notif_pref_quiet_hours', [], 'Quiet Hours'); ?></h5>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label"><?php echo __('notif_pref_start_time', [], 'Start Time'); ?></label>
                                <input type="time" name="quiet_hours_start" class="form-control" value="<?php echo htmlspecialchars($prefs['booking']['quiet_hours_start'] ?? ''); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label"><?php echo __('notif_pref_end_time', [], 'End Time'); ?></label>
                                <input type="time" name="quiet_hours_end" class="form-control" value="<?php echo htmlspecialchars($prefs['booking']['quiet_hours_end'] ?? ''); ?>">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1"><?php echo __('notif_pref_quiet_desc', [], 'No notifications will be sent during this time.'); ?></small>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-tachometer-alt me-2 text-success"></i><?php echo __('notif_pref_frequency', [], 'Default Frequency'); ?></h5>
                        <select name="frequency" class="form-select">
                            <?php
                            $freqs = ['immediate' => __('freq_immediate', [], 'Immediate'), 'hourly' => __('freq_hourly', [], 'Hourly Digest'), 'daily' => __('freq_daily', [], 'Daily Digest'), 'weekly' => __('freq_weekly', [], 'Weekly Digest'), 'never' => __('freq_never', [], 'Never')];
                            $currentFreq = $prefs['booking']['frequency'] ?? 'immediate';
                            foreach ($freqs as $val => $lab):
                            ?>
                            <option value="<?php echo e($val); ?>" <?php echo $currentFreq === $val ? 'selected' : ''; ?>><?php echo htmlspecialchars($lab ?? ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1"><?php echo __('notif_pref_frequency_desc', [], 'How often you receive non-urgent notifications.'); ?></small>
                    </div>
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <a href="<?php echo BASE_URL; ?>/user/dashboard" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i><?php echo __('back_to_dashboard', [], 'Back to Dashboard'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i><?php echo __('notif_pref_save', [], 'Save Preferences'); ?>
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
