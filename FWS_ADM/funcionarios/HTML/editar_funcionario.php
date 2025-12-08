<?php
include "../../conn.php";
session_start();

// Impede acesso sem login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id_admin = $_SESSION['usuario_id_ADM'];

// Busca dados do ADM logado
$stmt = $sql->prepare("SELECT nome, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id_admin);
$stmt->execute();
$stmt->bind_result($nome_adm, $nivel_admin);
$stmt->fetch();
$stmt->close();

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];
// Somente níveis 2 e 3 podem editar
if ($nivel_admin < 2) {
    die("<h2 style='color:red; text-align:center;'>Acesso negado!</h2>");
}

// Verifica ID recebido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<h2 style='color:red; text-align:center;'>Funcionário inválido!</h2>");
}

$func_id = $_GET['id'];

// Busca funcionário
$stmt = $sql->prepare("SELECT nome, email, CPF, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $func_id);
$stmt->execute();
$stmt->bind_result($func_nome, $func_email, $func_cpf, $func_nivel);
$stmt->fetch();
$stmt->close();

// Se não existir
if (!$func_nome) {
    die("<h2 style='color:red; text-align:center;'>Funcionário não encontrado!</h2>");
}



$msg = "";

// Salvar alterações
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novo_nome = $_POST["nome"];
    $novo_email = $_POST["email"];
    $novo_nivel = $_POST["nivel"];

    // Impede alteração do próprio nível
    if ($func_id == $id_admin && $novo_nivel != $func_nivel) {
        $msg = "<div class='alert alert-danger'>Você não pode alterar seu próprio nível de permissão!</div>";
    }
    // Garantia de segurança: usuário nível 2 não pode atribuir nível Master
   
    else {
        $stmt = $sql->prepare("
            UPDATE funcionarios 
            SET nome = ?, email = ?, nivel_permissao = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssii", $novo_nome, $novo_email, $novo_nivel, $func_id);

        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success'>Dados atualizados com sucesso!</div>";
            $func_nome = $novo_nome;
            $func_email = $novo_email;
            $func_nivel = $novo_nivel;
        } else {
            $msg = "<div class='alert alert-danger'>Erro ao atualizar!</div>";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Funcionário</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #fff8e1;
            font-family: "Poppins", sans-serif;
            margin: 0;
        }

        /* NAVBAR LATERAL */
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

        #menu { background-color: black; }

        #cor-fonte {
            color: #ff9100;
            font-size: 23px;
            padding-bottom: 30px;
        }
        #cor-fonte:hover { background-color: #f4a21d67 !important; }
        #cor-fonte img { width: 44px; }
        #logo-linha img { width: 170px; }

        /* CONTEÚDO */
        #conteudo-principal {
            margin-left: 250px;
            padding: 40px;
        }

        .container-box {
            background: white;
            padding: 30px;
            max-width: 700px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #ff9100;
            font-weight: bold;
        }

        .btn-voltar {
            background-color: #ff9100;
            color: white;
            font-weight: bold;
        }
        .btn-voltar:hover { background-color: #e68000; }

        .btn-salvar {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .btn-salvar:hover { background-color: #1f7e33; }

        .form-control[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        @import url('../../Fonte_Config/fonte_geral.css');
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- NAVBAR -->
        <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                    <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                    <li class="nav-item"><a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/painelgeral.png"><span class="ms-1 d-none d-sm-inline">Painel Geral</span></a></li>

                    <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/fastservice.png"><span class="ms-1 d-none d-sm-inline">Fast Service</span></a></li>

                    <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/financeiro.png"><span class="ms-1 d-none d-sm-inline">Financeiro</span></a></li>

                    <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/vendaspai.png"><span class="ms-1 d-none d-sm-inline">Vendas</span></a></li>

                    <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/estoque.png"><span class="ms-1 d-none d-sm-inline">Estoque</span></a></li>

                    <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/produtos.png"><span class="ms-1 d-none d-sm-inline">Produtos</span></a></li>

                    <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/fornecedor.png"><span class="ms-1 d-none d-sm-inline">Fornecedores</span></a></li>

                    <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link px-0" id="cor-fonte"><img src="../../menu_principal/IMG/funcionarios.png"><span class="ms-1 d-none d-sm-inline">Funcionários</span></a></li>
                </ul>

                <hr>

                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="../../fotodeperfiladm.png" width="30" height="30" class="rounded-circle">
                        <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow">
                        <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="col py-3" id="conteudo-principal">
            <div class="container-box">

                <a href="lista_funcionarios.php" class="btn btn-voltar mb-3">← Voltar</a>

                <h2>Editar Funcionário</h2>

                <?= $msg ?>

                <form method="POST">

                    <label class="form-label">Nome:</label>
                    <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($func_nome) ?>" required>

                    <label class="form-label mt-3">Email:</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($func_email) ?>" required>

                    <label class="form-label mt-3">CPF (não editável):</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($func_cpf) ?>" readonly>

                    <label class="form-label mt-3">Nível de Permissão:</label>
                    <?php if ($func_id == $id_admin): ?>
                        <div class="alert-info">Você não pode alterar seu próprio nível de permissão.</div>
                    <?php endif; ?>
                    <select class="form-select" name="nivel" <?= ($func_id == $id_admin ? "disabled" : "") ?> required>
                        <option value="1" <?= ($func_nivel == 1 ? "selected" : "") ?>>Atendente</option>
                        <option value="2" <?= ($func_nivel == 2 ? "selected" : "") ?>>Gerente</option>
                    </select>

                    <button type="submit" class="btn btn-salvar mt-4 w-100">Salvar Alterações</button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $sql->close(); ?>
