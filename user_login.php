<?php
$page_title = "Customer Login";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$redirect = $_GET['redirect'] ?? 'index.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Invalid password. Please check your credentials.";
        }
    } else {
        $error = "No registered account found with this email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - Cinema World</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px;">

    <div class="glass-card" style="width: 100%; max-width: 440px; padding: 40px 35px;">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <a href="index.php" class="brand-logo" style="justify-content: center; margin-bottom: 12px;">
                <i class="fa-solid fa-film" style="color: #6366f1;"></i>
                <span>CINEMA</span> WORLD
            </a>
            <h2 style="font-size: 24px; margin-bottom: 6px;">Welcome Back</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Sign in to reserve your favourite cinema seats</p>
        </div>

        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fa-regular fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required autofocus>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" style="margin-bottom: 0;"><i class="fa-solid fa-lock"></i> Password</label>
                </div>
                <div style="position: relative;">
                    <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required>
                    <button type="button" onclick="togglePassVisibility('loginPassword', this)" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-accent btn-lg" style="width: 100%; margin-top: 10px;">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In to Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 14px; color: var(--text-muted);">
            Don't have an account yet? <a href="signup.php" style="color: var(--accent); font-weight: 700;">Create Account</a>
        </div>

        <div style="text-align: center; margin-top: 15px;">
            <a href="index.php" style="color: var(--text-dim); font-size: 13px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Movies
            </a>
        </div>
    </div>

    <script>
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
</body>
</html>