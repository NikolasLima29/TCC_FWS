<?php
header('Content-Type: application/json; charset=utf-8');

define('GEMINI_API_KEY', 'AIzaSyCV7ZaUazXDACLiXnwBdYa9UuYiNPDDuXY');

// Fazer requisição para listar modelos
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://generativelanguage.googleapis.com/v1beta/models?key=' . GEMINI_API_KEY);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n\n";
echo $response;
?>
