<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}

require "config.php";

/* ---------------------------------------
   UPDATE PAYMENT STATUS
---------------------------------------- */
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $newStatus = $_GET['status'];

    if (in_array($newStatus, ['paid','pending','failed'])) {
        $stmt = mysqli_prepare($conn, "UPDATE payments SET status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $newStatus, $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: manage_payments.php");
    exit;
}

/* ---------------------------------------
   DELETE PAYMENT
---------------------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete payment
    mysqli_query($conn, "DELETE FROM payments WHERE id = $id");
    
    header("Location: manage_payments.php");
    exit;
}

/* ---------------------------------------
   FETCH ALL PAYMENTS
---------------------------------------- */
// Check which columns exist before building query
$canJoinUsers = false;
$canJoinBookings = false;
$canJoinServices = false;

// Check if payments table has user_id and users table has name column
$checkPaymentsUserId = mysqli_query($conn, "SHOW COLUMNS FROM payments LIKE 'user_id'");
$checkUsersName = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'name'");
if ($checkPaymentsUserId && mysqli_num_rows($checkPaymentsUserId) > 0 && 
    $checkUsersName && mysqli_num_rows($checkUsersName) > 0) {
    $canJoinUsers = true;
}

// Check if payments table has booking_id and bookings table exists
$checkPaymentsBookingId = mysqli_query($conn, "SHOW COLUMNS FROM payments LIKE 'booking_id'");
$checkBookingsTable = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
$hasBookingId = false;
$hasBookingsTable = false;

if ($checkPaymentsBookingId && mysqli_num_rows($checkPaymentsBookingId) > 0) {
    $hasBookingId = true;
}
if ($checkBookingsTable && mysqli_num_rows($checkBookingsTable) > 0) {
    $hasBookingsTable = true;
}

if ($hasBookingId && $hasBookingsTable) {
    $canJoinBookings = true;
}

// Check if bookings table has date and time columns
$hasBookingDate = false;
$hasBookingTime = false;
if ($canJoinBookings) {
    $checkBookingDate = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'date'");
    if ($checkBookingDate && mysqli_num_rows($checkBookingDate) > 0) {
        $hasBookingDate = true;
    }
    
    $checkBookingTime = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'time'");
    if ($checkBookingTime && mysqli_num_rows($checkBookingTime) > 0) {
        $hasBookingTime = true;
    }
}

// Check if services table has title column
$checkServicesTitle = mysqli_query($conn, "SHOW COLUMNS FROM services LIKE 'title'");
if ($checkServicesTitle && mysqli_num_rows($checkServicesTitle) > 0) {
    $canJoinServices = true;
}

// Build query based on what's available
$selectFields = "p.*";
$joins = "";

if ($canJoinUsers) {
    $selectFields .= ", u.name AS user_name";
    $joins .= " LEFT JOIN users u ON p.user_id = u.id";
}

if ($canJoinBookings) {
    $bookingFields = "";
    if ($hasBookingDate) {
        $bookingFields .= "b.date AS booking_date";
    }
    if ($hasBookingTime) {
        if ($bookingFields) $bookingFields .= ", ";
        $bookingFields .= "b.time AS booking_time";
    }
    
    if ($bookingFields) {
        $selectFields .= ", " . $bookingFields;
    }
    $joins .= " LEFT JOIN bookings b ON p.booking_id = b.id";
    
    // Check if bookings table has service_id before joining services
    if ($canJoinServices) {
        $checkBookingServiceId = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'service_id'");
        if ($checkBookingServiceId && mysqli_num_rows($checkBookingServiceId) > 0) {
            $selectFields .= ", s.title AS service_title";
            $joins .= " LEFT JOIN services s ON b.service_id = s.id";
        }
    }
}

$payments = mysqli_query($conn,
    "SELECT $selectFields
     FROM payments p
     $joins
     ORDER BY p.id DESC"
);

