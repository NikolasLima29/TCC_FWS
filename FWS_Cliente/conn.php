<?php
// Cria a conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "FWS");

//$conn = new mysqli("162.241.2.71", "quaiat07_fws", "JO@O_M@TH_1234", "quaiat07_fws");


// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Define o charset para UTF-8 (com suporte total a acentos e emojis)
$conn->set_charset("utf8mb4");
?>
