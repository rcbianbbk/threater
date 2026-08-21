<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: user_login.php"); 
    exit(); 
}

$movie_id = intval($_GET['movie_id'] ?? 0);
$movie = $conn->query("SELECT * FROM movies WHERE id = $movie_id")->fetch_assoc();

// Booked Seats
$booked_seats = [];
$res = $conn->query("SELECT seat_number FROM bookings WHERE movie_id = $movie_id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $booked_seats[] = $row['seat_number'];
    }
}

$msg = "";
$err = "";

if (isset($_POST['book'])) {
    $selected_seat = trim($_POST['seat']);
    $payment_method = $_POST['payment_method'] ?? 'QR Code';
    $txn_id = trim($_POST['transaction_id']);

    if (empty($selected_seat)) {
        $err = "कृपया पहिले सिट रोज्नुहोस्!";
    } elseif (empty($txn_id)) {
        $err = "कृपया भुक्तानी गरिसकेपछि Transaction ID / Ref ID राख्नुहोस्!";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, 'Success', ?)");
        $stmt->bind_param("iisss", $_SESSION['user_id'], $movie_id, $selected_seat, $payment_method, $txn_id);
        
        if ($stmt->execute()) {
            $msg = "🎉 Payment Verified & Ticket Booked Successfully! (Txn ID: " . htmlspecialchars($txn_id) . ")";
            $booked_seats[] = $selected_seat;
        } else {
            $err = "Booking गर्न सकिएन! पुनः प्रयास गर्नुहोस्।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <title>Book & Pay - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #0b0f19; color: #f8fafc; min-height: 100vh; padding: 30px 20px; display: flex; justify-content: center; align-items: center; }
        .booking-container { background: #1e293b; border-radius: 16px; border: 1px solid #334155; padding: 30px; max-width: 500px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.5); text-align: center; }
        h2 { color: #38bdf8; margin-bottom: 5px; }
        .movie-info { color: #94a3b8; font-size: 15px; margin-bottom: 20px; }
        
        .screen { background: linear-gradient(180deg, #38bdf8, transparent); height: 8px; width: 80%; margin: 0 auto 20px; border-radius: 50%; box-shadow: 0 10px 20px rgba(56, 189, 248, 0.4); }
        .screen-text { font-size: 11px; color: #64748b; letter-spacing: 2px; margin-bottom: 15px; }

        .seats-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; max-width: 360px; margin: 0 auto 20px; }
        .seat { background: #0f172a; border: 1px solid #334155; color: #f8fafc; padding: 10px 0; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 14px; }
        .seat:hover:not(.booked) { background: #38bdf8; color: #0f172a; }
        .seat.selected { background: #22c55e !important; color: white !important; border-color: #22c55e !important; }
        .seat.booked { background: #ef4444; color: white; border-color: #ef4444; cursor: not-allowed; opacity: 0.5; }

        /* Payment & Transaction Input Box */
        .payment-box { background: #0f172a; border: 1px solid #334155; border-radius: 10px; padding: 15px; margin: 20px 0; }
        .qr-img { width: 140px; height: 140px; border-radius: 8px; border: 2px solid #38bdf8; margin: 10px auto; display: block; background: white; padding: 5px; }
        
        .form-group { margin-top: 15px; text-align: left; }
        .form-group label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; background: #1e293b; border: 1px solid #334155; border-radius: 6px; color: white; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #38bdf8; }

        .btn-confirm { width: 100%; padding: 14px; background: #22c55e; color: white; border: none; border-radius: 8px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-confirm:hover { background: #16a34a; }
        
        .msg { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .err { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .back-link { display: block; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="booking-container">
    <h2>🎟 Select Seat & Pay</h2>
    <?php if($movie): ?>
        <p class="movie-info"><?= htmlspecialchars($movie['title']) ?> | <strong>Rs. <?= number_format($movie['price'], 2) ?></strong></p>
    <?php endif; ?>

    <?php if($msg != "") echo "<div class='msg'>$msg</div>"; ?>
    <?php if($err != "") echo "<div class='err'>$err</div>"; ?>

    <div class="screen"></div>
    <p class="screen-text">SCREEN THIS WAY</p>

    <!-- Seats Grid -->
    <div class="seats-grid">
        <?php 
        $rows = ['A', 'B', 'C', 'D'];
        for ($r = 0; $r < count($rows); $r++) {
            for ($s = 1; $s <= 6; $s++) {
                $seat_id = $rows[$r] . $s;
                $is_booked = in_array($seat_id, $booked_seats);
                $class = $is_booked ? 'seat booked' : 'seat';
                echo "<div class='$class' data-seat='$seat_id'>$seat_id</div>";
            }
        }
        ?>
    </div>

    <form method="POST">
        <input type="hidden" name="seat" id="selected_seat_input" required>
        
        <!-- QR & Payment Transaction Entry -->
        <div class="payment-box">
            <p style="color:#38bdf8; font-weight:bold;">📲 Scan QR to Pay (eSewa/Khalti)</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=CinemaWorld_Pay_Rs<?= $movie['price'] ?? 0 ?>" class="qr-img" alt="Payment QR Code">
            
            <div class="form-group">
                <label>Transaction ID / Ref Code (eSewa/Khalti):</label>
                <input type="text" name="transaction_id" placeholder="Ex: TXN12345678" required>
            </div>
            <input type="hidden" name="payment_method" value="QR Code">
        </div>

        <button type="submit" name="book" class="btn-confirm">✅ Submit & Verify Payment</button>
    </form>

    <a href="index.php" class="back-link">⬅ Back to Home</a>
</div>

<script>
    const seats = document.querySelectorAll('.seat:not(.booked)');
    const seatInput = document.getElementById('selected_seat_input');

    seats.forEach(seat => {
        seat.addEventListener('click', () => {
            seats.forEach(s => s.classList.remove('selected'));
            seat.classList.add('selected');
            seatInput.value = seat.getAttribute('data-seat');
        });
    });
</script>

</body>
</html>