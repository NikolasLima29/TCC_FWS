<?php
session_start();
require "../../conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cadastro_form']) && $_POST['cadastro_form'] == 1) {
        // Captura dos dados e limpeza
        $nome = trim($_POST['nome']);
        $data = trim($_POST['data']);
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);
        $con_senha = trim($_POST['con_senha']);
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // limpa o CPF para conter apenas números

        // Verificação de senha e confirmação
        if ($senha !== $con_senha) {
            echo "<script>alert('As senhas não coincidem.'); history.back();</script>";
            exit();
        }

        // Verifica se e-mail ou CPF já estão cadastrados
        $stmt_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();
        $email_existe = $stmt_email->num_rows > 0;
        $stmt_email->close();

        $stmt_cpf = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt_cpf->bind_param("s", $cpf);
        $stmt_cpf->execute();
        $stmt_cpf->store_result();
        $cpf_existe = $stmt_cpf->num_rows > 0;
        $stmt_cpf->close();

        if ($email_existe || $cpf_existe) {
            echo "<script>alert('E-mail ou CPF já cadastrados.'); history.back();</script>";
            exit();
        }

        // Criptografar senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir no banco de dados
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, data_nascimento, cpf, email, senha) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nome, $data, $cpf, $email, $senha_hash);

        if ($stmt_insert->execute()) {
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = '../login.html';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar usuário.'); history.back();</script>";
        }

        $stmt_insert->close();
        $conn->close();
    }
}
?>
