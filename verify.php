<?php
session_start();
include 'db.php';
$msg = "";

if (isset($_POST['verify'])) {
    $otp = $_POST['otp'];
    $phone = $_SESSION['temp_phone'] ?? '';

    $res = $conn->query("SELECT * FROM users WHERE phone='$phone' AND otp='$otp'");
    if ($res && $res->num_rows > 0) {
        $conn->query("UPDATE users SET is_verified=1 WHERE phone='$phone'");
        header("Location: user_login.php?verified=1");
        exit();
    } else {
        $msg = "Invalid OTP code. Try again!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body { background:#ffffff; font-family:sans-serif; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
        .box { width:100%; max-width:360px; text-align:center; padding:20px; }
        .input-field { width:100%; height:50px; text-align:center; font-size:20px; border:1px solid #000; border-radius:12px; margin:15px 0; outline:none; }
        .btn { width:100%; height:48px; background:#0064e0; color:#fff; border:none; border-radius:24px; font-size:16px; font-weight:600; cursor:pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Enter Verification Code</h2>
        <p style="color:#0064e0; font-weight:bold;">Demo OTP: <?php echo $_SESSION['demo_otp'] ?? '1234'; ?></p>
        <?php if($msg) echo "<p style='color:red;'>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="otp" class="input-field" maxlength="4" placeholder="4-digit OTP" required>
            <button type="submit" name="verify" class="btn">Verify & Continue</button>
        </form>
    </div>
</body>
</html>