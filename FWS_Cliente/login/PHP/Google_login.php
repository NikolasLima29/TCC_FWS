<?php
session_start();
require 'vendor/autoload.php';

// Substitua pelo seu Client ID gerado no Google Cloud Console
$CLIENT_ID = "310951678521-gr1qnsgde3hipgqcrgr1nkqts9c9lqlg.apps.googleusercontent.com";


if ($payload) {
    // Dados retornados do Google
    $google_id = $payload['sub'];
    $nome      = $payload['name'];
    $email     = $payload['email'];

    // Conexão ao banco
    include "..\..\conn.php";

    // Verifica se o usuário já existe no banco
    $sql = "SELECT * FROM usuarios WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Usuário já existe → login
        $user = $result->fetch_assoc();
    } else {
        // Se não existe → cadastra
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, google_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $google_id);
        $stmt->execute();
        $stmt->close();
    }

    // Salva sessão
    $_SESSION['nome']  = $nome;
    $_SESSION['email'] = $email;

    header("Location: ../../index.php");
    exit();
} else {
    echo "⚠ Token inválido.";
}
 else {
echo "⚠ Nenhum token recebido.";
}
