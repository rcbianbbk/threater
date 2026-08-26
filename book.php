<?php
$page_title = "Book Tickets & Select Seats";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User must be logged in to book seats
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = intval($_SESSION['user_id']);
$movie_id = intval($_GET['movie_id'] ?? 0);
$movie_res = $conn->query("SELECT * FROM movies WHERE id = $movie_id");
$movie = ($movie_res && $movie_res->num_rows > 0) ? $movie_res->fetch_assoc() : null;

if (!$movie) {
    header("Location: index.php");
    exit();
}

// Check user's previous total booked seats from database (lifetime history)
$prev_booked_seats_count = 0;
$history_stmt = $conn->prepare("SELECT seat_number FROM bookings WHERE user_id = ? AND payment_status != 'Cancelled'");
if ($history_stmt) {
    $history_stmt->bind_param("i", $user_id);
    $history_stmt->execute();
    $history_res = $history_stmt->get_result();
    while ($row = $history_res->fetch_assoc()) {
        $seats_arr = explode(',', $row['seat_number']);
        foreach ($seats_arr as $s) {
            if (trim($s) !== '') {
                $prev_booked_seats_count++;
            }
        }
    }
}

// Selected showtime (defaults to first available)
$available_times = array_map('trim', explode(',', $movie['show_times'] ?: '10:30 AM, 02:00 PM, 05:30 PM, 08:45 PM'));
$selected_showtime = $_POST['show_time'] ?? $_GET['show_time'] ?? ($available_times[0] ?? '05:30 PM');

if (!in_array($selected_showtime, $available_times)) {
    $selected_showtime = $available_times[0];
}

// Booked Seats for this movie & showtime (Hall occupancy check)
$booked_seats = [];
$stmt_seats = $conn->prepare("SELECT seat_number FROM bookings WHERE movie_id = ? AND show_time = ? AND payment_status != 'Cancelled'");
$stmt_seats->bind_param("is", $movie_id, $selected_showtime);
$stmt_seats->execute();
$res = $stmt_seats->get_result();

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

// VIP Extra Charge per seat
$vip_extra_fee = 150.00;

