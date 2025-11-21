<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.html");
    exit;
}

require "config.php";

/* -----------------------------------
   UPDATE COMPLAINT STATUS
------------------------------------ */
if (isset($_GET['status']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $newStatus = $_GET['status'];

    if ($newStatus == "resolved") {
        $stmt = mysqli_prepare($conn, "UPDATE complaints SET status='resolved' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: manage_complaints.php");
    exit;
}

/* -----------------------------------
   DELETE COMPLAINT
------------------------------------ */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM complaints WHERE id=$id");
    header("Location: manage_complaints.php");
    exit;
}

/* -----------------------------------
   FETCH ALL COMPLAINTS
------------------------------------ */
$rows = mysqli_query($conn,
  "SELECT c.*, u.name AS user_name
   FROM complaints c
   LEFT JOIN users u ON c.user_id = u.id
   ORDER BY c.id DESC"
);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Complaints</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="dashboard">

<?php include "admin_header.php"; ?>

<main class="main-content">

    <h2>Manage Complaints</h2>

    <div class="table-box">

        <table class="styled-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while($c = mysqli_fetch_assoc($rows)) { ?>

                <tr>
                    <td><?php echo $c['id']; ?></td>

                    <td><?php echo htmlspecialchars($c['user_name']); ?></td>

                    <td><?php echo htmlspecialchars($c['subject']); ?></td>

                    <td><?php echo nl2br(htmlspecialchars($c['message'])); ?></td>

                    <td>
                        <span class="status-badge <?php echo $c['status']; ?>">
                            <?php echo ucfirst($c['status']); ?>
                        </span>
                    </td>

                    <td><?php echo $c['created_at']; ?></td>
                        <td>

    <!-- If not resolved yet -->
    <?php if ($c['status'] == "pending") { ?>

        <a class="status-btn" 
           href="reply_complaint.php?id=<?php echo $c['id']; ?>">
           Reply
        </a>

        <a class="delete-btn"
           href="manage_complaints.php?delete=<?php echo $c['id']; ?>"
           onclick="return confirm('Delete complaint?');">
           Delete
        </a>

    <?php } else { ?>

        <span class="status-badge resolved">Resolved</span>

        <a class="delete-btn"
           href="manage_complaints.php?delete=<?php echo $c['id']; ?>"
           onclick="return confirm('Delete complaint?');">
           Delete
        </a>

    <?php } ?>

</td>

                    <td>
                        <?php if ($c['status'] == "pending") { ?>
                        <a class="status-btn" 
                           href="manage_complaints.php?status=resolved&id=<?php echo $c['id']; ?>">
                           Mark Resolved
                        </a>
                        <?php } ?>

                        <a class="delete-btn"
                           href="manage_complaints.php?delete=<?php echo $c['id']; ?>"
                           onclick="return confirm('Delete complaint?');">
                           Delete
                        </a>
                    </td>

                </tr>

                <?php } ?>
            </tbody>

        </table>

    </div>

</main>

</body>
</html>
