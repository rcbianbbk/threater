<?php
$page_title = "Home - Now Showing";
include 'db.php';

// Session start गर्ने (यदि गरिएको छैन भने)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';

// Fetch movies grouped by status
$now_showing = $conn->query("SELECT * FROM movies WHERE status = 'now_showing' OR status IS NULL ORDER BY id DESC");
$coming_soon = $conn->query("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY id DESC");

// Featured movie for Hero Banner
$featured_res = $conn->query("SELECT * FROM movies ORDER BY rating DESC, id DESC LIMIT 1");
$featured_movie = ($featured_res && $featured_res->num_rows > 0) ? $featured_res->fetch_assoc() : null;

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

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) ? 'true' : 'false';
?>

<main style="background-color: #0c0c0c; color: #e5e5e5; min-height: 100vh;">
    <!-- Hero Spotlight Banner -->
    <?php if($featured_movie): ?>
    <section class="container" style="padding-top: 20px; margin-bottom: 40px;">
        <div class="glass-card" style="min-height: 440px; display: flex; flex-direction: column; justify-content: flex-end; padding: 40px; position: relative; background: linear-gradient(180deg, rgba(12,12,12,0.1) 0%, rgba(12,12,12,0.95) 100%), url('<?= htmlspecialchars($featured_movie['poster_image'] ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1200') ?>') center/cover no-repeat; border-radius: 16px; border: 1px solid rgba(229,9,20,0.3);">
            
            <div style="position: absolute; top: 25px; left: 30px; display: flex; gap: 10px;">
                <span style="background: rgba(229,9,20,0.9); color: #fff; backdrop-filter: blur(8px); padding: 6px 14px; font-size: 12px; border-radius: 20px; font-weight: 600;">
                    <i class="fa-solid fa-fire" style="color: #ffd700; margin-right: 4px;"></i> FEATURED PREMIERE
                </span>
                <span style="background: rgba(0,0,0,0.7); color: #ffd700; border: 1px solid rgba(255,215,0,0.3); padding: 6px 12px; font-size: 13px; border-radius: 20px; font-weight: 600;">
                    <i class="fa-solid fa-star"></i> <?= number_format($featured_movie['rating'] ?: 4.8, 1) ?> / 10
                </span>
            </div>

            <div style="max-width: 680px; position: relative; z-index: 2;">
                <p style="color: #ffd700; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; font-size: 13px; margin-bottom: 8px;">
                    <?= htmlspecialchars($featured_movie['genre']) ?> • <?= $featured_movie['duration'] ?> Mins
                </p>
                <h1 style="font-size: clamp(28px, 4vw, 46px); color: #fff; margin-bottom: 14px; line-height: 1.15; font-weight: 800;">
                    <?= htmlspecialchars($featured_movie['title']) ?>
                </h1>
                <p style="color: #b3b3b3; font-size: 15px; line-height: 1.6; margin-bottom: 24px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($featured_movie['description'] ?: 'Immerse yourself in breathtaking visuals and cinematic storytelling on our ultra-wide laser screen with Dolby Atmos.') ?>
                </p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center;">
                    <a href="book.php?movie_id=<?= $featured_movie['id'] ?>" onclick="handleBooking(event, this.href)" class="btn btn-accent btn-lg" style="background: #e50914; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none;">
                        <i class="fa-solid fa-ticket"></i> Book Tickets Now (Rs. <?= number_format($featured_movie['price'], 2) ?>)
                    </a>
                    <?php if(!empty($featured_movie['trailer_url'])): ?>
                        <button onclick="openTrailer('<?= addslashes(htmlspecialchars($featured_movie['title'])) ?>', '<?= htmlspecialchars($featured_movie['trailer_url']) ?>')" class="btn btn-outline btn-lg" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.08); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 12px 24px; border-radius: 8px; cursor: pointer;">
                            <i class="fa-solid fa-play" style="color: #e50914;"></i> Watch Trailer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Search & Filter Controls -->
    <section class="container" id="movies" style="margin-bottom: 30px;">
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 25px;">
            <div>
                <h2 style="font-size: 28px; margin-bottom: 4px; color: #fff;">Explore Movies</h2>
                <p style="color: #999; font-size: 14px;">Select your movie, choose your seats, and enjoy the show</p>
            </div>

            <!-- Live Search Bar -->
            <div style="position: relative; min-width: 280px; max-width: 380px; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #777;"></i>
                <input type="text" id="movieSearch" placeholder="Search by title, genre..." style="width: 100%; padding: 12px 16px 12px 42px; background: #141414; border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 30px; outline: none;" onkeyup="filterMovies()">
            </div>
        </div>

        <!-- Filter Chips -->
        <div style="display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 25px;">
            <?php foreach(array_slice($genres, 0, 8) as $index => $gn): ?>
                <button class="genre-filter-btn" onclick="filterGenre('<?= htmlspecialchars($gn) ?>', this)" style="padding: 8px 16px; border-radius: 20px; font-size: 13px; cursor: pointer; white-space: nowrap; border: 1px solid <?= ($index === 0) ? '#e50914' : 'rgba(255,255,255,0.2)' ?>; background: <?= ($index === 0) ? '#e50914' : '#141414' ?>; color: #fff; transition: 0.2s;">
                    <?= htmlspecialchars($gn) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Now Showing Movie Grid -->
    <section class="container" style="margin-bottom: 60px;">
        <div id="moviesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 28px;">
            <?php if ($now_showing && $now_showing->num_rows > 0): ?>
                <?php while($m = $now_showing->fetch_assoc()): 
                    $poster = !empty($m['poster_image']) ? $m['poster_image'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600';
                    $rating = !empty($m['rating']) ? $m['rating'] : 4.5;
                ?>
                    <div class="movie-card" data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>" data-genre="<?= strtolower(htmlspecialchars($m['genre'])) ?>" style="background: #141414; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                        <div style="position: relative; height: 360px; overflow: hidden;">
                            <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($m['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=600'">
                            
                            <div style="position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="background: rgba(229,9,20,0.9); color: #fff; padding: 4px 10px; font-size: 10px; font-weight: bold; border-radius: 4px;">NOW SHOWING</span>
                                <span style="background: rgba(0,0,0,0.8); color: #ffd700; padding: 4px 8px; font-size: 11px; font-weight: bold; border-radius: 4px;"><i class="fa-solid fa-star"></i> <?= number_format($rating, 1) ?></span>
                            </div>
                        </div>

                        <div style="padding: 16px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                            <div>
                                <h3 style="font-size: 18px; color: #fff; margin-bottom: 6px; font-weight: 700;"><?= htmlspecialchars($m['title']) ?></h3>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #aaa; margin-bottom: 10px;">
                                    <span style="color: #ffd700;"><?= htmlspecialchars(explode('/', $m['genre'])[0]) ?></span>
                                    <span>•</span>
                                    <span><i class="fa-regular fa-clock"></i> <?= $m['duration'] ?>m</span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
                                <div style="color: #fff; font-size: 15px; font-weight: 700;">
                                    <small style="color: #e50914; font-size: 11px;">Rs.</small> <?= number_format($m['price'], 2) ?>
                                </div>
                                
                                <div style="display: flex; gap: 6px;">
                                    <?php if(!empty($m['trailer_url'])): ?>
                                        <button onclick="openTrailer('<?= addslashes(htmlspecialchars($m['title'])) ?>', '<?= htmlspecialchars($m['trailer_url']) ?>')" style="background: #1f1f1f; border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 6px 10px; border-radius: 6px; cursor: pointer;" title="Watch Trailer">
                                            <i class="fa-solid fa-play" style="color: #e50914;"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="book.php?movie_id=<?= $m['id'] ?>" onclick="handleBooking(event, this.href)" style="background: #e50914; color: #fff; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">
                                        <i class="fa-solid fa-ticket"></i> Book
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: #141414; border-radius: 12px;">
                    <i class="fa-solid fa-film" style="font-size: 40px; color: #555; margin-bottom: 12px;"></i>
                    <h3 style="color: #fff;">No Movies Currently Showing</h3>
                </div>
            <?php endif; ?>
        </div>
        <div id="noSearchMatches" style="display:none; text-align:center; padding:50px; background:#141414; border-radius:12px; margin-top:20px;">
            <h3 style="color: #fff;">No matching movies found</h3>
        </div>
    </section>
</main>

<!-- Trailer Modal Popup -->
<div id="trailerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div style="position: relative; width: 90%; max-width: 800px; background: #141414; border: 1px solid rgba(229,9,20,0.4); border-radius: 12px; overflow: hidden;">
        <div style="padding: 14px 20px; background: #1f1f1f; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="trailerTitle" style="color: #fff; font-size: 16px; margin: 0;">Movie Trailer</h3>
            <button onclick="closeTrailer()" style="background: transparent; border: none; color: #fff; font-size: 20px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
            <iframe id="trailerIframe" src="" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
        </div>
    </div>
</div>

<!-- Sign In Required Popup Modal -->
<div id="loginPromptModal" style="display:none; position:fixed; inset:0; z-index:999999; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:#0f1420; border:1px solid rgba(255,255,255,0.15); border-radius:20px; width:100%; max-width:420px; padding:35px; text-align:center; box-shadow:0 25px 50px rgba(0,0,0,0.7);">
        <div style="width:65px; height:65px; background:rgba(229,9,20,0.15); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px auto; color:#e50914; font-size:26px;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h3 style="color:#fff; font-size:22px; margin:0 0 10px 0; font-weight: 700;">Sign In Required</h3>
        <p style="color:#94a3b8; font-size:14px; margin:0 0 25px 0; line-height:1.5;">टिकट बुक गर्नको लागि कृपया क्लाइन्ट खातामा लगइन गर्नुहोस् वा नयाँ खाता बनाउनुहोस्।</p>
        
        <div style="display:flex; gap:12px; flex-direction: column;">
            <button onclick="openClientLoginModal()" style="background:#e50914; color:#fff; border:none; padding:12px; border-radius:12px; font-weight:700; font-size:14px; display:block; width:100%; cursor:pointer;">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In to Client Portal
            </button>
            <a href="register.php" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; text-decoration:none; padding:12px; border-radius:12px; font-weight:600; font-size:14px; display:block;">
                <i class="fa-solid fa-user-plus"></i> Create Client Account
            </a>
            <button onclick="closeLoginPrompt()" style="background:transparent; border:none; color:#888; font-size:13px; cursor:pointer; margin-top:5px;">
                Cancel / Close
            </button>
        </div>
    </div>
</div>

<!-- Client Login Popup Modal -->
<div id="clientLoginModal" style="display:none; position:fixed; inset:0; z-index:9999999; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); align-items:center; justify-content:center; padding:20px;">
    <div style="background:#141414; border:1px solid rgba(212,175,55,0.4); border-radius:16px; width:100%; max-width:400px; padding:40px; box-shadow:0 15px 35px rgba(0,0,0,0.8); position:relative;">
        
        <button onclick="closeClientLoginModal()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; color:#fff; font-size:18px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>

        <div style="text-align:center; margin-bottom:25px;">
            <div style="width:60px; height:60px; background:rgba(212,175,55,0.15); color:#d4af37; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 15px auto;">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2 style="color:#fff; font-size:22px; font-weight:700; letter-spacing: 1px; margin-bottom:5px;">CLIENT LOGIN</h2>
        </div>

        <form action="login_process.php" method="POST">
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:13px; color:#aaa; margin-bottom:6px; font-weight:500;">Email Address</label>
                <input type="email" name="email" required placeholder="bibek@gmail.com" style="width:100%; padding:12px 16px; background:#1f1f1f; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px; outline:none; font-size:14px;">
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; color:#aaa; margin-bottom:6px; font-weight:500;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" style="width:100%; padding:12px 16px; background:#1f1f1f; border:1px solid rgba(255,255,255,0.1); color:#fff; border-radius:8px; outline:none; font-size:14px;">
            </div>

            <button type="submit" style="width:100%; background:linear-gradient(135deg, #d4af37, #aa8c2c); color:#000; border:none; padding:12px; border-radius:8px; font-weight:700; font-size:15px; cursor:pointer; box-shadow: 0 4px 12px rgba(212,175,55,0.3);">
                Login
            </button>
        </form>
    </div>
</div>

<script>
const isLoggedIn = <?= $is_logged_in ?>;

function handleBooking(event, targetUrl) {
    if (!isLoggedIn) {
        event.preventDefault();
        document.getElementById('loginPromptModal').style.display = 'flex';
        return false;
    }
}

function closeLoginPrompt() {
    document.getElementById('loginPromptModal').style.display = 'none';
}

function openClientLoginModal() {
    document.getElementById('loginPromptModal').style.display = 'none';
    document.getElementById('clientLoginModal').style.display = 'flex';
}

function closeClientLoginModal() {
    document.getElementById('clientLoginModal').style.display = 'none';
}

window.addEventListener('DOMContentLoaded', (event) => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                checkAndShowLoginPopup();
            },
            function(error) {
                checkAndShowLoginPopup();
            },
            { timeout: 10000 }
        );
    } else {
        checkAndShowLoginPopup();
    }
});

