<?php
$page_title = "Admin Dashboard";
include 'db.php';

<<<<<<< HEAD
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
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    exit(); 
}

$msg = "";
$err = "";

// Movie Add Logic
if (isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);
    $poster_image = trim($_POST['poster_image']);
    $description = trim($_POST['description']);
    $rating = floatval($_POST['rating'] ?? 4.5);
    $trailer_url = trim($_POST['trailer_url']);
    $show_times = trim($_POST['show_times'] ?? '10:30 AM, 02:00 PM, 05:30 PM, 08:45 PM');
    $status = trim($_POST['status'] ?? 'now_showing');

    if (!empty($title) && !empty($genre) && $duration > 0 && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, price, poster_image, description, rating, trailer_url, show_times, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssdsss", $title, $genre, $duration, $price, $poster_image, $description, $rating, $trailer_url, $show_times, $status);
        if ($stmt->execute()) {
            $msg = "Movie '{$title}' added successfully!";
        } else {
            $err = "Failed to add movie: " . htmlspecialchars($conn->error);
        }
    } else {
        $err = "Please fill in all required movie fields correctly.";
    }
}

// Movie Edit Logic
if (isset($_POST['edit_movie'])) {
    $m_id = intval($_POST['movie_id']);
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);
    $poster_image = trim($_POST['poster_image']);
    $description = trim($_POST['description']);
    $rating = floatval($_POST['rating']);
    $trailer_url = trim($_POST['trailer_url']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("UPDATE movies SET title=?, genre=?, duration=?, price=?, poster_image=?, description=?, rating=?, trailer_url=?, status=? WHERE id=?");
    $stmt->bind_param("ssisssdssi", $title, $genre, $duration, $price, $poster_image, $description, $rating, $trailer_url, $status, $m_id);
    if ($stmt->execute()) {
        $msg = "Movie updated successfully!";
    } else {
        $err = "Failed to update movie.";
    }
}

// Movie Delete Logic (Fixed redirection bug)
if (isset($_GET['delete_movie'])) {
    $m_id = intval($_GET['delete_movie']);
    $conn->query("DELETE FROM movies WHERE id = $m_id");
    header("Location: dashboard.php?msg=deleted");
    exit();
}

// Booking Payment Status Update Logic
if (isset($_POST['update_payment_status'])) {
    $b_id = intval($_POST['booking_id']);
    $new_status = trim($_POST['status']);
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $b_id);
    $stmt->execute();
    $msg = "Booking #{$b_id} payment status updated to '{$new_status}'";
}

// KPI Stats Calculation
$total_revenue_res = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE payment_status = 'Success' OR payment_status = 'Verified'");
$total_revenue = $total_revenue_res ? $total_revenue_res->fetch_assoc()['total'] : 0;

$total_bookings_res = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
$total_bookings = $total_bookings_res ? $total_bookings_res->fetch_assoc()['cnt'] : 0;

$active_movies_res = $conn->query("SELECT COUNT(*) as cnt FROM movies WHERE status = 'now_showing' OR status IS NULL");
$active_movies = $active_movies_res ? $active_movies_res->fetch_assoc()['cnt'] : 0;

$total_users_res = $conn->query("SELECT COUNT(*) as cnt FROM users");
$total_users = $total_users_res ? $total_users_res->fetch_assoc()['cnt'] : 0;

// All Movies Fetch
$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");

