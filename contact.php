<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact Us';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // In a real app, you'd save to DB or send email
    $success = true;
}

include 'includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Get in <span class="gradient-text">Touch</span></h1>
        <p>Have a question about an instrument? We're here to help.</p>
    </div>
</div>

<div class="section">
    <div class="container">
        <div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:60px; align-items:start;">
            
            <aside>
                <div class="card mb-4" style="background:var(--bg-card); border-left: 4px solid var(--primary);">
                    <div class="card-body">
                        <h3 style="margin-bottom:20px; font-size:1.1rem; color:var(--text-primary);">📍 Our Location</h3>
                        <p style="font-size:0.9rem; color:var(--text-secondary); line-height:1.6;">
                            123 Harmony Lane<br>
                            London, EC1 2AB<br>
                            United Kingdom
                        </p>
                    </div>
                </div>

                <div class="card mb-4" style="background:var(--bg-card); border-left: 4px solid var(--secondary);">
                    <div class="card-body">
                        <h3 style="margin-bottom:20px; font-size:1.1rem; color:var(--text-primary);">📞 Contact Details</h3>
                        <p style="font-size:0.9rem; color:var(--text-secondary); margin-bottom:10px;">
                            <strong>Phone:</strong> +44 20 1234 5678
                        </p>
                        <p style="font-size:0.9rem; color:var(--text-secondary);">
                            <strong>Email:</strong> info@melodymasters.com
                        </p>
                    </div>
                </div>

                <div class="card" style="background:var(--bg-card); border-left: 4px solid var(--accent);">
                    <div class="card-body">
                        <h3 style="margin-bottom:20px; font-size:1.1rem; color:var(--text-primary);">🕒 Opening Hours</h3>
                        <div style="font-size:0.85rem; color:var(--text-secondary);">
                            <div class="flex-between mb-1"><span>Mon - Fri:</span> <span>9:00 AM - 7:00 PM</span></div>
                            <div class="flex-between mb-1"><span>Saturday:</span> <span>10:00 AM - 6:00 PM</span></div>
                            <div class="flex-between"><span>Sunday:</span> <span>Closed</span></div>
                        </div>
                    </div>
                </div>
            </aside>

            <div>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="padding:32px; flex-direction:column; align-items:center; text-align:center;">
                        <div style="font-size:3rem; margin-bottom:16px;">✉️</div>
                        <h3>Message Sent!</h3>
                        <p>Thank you for reaching out. Our team will get back to you within 24 hours.</p>
                        <a href="contact.php" class="btn btn-success btn-sm mt-3">Send Another Message</a>
                    </div>
                <?php else: ?>
                    <div class="card" style="padding:8px;">
                        <div class="card-header"><h2 style="font-size:1.1rem; margin:0;">Send us a <span class="gradient-text">Message</span></h2></div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label class="form-label">Your Name</label>
                                        <input type="text" name="name" class="form-control" required placeholder="John Doe">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" required placeholder="john@example.com">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control" required placeholder="Product Inquiry">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Message</label>
                                    <textarea name="message" class="form-control" rows="6" required placeholder="How can we help you?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg btn-block mt-2">Send Message 🚀</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
