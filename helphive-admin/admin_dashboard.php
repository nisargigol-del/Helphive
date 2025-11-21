<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}
require "config.php";

// Get admin name for display
$adminName = isset($_SESSION['admin']) ? $_SESSION['admin'] : 'Admin';

// Dashboard stats - Total counts (with error handling)
$users = 0;
$usersQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
if ($usersQuery) {
    $usersRow = mysqli_fetch_assoc($usersQuery);
    $users = $usersRow['count'] ?? 0;
}

$maids = 0;
// Check if status column exists first
$checkStatusColumn = mysqli_query($conn, "SHOW COLUMNS FROM maids LIKE 'status'");
if ($checkStatusColumn && mysqli_num_rows($checkStatusColumn) > 0) {
    // Status column exists, use it
    $maidsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM maids WHERE status='active'");
    if ($maidsQuery) {
        $maidsRow = mysqli_fetch_assoc($maidsQuery);
        $maids = $maidsRow['count'] ?? 0;
    }
} else {
    // Status column doesn't exist, count all maids
    $maidsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM maids");
    if ($maidsQuery) {
        $maidsRow = mysqli_fetch_assoc($maidsQuery);
        $maids = $maidsRow['count'] ?? 0;
    }
}

$bookings = 0;
$bookingsQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings");
if ($bookingsQuery) {
    $bookingsRow = mysqli_fetch_assoc($bookingsQuery);
    $bookings = $bookingsRow['count'] ?? 0;
}

// Calculate revenue from completed bookings
$revenue = 0;
$revenueResult = mysqli_query($conn, "SELECT SUM(amount) as total FROM bookings WHERE status='completed'");
if ($revenueResult) {
    $revenueRow = mysqli_fetch_assoc($revenueResult);
    $revenue = $revenueRow['total'] ?? 0;
}

// Calculate percentage changes from last month (with safe date handling)
$lastMonth = date('Y-m-d', strtotime('-1 month'));

// Users change - check if created_at exists
$usersChange = 0;
$usersLastMonth = 0;
$checkDateColumn = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'");
if ($checkDateColumn && mysqli_num_rows($checkDateColumn) > 0) {
    $usersLastMonthQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE created_at < '$lastMonth'");
    if ($usersLastMonthQuery) {
        $usersLastMonthRow = mysqli_fetch_assoc($usersLastMonthQuery);
        $usersLastMonth = $usersLastMonthRow['count'] ?? 0;
    }
}
$usersChange = $usersLastMonth > 0 ? round((($users - $usersLastMonth) / $usersLastMonth) * 100) : 0;

// Maids change
$maidsChange = 0;
$maidsLastMonth = 0;
$checkMaidDateColumn = mysqli_query($conn, "SHOW COLUMNS FROM maids LIKE 'created_at'");
if ($checkMaidDateColumn && mysqli_num_rows($checkMaidDateColumn) > 0) {
    $maidsLastMonthQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM maids WHERE created_at < '$lastMonth'");
    if ($maidsLastMonthQuery) {
        $maidsLastMonthRow = mysqli_fetch_assoc($maidsLastMonthQuery);
        $maidsLastMonth = $maidsLastMonthRow['count'] ?? 0;
    }
}
$maidsChange = $maidsLastMonth > 0 ? round((($maids - $maidsLastMonth) / $maidsLastMonth) * 100) : 0;

// Bookings change - try different date columns
$bookingsChange = 0;
$bookingsLastMonth = 0;
$checkBookingDateColumn = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'created_at'");
if ($checkBookingDateColumn && mysqli_num_rows($checkBookingDateColumn) > 0) {
    $bookingsLastMonthQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE created_at < '$lastMonth'");
    if ($bookingsLastMonthQuery) {
        $bookingsLastMonthRow = mysqli_fetch_assoc($bookingsLastMonthQuery);
        $bookingsLastMonth = $bookingsLastMonthRow['count'] ?? 0;
    }
} else {
    // Try with booking date
    $checkBookingDateColumn2 = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'date'");
    if ($checkBookingDateColumn2 && mysqli_num_rows($checkBookingDateColumn2) > 0) {
        $bookingsLastMonthQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE date < '$lastMonth'");
        if ($bookingsLastMonthQuery) {
            $bookingsLastMonthRow = mysqli_fetch_assoc($bookingsLastMonthQuery);
            $bookingsLastMonth = $bookingsLastMonthRow['count'] ?? 0;
        }
    }
}
$bookingsChange = $bookingsLastMonth > 0 ? round((($bookings - $bookingsLastMonth) / $bookingsLastMonth) * 100) : 0;

// Revenue change
$revenueChange = 0;
$revenueLastMonth = 0;
// Check which date column exists
$revenueDateColumn = 'created_at';
$checkRevenueDateColumn = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'created_at'");
if (!$checkRevenueDateColumn || mysqli_num_rows($checkRevenueDateColumn) == 0) {
    // created_at doesn't exist, check for date column
    $checkRevenueDateColumn2 = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'date'");
    if ($checkRevenueDateColumn2 && mysqli_num_rows($checkRevenueDateColumn2) > 0) {
        $revenueDateColumn = 'date';
    } else {
        $revenueDateColumn = null; // No date column found
    }
}

