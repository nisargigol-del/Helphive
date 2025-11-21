<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Ensure the database schema matches the expected columns.
    $expectedColumns = [
        'fullname' => 'VARCHAR(255) NOT NULL',
        'email'    => 'VARCHAR(255) NOT NULL',
        'phone'    => 'VARCHAR(50) NOT NULL',
        'address'  => 'TEXT NOT NULL',
        'password' => 'VARCHAR(255) NOT NULL'
    ];

    foreach ($expectedColumns as $column => $definition) {
        $columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$column'");
        if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
            mysqli_query($conn, "ALTER TABLE users ADD `$column` $definition");
        }
    }

    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $address  = $_POST['address'];
    $password = $_POST['password'];
    $cpass    = $_POST['confirm-password'];

    if ($password != $cpass) {
        echo "<script>alert('Passwords do not match');window.location='user-registration.php';</script>";
        exit();
    }

    $passHash = password_hash($password, PASSWORD_DEFAULT);

    $existing = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
    if ($existing && mysqli_num_rows($existing) > 0) {
        echo "<script>alert('Email already registered. Please login.'); window.location='user-login.php';</script>";
        exit();
    }

    $sql = "INSERT INTO users(fullname, email, phone, address, password)
            VALUES('$fullname', '$email', '$phone', '$address', '$passHash')";

    if (mysqli_query($conn, $sql)) {
        header("Location: user-login.php?registered=1");
        exit();
    } else {
        echo mysqli_error($conn);
    }
}
?>
 