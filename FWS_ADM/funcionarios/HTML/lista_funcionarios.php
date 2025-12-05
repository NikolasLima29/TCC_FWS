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

// Buscar todos os funcionários
$query = "SELECT id, nome, email, CPF, nivel_permissao, criado_em, ultimo_login FROM funcionarios ORDER BY id ASC";
$result = $sql->query($query);

// Função para traduzir nível de permissão
function nivelPermissao($nivel) {
    return ($nivel == 1) ? 'Atendente' : 'Gerente';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Funcionários</title>
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

    #cor-fonte { color: #ff9100; font-size: 23px; padding-bottom: 30px; }
    #cor-fonte:hover { background-color: #f4a21d67 !important; }
    #cor-fonte img { width: 44px; }
    #logo-linha img { width: 170px; }

    /* CONTEÚDO PRINCIPAL */
    #conteudo-principal { margin-left: 250px; padding: 40px; }

    .container {
        max-width: 1000px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 15px;
        color: #ff9100;
        font-weight: bold;
    }

    .btn-voltar {
        margin-bottom: 20px;
        background-color: #ff9100;
        color: white;
        font-weight: bold;
    }
    .btn-voltar:hover { background-color: #e68000; }

    table th, table td {
        text-align: center;
        vertical-align: middle;
    }

    .table thead.table-dark {
        background-color: #ff9100;
    }

    .table thead.table-dark th {
        background-color: #ff9100;
        color: white;
        border-color: #ff9100;
        border-right: 1px solid #e68000;
    }

    .table thead.table-dark th:last-child {
        border-right: none;
    }

    /* Nome com quebra de linha */
    .col-nome {
        max-width: 220px;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Sem quebra de linha em email, CPF e datas */
    .sem-quebra { white-space: nowrap; }

    /* Nível de permissão em vermelho */
    .nivel-vermelho { color: #d11b1b; font-weight: bold; }

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

                    <li class="nav-item">
                        <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte">
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

                    <li><a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/estoque.png">
                        <span class="ms-1 d-none d-sm-inline">Estoque</span>
                    </a></li>

                    <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/lista_produtos.php" class="nav-link align-middle px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/produtos.png">
                        <span class="ms-1 d-none d-sm-inline">Produtos</span>
                    </a></li>

                    <li><a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/lista_fornecedores.php" class="nav-link align-middle px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/fornecedor.png">
                        <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                    </a></li>

                    <li><a href="/TCC_FWS/FWS_ADM/funcionarios/HTML/lista_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/funcionarios.png">
                        <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                    </a></li>
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
            <div class="container">
                <a href="javascript:history.back()" class="btn btn-voltar">← Voltar</a>
                <h2>Funcionários Cadastrados</h2>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>CPF</th>
                            <th>Nível de permissão</th>
                            <th>Criado em</th>
                            <th>Último login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="sem-quebra"><?= $row['id'] ?></td>
                                    <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                    <td class="sem-quebra"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="sem-quebra"><?= htmlspecialchars($row['CPF']) ?></td>
                                    <td class="nivel-vermelho"><?= nivelPermissao($row['nivel_permissao']) ?></td>
                                    <td class="sem-quebra"><?= date('d/m/Y H:i', strtotime($row['criado_em'])) ?></td>
                                    <td class="sem-quebra"><?= $row['ultimo_login'] ? date('d/m/Y H:i', strtotime($row['ultimo_login'])) : '-' ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Nenhum funcionário cadastrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $sql->close(); ?>
