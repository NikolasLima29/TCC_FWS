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

if (!$sql){
    die("conexão falhou: " . mysqli_error());
}

// Processar reposição de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repor_estoque'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = 24;
    
    // 1. Buscar dados do produto
    $sql_produto = "SELECT validade_padrao_meses, fornecedor_id FROM produtos WHERE id = $produto_id";
    $res_produto = $sql->query($sql_produto);
    if ($res_produto && $res_produto->num_rows > 0) {
        $produto = $res_produto->fetch_assoc();
        $validade_padrao = $produto['validade_padrao_meses'];
        $fornecedor_id = $produto['fornecedor_id'];
        
        // 2. Calcular data de validade
        $data_validade = NULL;
        if ($validade_padrao && $validade_padrao > 0) {
            $data_validade = date('Y-m-d', strtotime("+$validade_padrao months"));
        }
        
        // 3. Inserir novo lote em lotes_produtos
        $sql_lote = "INSERT INTO lotes_produtos (produto_id, quantidade, validade, fornecedor_id) 
                     VALUES ($produto_id, $quantidade, " . ($data_validade ? "'$data_validade'" : "NULL") . ", " . ($fornecedor_id ? $fornecedor_id : "NULL") . ")";
        if (!$sql->query($sql_lote)) {
            die("Erro ao criar lote: " . $sql->error);
        }
        
        // 4. Registrar entrada na tabela movimentacao_estoque
        $sql_insert = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                       VALUES ($produto_id, 'entrada', $quantidade, NOW())";
        if (!$sql->query($sql_insert)) {
            die("Erro ao registrar movimentação: " . $sql->error);
        }
    } else {
        die("Produto não encontrado");
    }
    
    // Redirecionar para atualizar a página
    header('Location: estoque.php');
    exit;
}

$sqli = "SELECT p.id, p.nome, c.nome as categoria_nome, f.nome as fornecedor_nome, p.preco_venda, 
        SUM(lp.quantidade) as estoque_total, p.status, p.criado_em, lp.validade
FROM produtos p 
LEFT JOIN categorias c ON p.categoria_id = c.id 
LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
GROUP BY p.id, lp.validade
ORDER BY p.id, lp.validade ASC";
$result = $sql->query($sqli);
if(!$result){
    die("Erro na consulta: " . $sql->error);
}

// Query para estoque total (sem agrupar por lote)
$sqli_total = "SELECT p.id, p.nome, c.nome as categoria_nome, f.nome as fornecedor_nome, p.preco_venda, 
        p.estoque, p.status, p.criado_em
FROM produtos p 
LEFT JOIN categorias c ON p.categoria_id = c.id 
LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
ORDER BY p.id ASC";
$result_total = $sql->query($sqli_total);
if(!$result_total){
    die("Erro na consulta: " . $sql->error);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <title>Estoque de Produtos</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../CSS/menu_principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <style>
        body {
            background-color: #fff8e1;
            font-family: "Poppins", sans-serif;
            margin: 0;
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
            max-width: 1000px;
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

        table th,
        table td {
            text-align: center;
            vertical-align: middle;
        }

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

        .table {
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            padding: 8px 10px !important;
            text-transform: lowercase;
        }

        .table th::first-letter,
        .table td::first-letter {
            text-transform: uppercase;
        }

        .col-nome {
            text-transform: uppercase;
        }

        .col-preco {
            min-width: 120px;
        }

        .alerta-quantidade {
            background-color: #f5222d !important;
            color: #fff !important;
            font-weight: bold;
        }

        .alerta-validade {
            background-color: #f5222d !important;
            color: #fff !important;
            font-weight: bold;
        }

        .btn-reposicao {
            background-color: #52c41a;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .btn-reposicao:hover {
            background-color: #389e0d;
        }

        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: 2px solid #d11b1b;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .tab-btn.active {
            background-color: #d11b1b;
            color: white;
        }

        .tab-btn:hover {
            opacity: 0.8;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        @import url('../../Fonte_Config/fonte_geral.css');

        @media (max-width: 768px) {
            #fund {
                width: 200px;
            }
            #conteudo-principal {
                margin-left: 200px;
            }
        }
    </style>
</head>

