<?php
$page_title = "Create Customer Account";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

if (isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'];

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $error = "This email address is already registered. Please sign in.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, is_verified) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed_pass);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Account successfully registered! Please sign in.";
            header("Location: user_login.php");
            exit();
        } else {
            $error = "Registration failed! Error: " . htmlspecialchars($conn->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Cinema World</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="glass-card" style="width: 100%; max-width: 460px; padding: 40px 35px;">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <a href="index.php" class="brand-logo" style="justify-content: center; margin-bottom: 12px;">
                <i class="fa-solid fa-film" style="color: #6366f1;"></i>
                <span>CINEMA</span> WORLD
            </a>
            <h2 style="font-size: 24px; margin-bottom: 6px;">Create Account</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Join Cinema World for instant tickets & exclusive seats</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fa-regular fa-user"></i> Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Alex Johnson" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-regular fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="alex@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-phone"></i> Phone Number (Optional)</label>
                <input type="text" name="phone" class="form-control" placeholder="+977 98XXXXXXXX">
            </div>

            <div class="form-group">
                <label class="form-label"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <button type="submit" name="signup" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
                <i class="fa-solid fa-user-plus"></i> Create Customer Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 14px; color: var(--text-muted);">
            Already have an account? <a href="user_login.php" style="color: var(--accent); font-weight: 700;">Sign In</a>
        </div>

        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: var(--text-dim); font-size: 13px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Movies
            </a>
        </div>
    </div>

</body>
</html>