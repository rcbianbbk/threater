<?php
session_start();
include_once 'db.php'; // तपाईंको database connection फाइल

// १. URL बाट टिकट ID आएको छ कि छैन हेर्ने
if (!isset($_GET['id'])) {
    echo "Error: Ticket ID (id) is missing in URL!";
    exit();
}

$ticket_id = intval($_GET['id']);
echo "Ticket ID is: " . $ticket_id . "<br>";

// २. bookings टेबलबाट डाटा तानेको
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    echo "Booking record found!<br>";

    // युजर आईडी कुन स्तम्भमा छ चेक गर्ने
    $target_user_id = $booking['user_id'] ?? $booking['client_id'] ?? $booking['customer_id'] ?? null;
    echo "Target User ID: " . ($target_user_id ? $target_user_id : "NOT FOUND (NULL)") . "<br>";

    $movie_name = $booking['movie_title'] ?? $booking['movie_name'] ?? $booking['title'] ?? 'Movie';
    echo "Movie Name: " . $movie_name . "<br>";

    if (!empty($target_user_id)) {
        // ३. notifications टेबलमा इन्सर्ट गर्न खोज्ने
        $msg = "बधाई छ! एडमिनले तपाईंको '$movie_name' मुभीको टिकट अप्रुभ गरे।";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, is_read, created_at) VALUES (?, ?, 'ticket_approval', 0, NOW())");
        
        if ($notif_stmt) {
            $notif_stmt->bind_param("is", $target_user_id, $msg);
            if ($notif_stmt->execute()) {
                echo "<h3 style='color: green;'>Success! Notification inserted into database successfully.</h3>";
            } else {
                echo "<h3 style='color: red;'>MySQL Insert Error: " . $conn->error . "</h3>";
            }
        } else {
            echo "<h3 style='color: red;'>Prepare Error: " . $conn->error . "</h3>";
        }
    } else {
        echo "<h3 style='color: red;'>Error: bookings टेबलमा यो टिकटसँग जोडिएको user_id फेला परेन!</h3>";
    }
} else {
    echo "<h3 style='color: red;'>Error: bookings टेबलमा ID $ticket_id भएको कुनै टिकट नै छैन!</h3>";
}
?>