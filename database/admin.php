<?php
include 'db.php';

// Approve user
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE users SET status='approved' WHERE id=$id");
}

// Reject user
if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    mysqli_query($conn, "UPDATE users SET status='rejected' WHERE id=$id");
}

// Fetch pending users
$result = mysqli_query($conn, "SELECT * FROM users WHERE status='pending'");
?>

<h2>Admin Approval Panel</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['username'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['status'] ?></td>
            <td>
                <a href="?approve=<?= $row['id'] ?>">✅ Approve</a> | 
                <a href="?reject=<?= $row['id'] ?>">❌ Reject</a>
            </td>
        </tr>
    <?php } ?>
</table>
