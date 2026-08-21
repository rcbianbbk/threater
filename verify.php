<?php
<<<<<<< HEAD
session_start();
require_once 'db.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        header("Location: view_bookings.php");
        exit();
=======
$page_title = "Verify OTP";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = "";
$err = "";

if (isset($_POST['verify'])) {
    $otp = trim($_POST['otp']);
    $phone = $_SESSION['temp_phone'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ? AND otp = ?");
    $stmt->bind_param("ss", $phone, $otp);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE phone = ?");
        $update->bind_param("s", $phone);
        $update->execute();
        $_SESSION['success_msg'] = "Phone verified successfully! Please sign in.";
        header("Location: user_login.php");
        exit();
    } else {
        $err = "Invalid verification code. Please try again.";
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
    }

    // Database status update
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        header("Location: view_bookings.php?success=1");
    } else {
        echo "Error updating status: " . $conn->error;
    }
    $stmt->close();
} else {
    header("Location: view_bookings.php");
}
<<<<<<< HEAD
exit();
?>
=======
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Phone Code - Cinema World</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="glass-card" style="width: 100%; max-width: 420px; padding: 40px 35px; text-align: center;">
        <div style="width: 60px; height: 60px; border-radius: 16px; background: rgba(6,182,212,0.15); color: #22d3ee; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px;">
            <i class="fa-solid fa-mobile-screen"></i>
        </div>
        
        <h2 style="font-size: 22px; margin-bottom: 6px;">Enter Security Code</h2>
        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">
            We sent a verification code to your phone number.
        </p>

        <div style="background: rgba(99,102,241,0.1); border: 1px dashed rgba(99,102,241,0.3); border-radius: 8px; padding: 10px; font-size: 13px; color: #a5b4fc; margin-bottom: 20px;">
            Demo OTP: <strong><?= $_SESSION['demo_otp'] ?? '1234' ?></strong>
        </div>

        <?php if($err != ""): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="otp" class="form-control" maxlength="6" placeholder="• • • •" required autofocus style="text-align: center; font-size: 24px; letter-spacing: 8px; font-family: monospace;">
            </div>

            <button type="submit" name="verify" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 10px;">
                <i class="fa-solid fa-check"></i> Verify & Continue
            </button>
        </form>

        <div style="margin-top: 20px;">
            <a href="user_login.php" style="color: var(--text-dim); font-size: 13px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>

</body>
</html>
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
