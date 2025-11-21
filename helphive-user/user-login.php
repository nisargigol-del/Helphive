<?php $justRegistered = isset($_GET['registered']); ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
</head>
<body class="user-login">

<div class="login-container">

    <div class="login-left">
        <div class="brand-section">
            <h1 style="color:white">HelpHive</h1>
        </div>
    </div>

    <div class="login-right">
        <div class="login-form-wrapper">
            <h2>User Login</h2>
            <?php if ($justRegistered): ?>
                <div class="alert success">
                    Account created successfully. Please log in with your new credentials.
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>

            </form>

            <p class="switch-link" style="margin-top: 1.5rem; text-align: center;">
                Are you an admin? <a href="../helphive-admin/admin.html">Go to Admin Portal</a>
            </p>

        </div>
    </div>

</div>

</body>
</html>
