<?php
session_start();
include "../../conn.php";

if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];
$nome = trim($_POST["nome"]);
$email = trim($_POST["email"]);

if (empty($nome) || empty($email)) {
    header("Location: perfil.php?status=erro&msg=Preencha todos os campos");
    exit;
}

$stmt = $sql->prepare("UPDATE funcionarios SET nome = ?, email = ? WHERE id = ?");
$stmt->bind_param("ssi", $nome, $email, $id);

if ($stmt->execute()) {
    $_SESSION['usuario_nome_ADM'] = $nome;
    $_SESSION['usuario_email_ADM'] = $email;

    header("Location: perfil.php?status=sucesso&msg=Dados atualizados com sucesso");
} else {
    header("Location: perfil.php?status=erro&msg=Erro ao atualizar");
}
?>
