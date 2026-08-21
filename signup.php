<?php
session_start();
include 'db.php';

$error = "";

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "यो Email पहिले नै दर्ता भइसकेको छ!";
    } else {
        // Direct Active user (is_verified = 1)
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $name, $email, $hashed_pass);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Account successfully created! Please Login.";
            header("Location: user_login.php");
            exit();
        } else {
            $error = "Registration failed! Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Sign Up - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0b0f19; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #1e293b; padding: 35px; border-radius: 16px; border: 1px solid #334155; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #38bdf8; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; outline: none; }
        input:focus { border-color: #38bdf8; }
        button { width: 100%; padding: 12px; background: #38bdf8; color: #0f172a; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        button:hover { background: #0284c7; color: white; }
        .error { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .footer-text { text-align: center; margin-top: 20px; font-size: 14px; color: #94a3b8; }
        .footer-text a { color: #38bdf8; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>📝 Create Account</h2>
        <?php if($error != "") echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="user@gmail.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="signup">Sign Up</button>
        </form>
        <div class="footer-text">
            Already have an account? <a href="user_login.php">Login Here</a>
        </div>
    </div>
</body>
</html>