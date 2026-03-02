<?php require_once 'app/views/layouts/header.php'; ?>
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="font-weight-bold text-gray-900">EMI हिस्ट्री (EMI Calculation History)</h2>
            <p class="text-muted">आपकी पिछली सभी EMI गणनाओं का रिकॉर्ड यहाँ सुरक्षित है।</p>
        </div>
    </div>

    <!-- EMI History List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-body p-0">
                    <?php if (empty($emi_history)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calculator fa-4x text-muted mb-4"></i>
                            <h4 class="text-gray-800">अभी तक कोई रिकॉर्ड नहीं है!</h4>
                            <p class="text-muted">EMI कैलकुलेटर का उपयोग करें और अपनी गणनाओं को यहाँ सेव करें।</p>
                            <a href="/customer/emi-calculator" class="btn btn-primary mt-3">कैलकुलेटर पर जाएं</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>डेट और प्रॉपर्टी</th>
                                        <th>लोन राशि (Principal)</th>
                                        <th>ब्याज दर</th>
                                        <th>अवधि (Years)</th>
                                        <th>मासिक EMI</th>
                                        <th>कुल भुगतान</th>
                                        <th>एक्शन</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($emi_history as $history): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-gray-900"><?= date('d M Y, H:i', strtotime($history['created_at'])) ?></div>
                                                <div class="small text-muted">
                                                    <?php if ($history['property_id']): ?>
                                                        <a href="/customer/property/<?= $history['property_id'] ?>"><?= h($history['property_title']) ?></a>
                                                    <?php else: ?>
                                                        जनरल कैलकुलेशन
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>₹<?= number_format($history['loan_amount']) ?></td>
                                            <td><?= $history['interest_rate'] ?>%</td>
                                            <td><?= $history['loan_tenure'] ?> साल</td>
                                            <td class="font-weight-bold text-primary">₹<?= number_format($history['monthly_emi']) ?></td>
                                            <td>₹<?= number_format($history['total_payment']) ?></td>
                                            <td>
                                                <a href="/customer/emi-calculator?history_id=<?= $history['id'] ?>" class="btn btn-sm btn-outline-info mr-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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
<?php require_once 'app/views/layouts/footer.php'; ?>
