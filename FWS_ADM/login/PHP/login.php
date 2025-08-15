<?php
session_start();

// Conexão com o banco
$sql = new mysqli("localhost", "root", "", "FWS");
if ($sql->connect_error) {
    die("Erro na conexão: " . $sql->connect_error);
}
$sql->set_charset("utf8");

// Verificar se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cpf_email = trim($_POST["cpf_email"]);
    $senha = $_POST["senha"];

    if (empty($cpf_email) || empty($senha)) {
        echo "<script>alert('Por favor, preencha todos os campos.'); window.location.href='../HTML/login.html';</script>";
        exit;
    }

    // Consulta SQL
    $query = "SELECT * FROM funcionarios WHERE cpf = ? OR email = ?";
    $stmt = $sql->prepare($query);
    $stmt->bind_param("ss", $cpf_email, $cpf_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar se o usuário existe
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verifica a senha usando password_verify
        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

            header("Location: ../menu_principal/HTML/menu_principal.html");
            exit;
        } else {
            echo "<script>alert('Senha incorreta.'); window.location.href='../../index.html';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Usuário não encontrado.'); window.location.href='../../index.html';</script>";
        exit;
    }
}
?>
