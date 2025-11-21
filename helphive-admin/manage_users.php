<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}

require "config.php";

/* ---------------------------------------
   DELETE USER
---------------------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete user (and optionally related bookings, reviews, etc.)
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    
    header("Location: manage_users.php");
    exit;
}

/* ---------------------------------------
   FETCH ALL USERS
---------------------------------------- */
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");

// Get total users count
$totalUsers = mysqli_num_rows($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Helphive Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard">

    <?php include "admin_header.php"; ?>

    <main class="main-content">
        <header class="page-header">
            <div class="header-left">
                <h1>Manage Users</h1>
                <p>Total Users: <?php echo number_format($totalUsers); ?></p>
            </div>
        </header>

        <!-- Users Table -->
        <div class="bookings-table-container">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($users && mysqli_num_rows($users) > 0) {
                        while($u = mysqli_fetch_assoc($users)) {
                            $userInitial = strtoupper(substr($u['name'] ?? 'U', 0, 1));
                            $registeredDate = isset($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($u['name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($u['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo $registeredDate; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="manage_users.php?delete=<?php echo $u['id']; ?>" 
                                   class="btn-icon" 
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');"
                                   title="Delete User">
                                    <i class="fas fa-trash" style="color: var(--error);"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: var(--gray-600);">No users found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
