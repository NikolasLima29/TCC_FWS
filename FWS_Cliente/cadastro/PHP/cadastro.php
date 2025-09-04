<?php
// Inclui o arquivo de conexão com o banco de dados
// Este arquivo deve conter a variável $conn com a conexão MySQLi
include "..\..\conn.php";

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Verifica se o campo oculto 'cadastro_form' existe e é igual a 1
    // Isso confirma que o envio veio do formulário de cadastro
    if (isset($_POST['cadastro_form']) && $_POST['cadastro_form'] == 1) {

        // Coleta e limpa os dados enviados pelo formulário
        // trim() remove espaços em branco extras do início e fim
        $nome       = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $data       = isset($_POST['data']) ? trim($_POST['data']) : '';
        $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
        $senha      = isset($_POST['senha']) ? trim($_POST['senha']) : '';
        $con_senha  = isset($_POST['con_senha']) ? trim($_POST['con_senha']) : '';
        // Remove qualquer caractere que não seja número do CPF
        $cpf        = isset($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : '';

        // Validação básica: verifica se algum campo obrigatório está vazio
        if (empty($nome) || empty($data) || empty($email) || empty($senha) || empty($con_senha) || empty($cpf)) {
            // Se algum campo estiver vazio, redireciona para a página de cadastro
            // usando GET para enviar uma mensagem de erro
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("Todos os campos são obrigatórios."));
            exit(); // Encerra a execução do script
        }

        // Verifica se as senhas digitadas conferem
        if ($senha !== $con_senha) {
         header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("senhas não coincidem, tente novamente"));

            exit();
        }

        // Verifica se o e-mail já está cadastrado no banco
        $stmt_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_email->bind_param("s", $email); // "s" indica string
        $stmt_email->execute();
        $stmt_email->store_result(); // Armazena resultado para verificar número de linhas
        $email_existe = $stmt_email->num_rows > 0; // True se encontrar um registro
        $stmt_email->close(); // Fecha a declaração preparada

        // Verifica se o CPF já está cadastrado no banco
        $stmt_cpf = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt_cpf->bind_param("s", $cpf);
        $stmt_cpf->execute();
        $stmt_cpf->store_result();
        $cpf_existe = $stmt_cpf->num_rows > 0;
        $stmt_cpf->close();

        // Se e-mail ou CPF já existirem, mostra erro
        if ($email_existe || $cpf_existe) {
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("E-mail ou CPF já cadastrados."));
            exit();
        }

        // Criptografa a senha utilizando algoritmo padrão do PHP (bcrypt por padrão)
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Prepara a query para inserir os dados do novo usuário
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, data_nascimento, cpf, email, senha) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nome, $data, $cpf, $email, $senha_hash);

        // Executa a query e verifica se foi bem-sucedida
        if ($stmt_insert->execute()) {
            // Cadastro realizado com sucesso
            header("Location: ../HTML/cadastro.html?status=sucesso&msg=" . urlencode("Cadastro realizado com sucesso!"));
            exit();
        } else {
            // Ocorreu algum erro na inserção
            header("Location: ../HTML/cadastro.html?status=erro&msg=" . urlencode("Erro ao cadastrar usuário."));
            exit();
        }

        // Fecha a declaração preparada e a conexão com o banco
        $stmt_insert->close();
        $conn->close();
    }
}
?>
