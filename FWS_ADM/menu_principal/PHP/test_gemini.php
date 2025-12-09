<?php
header('Content-Type: application/json');

define('GEMINI_API_KEY', 'AIzaSyDHLY5mzHLj1Xa-K_WvaFczSzLCckVCVms');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

$requestBody = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'Responda com um "ok" simples.'
                ]
            ]
        ]
    ]
];

echo json_encode(['status' => 'iniciando teste']);

// Fazer requisição à API Gemini
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => GEMINI_API_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-goog-api-key: ' . GEMINI_API_KEY
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestBody),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "\n\n";
echo "HTTP Code: " . $httpCode . "\n";
echo "CURL Error: " . ($curlError ?: "Nenhum") . "\n";
echo "Response Length: " . strlen($response) . "\n";
echo "Response: " . $response . "\n";
?>
