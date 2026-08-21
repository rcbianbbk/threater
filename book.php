<?php
$page_title = "Book Tickets & Select Seats";
include 'db.php';

<<<<<<< HEAD
// 1. User login check
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Movie ID check from URL parameter
$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$movie_query = $conn->query("SELECT * FROM movies WHERE id = $movie_id");
$movie = $movie_query->fetch_assoc();

if (!$movie) {
    header("Location: index.php");
    exit();
}

$error = "";

// 2. Form submit huda execute hune code (Seat + Screenshot Processing)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_seats = isset($_POST['seats']) ? trim($_POST['seats']) : '';
    
    // Validation: Seat check
    if (empty($selected_seats)) {
        $error = "Please select at least one seat!";
    } 
    // Validation: Payment Screenshot upload check
    elseif (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] != 0) {
        $error = "Please upload your payment screenshot!";
    } else {
        // 3. Handle Payment Screenshot Image Upload & Save
        $img_name = $_FILES['payment_screenshot']['name'];
        $tmp_name = $_FILES['payment_screenshot']['tmp_name'];
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        
        // Unique file name generate garne taaki file name clash na hos
        $new_img_name = "pay_" . uniqid() . "." . $ext;
        
        // Uploads folder xena vane automatically create garne
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        
        $upload_path = "uploads/" . $new_img_name;

        // Move file to uploads folder & save booking to database
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Database ma insert garne (transaction_id column ma screenshot filename save huncha)
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seats, transaction_id, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("iiss", $user_id, $movie_id, $selected_seats, $new_img_name);
            
            if ($stmt->execute()) {
                header("Location: profile.php?booked=success");
                exit();
            } else {
                $error = "Database error! Please try again.";
            }
        } else {
            $error = "Failed to upload payment screenshot image.";
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User must be logged in to book seats
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$movie_id = intval($_GET['movie_id'] ?? 0);
$movie_res = $conn->query("SELECT * FROM movies WHERE id = $movie_id");
$movie = ($movie_res && $movie_res->num_rows > 0) ? $movie_res->fetch_assoc() : null;

if (!$movie) {
    // If invalid movie, redirect home
    header("Location: index.php");
    exit();
}

// Selected showtime (defaults to first available)
$available_times = array_map('trim', explode(',', $movie['show_times'] ?: '10:30 AM, 02:00 PM, 05:30 PM, 08:45 PM'));
$selected_showtime = $_POST['show_time'] ?? $_GET['show_time'] ?? ($available_times[0] ?? '05:30 PM');

// Booked Seats for this movie & showtime
$booked_seats = [];
$res = $conn->query("SELECT seat_number FROM bookings WHERE movie_id = $movie_id AND (show_time = '$selected_showtime' OR show_time IS NULL)");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $seats_in_booking = explode(',', $row['seat_number']);
        foreach ($seats_in_booking as $s) {
            $cleaned = trim($s);
            if (!empty($cleaned)) {
                $booked_seats[] = $cleaned;
            }
        }
    }
}

$msg = "";
$err = "";
$confirmed_booking_id = null;

// Handle Booking Form Submission
if (isset($_POST['confirm_booking'])) {
    $selected_seats_raw = trim($_POST['selected_seats'] ?? '');
    $selected_showtime = trim($_POST['show_time'] ?? $available_times[0]);
    $payment_method = trim($_POST['payment_method'] ?? 'eSewa / Khalti QR');
    $txn_id = trim($_POST['transaction_id'] ?? '');

    $seat_array = array_filter(array_map('trim', explode(',', $selected_seats_raw)));

    if (empty($seat_array)) {
        $err = "Please select at least one seat before proceeding!";
    } elseif (empty($txn_id)) {
        $err = "Please enter your Payment Transaction ID / Reference Code!";
    } else {
        // Check if any selected seat got booked concurrently
        $conflict = false;
        foreach ($seat_array as $st) {
            if (in_array($st, $booked_seats)) {
                $conflict = true;
                break;
            }
        }

        if ($conflict) {
            $err = "One or more selected seats have just been booked. Please choose other seats.";
        } else {
            $total_seats = count($seat_array);
            $total_amount = $total_seats * floatval($movie['price']);
            $joined_seats = implode(', ', $seat_array);
            $ticket_code = 'CW-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number, show_time, total_amount, payment_method, payment_status, transaction_id, ticket_code) VALUES (?, ?, ?, ?, ?, ?, 'Success', ?, ?)");
            $stmt->bind_param("iissdsss", $_SESSION['user_id'], $movie_id, $joined_seats, $selected_showtime, $total_amount, $payment_method, $txn_id, $ticket_code);

            if ($stmt->execute()) {
                $confirmed_booking_id = $conn->insert_id;
                $msg = "Ticket booked successfully! Ticket Code: <strong>$ticket_code</strong>";
                foreach ($seat_array as $st) {
                    $booked_seats[] = $st;
                }
            } else {
                $err = "Booking failed due to database error: " . htmlspecialchars($conn->error);
            }
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
        }
    }
}

