<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: admin.html"); exit; }
require "config.php";

/* -------------------------------------
   UPDATE REVIEW STATUS
-------------------------------------- */

if (isset($_GET['action']) && isset($_GET['id'])) {
    
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == "approve") {
        mysqli_query($conn, "UPDATE reviews SET status='approved' WHERE id=$id");
    } 
    else if ($action == "reject") {
        mysqli_query($conn, "UPDATE reviews SET status='rejected' WHERE id=$id");
    }

    header("Location: manage_reviews.php");
    exit;
}

/* -------------------------------------
   DELETE REVIEW
-------------------------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id=$id");
    header("Location: manage_reviews.php");
    exit;
}

/* -------------------------------------
   GET ALL REVIEWS
-------------------------------------- */
$reviews = mysqli_query($conn,
    "SELECT r.*, u.name AS user_name, m.name AS maid_name
     FROM reviews r
     LEFT JOIN users u ON r.user_id = u.id
     LEFT JOIN maids m ON r.maid_id = m.id
     ORDER BY r.id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Reviews</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="dashboard">

<?php include "admin_header.php"; ?>

<main class="main-content">

    <h2>Manage Reviews & Ratings</h2>

    <div class="table-box">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Maid</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($r = mysqli_fetch_assoc($reviews)) { ?>
                <tr>
                    <td><?php echo $r['id']; ?></td>
                    <td><?php echo $r['user_name']; ?></td>
                    <td><?php echo $r['maid_name']; ?></td>
                    <td>⭐<?php echo $r['rating']; ?></td>
                    <td><?php echo $r['review']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $r['status']; ?>">
                            <?php echo ucfirst($r['status']); ?>
                        </span>
                    </td>
                    <td><?php echo $r['created_at']; ?></td>

                    <td>
                        <?php if ($r['status'] == "pending") { ?>
                            <a href="manage_reviews.php?action=approve&id=<?php echo $r['id']; ?>" class="status-btn">Approve</a>
                            <a href="manage_reviews.php?action=reject&id=<?php echo $r['id']; ?>" class="status-btn cancel">Reject</a>
                        <?php } ?>

                        <a href="manage_reviews.php?delete=<?php echo $r['id']; ?>"
                           onclick="return confirm('Delete this review?')"
                           class="delete-btn">Delete</a>
                    </td>

                </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>

</main>

</body>
</html>
