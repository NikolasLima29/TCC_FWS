<?php
require_once 'vendor/autoload.php';
include 'conexao.php';
session_start();

// Seu CLIENT_ID do Google
$CLIENT_ID = "310951678521-gr1qnsgde3hipgqcrgr1nkqts9c9lqlg.apps.googleusercontent.com";

// Inicializa cliente do Google
$client = new Google_Client(['client_id' => $CLIENT_ID]);
$id_token = $_POST['id_token'] ?? null;

// Verifica o token enviado pelo Google
if ($id_token) {
    $payload = $client->verifyIdToken($id_token);
    if ($payload) {
        // Dados básicos do Google
        $google_id = $payload['sub'];
        $email = $payload['email'];
        $nome = $payload['name'];

        // Verifica se o usuário já existe no banco
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Usuário já existe
            $user = $result->fetch_assoc();

            if (empty($user['senha'])) {
                // Cria senha aleatória se não tiver
                $senha_aleatoria = bin2hex(random_bytes(8));
                $senha_hash = password_hash($senha_aleatoria, PASSWORD_DEFAULT);

                $update = $conn->prepare("UPDATE usuarios SET senha = ?, google_id = ? WHERE email = ?");
                $update->bind_param("sss", $senha_hash, $google_id, $email);
                $update->execute();
            } else {
                // Só atualiza o Google ID
                $update = $conn->prepare("UPDATE usuarios SET google_id = ? WHERE email = ?");
                $update->bind_param("ss", $google_id, $email);
                $update->execute();
            }

            // Inicia sessão
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];

            echo json_encode(["status" => "sucesso", "msg" => "Login Google concluído."]);

        } else {
            // Usuário novo → cria conta
            $senha_aleatoria = bin2hex(random_bytes(8));
            $senha_hash = password_hash($senha_aleatoria, PASSWORD_DEFAULT);

            $insert = $conn->prepare("INSERT INTO usuarios (nome, email, senha, google_id) VALUES (?, ?, ?, ?)");
            $insert->bind_param("ssss", $nome, $email, $senha_hash, $google_id);
            $insert->execute();

            $_SESSION['usuario_id'] = $conn->insert_id;
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_email'] = $email;

            echo json_encode(["status" => "sucesso", "msg" => "Usuário criado com login Google."]);
        }
    } else {
        echo "⚠ Token inválido.";
    }
} else if (!isset($_POST['id_token'])) {
    echo "⚠ Nenhum token recebido.";
}
?>
