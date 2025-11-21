<?php
session_start();
include "db.php";

// Ensure maids table exists
$maidsTable = mysqli_query($conn, "SHOW TABLES LIKE 'maids'");
if ($maidsTable && mysqli_num_rows($maidsTable) === 0) {
    $createMaids = "CREATE TABLE maids (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image VARCHAR(255) DEFAULT 'https://i.pravatar.cc/150',
        experience VARCHAR(100) NOT NULL,
        skills TEXT NOT NULL,
        rating DECIMAL(3,2) DEFAULT 4.5,
        is_available TINYINT(1) DEFAULT 1
    )";
    mysqli_query($conn, $createMaids);
}

// Check if maids exist, if not insert sample maids
$maidCount = mysqli_query($conn, "SELECT COUNT(*) as count FROM maids");
$count = mysqli_fetch_assoc($maidCount)['count'];

if ($count == 0) {
    // Insert sample maids with detailed profiles
    $sampleMaids = [
        [
            'name' => 'Priya Sharma',
        
            'experience' => '5 years experience',
            'skills' => 'Cleaning, Cooking, Laundry',
            'rating' => 4.8
        ],
        [
            'name' => 'Sunita Devi',
        
            'experience' => '7 years experience',
            'skills' => 'Cleaning, Baby Sitting, Elderly Care',
            'rating' => 4.9
        ],
        [
            'name' => 'Meera Patel',
        
            'experience' => '4 years experience',
            'skills' => 'Cooking, Elderly Care, Meal Planning',
            'rating' => 4.7
        ],
        [
            'name' => 'Kavita Singh',
            
            'experience' => '6 years experience',
            'skills' => 'Cleaning, Cooking, Baby Sitting',
            'rating' => 4.8
        ],
        [
            'name' => 'Anjali Verma',
        
            'experience' => '8 years experience',
            'skills' => 'Deep Cleaning, Cooking, Laundry',
            'rating' => 4.9
        ],
        [
            'name' => 'Rekha Kumari',
            
            'experience' => '5 years experience',
            'skills' => 'Baby Sitting, Elderly Care, Cooking',
            'rating' => 4.6
        ],
        [
            'name' => 'Sushila Devi',
        
            'experience' => '9 years experience',
            'skills' => 'Full House Management, Cooking, Cleaning',
            'rating' => 5.0
        ],
        [
            'name' => 'Geeta Yadav',
            
            'experience' => '6 years experience',
            'skills' => 'Cooking, Cleaning, Pet Care',
            'rating' => 4.7
        ]
    ];
    
    foreach ($sampleMaids as $maid) {
        $name = mysqli_real_escape_string($conn, $maid['name']);
        
        $experience = mysqli_real_escape_string($conn, $maid['experience']);
        $skills = mysqli_real_escape_string($conn, $maid['skills']);
        $rating = (float)$maid['rating'];
        
        $insertSql = "INSERT INTO maids (name, image, experience, skills, rating, is_available) 
                      VALUES ('$name', '$image', '$experience', '$skills', $rating, 1)";
        mysqli_query($conn, $insertSql);
    }
}

