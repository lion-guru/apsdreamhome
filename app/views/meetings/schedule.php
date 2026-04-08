<?php
/**
 * Meeting Scheduler View
 */
$meetings = $meetings ?? [];
$page_title = $page_title ?? 'Schedule Meeting';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Schedule a Meeting</h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="meetingForm" action="<?php echo $base; ?>/schedule-meeting" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Preferred Date *</label>
                                    <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Preferred Time *</label>
                                    <select name="time" class="form-select" required>
                                        <option value="">Select Time</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Meeting Type *</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="property_visit">Property Site Visit</option>
                                    <option value="consultation">Property Consultation</option>
                                    <option value="investment">Investment Discussion</option>
                                    <option value="booking">Booking Discussion</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Message / Notes</label>
                                <textarea name="message" class="form-control" rows="3" placeholder="Any specific requirements or questions..."></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Meeting
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Upcoming Meetings -->
                <?php if (!empty($meetings)): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Upcoming Meetings</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($meetings as $meeting): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $meeting['type'])); ?></h6>
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('M d, Y', strtotime($meeting['date'])); ?> at 
                                                <?php echo date('h:i A', strtotime($meeting['time'])); ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-<?php echo $meeting['status'] === 'confirmed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($meeting['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('meetingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Meeting scheduled successfully! We will contact you to confirm.');
            this.reset();
        });
    </script>
</body>
</html>
