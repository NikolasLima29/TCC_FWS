<?php
// ARQUIVO: logar_novo_funcionario.php
include "../../conn.php";
session_start();

header("Content-Type: application/json");

// Verifica se existe funcionário recém cadastrado
if (!isset($_SESSION['novo_funcionario'])) {
    echo json_encode(["status" => "erro", "msg" => "Nenhum funcionário cadastrado na sessão"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$senha_auto = $data['senha_auto'] ?? '';

if (empty($senha_auto)) {
    echo json_encode(["status" => "erro", "msg" => "Senha não enviada"]);
    exit;
}

$func = $_SESSION['novo_funcionario'];
$func_id = $func['id'];
$func_nome = $func['nome'];
$func_email = $func['email'];

// Salva hash da senha
$hash = password_hash($senha_auto, PASSWORD_DEFAULT);

$stmt = $sql->prepare("UPDATE funcionarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $func_id);

if (!$stmt->execute()) {
    echo json_encode(["status" => "erro", "msg" => $stmt->error]);
    exit;
}

$stmt->close();

/* ===========================================================
    LOGA O FUNCIONÁRIO NO SISTEMA
   =========================================================== */

// Apaga sessão do ADM
session_unset();
session_destroy();

// Inicia nova sessão para o FUNCIONÁRIO
session_start();

$_SESSION['usuario_id_ADM'] = $func_id;
$_SESSION['usuario_nome_ADM'] = $func_nome;     // Nome exibe no logout
$_SESSION['usuario_email_ADM'] = $func_email;   // Opcional mas recomendado

// Atualiza o último login do funcionário recém-logado
$update = $sql->prepare("UPDATE funcionarios SET ultimo_login = NOW() WHERE id = ?");
if ($update) {
    $update->bind_param("i", $func_id);
    $update->execute();
    $update->close();
}

echo json_encode(["status" => "ok"]);
exit;

?>
