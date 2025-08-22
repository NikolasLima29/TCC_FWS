<?php
session_start();

// Conexão
$sql = new mysqli("localhost", "root", "", "FWS");
if ($sql->connect_error) {
    die("Erro na conexão: " . $sql->connect_error);
}
$sql->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cpf_email = trim($_POST["cpf_email"]);
    $senha = $_POST["senha"];

    if (empty($cpf_email) || empty($senha)) {
        header("Location: ../../index.html?status=erro&msg=Preencha todos os campos");
        exit;
    }

    $query = "SELECT * FROM funcionarios WHERE cpf = ? OR email = ?";
    $stmt = $sql->prepare($query);
    $stmt->bind_param("ss", $cpf_email, $cpf_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

            header("Location: ../../index.html?status=sucesso&msg=Login realizado com sucesso");
            exit;
        } else {
            header("Location: ../../index.html?status=erro&msg=Senha incorreta ou Usuário não encontrado ");
            exit;
        }
    } else {
        header("Location: ../../index.html?status=erro&msg=Senha incorreta ou Usuário não encontrado");
        exit;
    }
}
?>
