<?php
session_start();
include "../../conn.php";

// Impede acesso sem login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Verifica método
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: perfil.php?status=erro&msg=Requisição inválida");
    exit;
}

$senha_atual = $_POST["senha_atual"] ?? "";
$nova_senha = $_POST["nova_senha"] ?? "";
$confirmar_senha = $_POST["confirmar_senha"] ?? "";

// ---------- Validações ----------
if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
    header("Location: perfil.php?status=erro&msg=Preencha todos os campos");
    exit;
}

if ($nova_senha !== $confirmar_senha) {
    header("Location: perfil.php?status=erro&msg=As senhas informadas não coincidem");
    exit;
}

if (strlen($nova_senha) < 6) {
    header("Location: perfil.php?status=erro&msg=A nova senha deve ter pelo menos 6 caracteres");
    exit;
}

// ---------- Busca senha atual no banco ----------
$stmt = $sql->prepare("SELECT senha FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($senha_hash_atual);
$stmt->fetch();
$stmt->close();

// Caso a conta não exista
if (!$senha_hash_atual) {
    header("Location: perfil.php?status=erro&msg=Erro ao buscar os dados do usuário");
    exit;
}

// ---------- Verifica senha atual ----------
if (!password_verify($senha_atual, $senha_hash_atual)) {
    header("Location: perfil.php?status=erro&msg=Senha atual incorreta!");
    exit;
}

// ---------- Gera hash da nova senha ----------
$novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

// ---------- Atualiza a senha ----------
$stmt = $sql->prepare("UPDATE funcionarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $novo_hash, $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: perfil.php?status=sucesso&msg=Senha alterada com sucesso!");
    exit;
} else {
    $stmt->close();
    header("Location: perfil.php?status=erro&msg=Erro ao atualizar a senha");
    exit;
}
?>
