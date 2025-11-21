<?php include "db.php"; ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>

<body class="user-login">

    <div class="login-container">

        <!-- Left side -->
        <div class="login-left">
            <div class="brand-section">
                <h1 style="color:white">HelpHive</h1>
                <p class="tagline">Your trusted maid service platform</p>
            </div>
        </div>

        <!-- Right side -->
        <div class="login-right">
            <div class="login-form-wrapper">
                <h2>Create Account</h2>
                
                <form action="register.php" method="POST" class="login-form">
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm-password" required>
                    </div>

                    <button type="submit" class="btn-primary">Create Account</button>

                    <p class="switch-link">
                        Already have an account? <a href="user-login.php">Login</a>
                    </p>
                    <p class="switch-link admin-link">
                        Are you an admin? <a href="../helphive-admin/admin.html">Go to Admin Portal</a>
                    </p>
                </form>

            </div>
        </div>

    </div>

</body>
</html>
