<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../db.php';

// Error/Success messages for popups
$admin_login_error = "";
$user_login_error = "";
$user_signup_error = "";

// 1. Handle Admin Quick Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['popup_admin_login'])) {
    $username = trim($_POST['admin_username'] ?? '');
    $password = trim($_POST['admin_password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $admin = $res->fetch_assoc();
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                $_SESSION['admin'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'] ?? 1;
                header("Location: dashboard.php");
                exit();
            } else {
                $admin_login_error = "Invalid admin password!";
            }
        } else {
            $admin_login_error = "Admin username not found!";
        }
    }
}

// 2. Handle Client Quick Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['popup_user_login'])) {
    $email = trim($_POST['user_email'] ?? '');
    $password = trim($_POST['user_password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $user_login_error = "Invalid password!";
            }
        } else {
            $user_login_error = "Email not found!";
        }
    }
}

// 3. Handle Client Quick Signup with Location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['popup_user_signup'])) {
    $name = trim($_POST['signup_name'] ?? '');
    $email = trim($_POST['signup_email'] ?? '');
    $location = trim($_POST['signup_location'] ?? '');
    $password = trim($_POST['signup_password'] ?? '');

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $user_signup_error = "Email already registered!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, location, password) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $email, $location, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_name'] = $name;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $user_signup_error = "Registration failed. Try again.";
            }
        }
    }
}

$current_user = null;
$user_avatar_initial = "";

if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $user_res = $conn->query("SELECT * FROM users WHERE id = $uid");
    if ($user_res && $user_res->num_rows > 0) {
        $current_user = $user_res->fetch_assoc();
        $user_avatar_initial = strtoupper(substr(trim($current_user['name']), 0, 1));
    }
}

