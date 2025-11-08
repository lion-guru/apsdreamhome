<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-lg-6">
                <h1 class="h3 mb-3">Get in Touch</h1>
                <p class="text-muted">Have questions about a property or need help with buying/selling? Send us a message and we’ll respond shortly.</p>

                <form action="/contact" method="POST" class="mt-4">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea id="message" name="message" rows="5" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h5">Contact Information</h2>
                        <p class="text-muted">Reach us directly using the details below.</p>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><strong>Address:</strong> 123 Dream Street, City, Country</li>
                            <li class="mb-2"><strong>Phone:</strong> <a href="tel:+1234567890">+1 (234) 567-890</a></li>
                            <li class="mb-2"><strong>Email:</strong> <a href="mailto:info@apsdreamhome.com">info@apsdreamhome.com</a></li>
                            <li class="mb-2"><strong>Working Hours:</strong> Mon - Sat, 9:00 AM - 6:00 PM</li>
                        </ul>
                        <div class="ratio ratio-16x9 mt-3">
                            <iframe src="https://maps.google.com/maps?q=mumbai&t=&z=13&ie=UTF8&iwloc=&output=embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
