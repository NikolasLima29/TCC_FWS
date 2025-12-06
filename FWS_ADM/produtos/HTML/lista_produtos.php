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

// Buscar todos os produtos
$query = "
    SELECT 
        p.id, 
        p.nome, 
        c.nome AS categoria, 
        f.nome AS fornecedor, 
        p.preco_venda, 
        p.preco_compra, 
        p.estoque, 
        p.status 
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
    ORDER BY p.id ASC
";
$result = $sql->query($query);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Produtos</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
    background-color: #fff8e1;
    font-family: "Poppins", sans-serif;
    margin: 0;
}

/* 🔹 Barra lateral fixa */
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

/* 🔹 Área principal */
#conteudo-principal {
    margin-left: 250px;
    padding: 40px;
}

.container {
    width: 100%;
    max-width: 100%;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #ff9100;
    font-weight: bold;
}

/* 🔹 TABELA AJUSTADA — SEM DESLIZAR PARA O LADO */
table {
    width: 100%;
    table-layout: fixed;
}

/* 🔹 Células centralizadas + quebra de texto */
table th,
table td {
    text-align: center;
    vertical-align: middle;
    word-break: break-word;
    white-space: normal;
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

/* 🔹 LARGURAS IDEIAIS */
table th:nth-child(1),
table td:nth-child(1) {
    width: 40px; /* ID */
}

table th:nth-child(2),
table td:nth-child(2) {
    width: 180px; /* Nome */
}

table th:nth-child(3),
table td:nth-child(3) {
    width: 130px; /* Categoria */
}

table th:nth-child(4),
table td:nth-child(4) {
    width: 180px; /* Fornecedor */
}

/* 🔥 COLUNAS CURTINHAS (com quebra “Preço/de/venda”) */
table th:nth-child(5),
table td:nth-child(5),
table th:nth-child(6),
table td:nth-child(6) {
    width: 70px; /* Preços */
}

/* 🔹 Força a quebra da legenda */
th:nth-child(5),
th:nth-child(6) {
    line-height: 1.1;
    word-break: break-word;
}

/* 🔹 Estoque */
table th:nth-child(7),
table td:nth-child(7) {
    width: 85px; 
}


/* 🔹 Status */
table th:nth-child(8),
table td:nth-child(8) {
    width: 70px;
}

/* 🔹 Ações */
table th:nth-child(9),
table td:nth-child(9) {
    width: 80px;
}
.no-wrap {
    white-space: nowrap !important;
}

.btn-cadastro {
    background-color: #ff9100;
    border: none;
    color: white;
    font-weight: bold;
}

.btn-cadastro:hover {
    background-color: #e68000;
    color: white;
}


/* 🔹 Botão Editar */
.btn-edit {
    background-color: #f4a01d;
    border: none;
    color: black;
    font-weight: bold;
}

.btn-edit:hover {
    background-color: #d68c19;
    color: white;
}

/* 🔹 Menu lateral */
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

/* Fonte personalizada */
@import url('../../Fonte_Config/fonte_geral.css');

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
                            <a href="/Fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/Fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
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
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- 🔹 Conteúdo principal -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <h2>Produtos Cadastrados</h2>

                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Fornecedor</th>
                                <th>Preço de venda</th>
                                <th>Preço de compra</th>
                                <th class= "no-wrap">Estoque</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['categoria']) ?></td>
                                <td><?= htmlspecialchars($row['fornecedor']) ?></td>
                                <td>R$ <?= number_format($row['preco_venda'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($row['preco_compra'], 2, ',', '.') ?></td>
                                <td><?= $row['estoque'] ?></td>
                                <td><?= ($row['status'] === 'ativo' ? 'ativo' : 'inativo') ?></td>
                                <td>
                                    <a href="editar_produto.php?id=<?= $row['id'] ?>"
                                        class="btn btn-edit btn-sm">Editar</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9">Nenhum produto cadastrado.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <a href="cadastro_produto.php" class="btn btn-cadastro mt-3">Cadastrar Novo Produto</a>

                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php $sql->close(); ?>
