<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // युजर खोज्ने (तपाईँको युजर टेबलको नाम 'users' वा उपयुक्त नाम राख्नुहोला)
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // पासवर्ड जाँच गर्ने (यदि password hash गरिएको छ भने password_verify प्रयोग हुन्छ)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: index.php");
                exit();
            } else {
                $error = "ግलत पासवर्ड! कृपया फेري प्रयास गर्नुहोस्।";
            }
        } else {
            $error = "यो इमेलबाट कुनै खाता फेला परेन।";
        }
        $stmt->close();
    } else {
        $error = "सबै क्षेत्रहरू भर्नुहोस्।";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Theater App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0c0c0c; color: #fff; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #141414; border: 1px solid rgba(212,175,55,0.3); border-radius: 16px; width: 100%; max-width: 420px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.8); position: relative; }
        .icon-box { width: 65px; height: 65px; background: rgba(212,175,55,0.15); color: #d4af37; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 20px auto; }
        h2 { text-align: center; font-size: 24px; font-weight: 700; letter-spacing: 1px; margin-bottom: 25px; color: #fff; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; color: #aaa; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 14px 16px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 10px; outline: none; font-size: 14px; transition: 0.3s; }
        input:focus { border-color: #d4af37; box-shadow: 0 0 8px rgba(212,175,55,0.2); }
        .btn-submit { width: 100%; background: linear-gradient(135deg, #d4af37, #aa8c2c); color: #000; border: none; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.3s; margin-top: 10px; box-shadow: 0 4px 15px rgba(212,175,55,0.3); }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .error-msg { background: rgba(229,9,20,0.15); border: 1px solid rgba(229,9,20,0.3); color: #ff5252; padding: 10px; border-radius: 8px; font-size: 13px; text-align: center; margin-bottom: 20px; }
        .back-link { text-align: center; margin-top: 20px; font-size: 13px; color: #888; }
        .back-link a { color: #d4af37; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="icon-box">
            <i class="fa-solid fa-user"></i>
        </div>
        <h2>CLIENT LOGIN</h2>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="bibek@gmail.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">Login to Account</button>
        </form>

        <div class="back-link">
            खाता छैन? <a href="register.php">नयाँ रेजिस्टर गर्नुहोस्</a> | <a href="index.php">होमपेजमा फर्कनुहोस्</a>
        </div>
    </div>

</body>
</html>