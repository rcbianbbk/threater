<?php
session_start();
include 'db.php';

$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");

// Current user details fetch for profile image
$user_data = null;
$user_initial = "";

if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $u_query = $conn->query("SELECT * FROM users WHERE id = $u_id");
    if ($u_query) {
        $user_data = $u_query->fetch_assoc();
        $user_initial = strtoupper(substr(trim($user_data['name']), 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema World - Home</title>
    <!-- html2canvas Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0b0f19; color: #f3f4f6; min-height: 100vh; padding: 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); padding: 15px 35px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); }
        .logo { font-size: 24px; font-weight: 800; color: #38bdf8; text-transform: uppercase; }
        .user-profile { display: flex; align-items: center; gap: 12px; }
        
        /* Profile Image / Initial Avatar Style */
        .avatar-box { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #38bdf8; overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #38bdf8, #0284c7); color: #0f172a; font-weight: 800; font-size: 20px; text-decoration: none; box-shadow: 0 0 10px rgba(56, 189, 248, 0.5); }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .user-name-text { color: #f8fafc; font-weight: 600; font-size: 15px; text-decoration: none; }
        .user-name-text:hover { color: #38bdf8; }
        
        .nav-links a { color: #94a3b8; text-decoration: none; font-weight: 600; padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .nav-links a:hover { color: #38bdf8; background: rgba(56, 189, 248, 0.1); }
        .btn-logout { background: rgba(248, 113, 113, 0.1); color: #f87171 !important; border: 1px solid #f87171; }
        
        .hero { text-align: center; margin: 40px 0 20px; }
        .hero h1 { font-size: 36px; color: #f8fafc; margin-bottom: 8px; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; max-width: 1200px; margin: 30px auto; }
        .card { background: #1e293b; border-radius: 14px; padding: 22px; border: 1px solid #334155; }
        .btn-book { display: block; width: 100%; text-align: center; background: #38bdf8; color: #0f172a; font-weight: 700; padding: 12px; border-radius: 8px; text-decoration: none; margin-top: 15px; border: none; cursor: pointer; }
        .btn-book:hover { background: #0284c7; color: #fff; }
    </style>
</head>
<body>

    <div class="nav">
        <div class="logo">🎬 Cinema World</div>
        
        <div class="user-profile">
            <?php if($user_data): ?>
                <a href="profile.php" class="avatar-box">
                    <?php if(!empty($user_data['profile_image']) && file_exists("uploads/" . $user_data['profile_image'])): ?>
                        <img src="uploads/<?= $user_data['profile_image'] ?>" alt="Profile">
                    <?php else: ?>
                        <?= $user_initial ?>
                    <?php endif; ?>
                </a>
                <a href="profile.php" class="user-name-text"><?= htmlspecialchars($user_data['name']) ?></a>
                <div class="nav-links">
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            <?php else: ?>
                <div class="nav-links">
                    <a href="user_login.php">User Login</a>
                    <a href="signup.php">Sign Up</a>
                    <a href="login.php" style="border:1px solid #38bdf8; color:#38bdf8;">Admin Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <h1>Now Showing Movies</h1>
        <p>Select a movie to reserve your seats</p>
    </div>

    <div class="grid">
        <?php while($m = $movies->fetch_assoc()): ?>
            <div class="card" id="card-<?= $m['id'] ?>">
                <h3 style="color:#38bdf8; margin-bottom:8px;"><?= htmlspecialchars($m['title']) ?></h3>
                <p style="color:#94a3b8; font-size:14px;">Genre: <?= htmlspecialchars($m['genre']) ?></p>
                <p style="font-size:22px; font-weight:bold; margin:10px 0;">Rs. <?= number_format($m['price'], 2) ?></p>
                
                <!-- Modified Button with JavaScript trigger -->
                <button onclick="captureAndBook(<?= $m['id'] ?>)" class="btn-book">🎟 Book Seat</button>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Hidden Form to send Screenshot & Movie ID to book.php -->
    <form id="screenshotForm" action="book.php" method="POST" style="display:none;">
        <input type="hidden" name="movie_id" id="form_movie_id">
        <input type="hidden" name="screenshot_data" id="form_screenshot_data">
    </form>

    <script>
    function captureAndBook(movieId) {
        // Screen ya Specific Card ko screenshot line
        let targetElement = document.getElementById('card-' + movieId);
        
        html2canvas(targetElement).then(canvas => {
            let imageData = canvas.toDataURL('image/png'); // Base64 Data
            
            // Hidden Form ma values set garne
            document.getElementById('form_movie_id').value = movieId;
            document.getElementById('form_screenshot_data').value = imageData;
            
            // Directly book.php ma POST submit garne
            document.getElementById('screenshotForm').submit();
        });
    }
    </script>

</body>
</html>