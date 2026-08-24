<?php
$conn = new mysqli("localhost", "root", "", "your_database_name");

if (isset($_POST['image'])) {
    $imgData = $_POST['image'];
    $imgData = str_replace('data:image/png;base64,', '', $imgData);
    $imgData = str_replace(' ', '+', $imgData);
    $data = base64_decode($imgData);

    $fileName = 'ss_' . time() . '.png';
    $filePath = 'uploads/' . $fileName;

    if (file_put_contents($filePath, $data)) {
        // Filename lai Database ma insert garne
        $stmt = $conn->prepare("INSERT INTO screenshots (image_path) VALUES (?)");
        $stmt->bind_param("s", $fileName);
        $stmt->execute();
        echo "Screenshot Successfully Saved!";
    } else {
        echo "Failed to save image.";
    }
}
?>