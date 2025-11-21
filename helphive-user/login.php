<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);

        if (password_verify($password, $row['password'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['fullname'];

            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Wrong Password'); window.location='user-login.php';</script>";
        }
    } else {
        echo "<script>alert('Email Not Found'); window.location='user-login.php';</script>";
    }
}
?>
<link rel="stylesheet" href="styles1.css">
