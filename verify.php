<?php
session_start();
require_once 'db.php';

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $status = 'Approved';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
    } else {
        header("Location: view_bookings.php");
        exit();
    }

    // Database status update
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        header("Location: view_bookings.php?success=1");
    } else {
        echo "Error updating status: " . $conn->error;
    }
    $stmt->close();
} else {
    header("Location: view_bookings.php");
}
exit();
?>