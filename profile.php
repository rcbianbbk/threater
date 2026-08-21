<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$err = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Profile Picture Upload Logic
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file_name = $_FILES['profile_pic']['name'];
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $upload_path = "uploads/" . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update image in Database
                $update = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $update->bind_param("si", $new_file_name, $user_id);
                $update->execute();
                
                $msg = "Profile photo updated successfully!";
                header("Refresh:1");
            } else {
                $err = "Photo upload गर्न सकिएन!";
            }
        } else {
            $err = "कृपया JPG, PNG, वा WEBP Format को Photo मात्र हाल्नुहोस्!";
        }
    } else {
        $err = "कृपया Photo Select गर्नुहोस्!";
    }
}

// User Initial Calculation
$user_initial = strtoupper(substr(trim($user['name']), 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Cinema World</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #0b0f19; color: #f8fafc; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card { background: #1e293b; padding: 35px; border-radius: 16px; border: 1px solid #334155; width: 100%; max-width: 420px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #38bdf8; margin-bottom: 20px; }
        
        .avatar-container { width: 110px; height: 110px; border-radius: 50%; margin: 0 auto 20px; position: relative; border: 3px solid #38bdf8; overflow: hidden; box-shadow: 0 0 15px rgba(56, 189, 248, 0.4); }
        .avatar-img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-initial { width: 100%; height: 100%; background: linear-gradient(135deg, #38bdf8, #0284c7); color: #0f172a; font-size: 48px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        
        .user-info { margin-bottom: 25px; text-align: left; background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; }
        .user-info p { color: #94a3b8; font-size: 14px; margin-bottom: 5px; }
        .user-info strong { color: #f8fafc; font-size: 16px; }
        
        input[type="file"] { display: none; }
        .custom-file-upload { display: inline-block; padding: 10px 20px; background: #0f172a; border: 1px solid #38bdf8; color: #38bdf8; border-radius: 8px; cursor: pointer; font-weight: 600; margin-bottom: 15px; transition: 0.3s; }
        .custom-file-upload:hover { background: rgba(56, 189, 248, 0.1); }
        
        .btn-submit { width: 100%; padding: 12px; background: #38bdf8; color: #0f172a; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; }
        .btn-submit:hover { background: #0284c7; color: white; }
        
        .msg { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .err { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .back-link { display: block; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>👤 User Profile</h2>

    <?php if($msg != "") echo "<div class='msg'>$msg</div>"; ?>
    <?php if($err != "") echo "<div class='err'>$err</div>"; ?>

    <div class="avatar-container">
        <?php if (!empty($user['profile_image']) && file_exists("uploads/" . $user['profile_image'])): ?>
            <img src="uploads/<?= $user['profile_image'] ?>" class="avatar-img" alt="Profile Photo">
        <?php else: ?>
            <div class="avatar-initial"><?= $user_initial ?></div>
        <?php endif; ?>
    </div>

    <div class="user-info">
        <p>Name: <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        <p>Email: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <label for="file-upload" class="custom-file-upload">
            📸 Choose New Photo
        </label>
        <input id="file-upload" type="file" name="profile_pic" accept="image/*" onchange="document.getElementById('file-name').innerText = this.files[0].name;">
        <p id="file-name" style="font-size: 12px; color: #94a3b8; margin-bottom: 15px;"></p>
        
        <button type="submit" name="upload_photo" class="btn-submit">Upload Profile Photo</button>
    </form>

    <a href="index.php" class="back-link">⬅ Back to Home</a>
</div>

</body>
</html>