// Handle Booking Form Submission
if (isset($_POST['confirm_booking'])) {
    $selected_seats_raw = trim($_POST['selected_seats'] ?? '');
    $selected_showtime = trim($_POST['show_time'] ?? $available_times[0]);
    $payment_method = trim($_POST['payment_method'] ?? 'eSewa');
    
    $seat_array = array_filter(array_map('trim', explode(',', $selected_seats_raw)));
    $total_seats_count = count($seat_array);

    // Calculate base seat prices
    $base_price = floatval($movie['price']);
    $seat_prices = [];
    foreach ($seat_array as $st) {
        $price = $base_price;
        if (strpos($st, 'D') === 0 || strpos($st, 'E') === 0) {
            $price += $vip_extra_fee;
        }
        $seat_prices[] = $price;
    }

    $is_100_milestone = ($prev_booked_seats_count >= 100);
    $is_discount_eligible = ($prev_booked_seats_count >= 5 && $prev_booked_seats_count < 10);
    
    $subtotal = array_sum($seat_prices);
    $total_amount = $subtotal;

    // नियम अनुसार: फ्री र छुट छुट्टाछुट्टै लागू हुने (एकैचोटि दुवै पर्दैन)
    if ($is_100_milestone) {
        foreach ($seat_prices as $k => $val) {
            $seat_prices[$k] = 0; 
        }
        $total_amount = 0;
    } elseif ($prev_booked_seats_count >= 10 && !empty($seat_prices)) {
        rsort($seat_prices);
        $seat_prices[0] = 0; // १० सिट माईलस्टोनमा एउटा सिट मात्र फ्री, बाँकीको फुल प्राइस
        $total_amount = array_sum($seat_prices);
    } elseif ($is_discount_eligible && !empty($seat_prices)) {
        $total_amount = $subtotal - ($subtotal * 0.30); // ५ देखि ९ सिट सम्म मात्र ३०% छुट
    }

    // Handle Payment Screenshot Upload
    $screenshot_path = $is_100_milestone ? "Milestone Reward (100+ Seats: Free + Membership + Voucher)" : "Free / Loyalty Reward";
    if ($total_amount > 0) {
        if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $file_name = $_FILES['payment_screenshot']['name'];
            $file_tmp = $_FILES['payment_screenshot']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = "uploads/payments/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_file_name = "pay_" . uniqid() . "." . $file_ext;
                $screenshot_path = $upload_dir . $new_file_name;
                move_uploaded_file($file_tmp, $screenshot_path);
            } else {
                $err = "कृपया मान्य (JPG, PNG, JPEG) फोटो मात्र अपलोड गर्नुहोस्!";
            }
        } else {
            $err = "Please upload your payment screenshot/receipt!";
        }
    }

    if ($total_seats_count === 0) {
        $err = "Please select at least one seat before proceeding!";
    } elseif ($err === "") {
        // Re-verify booked seats securely in hall
        $fresh_booked_seats = [];
        $stmt_check = $conn->prepare("SELECT seat_number FROM bookings WHERE movie_id = ? AND show_time = ? AND payment_status != 'Cancelled'");
        $stmt_check->bind_param("is", $movie_id, $selected_showtime);
        $stmt_check->execute();
        $check_res = $stmt_check->get_result();
        if ($check_res) {
            while ($row = $check_res->fetch_assoc()) {
                foreach (explode(',', $row['seat_number']) as $s) {
                    $c = trim($s);
                    if (!empty($c)) $fresh_booked_seats[] = $c;
                }
            }
        }

        $conflict = false;
        foreach ($seat_array as $st) {
            if (in_array($st, $fresh_booked_seats)) {
                $conflict = true;
                break;
            }
        }

        if ($conflict) {
            $err = "One or more selected seats have just been booked. Please choose other seats.";
        } else {
            $joined_seats = implode(', ', $seat_array);
            $ticket_code = 'CW-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $pay_status = ($total_amount <= 0) ? 'Verified' : 'Pending';

            $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number, show_time, total_amount, payment_method, payment_status, transaction_id, ticket_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissdssss", $user_id, $movie_id, $joined_seats, $selected_showtime, $total_amount, $payment_method, $pay_status, $screenshot_path, $ticket_code);

            if ($stmt->execute()) {
                $confirmed_booking_id = $conn->insert_id;
                
                if ($total_amount <= 0) {
                    $msg = "🎉 <strong>Congratulations and Thank You!</strong><br> तपाईंले यो बुकिङ पूर्ण रूपमा निःशुल्क (Free) प्राप्त गर्नुभएको छ।";
                    if ($is_100_milestone) {
                        $msg .= "<br><span style='color: #fbbf24; font-weight: bold;'><i class='fa-solid fa-gift'></i> विशेष उपहार: १०० सिट पूरा भएकोमा १० दिनको फ्रि मेम्बरशिप र रु. ५०० को फुड भाउचर प्राप्त गर्नुभएको छ!</span>";
                    }
                } else {
                    $msg = "Ticket booked successfully! Ticket Code: <strong>$ticket_code</strong>";
                }

                foreach ($seat_array as $st) {
                    $booked_seats[] = $st;
                }
            } else {
                $err = "Booking failed due to database error: " . htmlspecialchars($conn->error);
            }
        }
    }
}

include 'includes/header.php';
?>

<style>
body {
    background-color: #05070c;
    background-image: 
        radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 40%),
        radial-gradient(circle at 85% 80%, rgba(56, 189, 248, 0.1) 0%, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.05) 0%, transparent 60%);
    background-attachment: fixed;
    position: relative;
    overflow-x: hidden;
}

