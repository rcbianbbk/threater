<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . '/../db.php';

// Fetch current user details if logged in
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

// Helper to determine active navigation item
$current_script = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - Cinema World' : 'Cinema World - Premium Movie Experience' ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome / Phosphor Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if(isset($extra_css)) echo $extra_css; ?>
</head>
<body>

    <!-- Main Navigation Bar -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="brand-logo">
                <i class="fa-solid fa-film" style="color: #6366f1;"></i>
                <span>CINEMA</span> WORLD
            </a>

            <ul class="nav-menu">
                <li>
                    <a href="index.php" class="nav-link <?= ($current_script == 'index.php') ? 'active' : '' ?>">
                        <i class="fa-solid fa-house"></i> Home
                    </a>
                </li>
                <li>
                    <a href="index.php#movies" class="nav-link">
                        <i class="fa-solid fa-clapperboard"></i> Movies
                    </a>
                </li>
                <?php if ($current_user): ?>
                    <li>
                        <a href="my_bookings.php" class="nav-link <?= ($current_script == 'my_bookings.php' || $current_script == 'ticket.php') ? 'active' : '' ?>">
                            <i class="fa-solid fa-ticket"></i> My Tickets
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="user-nav-actions">
                <?php if ($current_user): ?>
                    <!-- Logged in Customer -->
                    <div style="display: flex; align-items: center; gap: 10px; position: relative;">
                        <a href="profile.php" class="avatar-badge" title="<?= htmlspecialchars($current_user['name']) ?>">
                            <?php if(!empty($current_user['profile_image']) && file_exists(__DIR__ . "/../uploads/" . $current_user['profile_image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($current_user['profile_image']) ?>" alt="User Avatar">
                            <?php else: ?>
                                <?= $user_avatar_initial ?>
                            <?php endif; ?>
                        </a>
                        <a href="profile.php" style="font-weight: 600; font-size: 14px; color: #f1f5f9; display: none; @media(min-width:768px){display:block;}">
                            <?= htmlspecialchars(explode(' ', $current_user['name'])[0]) ?>
                        </a>
                        <a href="logout.php" class="btn btn-outline btn-sm" style="color: #fb7185; border-color: rgba(244,63,94,0.3);" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php elseif (isset($_SESSION['admin'])): ?>
                    <!-- Logged in Admin -->
                    <a href="dashboard.php" class="btn btn-accent btn-sm">
                        <i class="fa-solid fa-gauge-high"></i> Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="fa-solid fa-power-off"></i>
                    </a>
                <?php else: ?>
                    <!-- Guest Links -->
                    <a href="user_login.php" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-user"></i> Login
                    </a>
                    <a href="signup.php" class="btn btn-primary btn-sm">
                        Sign Up
                    </a>
                    <a href="login.php" class="btn btn-outline btn-sm" style="border-color: rgba(99,102,241,0.4); color: #818cf8;" title="Admin Login">
                        <i class="fa-solid fa-shield-halved"></i>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
