<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // युजरले इन्पुट गरेको डेटा सुरक्षित रूपमा लिने
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        // डेटाबेसबाट प्रयोगकर्ता खोज्ने (तपाईंको टेबलको नाम 'users' वा आवश्यकता अनुसार मिलाउनुहोला)
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // पासवर्ड जाँच गर्ने (password_verify वा साधारण म्याच)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // लगइन सफल भएपछि सेसन सेट गर्ने
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                // होमपेज वा ड्यासबोर्डमा रिडिरेक्ट गर्ने
                header("Location: index.php");
                exit();
            } else {
                // गलत पासवर्ड भएमा
                header("Location: login.php?error=invalid_password");
                exit();
            }
        } else {
            // इमेल फेला नपरेमा
            header("Location: login.php?error=user_not_found");
            exit();
        }
        $stmt->close();
    } else {
        // खाली क्षेत्रहरू भएमा
        header("Location: login.php?error=empty_fields");
        exit();
    }
} else {
    // डायरेक्ट यो फाइल खोलेमा लगइन पेजमा पठाउने
    header("Location: login.php");
    exit();
}
?>