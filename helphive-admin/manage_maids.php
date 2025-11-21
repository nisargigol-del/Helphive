<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}

require "config.php";

/* ---------------------------------------
   ADD MAID
---------------------------------------- */
if (isset($_POST['add_maid'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $experience = mysqli_real_escape_string($conn, trim($_POST['experience']));
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    $rating = floatval($_POST['rating']);
    
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    $stmt = mysqli_prepare($conn, "INSERT INTO maids (name, experience, skills, rating, image, is_available) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssdsi", $name, $experience, $skills, $rating, $image, $is_available);
    mysqli_stmt_execute($stmt);
    
    header("Location: manage_maids.php");
    exit;
}

/* ---------------------------------------
   DELETE MAID
---------------------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Delete maid
    mysqli_query($conn, "DELETE FROM maids WHERE id = $id");
    
    header("Location: manage_maids.php");
    exit;
}

/* ---------------------------------------
   FETCH ALL MAIDS
---------------------------------------- */
$maids = mysqli_query($conn, "SELECT * FROM maids ORDER BY id DESC");

// Get total maids count
$totalMaids = mysqli_num_rows($maids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Maids - Helphive Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard">

    <?php include "admin_header.php"; ?>

    <main class="main-content">
        <header class="page-header">
            <div class="header-left">
                <h1>Manage Maids</h1>
                <p>Total Maids: <?php echo number_format($totalMaids); ?></p>
            </div>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i>
                Add New Maid
            </button>
        </header>

        <!-- Maids Table -->
        <div class="bookings-table-container">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Maid</th>
                        <th>Experience</th>
                        <th>Skills</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($maids && mysqli_num_rows($maids) > 0) {
                        while($m = mysqli_fetch_assoc($maids)) {
                            $maidInitial = strtoupper(substr($m['name'] ?? 'M', 0, 1));
                            $isAvailable = isset($m['is_available']) ? $m['is_available'] : 1;
                            $rating = isset($m['rating']) ? number_format($m['rating'], 1) : 'N/A';
                            $experience = isset($m['experience']) ? htmlspecialchars($m['experience']) : 'N/A';
                            $skills = isset($m['skills']) ? htmlspecialchars($m['skills']) : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo $m['id']; ?></td>
                        <td>
                            <div class="user-cell">
                               
                                <div>
                                    <div class="user-name"><?php echo htmlspecialchars($m['name'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $experience; ?></td>
                        <td><?php echo $skills; ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-star" style="color: #FFB8A0;"></i>
                                <span><?php echo $rating; ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $isAvailable ? 'completed' : 'cancelled'; ?>">
                                <?php echo $isAvailable ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="manage_maids.php?delete=<?php echo $m['id']; ?>" 
                                   class="btn-icon" 
                                   onclick="return confirm('Are you sure you want to delete this maid? This action cannot be undone.');"
                                   title="Delete Maid">
                                    <i class="fas fa-trash" style="color: var(--error);"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray-600);">No maids found. Add your first maid!</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- Add Maid Modal -->
    <div id="maidModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Maid</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="manage_maids.php" id="maidForm">
                <input type="hidden" name="add_maid" value="1">
                
                <div class="form-group">
                    <label for="name">Maid Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Priya Sharma">
                </div>
                
                <div class="form-group">
                    <label for="experience">Experience *</label>
                    <input type="text" id="experience" name="experience" required placeholder="e.g., 3 Years">
                </div>
                
                <div class="form-group">
                    <label for="skills">Skills *</label>
                    <input type="text" id="skills" name="skills" required placeholder="e.g., Cleaning, Cooking, Laundry">
                </div>
                
                <div class="form-group">
                    <label for="rating">Rating *</label>
                    <input type="number" id="rating" name="rating" step="0.1" min="1" max="5" value="4.5" required>
                    <small style="color: var(--gray-600); font-size: 0.875rem;">Rating from 1 to 5</small>
                </div>
                
                <div class="form-group">
                    <label for="is_available" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="is_available" name="is_available" value="1" checked>
                        <span>Available for booking</span>
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Maid</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('maidForm').reset();
            document.getElementById('is_available').checked = true;
            document.getElementById('maidModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('maidModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('maidModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>
