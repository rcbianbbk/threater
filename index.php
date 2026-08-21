<?php
session_start();
include 'db.php';

$movies = $conn->query("SELECT * FROM movies ORDER BY id DESC");

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
    <title>Cinema World - Ultimate Movie Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #38bdf8;
            --primary-glow: rgba(56, 189, 248, 0.4);
            --accent: #818cf8;
            --accent-glow: rgba(129, 140, 248, 0.3);
            --bg-dark: #030712;
            --card-bg: rgba(17, 24, 39, 0.6);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        body { 
            background: var(--bg-dark); 
            color: #f8fafc; 
            min-height: 100vh; 
            padding-bottom: 60px;
            overflow-x: hidden;
        }

        .spotlight-1 {
            position: fixed; top: -150px; left: -100px; width: 600px; height: 600px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            filter: blur(100px); pointer-events: none; z-index: -1; animation: pulse 8s infinite alternate;
        }
        .spotlight-2 {
            position: fixed; bottom: -150px; right: -100px; width: 600px; height: 600px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
            filter: blur(100px); pointer-events: none; z-index: -1; animation: pulse 6s infinite alternate-reverse;
        }

        @keyframes pulse {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.2) translate(30px, 30px); }
        }

        .nav-container { position: sticky; top: 20px; z-index: 100; max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        .nav { 
            display: flex; justify-content: space-between; align-items: center; 
            background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 16px 32px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        }

        .logo { font-size: 28px; font-weight: 900; letter-spacing: -1px; text-decoration: none; color: #fff; display: flex; align-items: center; gap: 10px; }
        .logo-badge { background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .user-profile { display: flex; align-items: center; gap: 16px; }
        .avatar-box { 
            width: 44px; height: 44px; border-radius: 14px; border: 2px solid var(--primary); 
            overflow: hidden; display: flex; align-items: center; justify-content: center; 
            background: linear-gradient(135deg, var(--primary), #0284c7); color: #0f172a; font-weight: 800; 
            font-size: 18px; text-decoration: none; box-shadow: 0 0 15px var(--primary-glow); transition: 0.3s;
        }
        .avatar-box:hover { transform: scale(1.1) rotate(5deg); border-color: var(--accent); }
        .avatar-box img { width: 100%; height: 100%; object-fit: cover; }
        .user-name-text { color: #f8fafc; font-weight: 700; font-size: 15px; text-decoration: none; }
        .btn-logout { background: rgba(248, 113, 113, 0.15) !important; color: #f87171 !important; border: 1px solid rgba(248, 113, 113, 0.3); padding: 8px 16px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13px; }
        .btn-logout:hover { background: #ef4444 !important; color: #fff !important; }

        .hero-banner { max-width: 1400px; margin: 30px auto 40px; padding: 0 20px; }
        .hero-card {
            height: 380px; border-radius: 30px;
            background: linear-gradient(180deg, rgba(3, 7, 18, 0.1) 0%, rgba(3, 7, 18, 0.95) 100%),
                        linear-gradient(90deg, rgba(3, 7, 18, 0.9) 30%, transparent 80%),
                        url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1600&auto=format&fit=crop') center/cover;
            border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: flex-end; padding: 50px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8); position: relative; overflow: hidden;
        }
        .hero-tag { background: var(--primary); color: #0f172a; font-size: 12px; font-weight: 900; padding: 6px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 12px; box-shadow: 0 0 15px var(--primary-glow); }
        .hero-title { font-size: 48px; font-weight: 900; letter-spacing: -1px; line-height: 1.1; margin-bottom: 12px; }
        .hero-desc { color: #94a3b8; font-size: 16px; max-width: 550px; }

        .section-header { max-width: 1400px; margin: 0 auto 30px; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
        .search-box { position: relative; min-width: 320px; }
        .search-box i { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: #64748b; }
        .search-box input {
            width: 100%; padding: 14px 20px 14px 48px; background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; color: #fff; font-size: 14px; font-weight: 600; outline: none; transition: 0.3s;
        }
        .search-box input:focus { border-color: var(--primary); box-shadow: 0 0 20px var(--primary-glow); }

        .grid { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 32px; }
        
        .movie-card {
            background: var(--card-bg); border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden; backdrop-filter: blur(12px); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; flex-direction: column; position: relative;
        }
        .movie-card:hover {
            transform: translateY(-12px) scale(1.02); border-color: var(--primary);
            box-shadow: 0 25px 50px -12px var(--primary-glow);
        }

        .poster-container { position: relative; width: 100%; height: 340px; overflow: hidden; background: #0f172a; }
        .poster-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .movie-card:hover .poster-img { transform: scale(1.1) rotate(1deg); }

        .genre-badge {
            position: absolute; top: 14px; left: 14px; background: rgba(3, 7, 18, 0.8);
            backdrop-filter: blur(8px); color: var(--primary); font-size: 11px; font-weight: 800;
            padding: 5px 12px; border-radius: 10px; border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .card-body { padding: 22px; display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1; }
        .movie-title { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 8px; line-height: 1.3; }
        .price-tag { font-size: 22px; font-weight: 900; color: #fff; margin: 12px 0 18px; display: flex; align-items: center; gap: 5px; }
        .price-tag span { font-size: 14px; color: #94a3b8; font-weight: 600; }

        .btn-book {
            width: 100%; background: linear-gradient(135deg, var(--primary), #0284c7);
            color: #0f172a; font-weight: 800; font-size: 15px; padding: 14px; border-radius: 14px;
            border: none; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px;
            box-shadow: 0 8px 20px var(--primary-glow);
        }
        .btn-book:hover { transform: scale(1.03); box-shadow: 0 12px 28px var(--primary-glow); color: #fff; background: linear-gradient(135deg, #0284c7, var(--primary)); }
    </style>
</head>
<body>

    <div class="spotlight-1"></div>
    <div class="spotlight-2"></div>

    <div class="nav-container">
        <div class="nav">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-film" style="color: var(--primary);"></i> CINEMA <span class="logo-badge">WORLD</span>
            </a>
            
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
                    <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i></a>
                <?php else: ?>
                    <div style="display:flex; gap:12px; align-items:center;">
                        <a href="admin_login.php" style="color:#94a3b8; text-decoration:none; font-weight:700; font-size:14px; padding:10px 14px; display:flex; align-items:center; gap:6px; transition:0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">
                            <i class="fa-solid fa-user-shield" style="color:var(--primary);"></i> Admin
                        </a>
                        <a href="user_login.php" style="color:#fff; text-decoration:none; font-weight:700; padding:10px 18px;">Sign In</a>
                        <a href="signup.php" style="background:var(--primary); color:#0f172a; text-decoration:none; font-weight:800; padding:10px 20px; border-radius:12px; box-shadow:0 0 15px var(--primary-glow);">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="hero-banner">
        <div class="hero-card">
            <div>
                <span class="hero-tag"><i class="fa-solid fa-fire"></i> Cinema World Specials</span>
                <h1 class="hero-title">Watch Next-Gen Movies<br>In Ultra HD Quality</h1>
                <p class="hero-desc">Reserve premium cinema seats instantly with screenshot-verified automated billing.</p>
            </div>
        </div>
    </div>

    <div class="section-header">
        <h2 style="font-size: 26px; font-weight: 900;">Now Showing</h2>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" onkeyup="filterMovies()" placeholder="Search movies by title...">
        </div>
    </div>

    <div class="grid" id="movieGrid">
        <?php while($m = $movies->fetch_assoc()): ?>
            <div class="movie-card movie-item" id="card-<?= $m['id'] ?>" data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>">
                <div class="poster-container">
                    <span class="genre-badge"><?= htmlspecialchars($m['genre']) ?></span>
                    <?php if(!empty($m['image']) && file_exists("uploads/" . $m['image'])): ?>
                        <img src="uploads/<?= $m['image'] ?>" class="poster-img" alt="Poster">
                    <?php else: ?>
                        <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=800&auto=format&fit=crop" class="poster-img" alt="Poster">
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div>
                        <h3 class="movie-title"><?= htmlspecialchars($m['title']) ?></h3>
                        <div class="price-tag">
                            <span>NPR</span> <?= number_format($m['price'], 2) ?>
                        </div>
                    </div>
                    <button onclick="captureAndBook(<?= $m['id'] ?>)" class="btn-book">
                        <i class="fa-solid fa-ticket"></i> Book Seat
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <form id="screenshotForm" action="book.php" method="POST" style="display:none;">
        <input type="hidden" name="movie_id" id="form_movie_id">
        <input type="hidden" name="screenshot_data" id="form_screenshot_data">
    </form>

    <script>
    function filterMovies() {
        let input = document.getElementById('searchInput').value.toLowerCase();
        let cards = document.getElementsByClassName('movie-item');

        for (let i = 0; i < cards.length; i++) {
            let title = cards[i].getAttribute('data-title');
            cards[i].style.display = title.includes(input) ? "flex" : "none";
        }
    }

    function captureAndBook(movieId) {
        let targetElement = document.getElementById('card-' + movieId);
        html2canvas(targetElement, { backgroundColor: '#030712', scale: 2 }).then(canvas => {
            document.getElementById('form_movie_id').value = movieId;
            document.getElementById('form_screenshot_data').value = canvas.toDataURL('image/png');
            document.getElementById('screenshotForm').submit();
        });
    }
    </script>

</body>
</html>