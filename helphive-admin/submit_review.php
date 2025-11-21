<?php
session_start();
require "config.php";

if (!isset($_SESSION['user_id'])) {
    die("Login required!");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $maid_id = $_POST['maid_id'];
    $rating = $_POST['rating'];
    $review = trim($_POST['review']);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO reviews (user_id, maid_id, rating, review) 
         VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiis", $user_id, $maid_id, $rating, $review);
    mysqli_stmt_execute($stmt);

    echo "<script>alert('Review submitted successfully!');history.back();</script>";
    exit;
}
?>
