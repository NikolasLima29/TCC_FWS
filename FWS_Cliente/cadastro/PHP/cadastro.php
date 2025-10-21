<?php
include "../../conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['cadastro_form']) && $_POST['cadastro_form'] == 1) {

        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $data = isset($_POST['data']) ? trim($_POST['data']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
        $con_senha = isset($_POST['con_senha']) ? trim($_POST['con_senha']) : '';
        $cpf = isset($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : '';
        $telefone = isset($_POST['telefone']) ? preg_replace('/\D/', '', trim($_POST['telefone'])) : '';

        // Verifica se o usuário aceitou os termos
        if (!isset($_POST['termos'])) {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("Você deve concordar com os termos e políticas."));
            exit();
        }

        // Validação básica
        if (empty($nome) || empty($data) || empty($email) || empty($senha) || empty($con_senha) || empty($cpf) || empty($telefone)) {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("Todos os campos são obrigatórios."));
            exit();
        }

        // Verifica se as senhas conferem
        if ($senha !== $con_senha) {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("senhas não coincidem, tente novamente"));
            exit();
        }

        // Verifica existência do e-mail
        $stmt_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();
        $email_existe = $stmt_email->num_rows > 0;
        $stmt_email->close();

        // Verifica existência do CPF
        $stmt_cpf = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt_cpf->bind_param("s", $cpf);
        $stmt_cpf->execute();
        $stmt_cpf->store_result();
        $cpf_existe = $stmt_cpf->num_rows > 0;
        $stmt_cpf->close();

        // Verifica existência do telefone
        $stmt_telefone = $conn->prepare("SELECT id FROM usuarios WHERE telefone = ?");
        $stmt_telefone->bind_param("s", $telefone);
        $stmt_telefone->execute();
        $stmt_telefone->store_result();
        $telefone_existe = $stmt_telefone->num_rows > 0;
        $stmt_telefone->close();

        if ($email_existe || $cpf_existe || $telefone_existe) {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("E-mail, CPF ou Telefone já cadastrados."));
            exit();
        }

        // Criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Insere no banco
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, data_nascimento, telefone, cpf, email, senha) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("ssisss", $nome, $data, $telefone, $cpf, $email, $senha_hash);

        if ($stmt_insert->execute()) {
            header("Location: ../HTML/cadastro.html?status=sucesso&msg=" . urlencode("Cadastro realizado com sucesso!"));
            exit();
        } else {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("Erro ao cadastrar usuário."));
            exit();
        }

        $stmt_insert->close();
        $conn->close();
    }
}
?>