$current_script = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - Luxury Cinema' : 'VIP Cinema Experience' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if(isset($extra_css)) echo $extra_css; ?>
</head>
<body style="background-color: #050505; color: #e5e5e5; font-family: 'Plus Jakarta Sans', sans-serif; margin: 0;">

    <!-- Luxury VIP Navigation Bar -->
    <header style="background: rgba(7, 7, 7, 0.95); border-bottom: 1px solid rgba(212, 175, 55, 0.2); position: sticky; top: 0; z-index: 1000; backdrop-filter: blur(12px); box-shadow: 0 10px 30px rgba(0,0,0,0.8);">
        <nav class="navbar" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 35px; max-width: 1400px; margin: 0 auto;">
            
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="index.php" class="brand-logo" style="text-decoration: none; font-size: 22px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 10px; font-family: 'Cinzel', serif; letter-spacing: 1px;">
                    <i class="fa-solid fa-crown" style="color: #d4af37; font-size: 20px;"></i>
                    <span>CINE</span><span style="color: #d4af37;">PREMIERE</span>
                </a>
                
                <button type="button" onclick="openModal('adminLoginModal')" title="Admin Portal" style="background: transparent; border: none; color: rgba(212, 175, 55, 0.4); font-size: 13px; cursor: pointer; transition: 0.3s; padding: 4px;" onmouseover="this.style.color='#d4af37'" onmouseout="this.style.color='rgba(212, 175, 55, 0.4)'">
                    <i class="fa-solid fa-shield-halved"></i>
                </button>
            </div>

            <ul class="nav-menu" style="display: flex; gap: 12px; list-style: none; align-items: center; margin: 0; padding: 0;">
                <li>
                    <a href="index.php" style="color: <?= ($current_script == 'index.php') ? '#d4af37' : '#b3b3b3' ?>; background: <?= ($current_script == 'index.php') ? 'rgba(212,175,55,0.1)' : 'transparent' ?>; border: <?= ($current_script == 'index.php') ? '1px solid rgba(212,175,55,0.4)' : '1px solid transparent' ?>; padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 7px; transition: 0.3s;">
                        <i class="fa-solid fa-house" style="color: #d4af37;"></i> Home
                    </a>
                </li>
                <li>
                    <a href="index.php#movies" style="color: #b3b3b3; padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 7px; transition: 0.3s;">
                        <i class="fa-solid fa-clapperboard"></i> Movies
                    </a>
                </li>
                <?php if ($current_user): ?>
                    <li>
                        <a href="my_bookings.php" style="color: <?= ($current_script == 'my_bookings.php' || $current_script == 'ticket.php') ? '#d4af37' : '#b3b3b3' ?>; background: <?= ($current_script == 'my_bookings.php' || $current_script == 'ticket.php') ? 'rgba(212,175,55,0.1)' : 'transparent' ?>; border: <?= ($current_script == 'my_bookings.php' || $current_script == 'ticket.php') ? '1px solid rgba(212,175,55,0.4)' : '1px solid transparent' ?>; padding: 8px 18px; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 7px; transition: 0.3s;">
                            <i class="fa-solid fa-ticket"></i> View Bookings
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="user-nav-actions" style="display: flex; align-items: center; gap: 14px;">
                <?php if ($current_user): ?>
                    <div style="display: flex; align-items: center; gap: 12px; position: relative;">
                        <a href="profile.php" title="<?= htmlspecialchars($current_user['name']) ?>" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; display: flex; align-items: center; justify-content: center; font-weight: 700; text-decoration: none; overflow: hidden; border: 2px solid rgba(212,175,55,0.5); box-shadow: 0 0 10px rgba(212,175,55,0.3);">
                            <?php if(!empty($current_user['profile_image']) && file_exists(__DIR__ . "/../uploads/" . $current_user['profile_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($current_user['profile_image']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <?= $user_avatar_initial ?>
                            <?php endif; ?>
                        </a>
                        <a href="logout.php" style="color: #ff5252; border: 1px solid rgba(255,82,82,0.3); background: rgba(255,82,82,0.05); padding: 7px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; transition: 0.3s;" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php elseif (isset($_SESSION['admin'])): ?>
                    <a href="dashboard.php" style="background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700;">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                    <a href="logout.php" style="background: #1a1a1a; color: #ff5252; border: 1px solid rgba(255,82,82,0.3); padding: 8px 12px; border-radius: 8px; text-decoration: none;">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                <?php else: ?>
                    <button type="button" onclick="openModal('userLoginModal')" style="color: #fff; background: transparent; border: 1px solid rgba(255,255,255,0.15); padding: 8px 18px; border-radius: 30px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.3s;">
                        <i class="fa-solid fa-user" style="color: #d4af37;"></i> Login
                    </button>
                    <button type="button" onclick="openModal('userSignupModal')" style="background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; border: none; padding: 8px 20px; border-radius: 30px; font-size: 13px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 15px rgba(212,175,55,0.35); transition: 0.3s;">
                        VIP Register
                    </button>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- 1. Admin Login Modal -->
    <div id="adminLoginModal" style="display: <?= !empty($admin_login_error) ? 'flex' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="width: 100%; max-width: 400px; background: #121212; border: 1px solid rgba(212,175,55,0.4); border-radius: 14px; padding: 30px; position: relative;">
            <button type="button" onclick="closeModal('adminLoginModal')" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: #aaa; font-size: 18px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            <div style="text-align: center; margin-bottom: 24px;">
                <i class="fa-solid fa-shield-halved" style="font-size: 36px; color: #d4af37; margin-bottom: 10px;"></i>
                <h3 style="font-family: 'Cinzel', serif; color: #fff; margin: 0; font-size: 20px;">Admin Portal</h3>
            </div>
            <?php if(!empty($admin_login_error)): ?>
                <div style="background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: #ff5252; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center;"><?= htmlspecialchars($admin_login_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="popup_admin_login" value="1">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 6px;">Username</label>
                    <input type="text" name="admin_username" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 6px;">Password</label>
                    <input type="password" name="admin_password" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer;">Authenticate</button>
            </form>
        </div>
    </div>

    <!-- 2. Client Login Modal -->
    <div id="userLoginModal" style="display: <?= !empty($user_login_error) ? 'flex' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="width: 100%; max-width: 400px; background: #121212; border: 1px solid rgba(212,175,55,0.4); border-radius: 14px; padding: 30px; position: relative;">
            <button type="button" onclick="closeModal('userLoginModal')" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: #aaa; font-size: 18px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            <div style="text-align: center; margin-bottom: 24px;">
                <i class="fa-solid fa-user-circle" style="font-size: 36px; color: #d4af37; margin-bottom: 10px;"></i>
                <h3 style="font-family: 'Cinzel', serif; color: #fff; margin: 0; font-size: 20px;">Client Login</h3>
            </div>
            <?php if(!empty($user_login_error)): ?>
                <div style="background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: #ff5252; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center;"><?= htmlspecialchars($user_login_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="popup_user_login" value="1">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 6px;">Email Address</label>
                    <input type="email" name="user_email" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 6px;">Password</label>
                    <input type="password" name="user_password" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer;">Login</button>
            </form>
        </div>
    </div>

    <!-- 3. Client Signup Modal with Location -->
    <div id="userSignupModal" style="display: <?= !empty($user_signup_error) ? 'flex' : 'none' ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="width: 100%; max-width: 400px; background: #121212; border: 1px solid rgba(212,175,55,0.4); border-radius: 14px; padding: 30px; position: relative;">
            <button type="button" onclick="closeModal('userSignupModal')" style="position: absolute; top: 15px; right: 15px; background: transparent; border: none; color: #aaa; font-size: 18px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            <div style="text-align: center; margin-bottom: 24px;">
                <i class="fa-solid fa-user-plus" style="font-size: 36px; color: #d4af37; margin-bottom: 10px;"></i>
                <h3 style="font-family: 'Cinzel', serif; color: #fff; margin: 0; font-size: 20px;">VIP Registration</h3>
            </div>
            <?php if(!empty($user_signup_error)): ?>
                <div style="background: rgba(255,82,82,0.1); border: 1px solid rgba(255,82,82,0.3); color: #ff5252; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center;"><?= htmlspecialchars($user_signup_error) ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="popup_user_signup" value="1">
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 4px;">Full Name</label>
                    <input type="text" name="signup_name" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 4px;">Email Address</label>
                    <input type="email" name="signup_email" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-size: 12px; color: #bbb;">Location / City</label>
                        <button type="button" onclick="detectLocation()" style="background: none; border: none; color: #d4af37; font-size: 11px; cursor: pointer; text-decoration: underline;"><i class="fa-solid fa-location-crosshairs"></i> Detect My Location</button>
                    </div>
                    <input type="text" id="signup_location" name="signup_location" required placeholder="Detecting or enter location" style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; color: #bbb; margin-bottom: 4px;">Password</label>
                    <input type="password" name="signup_password" required style="width: 100%; padding: 10px 14px; background: #1a1a1a; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; box-sizing: border-box;">
                </div>
                <button type="submit" style="width: 100%; background: linear-gradient(135deg, #d4af37, #aa771c); color: #000; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer;">Register</button>
            </form>
        </div>
    </div>

    <script>
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // पेज रिफ्रेस वा ओपेन हुँदा सधैं नयाँ लोकेशन माग्न र फेच गर्न (maximumAge: 0 प्रयोग गरिएको)
    window.addEventListener('DOMContentLoaded', (event) => {
        const locationInput = document.getElementById('signup_location');
        if (locationInput && navigator.geolocation) {
            locationInput.value = "Requesting location permission...";
            navigator.geolocation.getCurrentPosition(
                async function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                        const data = await response.json();
                        const address = data.address;
                        const city = address.city || address.town || address.village || address.state || "Unknown City";
                        locationInput.value = city;
                    } catch (error) {
                        locationInput.value = `Lat: ${lat.toFixed(2)}, Lon: ${lon.toFixed(2)}`;
                    }
                },
                function(error) {
                    locationInput.value = ""; // पर्मिसन नदिएमा वा ब्लक भएमा खाली रहनेछ
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 15000, 
                    maximumAge: 0 // यसले ब्राउजरलाई पुरानो डाटा प्रयोग गर्न दिँदैन र रिफ्रेस गर्दा हरेл पटक नयाँ पपअप माग्छ
                }
            );
        }
    });

    // बटन क्लिक गरेर फेरि फेच गर्नुपरेमा
    function detectLocation() {
        const locationInput = document.getElementById('signup_location');
        if (navigator.geolocation) {
            locationInput.value = "Detecting location...";
            navigator.geolocation.getCurrentPosition(
                async function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                        const data = await response.json();
                        const address = data.address;
                        const city = address.city || address.town || address.village || address.state || "Unknown City";
                        locationInput.value = city;
                    } catch (error) {
                        locationInput.value = `Lat: ${lat.toFixed(2)}, Lon: ${lon.toFixed(2)}`;
                    }
                },
                function(error) {
                    alert("Location access denied or unavailable. Please enter manually.");
                    locationInput.value = "";
                },
                { 
                    enableHighAccuracy: true, 
                    timeout: 15000, 
                    maximumAge: 0 
                }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    }
    </script>
</body>
</html>