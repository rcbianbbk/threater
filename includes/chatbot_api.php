<?php
header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($data['message']) ? trim($data['message']) : '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please enter a message!']);
    exit;
}

// Your Gemini API Key (Yeta timro API key paste gara)
$apiKey = "YOUR_GEMINI_API_KEY_HERE"; 

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

// System instruction to make it act like CineWorld Concierge
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "You are CineWorld Concierge, a VIP Box Office Support chatbot for a cinema located at Durbar Marg, Kathmandu, Nepal. Answer the user's question politely in Nepali and English regarding movies, ticket booking, payment methods (easeva/khalti), refunds, and location. User's question: " . $userMessage]
            ]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// Extract reply from Gemini response
$botReply = "Sorry, I am having trouble connecting right now.";
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $botReply = $result['candidates'][0]['content']['parts'][0]['text'];
}

echo json_encode(['reply' => $botReply]);
?>