<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Seu código abaixo...

// Configurações de conexão com o banco
include "../../conn.php";


if ($sql->connect_error) {
    // Erro na conexão — logar e mostrar mensagem genérica
    error_log("Erro na conexão com o banco: " . $sql->connect_error);
    header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
    exit;
}

// Define charset para suportar caracteres especiais
$sql->set_charset("utf8");

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitiza e captura os dados enviados
    $cpf_email = trim($_POST["cpf_email"] ?? '');
    $senha = $_POST["senha"] ?? '';

    // Validação: campos não podem estar vazios
    if (empty($cpf_email) || empty($senha)) {
        header("Location: ../../index.html?status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Prepara a consulta SQL com prepared statements
    $query = "SELECT * FROM funcionarios WHERE cpf = ? OR email = ?";
    $stmt = $sql->prepare($query);

    if (!$stmt) {
        // Falha ao preparar a query — logar e mostrar erro genérico
        error_log("Erro ao preparar a query: " . $sql->error);
        header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
        exit;
    }

    // Associa os parâmetros
    $stmt->bind_param("ss", $cpf_email, $cpf_email);

    // Executa a query
    if (!$stmt->execute()) {
        // Falha na execução — logar e mostrar erro genérico
        error_log("Erro na execução da query: " . $stmt->error);
        header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
        exit;
    }

    $result = $stmt->get_result();

    // Verifica se o usuário foi encontrado
    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verifica se a senha fornecida corresponde ao hash
        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido — registra informações na sessão
            $_SESSION['usuario_id_ADM'] = $usuario['id'];
            $_SESSION['usuario_nome_ADM'] = $usuario['nome'];
            $_SESSION['usuario_email_ADM'] = $usuario['email'];

            // Atualiza o último login do funcionário
            $update = $sql->prepare("UPDATE funcionarios SET ultimo_login = NOW() WHERE id = ?");
            if ($update) {
                $update->bind_param("i", $usuario['id']);
                $update->execute();
                $update->close();
            }

            header("Location: ../../index.html?status=sucesso&msg=Login realizado com sucesso");
            exit;
        } else {
            // Senha incorreta
            header("Location: ../../index.html?status=erro&msg=Senha incorreta ou Usuário não encontrado");
            exit;
        }
    } else {
        // Nenhum usuário encontrado
        header("Location: ../../index.html?status=erro&msg=Senha incorreta ou Usuário não encontrado");
        exit;
    }
}
?>
