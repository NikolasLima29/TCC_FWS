<?php
session_start();
include "../../conn.php";

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Busca dados do funcionário
$stmt = $sql->prepare("SELECT nome, cpf, email, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome, $cpf, $email, $nivel);
$stmt->fetch();
$stmt->close();

function nivel($n) {
    return $n == 1 ? "Atendente" : "Gerente";
}

// Foto padrão do funcionário
$foto = "../../fotodeperfiladm.png";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <style>
    /* NAVBAR */
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

    /* PERFIL - ANIMAÇÕES */
    #perfilBox {
        animation: fadeIn 0.45s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(25px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .foto-perfil {
        width: 130px;
        height: 130px;
        border-radius: 100%;
        border: 3px solid #f4a01d;
        object-fit: cover;
        display: block;
        margin: 0 auto 15px auto;
        transition: 0.25s ease;
    }

    .foto-perfil:hover {
        transform: scale(1.06);
    }

    /* ➕ ALTERAÇÃO DE SENHA */
    #boxSenha {
        display: none;
        margin-top: 25px;
        padding: 20px;
        border-radius: 10px;
        background: #fff3e0;
        animation: fadeSenha 0.4s ease;
    }

    @keyframes fadeSenha {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-senha {
        background-color: #ff5e00ff;
        color: white;
        font-weight: bold;
        width: 100%;
        margin-top: 20px;
    }

    .btn-senha:hover {
        background-color: #d11b1b;
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
                            <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a></li>
                    </ul>

                    <hr>

                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <img src="<?= $foto ?>" width="30" height="30" class="rounded-circle">
                            <span class="d-none d-sm-inline mx-1"><?= $nome ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- CONTEÚDO PERFIL -->
            <div class="col py-3" id="conteudo-principal">
                <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
                <div class="alert alert-<?php echo ($_GET['status'] == 'sucesso' ? 'success' : 'danger'); ?> alert-dismissible fade show"
                    role="alert" style="max-width: 650px; margin: 0 auto 20px auto; animation: fadeIn 0.4s;">
                    <strong><?php echo ($_GET['status'] == 'sucesso' ? 'Sucesso!' : 'Erro!'); ?></strong>
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="container" id="perfilBox">

                    <h2>Meu Perfil</h2>

                    <img src="<?= $foto ?>" class="foto-perfil" alt="Foto do Funcionário">

                    <form action="perfil_update.php" method="POST" id="formPerfil">

                        <label>Nome *</label>
                        <input type="text" class="form-control campo" name="nome" value="<?= $nome ?>" readonly>

                        <label class="mt-3">Email *</label>
                        <input type="email" class="form-control campo" name="email" value="<?= $email ?>" readonly>

                        <label class="mt-3">CPF</label>
                        <input type="text" id="cpf" class="form-control" value="<?= $cpf ?>" readonly>

                        <label class="mt-3">Nível de permissão</label>
                        <input type="text" class="form-control" value="<?= nivel($nivel) ?>" readonly>

                        <button type="button" id="btnEditar" class="btn btn-edit mt-4">Editar</button>
                        <a href="<?= $_SESSION['voltar_para'] ?? '/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html' ?>"
                            class="btn btn-secondary mt-2" id="btnVoltar">
                            Voltar
                        </a>



                        <div id="areaBotoes" style="display:none;">
                            <button type="button" id="btnCancelar" class="btn btn-cancel mt-3">Cancelar</button>
                            <button type="submit" class="btn btn-update mt-2">Atualizar</button>
                        </div>

                    </form>

                    <!-- 🔐 ALTERAÇÃO DE SENHA -->
                    <button class="btn btn-senha" id="btnSenha">Alterar Senha</button>

                    <div id="boxSenha">
                        <form method="POST" action="alterar_senha.php">

                            <label>Senha atual *</label>
                            <input type="password" class="form-control" name="senha_atual" required>

                            <label class="mt-3">Nova senha *</label>
                            <input type="password" class="form-control" name="nova_senha" required minlength="6">

                            <label class="mt-3">Confirmar nova senha *</label>
                            <input type="password" class="form-control" name="confirmar_senha" required>

                            <button type="submit" class="btn btn-update mt-3">Atualizar Senha</button>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Máscara CPF
        $("#cpf").mask("000.000.000-00");

        // Salvar valores originais
        let original = {
            nome: $("input[name='nome']").val(),
            email: $("input[name='email']").val()
        };

        // Botão Editar
        $("#btnEditar").click(function() {
            $(".campo").prop("readonly", false);
            $("#btnEditar, #btnVoltar").hide();
            $("#areaBotoes").show();
        });

        // Botão Cancelar
        $("#btnCancelar").click(function() {
            $("input[name='nome']").val(original.nome);
            $("input[name='email']").val(original.email);
            $(".campo").prop("readonly", true);
            $("#areaBotoes").hide();
            $("#btnEditar, #btnVoltar").show();
        });

        // ➕ Mostrar/Ocultar área de alteração de senha
        $("#btnSenha").click(function() {
            $("#boxSenha").slideToggle();
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>