<div class="container-fluid py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-balance-scale me-2 text-primary"></i>Payment Reconciliation Desk</h4>
    <a href="<?= BASE_URL ?>/admin/payments" class="btn btn-outline-secondary btn-sm">Payments Ledger</a>
  </div>
  <p class="text-muted small">Orphan payments (missing booking/customer, total mismatch) — dev test data cleanup. Link = booking se jodo, Void = cancelled (amount audit me rehta hai), Keep = orphan rakho.</p>

  <div class="row mb-3">
    <div class="col-md-2"><div class="card text-center"><div class="card-body"><div class="fs-4 fw-bold"><?= (int)($stats['total_payments'] ?? 0) ?></div><small class="text-muted">Total Payments</small></div></div></div>
    <div class="col-md-2"><div class="card text-center border-warning"><div class="card-body"><div class="fs-4 fw-bold text-warning"><?= (int)($stats['orphan_booking'] ?? 0) ?></div><small class="text-muted">Orphan Booking</small></div></div></div>
    <div class="col-md-2"><div class="card text-center border-danger"><div class="card-body"><div class="fs-4 fw-bold text-danger"><?= (int)($stats['null_customer'] ?? 0) ?></div><small class="text-muted">NULL Customer</small></div></div></div>
    <div class="col-md-2"><div class="card text-center border-info"><div class="card-body"><div class="fs-4 fw-bold text-info"><?= (int)($stats['mismatch_total'] ?? 0) ?></div><small class="text-muted">Total Mismatch</small></div></div></div>
    <div class="col-md-2"><div class="card text-center"><div class="card-body"><div class="fs-4 fw-bold"><?= (int)($stats['null_booking'] ?? 0) ?></div><small class="text-muted">NULL Booking</small></div></div></div>
    <div class="col-md-2"><div class="card text-center"><div class="card-body"><div class="fs-4 fw-bold text-success">₹<?= number_format((float)($stats['total_amount'] ?? 0)) ?></div><small class="text-muted">Total Amount</small></div></div></div>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>ID</th><th>Amount</th><th>Total</th><th>Booking</th><th>Customer</th><th>User</th><th>Status</th><th>Gateway</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (empty($orphans)): ?>
        <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-check-circle me-1"></i> Koi orphan payment nahi — sab clean! 🎉</td></tr>
      <?php endif; ?>
      <?php foreach (($orphans ?? []) as $p): ?>
        <tr>
          <td>#<?= (int)$p['id'] ?><br><small class="text-muted"><?= htmlspecialchars($p['payment_id'] ?? '') ?></small></td>
          <td>₹<?= number_format((float)$p['amount']) ?></td>
          <td>₹<?= number_format((float)$p['total_amount']) ?></td>
          <td><?= $p['booking_id'] ? '#'.(int)$p['booking_id'] : '<span class="text-danger">NULL</span>' ?></td>
          <td><?= $p['customer_id'] ? '#'.(int)$p['customer_id'] : '<span class="text-danger">NULL</span>' ?></td>
          <td><?= htmlspecialchars($p['user_name'] ?? '') ?> <small class="text-muted">#<?= (int)($p['user_id'] ?? 0) ?></small></td>
          <td><span class="badge bg-<?= ($p['status']==='completed'?'success':($p['status']==='cancelled'?'secondary':'warning')) ?>"><?= htmlspecialchars($p['status'] ?? '') ?></span></td>
          <td><?= htmlspecialchars($p['gateway'] ?? '') ?></td>
          <td><?= htmlspecialchars(substr($p['payment_date'] ?? '', 0, 10)) ?></td>
          <td>
            <form method="POST" action="<?= BASE_URL ?>/admin/payments/reconciliation/<?= (int)$p['id'] ?>/link" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <select aria-label="Booking id" name="booking_id" class="form-select form-select-sm d-inline-block w-auto" required>
                <option value="">Booking…</option>
                <?php foreach (($bookings ?? []) as $b): ?>
                  <option value="<?= (int)$b['id'] ?>">#<?= (int)$b['id'] ?> <?= htmlspecialchars($b['booking_number'] ?? '') ?> (₹<?= number_format((float)$b['total_amount']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-success btn-sm" type="submit" onclick="return confirm('Payment #<?= (int)$p['id'] ?> ko is booking se link karein?')">Link</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/admin/payments/reconciliation/<?= (int)$p['id'] ?>/void" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <button class="btn btn-warning btn-sm" type="submit" onclick="return confirm('Payment #<?= (int)$p['id'] ?> void karein?')">Void</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/admin/payments/reconciliation/<?= (int)$p['id'] ?>/keep" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <button class="btn btn-outline-secondary btn-sm" type="submit">Keep</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <p class="text-muted small mt-2">Money-proof: Link ke baad amount byte-identical verify hota hai. Void me amount delete nahi hota (audit preserved).</p>
</div>
