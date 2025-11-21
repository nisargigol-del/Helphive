<?php
session_start();
include "config.php";

if(isset($_POST['username']) && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = md5($_POST['password']);  // encrypt password

    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<script>
                alert('Incorrect Username or Password!');
                window.location.href = 'admin.html';
              </script>";
    }

} else {
    header("Location: admin.html");
    exit();
}
?>