if ($revenueDateColumn) {
    $revenueLastMonthResult = mysqli_query($conn, 
        "SELECT SUM(amount) as total FROM bookings WHERE status='completed' AND $revenueDateColumn < '$lastMonth'");
    if ($revenueLastMonthResult) {
        $revenueLastMonthRow = mysqli_fetch_assoc($revenueLastMonthResult);
        $revenueLastMonth = $revenueLastMonthRow['total'] ?? 0;
    }
}
$revenueChange = $revenueLastMonth > 0 ? round((($revenue - $revenueLastMonth) / $revenueLastMonth) * 100) : 0;

// Get recent bookings (last 4)
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

// Execute query
$recentBookings = mysqli_query($conn,
    "SELECT $selectFields
     FROM bookings b
     $joins
     ORDER BY b.id DESC
     LIMIT 4"
);


// Format revenue for display
function formatRevenue($amount) {
    if ($amount >= 1000000) {
        return '₹' . number_format($amount / 1000000, 1) . 'M';
    } elseif ($amount >= 1000) {
        return '₹' . number_format($amount / 1000, 1) . 'K';
    }
    return '₹' . number_format($amount, 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Helphive</title>
    <meta name="description" content="Helphive Admin Dashboard - Comprehensive platform management">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard">

    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-small">
                <div class="logo-icon">
                    <div class="logo-hexagon"></div>
                    <div class="logo-circle"></div>
                </div>
                <h2>Helphive</h2>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-item active">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="manage_users.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
            
            <a href="manage_maids.php" class="nav-item">
                <i class="fas fa-user-nurse"></i>
                <span>Manage Maids</span>
            </a>
            
            <a href="manage_bookings.php" class="nav-item">
                <i class="fas fa-calendar-check"></i>
                <span>Manage Bookings</span>
            </a>
            
            <a href="manage_payments.php" class="nav-item">
                <i class="fas fa-credit-card"></i>
                <span>Manage Payments</span>
            </a>
            
            <a href="manage_complaints.php" class="nav-item">
                <i class="fas fa-comments"></i>
                <span>Handle Complaints</span>
            </a>
            
            <a href="manage_reviews.php" class="nav-item">
                <i class="fas fa-star"></i>
                <span>Reviews & Ratings</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <main class="main-content">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Dashboard Overview</h1>
                <p>Welcome back, <?php echo htmlspecialchars($adminName); ?></p>
            </div>
            <div class="header-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="profile-menu">
                    <div class="avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
                </div>
            </div>
        </header>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo number_format($users); ?></p>
                    <span class="stat-change <?php echo $usersChange >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $usersChange >= 0 ? '+' : ''; ?><?php echo $usersChange; ?>% from last month
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon maids">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <div class="stat-content">
                    <h3>Active Maids</h3>
                    <p class="stat-number"><?php echo number_format($maids); ?></p>
                    <span class="stat-change <?php echo $maidsChange >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $maidsChange >= 0 ? '+' : ''; ?><?php echo $maidsChange; ?>% from last month
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Bookings</h3>
                    <p class="stat-number"><?php echo number_format($bookings); ?></p>
                    <span class="stat-change <?php echo $bookingsChange >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $bookingsChange >= 0 ? '+' : ''; ?><?php echo $bookingsChange; ?>% from last month
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Revenue</h3>
                    <p class="stat-number"><?php echo formatRevenue($revenue); ?></p>
                    <span class="stat-change <?php echo $revenueChange >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $revenueChange >= 0 ? '+' : ''; ?><?php echo $revenueChange; ?>% from last month
                    </span>
                </div>
            </div>
            
            <div class="recent-card">
                <h3>Recent Bookings</h3>
                <div class="recent-list">
                    <?php 
                    if ($recentBookings && mysqli_num_rows($recentBookings) > 0) {
                        while($booking = mysqli_fetch_assoc($recentBookings)) {
                            $userInitial = strtoupper(substr($booking['user_name'] ?? 'U', 0, 1));
                            $statusClass = $booking['status'] ?? 'pending';
                            $statusText = ucfirst($statusClass);
                            if ($statusClass == 'confirmed') $statusClass = 'in-progress';
                    ?>
                    <div class="recent-item">
                        <div class="recent-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
                        <div class="recent-info">
                            <h4><?php echo htmlspecialchars($booking['user_name'] ?? 'Unknown User'); ?></h4>
                            <p><?php echo htmlspecialchars($booking['service_title'] ?? 'Service'); ?> - ₹<?php echo number_format($booking['amount'] ?? 0, 0); ?></p>
                        </div>
                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </div>
                    <?php 
                        }
                    } else {
                        echo '<p style="text-align: center; color: var(--gray-600); padding: 2rem;">No recent bookings</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>