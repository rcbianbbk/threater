<?php
$page_title = "Admin Dashboard | Cine Premiere Pro";
include 'db.php';

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
    $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, price, poster_image, description, rating, trailer_url, show_times, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssdsss", 
        $_POST['title'], $_POST['genre'], $_POST['duration'], $_POST['price'], 
        $_POST['poster_image'], $_POST['description'], $_POST['rating'], 
        $_POST['trailer_url'], $_POST['show_times'], $_POST['status']
    );
    
    if ($stmt->execute()) {
        $msg = "Movie added successfully!";
    } else {
        $err = "Database error: " . $conn->error;
    }
}

// Movie Edit Logic
if (isset($_POST['edit_movie'])) {
    $stmt = $conn->prepare("UPDATE movies SET title=?, genre=?, duration=?, price=?, poster_image=?, description=?, rating=?, trailer_url=?, show_times=?, status=? WHERE id=?");
    $stmt->bind_param("ssisssdsssi", 
        $_POST['title'], $_POST['genre'], $_POST['duration'], $_POST['price'], 
        $_POST['poster_image'], $_POST['description'], $_POST['rating'], 
        $_POST['trailer_url'], $_POST['show_times'], $_POST['status'], $_POST['movie_id']
    );
    if ($stmt->execute()) {
        $msg = "Movie updated successfully!";
    } else {
        $err = "Update failed: " . $conn->error;
    }
}

// Movie Delete Logic
if (isset($_GET['delete_movie'])) {
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete_movie']);
    $stmt->execute();
    header("Location: dashboard.php?msg=deleted");
    exit();
}

// Booking Payment Status Update
if (isset($_POST['update_payment_status'])) {
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $_POST['status'], $_POST['booking_id']);
    $stmt->execute();
    $msg = "Payment status updated!";
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

// Data Fetching
$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");
$bookings = $conn->query("
    SELECT b.*, u.name as user_name, u.email as user_email, m.title as movie_title 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.id
    ORDER BY b.id DESC
");
$users_list = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <!-- CSS & FontAwesome -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pro-bg: #05070b;
            --pro-surface: rgba(15, 20, 32, 0.75);
            --pro-surface-hover: rgba(22, 29, 47, 0.85);
            --pro-border: rgba(255, 255, 255, 0.08);
            --accent-primary: #6366f1;
        }
        
        body {
            background-color: var(--pro-bg) !important;
            font-family: 'Inter', sans-serif;
            color: #f8fafc;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        /* --- LIVE DYNAMIC BACKGROUND GLOW EFFECTS --- */
        .live-bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            pointer-events: none;
        }

        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.35;
            animation: floatGlow 12s infinite alternate ease-in-out;
        }

        .orb-1 {
            width: 450px;
            height: 450px;
            background: #6366f1;
            top: -100px;
            left: -100px;
            animation-duration: 14s;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #a855f7;
            bottom: -150px;
            right: -100px;
            animation-duration: 18s;
            animation-delay: -3s;
        }

        .orb-3 {
            width: 350px;
            height: 350px;
            background: #06b6d4;
            top: 40%;
            left: 30%;
            animation-duration: 10s;
            animation-delay: -6s;
            opacity: 0.2;
        }

        @keyframes floatGlow {
            0% {
                transform: translate(0px, 0px) scale(1);
            }
            50% {
                transform: translate(60px, 80px) scale(1.15);
            }
            100% {
                transform: translate(-40px, 50px) scale(0.9);
            }
        }
        
        h1, h2, h3, h4, h5 { font-family: 'Outfit', sans-serif; }
        
        /* Pro Admin Topbar with Glassmorphism */
        .pro-topbar {
            background: rgba(11, 15, 25, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--pro-border);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .pro-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 21px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .pro-logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }
        
        .pro-badge {
            background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.2));
            color: #a5b4fc;
            border: 1px solid rgba(99, 102, 241, 0.3);
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        /* Container */
        .pro-container {
            max-width: 1500px;
            margin: 35px auto;
            padding: 0 30px 80px 30px;
            position: relative;
            z-index: 1;
        }

        /* Luxury Glass Cards */
        .pro-card {
            background: var(--pro-surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--pro-border);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .pro-card:hover {
            border-color: rgba(99, 102, 241, 0.25);
        }

        /* Pro Custom Tabs */
        .pro-tabs-wrapper {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            background: rgba(11, 15, 25, 0.5);
            backdrop-filter: blur(12px);
            padding: 8px;
            border-radius: 14px;
            border: 1px solid var(--pro-border);
            width: fit-content;
        }
        
        .pro-tab-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 12px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.25s ease;
            font-family: 'Outfit', sans-serif;
        }
        
        .pro-tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }
        
        .pro-tab-btn.active {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }

        /* Pro Table Enhancements */
        .pro-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .pro-table th {
            background: rgba(5, 7, 11, 0.6);
            color: #94a3b8;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--pro-border);
            font-family: 'Outfit', sans-serif;
        }
        
        .pro-table td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--pro-border);
            font-size: 14px;
            color: #cbd5e1;
            vertical-align: middle;
        }
        
        .pro-table tbody tr {
            transition: background 0.2s ease;
        }
        
        .pro-table tbody tr:hover {
            background: var(--pro-surface-hover);
        }
    </style>
