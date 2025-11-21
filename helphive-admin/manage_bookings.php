<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}
require "config.php";

/* ---------------------------------------
   UPDATE BOOKING STATUS (ADMIN ACTION)
---------------------------------------- */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['action'];

    if (in_array($status, ['pending','confirmed','completed','cancelled'])) {
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: manage_bookings.php");
    exit;
}

/* ---------------------------------------
   DELETE BOOKING
---------------------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete booking
    mysqli_query($conn, "DELETE FROM bookings WHERE id = $id");
    
    header("Location: manage_bookings.php");
    exit;
}

/* ---------------------------------------
   GET ALL BOOKINGS
---------------------------------------- */
// Check which columns exist before building query
$canJoinUsers = false;
$canJoinServices = false;
$canJoinMaids = false;

// Check if bookings table has user_id and users table has name column
$checkBookingsUserId = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'user_id'");
$checkUsersName = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'name'");
if ($checkBookingsUserId && mysqli_num_rows($checkBookingsUserId) > 0 && 
    $checkUsersName && mysqli_num_rows($checkUsersName) > 0) {
    $canJoinUsers = true;
}

// Check if bookings table has service_id and services table has title column
$checkBookingsServiceId = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'service_id'");
$checkServicesTitle = mysqli_query($conn, "SHOW COLUMNS FROM services LIKE 'title'");
if ($checkBookingsServiceId && mysqli_num_rows($checkBookingsServiceId) > 0 && 
    $checkServicesTitle && mysqli_num_rows($checkServicesTitle) > 0) {
    $canJoinServices = true;
}

// Check if bookings table has maid_id and maids table has name column
$checkBookingsMaidId = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'maid_id'");
$checkMaidsName = mysqli_query($conn, "SHOW COLUMNS FROM maids LIKE 'name'");
if ($checkBookingsMaidId && mysqli_num_rows($checkBookingsMaidId) > 0 && 
    $checkMaidsName && mysqli_num_rows($checkMaidsName) > 0) {
    $canJoinMaids = true;
}

// Build query based on what's available
$selectFields = "b.*";
$joins = "";

if ($canJoinUsers) {
    $selectFields .= ", u.name AS user_name";
    $joins .= " LEFT JOIN users u ON b.user_id = u.id";
}

if ($canJoinServices) {
    $selectFields .= ", s.title AS service_title";
    $joins .= " LEFT JOIN services s ON b.service_id = s.id";
}

if ($canJoinMaids) {
    $selectFields .= ", m.name AS maid_name";
    $joins .= " LEFT JOIN maids m ON b.maid_id = m.id";
}

$bookings = mysqli_query($conn,
    "SELECT $selectFields
     FROM bookings b
     $joins
     ORDER BY b.id DESC"
);

// Get total bookings count
$totalBookings = mysqli_num_rows($bookings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Helphive Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard">

    <?php include "admin_header.php"; ?>

    <main class="main-content">
        <header class="page-header">
            <div class="header-left">
                <h1>Manage Bookings</h1>
                <p>Total Bookings: <?php echo number_format($totalBookings); ?></p>
            </div>
        </header>

        <!-- Bookings Table -->
        <div class="bookings-table-container">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Maid</th>
                        <th>City</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($bookings && mysqli_num_rows($bookings) > 0) {
                        while($b = mysqli_fetch_assoc($bookings)) {
                            $userInitial = strtoupper(substr($b['user_name'] ?? 'U', 0, 1));
                            $statusClass = $b['status'] ?? 'pending';
                            if ($statusClass == 'confirmed') $statusClass = 'in-progress';
                    ?>
                    <tr>
                        <td><?php echo $b['id']; ?></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($b['user_name'] ?? 'Unknown'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($b['service_title'] ?? 'N/A'); ?></td>
                        <td><?php echo $b['maid_name'] ? htmlspecialchars($b['maid_name']) : '<span style="color: var(--gray-500);">Not Assigned</span>'; ?></td>
                        <td><?php echo htmlspecialchars($b['city'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($b['date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($b['time'] ?? 'N/A'); ?></td>
                        <td class="amount">₹<?php echo number_format($b['amount'] ?? 0, 2); ?></td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($b['status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php 
                                $currentStatus = strtolower($b['status'] ?? 'pending');
                                
                                // Confirm button - show for pending bookings
                                if ($currentStatus == 'pending') { ?>
                                    <a href="manage_bookings.php?action=confirmed&id=<?php echo $b['id']; ?>" 
                                       class="btn-icon" 
                                       onclick="return confirm('Confirm this booking?');"
                                       title="Confirm Booking"
                                       style="color: var(--success);">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php } ?>
                                
                                <!-- Cancel button - show for all bookings except already cancelled -->
                                <?php if ($currentStatus != 'cancelled') { ?>
                                    <a href="manage_bookings.php?action=cancelled&id=<?php echo $b['id']; ?>" 
                                       class="btn-icon" 
                                       onclick="return confirm('Cancel this booking?');"
                                       title="Cancel Booking"
                                       style="color: var(--warning);">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                <?php } ?>
                                
                                <!-- Delete button - always available -->
                                <a href="manage_bookings.php?delete=<?php echo $b['id']; ?>" 
                                   class="btn-icon" 
                                   onclick="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');"
                                   title="Delete Booking">
                                    <i class="fas fa-trash" style="color: var(--error);"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="10" style="text-align: center; padding: 2rem; color: var(--gray-600);">No bookings found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
