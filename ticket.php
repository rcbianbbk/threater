<?php
$page_title = "Digital E-Ticket Pass";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id <= 0) {
    header("Location: my_bookings.php");
    exit();
}

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
           m.title as movie_title, m.poster_image, m.duration, m.genre, m.price as single_price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN movies m ON b.movie_id = m.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    echo "Ticket not found or invalid booking ID.";
    exit();
}

// Ensure security: user can only view their own ticket (unless admin)
if (!isset($_SESSION['admin']) && (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $booking['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$ticket_code = $booking['ticket_code'] ?: ('CW-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT));
$qr_data = "CINEMA_WORLD_TICKET|" . $ticket_code . "|MOVIE:" . $booking['movie_title'] . "|SEATS:" . $booking['seat_number'] . "|USER:" . $booking['user_name'];
$poster = !empty($booking['poster_image']) ? $booking['poster_image'] : 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket: <?= htmlspecialchars($ticket_code) ?> - Cinema World</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body {
            background-color: #060910;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .ticket-wrapper {
            max-width: 650px;
            width: 100%;
            margin: 0 auto;
        }

        .ticket-pass {
            background: #0f1523;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7);
            position: relative;
            overflow: hidden;
        }

        .ticket-hero {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.2) 0%, rgba(15, 23, 42, 0.95) 100%), url('<?= htmlspecialchars($poster) ?>') center/cover no-repeat;
            padding: 30px;
            position: relative;
        }

        .ticket-hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .ticket-poster-thumb {
            width: 90px;
            height: 130px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
        }

        .ticket-details-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            padding: 24px 30px;
            background: #0f1523;
        }

        .detail-cell small {
            display: block;
            color: var(--text-dim);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .detail-cell strong {
            color: #fff;
            font-size: 15px;
        }

        .ticket-stub {
            background: #0a0e1a;
            border-top: 2px dashed rgba(255, 255, 255, 0.12);
            padding: 24px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        /* Notches */
        .stub-notch-left, .stub-notch-right {
            position: absolute;
            top: -14px;
            width: 28px;
            height: 28px;
            background: #060910;
            border-radius: 50%;
            z-index: 10;
        }
        .stub-notch-left { left: -14px; }
        .stub-notch-right { right: -14px; }

        .barcode-font {
            font-family: 'Libre Barcode 128', cursive, monospace;
            font-size: 44px;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 2px;
            line-height: 1;
        }

        /* Print Mode styles */
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .ticket-pass {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
            }
            .ticket-hero {
                background: #f1f5f9 !important;
                color: black !important;
            }
            .ticket-hero h2, .ticket-details-grid strong, .detail-cell strong {
                color: black !important;
            }
            .detail-cell small {
                color: #64748b !important;
            }
            .ticket-stub {
                background: #f8fafc !important;
                border-top: 2px dashed #000 !important;
            }
            .stub-notch-left, .stub-notch-right {
                background: white !important;
            }
            .barcode-font {
                color: black !important;
            }
        }
    </style>
</head>
<body>

    <div class="ticket-wrapper">
        <!-- Top Action Controls -->
        <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="my_bookings.php" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left"></i> My Bookings
            </a>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.print()" class="btn btn-accent btn-sm">
                    <i class="fa-solid fa-print"></i> Print Ticket
                </button>
                <a href="index.php" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            </div>
        </div>

        <!-- Digital Boarding Pass Ticket -->
        <div class="ticket-pass">
            
            <!-- Hero Header -->
            <div class="ticket-hero">
                <div class="ticket-hero-content">
                    <img src="<?= htmlspecialchars($poster) ?>" alt="Poster" class="ticket-poster-thumb">
                    <div>
                        <span class="badge-status" style="font-size: 11px; margin-bottom: 6px; display: inline-block;">
                            <i class="fa-solid fa-film"></i> AUDITORIUM 1 • LASER 4K
                        </span>
                        <h2 style="font-size: 24px; color: #fff; margin-bottom: 6px; line-height: 1.2;">
                            <?= htmlspecialchars($booking['movie_title']) ?>
                        </h2>
                        <p style="color: var(--text-muted); font-size: 13px;">
                            <?= htmlspecialchars($booking['genre']) ?> • <?= $booking['duration'] ?> Mins
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ticket Information Details Grid -->
            <div class="ticket-details-grid">
                <div class="detail-cell">
                    <small>Showtime</small>
                    <strong style="color: var(--accent);"><?= htmlspecialchars($booking['show_time'] ?? '05:30 PM') ?></strong>
                </div>
                <div class="detail-cell">
                    <small>Selected Seats</small>
                    <strong style="color: #34d399; font-size: 18px;"><?= htmlspecialchars($booking['seat_number']) ?></strong>
                </div>
                <div class="detail-cell">
                    <small>Total Paid</small>
                    <strong>Rs. <?= number_format($booking['total_amount'] ?? ($booking['single_price'] ?: 350), 2) ?></strong>
                </div>
                <div class="detail-cell">
                    <small>Guest Name</small>
                    <strong><?= htmlspecialchars($booking['user_name']) ?></strong>
                </div>
                <div class="detail-cell">
                    <small>Payment Status</small>
                    <strong style="color: #34d399;"><i class="fa-solid fa-circle-check" style="font-size: 12px;"></i> <?= htmlspecialchars($booking['payment_status'] ?? 'Success') ?></strong>
                </div>
                <div class="detail-cell">
                    <small>Booking Date</small>
                    <strong style="font-size: 13px;"><?= date('M d, Y', strtotime($booking['booking_date'])) ?></strong>
                </div>
            </div>

            <!-- Ticket Stub with Barcode & QR Code -->
            <div class="ticket-stub">
                <div class="stub-notch-left"></div>
                <div class="stub-notch-right"></div>

                <div>
                    <span style="display: block; font-size: 11px; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-bottom: 2px;">
                        Boarding Ticket Code
                    </span>
                    <strong style="font-size: 18px; color: #818cf8; letter-spacing: 1.5px; font-family: 'Outfit', sans-serif;">
                        <?= htmlspecialchars($ticket_code) ?>
                    </strong>
                    <div class="barcode-font"><?= htmlspecialchars($ticket_code) ?></div>
                    <small style="color: var(--text-dim); font-size: 10px; display: block; margin-top: -4px;">Txn Ref: <?= htmlspecialchars($booking['transaction_id'] ?? 'ONLINE') ?></small>
                </div>

                <div style="text-align: center;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=<?= urlencode($qr_data) ?>" alt="Ticket QR" style="width: 85px; height: 85px; background: #fff; padding: 4px; border-radius: 8px; border: 2px solid var(--accent); display: block;">
                    <small style="color: var(--text-dim); font-size: 10px; display: block; margin-top: 4px;">Gate Scanner</small>
                </div>
            </div>

        </div>

        <p class="no-print" style="text-align: center; color: var(--text-dim); font-size: 12px; margin-top: 20px;">
            <i class="fa-solid fa-circle-info"></i> Please present this digital QR code or printout at the cinema entrance. Doors open 15 minutes before showtime.
        </p>
    </div>

</body>
</html>
