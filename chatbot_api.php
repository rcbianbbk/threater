<?php
// chatbot_api.php (Temporary local mock response)
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$userMessage = strtolower(trim($data['message'] ?? ''));

if (empty($userMessage)) {
    echo json_encode(['reply' => 'कृपया केही लेख्नुहोस्।']);
    exit;
}

// Simple logic to test chat without external API key
if (str_contains($userMessage, 'movie') || str_contains($userMessage, 'moviw')) {
    $reply = "हामीसँग थुप्रै नयाँ मुभीहरू उपलब्ध छन्! तपाईंले होमपेजमा गएर बुक गर्न सक्नुहुन्छ।";
} else if (str_contains($userMessage, 'hello') || str_contains($userMessage, 'hey')) {
    $reply = "नमस्ते! आज तपाईंलाई कसरी सहयोग गर्न सक्छु?";
} else {
    $reply = "तपाईंको प्रश्न '" . htmlspecialchars($data['message']) . "' को लागि धन्यवाद। सिनेमा वर्ल्डमा तपाईंलाई स्वागत छ!";
}

echo json_encode(['reply' => $reply]);
?>