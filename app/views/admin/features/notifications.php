<?php
$page_title = $page_title ?? 'Notification Center';
$page_heading = $page_heading ?? 'Notification Center';
$content = $content ?? '';
ob_start();
?>
<div class="container-fluid py-4">
  <h1 class="h3 mb-4"><i class="fas fa-bell me-2"></i>Notification Center</h1>

  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tmpl">Templates</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sms">SMS Templates</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#send">Send Test</button></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="tmpl">
          <div class="card shadow-sm"><div class="card-body p-0">
            <div class="table-responsive"><table class="table mb-0">
              <thead class="table-light"><tr><th>Code</th><th>Channel</th><th>Subject</th><th>Body</th></tr></thead>
              <tbody>
                <?php if (empty($templates)): ?>
                  <tr><td colspan="4" class="text-center py-3 text-muted">No templates</td></tr>
                <?php else: foreach ($templates as $t): ?>
                  <tr>
                    <td><code><?= htmlspecialchars($t['template_code'] ?? '') ?></code></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($t['channel'] ?? '') ?></span></td>
                    <td><small><?= htmlspecialchars($t['subject'] ?? '') ?></small></td>
                    <td><small><?= htmlspecialchars(substr($t['body'] ?? '', 0, 80)) ?></small></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table></div>
          </div></div>
        </div>

        <div class="tab-pane fade" id="sms">
          <div class="card shadow-sm"><div class="card-body p-0">
            <div class="table-responsive"><table class="table mb-0">
              <thead class="table-light"><tr><th>Code</th><th>Category</th><th>Body</th></tr></thead>
              <tbody>
                <?php if (empty($smsTemplates)): ?>
                  <tr><td colspan="3" class="text-center py-3 text-muted">No SMS templates</td></tr>
                <?php else: foreach ($smsTemplates as $s): ?>
                  <tr>
                    <td><code><?= htmlspecialchars($s['template_code'] ?? '') ?></code></td>
                    <td><?= htmlspecialchars($s['category'] ?? '') ?></td>
                    <td><small><?= htmlspecialchars($s['body'] ?? '') ?></small></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table></div>
          </div></div>
        </div>

        <div class="tab-pane fade" id="send">
          <form method="POST" action="<?= BASE_URL ?>/api/v2/notification/send" class="card card-body">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="row g-2">
              <div class="col-md-3"><label>User ID</label><input name="user_id" type="number" class="form-control" required></div>
              <div class="col-md-3"><label>Channel</label>
                <select name="channel" class="form-select"><option>email</option><option>sms</option><option>push</option><option>whatsapp</option></select>
              </div>
              <div class="col-md-6"><label>Subject</label><input name="subject" class="form-control"></div>
              <div class="col-12"><label>Message</label><textarea name="message" class="form-control" rows="3" required></textarea></div>
              <div class="col-12"><button class="btn btn-primary">Send</button></div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
