<?php
$page_title = "My Profile";
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$msg = "";
$err = "";

// Profile details update logic
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $phone, $user_id);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $msg = "Profile details updated successfully!";
        } else {
            $err = "Failed to update profile details.";
        }
    } else {
        $err = "Name cannot be empty.";
    }
}

// Profile Picture Upload Logic
if (isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $file_name = $_FILES['profile_pic']['name'];
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed)) {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_file_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $upload_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $update = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $update->bind_param("si", $new_file_name, $user_id);
                $update->execute();
                
                $msg = "Profile photo updated successfully!";
            } else {
                $err = "Could not upload file to server.";
            }
        } else {
            $err = "Only JPG, PNG, or WEBP formats are supported.";
        }
    } else {
        $err = "Please select a photo to upload.";
    }
}

// Fetch refreshed user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_initial = strtoupper(substr(trim($user['name']), 0, 1));

// User stats
$stats_res = $conn->query("SELECT COUNT(*) as total_bookings, COALESCE(SUM(total_amount), 0) as total_spent FROM bookings WHERE user_id = $user_id");
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total_bookings' => 0, 'total_spent' => 0];

include 'includes/header.php';
?>

<main class="container" style="margin-top: 30px; margin-bottom: 70px;">
    
    <div style="max-width: 750px; margin: 0 auto;">

        <?php if($msg != ""): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $msg ?></div>
        <?php endif; ?>

        <?php if($err != ""): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $err ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 25px; align-items: start; @media(max-width: 768px){ grid-template-columns: 1fr; }">
            
            <!-- Left Avatar & Stats Column -->
            <div class="glass-card" style="padding: 30px 20px; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 16px; border: 3px solid var(--accent); box-shadow: 0 0 20px var(--accent-glow); overflow: hidden; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #6366f1, #06b6d4);">
                    <?php if(!empty($user['profile_image']) && file_exists(__DIR__ . "/uploads/" . $user['profile_image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="font-size: 46px; font-weight: 800; color: #fff;"><?= $user_initial ?></span>
                    <?php endif; ?>
                </div>

                <h3 style="font-size: 19px; margin-bottom: 4px;"><?= htmlspecialchars($user['name']) ?></h3>
                <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;"><?= htmlspecialchars($user['email']) ?></p>

                <!-- Avatar Upload Form -->
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
                    <label for="profile_pic_input" class="btn btn-outline btn-sm" style="cursor: pointer; width: 100%; margin-bottom: 8px;">
                        <i class="fa-solid fa-camera"></i> Change Photo
                    </label>
                    <input type="file" id="profile_pic_input" name="profile_pic" accept="image/*" style="display: none;" onchange="this.form.submit_photo.click();">
                    <button type="submit" name="upload_photo" id="submit_photo" style="display: none;"></button>
                </form>

                <!-- Stats summary -->
                <div style="background: #090d16; border-radius: var(--radius-sm); padding: 15px; border: 1px solid var(--border-color); text-align: left;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-dim); font-size: 12px;">Total Bookings:</span>
                        <strong style="color: var(--accent);"><?= $stats['total_bookings'] ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim); font-size: 12px;">Total Spent:</span>
                        <strong style="color: #34d399;">Rs. <?= number_format($stats['total_spent'], 2) ?></strong>
                    </div>
                </div>

                <a href="my_bookings.php" class="btn btn-accent" style="width: 100%; margin-top: 15px;">
                    <i class="fa-solid fa-ticket"></i> View My Tickets
                </a>
            </div>

            <!-- Right Details & Settings Column -->
            <div class="glass-card" style="padding: 30px;">
                <h3 style="font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <i class="fa-solid fa-user-gear" style="color: var(--accent); margin-right: 8px;"></i> Account Details
                </h3>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address (Registered)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="opacity: 0.7; cursor: not-allowed;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+977 98XXXXXXXX">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top: 10px;">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </form>
            </div>

        </div>

    </div>

</main>

<?php include 'includes/footer.php'; ?>