</head>
<body>

    <!-- LIVE ANIMATED BACKGROUND GLOW ORBS -->
    <div class="live-bg-container">
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    </div>

    <!-- Pro Admin Topbar -->
    <header class="pro-topbar">
        <div class="pro-logo">
            <div class="pro-logo-icon">
                <i class="fa-solid fa-crown"></i>
            </div>
            <span>CINE PREMIERE</span>
            <span class="pro-badge">PRO v2.5</span>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="text-align: right; display: none; @media(min-width: 768px){display:block;}">
                <span style="display: block; font-size: 14px; font-weight: 700; color: #fff;">Super Administrator</span>
                <span style="font-size: 11px; color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> System Secure</span>
            </div>
            <a href="logout.php" class="btn btn-danger btn-sm" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 600; border-radius: 10px; background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); text-decoration: none;">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </div>
    </header>

    <main class="pro-container">
        
        <!-- Top Action Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: 32px; margin: 0 0 8px 0; color: #fff; font-weight: 800;">Executive Dashboard</h1>
                <p style="color: #94a3b8; font-size: 15px; margin: 0;">Real-time management portal for global cinema operations & bookings.</p>
            </div>
            <div>
                <button onclick="document.getElementById('addMovieModal').style.display='flex'" class="btn btn-accent" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 24px; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 10px 25px rgba(99,102,241,0.4); cursor: pointer; border: none; color: white;">
                    <i class="fa-solid fa-plus-circle fa-lg"></i> Add New Movie
                </button>
            </div>
        </div>

        <?php if($msg != "" || isset($_GET['msg'])): ?>
            <div style="padding: 16px 20px; background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.35); color: #34d399; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 12px; font-weight: 500;">
                <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i> 
                <span><?= $msg ?: "Action performed successfully." ?></span>
            </div>
        <?php endif; ?>

        <?php if($err != ""): ?>
            <div style="padding: 16px 20px; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.35); color: #f87171; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 12px; font-weight: 500;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 20px;"></i> 
                <span><?= $err ?></span>
            </div>
        <?php endif; ?>

        <!-- KPI Metric Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
            <div class="pro-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #10b981;">
                <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #34d399;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <small style="color: #94a3b8; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 1px;">Total Revenue</small>
                    <h3 style="font-size: 26px; color: #fff; margin: 6px 0 0 0; font-weight: 700;">Rs. <?= number_format($total_revenue, 2) ?></h3>
                </div>
            </div>

            <div class="pro-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #06b6d4;">
                <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(6,182,212,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #22d3ee;">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                    <small style="color: #94a3b8; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 1px;">Total Bookings</small>
                    <h3 style="font-size: 26px; color: #fff; margin: 6px 0 0 0; font-weight: 700;"><?= $total_bookings ?></h3>
                </div>
            </div>

            <div class="pro-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #6366f1;">
                <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(99,102,241,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #818cf8;">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div>
                    <small style="color: #94a3b8; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 1px;">Active Movies</small>
                    <h3 style="font-size: 26px; color: #fff; margin: 6px 0 0 0; font-weight: 700;"><?= $active_movies ?></h3>
                </div>
            </div>

            <div class="pro-card" style="padding: 24px; display: flex; align-items: center; gap: 20px; border-left: 5px solid #f59e0b;">
                <div style="width: 58px; height: 58px; border-radius: 16px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fbbf24;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <small style="color: #94a3b8; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 1px;">Registered Users</small>
                    <h3 style="font-size: 26px; color: #fff; margin: 6px 0 0 0; font-weight: 700;"><?= $total_users ?></h3>
                </div>
            </div>
        </div>

        <!-- Pro Tab Navigation Bar -->
        <div class="pro-tabs-wrapper">
            <button onclick="switchTab('moviesTab', this)" class="pro-tab-btn active">
                <i class="fa-solid fa-clapperboard"></i> Movies Catalog
            </button>
            <button onclick="switchTab('bookingsTab', this)" class="pro-tab-btn">
                <i class="fa-solid fa-receipt"></i> Customer Bookings & Verification
            </button>
            <button onclick="switchTab('usersTab', this)" class="pro-tab-btn">
                <i class="fa-solid fa-users"></i> Customer Accounts
            </button>
        </div>

        <!-- TAB 1: MOVIES CATALOG -->
        <div id="moviesTab" class="tab-pane pro-card" style="padding: 24px;">
            <div class="table-responsive">
                <table class="pro-table">
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
                                    <td style="width: 80px;">
                                        <img src="<?= htmlspecialchars($poster) ?>" alt="Poster" style="width: 50px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid var(--pro-border);">
                                    </td>
                                    <td>
                                        <strong style="color: #fff; font-size: 15px; display: block; font-family: 'Outfit', sans-serif;"><?= htmlspecialchars($m['title']) ?></strong>
                                        <small style="color: #fbbf24; font-weight: 600;"><i class="fa-solid fa-star"></i> <?= $m['rating'] ?: 4.5 ?> / 10</small>
                                    </td>
                                    <td><span style="background: rgba(99,102,241,0.15); color: #818cf8; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($m['genre']) ?></span></td>
                                    <td><span style="font-weight: 500;"><?= $m['duration'] ?> Mins</span></td>
                                    <td><strong style="color: #34d399; font-size: 15px;">Rs. <?= number_format($m['price'], 2) ?></strong></td>
                                    <td>
                                        <span class="badge-status <?= ($m['status'] == 'coming_soon') ? 'coming_soon' : '' ?>">
                                            <?= ($m['status'] == 'coming_soon') ? 'Coming Soon' : 'Now Showing' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button onclick='openEditModal(<?= json_encode($m) ?>)' class="btn btn-outline btn-sm" title="Edit Movie" style="border-radius: 8px; padding: 6px 12px;">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="dashboard.php?delete_movie=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this movie?');" title="Delete Movie" style="border-radius: 8px; padding: 6px 12px;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; color: #94a3b8; padding: 50px;">No movies found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: CUSTOMER BOOKINGS & VERIFICATION -->
        <div id="bookingsTab" class="tab-pane pro-card" style="padding: 30px; display: none;">
            <div style="margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div style="position: relative; max-width: 400px; width: 100%;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 14px;"></i>
                    <input type="text" id="bookingSearch" placeholder="Search customer, movie, txn ref..." onkeyup="filterBookingsTable()" style="width: 100%; background: rgba(5, 7, 11, 0.6); border: 1px solid rgba(255,255,255,0.08); padding: 14px 16px 14px 46px; border-radius: 14px; color: #fff; font-size: 14px; outline: none;">
                </div>
                <div style="display: flex; align-items: center; gap: 10px; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); padding: 8px 16px; border-radius: 12px;">
                    <i class="fa-solid fa-shield-halved" style="color: #34d399; font-size: 14px;"></i>
                    <span style="color: #34d399; font-size: 13px; font-weight: 600;">Transaction Verification Secure</span>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="pro-table" id="bookingsTable">
                    <thead>
                        <tr style="color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                            <th>Ticket Code</th>
                            <th>Customer</th>
                            <th>Movie</th>
                            <th>Seats</th>
                            <th>Amount</th>
                            <th>Txn Ref</th>
                            <th>Payment Status</th>
                            <th style="text-align: center;">Receipt</th>
                            <th style="text-align: right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($bookings && $bookings->num_rows > 0): ?>
                            <?php while($b = $bookings->fetch_assoc()): 
                                $ticket_code = $b['ticket_code'] ?: ('CW-' . str_pad($b['id'], 6, '0', STR_PAD_LEFT));
                                $receipt_image = !empty($b['payment_screenshot']) ? $b['payment_screenshot'] : (strpos($b['transaction_id'], 'uploads/') !== false ? $b['transaction_id'] : '');
                                
                                $status_color = "#f59e0b";
                                $status_bg = "rgba(245, 158, 11, 0.1)";
                                if($b['payment_status'] == 'Success' || $b['payment_status'] == 'Verified') {
                                    $status_color = "#34d399";
                                    $status_bg = "rgba(16, 185, 129, 0.1)";
                                } elseif($b['payment_status'] == 'Cancelled') {
                                    $status_color = "#f87171";
                                    $status_bg = "rgba(239, 68, 68, 0.1)";
                                }
                            ?>
                                <tr>
                                    <td>
                                        <a href="ticket.php?id=<?= $b['id'] ?>" target="_blank" style="color: #a5b4fc; font-weight: 700; text-decoration: none;">
                                            <i class="fa-solid fa-ticket" style="font-size: 12px; color: #818cf8;"></i> <?= htmlspecialchars($ticket_code) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong style="color: #ffffff; display: block;"><?= htmlspecialchars($b['user_name']) ?></strong>
                                        <span style="color: #64748b; font-size: 12px;"><?= htmlspecialchars($b['user_email']) ?></span>
                                    </td>
                                    <td><span style="font-weight: 600; color: #e2e8f0;"><?= htmlspecialchars($b['movie_title']) ?></span></td>
                                    <td>
                                        <span style="color: #38bdf8; background: rgba(56,189,248,0.1); padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">
                                            <?= htmlspecialchars($b['seat_number']) ?>
                                        </span>
                                    </td>
                                    <td><strong style="color: #34d399;">Rs. <?= number_format($b['total_amount'] ?? 350, 2) ?></strong></td>
                                    <td>
                                        <span style="background: rgba(5,7,11,0.5); padding: 6px 10px; border-radius: 8px; color: #94a3b8; font-size: 11px;">
                                            <?= htmlspecialchars($b['transaction_id'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <select name="status" style="padding: 7px 12px; font-size: 12px; width: 120px; background: <?= $status_bg ?>; color: <?= $status_color ?>; border: 1px solid <?= $status_color ?>40; border-radius: 8px; font-weight: 700; outline: none; cursor: pointer;" onchange="this.form.submit();">
                                                <option value="Success" <?= ($b['payment_status'] == 'Success') ? 'selected' : '' ?> style="background: #0f1420; color: #34d399;">Success</option>
                                                <option value="Pending" <?= ($b['payment_status'] == 'Pending') ? 'selected' : '' ?> style="background: #0f1420; color: #f59e0b;">Pending</option>
                                                <option value="Cancelled" <?= ($b['payment_status'] == 'Cancelled') ? 'selected' : '' ?> style="background: #0f1420; color: #f87171;">Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_payment_status" value="1">
                                        </form>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if(!empty($receipt_image)): ?>
                                            <button type="button" onclick="openScreenshotModal('<?= htmlspecialchars($receipt_image, ENT_QUOTES) ?>')" style="padding: 6px 12px; font-size: 12px; background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3); border-radius: 8px; font-weight: 600; cursor: pointer;">
                                                <i class="fa-solid fa-image"></i> View SS
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #475569; font-size: 12px; font-style: italic;">No SS</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; color: #64748b; font-size: 12px;">
                                        <?= date('M d, H:i', strtotime($b['booking_date'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9" style="text-align: center; color: #64748b; padding: 60px;">No customer bookings recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: REGISTERED CUSTOMERS -->
        <div id="usersTab" class="tab-pane pro-card" style="padding: 24px; display: none;">
            <div class="table-responsive">
                <table class="pro-table">
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
                                    <td><span style="color: #818cf8; font-weight: 700;">#<?= $u['id'] ?></span></td>
                                    <td><strong style="color: #fff;"><?= htmlspecialchars($u['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span style="color: #38bdf8;"><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></span></td>
                                    <td style="color: #94a3b8;"><?= date('M d, Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: #94a3b8; padding: 50px;">No registered users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- SCREENSHOT PREVIEW MODAL -->
    <div id="screenshotModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); align-items:center; justify-content:center; padding:20px;">
        <div class="pro-card" style="width:100%; max-width:520px; max-height:90vh; display:flex; flex-direction:column; padding:24px; text-align:center;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--pro-border); padding-bottom:12px;">
                <h3 style="color:#fff; font-size:18px; margin:0;"><i class="fa-solid fa-receipt" style="color:var(--accent-primary); margin-right:8px;"></i> Payment Screenshot Viewer</h3>
                <button onclick="closeScreenshotModal()" style="background:none; border:none; color:#94a3b8; font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div style="overflow-y:auto; flex-grow:1; display:flex; justify-content:center; align-items:center;">
                <img id="modalImageSrc" src="" alt="Payment Screenshot" style="max-width:100%; max-height:70vh; border-radius:10px; border:1px solid var(--pro-border); object-fit:contain;">
            </div>
            <div style="margin-top:18px;">
                <a id="downloadImageBtn" href="" target="_blank" class="btn btn-outline btn-sm" style="font-size:13px; border-radius: 8px; padding: 8px 16px;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Original Image
                </a>
            </div>
        </div>
    </div>

    <!-- Add Movie Modal -->
    <div id="addMovieModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); align-items:center; justify-content:center; padding:20px;">
        <div class="pro-card" style="width:100%; max-width:650px; max-height:90vh; overflow-y:auto; padding:35px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid var(--pro-border); padding-bottom:15px;">
                <h3 style="color:#fff; margin:0; font-size:20px;"><i class="fa-solid fa-plus-circle" style="color:#6366f1; margin-right:8px;"></i> Add New Movie</h3>
                <button onclick="document.getElementById('addMovieModal').style.display='none'" style="background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Movie Title *</label>
                        <input type="text" name="title" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Genre *</label>
                        <input type="text" name="genre" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Duration (Mins) *</label>
                        <input type="number" name="duration" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Ticket Price (Rs.) *</label>
                        <input type="number" step="0.01" name="price" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Rating (0 - 10)</label>
                        <input type="number" step="0.1" name="rating" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" value="8.9">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Poster Image URL</label>
                        <input type="url" name="poster_image" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">YouTube Trailer URL</label>
                        <input type="url" name="trailer_url" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Movie Status</label>
                        <select name="status" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                            <option value="now_showing" style="background:#0f1420;">Now Showing</option>
                            <option value="coming_soon" style="background:#0f1420;">Coming Soon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Daily Showtimes</label>
                        <input type="text" name="show_times" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" value="11:00 AM, 03:00 PM, 07:00 PM">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Description</label>
                        <textarea name="description" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_movie" class="btn btn-accent btn-lg" style="width:100%; margin-top:20px; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 14px;">
                    <i class="fa-solid fa-check"></i> Save & Publish Movie
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Movie Modal -->
    <div id="editMovieModal" style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); align-items:center; justify-content:center; padding:20px;">
        <div class="pro-card" style="width:100%; max-width:650px; max-height:90vh; overflow-y:auto; padding:35px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid var(--pro-border); padding-bottom:15px;">
                <h3 style="color:#fff; margin:0; font-size:20px;"><i class="fa-solid fa-pen-to-square" style="color:#6366f1; margin-right:8px;"></i> Edit Movie Details</h3>
                <button onclick="document.getElementById('editMovieModal').style.display='none'" style="background:none; border:none; color:#94a3b8; font-size:22px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST">
                <input type="hidden" name="movie_id" id="edit_id">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Movie Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Genre</label>
                        <input type="text" name="genre" id="edit_genre" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Duration (Mins)</label>
                        <input type="number" name="duration" id="edit_duration" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Ticket Price (Rs.)</label>
                        <input type="number" step="0.01" name="price" id="edit_price" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Rating</label>
                        <input type="number" step="0.1" name="rating" id="edit_rating" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Poster Image URL</label>
                        <input type="url" name="poster_image" id="edit_poster" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Trailer URL</label>
                        <input type="url" name="trailer_url" id="edit_trailer" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Status</label>
                        <select name="status" id="edit_status" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                            <option value="now_showing" style="background:#0f1420;">Now Showing</option>
                            <option value="coming_soon" style="background:#0f1420;">Coming Soon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Daily Showtimes</label>
                        <input type="text" name="show_times" id="edit_show_times" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" style="color:#94a3b8; font-size:13px; font-weight:600;">Description</label>
                        <textarea name="description" id="edit_desc" class="form-control" style="background:rgba(5,7,11,0.6); border-color:var(--pro-border); color:#fff; border-radius: 10px; padding: 12px;" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" name="edit_movie" class="btn btn-accent btn-lg" style="width:100%; margin-top:20px; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #6366f1, #4f46e5); padding: 14px;">
                    <i class="fa-solid fa-floppy-disk"></i> Update Movie Details
                </button>
            </form>
        </div>
    </div>

    <script>
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.pro-tab-btn').forEach(b => {
            b.classList.remove('active');
        });
        document.getElementById(tabId).style.display = 'block';
        btn.classList.add('active');
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
        document.getElementById('edit_show_times').value = movie.show_times || '';
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

    function openScreenshotModal(imageUrl) {
        document.getElementById('modalImageSrc').src = imageUrl;
        document.getElementById('downloadImageBtn').href = imageUrl;
        document.getElementById('screenshotModal').style.display = 'flex';
    }

    function closeScreenshotModal() {
        document.getElementById('screenshotModal').style.display = 'none';
        document.getElementById('modalImageSrc').src = '';
    }
    </script>
</body>
</html>