function checkAndShowLoginPopup() {
    if (!isLoggedIn) {
        setTimeout(() => {
            document.getElementById('loginPromptModal').style.display = 'flex';
        }, 1000);
    }
}

let currentGenre = 'All';

function filterGenre(genre, btnElement) {
    currentGenre = genre;
    document.querySelectorAll('.genre-filter-btn').forEach(btn => {
        btn.style.background = '#141414';
        btn.style.borderColor = 'rgba(255,255,255,0.2)';
    });
    btnElement.style.background = '#e50914';
    btnElement.style.borderColor = '#e50914';
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
    }
}

function openTrailer(title, url) {
    let modal = document.getElementById('trailerModal');
    let iframe = document.getElementById('trailerIframe');
    let titleEl = document.getElementById('trailerTitle');
    
    let embedUrl = url;
    if (url.includes('watch?v=')) {
        embedUrl = url.replace('watch?v=', 'embed/');
    } else if (url.includes('youtu.be/')) {
        embedUrl = url.replace('youtu.be/', 'www.youtube.com/embed/');
    }

    titleEl.innerText = title + ' - Official Trailer';
    iframe.src = embedUrl;
    modal.style.display = 'flex';
}

function closeTrailer() {
    let modal = document.getElementById('trailerModal');
    let iframe = document.getElementById('trailerIframe');
    iframe.src = '';
    modal.style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>