<body>
<main>
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
                        <li>
                            <a href="/TCC_FWS/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
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

                        <li><a href="#" class="nav-link align-middle px-0"
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
                            <img src="../../fotodeperfiladm.png " width="30" height="30" class="rounded-circle">
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

            <!-- 🔹 Conteúdo principal -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <h2>Estoque de Produtos</h2>

                    <!-- Abas de seleção -->
                    <div class="tabs-container">
                        <button class="tab-btn active" onclick="mudarTab('lote')">Estoque por Lote</button>
                        <button class="tab-btn" onclick="mudarTab('total')">Estoque Total</button>
                    </div>

                    <!-- Tabela de Estoque por Lote -->
                    <div id="lote" class="tab-content active">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Fornecedor</th>
                                    <th>Preço</th>
                                    <th>Quantidade</th>
                                    <th>Validade</th>
                                    <th>Status</th>
                                    <th>Chegada</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): 
                                        $estoque = $row['estoque_total'] ?? 0;
                                        $produto_id = $row['id'];
                                        $validade = $row['validade'];
                                        
                                        // Buscar estoque total do produto
                                        $sql_estoque_total = "SELECT estoque FROM produtos WHERE id = $produto_id";
                                        $res_estoque_total = $sql->query($sql_estoque_total);
                                        $estoque_total_produto = 0;
                                        if ($res_estoque_total && $res_estoque_total->num_rows > 0) {
                                            $row_est = $res_estoque_total->fetch_assoc();
                                            $estoque_total_produto = $row_est['estoque'] ?? 0;
                                        }
                                        
                                        // Alerta apenas se o TOTAL está baixo
                                        $class_alerta_qtd = ($estoque_total_produto < 15) ? 'alerta-quantidade' : '';
                                        
                                        $class_alerta_val = '';
                                        if ($validade) {
                                            $data_validade = new DateTime($validade);
                                            $data_hoje = new DateTime();
                                            $intervalo = $data_hoje->diff($data_validade);
                                            if ($intervalo->days <= 7 && $intervalo->invert == 0) {
                                                $class_alerta_val = 'alerta-validade';
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['categoria_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['fornecedor_nome'] ?? 'N/A') ?></td>
                                        <td class="col-preco">R$ <?= number_format($row['preco_venda'], 2, ',', '.') ?></td>
                                        <td class="<?= $class_alerta_qtd ?>"><?= $estoque ?></td>
                                        <td class="<?= $class_alerta_val ?>"><?= $validade ? date('d/m/Y', strtotime($validade)) : 'Sem validade' ?></td>
                                        <td><?= ($row['status'] === 'ativo' ? 'Ativo' : 'Inativo') ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['criado_em'])) ?></td>
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

                    <!-- Tabela de Estoque Total -->
                    <div id="total" class="tab-content">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Fornecedor</th>
                                    <th>Preço</th>
                                    <th>Quantidade Total</th>
                                    <th>Status</th>
                                    <th>Chegada</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($result_total->num_rows > 0): ?>
                                    <?php while ($row = $result_total->fetch_assoc()): 
                                        $estoque_total = $row['estoque'] ?? 0;
                                        $class_alerta_qtd_total = ($estoque_total < 15) ? 'alerta-quantidade' : '';
                                    ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['categoria_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['fornecedor_nome'] ?? 'N/A') ?></td>
                                        <td class="col-preco">R$ <?= number_format($row['preco_venda'], 2, ',', '.') ?></td>
                                        <td class="<?= $class_alerta_qtd_total ?>"><?= $estoque_total ?></td>
                                        <td><?= ($row['status'] === 'ativo' ? 'Ativo' : 'Inativo') ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['criado_em'])) ?></td>
                                        <td>
                                            <?php if ($estoque_total < 15): ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="produto_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="repor_estoque" class="btn-reposicao">+24</button>
                                                </form>
                                            <?php endif; ?>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mudarTab(tabName) {
        // Esconder todas as abas
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Remover classe active de todos os botões
        const btns = document.querySelectorAll('.tab-btn');
        btns.forEach(btn => btn.classList.remove('active'));
        
        // Mostrar a aba selecionada
        document.getElementById(tabName).classList.add('active');
        
        // Adicionar classe active ao botão clicado
        event.target.classList.add('active');
    }
    </script>

</body>

</html>

<?php $sql->close(); ?>
