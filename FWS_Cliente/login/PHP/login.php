<?php
session_start();

// Conexão com o banco de dados
include "../../conn.php";

if ($sql->connect_error) {
    error_log("Erro na conexão com o banco: " . $sql->connect_error);
    header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
    exit;
}

$sql->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cpf_email = trim($_POST["cpf_email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    if (empty($cpf_email) || empty($senha)) {
        header("Location: ../../index.html?status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Consulta na tabela de USUÁRIOS
    $query = "SELECT * FROM usuarios WHERE cpf = ? OR email = ?";
    $stmt = $sql->prepare($query);

    if (!$stmt) {
        error_log("Erro ao preparar a query: " . $sql->error);
        header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
        exit;
    }

    $stmt->bind_param("ss", $cpf_email, $cpf_email);

    if (!$stmt->execute()) {
        error_log("Erro na execução da query: " . $stmt->error);
        header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
        exit;
    }

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido: registra informações na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];

            // Atualiza último login
            $update = $sql->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
            $update->bind_param("i", $usuario['id']);
            $update->execute();

            header("Location: ../../index.html?status=sucesso&msg=Login realizado com sucesso");
            exit;
        } else {
            // Senha incorreta
            header("Location: ../../index.html?status=erro&msg=Senha incorreta ou usuário não encontrado");
            exit;
        }
    } else {
        // Usuário não encontrado
        header("Location: ../../index.html?status=erro&msg=Senha incorreta ou usuário não encontrado");
        exit;
    }
}
?>
