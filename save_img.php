<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['image'])) {
        $imgData = $data['image'];
        
        // Base64 string bata header hataune
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        
        $decodedImage = base64_decode($imgData);

        // Uploads folder nabhaye banaune
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Unique image name banaune
        $fileName = 'receipt_' . time() . '_' . rand(1000, 9999) . '.png';
        $filePath = 'uploads/' . $fileName;

        if (file_put_contents($filePath, $decodedImage)) {
            echo json_encode([
                'status' => 'success',
                'file_path' => $filePath
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save image'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No image data provided'
        ]);
    }
}
?>