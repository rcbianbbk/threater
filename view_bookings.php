<?php
session_start();
require_once 'db.php';

// Database bata sabai bookings fetch garne
$query = "SELECT * FROM bookings ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Bookings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        th { background-color: #007bff; color: white; }
        .receipt-img { width: 120px; height: auto; border-radius: 4px; transition: transform 0.2s; }
        .receipt-img:hover { transform: scale(1.5); }
        .btn { padding: 6px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; }
        .btn-approve { background-color: #28a745; }
        .btn-reject { background-color: #dc3545; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-rejected { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Admin - Movie Booking & Payment Verification</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Movie Name</th>
            <th>Seats</th>
            <th>Amount</th>
            <th>Payment Receipt</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['movie_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['seats'] ?? 'N/A') ?></td>
                <td>Rs. <?= htmlspecialchars($row['amount'] ?? '0') ?></td>
                <td>
                    <?php if (!empty($row['screenshot_path']) && file_exists($row['screenshot_path'])): ?>
                        <a href="<?= $row['screenshot_path'] ?>" target="_blank">
                            <img src="<?= $row['screenshot_path'] ?>" class="receipt-img" alt="Payment Screenshot">
                        </a>
                    <?php else: ?>
                        <span>No image</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-<?= strtolower($row['status'] ?? 'pending') ?>">
                        <?= htmlspecialchars($row['status'] ?? 'Pending') ?>
                    </span>
                </td>
                <td>
                    <?php if (($row['status'] ?? 'Pending') === 'Pending'): ?>
                        <a href="verify.php?id=<?= $row['id'] ?>&action=approve" class="btn btn-approve">Approve</a>
                        <a href="verify.php?id=<?= $row['id'] ?>&action=reject" class="btn btn-reject">Reject</a>
                    <?php else: ?>
                        <em>Processed</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No booking records found.</td>
            </tr>
        <?php endif; ?>
    </table>

</body>
</html>