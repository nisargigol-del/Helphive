<?php
session_start();
// Ensure database connection exists
if (!file_exists("db.php")) {
    die("Error: db.php not found.");
}
include "db.php";

// 1. FIX: Sanitize input to prevent SQL Injection
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : (isset($_SESSION['feedback_booking_id']) ? (int)$_SESSION['feedback_booking_id'] : 0);

// If no valid booking ID, redirect
if ($bookingId <= 0) {
    header("Location: index.php");
    exit();
}

// 2. FIX: Check if query was successful before fetching
$bookingSql = "SELECT b.*, m.name as maid_name 
                FROM bookings b 
                LEFT JOIN maids m ON b.maid_id = m.id 
                WHERE b.id = $bookingId";
$bookingResult = mysqli_query($conn, $bookingSql);

if (!$bookingResult || mysqli_num_rows($bookingResult) === 0) {
    // Booking not found
    header("Location: index.php");
    exit();
}

$booking = mysqli_fetch_assoc($bookingResult);

// Get user details if logged in
$userName = '';
$userEmail = '';
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $userSql = "SELECT fullname, email FROM users WHERE id = $userId";
    $userResult = mysqli_query($conn, $userSql);
    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $user = mysqli_fetch_assoc($userResult);
        $userName = $user['fullname'];
        $userEmail = $user['email'];
    }
}

// 3. FIX: Updated Table Creation to allow NULLs (prevents insert errors for different form types)
$reviewsTable = mysqli_query($conn, "SHOW TABLES LIKE 'reviews'");
if ($reviewsTable && mysqli_num_rows($reviewsTable) === 0) {
    $createReviews = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        user_id INT NULL,
        maid_id INT NULL,
        maid_name VARCHAR(100) NOT NULL,
        rating INT DEFAULT 0,
        review_title VARCHAR(255) DEFAULT NULL,
        review_text TEXT NOT NULL,
        reviewer_name VARCHAR(100) NOT NULL,
        reviewer_email VARCHAR(255) NOT NULL,
        type ENUM('review', 'feedback', 'complaint') DEFAULT 'review',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $createReviews);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review & Feedback - Helphive</title>
    <meta name="description" content="Share your experience with our maid services.">
    <link rel="stylesheet" href="style.css">
</head>