// Get total payments count and calculate total revenue
$totalPayments = mysqli_num_rows($payments);
$totalRevenue = 0;
$revenueQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status='paid'");
if ($revenueQuery) {
    $revenueRow = mysqli_fetch_assoc($revenueQuery);
    $totalRevenue = $revenueRow['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - Helphive Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard">

    <?php include "admin_header.php"; ?>

    <main class="main-content">
        <header class="page-header">
            <div class="header-left">
                <h1>Manage Payments</h1>
                <p>Total Payments: <?php echo number_format($totalPayments); ?> | Total Revenue: ₹<?php echo number_format($totalRevenue, 2); ?></p>
            </div>
        </header>

        <!-- Payments Table -->
        <div class="bookings-table-container">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Service</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Booking Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($payments && mysqli_num_rows($payments) > 0) {
                        while($p = mysqli_fetch_assoc($payments)) {
                            $userInitial = strtoupper(substr($p['user_name'] ?? 'U', 0, 1));
                            $statusClass = strtolower($p['status'] ?? 'pending');
                            $paymentMethod = isset($p['method']) ? strtoupper($p['method']) : 'N/A';
                            
                            // Format booking date and time
                            $bookingDate = isset($p['booking_date']) && $p['booking_date'] ? $p['booking_date'] : null;
                            $bookingTime = isset($p['booking_time']) && $p['booking_time'] ? $p['booking_time'] : null;
                            
                            if ($bookingDate && $bookingTime) {
                                $bookingDateTime = $bookingDate . ' ' . $bookingTime;
                            } elseif ($bookingDate) {
                                $bookingDateTime = $bookingDate;
                            } elseif ($bookingTime) {
                                $bookingDateTime = $bookingTime;
                            } else {
                                $bookingDateTime = 'N/A';
                            }
                    ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($p['user_name'] ?? 'Unknown'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($p['service_title'] ?? 'N/A'); ?></td>
                        <td class="amount">₹<?php echo number_format($p['amount'] ?? 0, 2); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <?php if (strpos(strtolower($paymentMethod), 'card') !== false) { ?>
                                    <i class="fas fa-credit-card" style="color: var(--primary-teal);"></i>
                                <?php } elseif (strpos(strtolower($paymentMethod), 'cash') !== false) { ?>
                                    <i class="fas fa-money-bill-wave" style="color: var(--success);"></i>
                                <?php } elseif (strpos(strtolower($paymentMethod), 'upi') !== false || strpos(strtolower($paymentMethod), 'wallet') !== false) { ?>
                                    <i class="fas fa-wallet" style="color: var(--info);"></i>
                                <?php } else { ?>
                                    <i class="fas fa-money-check-alt" style="color: var(--gray-600);"></i>
                                <?php } ?>
                                <span><?php echo htmlspecialchars($paymentMethod); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php 
                                if ($statusClass == 'paid') echo 'completed';
                                elseif ($statusClass == 'failed') echo 'cancelled';
                                else echo 'pending';
                            ?>">
                                <?php echo ucfirst($statusClass); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($bookingDateTime); ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($statusClass != 'paid') { ?>
                                    <a href="manage_payments.php?status=paid&id=<?php echo $p['id']; ?>" 
                                       class="btn-icon" 
                                       onclick="return confirm('Mark this payment as paid?');"
                                       title="Mark as Paid"
                                       style="color: var(--success);">
                                        <i class="fas fa-check-circle"></i>
                                    </a>
                                <?php } ?>
                                
                                <?php if ($statusClass != 'pending') { ?>
                                    <a href="manage_payments.php?status=pending&id=<?php echo $p['id']; ?>" 
                                       class="btn-icon" 
                                       onclick="return confirm('Mark this payment as pending?');"
                                       title="Mark as Pending"
                                       style="color: var(--warning);">
                                        <i class="fas fa-clock"></i>
                                    </a>
                                <?php } ?>
                                
                                <?php if ($statusClass != 'failed') { ?>
                                    <a href="manage_payments.php?status=failed&id=<?php echo $p['id']; ?>" 
                                       class="btn-icon" 
                                       onclick="return confirm('Mark this payment as failed?');"
                                       title="Mark as Failed"
                                       style="color: var(--error);">
                                        <i class="fas fa-times-circle"></i>
                                    </a>
                                <?php } ?>
                                
                                <a href="manage_payments.php?delete=<?php echo $p['id']; ?>" 
                                   class="btn-icon" 
                                   onclick="return confirm('Are you sure you want to delete this payment record? This action cannot be undone.');"
                                   title="Delete Payment">
                                    <i class="fas fa-trash" style="color: var(--error);"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--gray-600);">No payments found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
