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

$nome_adm = explode(" ", trim($nome_adm))[0];

// Buscar produtos
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

// Buscar todos os fornecedores
$fornecedores_query = "SELECT id, nome FROM fornecedores ORDER BY nome ASC";
$fornecedores_result = $sql->query($fornecedores_query);

// Buscar todas as categorias
$categorias_query = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$categorias_result = $sql->query($categorias_query);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Produtos</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
      <link rel="stylesheet" href="../../menu_principal/CSS/menu_principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
        margin: 0;
        overflow-x: hidden;
        animation: fadeInBody .5s ease;
    }

    @keyframes fadeInBody {
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
        max-width: 1050px;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.12);
        animation: fadeInCard .6s ease;
    }

    @keyframes fadeInCard {
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
        margin-bottom: 25px;
        color: #ff9100;
        font-weight: bold;
    }

    .btn-cadastro {
        background-color: #ff9100;
        color: white;
        font-weight: bold;
        border: none;
        padding: 10px 18px;
        transition: all .25s ease-in-out;
        border-radius: 8px;
    }

    .btn-cadastro:hover {
        transform: scale(1.05);
        background-color: #e68000;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(255, 140, 0, .45);
    }

    table {
        animation: fadeInTable .6s ease;
    }

    @keyframes fadeInTable {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table thead.table-dark {
        background-color: #ff9100;
    }

    .table thead.table-dark th {
        background-color: #ff9100;
        color: white;
        border-color: #ff9100;
    }

    tr {
        transition: background .18s ease, transform .15s ease;
    }

    tr:hover {
        background-color: #fff3cd;
        transform: scale(1.007);
    }

    .btn-edit {
        background-color: #f4a01d;
        color: black;
        border: none;
        font-weight: bold;
        transition: all .2s ease;
    }

    .btn-edit:hover {
        background-color: #d68c19;
        color: white;
        transform: scale(1.07);
    }

    /* 🔹 Estiliza o cabeçalho da tabela */
    .table thead th {
        text-align: center !important;
        vertical-align: middle !important;
        padding-top: 12px !important;
        padding-bottom: 12px !important;
        font-size: 15px;
        letter-spacing: 0.3px;
        font-weight: 600;
    }

    /* 🔹 Ajusta o texto das colunas com quebra (Preço Venda / Compra) */
    .table thead th small {
        display: block;
        font-size: 12px;
        margin-top: 2px;
    }

    /* 🔹 Mantém a mesma aparência do fundo */
    .table thead th {
        background-color: #ff9100 !important;
        color: white !important;
    }

    @import url('../../Fonte_Config/fonte_geral.css');

    /* ========== ESTILOS DO CAMPO DE PESQUISA E FILTROS ========== */
    .search-filter-container {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 2px solid #ff9100;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px;
        font-size: 14px;
        border: 2px solid #ff9100;
        border-radius: 6px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #f4a01d;
        box-shadow: 0 0 8px rgba(255, 145, 0, 0.3);
        background-color: #fffbf0;
    }

    .filters-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .filter-select {
        padding: 10px 12px;
        border: 2px solid #ff9100;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: white;
    }

    .filter-select:focus {
        outline: none;
        border-color: #f4a01d;
        box-shadow: 0 0 8px rgba(255, 145, 0, 0.2);
    }

    .filter-select option {
        padding: 8px;
    }

    @media (max-width: 768px) {
        .filters-row {
            grid-template-columns: 1fr;
        }
    }
    
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- MENU LATERAL COMPLETO -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">

                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">

                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                        <li><a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php" class="nav-link px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php" class="nav-link px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php" class="nav-link px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link px-0"
                                id="cor-fonte">
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
            <!-- FIM MENU -->

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <h2>Produtos Cadastrados</h2>

                    <!-- ========== CAMPO DE PESQUISA E FILTROS ========== -->
                    <div class="search-filter-container">
                        <!-- Campo de Pesquisa -->
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="🔍 Pesquisar por nome do produto..."
                        >

                        <!-- Filtros de Fornecedor e Categoria -->
                        <div class="filters-row">
                            <!-- Filtro de Fornecedor -->
                            <div class="filter-group">
                                <label for="fornecedorFilter">Filtrar por Fornecedor:</label>
                                <select id="fornecedorFilter" class="filter-select">
                                    <option value="">-- Todos os Fornecedores --</option>
                                    <?php
                                    while ($fornecedor = $fornecedores_result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($fornecedor['nome']) . "'>" . htmlspecialchars($fornecedor['nome']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Filtro de Categoria -->
                            <div class="filter-group">
                                <label for="categoriaFilter">Filtrar por Categoria:</label>
                                <select id="categoriaFilter" class="filter-select">
                                    <option value="">-- Todas as Categorias --</option>
                                    <?php
                                    while ($categoria = $categorias_result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($categoria['nome']) . "'>" . htmlspecialchars($categoria['nome']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BOTÃO NO TOPO (idêntico ao dos fornecedores) -->
                    <div class="d-flex justify-content-end mb-3">
                        <a href="cadastro_produto.php" class="btn btn-cadastro">
                            + Cadastrar Novo Produto
                        </a>
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Fornecedor</th>
                                <th>Preço Venda</th>
                                <th>Preço Compra</th>
                                <th>Estoque</th>
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

                </div>
            </div>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ANIMAÇÃO IGUAL AO MODELO DOS FORNECEDORES
    document.querySelectorAll("tbody tr").forEach((tr, i) => {
        tr.style.opacity = "0";
        tr.style.transform = "translateY(10px)";
        setTimeout(() => {
            tr.style.transition = "0.4s";
            tr.style.opacity = "1";
            tr.style.transform = "translateY(0)";
        }, 80 * i);
    });

    // ========== FUNÇÃO DE FILTRO EM TEMPO REAL ==========
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const fornecedorFilter = document.getElementById('fornecedorFilter').value.toLowerCase();
        const categoriaFilter = document.getElementById('categoriaFilter').value.toLowerCase();
        const table = document.querySelector('tbody');
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.querySelector('td:nth-child(1)') === null) return; // Pula linhas vazias

            const nome = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const categoria = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const fornecedor = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

            // Verifica se atende aos critérios de filtro
            const nomeMatch = nome.includes(searchInput);
            const fornecedorMatch = fornecedorFilter === '' || fornecedor.includes(fornecedorFilter);
            const categoriaMatch = categoriaFilter === '' || categoria.includes(categoriaFilter);

            if (nomeMatch && fornecedorMatch && categoriaMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Mensagem caso nenhum resultado seja encontrado
        if (visibleCount === 0) {
            const emptyRow = table.querySelector('tr[style*="display: none"]');
            if (!table.querySelector('.no-results')) {
                const noResults = document.createElement('tr');
                noResults.className = 'no-results';
                noResults.innerHTML = '<td colspan="9" style="text-align: center; padding: 20px; color: #999;">Nenhum produto encontrado com esses filtros.</td>';
                table.appendChild(noResults);
            }
        } else {
            const noResults = table.querySelector('.no-results');
            if (noResults) noResults.remove();
        }
    }

    // Adiciona os event listeners
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('fornecedorFilter').addEventListener('change', filterTable);
    document.getElementById('categoriaFilter').addEventListener('change', filterTable);
    </script>

</body>

</html>

<?php $sql->close(); ?>