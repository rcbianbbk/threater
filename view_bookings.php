<?php
<<<<<<< HEAD
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
=======
$page_title = "Customer Bookings Report";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all bookings with user and movie details
$sql = "SELECT b.*, u.name AS user_name, u.email AS user_email, m.title AS movie_title, m.price AS movie_price
        FROM bookings b
        JOIN users u ON b.user_id = u.id 
        JOIN movies m ON b.movie_id = m.id 
        ORDER BY b.id DESC";

$result = $conn->query($sql);

include 'includes/header.php';
?>

<main class="container" style="margin-top: 25px; margin-bottom: 70px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 style="font-size: 28px; margin-bottom: 4px;">Customer Booking Records</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Detailed transaction log of all reservations and payments.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fa-solid fa-gauge-high"></i> Admin Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-accent">
                <i class="fa-solid fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <div class="glass-card" style="padding: 24px;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Ticket Code</th>
                        <th>Customer</th>
                        <th>Movie Title</th>
                        <th>Seats</th>
                        <th>Showtime</th>
                        <th>Total Paid</th>
                        <th>Txn Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $ticket_code = $row['ticket_code'] ?: ('CW-' . str_pad($row['id'], 6, '0', STR_PAD_LEFT));
                        ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td>
                                    <a href="ticket.php?id=<?= $row['id'] ?>" target="_blank" style="color: #818cf8; font-weight: 700; font-family: 'Outfit';">
                                        <?= htmlspecialchars($ticket_code) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['user_name']) ?></strong>
                                    <small style="display: block; color: var(--text-dim);"><?= htmlspecialchars($row['user_email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['movie_title']) ?></td>
                                <td><strong style="color: var(--accent);"><?= htmlspecialchars($row['seat_number']) ?></strong></td>
                                <td><?= htmlspecialchars($row['show_time'] ?? '05:30 PM') ?></td>
                                <td><strong>Rs. <?= number_format($row['total_amount'] ?? $row['movie_price'], 2) ?></strong></td>
                                <td>
                                    <span style="font-family: monospace; background: #090d16; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-color); color: #38bdf8; font-size: 12px;">
                                        <?= htmlspecialchars($row['transaction_id'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status" style="background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3);">
                                        <?= htmlspecialchars($row['payment_status'] ?? 'Success') ?>
                                    </span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-dim);"><?= date('M d, Y', strtotime($row['booking_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No customer booking records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987

</main>

<?php include 'includes/footer.php'; ?>