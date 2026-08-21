<?php
session_start();
include 'db.php';

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
        }
    }
}
?>
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

</body>
</html>