<?php
// Inclui o arquivo de conexão com o banco de dados
include "..\..\conn.php";

// Verifica se a requisição foi feita via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se o formulário de cadastro foi enviado (campo oculto 'cadastro_form' com valor 1)
    if (isset($_POST['cadastro_form']) && $_POST['cadastro_form'] == 1) {

        // Coleta e limpa os dados enviados pelo formulário, garantindo que todas as variáveis estejam definidas
        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $data = isset($_POST['data']) ? trim($_POST['data']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
        $con_senha = isset($_POST['con_senha']) ? trim($_POST['con_senha']) : '';
        // Remove tudo que não for número do CPF para padronizar o dado
        $cpf = isset($_POST['cpf']) ? preg_replace('/[^0-9]/', '', $_POST['cpf']) : '';

        // Validação básica: verifica se algum campo obrigatório está vazio
        if (empty($nome) || empty($data) || empty($email) || empty($senha) || empty($con_senha) || empty($cpf)) {
            // Exibe alerta e retorna para a página anterior
            echo "<script>alert('Todos os campos são obrigatórios.'); history.back();</script>";
            exit();
        }

        // Verifica se as senhas digitadas conferem
        if ($senha !== $con_senha) {
            echo "<script>alert('As senhas não coincidem.'); history.back();</script>";
            exit();
        }

        // Verifica se o e-mail já está cadastrado no banco
        $stmt_email = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();
        $email_existe = $stmt_email->num_rows > 0; // True se encontrar um registro
        $stmt_email->close();

        // Verifica se o CPF já está cadastrado no banco
        $stmt_cpf = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $stmt_cpf->bind_param("s", $cpf);
        $stmt_cpf->execute();
        $stmt_cpf->store_result();
        $cpf_existe = $stmt_cpf->num_rows > 0; // True se encontrar um registro
        $stmt_cpf->close();

        // Se e-mail ou CPF já existirem, mostra alerta e retorna
        if ($email_existe || $cpf_existe) {
            echo "<script>alert('E-mail ou CPF já cadastrados.'); history.back();</script>";
            exit();
        }

        // Criptografa a senha utilizando algoritmo padrão do PHP (bcrypt por padrão)
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Prepara a query para inserir os dados do novo usuário
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, data_nascimento, cpf, email, senha) VALUES (?, ?, ?, ?, ?)");
        // Liga os parâmetros à query para evitar SQL Injection
        $stmt_insert->bind_param("sssss", $nome, $data, $cpf, $email, $senha_hash);

        // Executa a query e verifica se foi bem-sucedida
        if ($stmt_insert->execute()) {
            // Se cadastro deu certo, exibe alerta de sucesso e redireciona para a página de login
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = '../login.html';</script>";
        } else {
            // Se deu erro na inserção, exibe alerta e volta para a página anterior
            echo "<script>alert('Erro ao cadastrar usuário.'); history.back();</script>";
        }

        // Fecha a declaração preparada e a conexão com o banco
        $stmt_insert->close();
        $conn->close();
    }
}
?>
