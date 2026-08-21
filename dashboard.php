<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}

$msg = "";
$err = "";

// Movie Add गर्ने Logic
if (isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);

    if (!empty($title) && !empty($genre) && $duration > 0 && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid", $title, $genre, $duration, $price);
        if ($stmt->execute()) {
            $msg = "✅ Movie सफलतापूर्वक थपियो!";
        } else {
            $err = "Movie थप्न सकिएन!";
        }
    } else {
        $err = "सबै विवरणहरू सही तरिकाले भर्नुहोस्!";
    }
}

// Movie Delete गर्ने Logic
if (isset($_GET['delete_movie'])) {
    $m_id = intval($_GET['delete_movie']);
    $conn->query("DELETE FROM movies WHERE id = $m_id");
    header("Location: admin_dashboard.php");
    exit();
}

// All Movies Fetch
$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");

// All Bookings Fetch
$bookings = $conn->query("
    SELECT b.id, u.name as user_name, u.email, m.title as movie_title, b.seat_number, b.payment_method, b.payment_status, b.transaction_id, b.booking_date 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0b0f19; color: #f8fafc; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        h2 { color: #38bdf8; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        
        .grid-container { display: grid; grid-template-columns: 320px 1fr; gap: 25px; }
        
        /* Add Movie Card */
        .card { background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; height: fit-content; }
        .card h3 { color: #38bdf8; margin-bottom: 15px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 4px; }
        input { width: 100%; padding: 10px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: white; outline: none; }
        input:focus { border-color: #38bdf8; }
        .btn-add { width: 100%; padding: 10px; background: #38bdf8; color: #0f172a; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 5px; }
        .btn-add:hover { background: #0284c7; color: white; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 10px; overflow: hidden; border: 1px solid #334155; margin-bottom: 25px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
        th { background: #0f172a; color: #38bdf8; }
        tr:hover { background: rgba(56, 189, 248, 0.05); }
        .txn-code { background: #0f172a; padding: 3px 6px; border-radius: 4px; border: 1px solid #38bdf8; color: #38bdf8; font-family: monospace; }
        .btn-del { color: #ef4444; text-decoration: none; font-weight: bold; }
        
        .msg { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; padding: 8px; border-radius: 6px; margin-bottom: 12px; font-size: 13px; text-align: center; }
        .err { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 8px; border-radius: 6px; margin-bottom: 12px; font-size: 13px; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>📊 Admin Dashboard</h2>
        <div>
            <a href="index.php" style="color: #94a3b8; text-decoration: none; margin-right: 15px;">Home Page</a>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="grid-container">
        <!-- Add Movie Form -->
        <div class="card">
            <h3>🎬 Add New Movie</h3>
            <?php if($msg != "") echo "<div class='msg'>$msg</div>"; ?>
            <?php if($err != "") echo "<div class='err'>$err</div>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Movie Title</label>
                    <input type="text" name="title" placeholder="Ex: Avatar 2" required>
                </div>
                <div class="form-group">
                    <label>Genre</label>
                    <input type="text" name="genre" placeholder="Ex: Action / Sci-Fi" required>
                </div>
                <div class="form-group">
                    <label>Duration (Minutes)</label>
                    <input type="number" name="duration" placeholder="180" required>
                </div>
                <div class="form-group">
                    <label>Ticket Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" placeholder="350" required>
                </div>
                <button type="submit" name="add_movie" class="btn-add">+ Add Movie</button>
            </form>
        </div>

        <!-- Tables Display -->
        <div>
            <!-- Movie List Table -->
            <h3 style="color:#38bdf8; margin-bottom: 10px;">🎥 Active Movies List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($movies && $movies->num_rows > 0): ?>
                        <?php while($m = $movies->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $m['id'] ?></td>
                                <td><strong><?= htmlspecialchars($m['title']) ?></strong></td>
                                <td><?= htmlspecialchars($m['genre']) ?></td>
                                <td><?= $m['duration'] ?> Mins</td>
                                <td>Rs. <?= number_format($m['price'], 2) ?></td>
                                <td><a href="admin_dashboard.php?delete_movie=<?= $m['id'] ?>" class="btn-del" onclick="return confirm('के तपाईं यो फिल्म हटाउन चाहनुहुन्छ?');">Delete</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:#64748b;">कुनै Movie थपिएको छैन।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Bookings List Table -->
            <h3 style="color:#38bdf8; margin-bottom: 10px; margin-top:20px;">🎟 User Booking & Payment Records</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Movie</th>
                        <th>Seat</th>
                        <th>Transaction Code</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($bookings && $bookings->num_rows > 0): ?>
                        <?php while($b = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['user_name']) ?></td>
                                <td><?= htmlspecialchars($b['movie_title']) ?></td>
                                <td><strong style="color:#22c55e;"><?= htmlspecialchars($b['seat_number']) ?></strong></td>
                                <td><span class="txn-code"><?= htmlspecialchars($b['transaction_id'] ?? 'N/A') ?></span></td>
                                <td><?= $b['booking_date'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:#64748b;">कुनै Booking रेकर्ड छैन।</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>