// Handle maid selection
if (isset($_GET['select_maid'])) {
    $maidId = (int)$_GET['select_maid'];
    $bookingId = isset($_SESSION['current_booking_id']) ? (int)$_SESSION['current_booking_id'] : null;
    
    if ($bookingId && $maidId > 0) {
        $maidIdEsc = mysqli_real_escape_string($conn, $maidId);
        $bookingIdEsc = mysqli_real_escape_string($conn, $bookingId);
        
        $updateSql = "UPDATE bookings SET maid_id = $maidIdEsc WHERE id = $bookingIdEsc";
        if (mysqli_query($conn, $updateSql)) {
            $_SESSION['selected_maid_id'] = $maidId;
            header("Location: payment.php");
            exit();
        } else {
            $error = "Failed to select maid. Please try again.";
        }
    } else {
        $error = "Invalid booking or maid selection.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM maids WHERE is_available = 1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Maid - Helphive</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="booking-page">

    <!---------------- NAVBAR ---------------->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php"><h2>Helphive</h2></a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="booking.php">Book Now</a></li>
                <li><a href="maids.php" class="active">Our Maids</a></li>
                <li><a href="review_feedback_complaint.php">Feedback</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="nav-buttons" style="display: flex; gap: 1rem; align-items: center;">
                <a href="../helphive-admin/admin.html" class="btn-outline">Admin</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn-outline">Logout</a>
                <?php else: ?>
                    <a href="user-login.php" class="btn-outline">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!---------------- HERO SECTION --------------->
    <section class="hero">
        <div class="container">
            <h1>Select Your Maid</h1>
            <p>Choose from our verified and experienced maids</p>
        </div>
    </section>

    <!---------------- MAIN CONTENT --------------->
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert error">
                <p><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['current_booking_id'])): ?>
            <div class="alert" style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
                <p><strong>Note:</strong> To select a maid, please <a href="booking.php" style="color: #b5bcc5ff; font-weight: 600;">complete your booking first</a>. You can browse all available maids below.</p>
            </div>
        <?php endif; ?>

        <div class="maids-grid">
            <?php 
            if (mysqli_num_rows($result) > 0):
                while($m = mysqli_fetch_assoc($result)): 
                    $skillsArray = explode(',', $m['skills']);
            ?>
                <div class="maid-profile-card">
                    <div class="maid-profile-header">
                        <div class="maid-avatar-wrapper">
                             
                                 alt="<?php echo htmlspecialchars($m['name']); ?>" 
                                 class="maid-avatar">
                            <span class="verified-badge">✓ Verified</span>
                        </div>
                        <div class="maid-rating">
                            <span class="rating-stars">
                                <?php 
                                $rating = (float)$m['rating'];
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                for ($i = 0; $i < $fullStars; $i++) {
                                    echo '<span class="star filled">★</span>';
                                }
                                if ($hasHalfStar) {
                                    echo '<span class="star half">★</span>';
                                }
                                for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++) {
                                    echo '<span class="star">☆</span>';
                                }
                                ?>
                            </span>
                            <span class="rating-value"><?php echo number_format($rating, 1); ?>/5.0</span>
                        </div>
                    </div>
                    
                    <div class="maid-profile-body">
                        <h3 class="maid-name"><?php echo htmlspecialchars($m['name']); ?></h3>
                        
                        <div class="maid-experience">
                            <span class="experience-icon">💼</span>
                            <span class="experience-text"><?php echo htmlspecialchars($m['experience']); ?></span>
                        </div>
                        
                        <div class="maid-skills">
                            <h4 class="skills-title">Specializations</h4>
                            <div class="skills-tags">
                                <?php foreach ($skillsArray as $skill): 
                                    $skill = trim($skill);
                                    if (!empty($skill)):
                                ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                        
                        <div class="maid-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo number_format($rating, 1); ?></span>
                                <span class="stat-label">Rating</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo rand(50, 200); ?>+</span>
                                <span class="stat-label">Jobs Done</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo rand(90, 100); ?>%</span>
                                <span class="stat-label">Satisfaction</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="maid-profile-footer">
                        <?php if (isset($_SESSION['current_booking_id'])): ?>
                            <a href="maids.php?select_maid=<?php echo $m['id']; ?>" class="btn-select-maid">
                                <span>Select This Maid</span>
                                <span class="btn-arrow">→</span>
                            </a>
                        <?php else: ?>
                            <a href="booking.php" class="btn-select-maid" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                                <span>Book to Select</span>
                                <span class="btn-arrow">→</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-maids-message">
                    <div class="no-maids-icon">👥</div>
                    <h3>No Maids Available</h3>
                    <p>We're currently updating our maid profiles. Please check back later.</p>
                    <a href="booking.php" class="btn-primary">Go Back to Booking</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!---------------- FOOTER --------------->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Helphive. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
