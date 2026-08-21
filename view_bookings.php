<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch all bookings with user and movie details
$sql = "SELECT bookings.id, users.name AS user_name, users.email AS user_email, movies.title AS movie_title, movies.price, bookings.seat_number, bookings.booking_date 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id 
        JOIN movies ON bookings.movie_id = movies.id 
        ORDER BY bookings.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings - Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 30px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #334155;
            padding-bottom: 15px;
        }
        h2 {
            color: #38bdf8;
        }
        .btn-back {
            background: #3b82f6;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
        }
        .btn-back:hover {
            background: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #334155;
        }
        th {
            background-color: #0f172a;
            color: #38bdf8;
        }
        tr:hover {
            background-color: #334155;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📋 Customer Bookings Details</h2>
        <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Movie Title</th>
                <th>Seat Number</th>
                <th>Price</th>
                <th>Booking Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                        <td><strong style="color: #facc15;"><?php echo htmlspecialchars($row['seat_number']); ?></strong></td>
                        <td>Rs. <?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo $row['booking_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-data">कुनै पनि सिट बुक भएको छैन (No Bookings Found).</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>