include 'includes/header.php';
?>
<<<<<<< HEAD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema World - Book Seats & Pay</title>
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
        body { background: var(--bg-dark); color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }

        .booking-container {
            background: var(--card-bg); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 35px; border-radius: 28px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.8);
        }
        .header-title { text-align: center; margin-bottom: 25px; }
        .header-title h2 { font-size: 24px; font-weight: 900; color: #fff; }
        .header-title span { color: var(--primary); }

        .screen-indicator {
            background: linear-gradient(90deg, transparent, var(--primary-glow), transparent);
            text-align: center; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px;
            color: #94a3b8; padding: 6px; margin-bottom: 20px; border-radius: 6px;
        }

        .seats-grid {
            display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 25px;
        }
        .seat-btn {
            background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff; padding: 12px 0; border-radius: 10px; font-weight: 700; font-size: 13px;
            cursor: pointer; text-align: center; transition: 0.2s; user-select: none;
        }
        .seat-btn:hover { border-color: var(--primary); }
        .seat-btn.selected { background: var(--primary); color: #0f172a; border-color: var(--primary); box-shadow: 0 0 12px var(--primary-glow); }

        .payment-box {
            background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 20px; border-radius: 20px; margin-bottom: 20px; text-align: center;
        }
        .qr-img { width: 140px; height: 140px; border-radius: 12px; background: #fff; padding: 8px; margin: 10px auto; display: block; object-fit: contain; }

        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #94a3b8; margin-bottom: 6px; }
        .file-input {
            width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; color: #fff; font-size: 13px; outline: none; cursor: pointer;
        }

        .btn-submit {
            width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            font-weight: 800; font-size: 15px; padding: 14px; border-radius: 14px; border: none; cursor: pointer;
            transition: 0.3s; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4); display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { transform: scale(1.02); }
        .error-msg { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 15px; text-align: center; }
        .back-link { display: block; text-align: center; color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 600; margin-top: 15px; }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="booking-container">
        <div class="header-title">
            <h2>Select Seat & Pay</h2>
            <p style="color: #64748b; font-size: 13px; font-weight: 600; margin-top: 4px;"><?= htmlspecialchars($movie['title']) ?> (NPR <?= number_format($movie['price'], 2) ?>)</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <!-- Form with enctype multipart for image file upload -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="screen-indicator">Screen This Way</div>

            <!-- Seat Grid Selection -->
            <div class="seats-grid" id="seatsGrid">
                <?php 
                $rows = ['A', 'B', 'C', 'D'];
                foreach($rows as $r) {
                    for($i = 1; $i <= 6; $i++) {
                        $seatName = $r.$i;
                        echo '<div class="seat-btn" onclick="toggleSeat(this, \'' . $seatName . '\')">' . $seatName . '</div>';
                    }
                }
                ?>
            </div>

            <input type="hidden" name="seats" id="selectedSeatsInput">

            <!-- Payment Box with QR and Screenshot File Upload -->
            <div class="payment-box">
                <p style="font-size: 13px; font-weight: 700; color: #fff;"><i class="fa-solid fa-qrcode" style="color: var(--primary);"></i> Scan QR to Pay (eSewa / Khalti)</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=esewa_payment_dummy" alt="Payment QR" class="qr-img">
                
                <div class="form-group">
                    <label><i class="fa-solid fa-image" style="color: var(--primary);"></i> Upload Payment Screenshot:</label>
                    <input type="file" name="payment_screenshot" accept="image/*" required class="file-input">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-shield-check"></i> Submit & Verify Payment
            </button>
        </form>

        <a href="index.php" class="back-link">← Back to Home</a>
    </div>

    <script>
    let selectedSeats = [];

    function toggleSeat(element, seatName) {
        if (element.classList.contains('selected')) {
            element.classList.remove('selected');
            selectedSeats = selectedSeats.filter(s => s !== seatName);
        } else {
            element.classList.add('selected');
            selectedSeats.push(seatName);
        }
        document.getElementById('selectedSeatsInput').value = selectedSeats.join(',');
    }
    </script>
=======

<style>
/* Seat Map Styling */
.hall-container {
    background: #090d16;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 30px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cinema-screen-curve {
    width: 85%;
    max-width: 540px;
    height: 12px;
    margin: 0 auto 10px;
    background: linear-gradient(180deg, #38bdf8 0%, rgba(56, 189, 248, 0.05) 100%);
    border-radius: 50%;
    box-shadow: 0 10px 25px rgba(56, 189, 248, 0.45);
}

.screen-label {
    font-size: 11px;
    letter-spacing: 4px;
    color: var(--text-dim);
    text-transform: uppercase;
    margin-bottom: 30px;
}

.seating-matrix {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
    max-width: 520px;
    margin: 0 auto;
}

.seat-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.row-letter {
    width: 24px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-dim);
}

.seat-item {
    width: 38px;
    height: 36px;
    background: #141b2d;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px 8px 4px 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    position: relative;
}

.seat-item:hover:not(.seat-booked) {
    background: #38bdf8;
    color: #0b0f19;
    border-color: #38bdf8;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.4);
}

.seat-item.seat-selected {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #fff !important;
    transform: scale(1.08);
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.5);
}

.seat-item.seat-booked {
    background: #27141f;
    border-color: rgba(244, 63, 94, 0.3);
    color: #64748b;
    cursor: not-allowed;
    opacity: 0.5;
}

.seat-item.seat-vip {
    border-color: rgba(245, 158, 11, 0.5);
}

.seat-legend {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 13px;
    color: var(--text-muted);
}

.legend-pill {
    display: flex;
    align-items: center;
    gap: 8px;
}

.legend-box {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

.time-chip {
    padding: 8px 16px;
    border-radius: 8px;
    background: #141b2d;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    display: inline-block;
}
.time-chip.active, .time-chip:hover {
    background: rgba(99, 102, 241, 0.2);
    border-color: var(--primary);
    color: #fff;
}
</style>

<main class="container" style="margin-top: 15px; margin-bottom: 60px;">

    <?php if($confirmed_booking_id): ?>
        <!-- Success Confirmation Card -->
        <div class="glass-card" style="max-width: 600px; margin: 20px auto; padding: 35px; text-align: center; border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 style="color: #fff; margin-bottom: 8px;">Booking Confirmed!</h2>
            <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 25px;">
                <?= $msg ?>
            </p>

            <div style="background: #090d16; border-radius: var(--radius-md); padding: 20px; text-align: left; margin-bottom: 25px; border: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: var(--text-dim);">Movie:</span>
                    <strong style="color: #fff;"><?= htmlspecialchars($movie['title']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: var(--text-dim);">Showtime:</span>
                    <strong style="color: var(--accent);"><?= htmlspecialchars($selected_showtime) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: var(--text-dim);">Selected Seats:</span>
                    <strong style="color: #34d399;"><?= htmlspecialchars($joined_seats) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Total Amount:</span>
                    <strong style="color: #fff; font-size: 16px;">Rs. <?= number_format($total_amount, 2) ?></strong>
                </div>
            </div>

            <div style="display: flex; gap: 14px; justify-content: center;">
                <a href="ticket.php?id=<?= $confirmed_booking_id ?>" class="btn btn-accent btn-lg">
                    <i class="fa-solid fa-receipt"></i> View & Print E-Ticket
                </a>
                <a href="my_bookings.php" class="btn btn-outline btn-lg">
                    <i class="fa-solid fa-list-check"></i> My Bookings
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- Breadcrumb / Back Link -->
        <div style="margin-bottom: 20px;">
            <a href="index.php" style="color: var(--text-muted); font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Now Showing
            </a>
        </div>

        <?php if($err != ""): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start; @media(max-width: 900px){ grid-template-columns: 1fr; }">
            
            <!-- Left Column: Movie Info, Showtime Picker & Hall Matrix -->
            <div>
                <!-- Movie Quick Glance Header -->
                <div class="glass-card" style="padding: 22px; margin-bottom: 25px; display: flex; gap: 20px; align-items: center;">
                    <img src="<?= htmlspecialchars($movie['poster_image'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400') ?>" alt="<?= htmlspecialchars($movie['title']) ?>" style="width: 75px; height: 110px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div>
                        <span class="badge-status" style="font-size: 11px; padding: 2px 8px; margin-bottom: 6px; display: inline-block;">Screen 1 • Laser 4K</span>
                        <h2 style="font-size: 22px; margin-bottom: 4px;"><?= htmlspecialchars($movie['title']) ?></h2>
                        <p style="color: var(--text-muted); font-size: 13px;">
                            <?= htmlspecialchars($movie['genre']) ?> • <?= $movie['duration'] ?> Mins • <span style="color: #fbbf24;"><i class="fa-solid fa-star"></i> <?= $movie['rating'] ?: 4.5 ?></span>
                        </p>
                    </div>
                </div>

                <!-- Showtime Selector -->
                <div class="glass-card" style="padding: 20px; margin-bottom: 25px;">
                    <h4 style="font-size: 15px; margin-bottom: 12px; color: #fff;">
                        <i class="fa-regular fa-clock" style="color: var(--accent); margin-right: 6px;"></i> 1. Select Showtime
                    </h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach($available_times as $time): ?>
                            <a href="book.php?movie_id=<?= $movie_id ?>&show_time=<?= urlencode($time) ?>" class="time-chip <?= ($selected_showtime == $time) ? 'active' : '' ?>">
                                <i class="fa-solid fa-sun" style="font-size: 11px; margin-right: 4px;"></i> <?= htmlspecialchars($time) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Interactive Cinema Hall Map -->
                <div class="hall-container">
                    <h4 style="font-size: 15px; margin-bottom: 18px; color: #fff; text-align: left;">
                        <i class="fa-solid fa-couch" style="color: var(--accent); margin-right: 6px;"></i> 2. Choose Your Seats
                    </h4>

                    <div class="cinema-screen-curve"></div>
                    <p class="screen-label">Screen Projection</p>

                    <div class="seating-matrix">
                        <?php
                        $row_configs = [
                            ['label' => 'VIP', 'row' => 'A', 'seats' => 6, 'is_vip' => true],
                            ['label' => 'Prem', 'row' => 'B', 'seats' => 8, 'is_vip' => false],
                            ['label' => 'Prem', 'row' => 'C', 'seats' => 8, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'D', 'seats' => 8, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'E', 'seats' => 8, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'F', 'seats' => 8, 'is_vip' => false]
                        ];

                        foreach ($row_configs as $rc):
                        ?>
                            <div class="seat-row">
                                <span class="row-letter"><?= $rc['row'] ?></span>
                                <div style="display: flex; gap: 6px;">
                                    <?php for($s = 1; $s <= $rc['seats']; $s++): 
                                        $seat_code = $rc['row'] . $s;
                                        $is_booked = in_array($seat_code, $booked_seats);
                                        $seat_class = 'seat-item';
                                        if ($is_booked) $seat_class .= ' seat-booked';
                                        if ($rc['is_vip']) $seat_class .= ' seat-vip';
                                    ?>
                                        <div class="<?= $seat_class ?>" data-seat="<?= $seat_code ?>" <?= $is_booked ? 'title="Already Booked"' : 'onclick="toggleSeat(this)"' ?>>
                                            <?= $seat_code ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <span class="row-letter"><?= $rc['row'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="seat-legend">
                        <div class="legend-pill">
                            <div class="legend-box" style="background:#141b2d; border:1px solid rgba(255,255,255,0.15);"></div>
                            <span>Available</span>
                        </div>
                        <div class="legend-pill">
                            <div class="legend-box" style="background:#10b981;"></div>
                            <span>Selected</span>
                        </div>
                        <div class="legend-pill">
                            <div class="legend-box" style="background:#27141f; border:1px solid rgba(244,63,94,0.3);"></div>
                            <span>Occupied</span>
                        </div>
                        <div class="legend-pill">
                            <div class="legend-box" style="background:#141b2d; border:1px solid rgba(245,158,11,0.6);"></div>
                            <span>VIP Recliner (A)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Booking Summary & Payment Checkout Form -->
            <div>
                <div class="glass-card" style="padding: 24px; position: sticky; top: 90px;">
                    <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <i class="fa-solid fa-receipt" style="color: var(--accent); margin-right: 6px;"></i> Booking Summary
                    </h3>

                    <div style="margin-bottom: 18px; font-size: 14px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Movie Ticket:</span>
                            <span style="color:#fff;">Rs. <?= number_format($movie['price'], 2) ?> / seat</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Selected Seats:</span>
                            <span id="summarySeatsDisplay" style="color:var(--accent); font-weight:700;">None Selected</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Showtime:</span>
                            <span style="color:#fff;"><?= htmlspecialchars($selected_showtime) ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding-top:10px; border-top:1px dashed rgba(255,255,255,0.1); margin-top:10px; font-size:16px;">
                            <span style="font-weight:700;">Total Payable:</span>
                            <span id="summaryTotalDisplay" style="font-weight:800; font-family:'Outfit'; color:#34d399; font-size:20px;">Rs. 0.00</span>
                        </div>
                    </div>

                    <!-- Payment Box -->
                    <form method="POST" id="bookingForm" onsubmit="return validateBooking();">
                        <input type="hidden" name="selected_seats" id="selected_seats_input">
                        <input type="hidden" name="show_time" value="<?= htmlspecialchars($selected_showtime) ?>">

                        <div style="background: #090d16; border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); margin-bottom: 18px; text-align: center;">
                            <p style="font-size: 13px; font-weight: 700; color: #38bdf8; margin-bottom: 10px;">
                                <i class="fa-solid fa-qrcode"></i> Instant Scan & Pay (eSewa / Khalti)
                            </p>
                            
                            <!-- Dynamic QR preview -->
                            <img id="qrCodeImg" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=CinemaWorld_Movie_<?= $movie['id'] ?>" alt="Payment QR" style="width: 130px; height: 130px; border-radius: 8px; background: #fff; padding: 4px; border: 2px solid var(--accent); margin: 0 auto 10px; display: block;">

                            <span style="font-size: 11px; color: var(--text-dim); display: block; margin-bottom: 10px;">
                                Scan with eSewa / Khalti / Mobile Banking App
                            </span>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 12px;">Transaction / Ref ID <span style="color: #fb7185;">*</span></label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="e.g. TXN-8924018" required style="font-size: 13px; padding: 10px;">
                            </div>
                            <input type="hidden" name="payment_method" value="eSewa / Khalti QR">
                        </div>

                        <button type="submit" name="confirm_booking" id="btnSubmitBooking" class="btn btn-accent" style="width: 100%; padding: 14px; font-size: 15px;" disabled>
                            <i class="fa-solid fa-lock"></i> Select Seats to Proceed
                        </button>
                    </form>
                </div>
            </div>

        </div>

    <?php endif; ?>

</main>

<script>
const unitPrice = <?= floatval($movie['price']) ?>;
const movieId = <?= intval($movie['id']) ?>;
let selectedSeats = [];

function toggleSeat(elem) {
    const seatCode = elem.getAttribute('data-seat');
    
    if (selectedSeats.includes(seatCode)) {
        selectedSeats = selectedSeats.filter(s => s !== seatCode);
        elem.classList.remove('seat-selected');
    } else {
        selectedSeats.push(seatCode);
        elem.classList.add('seat-selected');
    }

    updateSummary();
}

function updateSummary() {
    const seatsInput = document.getElementById('selected_seats_input');
    const seatsDisplay = document.getElementById('summarySeatsDisplay');
    const totalDisplay = document.getElementById('summaryTotalDisplay');
    const btnSubmit = document.getElementById('btnSubmitBooking');
    const qrImg = document.getElementById('qrCodeImg');

    if (selectedSeats.length === 0) {
        seatsInput.value = '';
        seatsDisplay.innerText = 'None Selected';
        totalDisplay.innerText = 'Rs. 0.00';
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-lock"></i> Select Seats to Proceed';
        btnSubmit.classList.remove('btn-accent');
        btnSubmit.classList.add('btn-outline');
    } else {
        const sorted = [...selectedSeats].sort();
        const joined = sorted.join(', ');
        const total = (selectedSeats.length * unitPrice).toFixed(2);

        seatsInput.value = joined;
        seatsDisplay.innerText = `${joined} (${selectedSeats.length})`;
        totalDisplay.innerText = `Rs. ${total}`;
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = `<i class="fa-solid fa-circle-check"></i> Pay & Confirm Rs. ${total}`;
        btnSubmit.classList.remove('btn-outline');
        btnSubmit.classList.add('btn-accent');

        // Update QR code data string
        if (qrImg) {
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=CinemaWorld_Pay_Rs${total}_Movie${movieId}_Seats_${encodeURIComponent(joined)}`;
        }
    }
}

function validateBooking() {
    if (selectedSeats.length === 0) {
        alert("Please select at least one seat from the hall map!");
        return false;
    }
    return true;
}
</script>
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987

<?php include 'includes/footer.php'; ?>