body::before {
    content: '';
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(120deg, rgba(56, 189, 248, 0.03) 0%, rgba(99, 102, 241, 0.05) 50%, rgba(16, 185, 129, 0.03) 100%);
    pointer-events: none;
    z-index: -1;
    animation: ambientGlow 12s ease-in-out infinite alternate;
}

@keyframes ambientGlow {
    0% { transform: scale(1) translateY(0); filter: hue-rotate(0deg); }
    100% { transform: scale(1.05) translateY(-10px); filter: hue-rotate(15deg); }
}

.hall-container {
    background: rgba(13, 18, 30, 0.75);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-lg);
    padding: 30px 20px;
    text-align: center;
    position: relative;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.cinema-screen-curve {
    width: 85%;
    max-width: 650px;
    height: 14px;
    margin: 0 auto 10px;
    background: linear-gradient(180deg, #38bdf8 0%, rgba(56, 189, 248, 0.02) 100%);
    border-radius: 50%;
    box-shadow: 0 12px 30px rgba(56, 189, 248, 0.6);
    animation: screenPulse 4s ease-in-out infinite alternate;
}

@keyframes screenPulse {
    0% { box-shadow: 0 8px 20px rgba(56, 189, 248, 0.4); }
    100% { box-shadow: 0 15px 35px rgba(56, 189, 248, 0.8); }
}

.screen-label {
    font-size: 11px;
    letter-spacing: 5px;
    color: var(--text-dim);
    text-transform: uppercase;
    margin-bottom: 30px;
}

.seating-matrix {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    min-width: 600px;
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
    width: 34px;
    height: 34px;
    background: rgba(20, 27, 45, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px 8px 4px 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    position: relative;
}

.seat-item:hover:not(.seat-booked) {
    background: #38bdf8;
    color: #0b0f19;
    border-color: #38bdf8;
    transform: scale(1.12);
    box-shadow: 0 6px 16px rgba(56, 189, 248, 0.5);
}

.seat-item.seat-selected {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #fff !important;
    transform: scale(1.1);
    box-shadow: 0 0 18px rgba(16, 185, 129, 0.6);
}

.seat-item.seat-booked {
    background: #27141f;
    border-color: rgba(244, 63, 94, 0.3);
    color: #64748b;
    cursor: not-allowed;
    opacity: 0.45;
}

.seat-item.seat-vip {
    border-color: rgba(245, 158, 11, 0.8);
    background: rgba(26, 23, 16, 0.9);
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
    background: rgba(20, 27, 45, 0.8);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.25s;
    text-decoration: none;
    display: inline-block;
}
.time-chip.active, .time-chip:hover {
    background: rgba(99, 102, 241, 0.25);
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.pay-tab-btn {
    flex: 1;
    padding: 8px 10px;
    background: rgba(20, 27, 45, 0.9);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s;
}
.pay-tab-btn.active {
    background: var(--accent);
    color: #090d16;
    border-color: var(--accent);
    font-weight: 700;
}

.glass-card {
    background: rgba(13, 18, 30, 0.75);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-lg);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}
</style>

<main class="container" style="margin-top: 15px; margin-bottom: 60px;">

    <?php if($confirmed_booking_id): ?>
        <div class="glass-card" style="max-width: 600px; margin: 20px auto; padding: 35px; text-align: center; border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 0 35px rgba(16, 185, 129, 0.25);">
            <div style="width: 70px; height: 70px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 20px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 style="color: #fff; margin-bottom: 8px;">Booking Success!</h2>
            <div style="color: var(--text-muted); font-size: 15px; margin-bottom: 25px; line-height: 1.6;">
                <?= $msg ?>
            </div>

            <div style="background: rgba(9, 13, 22, 0.8); border-radius: var(--radius-md); padding: 20px; text-align: left; margin-bottom: 25px; border: 1px solid var(--border-color);">
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
                    <i class="fa-solid fa-receipt"></i> View E-Ticket
                </a>
                <a href="my_bookings.php" class="btn btn-outline btn-lg">
                    <i class="fa-solid fa-list-check"></i> My Bookings
                </a>
            </div>
        </div>
    <?php else: ?>

        <div style="margin-bottom: 20px;">
            <a href="index.php" style="color: var(--text-muted); font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Now Showing
            </a>
        </div>

        <?php if($err != ""): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?>
            </div>
        <?php endif; ?>

        <!-- Dynamic User Status & Loyalty Offer Banner -->
        <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: var(--radius-md); padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: #fbbf24; font-size: 13px; backdrop-filter: blur(10px);">
            <i class="fa-solid fa-crown" style="font-size: 18px;"></i>
            <div>
                <strong>Account Status:</strong> You have booked <strong><?= $prev_booked_seats_count ?></strong> seats previously. 
                <?php if($prev_booked_seats_count >= 100): ?>
                    <span style="color: #34d399; font-weight: 700;">(🎉 100+ Milestone: Free Bookings + Membership Active!)</span>
                <?php elseif($prev_booked_seats_count >= 10): ?>
                    <span style="color: #34d399; font-weight: 700;">(10+ Seat Milestone: 1 Seat Free, remaining at full price!)</span>
                <?php elseif($prev_booked_seats_count >= 5): ?>
                    <span style="color: #34d399; font-weight: 700;">(Member Loyalty 30% Discount Active.)</span>
                <?php else: ?>
                    <span>(Book more seats to unlock special discounts and 100-seat milestone rewards!)</span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
            
            <!-- Left Column -->
            <div>
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

                <div class="hall-container">
                    <h4 style="font-size: 15px; margin-bottom: 18px; color: #fff; text-align: left;">
                        <i class="fa-solid fa-couch" style="color: var(--accent); margin-right: 6px;"></i> 2. Choose Your Seats (Rows A - H)
                    </h4>

                    <div class="cinema-screen-curve"></div>
                    <p class="screen-label">Screen Projection</p>

                    <div class="seating-matrix">
                        <?php
                        $row_configs = [
                            ['label' => 'Std', 'row' => 'A', 'seats' => 12, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'B', 'seats' => 12, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'C', 'seats' => 12, 'is_vip' => false],
                            ['label' => 'VIP', 'row' => 'D', 'seats' => 12, 'is_vip' => true],
                            ['label' => 'VIP', 'row' => 'E', 'seats' => 12, 'is_vip' => true],
                            ['label' => 'Std', 'row' => 'F', 'seats' => 12, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'G', 'seats' => 12, 'is_vip' => false],
                            ['label' => 'Std', 'row' => 'H', 'seats' => 12, 'is_vip' => false]
                        ];

                        foreach ($row_configs as $rc):
                        ?>
                            <div class="seat-row">
                                <span class="row-letter"><?= $rc['row'] ?></span>
                                <div style="display: flex; gap: 5px;">
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
                            <div class="legend-box" style="background:rgba(20,27,45,0.9); border:1px solid rgba(255,255,255,0.15);"></div>
                            <span>Standard (Rs. <?= number_format($movie['price'], 0) ?>)</span>
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
                            <div class="legend-box" style="background:rgba(26,23,16,0.9); border:1px solid rgba(245,158,11,0.8);"></div>
                            <span>VIP (Row D & E) (+Rs. <?= number_format($vip_extra_fee, 0) ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Payment Section -->
            <div>
                <div class="glass-card" style="padding: 24px; position: sticky; top: 90px;">
                    <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <i class="fa-solid fa-receipt" style="color: var(--accent); margin-right: 6px;"></i> Booking Summary
                    </h3>

                    <div style="margin-bottom: 18px; font-size: 14px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Std Price:</span>
                            <span style="color:#fff;">Rs. <?= number_format($movie['price'], 2) ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">VIP (Row D & E) Extra:</span>
                            <span style="color:#fbbf24;">+ Rs. <?= number_format($vip_extra_fee, 2) ?></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Selected Seats:</span>
                            <span id="summarySeatsDisplay" style="color:var(--accent); font-weight:700;">None Selected</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="color:var(--text-muted);">Showtime:</span>
                            <span style="color:#fff;"><?= htmlspecialchars($selected_showtime) ?></span>
                        </div>
                        <div id="discountNoticeContainer" style="display:none; background: rgba(16, 185, 129, 0.12); border: 1px dashed rgba(16, 185, 129, 0.4); padding: 8px; border-radius: 6px; margin-top: 8px; font-size: 12px; color: #34d399; text-align: center;">
                            <i class="fa-solid fa-tags"></i> <span id="discountText"></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding-top:10px; border-top:1px dashed rgba(255,255,255,0.1); margin-top:10px; font-size:16px;">
                            <span style="font-weight:700;">Total Payable:</span>
                            <span id="summaryTotalDisplay" style="font-weight:800; font-family:'Outfit'; color:#34d399; font-size:20px;">Rs. 0.00</span>
                        </div>
                    </div>

                    <form method="POST" id="bookingForm" enctype="multipart/form-data" onsubmit="return validateBooking();">
                        <input type="hidden" name="selected_seats" id="selected_seats_input">
                        <input type="hidden" name="show_time" value="<?= htmlspecialchars($selected_showtime) ?>">
                        <input type="hidden" name="payment_method" id="payment_method_input" value="eSewa">

                        <!-- Payment Box -->
                        <div id="paymentBoxContainer" style="background: rgba(9, 13, 22, 0.8); border-radius: var(--radius-md); padding: 16px; border: 1px solid var(--border-color); margin-bottom: 18px; text-align: center;">
                            <p style="font-size: 13px; font-weight: 700; color: #38bdf8; margin-bottom: 10px;">
                                <i class="fa-solid fa-qrcode"></i> Choose Payment & Scan QR
                            </p>
                            
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <button type="button" class="pay-tab-btn active" id="btnEsewaTab" onclick="switchPaymentGateway('eSewa')">
                                    <i class="fa-solid fa-wallet"></i> eSewa
                                </button>
                                <button type="button" class="pay-tab-btn" id="btnBankTab" onclick="switchPaymentGateway('Khalti / Bank')">
                                    <i class="fa-solid fa-building-columns"></i> Khalti / Bank
                                </button>
                            </div>

                            <img id="qrCodeImg" src="uploads/payments/esewa_qr.png" alt="Payment QR" style="width: 120px; height: 120px; border-radius: 8px; background: #fff; padding: 4px; border: 2px solid var(--accent); margin: 0 auto 10px; display: block; object-fit: contain;">

                            <span id="qrInstruction" style="font-size: 11px; color: var(--text-dim); display: block; margin-bottom: 12px;">
                                Scan via eSewa App & Upload Screenshot
                            </span>

                            <div class="form-group" style="margin-bottom: 0; text-align: left;">
                                <label class="form-label" style="font-size: 12px;">Upload Payment Screenshot <span style="color: #fb7185;">*</span></label>
                                <input type="file" name="payment_screenshot" id="payment_screenshot_input" class="form-control" accept="image/*" style="font-size: 12px; padding: 8px; background: rgba(20, 27, 45, 0.9);">
                            </div>
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
const basePrice = <?= floatval($movie['price']) ?>;
const vipExtraFee = <?= floatval($vip_extra_fee) ?>;
const prevBookedCount = <?= intval($prev_booked_seats_count) ?>;
let selectedSeats = [];

function switchPaymentGateway(gateway) {
    const qrImg = document.getElementById('qrCodeImg');
    const instruction = document.getElementById('qrInstruction');
    const methodInput = document.getElementById('payment_method_input');
    const btnEsewa = document.getElementById('btnEsewaTab');
    const btnBank = document.getElementById('btnBankTab');

    methodInput.value = gateway;

    if (gateway === 'eSewa') {
        qrImg.src = 'uploads/payments/esewa_qr.png';
        instruction.innerText = 'Scan via eSewa App & Upload Screenshot';
        btnEsewa.classList.add('active');
        btnBank.classList.remove('active');
    } else {
        qrImg.src = 'uploads/payments/bank_qr.png';
        instruction.innerText = 'Scan via Khalti/Bank App & Upload Screenshot';
        btnBank.classList.add('active');
        btnEsewa.classList.remove('active');
    }
}

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
    const discountNoticeContainer = document.getElementById('discountNoticeContainer');
    const discountText = document.getElementById('discountText');
    const paymentBoxContainer = document.getElementById('paymentBoxContainer');
    const screenshotInput = document.getElementById('payment_screenshot_input');

    if (selectedSeats.length === 0) {
        seatsInput.value = '';
        seatsDisplay.innerText = 'None Selected';
        totalDisplay.innerText = 'Rs. 0.00';
        discountNoticeContainer.style.display = 'none';
        paymentBoxContainer.style.display = 'block';
        screenshotInput.required = true;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fa-solid fa-lock"></i> Select Seats to Proceed';
        btnSubmit.classList.remove('btn-accent');
        btnSubmit.classList.add('btn-outline');
    } else {
        const sorted = [...selectedSeats].sort();
        const joined = sorted.join(', ');
        const totalCount = selectedSeats.length;
        
        let seatPrices = [];
        selectedSeats.forEach(seat => {
            let price = basePrice;
            if (seat.startsWith('D') || seat.startsWith('E')) {
                price += vipExtraFee;
            }
            seatPrices.push(price);
        });

        let subtotal = seatPrices.reduce((a, b) => a + b, 0);
        let finalTotal = subtotal;
        let noticeMsg = "";

        // नियम अनुसार छुट्टाछुट्टै अफर लगाउने (दुवै एकैचोटि नमिसाउने)
        if (prevBookedCount >= 100) {
            seatPrices.fill(0);
            finalTotal = 0;
            noticeMsg = "🎉 100+ Milestone: FREE Booking + Membership & Voucher Active!";
            discountNoticeContainer.style.display = 'block';
            discountText.innerText = noticeMsg;
        } else if (prevBookedCount >= 10 && seatPrices.length > 0) {
            seatPrices.sort((a, b) => b - a);
            seatPrices[0] = 0; // एउटा मात्र सिट फ्री, बाँकीको फुल प्राइस
            finalTotal = seatPrices.reduce((a, b) => a + b, 0);
            
            noticeMsg = "VIP Loyalty: 1 Seat FREE! Remaining seats at full price.";
            discountNoticeContainer.style.display = 'block';
            discountText.innerText = noticeMsg;
        } else if (prevBookedCount >= 5 && prevBookedCount < 10) {
            finalTotal = subtotal - (subtotal * 0.30); // ३०% छुट मात्र (फ्री सिट होइन)
            noticeMsg = "Member Loyalty: 30% Discount applied!";
            discountNoticeContainer.style.display = 'block';
            discountText.innerText = noticeMsg;
        } else {
            discountNoticeContainer.style.display = 'none';
        }

        const totalStr = finalTotal.toFixed(2);

        seatsInput.value = joined;
        seatsDisplay.innerText = `${joined} (${totalCount})`;
        totalDisplay.innerText = `Rs. ${totalStr}`;
        btnSubmit.disabled = false;

        if (finalTotal <= 0) {
            paymentBoxContainer.style.display = 'none';
            screenshotInput.required = false;
            btnSubmit.innerHTML = `<i class="fa-solid fa-circle-check"></i> Confirm Free Booking`;
        } else {
            paymentBoxContainer.style.display = 'block';
            screenshotInput.required = true;
            btnSubmit.innerHTML = `<i class="fa-solid fa-circle-check"></i> Upload SS & Confirm Rs. ${totalStr}`;
        }

        btnSubmit.classList.remove('btn-outline');
        btnSubmit.classList.add('btn-accent');
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

<?php include 'includes/footer.php'; ?>