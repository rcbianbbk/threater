<?php
session_start();
include 'db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Using Prepared Statements for security
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verification: Supports both plain-text and hashed passwords
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_user'] = $row['username'];
            
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Invalid username or account not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Admin Login | Cinema World</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.4);
            --accent: #818cf8;
            --bg-dark: #030712;
            --card-bg: rgba(17, 24, 39, 0.75);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            background: var(--bg-dark); 
            display: flex; align-items: center; justify-content: center; 
            min-height: 100vh; color: #f8fafc;
            background: linear-gradient(135deg, rgba(3, 7, 18, 0.85), rgba(15, 23, 42, 0.9)),
                        url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1600&auto=format&fit=crop') center/cover;
            position: relative; overflow: hidden;
        }

        .spotlight {
            position: absolute; width: 500px; height: 500px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter: blur(80px); pointer-events: none; z-index: 0;
        }

        .login-box {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 45px 40px; border-radius: 30px;
            width: 100%; max-width: 440px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8);
            position: relative; z-index: 1;
        }

        .brand-icon {
            width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 18px; display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: #0f172a; margin-bottom: 20px; box-shadow: 0 0 20px var(--primary-glow);
        }

        h2 { font-size: 28px; margin-bottom: 6px; font-weight: 900; letter-spacing: -0.5px; }
        h2 span { color: var(--primary); }
        p { color: #94a3b8; margin-bottom: 30px; font-size: 14px; font-weight: 500; }
        
        .input-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; }
        
        input {
            width: 100%; padding: 14px 16px 14px 48px; background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px;
            color: white; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 20px var(--primary-glow); }
        
        button {
            width: 100%; padding: 15px; background: linear-gradient(135deg, var(--primary), #0284c7);
            color: #0f172a; border: none; border-radius: 14px;
            font-size: 16px; font-weight: 800; cursor: pointer;
            transition: 0.3s; margin-top: 10px; box-shadow: 0 8px 20px var(--primary-glow);
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 12px 25px var(--primary-glow); color: #fff; }
        
        .error {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171; padding: 12px; border-radius: 12px;
            font-size: 13px; font-weight: 600; margin-bottom: 20px; text-align: center;
        }

        .back-home { display: block; text-align: center; margin-top: 25px; color: #94a3b8; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.3s; }
        .back-home:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="spotlight"></div>

    <div class="login-box">
        <div class="brand-icon">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <h2>Admin <span>Portal</span></h2>
        <p>Authenticate securely to manage cinema bookings</p>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
            </div>
            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit"><i class="fa-solid fa-right-to-bracket"></i> Sign In</button>
        </form>

        <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Back to Cinema World</a>
    </div>
</body>
</html>