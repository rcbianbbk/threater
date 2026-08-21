<?php
$page_title = "Admin Login";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Admin authentication
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid administrator credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Cinema World</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="glass-card" style="width: 100%; max-width: 420px; padding: 40px 35px; border-color: rgba(99,102,241,0.3); box-shadow: 0 0 35px rgba(99,102,241,0.2);">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 60px; height: 60px; border-radius: 16px; background: rgba(99,102,241,0.15); color: #818cf8; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 style="font-size: 24px; margin-bottom: 4px;">Admin Portal</h2>
            <p style="color: var(--text-muted); font-size: 13px;">Authorized personnel cinema management</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-user-shield"></i> Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-key"></i> Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div style="background: rgba(99,102,241,0.1); border: 1px dashed rgba(99,102,241,0.3); border-radius: 8px; padding: 10px; font-size: 12px; color: #a5b4fc; margin-bottom: 18px;">
                <i class="fa-solid fa-circle-info"></i> Demo credentials: <strong>admin</strong> / <strong>admin123</strong>
            </div>

            <button type="submit" name="login" class="btn btn-accent btn-lg" style="width: 100%;">
                <i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: var(--text-dim); font-size: 13px;">
                <i class="fa-solid fa-arrow-left"></i> Return to Main Website
            </a>
        </div>
    </div>

</body>
</html>