<body class="feedback-page">

    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php"><h2>Helphive</h2></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="booking.php">Book Now</a></li>
                <li><a href="maids.php">Our Maids</a></li>
                <li><a href="feedback.php" class="active">Feedback</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav-buttons" style="display: flex; gap: 1rem; align-items: center;">
                <a href="../helphive-admin/admin.html" class="btn-outline">Admin</a>
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
            <h1>We Value Your Feedback</h1>
            <p>Help us serve you better by sharing your experience</p>
        </div>
    </section>

    <div class="container">
        <div class="feedback-layout">
            <div class="feedback-main">
                <div class="feedback-types">
                    <button class="feedback-type-btn active" data-type="review">
                        <div class="type-icon">⭐</div>
                        <h3>Write a Review</h3>
                        <p>Rate your experience</p>
                    </button>
                    <button class="feedback-type-btn" data-type="feedback">
                        <div class="type-icon">💬</div>
                        <h3>Give Feedback</h3>
                        <p>Suggestions & comments</p>
                    </button>
                    <button class="feedback-type-btn" data-type="complaint">
                        <div class="type-icon">⚠️</div>
                        <h3>File Complaint</h3>
                        <p>Report an issue</p>
                    </button>
                </div>

                <div class="form-container active" id="reviewForm">
                    <h2>Rate Your Experience</h2>
                    <form action="save-feedback.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                        <input type="hidden" name="type" value="review">
                        <input type="hidden" name="maid_id" value="<?php echo $booking['maid_id'] ?? 0; ?>">
                        <input type="hidden" name="maid_name" value="<?php echo htmlspecialchars($booking['maid_name'] ?? 'Unknown'); ?>">
                        <input type="hidden" name="reviewer_name" value="<?php echo htmlspecialchars($userName ?: 'Guest'); ?>">
                        <input type="hidden" name="reviewer_email" value="<?php echo htmlspecialchars($userEmail ?: 'guest@example.com'); ?>">
                        
                        <input type="hidden" name="review_title" value="Star Rating">
                        <input type="hidden" name="review_text" value="Rating submitted via star system">

                        <div style="text-align: center; padding: 2rem 0;">
                            <div style="margin-bottom: 1rem;">
                                <p style="font-size: 1.1rem; color: #666; margin-bottom: 0.5rem;">Booking: MB<?php echo str_pad($bookingId, 10, '0', STR_PAD_LEFT); ?></p>
                                <p style="font-size: 1.1rem; color: #666;">Maid: <?php echo htmlspecialchars($booking['maid_name'] ?? 'Not assigned'); ?></p>
                            </div>

                            <div class="form-group" style="margin: 2rem 0;">
                                <label style="font-size: 1.2rem; margin-bottom: 1rem; display: block;">Overall Rating *</label>
                                <div class="star-rating" style="display: flex; justify-content: center; gap: 0.5rem; font-size: 3rem; flex-direction: row-reverse;">
                                    <input type="radio" name="rating" id="star5" value="5" required style="display: none;">
                                    <label for="star5" style="cursor: pointer; color: #ccc; transition: all 0.2s; user-select: none;">★</label>
                                    <input type="radio" name="rating" id="star4" value="4" style="display: none;">
                                    <label for="star4" style="cursor: pointer; color: #ccc; transition: all 0.2s; user-select: none;">★</label>
                                    <input type="radio" name="rating" id="star3" value="3" style="display: none;">
                                    <label for="star3" style="cursor: pointer; color: #ccc; transition: all 0.2s; user-select: none;">★</label>
                                    <input type="radio" name="rating" id="star2" value="2" style="display: none;">
                                    <label for="star2" style="cursor: pointer; color: #ccc; transition: all 0.2s; user-select: none;">★</label>
                                    <input type="radio" name="rating" id="star1" value="1" style="display: none;">
                                    <label for="star1" style="cursor: pointer; color: #ccc; transition: all 0.2s; user-select: none;">★</label>
                                </div>
                                <p id="ratingText" style="margin-top: 1rem; font-size: 1rem; color: #666;"></p>
                            </div>

                            <button type="submit" class="btn-primary" style="width: 100%; max-width: 300px; margin-top: 2rem;">Submit Rating</button>
                        </div>
                    </form>
                </div>

                <div class="form-container" id="feedbackForm" style="display: none;">
                    <h2>Give Feedback</h2>
                    <form action="save-feedback.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                        <input type="hidden" name="type" value="feedback">
                        <input type="hidden" name="maid_id" value="<?php echo $booking['maid_id'] ?? 0; ?>">
                        <input type="hidden" name="maid_name" value="<?php echo htmlspecialchars($booking['maid_name'] ?? 'Unknown'); ?>">
                        
                        <input type="hidden" name="rating" value="0">
                        <input type="hidden" name="review_title" value="General Feedback">

                        <div class="form-group">
                            <label for="feedbackText">Your Feedback *</label>
                            <textarea id="feedbackText" name="review_text" rows="5" placeholder="Share your suggestions and comments..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="feedbackName">Your Name *</label>
                            <input type="text" id="feedbackName" name="reviewer_name" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="feedbackEmail">Email *</label>
                            <input type="email" id="feedbackEmail" name="reviewer_email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%;">Submit Feedback</button>
                    </form>
                </div>

                <div class="form-container" id="complaintForm" style="display: none;">
                    <h2>File a Complaint</h2>
                    <form action="save-feedback.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                        <input type="hidden" name="type" value="complaint">
                        <input type="hidden" name="maid_id" value="<?php echo $booking['maid_id'] ?? 0; ?>">
                        <input type="hidden" name="maid_name" value="<?php echo htmlspecialchars($booking['maid_name'] ?? 'Unknown'); ?>">

                        <input type="hidden" name="rating" value="0">
                        <input type="hidden" name="review_title" value="Complaint Filed">

                        <div class="form-group">
                            <label for="complaintText">Describe the Issue *</label>
                            <textarea id="complaintText" name="review_text" rows="5" placeholder="Please describe the issue in detail..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="complaintName">Your Name *</label>
                            <input type="text" id="complaintName" name="reviewer_name" value="<?php echo htmlspecialchars($userName); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="complaintEmail">Email *</label>
                            <input type="email" id="complaintEmail" name="reviewer_email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%;">Submit Complaint</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Helphive. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="contact.php">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
        // Handle feedback type buttons
        document.querySelectorAll('.feedback-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.feedback-type-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Hide all forms
                document.querySelectorAll('.form-container').forEach(form => {
                    form.style.display = 'none';
                    form.classList.remove('active');
                });

                // Show selected form
                const type = this.dataset.type;
                const formId = type + 'Form';
                const form = document.getElementById(formId);
                if (form) {
                    form.style.display = 'block';
                    // Small delay to allow display block to apply before adding active class for animation
                    setTimeout(() => form.classList.add('active'), 10);
                }
            });
        });

        // Star rating interaction for review form
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            const starInputs = reviewForm.querySelectorAll('.star-rating input[type="radio"]');
            const starLabels = reviewForm.querySelectorAll('.star-rating label');
            const ratingText = document.getElementById('ratingText');
            const totalStars = starLabels.length;
            
            const ratingLabels = {
                1: 'Poor',
                2: 'Fair',
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };

            // Function to fill stars from right to left
            function fillStars(rating, labels) {
                labels.forEach((label, idx) => {
                    // Calculate position from right (5, 4, 3, 2, 1)
                    const positionFromRight = totalStars - idx;
                    if (positionFromRight <= rating) {
                        label.style.color = '#ffd700';
                        label.style.transform = 'scale(1.2)';
                        label.textContent = '★';
                    } else {
                        label.style.color = '#ccc';
                        label.style.transform = 'scale(1)';
                        label.textContent = '★';
                    }
                });
            }

            starInputs.forEach((radio, index) => {
                radio.addEventListener('change', function() {
                    const rating = parseInt(this.value);
                    fillStars(rating, starLabels);
                    if (ratingText) {
                        ratingText.textContent = ratingLabels[rating] || '';
                        ratingText.style.color = '#4A90E2';
                        ratingText.style.fontWeight = '600';
                    }
                });

                // Hover effect
                starLabels[index].addEventListener('mouseenter', function() {
                    const hoverRating = totalStars - index;
                    fillStars(hoverRating, starLabels);
                });
            });

            // Reset on mouse leave if no selection
            reviewForm.querySelector('.star-rating').addEventListener('mouseleave', function() {
                const selected = reviewForm.querySelector('.star-rating input[type="radio"]:checked');
                if (!selected) {
                    starLabels.forEach(label => {
                        label.style.color = '#ccc';
                        label.style.transform = 'scale(1)';
                    });
                    if (ratingText) ratingText.textContent = '';
                } else {
                    fillStars(parseInt(selected.value), starLabels);
                }
            });
        }
    </script>

</body>
</html>