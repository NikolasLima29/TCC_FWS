<?php
include "../../conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpando e preparando os dados
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
    $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validação básica
    if ($nome === '' || $cnpj === '') {
        header("Location: cadastro_fornecedor.php?status=erro&msg=Preencha todos os campos obrigatórios");
        exit;
    }

    // Verificar duplicidade de CNPJ
    $stmt = $sql->prepare("SELECT COUNT(*) FROM fornecedores WHERE cnpj = ?");
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header("Location: cadastro_fornecedor.php?status=erro&msg=Fornecedor com este CNPJ já cadastrado");
        exit;
    }

    // Inserir no banco
    $stmt = $sql->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $cnpj, $telefone, $email);

    if (!$stmt->execute()) {
        die("Erro ao cadastrar: " . $stmt->error);
    }

    $stmt->close();

    header("Location: cadastro_fornecedor.php?status=sucesso&msg=Fornecedor cadastrado com sucesso!");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Fornecedor</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <style>
        body {
            background-color: #fff8e1;
            font-family: "Poppins", sans-serif;
            margin: 0;
        }

        /* Barra lateral fixa */
        #fund {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: black !important;
            overflow-y: auto;
            z-index: 1000;
        }

        #menu {
            background-color: black;
        }

        #cor-fonte {
            color: #ff9100;
            font-size: 23px;
            padding-bottom: 30px;
        }

        #cor-fonte:hover {
            background-color: #f4a21d67 !important;
        }

        #cor-fonte img {
            width: 44px;
        }

        #logo-linha img {
            width: 170px;
        }

        /* Área principal */
        #conteudo-principal {
            margin-left: 250px;
            padding: 40px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #d11b1b;
            font-weight: bold;
        }

        label {
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #f4a01d;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .btn-primary {
            background-color: #f4a01d;
            border: none;
            color: black;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #d68c19;
            color: white;
        }

        .btn-secondary {
            background-color: #d11b1b;
            border: none;
            color: white;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }
    </style>

    <script>
        $(document).ready(function () {
            $("#cnpj").mask("00.000.000/0000-00");
            $("#telefone").mask("(00) 00000-0000");
        });
    </script>

</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- Barra lateral -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo"></li>

                        <li class="nav-item">
                            <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png" alt="Painel Geral">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img
                                    src="../../menu_principal/IMG/fastservice.png" alt="Fast Service"> <span
                                    class="ms-1 d-none d-sm-inline">Fast Service</span></a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img
                                    src="../../menu_principal/IMG/financeiro.png" alt="Financeiro"> <span
                                    class="ms-1 d-none d-sm-inline">Financeiro</span></a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img
                                    src="../../menu_principal/IMG/vendaspai.png" alt="Vendas"> <span
                                    class="ms-1 d-none d-sm-inline">Vendas</span></a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte"><img src="../../menu_principal/IMG/estoque.png" alt="Estoque"> <span
                                    class="ms-1 d-none d-sm-inline">Estoque</span></a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php"
                                class="nav-link align-middle px-0" id="cor-fonte"><img
                                    src="../../menu_principal/IMG/produtos.png" alt="Produtos"> <span
                                    class="ms-1 d-none d-sm-inline">Produtos</span></a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/listar_fornecedores.php" class="nav-link align-middle px-0"
                                id="cor-fonte"><img src="../../menu_principal/IMG/fornecedor.png" alt="Fornecedores"> <span
                                    class="ms-1 d-none d-sm-inline">Fornecedores</span></a></li>
                    </ul>
                </div>
            </div>

            <!-- Conteúdo principal -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">
                    <h2>Cadastro de Fornecedor</h2>

                    <!-- ALERTA -->
                    <?php if (isset($_GET['status']) && isset($_GET['msg'])) : ?>
                        <div
                            class="alert <?php echo $_GET['status'] == 'erro' ? 'alert-danger' : 'alert-success'; ?>">
                            <?= htmlspecialchars($_GET['msg']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORMULÁRIO -->
                    <form method="POST" novalidate>

                        <label>Nome do Fornecedor *</label>
                        <input type="text" name="nome" required>

                        <label>CNPJ *</label>
                        <input type="text" id="cnpj" name="cnpj" maxlength="18" required>

                        <label>Telefone</label>
                        <input type="text" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000">

                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="exemplo@dominio.com">

                        <button type="submit" class="btn btn-primary mt-3">Cadastrar</button>
                        <a href="listar_fornecedores.php" class="btn btn-secondary mt-2">Voltar</a>
                    </form>

                </div>
            </div>
        </div>
    </div>

</body>

</html>

<?php $sql->close(); ?>
