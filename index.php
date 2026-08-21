<?php
$page_title = "Home - Now Showing";
include 'db.php';
include 'includes/header.php';

// Fetch movies grouped by status
$now_showing = $conn->query("SELECT * FROM movies WHERE status = 'now_showing' OR status IS NULL ORDER BY id DESC");
$coming_soon = $conn->query("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY id DESC");

// Featured movie for Hero Banner (Top rated or first movie)
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
?>

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
        </div>
        <div id="noSearchMatches" style="display:none; text-align:center; padding:50px; background:var(--bg-card); border-radius:var(--radius-md); margin-top:20px;">
            <i class="fa-solid fa-magnifying-glass" style="font-size:36px; color:var(--text-dim); margin-bottom:10px;"></i>
            <h3>No matching movies found</h3>
            <p style="color:var(--text-muted);">Try searching with different keywords or clearing the genre filter.</p>
        </div>
    </section>

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
    }
}
</script>

<?php include 'includes/footer.php'; ?>