<?php
session_start();
include "db.php";

// Ensure bookings table exists
$bookingsTable = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
if ($bookingsTable && mysqli_num_rows($bookingsTable) === 0) {
    $createBookings = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        maid_id INT NULL,
        service_type VARCHAR(100) NOT NULL,
        service_date DATE NOT NULL,
        service_time VARCHAR(50) NOT NULL,
        duration VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Booked',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $createBookings);
} else {
    // Ensure maid_id column exists
    $maidIdCol = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'maid_id'");
    if ($maidIdCol && mysqli_num_rows($maidIdCol) === 0) {
        mysqli_query($conn, "ALTER TABLE bookings ADD maid_id INT NULL AFTER user_id");
    }
}

// Ensure users table tracks booking count
$bookingCountCol = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'booking_count'");
if ($bookingCountCol && mysqli_num_rows($bookingCountCol) === 0) {
    mysqli_query($conn, "ALTER TABLE users ADD booking_count INT NOT NULL DEFAULT 0");
}

$errors = [];

// Booking submission (NO LOGIN REQUIRED)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["finalSubmit"])) {

    $uid = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;

    $service  = trim($_POST["service"] ?? '');
    $date     = trim($_POST["date"] ?? '');
    $time     = trim($_POST["time"] ?? '');
    $duration = trim($_POST["duration"] ?? '');
    $address  = trim($_POST["address"] ?? '');

    if ($service === '' || $date === '' || $time === '' || $duration === '' || $address === '') {
        $errors[] = "Please complete all required fields.";
    }

    $priceList = [
        "Cleaning" => 500,
        "Baby-sitter"  => 400,
        "Cooking"        => 600,
        "Elder-care" => 1500
    ];

    $price = $priceList[$service] ?? null;
    if ($price === null) {
        $errors[] = "Please select a valid service.";
    }

    if (empty($errors)) {
        $userIdValue = $uid !== null ? $uid : "NULL";

        $serviceEsc  = mysqli_real_escape_string($conn, $service);
        $dateEsc     = mysqli_real_escape_string($conn, $date);
        $timeEsc     = mysqli_real_escape_string($conn, $time);
        $durationEsc = mysqli_real_escape_string($conn, $duration);
        $addressEsc  = mysqli_real_escape_string($conn, $address);

        $sql = "INSERT INTO bookings 
                (user_id, service_type, service_date, service_time, duration, address, amount, status) 
                VALUES 
                ($userIdValue, '$serviceEsc', '$dateEsc', '$timeEsc', '$durationEsc', '$addressEsc', '$price', 'Booked')";

        if (mysqli_query($conn, $sql)) {
            $bookingId = mysqli_insert_id($conn);
            $_SESSION['current_booking_id'] = $bookingId;
            
            if ($uid !== null) {
                mysqli_query($conn, "UPDATE users SET booking_count = booking_count + 1 WHERE id = $uid");
            }
            header("Location: maids.php");
            exit();
        } else {
            $errors[] = "Failed to save your booking. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Maid Service - Helphive</title>
    <meta name="description" content="Book trusted and verified maid services online.">
    <link rel="stylesheet" href="style.css">
</head>

<body class="booking-page">

    <!---------------- NAVBAR ---------------->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php"><h2>Helphive</h2></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="booking.php" class="active">Book Now</a></li>
                <li><a href="maids.php">Our Maids</a></li>
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

    <!---------------- HERO SECTION ---------------->
    <section class="hero">
        <div class="container">
            <h1>Book Your Cleaning Service</h1>
            <p>Simple, fast, and reliable booking in just 3 steps</p>
        </div>
    </section>

    <!---------------- MAIN BOOKING AREA ---------------->
    <div class="container">

        <div class="progress-steps">
            <div class="step active"><div class="step-number">1</div><div class="step-text">Service Details</div></div>
            <div class="step"><div class="step-number">2</div><div class="step-text">Select Maid</div></div>
            <div class="step"><div class="step-number">3</div><div class="step-text">Payment</div></div>
        </div>

        <div class="booking-layout">

            <!---------------- LEFT SIDE (FORM) ---------------->
            <div class="booking-main">

                <h2>Select Service Details</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">

                    <div class="form-group">
                        <label>Service Type *</label>
                        <div class="service-grid">

                            <label class="service-card">
                                <input type="radio" name="service" value=" Cleaning" required data-price="500">
                                <div class="service-icon">🏡</div>
                                <h3>Daily Cleaning</h3>
                                <span class="price">₹500/day</span>
                            </label>

                            <label class="service-card">
                                <input type="radio" name="service" value="Baby-sitter" data-price="400">
                                <div class="service-icon">✨</div>
                                <h3>Baby-sitter</h3>
                                <span class="price">₹400/session</span>
                            </label>

                            <label class="service-card">
                                <input type="radio" name="service" value="Cooking" data-price="600">
                                <div class="service-icon">🍳</div>
                                <h3>Cooking</h3>
                                <span class="price">₹600/day</span>
                            </label>

                            <label class="service-card">
                                <input type="radio" name="service" value="Full-Time Maid" data-price="1500">
                                <div class="service-icon">👤</div>
                                <h3>elderly care</h3>
                                <span class="price">₹1500/month</span>
                            </label>

                        </div>
                    </div>

                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label>Preferred Time *</label>
                        <select id="time" name="time" required>
                            <option value="">Select time</option>
                            <option value="Morning">Morning (8 AM - 12 PM)</option>
                            <option value="Afternoon">Afternoon (12 PM - 4 PM)</option>
                            <option value="Evening">Evening (4 PM - 8 PM)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Duration *</label>
                        <select id="duration" name="duration" required>
                            <option value="">Select duration</option>
                            <option value="1 Hour">1 Hour</option>
                            <option value="2 Hours">2 Hours</option>
                            <option value="3 Hours">3 Hours</option>
                            <option value="4 Hours">4 Hours</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Service Address *</label>
                        <textarea id="address" name="address" rows="3" required placeholder="Enter your complete address"></textarea>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="finalSubmit" class="btn-primary">
                            Choose Maids
                        </button>
                    </div>

                </form>

            </div>

            <!---------------- RIGHT SIDE (SUMMARY) ---------------->
            <div class="booking-sidebar">
                <div class="booking-summary">
                    <h3>Booking Summary</h3>

                    <div class="summary-item">
                        <span>Service:</span>
                        <strong id="summaryService">Not selected</strong>
                    </div>

                    <div class="summary-item">
                        <span>Date:</span>
                        <strong id="summaryDate">Not selected</strong>
                    </div>

                    <div class="summary-item">
                        <span>Time:</span>
                        <strong id="summaryTime">Not selected</strong>
                    </div>

                    <div class="summary-total">
                        <span>Total:</span>
                        <strong id="summaryTotal">₹0</strong>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!---------------- FOOTER ---------------->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Helphive. All rights reserved.</p>
        </div>
    </footer>

    <!---------------- LIVE SUMMARY SCRIPT ---------------->
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const serviceInputs = document.querySelectorAll("input[name='service']");
        const dateInput     = document.getElementById("date");
        const timeInput     = document.getElementById("time");

        const summaryService = document.getElementById("summaryService");
        const summaryDate    = document.getElementById("summaryDate");
        const summaryTime    = document.getElementById("summaryTime");
        const summaryTotal   = document.getElementById("summaryTotal");

        let selectedPrice = 0;

        // Update service summary + price
        serviceInputs.forEach(input => {
            input.addEventListener("change", function () {
                const card = this.closest(".service-card");
                const title = card.querySelector("h3").textContent;
                selectedPrice = parseInt(this.dataset.price);

                summaryService.textContent = title;
                summaryTotal.textContent = "₹" + selectedPrice;
            });
        });

        dateInput.addEventListener("change", function () {
            summaryDate.textContent = this.value || "Not selected";
        });

        timeInput.addEventListener("change", function () {
            summaryTime.textContent = this.value || "Not selected";
        });

    });
    </script>

</body>
</html>
