<?php
session_start();
include "db.php";

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject'] ?? ''));
    $message = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));

    // Validate required fields
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Ensure contacts table exists
        $contactsTable = mysqli_query($conn, "SHOW TABLES LIKE 'contacts'");
        if ($contactsTable && mysqli_num_rows($contactsTable) === 0) {
            $createContacts = "CREATE TABLE contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                subject VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                user_id INT NULL,
                status VARCHAR(50) DEFAULT 'New',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            mysqli_query($conn, $createContacts);
        }

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $userIdValue = $userId ? $userId : "NULL";

        // Insert contact message
        $insertSql = "INSERT INTO contacts (name, email, phone, subject, message, user_id) 
                      VALUES ('$name', '$email', '$phone', '$subject', '$message', $userIdValue)";

        if (mysqli_query($conn, $insertSql)) {
            $success = "Thank you for contacting us! We'll get back to you soon.";
            // Clear form data
            $_POST = array();
        } else {
            $error = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}

// Get user details if logged in
$userName = '';
$userEmail = '';
$userPhone = '';
if (isset($_SESSION['user_id'])) {
    $userSql = "SELECT fullname, email, phone FROM users WHERE id = " . (int)$_SESSION['user_id'];
    $userResult = mysqli_query($conn, $userSql);
    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $user = mysqli_fetch_assoc($userResult);
        $userName = $user['fullname'] ?? '';
        $userEmail = $user['email'] ?? '';
        $userPhone = $user['phone'] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Helphive</title>
    <meta name="description" content="Get in touch with Helphive. We're here to help with all your queries.">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <h2>Helphive</h2>
                <p class="tagline">The new way to hire a trusted maid</p>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="booking.php">Book Now</a></li>
                <li><a href="maids.php">Our Maids</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn-outline">Logout</a>
                <?php else: ?>
                    <a href="user-login.php" class="btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1>📧 Contact Us</h1>
            <p>We're here to help! Reach out with any questions or concerns</p>
        </div>
    </section>

    <div class="container">
        <?php if ($success): ?>
            <div class="alert success" style="padding: 1rem; background: #d4edda; color: #155724; border-radius: 8px; margin: 1rem 0; border: 1px solid #c3e6cb;">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error" style="padding: 1rem; background: #f8d7da; color: #721c24; border-radius: 8px; margin: 1rem 0; border: 1px solid #f5c6cb;">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin: 3rem 0;">
            <!-- Contact Form -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">📨 Send Us a Message</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">👤 Your Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userName); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">📧 Email Address:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">📞 Phone Number:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">🏷️ Subject:</label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="booking">Booking Inquiry</option>
                            <option value="complaint">Complaint</option>
                            <option value="feedback">Feedback</option>
                            <option value="partnership">Partnership</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">💬 Your Message:</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" name="submit_contact" class="btn-primary" style="width: 100%;">📤 Send Message</button>
                </form>
            </div>

            <!-- Contact Information -->
            <div>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1.5rem;">📍 Contact Information</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.5rem;">📞 Phone</h4>
                        <p><a href="tel:+911234567890" style="color: #4A90E2; text-decoration: none;">+91 123-456-7890</a></p>
                        <p><a href="tel:+919876543210" style="color: #4A90E2; text-decoration: none;">+91 987-654-3210</a></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.5rem;">📧 Email</h4>
                        <p><a href="mailto:info@helphive.com" style="color: #4A90E2; text-decoration: none;">info@helphive.com</a></p>
                        <p><a href="mailto:support@helphive.com" style="color: #4A90E2; text-decoration: none;">support@helphive.com</a></p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="margin-bottom: 0.5rem;">📍 Address</h4>
                        <p>123 Service Street<br>
                        Andheri West<br>
                        Mumbai, Maharashtra 400058<br>
                        India</p>
                    </div>

                    <div>
                        <h4 style="margin-bottom: 0.5rem;">⏰ Business Hours</h4>
                        <p>Monday - Saturday: 8:00 AM - 8:00 PM</p>
                        <p>Sunday: 9:00 AM - 5:00 PM</p>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1.5rem;">❓ Frequently Asked Questions</h3>
                    
                    <div style="margin-bottom: 1rem;">
                        <h4 style="margin-bottom: 0.5rem;">How do I book a maid?</h4>
                        <p style="font-size: 0.9rem;">Simply visit our booking page, select your service, choose a maid, and complete the payment.</p>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <h4 style="margin-bottom: 0.5rem;">Are all maids verified?</h4>
                        <p style="font-size: 0.9rem;">Yes, all our maids undergo thorough background checks and verification before joining our platform.</p>
                    </div>

                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Can I cancel my booking?</h4>
                        <p style="font-size: 0.9rem;">Yes, you can cancel up to 24 hours before your scheduled service for a full refund.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Helphive</h3>
                    <p>Your trusted partner for professional maid services. Making homes cleaner and lives easier.</p>
                </div>
                
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="booking.php">Book Now</a></li>
                        <li><a href="maids.php">Our Maids</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Cleaning</a></li>
                        <li><a href="#">Cooking</a></li>
                        <li><a href="#">Baby Sitting</a></li>
                        <li><a href="#">Elderly Care</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="tel:+911234567890">+91 123-456-7890</a></li>
                        <li><a href="mailto:info@helphive.com">info@helphive.com</a></li>
                        <li><a href="#">Mumbai, India</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Helphive. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>

