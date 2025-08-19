<?php
// Cria a conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "FWS");

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Define o charset para UTF-8 (com suporte total a acentos e emojis)
$conn->set_charset("utf8mb4");
?>