// All Bookings Fetch
$bookings = $conn->query("
    SELECT b.*, u.name as user_name, u.email as user_email, m.title as movie_title 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.id DESC
");

// All Users Fetch
$users_list = $conn->query("SELECT id, name, email, phone, created_at FROM users ORDER BY id DESC");

include 'includes/header.php';
?>

<main class="container" style="margin-top: 25px; margin-bottom: 70px;">
    
    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <span class="badge-status" style="background: rgba(99,102,241,0.2); color: #818cf8; border: 1px solid rgba(99,102,241,0.4); font-size: 12px; margin-bottom: 6px; display: inline-block;">
                <i class="fa-solid fa-shield-halved"></i> Cinema Management System
            </span>
            <h1 style="font-size: 28px;">Admin Operations Portal</h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="document.getElementById('addMovieModal').style.display='flex'" class="btn btn-accent">
                <i class="fa-solid fa-plus"></i> Add New Movie
            </button>
            <a href="logout.php" class="btn btn-danger">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </div>
    </div>

    <?php if($msg != "" || isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $msg ?: "Action performed successfully." ?></div>
    <?php endif; ?>

    <?php if($err != ""): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?></div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div class="glass-card" style="padding: 22px; display: flex; align-items: center; gap: 18px; border-left: 4px solid #10b981;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #34d399;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <small style="color: var(--text-dim); text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Revenue</small>
                <h3 style="font-size: 24px; color: #fff; font-family: 'Outfit';">Rs. <?= number_format($total_revenue, 2) ?></h3>
            </div>
        </div>

        <div class="glass-card" style="padding: 22px; display: flex; align-items: center; gap: 18px; border-left: 4px solid #06b6d4;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(6,182,212,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #22d3ee;">
                <i class="fa-solid fa-ticket"></i>
            </div>
            <div>
                <small style="color: var(--text-dim); text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Total Bookings</small>
                <h3 style="font-size: 24px; color: #fff; font-family: 'Outfit';"><?= $total_bookings ?></h3>
            </div>
        </div>

        <div class="glass-card" style="padding: 22px; display: flex; align-items: center; gap: 18px; border-left: 4px solid #6366f1;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(99,102,241,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #818cf8;">
                <i class="fa-solid fa-film"></i>
            </div>
            <div>
                <small style="color: var(--text-dim); text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Active Movies</small>
                <h3 style="font-size: 24px; color: #fff; font-family: 'Outfit';"><?= $active_movies ?></h3>
            </div>
        </div>

        <div class="glass-card" style="padding: 22px; display: flex; align-items: center; gap: 18px; border-left: 4px solid #f59e0b;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fbbf24;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <small style="color: var(--text-dim); text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">Registered Users</small>
                <h3 style="font-size: 24px; color: #fff; font-family: 'Outfit';"><?= $total_users ?></h3>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="display: flex; gap: 12px; margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
        <button onclick="switchTab('moviesTab', this)" class="btn btn-accent tab-btn">
            <i class="fa-solid fa-clapperboard"></i> Movies Catalog
        </button>
        <button onclick="switchTab('bookingsTab', this)" class="btn btn-outline tab-btn">
            <i class="fa-solid fa-receipt"></i> Customer Bookings & Verification
        </button>
        <button onclick="switchTab('usersTab', this)" class="btn btn-outline tab-btn">
            <i class="fa-solid fa-users"></i> Customer Accounts
        </button>
    </div>

    <!-- TAB 1: MOVIES CATALOG -->
    <div id="moviesTab" class="tab-pane">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Title & Details</th>
                        <th>Genre</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($movies && $movies->num_rows > 0): ?>
                        <?php while($m = $movies->fetch_assoc()): 
                            $poster = !empty($m['poster_image']) ? $m['poster_image'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=200';
                        ?>
                            <tr>
                                <td style="width: 70px;">
                                    <img src="<?= htmlspecialchars($poster) ?>" alt="Poster" style="width: 50px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                </td>
                                <td>
                                    <strong style="color: #fff; font-size: 15px; display: block;"><?= htmlspecialchars($m['title']) ?></strong>
                                    <small style="color: #fbbf24;"><i class="fa-solid fa-star"></i> <?= $m['rating'] ?: 4.5 ?> / 10</small>
                                </td>
                                <td><span class="movie-genre-tag"><?= htmlspecialchars($m['genre']) ?></span></td>
                                <td><?= $m['duration'] ?> Mins</td>
                                <td><strong style="color: var(--accent);">Rs. <?= number_format($m['price'], 2) ?></strong></td>
                                <td>
                                    <span class="badge-status <?= ($m['status'] == 'coming_soon') ? 'coming_soon' : '' ?>">
                                        <?= ($m['status'] == 'coming_soon') ? 'Coming Soon' : 'Now Showing' ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <button onclick='openEditModal(<?= json_encode($m) ?>)' class="btn btn-outline btn-sm" title="Edit Movie">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="dashboard.php?delete_movie=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this movie?');" title="Delete Movie">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px;">No movies found. Click "+ Add New Movie" to add one.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: CUSTOMER BOOKINGS & VERIFICATION -->
    <div id="bookingsTab" class="tab-pane" style="display: none;">
        <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="position: relative; max-width: 320px; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                <input type="text" id="bookingSearch" placeholder="Search by customer, movie, txn..." class="form-control" style="padding-left: 38px;" onkeyup="filterBookingsTable()">
            </div>
            <span style="color: var(--text-muted); font-size: 13px;">Showing latest transactions</span>
        </div>

        <div class="table-responsive">
            <table class="custom-table" id="bookingsTable">
                <thead>
                    <tr>
                        <th>Ticket Code</th>
                        <th>Customer</th>
                        <th>Movie</th>
                        <th>Seats</th>
                        <th>Showtime</th>
                        <th>Amount</th>
                        <th>Transaction Ref</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($bookings && $bookings->num_rows > 0): ?>
                        <?php while($b = $bookings->fetch_assoc()): 
                            $ticket_code = $b['ticket_code'] ?: ('CW-' . str_pad($b['id'], 6, '0', STR_PAD_LEFT));
                        ?>
                            <tr>
                                <td>
                                    <a href="ticket.php?id=<?= $b['id'] ?>" target="_blank" style="color: #818cf8; font-weight: 700; font-family: 'Outfit';">
                                        <i class="fa-solid fa-receipt"></i> <?= htmlspecialchars($ticket_code) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['user_name']) ?></strong>
                                    <small style="display: block; color: var(--text-dim);"><?= htmlspecialchars($b['user_email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($b['movie_title']) ?></td>
                                <td><strong style="color: var(--accent);"><?= htmlspecialchars($b['seat_number']) ?></strong></td>
                                <td><?= htmlspecialchars($b['show_time'] ?? '05:30 PM') ?></td>
                                <td><strong>Rs. <?= number_format($b['total_amount'] ?? 350, 2) ?></strong></td>
                                <td>
                                    <span style="font-family: monospace; background: #090d16; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-color); color: #38bdf8; font-size: 12px;">
                                        <?= htmlspecialchars($b['transaction_id'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <select name="status" class="form-control" style="padding: 6px 10px; font-size: 12px; width: 110px;" onchange="this.form.submit();">
                                            <option value="Success" <?= ($b['payment_status'] == 'Success') ? 'selected' : '' ?>>Success</option>
                                            <option value="Pending" <?= ($b['payment_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="Cancelled" <?= ($b['payment_status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_payment_status" value="1">
                                    </form>
                                </td>
                                <td style="font-size: 12px; color: var(--text-dim);"><?= date('M d, H:i', strtotime($b['booking_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align: center; color: var(--text-muted); padding: 40px;">No bookings recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
        </div>
    </div>

    <!-- TAB 3: REGISTERED CUSTOMERS -->
    <div id="usersTab" class="tab-pane" style="display: none;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users_list && $users_list->num_rows > 0): ?>
                        <?php while($u = $users_list->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">No registered users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Add Movie Modal -->
<div id="addMovieModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card" style="width:100%; max-width:620px; max-height:90vh; overflow-y:auto; padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
            <h3 style="color:#fff;"><i class="fa-solid fa-plus-circle" style="color:var(--accent); margin-right:8px;"></i> Add New Movie</h3>
            <button onclick="document.getElementById('addMovieModal').style.display='none'" style="background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Movie Title <span style="color:#fb7185;">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Gladiator II" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Genre <span style="color:#fb7185;">*</span></label>
                    <input type="text" name="genre" class="form-control" placeholder="Action / Drama / History" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Duration (Minutes) <span style="color:#fb7185;">*</span></label>
                    <input type="number" name="duration" class="form-control" placeholder="150" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ticket Price (Rs.) <span style="color:#fb7185;">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="400" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Audience Rating (0 - 10)</label>
                    <input type="number" step="0.1" name="rating" class="form-control" placeholder="8.5" value="8.5">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Poster Image URL</label>
                    <input type="url" name="poster_image" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">YouTube Trailer URL</label>
                    <input type="url" name="trailer_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                </div>

                <div class="form-group">
                    <label class="form-label">Movie Status</label>
                    <select name="status" class="form-control">
                        <option value="now_showing">Now Showing</option>
                        <option value="coming_soon">Coming Soon</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Daily Showtimes</label>
                    <input type="text" name="show_times" class="form-control" value="10:30 AM, 02:00 PM, 05:30 PM, 08:45 PM">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Synopsis / Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief storyline of the film..."></textarea>
                </div>
            </div>

            <button type="submit" name="add_movie" class="btn btn-accent btn-lg" style="width:100%; margin-top:10px;">
                <i class="fa-solid fa-check"></i> Save & Publish Movie
            </button>
        </form>
    </div>
</div>

<!-- Edit Movie Modal -->
<div id="editMovieModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.8); backdrop-filter:blur(8px); align-items:center; justify-content:center; padding:20px;">
    <div class="glass-card" style="width:100%; max-width:620px; max-height:90vh; overflow-y:auto; padding:30px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid var(--border-color); padding-bottom:12px;">
            <h3 style="color:#fff;"><i class="fa-solid fa-pen-to-square" style="color:var(--accent); margin-right:8px;"></i> Edit Movie</h3>
            <button onclick="document.getElementById('editMovieModal').style.display='none'" style="background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST">
            <input type="hidden" name="movie_id" id="edit_id">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Movie Title</label>
                    <input type="text" name="title" id="edit_title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Genre</label>
                    <input type="text" name="genre" id="edit_genre" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Duration (Mins)</label>
                    <input type="number" name="duration" id="edit_duration" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ticket Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Rating</label>
                    <input type="number" step="0.1" name="rating" id="edit_rating" class="form-control">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Poster Image URL</label>
                    <input type="url" name="poster_image" id="edit_poster" class="form-control">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Trailer URL</label>
                    <input type="url" name="trailer_url" id="edit_trailer" class="form-control">
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="now_showing">Now Showing</option>
                        <option value="coming_soon">Coming Soon</option>
                    </select>
                </div>

                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <button type="submit" name="edit_movie" class="btn btn-accent btn-lg" style="width:100%; margin-top:10px;">
                <i class="fa-solid fa-floppy-disk"></i> Update Movie Details
            </button>
        </form>
    </div>
</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('btn-accent');
        b.classList.add('btn-outline');
    });
    document.getElementById(tabId).style.display = 'block';
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-accent');
}

function openEditModal(movie) {
    document.getElementById('edit_id').value = movie.id;
    document.getElementById('edit_title').value = movie.title || '';
    document.getElementById('edit_genre').value = movie.genre || '';
    document.getElementById('edit_duration').value = movie.duration || 120;
    document.getElementById('edit_price').value = movie.price || 350;
    document.getElementById('edit_rating').value = movie.rating || 4.5;
    document.getElementById('edit_poster').value = movie.poster_image || '';
    document.getElementById('edit_trailer').value = movie.trailer_url || '';
    document.getElementById('edit_status').value = movie.status || 'now_showing';
    document.getElementById('edit_desc').value = movie.description || '';

    document.getElementById('editMovieModal').style.display = 'flex';
}

function filterBookingsTable() {
    let filter = document.getElementById('bookingSearch').value.toLowerCase();
    let rows = document.querySelectorAll('#bookingsTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>

<?php include 'includes/footer.php'; ?>