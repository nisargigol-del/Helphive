<?php
session_start();
include "db.php";

// Check if user has a booking
$bookingId = isset($_SESSION['current_booking_id']) ? (int)$_SESSION['current_booking_id'] : null;

if (!$bookingId) {
    header("Location: booking.php");
    exit();
}

// Get booking details
$bookingSql = "SELECT b.*, m.name as maid_name, m.image as maid_image 
                FROM bookings b 
                LEFT JOIN maids m ON b.maid_id = m.id 
                WHERE b.id = $bookingId";
$bookingResult = mysqli_query($conn, $bookingSql);
$booking = mysqli_fetch_assoc($bookingResult);

if (!$booking) {
    header("Location: booking.php");
    exit();
}

$subtotal = (float)$booking['amount'];
$tax = round($subtotal * 0.10, 2); // 10% tax
$total = $subtotal + $tax;

// Handle payment submission
$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["process_payment"])) {
    $paymentMethod = trim($_POST["payment_method"] ?? '');
    
    if (empty($paymentMethod)) {
        $errors[] = "Please select a payment method.";
    } else {
        // For card payments, validate card details
        if ($paymentMethod === 'card') {
            $cardNumber = trim($_POST["card_number"] ?? '');
            $cardName = trim($_POST["card_name"] ?? '');
            $expiry = trim($_POST["expiry"] ?? '');
            $cvv = trim($_POST["cvv"] ?? '');
            
            if (empty($cardNumber) || empty($cardName) || empty($expiry) || empty($cvv)) {
                $errors[] = "Please fill in all card details.";
            }
        }
        
        if (empty($errors)) {
            // Update booking status to Paid
            $paymentMethodEsc = mysqli_real_escape_string($conn, $paymentMethod);
            $updateSql = "UPDATE bookings SET status = 'Paid', payment_method = '$paymentMethodEsc' WHERE id = $bookingId";
            
            // Ensure payment_method column exists
            $paymentCol = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'payment_method'");
            if ($paymentCol && mysqli_num_rows($paymentCol) === 0) {
                mysqli_query($conn, "ALTER TABLE bookings ADD payment_method VARCHAR(50) NULL AFTER status");
            }
            
            if (mysqli_query($conn, $updateSql)) {
                // Store booking ID in session for feedback page
                $_SESSION['feedback_booking_id'] = $bookingId;
                unset($_SESSION['selected_maid_id']);
                header("Location: feedback.php?booking_id=" . $bookingId);
                exit();
            } else {
                $errors[] = "Payment processing failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Helphive</title>
    <meta name="description" content="Complete your booking with secure payment options.">
    <link rel="stylesheet" href="style.css">
</head>

<body class="payment-page">

    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🏠 Helphive</a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="maids.php">Our Maids</a></li>
                <li><a href="booking.php">Book Now</a></li>
                <li><a href="review_feedback_complaint.php">Feedback</a></li>
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
            <h1>Secure Payment</h1>
            <p>Complete your booking with confidence</p>
        </div>
    </section>

    <div class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert error" style="padding: 1rem; background: #fee; color: #c33; border-radius: 8px; margin: 1rem 0;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="payment-layout">
            <div class="payment-main">
                <h2>Select Payment Method</h2>
                
                <form action="" method="POST" id="paymentForm">
                   

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="upi" id="upi">
                        <div class="option-content">
                            <div class="option-icon">📱</div>
                            <div class="option-text">
                                <h3>UPI</h3>
                                <p>PhonePe, Google Pay, Paytm & more</p>
                            </div>
                        </div>
                    </label>


                   

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" id="cod">
                        <div class="option-content">
                            <div class="option-icon">💵</div>
                            <div class="option-text">
                                <h3>Cash on Service</h3>
                                <p>Pay when service is delivered</p>
                            </div>
                        </div>
                    </label>

                    <button type="submit" name="process_payment" class="btn-primary" style="width: 100%; margin-top: 1rem;">Pay ₹<?php echo number_format($total, 2); ?></button>
                </form>
            </div>

            <div class="payment-sidebar">
                <div class="booking-summary">
                    <h3>Order Summary</h3>
                    
                    <?php if ($booking['maid_name']): ?>
                        <div class="summary-item" style="text-align: center; padding: 1rem 0; border-bottom: 1px solid #eee; margin-bottom: 1rem;">
                            <img src="<?php echo htmlspecialchars($booking['maid_image'] ?? 'https://i.pravatar.cc/150'); ?>" 
                                 alt="<?php echo htmlspecialchars($booking['maid_name']); ?>"
                                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 0.5rem;">
                            <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($booking['maid_name']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="summary-item">
                        <span>Service</span>
                        <strong><?php echo htmlspecialchars($booking['service_type']); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Date</span>
                        <strong><?php echo date('d M Y', strtotime($booking['service_date'])); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Time</span>
                        <strong><?php echo htmlspecialchars($booking['service_time']); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Duration</span>
                        <strong><?php echo htmlspecialchars($booking['duration']); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Tax</span>
                        <strong>₹<?php echo number_format($tax, 2); ?></strong>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <strong>₹<?php echo number_format($total, 2); ?></strong>
                    </div>
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
        // Show/hide card form based on payment method selection
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const cardForm = document.getElementById('cardForm');
            
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    if (this.value === 'card') {
                        cardForm.classList.add('active');
                        // Make card fields required
                        document.getElementById('cardNumber').required = true;
                        document.getElementById('cardName').required = true;
                        document.getElementById('expiry').required = true;
                        document.getElementById('cvv').required = true;
                    } else {
                        cardForm.classList.remove('active');
                        // Remove required from card fields
                        document.getElementById('cardNumber').required = false;
                        document.getElementById('cardName').required = false;
                        document.getElementById('expiry').required = false;
                        document.getElementById('cvv').required = false;
                    }
                });
            });

            // Format card number with spaces
            const cardNumberInput = document.getElementById('cardNumber');
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s/g, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formattedValue;
                });
            }

            // Format expiry date
            const expiryInput = document.getElementById('expiry');
            if (expiryInput) {
                expiryInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    e.target.value = value;
                });
            }
        });
    </script>

</body>
</html>
