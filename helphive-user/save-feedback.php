<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Ensure reviews table exists
$reviewsTable = mysqli_query($conn, "SHOW TABLES LIKE 'reviews'");
if ($reviewsTable && mysqli_num_rows($reviewsTable) === 0) {
    $createReviews = "CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        user_id INT NULL,
        maid_id INT NULL,
        maid_name VARCHAR(100) NOT NULL,
        rating INT NULL,
        review_title VARCHAR(255) NULL,
        review_text TEXT NOT NULL,
        reviewer_name VARCHAR(100) NOT NULL,
        reviewer_email VARCHAR(255) NOT NULL,
        type ENUM('review', 'feedback', 'complaint') DEFAULT 'review',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $createReviews);
}

$bookingId = (int)$_POST['booking_id'];
$type = mysqli_real_escape_string($conn, $_POST['type'] ?? 'review');
$maidId = isset($_POST['maid_id']) && $_POST['maid_id'] ? (int)$_POST['maid_id'] : null;
$maidName = mysqli_real_escape_string($conn, $_POST['maid_name'] ?? 'Not assigned');
$reviewText = mysqli_real_escape_string($conn, trim($_POST['review_text'] ?? ''));
$reviewerName = mysqli_real_escape_string($conn, trim($_POST['reviewer_name'] ?? ''));
$reviewerEmail = mysqli_real_escape_string($conn, trim($_POST['reviewer_email'] ?? ''));
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
$reviewTitle = isset($_POST['review_title']) ? mysqli_real_escape_string($conn, trim($_POST['review_title'])) : null;

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// Validate required fields
// For reviews, only rating is required (review_text can be empty)
if ($type === 'review') {
    if (!$rating) {
        echo "<script>alert('Please provide a rating.'); window.location='feedback.php?booking_id=$bookingId';</script>";
        exit();
    }
    // Set default review text if empty for reviews
    if (empty($reviewText)) {
        $reviewText = 'Rating: ' . $rating . ' stars';
    }
} else {
    // For feedback and complaint, review_text is required
    if (empty($reviewText) || empty($reviewerName) || empty($reviewerEmail)) {
        echo "<script>alert('Please fill in all required fields.'); window.location='feedback.php?booking_id=$bookingId';</script>";
        exit();
    }
}

// Insert into reviews table
$maidIdValue = $maidId ? $maidId : "NULL";
$userIdValue = $userId ? $userId : "NULL";
$ratingValue = $rating ? $rating : "NULL";
$reviewTitleValue = $reviewTitle ? "'$reviewTitle'" : "NULL";

$insertSql = "INSERT INTO reviews (booking_id, user_id, maid_id, maid_name, rating, review_title, review_text, reviewer_name, reviewer_email, type) 
              VALUES ($bookingId, $userIdValue, $maidIdValue, '$maidName', $ratingValue, $reviewTitleValue, '$reviewText', '$reviewerName', '$reviewerEmail', '$type')";

if (mysqli_query($conn, $insertSql)) {
    // Clear feedback booking session
    unset($_SESSION['feedback_booking_id']);
    
    $message = $type === 'review' ? 'Thank you for your review!' : 
               ($type === 'complaint' ? 'Your complaint has been submitted. We will look into it.' : 
                'Thank you for your feedback!');
    
    echo "<script>alert('$message'); window.location='index.php';</script>";
    exit();
} else {
    echo "<script>alert('Error saving feedback: " . mysqli_error($conn) . "'); window.location='feedback.php?booking_id=$bookingId';</script>";
    exit();
}
?>
