<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Fetch user bookings with movie details
$bookings_query = $conn->prepare("
    SELECT bookings.*, movies.title as movie_title, movies.price as movie_price, movies.poster 
    FROM bookings 
    JOIN movies ON bookings.movie_id = movies.id 
    WHERE bookings.user_id = ? 
    ORDER BY bookings.id DESC
");
$bookings_query->bind_param("i", $user_id);
$bookings_query->execute();
$bookings = $bookings_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema World - User Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.4);
            --accent: #818cf8;
            --bg-dark: #030712;
            --card-bg: rgba(17, 24, 39, 0.75);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-dark); color: #f8fafc; min-height: 100vh; padding: 40px 20px; }

        .dashboard-container { max-width: 1200px; margin: 0 auto; }

        /* Header Section */
        .dash-header {
            background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px; border-radius: 24px; display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 35px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        }
        .user-info h1 { font-size: 26px; font-weight: 900; color: #fff; }
        .user-info h1 span { color: var(--primary); }
        .user-info p { color: #94a3b8; font-size: 14px; font-weight: 600; margin-top: 4px; }
        
        .nav-btns { display: flex; gap: 12px; }
        .btn-action {
            background: rgba(56, 189, 248, 0.15); color: var(--primary); border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 10px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 14px; transition: 0.3s;
        }
        .btn-action:hover { background: var(--primary); color: #0f172a; }
        .btn-logout { background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
        .btn-logout:hover { background: #ef4444; color: #fff; }

        /* Section Title */
        .section-title { font-size: 20px; font-weight: 800; margin-bottom: 20px; color: #fff; display: flex; align-items: center; gap: 10px; }
        
        /* Bookings Grid */
        .bookings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px; }
        
        .booking-card {
            background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px; padding: 22px; box-shadow: 0 15px 30px rgba(0,0,0,0.5); transition: 0.3s;
        }
        .booking-card:hover { border-color: var(--primary); transform: translateY(-3px); }

        .movie-title { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 8px; }
        .booking-detail { font-size: 13px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; display: flex; justify-content: space-between; }
        .booking-detail span { color: #f8fafc; font-weight: 700; }

        /* Status Badges */
        .badge-pending { background: rgba(234, 179, 8, 0.15); color: #facc15; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid rgba(234, 179, 8, 0.3); }
        .badge-approved { background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-rejected { background: rgba(239, 68, 68, 0.15); color: #f87171; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid rgba(239, 68, 68, 0.3); }

        .empty-state { text-align: center; padding: 50px; color: #94a3b8; font-weight: 600; background: var(--card-bg); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.08); grid-column: 1 / -1; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Header -->
        <div class="dash-header">
            <div class="user-info">
                <h1>Welcome, <span><?= htmlspecialchars($user['name']) ?></span>!</h1>
                <p><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> <?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div class="nav-btns">
                <a href="index.php" class="btn-action"><i class="fa-solid fa-film"></i> Browse Movies</a>
                <a href="logout.php" class="btn-action btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>

        <!-- My Bookings Section -->
        <div class="section-title">
            <i class="fa-solid fa-ticket" style="color: var(--primary);"></i> My Movie Tickets & Bookings
        </div>

        <div class="bookings-grid">
            <?php if ($bookings->num_rows > 0): ?>
                <?php while($row = $bookings->fetch_assoc()): ?>
                    <div class="booking-card">
                        <div class="movie-title"><?= htmlspecialchars($row['movie_title']) ?></div>
                        <div class="booking-detail">Booking ID: <span>#<?= $row['id'] ?></span></div>
                        <div class="booking-detail">Selected Seats: <span style="color: var(--primary);"><?= htmlspecialchars($row['seats']) ?></span></div>
                        <div class="booking-detail">Total Price: <span>NPR <?= number_format($row['movie_price'], 2) ?></span></div>
                        <div class="booking-detail" style="align-items: center; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06);">
                            Status: 
                            <?php if($row['status'] == 'Pending'): ?>
                                <span class="badge-pending">Pending Verification</span>
                            <?php elseif($row['status'] == 'Approved'): ?>
                                <span class="badge-approved">Approved (Confirmed)</span>
                            <?php else: ?>
                                <span class="badge-rejected">Rejected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-ticket-simple" style="font-size: 40px; color: var(--primary); margin-bottom: 12px; display: block;"></i>
                    You haven't booked any movies yet! <br>
                    <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 700; margin-top: 8px; display: inline-block;">Book your first movie now →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>