<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php'; // तपाईंको डाटाबेस जोड्ने फाइल

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];
    $description = trim($_POST['description']);
    $poster_image = trim($_POST['poster_image']);
    $trailer_url = trim($_POST['trailer_url']);
    
    // एडमिनले इन्पुट गरेको पासवर्ड
    $admin_password = $_POST['admin_password'];
    
    // तपाईंको गोप्य एडमिन पासवर्ड
    $secret_admin_pass = "admin123"; 

    // पासवर्ड प्रमाणिकरण
    if (empty($admin_password)) {
        $error = "कृपया कन्फर्मेसनको लागि एडमिन पासवर्ड राख्नुहोस्।";
    } elseif ($admin_password !== $secret_admin_pass) {
        $error = "गलत एडमिन पासवर्ड! चलचित्र थप्न सकिएन।";
    } else {
        // डाटाबेसमा सेभ गर्ने क्वेरी
        $sql = "INSERT INTO movies (title, genre, duration, price, status, description, poster_image, trailer_url) 
                VALUES ('$title', '$genre', '$duration', '$price', '$status', '$description', '$poster_image', '$trailer_url')";
        
        if ($conn->query($sql)) {
            $success = "चलचित्र सफलतापूर्वक थपियो!";
        } else {
            $error = "डेटाबेसमा समस्या: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Movie - Cinema World</title>
    <!-- यहाँ तपाईंको साइटको CSS र FontAwesome लिङ्कहरू राख्नुहोला -->
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #07090e; color: #fff; font-family: sans-serif;">

<div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px;">
    <div style="background: #0f172a; padding: 30px; border-radius: 16px; border: 1px solid rgba(99,102,241,0.3);">
        <h2 style="margin-bottom: 20px;"><i class="fa-solid fa-film"></i> Add New Movie</h2>

        <?php if(!empty($error)): ?>
            <div style="background: rgba(239,68,68,0.2); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div style="background: rgba(16,185,129,0.2); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Form सुरु भयो -->
        <form action="" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Movie Title</label>
                <input type="text" name="title" required style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Genre (विधा)</label>
                <input type="text" name="genre" required placeholder="Action / Drama" style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; margin-bottom:5px;">Duration (Mins)</label>
                    <input type="number" name="duration" required style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:5px;">Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" required style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Status</label>
                <select name="status" style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
                    <option value="now_showing">Now Showing</option>
                    <option value="coming_soon">Coming Soon</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Poster Image URL</label>
                <input type="text" name="poster_image" placeholder="https://image-link.com" style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Description</label>
                <textarea name="rows="3" style="width:100%; padding:10px; background:#1e293b; border:1px solid #334155; color:#fff; border-radius:6px;"></textarea>
            </div>

            <!-- एडमिन पासवर्ड प्रमाणीकरण सेक्सन -->
            <div style="margin: 20px 0; background: rgba(99,102,241,0.1); padding: 15px; border-radius: 8px; border: 1px dashed #6366f1;">
                <label style="display:block; margin-bottom:5px; color: #818cf8; font-weight: bold;">
                    <i class="fa-solid fa-lock"></i> Enter Admin Password to Confirm
                </label>
                <input type="password" name="admin_password" required placeholder="Enter admin password (e.g. admin123)" style="width:100%; padding:10px; background:#07090e; border:1px solid #6366f1; color:#fff; border-radius:6px;">
            </div>

            <button type="submit" style="width:100%; padding: 12px; background: #6366f1; color: #fff; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
                Add Movie
            </button>
        </form>
    </div>
</div>

</body>
</html>