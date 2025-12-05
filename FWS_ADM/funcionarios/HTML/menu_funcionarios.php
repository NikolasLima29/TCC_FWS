<?php
include "../../conn.php";
session_start();

/* ============================================================
   AUTENTICAÇÃO
   ============================================================ */
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

/* ============================================================
   CARREGAR DADOS DO ADM (NAVBAR)
   ============================================================ */
$stmt = $sql->prepare("SELECT nome, cpf, email, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome_adm, $cpf, $email, $nivel);
$stmt->fetch();
$stmt->close();

$foto = "../../fotodeperfiladm.png";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Funcionários</title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* ======== ESTILO GLOBAL ======== */

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
}

#menu {
    background-color: black;
}

#cor-fonte {
    color: #ff9100;
    font-size: 23px;
    padding-bottom: 30px;
    width: 100%;
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

/* ===========================
   TÍTULO
   =========================== */

.titulo {
    text-align: center;
    font-size: 42px;
    font-weight: 900;
    color: #ff9100;
    margin-top: 40px;
}

/* ===========================
   NOVOS CARDS EXATAMENTE COMO A IMAGEM
   =========================== */

#btn-container {
    margin-top: 160px;               /* DESCER OS BOTÕES */
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 90px;                       /* Espaço ideal entre eles */
}

.action-card {
    width: 410px;                    /* LARGURA GRANDE */
    height: 180px;                   /* ALTURA GRANDE */
    background: #ff9100;
    border-radius: 22px;
    cursor: pointer;

    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    transition: all .25s ease-in-out;
}

.action-card img {
    width: 82px;                     /* Ícone maior */
    opacity: 0.95;
    transition: .25s;
}

.action-card span {
    margin-top: 12px;
    font-size: 23px;
    font-weight: 800;
    color: black;
}

.action-card:hover {
    transform: translateY(-8px) scale(1.05);
    background: #ffa733;
    box-shadow: 0 16px 40px rgba(0,0,0,0.32);
}

.action-card:hover img {
    transform: scale(1.18);
    opacity: 1;
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
                            <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/TCC_FWS/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/TCC_FWS/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
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

                        <li><a href="/TCC_FWS/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
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
                            <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

        <!-- ================= CONTEÚDO PRINCIPAL ================= -->
        <div class="col py-3" id="conteudo-principal">

            <h1 class="titulo">Funcionários</h1>

            <div id="btn-container">

                <div class="action-card" onclick="window.location.href='cadastrar_funcionario.php'">
                    <img src="../../menu_principal/IMG/funcionarios.png">
                    <span>Cadastrar Funcionário</span>
                </div>

                <div class="action-card" onclick="window.location.href='lista_funcionarios.php'">
                    <img src="../../menu_principal/IMG/fastservice.png">
                    <span>Ver Lista de Funcionários</span>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $sql->close(); ?>
