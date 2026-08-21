<?php
session_start();
include 'db.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepared Statement बाट User खोज्ने (Phone होइन, Email बाट)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Hashed Password Check गर्ने
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "पासवर्ड मिलेन! कृपया सही पासवर्ड राख्नुहोस्।";
        }
    } else {
        $error = "यो Email दर्ता भएको छैन! पहिले Sign Up गर्नुहोस्।";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0b0f19; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #1e293b; padding: 35px; border-radius: 16px; border: 1px solid #334155; width: 100%; max-width: 380px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #38bdf8; text-align: center; margin-bottom: 20px; font-size: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 6px; }
        input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; outline: none; font-size: 14px; }
        input:focus { border-color: #38bdf8; }
        button { width: 100%; padding: 12px; background: #38bdf8; color: #0f172a; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; font-size: 15px; transition: 0.3s; }
        button:hover { background: #0284c7; color: white; }
        .error { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .success { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .footer-text { text-align: center; margin-top: 20px; font-size: 14px; color: #94a3b8; }
        .footer-text a { color: #38bdf8; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="card">
        <h2>🔑 Customer Login</h2>

        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="success"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
        <?php endif; ?>

        <?php if($error != ""): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>

        <div class="footer-text">
            खाता छैन? <a href="signup.php">Sign Up गर्नुहोस्</a>
        </div>
    </div>

</body>
</html>