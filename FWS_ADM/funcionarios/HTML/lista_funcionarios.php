<?php
include "../../conn.php";
session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Busca nome e nível do ADM
$stmt = $sql->prepare("SELECT nome, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome_adm, $nivel_adm);
$stmt->fetch();
$stmt->close();

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];

// Buscar todos os funcionários
$query = "SELECT id, nome, email, CPF, nivel_permissao, criado_em, ultimo_login FROM funcionarios ORDER BY id ASC";
$result = $sql->query($query);

// Função para traduzir nível de permissão
function nivelPermissao($nivel) {
    if ($nivel == 1) return "Atendente";
    if ($nivel == 2) return "Gerente";
    if ($nivel == 3) return "Gerente";
    return "Desconhecido";
}

$pagina = 'funcionarios';

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Funcionários</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../menu_principal/CSS/menu_principal.css">
  
    <style>
        @import url('../../Fonte_Config/fonte_geral.css');
    body {
        background-color: #fff8e1;
        overflow-x: hidden;
        animation: fadeIn 0.5s ease;
        margin: 0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

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

    #texto {
        text-align: center;
        font-size: 80px;
        height: 140px;
    }

    #menu {
        background-color: black;
    }

    #fund {
        background-color: black !important;
    }

    #cor-fonte {
        color: #ff9100;
        font-size: 21px;
        padding-bottom: 13px;
    }

    #cor-fonte img{
        width: 32px;
    }

    #cor-fonte:hover {
        background-color: #f4a21d67 !important;
    }

    #logo-linha img {
        width: 150px;
    }

    .nav-link {
        width: 100%;
        display: block;
        border-radius: 10px;
    }

    .nav-link.active {
        background-color: #f4a21d67 !important;
        border-radius: 5px;
    }


    /* CONTEÚDO PRINCIPAL */
    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container {
        max-width: 1100px;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.5s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    h2 {
        text-align: center;
        color: #ff9100;
        font-weight: bold;
        margin-bottom: 20px;
    }

    /* BOTÕES TOPO */
    .btn-voltar,
    .btn-cadastrar {
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
        transition: all .2s ease;
        color: white;
    }

    .btn-voltar {
        background-color: #e68000;
    }

    .btn-cadastrar {
        background-color: #e68000;
    }

    .btn-voltar:hover,
    .btn-cadastrar:hover {
        transform: scale(1.05);
        box-shadow: 0px 4px 10px rgba(255, 138, 0, 0.4);
    }

    /* TABELA */
    .table thead.table-dark {
        background-color: #ff9100;
    }

    .table thead.table-dark th {
        background-color: #ff9100;
        color: white;
        border-color: #ff8800;
    }

    tr {
        transition: all .18s ease;
    }

    tr:hover {
        background-color: #fff3cd;
        transform: scale(1.006);
    }

    .col-nome {
        max-width: 220px;
        word-wrap: break-word;
    }

    .sem-quebra {
        white-space: nowrap;
    }

    .nivel-vermelho {
        color: #d11b1b;
        font-weight: bold;
    }

    .btn-editar {
        background-color: #007bff;
        color: white;
        border-radius: 6px;
        padding: 6px 12px;
        font-weight: bold;
        transition: .2s;
        text-decoration: none;
    }

    .btn-editar:hover {
        background-color: #0056b3;
        transform: scale(1.07);
    }

    /* Melhorias responsivas para evitar corte da listagem */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 6px 8px !important;
        font-size: 0.95rem;
        white-space: normal;
        word-break: break-word;
    }

    .col-nome {
        max-width: 180px;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Em telas pequenas permitir quebra até mesmo em campos marcados como sem-quebra */
    @media (max-width: 900px) {
        .table th,
        .table td {
            font-size: 0.88rem;
            padding: 6px 6px !important;
        }
        .sem-quebra {
            white-space: normal;
        }
        .container {
            padding: 20px;
        }
    }

    a {
        text-decoration: none !important;
    }

    a:hover {
        text-decoration: none !important;
    }


    @import url('../../Fonte_Config/fonte_geral.css');
    </style>

</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- NAVBAR LATERAL COMPLETA -->
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

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span></a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span></a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span></a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span></a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span></a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span></a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php"
                                class="nav-link align-middle px-0 <?php if($pagina=='funcionarios') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span></a></li>
                    </ul>

                    <hr class="text-white">

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

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <!-- BOTÕES NO TOPO -->
                    <div class="d-flex justify-content-between mb-3">
                        <a href="menu_funcionarios.php" class="btn-voltar">← Voltar</a>
                        <a href="cadastrar_funcionario.php" class="btn-cadastrar">+ Cadastrar Funcionário</a>
                    </div>

                    <h2>Funcionários Cadastrados</h2>

                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>CPF</th>
                                <th>Nível</th>
                                <th>Criado em</th>
                                <th>Último login</th>
                                <?php if ($nivel_adm == 2 || $nivel_adm == 3): ?>
                                <th>Ações</th>
                                <?php endif; ?>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="sem-quebra"><?= $row['id'] ?></td>
                                <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                <td class="sem-quebra"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="sem-quebra"><?= htmlspecialchars($row['CPF']) ?></td>
                                <td class="nivel-vermelho"><?= nivelPermissao($row['nivel_permissao']) ?></td>
                                <td class="sem-quebra"><?= date('d/m/Y H:i', strtotime($row['criado_em'])) ?></td>
                                <td class="sem-quebra">
                                    <?= $row['ultimo_login'] ? date('d/m/Y H:i', strtotime($row['ultimo_login'])) : '-' ?>
                                </td>

                                <?php if ($nivel_adm == 2 || $nivel_adm == 3): ?>
                                <td>
                                    <a href="editar_funcionario.php?id=<?= $row['id'] ?>" class="btn-editar">Editar</a>
                                </td>
                                <?php endif; ?>

                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8">Nenhum funcionário encontrado.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // animação das linhas
    document.querySelectorAll("tbody tr").forEach((tr, i) => {
        tr.style.opacity = "0";
        tr.style.transform = "translateY(10px)";
        setTimeout(() => {
            tr.style.transition = "0.35s";
            tr.style.opacity = "1";
            tr.style.transform = "translateY(0)";
        }, 80 * i);
    });
    </script>

</body>

</html>

<?php $sql->close(); ?>