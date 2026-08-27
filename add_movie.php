<?php
include 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = trim($_POST['duration']);
    $price = trim($_POST['price']);
    $poster_url = trim($_POST['poster_url']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    $stmt = $conn->prepare("INSERT INTO movies (title, genre, duration, price, poster_url, description, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssdsss", $title, $genre, $duration, $price, $poster_url, $description, $status);
        if ($stmt->execute()) {
            $success = "Movie successfully added!";
        } else {
            $error = "Failed to add movie. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Movie - CineWorld</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #080808; color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px;">

    <div style="background: #141414; border: 1px solid rgba(229,9,20,0.3); padding: 35px; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.8);">
        <h2 style="margin-top: 0; color: #e50914; margin-bottom: 20px; font-size: 24px; font-weight: 700;"><i class="fa-solid fa-film"></i> Add New Movie</h2>
        
        <?php if($success): ?>
            <div style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: rgba(229, 9, 20, 0.2); color: #e50914; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Movie Title</label>
                <input type="text" name="title" required placeholder="e.g. Inception" style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Genre (e.g. Action / Sci-Fi)</label>
                <input type="text" name="genre" required placeholder="e.g. Action / Sci-Fi" style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Duration</label>
                    <input type="text" name="duration" required placeholder="e.g. 148 Mins" style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" required placeholder="400.00" style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Poster Image URL (IMDb / Direct Link)</label>
                <input type="url" name="poster_url" placeholder="https://m.media-amazon.com/images/..." required style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Status</label>
                <select name="status" style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px;">
                    <option value="Now Showing">Now Showing</option>
                    <option value="Coming Soon">Coming Soon</option>
                </select>
            </div>

            <div style="margin-bottom: 22px;">
                <label style="font-size: 13px; color: #aaa; display: block; margin-bottom: 6px; font-weight: 600;">Description</label>
                <textarea name="description" rows="3" placeholder="Enter short movie plot..." style="width: 100%; padding: 11px 14px; background: #1f1f1f; border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 8px; outline: none; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #e50914; color: #fff; border: none; padding: 13px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 14px; transition: background 0.2s;">Add Movie</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #888; text-decoration: none; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>

</body>
</html>