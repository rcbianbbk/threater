<?php
$page_title = "Home - Now Showing";
include 'db.php';
include 'includes/header.php';

// Fetch movies grouped by status
$now_showing = $conn->query("SELECT * FROM movies WHERE status = 'now_showing' OR status IS NULL ORDER BY id DESC");
$coming_soon = $conn->query("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY id DESC");

<<<<<<< HEAD
$user_data = null;
$user_initial = "";
=======
// Featured movie for Hero Banner (Top rated or first movie)
$featured_res = $conn->query("SELECT * FROM movies ORDER BY rating DESC, id DESC LIMIT 1");
$featured_movie = ($featured_res && $featured_res->num_rows > 0) ? $featured_res->fetch_assoc() : null;
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987

// Collect unique genres for filter chips
$genres = ['All'];
$all_genres_res = $conn->query("SELECT DISTINCT genre FROM movies");
if ($all_genres_res) {
    while($g = $all_genres_res->fetch_assoc()) {
        $parts = explode('/', $g['genre']);
        foreach($parts as $p) {
            $trimmed = trim($p);
            if ($trimmed && !in_array($trimmed, $genres)) {
                $genres[] = $trimmed;
            }
        }
    }
}
?>
<<<<<<< HEAD
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
=======

<main>
    <!-- Hero Spotlight Banner -->
    <?php if($featured_movie): ?>
    <section class="container" style="margin-top: 15px; margin-bottom: 40px;">
        <div class="glass-card" style="min-height: 440px; display: flex; flex-direction: column; justify-content: flex-end; padding: 40px; position: relative; background: linear-gradient(180deg, rgba(7,9,14,0.2) 0%, rgba(7,9,14,0.95) 100%), url('<?= htmlspecialchars($featured_movie['poster_image'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200') ?>') center/cover no-repeat; border-radius: var(--radius-xl);">
            
            <div style="position: absolute; top: 25px; left: 30px; display: flex; gap: 10px;">
                <span class="badge-status" style="background: rgba(99,102,241,0.9); backdrop-filter: blur(8px); padding: 6px 14px; font-size: 12px;">
                    <i class="fa-solid fa-fire" style="color: #f59e0b; margin-right: 4px;"></i> FEATURED PREMIERE
                </span>
                <span class="badge-rating" style="padding: 6px 12px; font-size: 13px;">
                    <i class="fa-solid fa-star"></i> <?= number_format($featured_movie['rating'] ?: 4.8, 1) ?> / 10
                </span>
            </div>

            <div style="max-width: 680px; position: relative; z-index: 2;">
                <p style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; font-size: 13px; margin-bottom: 8px;">
                    <?= htmlspecialchars($featured_movie['genre']) ?> • <?= $featured_movie['duration'] ?> Mins
                </p>
                <h1 style="font-size: clamp(28px, 4vw, 46px); color: #fff; margin-bottom: 14px; line-height: 1.15;">
                    <?= htmlspecialchars($featured_movie['title']) ?>
                </h1>
                <p style="color: var(--text-muted); font-size: 15px; line-height: 1.6; margin-bottom: 24px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($featured_movie['description'] ?: 'Immerse yourself in breathtaking visuals and cinematic storytelling on our ultra-wide laser screen with Dolby Atmos.') ?>
                </p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
                    <a href="book.php?movie_id=<?= $featured_movie['id'] ?>" class="btn btn-accent btn-lg">
                        <i class="fa-solid fa-ticket"></i> Book Tickets Now (Rs. <?= number_format($featured_movie['price'], 2) ?>)
                    </a>
                    <?php if(!empty($featured_movie['trailer_url'])): ?>
                        <button onclick="openTrailer('<?= addslashes(htmlspecialchars($featured_movie['title'])) ?>', '<?= htmlspecialchars($featured_movie['trailer_url']) ?>')" class="btn btn-outline btn-lg" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.08);">
                            <i class="fa-solid fa-play" style="color: var(--accent);"></i> Watch Trailer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Experience Highlights Strip -->
    <section class="container" style="margin-bottom: 45px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px;">
            <div class="glass-card" style="padding: 18px 22px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99,102,241,0.15); display:flex; align-items:center; justify-content:center; color:#818cf8; font-size: 22px;">
                    <i class="fa-solid fa-video"></i>
                </div>
                <div>
                    <h4 style="font-size: 15px; margin-bottom: 2px;">4K Laser Cinema</h4>
                    <p style="font-size: 12px; color: var(--text-muted);">Crystal ultra-sharp projection</p>
                </div>
            </div>

            <div class="glass-card" style="padding: 18px 22px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(6,182,212,0.15); display:flex; align-items:center; justify-content:center; color:#22d3ee; font-size: 22px;">
                    <i class="fa-solid fa-volume-high"></i>
                </div>
                <div>
                    <h4 style="font-size: 15px; margin-bottom: 2px;">Dolby Atmos 360°</h4>
                    <p style="font-size: 12px; color: var(--text-muted);">Spatial surround soundscape</p>
                </div>
            </div>

            <div class="glass-card" style="padding: 18px 22px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245,158,11,0.15); display:flex; align-items:center; justify-content:center; color:#fbbf24; font-size: 22px;">
                    <i class="fa-solid fa-couch"></i>
                </div>
                <div>
                    <h4 style="font-size: 15px; margin-bottom: 2px;">Luxury Recliners</h4>
                    <p style="font-size: 12px; color: var(--text-muted);">Ergonomic VIP comfort</p>
                </div>
            </div>

            <div class="glass-card" style="padding: 18px 22px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16,185,129,0.15); display:flex; align-items:center; justify-content:center; color:#34d399; font-size: 22px;">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h4 style="font-size: 15px; margin-bottom: 2px;">Instant E-Tickets</h4>
                    <p style="font-size: 12px; color: var(--text-muted);">Fast QR contactless check-in</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Filter Controls -->
    <section class="container" id="movies" style="margin-bottom: 30px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 28px; margin-bottom: 4px;">Explore Movies</h2>
                <p style="color: var(--text-muted); font-size: 14px;">Select your movie, choose your seats, and enjoy the show</p>
            </div>

            <!-- Live Search Bar -->
            <div style="position: relative; min-width: 280px; max-width: 380px; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-dim);"></i>
                <input type="text" id="movieSearch" placeholder="Search by title, genre..." class="form-control" style="padding-left: 42px; border-radius: 30px;" onkeyup="filterMovies()">
            </div>
        </div>

        <!-- Filter Chips -->
        <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 25px;">
            <?php foreach(array_slice($genres, 0, 8) as $index => $gn): ?>
                <button class="btn btn-sm genre-filter-btn <?= ($index === 0) ? 'btn-accent' : 'btn-outline' ?>" onclick="filterGenre('<?= htmlspecialchars($gn) ?>', this)">
                    <?= htmlspecialchars($gn) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Now Showing Movie Grid -->
    <section class="container" style="margin-bottom: 60px;">
        <div class="grid" id="moviesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 28px;">
            <?php if ($now_showing && $now_showing->num_rows > 0): ?>
                <?php while($m = $now_showing->fetch_assoc()): 
                    $poster = !empty($m['poster_image']) ? $m['poster_image'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600';
                    $rating = !empty($m['rating']) ? $m['rating'] : 4.5;
                ?>
                    <div class="movie-card" data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>" data-genre="<?= strtolower(htmlspecialchars($m['genre'])) ?>">
                        <div class="movie-poster-wrap">
                            <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($m['title']) ?>" class="movie-poster-img" onerror="this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600'">
                            
                            <div class="movie-overlay-badges">
                                <span class="badge-status">NOW SHOWING</span>
                                <span class="badge-rating"><i class="fa-solid fa-star"></i> <?= number_format($rating, 1) ?></span>
                            </div>
                        </div>

                        <div class="movie-card-content">
                            <h3 class="movie-title"><?= htmlspecialchars($m['title']) ?></h3>
                            
                            <div class="movie-meta">
                                <span class="movie-genre-tag"><?= htmlspecialchars(explode('/', $m['genre'])[0]) ?></span>
                                <span>•</span>
                                <span><i class="fa-regular fa-clock" style="margin-right: 2px;"></i> <?= $m['duration'] ?>m</span>
                            </div>

                            <p style="color: var(--text-dim); font-size: 13px; margin-bottom: 12px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($m['description'] ?: 'Experience exhilarating cinematic entertainment in our high fidelity hall.') ?>
                            </p>

                            <div class="movie-price-row">
                                <div class="movie-price-tag">
                                    <small>Rs.</small> <?= number_format($m['price'], 2) ?>
                                </div>
                                
                                <div style="display: flex; gap: 6px;">
                                    <?php if(!empty($m['trailer_url'])): ?>
                                        <button onclick="openTrailer('<?= addslashes(htmlspecialchars($m['title'])) ?>', '<?= htmlspecialchars($m['trailer_url']) ?>')" class="btn btn-outline btn-sm" title="Watch Trailer" style="padding: 8px 10px;">
                                            <i class="fa-solid fa-play" style="color: var(--accent);"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="book.php?movie_id=<?= $m['id'] ?>" class="btn btn-accent btn-sm">
                                        <i class="fa-solid fa-ticket"></i> Book
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: var(--bg-card); border-radius: var(--radius-md);">
                    <i class="fa-solid fa-film" style="font-size: 40px; color: var(--text-dim); margin-bottom: 12px;"></i>
                    <h3>No Movies Currently Showing</h3>
                    <p style="color: var(--text-muted); margin-top: 6px;">Please check back soon or explore our upcoming titles.</p>
                </div>
            <?php endif; ?>
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
        </div>
        <div id="noSearchMatches" style="display:none; text-align:center; padding:50px; background:var(--bg-card); border-radius:var(--radius-md); margin-top:20px;">
            <i class="fa-solid fa-magnifying-glass" style="font-size:36px; color:var(--text-dim); margin-bottom:10px;"></i>
            <h3>No matching movies found</h3>
            <p style="color:var(--text-muted);">Try searching with different keywords or clearing the genre filter.</p>
        </div>
    </section>

<<<<<<< HEAD
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
=======
    <!-- Coming Soon Section (if any) -->
    <?php if($coming_soon && $coming_soon->num_rows > 0): ?>
    <section class="container" id="upcoming" style="margin-bottom: 60px;">
        <div style="margin-bottom: 25px;">
            <span class="badge-status coming_soon" style="font-size: 12px; padding: 4px 10px; margin-bottom: 8px; display: inline-block;">COMING SOON</span>
            <h2 style="font-size: 26px;">Upcoming Blockbusters</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Get ready for the most anticipated releases</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 28px;">
            <?php while($cs = $coming_soon->fetch_assoc()): 
                $poster = !empty($cs['poster_image']) ? $cs['poster_image'] : 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=600';
            ?>
                <div class="movie-card">
                    <div class="movie-poster-wrap">
                        <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($cs['title']) ?>" class="movie-poster-img">
                        <div class="movie-overlay-badges">
                            <span class="badge-status coming_soon">PREMIERING SOON</span>
                        </div>
                    </div>
                    <div class="movie-card-content">
                        <h3 class="movie-title"><?= htmlspecialchars($cs['title']) ?></h3>
                        <div class="movie-meta">
                            <span class="movie-genre-tag"><?= htmlspecialchars($cs['genre']) ?></span>
                            <span>•</span>
                            <span><?= $cs['duration'] ?>m</span>
                        </div>
                        <div class="movie-price-row">
                            <span style="color: var(--amber); font-size: 13px; font-weight: 600;"><i class="fa-solid fa-bell"></i> Tickets opening soon</span>
                            <?php if(!empty($cs['trailer_url'])): ?>
                                <button onclick="openTrailer('<?= addslashes(htmlspecialchars($cs['title'])) ?>', '<?= htmlspecialchars($cs['trailer_url']) ?>')" class="btn btn-outline btn-sm">
                                    <i class="fa-solid fa-play"></i> Trailer
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
let currentGenre = 'All';

function filterGenre(genre, btnElement) {
    currentGenre = genre;
    document.querySelectorAll('.genre-filter-btn').forEach(btn => {
        btn.classList.remove('btn-accent');
        btn.classList.add('btn-outline');
    });
    btnElement.classList.remove('btn-outline');
    btnElement.classList.add('btn-accent');
    filterMovies();
}

function filterMovies() {
    let query = document.getElementById('movieSearch').value.toLowerCase().trim();
    let cards = document.querySelectorAll('#moviesGrid .movie-card');
    let visibleCount = 0;

    cards.forEach(card => {
        let title = card.getAttribute('data-title') || '';
        let genre = card.getAttribute('data-genre') || '';

        let matchQuery = (title.includes(query) || genre.includes(query));
        let matchGenre = (currentGenre === 'All' || genre.includes(currentGenre.toLowerCase()));

        if (matchQuery && matchGenre) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    let noMatch = document.getElementById('noSearchMatches');
    if (visibleCount === 0 && cards.length > 0) {
        noMatch.style.display = 'block';
    } else {
        noMatch.style.display = 'none';
>>>>>>> b272aa372d89b77b743fc0244c37faf76bb97987
    }
}
</script>

<?php include 'includes/footer.php'; ?>