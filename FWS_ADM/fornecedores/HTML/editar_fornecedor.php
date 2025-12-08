<?php
include "../../conn.php";

session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Busca nome do ADM
$stmt = $sql->prepare("SELECT nome FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome_adm);
$stmt->fetch();
$stmt->close();

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];

// Verifica se existe ID na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Fornecedor inválido.");
}

$id = intval($_GET['id']);

// Busca dados do fornecedor
$stmt = $sql->prepare("SELECT nome, cnpj, telefone, email FROM fornecedores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("Fornecedor não encontrado.");
}

$stmt->bind_result($nome, $cnpj, $telefone, $email);
$stmt->fetch();
$stmt->close();


// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $novo_nome = trim($_POST['nome']);
    $novo_tel = preg_replace('/\D/', '', $_POST['telefone']);
    $novo_email = trim($_POST['email']);

    $update = $sql->prepare("UPDATE fornecedores SET nome=?, telefone=?, email=? WHERE id=?");
    $update->bind_param("sssi", $novo_nome, $novo_tel, $novo_email, $id);

    if ($update->execute()) {
        header("Location: editar_fornecedor.php?id=$id&status=sucesso&msg=Fornecedor atualizado com sucesso!");
        exit;
    } else {
        header("Location: editar_fornecedor.php?id=$id&status=erro&msg=Erro ao atualizar fornecedor");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Fornecedor</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <style>
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
    }

    #fund {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background-color: black !important;
        overflow-y: auto;
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

    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container {
        max-width: 650px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        color: #d11b1b;
        margin-bottom: 25px;
    }

    input[readonly] {
        background-color: #f5f5f5 !important;
        cursor: not-allowed;
    }

    .btn-edit {
        background-color: #f4a01d;
        font-weight: bold;
        border: none;
        width: 100%;
    }

    .btn-edit:hover {
        background-color: #d68c19;
        color: white;
    }

    .btn-cancel {
        background-color: #606060;
        color: white;
        width: 100%;
        font-weight: bold;
    }

    .btn-update {
        background-color: #28a745;
        color: white;
        width: 100%;
        font-weight: bold;
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
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- NAVBAR -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">

                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                        <li class="nav-item">
                            <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a></li>
                    </ul>

                    <hr>

                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <img src="../../fotodeperfiladm.png" width="30" height="30" class="rounded-circle">
                            <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="#../../perfil/HTML/perfil.php">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <h2>Editar Fornecedor</h2>

                    <!-- ALERTA -->
                    <?php if (isset($_GET['status'])) : ?>
                    <div class="alert <?= $_GET['status'] === 'erro' ? 'alert-danger' : 'alert-success' ?>">
                        <?= htmlspecialchars($_GET['msg']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- FORMULÁRIO -->
                    <form method="POST" id="formEditar">

                        <label>Nome *</label>
                        <input type="text" name="nome" id="nome" class="form-control"
                            value="<?= htmlspecialchars($nome); ?>" readonly>

                        <label class="mt-3">CNPJ *</label>
                        <input type="text" id="cnpj" class="form-control" value="<?= htmlspecialchars($cnpj); ?>"
                            readonly>

                        <label class="mt-3">Telefone</label>
                        <input type="text" id="telefone" name="telefone" class="form-control"
                            value="<?= htmlspecialchars($telefone); ?>" readonly>

                        <label class="mt-3">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="<?= htmlspecialchars($email); ?>" readonly>

                        <!-- BOTÕES -->
                        <button type="button" id="btnEditar" class="btn btn-edit mt-4">Editar</button>
                        <a href="lista_fornecedores.php" id="btnVoltar" class="btn btn-secondary mt-2">Voltar</a>

                        <div id="areaBotoes" style="display: none;">
                            <button type="button" id="btnCancelar" class="btn btn-cancel mt-3">Cancelar</button>
                            <button type="submit" class="btn btn-update mt-2">Atualizar</button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

    <script>
    $(document).ready(function() {

        // Função de máscara para CNPJ
        function mascaraCNPJ(cnpj) {
            return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
        }

        // Função de máscara para telefone
        function mascaraTel(t) {
            return t.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3")
                .replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
        }

        // Aplica a máscara manualmente no carregamento
        $("#cnpj").val(mascaraCNPJ($("#cnpj").val()));
        $("#telefone").val(mascaraTel($("#telefone").val()));

        // Mantém a máscara normal quando editar
        $("#telefone").mask("(00) 00000-0000");
    });
    </script>

    <script>
$(document).ready(function() {

    // Salvar valores originais
    let original = {
        nome: $("#nome").val(),
        telefone: $("#telefone").val(),
        email: $("#email").val()
    };

    // Quando clicar em EDITAR
    $("#btnEditar").click(function() {

        // Habilitar campos
        $("#nome, #telefone, #email").prop("readonly", false);

        // Mostrar botões de atualizar/cancelar
        $("#areaBotoes").show();

        // Esconder botão editar e voltar
        $("#btnEditar").hide();
        $("#btnVoltar").hide();   // <--- AGORA ELE SOME!

        // Aplicar máscara ao liberar edição
        $("#telefone").mask("(00) 00000-0000");
    });

    // Quando clicar em CANCELAR
    $("#btnCancelar").click(function() {

        // Restaurar valores originais
        $("#nome").val(original.nome);
        $("#telefone").val(original.telefone);
        $("#email").val(original.email);

        // Bloquear campos novamente
        $("#nome, #telefone, #email").prop("readonly", true);

        // Mostrar botões editar e voltar
        $("#btnEditar").show();
        $("#btnVoltar").show();   // <--- VOLTA AO NORMAL

        // Esconder botões atualizar/cancelar
        $("#areaBotoes").hide();
    });

});
</script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php $sql->close(); ?>
