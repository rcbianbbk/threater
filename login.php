<?php
session_start();
if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0b0f19; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: #1e293b; padding: 40px; border-radius: 16px; border: 1px solid #334155; width: 100%; max-width: 380px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .box h2 { text-align: center; color: #38bdf8; margin-bottom: 8px; }
        .box p.sub { text-align: center; color: #64748b; font-size: 14px; margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; color: #94a3b8; margin-bottom: 6px; }
        input { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: white; outline: none; }
        input:focus { border-color: #38bdf8; }
        button { width: 100%; padding: 12px; background: #38bdf8; color: #0f172a; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        button:hover { background: #0284c7; color: white; }
        .error { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Admin Portal</h2>
        <p class="sub">Enter credentials to manage theater</p>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="admin" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" name="login">Login to Dashboard</button>
        </form>
    </div>
</body>
</html>