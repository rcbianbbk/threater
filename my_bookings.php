<?php
$page_title = "My Bookings & Tickets";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

$bookings_res = $conn->query("
    SELECT b.*, m.title as movie_title, m.poster_image, m.duration, m.genre 
    FROM bookings b
    JOIN movies m ON b.movie_id = m.id
    WHERE b.user_id = $user_id
    ORDER BY b.id DESC
");

include 'includes/header.php';
?>

<main class="container" style="margin-top: 25px; margin-bottom: 70px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h1 style="font-size: 28px; margin-bottom: 4px;">My Cinema Tickets</h1>
            <p style="color: var(--text-muted); font-size: 14px;">View your active reservations, digital passes, and order history.</p>
        </div>
        <a href="index.php#movies" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Book Another Movie
        </a>
    </div>

    <?php if($bookings_res && $bookings_res->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;">
            <?php while($b = $bookings_res->fetch_assoc()): 
                $ticket_code = $b['ticket_code'] ?: ('CW-' . str_pad($b['id'], 6, '0', STR_PAD_LEFT));
                $poster = !empty($b['poster_image']) ? $b['poster_image'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=400';
            ?>
                <div class="glass-card" style="border-radius: var(--radius-lg); overflow: hidden; display: flex; flex-direction: column;">
                    
                    <!-- Card Top Header -->
                    <div style="background: linear-gradient(135deg, #1e1b4b, #0f172a); padding: 18px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 700; color: #818cf8; letter-spacing: 1px;">
                            <i class="fa-solid fa-ticket"></i> <?= htmlspecialchars($ticket_code) ?>
                        </span>
                        <span class="badge-status" style="background: rgba(16,185,129,0.2); color: #34d399; border: 1px solid rgba(16,185,129,0.4); font-size: 11px;">
                            <i class="fa-solid fa-circle-check" style="font-size: 10px;"></i> <?= htmlspecialchars($b['payment_status'] ?? 'Success') ?>
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 20px; display: flex; gap: 16px; flex-grow: 1;">
                        <img src="<?= htmlspecialchars($poster) ?>" alt="<?= htmlspecialchars($b['movie_title']) ?>" style="width: 80px; height: 115px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                        
                        <div style="flex-grow: 1;">
                            <h3 style="font-size: 17px; margin-bottom: 6px; color: #fff;"><?= htmlspecialchars($b['movie_title']) ?></h3>
                            <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 8px;">
                                <?= htmlspecialchars(explode('/', $b['genre'])[0]) ?> • <?= $b['duration'] ?>m
                            </p>
                            
                            <div style="background: #090d16; padding: 10px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 10px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom: 4px;">
                                    <span style="color: var(--text-dim);">Seats:</span>
                                    <strong style="color: var(--accent);"><?= htmlspecialchars($b['seat_number']) ?></strong>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span style="color: var(--text-dim);">Showtime:</span>
                                    <strong style="color: #fff;"><?= htmlspecialchars($b['show_time'] ?? '05:30 PM') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Bottom Footer with Action -->
                    <div style="padding: 14px 20px; background: rgba(15,23,42,0.6); border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <small style="color: var(--text-dim); display: block; font-size: 11px;">Paid Amount</small>
                            <strong style="color: #34d399; font-size: 15px;">Rs. <?= number_format($b['total_amount'] ?? 350, 2) ?></strong>
                        </div>
                        <a href="ticket.php?id=<?= $b['id'] ?>" class="btn btn-accent btn-sm">
                            <i class="fa-solid fa-receipt"></i> View E-Ticket
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 70px 20px; max-width: 550px; margin: 40px auto;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(99,102,241,0.15); display: flex; align-items: center; justify-content: center; font-size: 34px; color: #818cf8; margin: 0 auto 20px;">
                <i class="fa-solid fa-ticket-simple"></i>
            </div>
            <h2 style="margin-bottom: 8px;">No Bookings Found</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">
                You haven't booked any movie tickets yet. Explore our latest blockbuster lineup!
            </p>
            <a href="index.php" class="btn btn-accent btn-lg">
                <i class="fa-solid fa-clapperboard"></i> Browse Now Showing
            </a>
        </div>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>
