<?php
session_start();
include 'db.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = intval($_POST['movie_id'] ?? 0);
    $selected_seats_raw = trim($_POST['selected_seats'] ?? '');
    $selected_showtime = trim($_POST['show_time'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'eSewa / Khalti QR');
    $txn_id = trim($_POST['transaction_id'] ?? '');

    // Fetch movie price
    $movie_res = $conn->query("SELECT price FROM movies WHERE id = $movie_id");
    if (!$movie_res || $movie_res->num_rows === 0) {
        $_SESSION['booking_error'] = "Invalid movie selected.";
        header("Location: index.php");
        exit();
    }
    $movie = $movie_res->fetch_assoc();
    $unit_price = floatval($movie['price']);

    $seat_array = array_filter(array_map('trim', explode(',', $selected_seats_raw)));

    if (empty($seat_array)) {
        $_SESSION['booking_error'] = "Please select at least one seat!";
        header("Location: book.php?movie_id=" . $movie_id);
        exit();
    }

    if (empty($txn_id)) {
        $_SESSION['booking_error'] = "Please enter your Transaction ID / Reference Code!";
        header("Location: book.php?movie_id=" . $movie_id . "&show_time=" . urlencode($selected_showtime));
        exit();
    }

    // Re-verify if any seats were booked concurrently
    $fresh_booked_seats = [];
    $stmt_check = $conn->prepare("SELECT seat_number FROM bookings WHERE movie_id = ? AND show_time = ?");
    $stmt_check->bind_param("is", $movie_id, $selected_showtime);
    $stmt_check->execute();
    $check_res = $stmt_check->get_result();
    if ($check_res) {
        while ($row = $check_res->fetch_assoc()) {
            foreach (explode(',', $row['seat_number']) as $s) {
                $c = trim($s);
                if (!empty($c)) $fresh_booked_seats[] = $c;
            }
        }
    }

    $conflict = false;
    foreach ($seat_array as $st) {
        if (in_array($st, $fresh_booked_seats)) {
            $conflict = true;
            break;
        }
    }

    if ($conflict) {
        $_SESSION['booking_error'] = "One or more selected seats have just been booked by someone else. Please choose different seats.";
        header("Location: book.php?movie_id=" . $movie_id . "&show_time=" . urlencode($selected_showtime));
        exit();
    }

    // Calculate total amount
    $total_seats = count($seat_array);
    $total_amount = $total_seats * $unit_price;
    $joined_seats = implode(', ', $seat_array);
    $ticket_code = 'CW-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    // Insert booking into database securely
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, movie_id, seat_number, show_time, total_amount, payment_method, payment_status, transaction_id, ticket_code) VALUES (?, ?, ?, ?, ?, ?, 'Success', ?, ?)");
    $stmt->bind_param("iissdsss", $_SESSION['user_id'], $movie_id, $joined_seats, $selected_showtime, $total_amount, $payment_method, $txn_id, $ticket_code);

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        header("Location: ticket.php?id=" . $booking_id);
        exit();
    } else {
        $_SESSION['booking_error'] = "Database error: " . $conn->error;
        header("Location: book.php?movie_id=" . $movie_id);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>