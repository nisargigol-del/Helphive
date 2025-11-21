<?php
session_start();
require "config.php";

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $stmt = mysqli_prepare($conn, "INSERT INTO complaints (user_id, subject, message) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $subject, $message);
    mysqli_stmt_execute($stmt);

    echo "<script>alert('Complaint submitted successfully!'); window.location='userdashboard.php';</script>";
    exit;
}
?>
