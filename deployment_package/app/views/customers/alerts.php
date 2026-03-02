<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title mb-2">प्रॉपर्टी अलर्ट्स (Property Alerts)</h4>
                            <p class="card-text mb-0">जब भी आपकी पसंद की कोई नई प्रॉपर्टी आएगी, हम आपको सूचित करेंगे।</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-light text-warning" data-toggle="modal" data-target="#createAlertModal">
                                <i class="fas fa-plus mr-1"></i> नया अलर्ट बनाएं
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Alerts List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">आपके एक्टिव अलर्ट्स</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($alerts)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-4x text-muted mb-4"></i>
                            <h4 class="text-gray-800">कोई एक्टिव अलर्ट नहीं है!</h4>
                            <p class="text-muted">अपनी पसंद के अनुसार अलर्ट सेट करें और नई प्रॉपर्टीज की जानकारी सबसे पहले पाएं।</p>
                            <button class="btn btn-primary mt-3" data-toggle="modal" data-target="#createAlertModal">अलर्ट सेट करें</button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>अलर्ट डिटेल्स</th>
                                        <th>प्राइस रेंज</th>
                                        <th>फ्रीक्वेंसी</th>
                                        <th>टाइप</th>
                                        <th>स्टेटस</th>
                                        <th>एक्शन</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alerts as $alert): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-gray-900"><?= h($alert['property_type_name'] ?? 'सभी टाइप्स') ?></div>
                                                <div class="small text-muted"><?= h($alert['city'] ?? 'सभी शहर') ?>, <?= h($alert['state'] ?? '') ?></div>
                                                <div class="small text-muted"><?= $alert['min_bedrooms'] ? $alert['min_bedrooms'] . '+ BHK' : '' ?></div>
                                            </td>
                                            <td>
                                                ₹<?= number_format($alert['min_price'] ?? 0) ?> - ₹<?= number_format($alert['max_price'] ?? 0) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= ucfirst($alert['frequency']) ?></span>
                                            </td>
                                            <td>
                                                <i class="fas fa-<?= $alert['alert_type'] == 'email' ? 'envelope' : 'mobile-alt' ?> mr-1"></i>
                                                <?= ucfirst($alert['alert_type']) ?>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="alertSwitch<?= $alert['id'] ?>" <?= $alert['status'] == 'active' ? 'checked' : '' ?>>
                                                    <label class="custom-control-label" for="alertSwitch<?= $alert['id'] ?>"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Alert Modal -->
<div class="modal fade" id="createAlertModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">नया प्रॉपर्टी अलर्ट बनाएं</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/customer/create-alert" method="POST">
                <?php echo getCsrfField(); ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">प्रॉपर्टी टाइप</label>
                            <select name="property_type_id" class="form-control">
                                <option value="">सभी टाइप्स</option>
                                <?php foreach ($property_types as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= h($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">शहर</label>
                            <select name="city" class="form-control">
                                <option value="">सभी शहर</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['city'] ?>"><?= h($location['city']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">मिनिमम बजट (₹)</label>
                            <input type="number" name="min_price" class="form-control" placeholder="जैसे: 10,00,000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">मैक्सिमम बजट (₹)</label>
                            <input type="number" name="max_price" class="form-control" placeholder="जैसे: 50,00,000">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">मिनिमम बेडरूम (BHK)</label>
                            <select name="min_bedrooms" class="form-control">
                                <option value="">कोई भी</option>
                                <option value="1">1+ BHK</option>
                                <option value="2">2+ BHK</option>
                                <option value="3">3+ BHK</option>
                                <option value="4">4+ BHK</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">नोटिफिकेशन फ्रीक्वेंसी</label>
                            <select name="frequency" class="form-control">
                                <option value="daily">डेली (Daily)</option>
                                <option value="weekly">वीकली (Weekly)</option>
                                <option value="instant">तुरंत (Instant)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-0 mt-3">
                        <label class="font-weight-bold small">नोटिफिकेशन टाइप</label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" id="typeEmail" name="alert_type" value="email" class="custom-control-input" checked>
                                <label class="custom-control-label" for="typeEmail"><i class="fas fa-envelope mr-1"></i> ईमेल</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="typeSMS" name="alert_type" value="sms" class="custom-control-input">
                                <label class="custom-control-label" for="typeSMS"><i class="fas fa-comment-alt mr-1"></i> SMS</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">बंद करें</button>
                    <button type="submit" class="btn btn-primary px-4">अलर्ट सेव करें</button>
                </div>
            </form>
        </div>
    </div>
</div>
