<?php
include "../../conn.php";
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION['novo_funcionario']['id'])) {
    echo json_encode(["status" => "erro", "msg" => "Funcionário não encontrado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$senha = $data['senha_auto'] ?? '';

if (empty($senha)) {
    echo json_encode(["status" => "erro", "msg" => "Senha inválida"]);
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);
$id   = $_SESSION['novo_funcionario']['id'];

$stmt = $sql->prepare("UPDATE funcionarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "erro", "msg" => $stmt->error]);
}
