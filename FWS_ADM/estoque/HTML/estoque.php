<?php
date_default_timezone_set('America/Sao_Paulo');

include "../../conn.php";
include "../PHP/calcular_validade_lote.php";
include "../PHP/gerenciar_chegada_lote.php";

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
    $quantidade = isset($_POST['quantidade_custom']) && $_POST['quantidade_custom'] > 0 ? intval($_POST['quantidade_custom']) : 24;
    
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
        
        // 3. Calcular data de chegada (data de hoje)
        $data_chegada = date('Y-m-d H:i:s');
        
        // 4. Inserir novo lote em lotes_produtos COM data de chegada
        $sql_lote = "INSERT INTO lotes_produtos (produto_id, quantidade, validade, fornecedor_id, chegada) 
                     VALUES ($produto_id, $quantidade, " . ($data_validade ? "'$data_validade'" : "NULL") . ", " . ($fornecedor_id ? $fornecedor_id : "NULL") . ", '$data_chegada')";
        if (!$sql->query($sql_lote)) {
            die("Erro ao criar lote: " . $sql->error);
        }
        
        // 5. Atualizar estoque na tabela produtos
        $sql_update_estoque = "UPDATE produtos SET estoque = estoque + $quantidade WHERE id = $produto_id";
        if (!$sql->query($sql_update_estoque)) {
            die("Erro ao atualizar estoque: " . $sql->error);
        }
        
        // 6. Registrar entrada na tabela movimentacao_estoque
        $sql_insert = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                       VALUES ($produto_id, 'entrada', $quantidade, NOW())";
        if (!$sql->query($sql_insert)) {
            die("Erro ao registrar movimentação: " . $sql->error);
        }
    } else {
        die("Produto não encontrado");
    }
    
    // Redirecionar para atualizar a página
    header('Location: estoque.php?sucesso=lote_adicionado');
    exit;
}

// Processar retirada de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirar_estoque'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade_retirada = isset($_POST['quantidade_retirada']) && $_POST['quantidade_retirada'] > 0 ? intval($_POST['quantidade_retirada']) : 1;
    $tipo_retirada = isset($_POST['tipo_retirada']) ? $_POST['tipo_retirada'] : 'outros';
    $motivo_retirada = isset($_POST['motivo_retirada']) && !empty($_POST['motivo_retirada']) ? $_POST['motivo_retirada'] : NULL;
    
    // Buscar estoque atual do produto
    $sql_check = "SELECT estoque FROM produtos WHERE id = $produto_id";
    $res_check = $sql->query($sql_check);
    if ($res_check && $res_check->num_rows > 0) {
        $row_check = $res_check->fetch_assoc();
        $estoque_atual = $row_check['estoque'];
        
        // Verificar se há quantidade suficiente
        if ($estoque_atual >= $quantidade_retirada) {
            // Registrar saída na tabela movimentacao_estoque
            $sql_saida = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                         VALUES ($produto_id, 'saida', $quantidade_retirada, NOW())";
            if (!$sql->query($sql_saida)) {
                die("Erro ao registrar movimentação: " . $sql->error);
            }
            
            // Atualizar estoque na tabela produtos
            $sql_update_estoque = "UPDATE produtos SET estoque = estoque - $quantidade_retirada WHERE id = $produto_id";
            if (!$sql->query($sql_update_estoque)) {
                die("Erro ao atualizar estoque: " . $sql->error);
            }
            
            // Registrar na tabela retiradas
            $motivo_sql = $motivo_retirada ? "'" . $sql->real_escape_string($motivo_retirada) . "'" : "NULL";
            $sql_retirada = "INSERT INTO retiradas (produto_id, funcionario_id, quantidade, tipo_motivo, motivo) 
                            VALUES ($produto_id, $id, $quantidade_retirada, '$tipo_retirada', $motivo_sql)";
            if (!$sql->query($sql_retirada)) {
                die("Erro ao registrar retirada: " . $sql->error);
            }
        } else {
            die("Quantidade insuficiente em estoque. Disponível: $estoque_atual");
        }
    } else {
        die("Produto não encontrado");
    }
    
    // Redirecionar para atualizar a página
    header('Location: estoque.php?sucesso=estoque_retirado');
    exit;
}

$sqli = "SELECT p.id, p.nome, p.foto_produto, c.nome as categoria_nome, f.nome as fornecedor_nome, p.preco_venda, 
        lp.id as lote_id, lp.quantidade, p.status, p.criado_em, lp.validade, lp.chegada
FROM produtos p 
LEFT JOIN categorias c ON p.categoria_id = c.id 
LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
ORDER BY lp.validade ASC, p.id ASC, lp.id ASC";
$result = $sql->query($sqli);
if(!$result){
    die("Erro na consulta: " . $sql->error);
}

// Query para estoque total (sem agrupar por lote)
$sqli_total = "SELECT p.id, p.nome, p.foto_produto, c.nome as categoria_nome, f.nome as fornecedor_nome, p.preco_venda, 
        p.estoque, p.status, p.criado_em
FROM produtos p 
LEFT JOIN categorias c ON p.categoria_id = c.id 
LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
ORDER BY p.estoque ASC, p.id ASC";
$result_total = $sql->query($sqli_total);
if(!$result_total){
    die("Erro na consulta: " . $sql->error);
}

// Buscar produtos com estoque baixo
$sqli_baixo = "SELECT id, nome, estoque, foto_produto FROM produtos WHERE estoque < 15 ORDER BY estoque ASC";
$result_baixo = $sql->query($sqli_baixo);
$produtos_baixos = [];
if($result_baixo && $result_baixo->num_rows > 0) {
    while($row = $result_baixo->fetch_assoc()) {
        $produtos_baixos[] = $row;
    }
}

// Buscar produtos que vencem HOJE
$sqli_vencendo_hoje = "SELECT p.id, p.nome, p.foto_produto, MAX(DATE(lp.validade)) as validade FROM produtos p
                  LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
                  WHERE lp.validade IS NOT NULL 
                  AND DATE(lp.validade) = CURDATE()
                  GROUP BY p.id
                  ORDER BY MAX(DATE(lp.validade)) ASC";
$result_vencendo_hoje = $sql->query($sqli_vencendo_hoje);
$produtos_vencendo_hoje = [];
if($result_vencendo_hoje && $result_vencendo_hoje->num_rows > 0) {
    while($row = $result_vencendo_hoje->fetch_assoc()) {
        $produtos_vencendo_hoje[] = $row;
    }
}

// Buscar produtos que vencem nos próximos 10 dias (amanhã até +10 dias)
$sqli_vencendo_10dias = "SELECT p.id, p.nome, p.foto_produto, MAX(DATE(lp.validade)) as validade FROM produtos p
                  LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
                  WHERE lp.validade IS NOT NULL 
                  AND DATE(lp.validade) > CURDATE()
                  AND DATE(lp.validade) <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)
                  GROUP BY p.id
                  ORDER BY MAX(DATE(lp.validade)) ASC";
$result_vencendo_10dias = $sql->query($sqli_vencendo_10dias);
$produtos_vencendo_10dias = [];
if($result_vencendo_10dias && $result_vencendo_10dias->num_rows > 0) {
    while($row = $result_vencendo_10dias->fetch_assoc()) {
        $produtos_vencendo_10dias[] = $row;
    }
}

// Buscar produtos vencidos (antes de hoje - pega o lote MAIS PRÓXIMO de hoje que venceu)
$sqli_vencidos = "SELECT p.id, p.nome, p.foto_produto, MAX(DATE(lp.validade)) as validade FROM produtos p
                  LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
                  WHERE lp.validade IS NOT NULL 
                  AND DATE(lp.validade) < CURDATE()
                  GROUP BY p.id
                  ORDER BY MAX(DATE(lp.validade)) DESC";
$result_vencidos = $sql->query($sqli_vencidos);
$produtos_vencidos = [];
if($result_vencidos && $result_vencidos->num_rows > 0) {
    while($row = $result_vencidos->fetch_assoc()) {
        $produtos_vencidos[] = $row;
    }
}


// Buscar todas as categorias
$sql_categorias = "SELECT DISTINCT id, nome FROM categorias ORDER BY nome ASC";
$result_categorias = $sql->query($sql_categorias);

// Buscar todos os fornecedores
$sql_fornecedores = "SELECT DISTINCT id, nome FROM fornecedores ORDER BY nome ASC";
$result_fornecedores = $sql->query($sql_fornecedores);

// Buscar movimentações de estoque
$sql_movimentacao = "SELECT m.id, m.produto_id, m.tipo_movimentacao, m.quantidade, m.data_movimentacao, m.venda_id,
                            p.nome as produto_nome, p.foto_produto, p.categoria_id, p.fornecedor_id,
                            c.nome as categoria_nome, f.nome as fornecedor_nome
                     FROM movimentacao_estoque m
                     LEFT JOIN produtos p ON m.produto_id = p.id
                     LEFT JOIN categorias c ON p.categoria_id = c.id
                     LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
                     ORDER BY m.data_movimentacao DESC";
$result_movimentacao = $sql->query($sql_movimentacao);
if(!$result_movimentacao){
    die("Erro na consulta de movimentação: " . $sql->error);
}

// Buscar retiradas de estoque
$sql_retiradas = "SELECT r.id, r.produto_id, r.funcionario_id, r.quantidade, r.tipo_motivo, r.motivo, r.data_retirada,
                         p.nome as produto_nome, p.foto_produto,
                         f.nome as funcionario_nome
                  FROM retiradas r
                  LEFT JOIN produtos p ON r.produto_id = p.id
                  LEFT JOIN funcionarios f ON r.funcionario_id = f.id
                  ORDER BY r.data_retirada DESC";
$result_retiradas = $sql->query($sql_retiradas);
if(!$result_retiradas){
    die("Erro na consulta de retiradas: " . $sql->error);
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
            max-width: 1140px;
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

        .col-quantidade {
            min-width: 80px;
        }

        .col-acao {
            min-width: 100px;
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

        .alerta-validade-proxima {
            background-color: #ff8c00 !important;
            color: #fff !important;
            font-weight: bold;
        }

        .alerta-validade-valido {
            background-color: #52c41a !important;
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

        .btn-retirada {
            background-color: #ff7875;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            margin-top: 5px;
        }

        .btn-retirada:hover {
            background-color: #d32f2f;
        }

        th {
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        th:hover {
            background-color: #495057 !important;
            transition: all 0.3s;
        }

        th .sort-icon {
            margin-left: 5px;
            font-size: 0.8rem;
            opacity: 0.6;
        }

        th.sorted-asc .sort-icon::after {
            content: " ▲";
            opacity: 1;
            color: #fff;
        }

        th.sorted-desc .sort-icon::after {
            content: " ▼";
            opacity: 1;
            color: #fff;
        }

        .tabs-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 10px 20px;
            background-color: #f0f0f0;
            border: 2px solid #ff9100;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .tab-btn.active {
            background-color: #ff9100;
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

                    <!-- Barra de Pesquisa -->
                    <div style="margin-bottom:20px;">
                        <input type="text" id="barraPesquisa" placeholder="🔍 Pesquisar por nome do produto ou fornecedor..." style="width:100%; padding:12px; border:2px solid #ff9100; border-radius:6px; font-size:1rem;">
                    </div>

                    <!-- Filtros -->
                    <div style="margin-bottom:20px; display:flex; gap:15px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333;">Categoria:</label>
                            <select id="filtroCategoria" style="width:100%; padding:10px; border:2px solid #ff9100; border-radius:6px; font-size:0.95rem;">
                                <option value="">Todas as categorias</option>
                                <?php if($result_categorias && $result_categorias->num_rows > 0): ?>
                                    <?php while($cat = $result_categorias->fetch_assoc()): ?>
                                        <option value="<?= $cat['nome'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div style="flex:1; min-width:200px;">
                            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333;">Fornecedor:</label>
                            <select id="filtroFornecedor" style="width:100%; padding:10px; border:2px solid #ff9100; border-radius:6px; font-size:0.95rem;">
                                <option value="">Todos os fornecedores</option>
                                <?php if($result_fornecedores && $result_fornecedores->num_rows > 0): ?>
                                    <?php while($forn = $result_fornecedores->fetch_assoc()): ?>
                                        <option value="<?= $forn['nome'] ?>"><?= htmlspecialchars($forn['nome']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div style="flex:1; min-width:200px;">
                            <label style="display:block; margin-bottom:8px; font-weight:bold; color:#333;">Preço Máximo:</label>
                            <input type="number" id="filtroPreco" placeholder="0.00" min="0" step="0.01" style="width:100%; padding:10px; border:2px solid #ff9100; border-radius:6px; font-size:0.95rem;">
                        </div>
                    </div>

                    <!-- Modal de Alerta de Estoque Baixo e Validade Próxima -->
                    <div id="modalEstoqueBaixo" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9998; align-items:center; justify-content:center;" onclick="if(event.target === this) fecharAlertaEstoque();">
                        <div style="background-color:white; padding:30px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:550px; max-height:80vh; overflow-y:auto;">
                            <h3 style="color:#f5222d; margin-bottom:20px; text-align:center;">⚠️ Alertas de Estoque e Validade</h3>
                            
                            <!-- Seção Estoque Baixo -->
                            <div id="listaProdutosBaixos" style="margin-bottom:20px;">
                                <!-- Lista dinâmica de estoque baixo -->
                            </div>

                            <!-- Seção Vencendo Hoje -->
                            <div id="listaProdutosVendoHoje" style="margin-bottom:20px;">
                                <!-- Lista dinâmica de vencendo hoje -->
                            </div>

                            <!-- Seção Validade Próxima (próximos 10 dias) -->
                            <div id="listaProdutosVendoProximos10" style="margin-bottom:25px;">
                                <!-- Lista dinâmica de validade próxima (próximos 10 dias) -->
                            </div>

                            <!-- Seção Produtos Vencidos -->
                            <div id="listaProdutosVencidos" style="margin-bottom:25px;">
                                <!-- Lista dinâmica de produtos vencidos -->
                            </div>

                            <div style="text-align:center;">
                                <button onclick="fecharAlertaEstoque()" style="padding:10px 40px; background-color:#52c41a; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">OK</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Confirmação de Remoção de Lote Vencido -->
                    <div id="modalRemocaoLote" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;" onclick="if(event.target === this) fecharModalRemocao();">
                        <div style="background-color:white; padding:30px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:400px; text-align:center;">
                            <h3 style="color:#d11b1b; margin-bottom:20px;">⚠️ Confirmar Remoção</h3>
                            <p id="textoConfirmacao" style="color:#333; margin-bottom:20px; font-size:1rem;"></p>
                            <div style="display:flex; gap:10px; justify-content:center;">
                                <button onclick="fecharModalRemocao()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                                <button id="btnConfirmarRemocao" onclick="confirmarRemocaoLote()" style="padding:10px 30px; background-color:#d11b1b; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Remover</button>
                            </div>
                        </div>
                    </div>

                    <!-- Abas de seleção -->
                    <div class="tabs-container">
                        <button class="tab-btn active" onclick="mudarTab('total')">Estoque Total</button>
                        <button class="tab-btn" onclick="mudarTab('lote')">Estoque por Lote</button>
                        <button class="tab-btn" onclick="mudarTab('movimentacao')">Movimentação Estoque</button>
                        <button class="tab-btn" onclick="mudarTab('retiradas')">Retiradas</button>
                    </div>

                    <!-- Tabela de Estoque Total -->
                    <div id="total" class="tab-content active">
                        <table class="table table-bordered table-hover" id="tabelaTotal">
                            <thead class="table-dark">
                                <tr>
                                    <th onclick="ordenarTabela('tabelaTotal', 0, 'foto')">Foto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 1, 'numero')">ID <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 2, 'texto')">Nome <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 3, 'texto')">Categoria <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 4, 'texto')">Fornecedor <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 5, 'numero')">Preço <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 6, 'numero')">Quantidade Total <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 7, 'texto')">Status <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaTotal', 8, 'data')">Chegada <span class="sort-icon"></span></th>
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
                                        <td style="padding:8px;">
                                            <?php 
                                                $foto = !empty($row['foto_produto']) ? htmlspecialchars($row['foto_produto']) : '/fws/IMG_Produtos/sem_imagem.png';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($row['nome']) ?>" style="width:75px; height:45px; object-fit:cover; border-radius:4px;">
                                        </td>
                                        <td style="font-weight:bold; color:#ff9100;"><?= $row['id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['categoria_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['fornecedor_nome'] ?? 'N/A') ?></td>
                                        <td class="col-preco">R$ <?= number_format($row['preco_venda'], 2, ',', '.') ?></td>
                                        <td class="col-quantidade <?= $class_alerta_qtd_total ?>"><?= $estoque_total ?></td>
                                        <td><?= ($row['status'] === 'ativo' ? 'Ativo' : 'Inativo') ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['criado_em'])) ?></td>
                                        <td class="col-acao">
                                            <button type="button" class="btn-reposicao" onclick="confirmarAdicao(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nome']) ?>')">+1 lote</button>
                                            <br>
                                            <button type="button" class="btn-retirada" onclick="confirmarRetirada(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nome']) ?>')">-Retirada</button>
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

                    <!-- Tabela de Estoque por Lote -->
                    <div id="lote" class="tab-content">
                        <table class="table table-bordered table-hover" id="tabelaLote">
                            <thead class="table-dark">
                                <tr>
                                    <th onclick="ordenarTabela('tabelaLote', 0, 'foto')">Foto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 1, 'numero')">ID <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 2, 'texto')">Nome <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 3, 'texto')">Categoria <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 4, 'texto')">Fornecedor <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 5, 'numero')">Preço <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 6, 'numero')">Quantidade <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 7, 'data')">Validade <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 8, 'texto')">Status <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaLote', 9, 'data')">Chegada <span class="sort-icon"></span></th>
                                    <th>Ação</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): 
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
                                        
                                        // Calcular validade usando a função
                                        $resultado_validade = calcularValidadeLote($sql, $produto_id);
                                        $validade_exibicao = '';
                                        
                                        if ($validade) {
                                            // Usar a validade do banco de dados se existir
                                            $data_validade = new DateTime($validade);
                                            $data_hoje = new DateTime();
                                            $intervalo = $data_hoje->diff($data_validade);
                                            
                                            if ($intervalo->days <= 9 && $intervalo->invert == 0) {
                                                $class_alerta_val = 'alerta-validade-proxima';
                                                $validade_exibicao = '<span style="font-weight:bold;">⏰ ' . date('d/m/Y', strtotime($validade)) . '</span>';
                                            } else if ($intervalo->invert == 1) {
                                                // Vencido - mostra data em vermelho com ícone de alerta
                                                $class_alerta_val = 'alerta-validade';
                                                $validade_exibicao = '<span style="color:white; font-weight:bold;">⚠️ ' . date('d/m/Y', strtotime($validade)) . '</span>';
                                            } else {
                                                // Válido
                                                $class_alerta_val = 'alerta-validade-valido';
                                                $validade_exibicao = '<span style="font-weight:bold;">✓ ' . date('d/m/Y', strtotime($validade)) . '</span>';
                                            }
                                        } else {
                                            // Se não tem validade no lote, usar a calculada
                                            if ($resultado_validade['sucesso'] && $resultado_validade['validade']) {
                                                $validade_exibicao = '<span style="color:#52c41a;">✓ ' . $resultado_validade['validade_formatada'] . ' (calculada)</span>';
                                                $class_alerta_val = '';
                                            } else {
                                                $validade_exibicao = '<span style="color:#999; font-style:italic;">Sem validade</span>';
                                                $class_alerta_val = '';
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td style="padding:8px;">
                                            <?php 
                                                $foto = !empty($row['foto_produto']) ? htmlspecialchars($row['foto_produto']) : '/fws/IMG_Produtos/sem_imagem.png';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($row['nome']) ?>" style="width:75px; height:45px; object-fit:cover; border-radius:4px;">
                                        </td>
                                        <td style="font-weight:bold; color:#ff9100;"><?= $row['id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row['nome']) ?></td>
                                        <td><?= htmlspecialchars($row['categoria_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['fornecedor_nome'] ?? 'N/A') ?></td>
                                        <td class="col-preco">R$ <?= number_format($row['preco_venda'], 2, ',', '.') ?></td>
                                        <td class="<?= $class_alerta_qtd ?>"><?= $row['quantidade'] ?? 0 ?></td>
                                        <td class="<?= $class_alerta_val ?>"><?= $validade_exibicao ?></td>
                                        <td><?= ($row['status'] === 'ativo' ? 'Ativo' : 'Inativo') ?></td>
                                        <td><?= $row['chegada'] ? date('d/m/Y', strtotime($row['chegada'])) : 'N/A' ?></td>
                                        <td>
                                            <?php 
                                            // Mostrar botão de remover apenas se estiver vencido ou vencem hoje
                                            if (isset($intervalo) && (($intervalo->invert == 1) || ($intervalo->days == 0 && $intervalo->invert == 0))) {
                                                ?>
                                                <button onclick="removerLoteVencido(<?= $row['lote_id'] ?>, '<?= htmlspecialchars($row['nome']) ?>', <?= $row['quantidade'] ?? 0 ?>)" style="padding:5px 10px; background-color:#d11b1b; color:white; border:none; border-radius:4px; cursor:pointer; font-size:0.85rem; font-weight:bold;">🗑️ Remover</button>
                                                <?php
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                                <td colspan="9">Nenhum produto cadastrado.</td>
                                </tr>
                                <?php endif; ?>
                        </table>
                    </div>

                    <!-- Tabela de Movimentação de Estoque -->
                    <div id="movimentacao" class="tab-content">
                        <table class="table table-bordered table-hover" id="tabelaMovimentacao">
                            <thead class="table-dark">
                                <tr>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 0, 'foto')">Foto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 1, 'numero')">ID <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 2, 'texto')">Produto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 3, 'texto')">Categoria <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 4, 'texto')">Fornecedor <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 5, 'numero')">Quantidade <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 6, 'tipo')">Tipo <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 7, 'data')">Data <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaMovimentacao', 8, 'numero')">Nº Pedido <span class="sort-icon"></span></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($result_movimentacao->num_rows > 0): ?>
                                    <?php while ($row_mov = $result_movimentacao->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding:8px;">
                                            <?php 
                                                $foto = !empty($row_mov['foto_produto']) ? htmlspecialchars($row_mov['foto_produto']) : '/fws/IMG_Produtos/sem_imagem.png';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($row_mov['produto_nome'] ?? 'Produto') ?>" style="width:75px; height:45px; object-fit:cover; border-radius:4px;">
                                        </td>
                                        <td style="font-weight:bold; color:#ff9100;"><?= $row_mov['produto_id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row_mov['produto_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row_mov['categoria_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row_mov['fornecedor_nome'] ?? 'N/A') ?></td>
                                        <td style="text-align:center; font-weight:bold;"><?= $row_mov['quantidade'] ?></td>
                                        <td style="text-align:center;">
                                            <?php 
                                                $tipo = strtoupper($row_mov['tipo_movimentacao']);
                                                $cor = ($tipo === 'ENTRADA') ? '#52c41a' : '#ff7875';
                                                $icon = ($tipo === 'ENTRADA') ? '▲' : '▼';
                                            ?>
                                            <span style="background-color:<?= $cor ?>; color:white; padding:4px 8px; border-radius:4px; font-weight:bold; display:inline-block;"><?= $icon ?> <?= $tipo ?></span>
                                        </td>
                                        <td style="text-align:center;"><?= date('d/m/Y H:i', strtotime($row_mov['data_movimentacao'])) ?></td>
                                        <td style="text-align:center;">
                                            <?php 
                                                if ($row_mov['tipo_movimentacao'] === 'saida' && $row_mov['venda_id']) {
                                                    echo $row_mov['venda_id'];
                                                } else {
                                                    echo '-';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">Nenhuma movimentação registrada.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabela de Retiradas -->
                    <div id="retiradas" class="tab-content">
                        <table class="table table-bordered table-hover" id="tabelaRetiradas">
                            <thead class="table-dark">
                                <tr>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 0, 'foto')">Foto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 1, 'numero')">ID Produto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 2, 'texto')">Produto <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 3, 'texto')">Funcionário <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 4, 'numero')">Quantidade <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 5, 'texto')">Motivo <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 6, 'texto')">Descrição <span class="sort-icon"></span></th>
                                    <th onclick="ordenarTabela('tabelaRetiradas', 7, 'data')">Data Retirada <span class="sort-icon"></span></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($result_retiradas->num_rows > 0): ?>
                                    <?php while ($row_ret = $result_retiradas->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding:8px;">
                                            <?php 
                                                $foto = !empty($row_ret['foto_produto']) ? htmlspecialchars($row_ret['foto_produto']) : '/fws/IMG_Produtos/sem_imagem.png';
                                            ?>
                                            <img src="<?= $foto ?>" alt="<?= htmlspecialchars($row_ret['produto_nome'] ?? 'Produto') ?>" style="width:75px; height:45px; object-fit:cover; border-radius:4px;">
                                        </td>
                                        <td style="font-weight:bold; color:#ff9100;"><?= $row_ret['produto_id'] ?></td>
                                        <td class="col-nome"><?= htmlspecialchars($row_ret['produto_nome'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row_ret['funcionario_nome'] ?? 'N/A') ?></td>
                                        <td style="text-align:center; font-weight:bold;"><?= $row_ret['quantidade'] ?></td>
                                        <td><?php 
                                            // Formatar tipo_motivo: substituir underscores por espaços e capitalizar
                                            $tipo_motivo = $row_ret['tipo_motivo'] ?? 'N/A';
                                            
                                            // Mapa de conversão para nomes mais legíveis
                                            $mapa_motivos = [
                                                'doacao' => 'Doação',
                                                'uso_interno' => 'Uso Interno',
                                                'roubo' => 'Roubo',
                                                'quebra' => 'Quebra',
                                                'outros' => 'Outros'
                                            ];
                                            
                                            $tipo_formatado = isset($mapa_motivos[$tipo_motivo]) ? $mapa_motivos[$tipo_motivo] : ucwords(str_replace('_', ' ', $tipo_motivo));
                                            echo htmlspecialchars($tipo_formatado);
                                        ?></td>
                                        <td><?= htmlspecialchars($row_ret['motivo'] ?? '-') ?></td>
                                        <td style="text-align:center;"><?= date('d/m/Y H:i', strtotime($row_ret['data_retirada'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center;">Nenhuma retirada registrada.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal de Confirmação -->
                    <div id="modalConfirmacao" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                        <div style="background-color:white; padding:30px; border-radius:8px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:400px;">
                            <h3 style="color:#d11b1b; margin-bottom:20px;">Adicionar Lote</h3>
                            <p id="textoProduto" style="margin-bottom:20px; font-size:1rem; color:#333;"></p>
                            <div style="margin-bottom:15px;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold; color:#333; font-size:0.9rem;">Quantidade:</label>
                                <input type="number" id="quantidadeInput" min="1" value="24" style="width:100%; padding:6px 8px; border:2px solid #ddd; border-radius:4px; font-size:0.9rem; text-align:center;">
                            </div>
                            <div style="display:flex; gap:10px; justify-content:center;">
                                <button onclick="cancelarAdicao()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                                <form id="formConfirmacao" method="post" style="display:inline;">
                                    <input type="hidden" name="produto_id" id="produtoId">
                                    <input type="hidden" name="quantidade_custom" id="quantidadeCustom">
                                    <button type="submit" name="repor_estoque" onclick="return setarQuantidade()" style="padding:10px 30px; background-color:#52c41a; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Confirmar</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Retirada -->
                    <div id="modalRetirada" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                        <div style="background-color:white; padding:30px; border-radius:8px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:450px;">
                            <h3 style="color:#ff7875; margin-bottom:20px;">Retirada de Estoque</h3>
                            <p id="textoProdutoRetirada" style="margin-bottom:20px; font-size:1rem; color:#333;"></p>
                            <div style="margin-bottom:15px;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold; color:#333; font-size:0.9rem;">Quantidade a retirar:</label>
                                <input type="number" id="quantidadeRetiradaInput" min="1" value="1" style="width:100%; padding:6px 8px; border:2px solid #ddd; border-radius:4px; font-size:0.9rem; text-align:center;">
                            </div>
                            <div style="margin-bottom:15px;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold; color:#333; font-size:0.9rem;">Tipo de Retirada:</label>
                                <select id="tipoRetiradaInput" style="width:100%; padding:6px 8px; border:2px solid #ddd; border-radius:4px; font-size:0.9rem;">
                                    <option value="uso_interno">Uso Interno</option>
                                    <option value="roubo">Roubo</option>
                                    <option value="quebra">Quebra</option>
                                    <option value="doacao">Doação</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                            <div style="margin-bottom:15px;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold; color:#333; font-size:0.9rem;">Descrição (opcional):</label>
                                <textarea id="motivoInput" placeholder="Descreva o motivo..." style="width:100%; padding:6px 8px; border:2px solid #ddd; border-radius:4px; font-size:0.9rem; resize:vertical; min-height:60px;"></textarea>
                            </div>
                            <div style="display:flex; gap:10px; justify-content:center;">
                                <button onclick="cancelarRetirada()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                                <form id="formRetirada" method="post" style="display:inline;">
                                    <input type="hidden" name="produto_id" id="produtoIdRetirada">
                                    <input type="hidden" name="quantidade_retirada" id="quantidadeRetiradaCustom">
                                    <input type="hidden" name="tipo_retirada" id="tipoRetiradaCustom">
                                    <input type="hidden" name="motivo_retirada" id="motivoCustom">
                                    <button type="submit" name="retirar_estoque" onclick="return setarQuantidadeRetirada()" style="padding:10px 30px; background-color:#ff7875; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Confirmar</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Verificar se há mensagem de sucesso na URL
    const urlParams = new URLSearchParams(window.location.search);
    const sucesso = urlParams.get('sucesso');

    if (sucesso === 'lote_adicionado') {
        alert('✅ Lote adicionado com sucesso!');
        // Remover o parâmetro da URL para não aparecer novamente
        window.history.replaceState({}, document.title, window.location.pathname);
    } else if (sucesso === 'estoque_retirado') {
        alert('✅ Estoque retirado com sucesso!');
        // Remover o parâmetro da URL para não aparecer novamente
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Dados dos produtos com estoque baixo
    const produtosBaixos = <?php echo json_encode($produtos_baixos); ?>;
    const produtosVendoHoje = <?php echo json_encode($produtos_vencendo_hoje); ?>;
    const produtosVendoProximos10 = <?php echo json_encode($produtos_vencendo_10dias); ?>;
    const produtosVencidos = <?php echo json_encode($produtos_vencidos); ?>;

    // Mostrar modal ao carregar a página se houver alertas
    document.addEventListener('DOMContentLoaded', function() {
        if (produtosBaixos.length > 0 || produtosVendoHoje.length > 0 || produtosVendoProximos10.length > 0 || produtosVencidos.length > 0) {
            exibirAlertaEstoque();
        }
    });

    function exibirAlertaEstoque() {
        const listaEstoque = document.getElementById('listaProdutosBaixos');
        const listaVendoHoje = document.getElementById('listaProdutosVendoHoje');
        const listaVendoProximos10 = document.getElementById('listaProdutosVendoProximos10');
        const listaVencidos = document.getElementById('listaProdutosVencidos');
        
        listaEstoque.innerHTML = '';
        listaVendoHoje.innerHTML = '';
        listaVendoProximos10.innerHTML = '';
        listaVencidos.innerHTML = '';
        
        // Seção Estoque Baixo
        if (produtosBaixos.length > 0) {
            const tituloBaixo = document.createElement('h4');
            tituloBaixo.style.cssText = 'color:#f5222d; margin-bottom:15px; font-size:1rem;';
            tituloBaixo.innerHTML = '📉 Produtos com Estoque Baixo (igual ou menor que 15 unidades)';
            listaEstoque.appendChild(tituloBaixo);
            
            produtosBaixos.forEach((produto, index) => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color:#f5222d; padding:12px; margin-bottom:10px; border-radius:4px; border-left:4px solid #f5222d; display:flex; gap:12px; align-items:flex-start;';
                const imgPath = produto.foto_produto ? produto.foto_produto : '/fws/IMG_Produtos/sem_imagem.png';
                item.innerHTML = `
                    <img src="${imgPath}" alt="${produto.nome}" style="width:60px; height:60px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                    <div style="flex:1;">
                        <div style="font-weight:bold; color:white;">${index + 1}. ${produto.nome}</div>
                        <div style="color:white; margin-top:5px;">Estoque atual: <strong>${produto.estoque}</strong> unidades</div>
                    </div>
                `;
                listaEstoque.appendChild(item);
            });
        }
        
        // Seção Vencendo Hoje
        if (produtosVendoHoje.length > 0) {
            const tituloHoje = document.createElement('h4');
            tituloHoje.style.cssText = 'color:#d11b1b; margin-bottom:15px; font-size:1rem;';
            tituloHoje.innerHTML = '🚨 Vencendo HOJE';
            listaVendoHoje.appendChild(tituloHoje);
            
            produtosVendoHoje.forEach((produto, index) => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color:#d11b1b; padding:12px; margin-bottom:10px; border-radius:4px; border-left:4px solid #d11b1b; display:flex; gap:12px; align-items:flex-start;';
                const [ano, mes, dia] = produto.validade.split('-');
                const dataFormatada = `${dia}/${mes}/${ano}`;
                const imgPath = produto.foto_produto ? produto.foto_produto : '/fws/IMG_Produtos/sem_imagem.png';
                item.innerHTML = `
                    <img src="${imgPath}" alt="${produto.nome}" style="width:60px; height:60px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                    <div style="flex:1;">
                        <div style="font-weight:bold; color:white;">${index + 1}. ${produto.nome}</div>
                        <div style="color:white; margin-top:5px;">Vence em: <strong>${dataFormatada}</strong></div>
                    </div>
                `;
                listaVendoHoje.appendChild(item);
            });
        }
        
        // Seção Validade Próxima (próximos 10 dias)
        if (produtosVendoProximos10.length > 0) {
            const tituloProximos = document.createElement('h4');
            tituloProximos.style.cssText = 'color:#ff8c00; margin-bottom:15px; font-size:1rem;';
            tituloProximos.innerHTML = '⏰ Vencendo nos próximos 10 dias';
            listaVendoProximos10.appendChild(tituloProximos);
            
            produtosVendoProximos10.forEach((produto, index) => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color:#ff8c00; padding:12px; margin-bottom:10px; border-radius:4px; border-left:4px solid #ff8c00; display:flex; gap:12px; align-items:flex-start;';
                const [ano, mes, dia] = produto.validade.split('-');
                const dataFormatada = `${dia}/${mes}/${ano}`;
                const imgPath = produto.foto_produto ? produto.foto_produto : '/fws/IMG_Produtos/sem_imagem.png';
                item.innerHTML = `
                    <img src="${imgPath}" alt="${produto.nome}" style="width:60px; height:60px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                    <div style="flex:1;">
                        <div style="font-weight:bold; color:white;">${index + 1}. ${produto.nome}</div>
                        <div style="color:white; margin-top:5px;">Validade: <strong>${dataFormatada}</strong></div>
                    </div>
                `;
                listaVendoProximos10.appendChild(item);
            });
        }
        
        // Seção Produtos Vencidos
        if (produtosVencidos.length > 0) {
            const tituloVencidos = document.createElement('h4');
            tituloVencidos.style.cssText = 'color:#d11b1b; margin-bottom:15px; margin-top:20px; font-size:1rem;';
            tituloVencidos.innerHTML = '🚨 Produtos VENCIDOS';
            listaVencidos.appendChild(tituloVencidos);
            
            produtosVencidos.forEach((produto, index) => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color:#d11b1b; padding:12px; margin-bottom:10px; border-radius:4px; border-left:4px solid #d11b1b; display:flex; gap:12px; align-items:flex-start;';
                const [ano, mes, dia] = produto.validade.split('-');
                const dataFormatada = `${dia}/${mes}/${ano}`;
                const imgPath = produto.foto_produto ? produto.foto_produto : '/fws/IMG_Produtos/sem_imagem.png';
                item.innerHTML = `
                    <img src="${imgPath}" alt="${produto.nome}" style="width:60px; height:60px; object-fit:cover; border-radius:4px; flex-shrink:0;">
                    <div style="flex:1;">
                        <div style="font-weight:bold; color:white;">${index + 1}. ${produto.nome}</div>
                        <div style="color:white; margin-top:5px;">Venceu em: <strong>${dataFormatada}</strong></div>
                    </div>
                `;
                listaVencidos.appendChild(item);
            });
        }
        
        document.getElementById('modalEstoqueBaixo').style.display = 'flex';
    }

    function fecharAlertaEstoque() {
        document.getElementById('modalEstoqueBaixo').style.display = 'none';
    }

    function removerProdutosVencidos() {
        if (!confirm('Tem certeza que deseja remover TODOS os LOTES VENCIDOS? Isto irá criar registros de movimentação de estoque.')) {
            return;
        }

        // Enviar requisição para remover vencidos
        fetch('/fws/FWS_ADM/estoque/PHP/remover_produtos_vencidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                remover_todos: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('✅ ' + data.mensagem);
                // Recarregar a página após 1 segundo
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('❌ Erro: ' + data.erro);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('❌ Erro ao remover lotes vencidos!');
        });
    }

    function removerLoteVencido(loteId, nomeProduto, quantidade) {
        // Guardar dados para usar na confirmação
        window.loteParaRemover = {
            id: loteId,
            nome: nomeProduto,
            quantidade: quantidade
        };

        // Mostrar modal de confirmação
        document.getElementById('textoConfirmacao').innerHTML = `Tem certeza que deseja remover o lote vencido de <strong>${nomeProduto}</strong>?<br><br>Quantidade: <strong>${quantidade} un.</strong><br><br>Isto irá criar um registro de movimentação de estoque.`;
        document.getElementById('modalRemocaoLote').style.display = 'flex';
    }

    function fecharModalRemocao() {
        document.getElementById('modalRemocaoLote').style.display = 'none';
        window.loteParaRemover = null;
    }

    function confirmarRemocaoLote() {
        if (!window.loteParaRemover) {
            alert('Erro: nenhum lote selecionado');
            return;
        }

        const lote = window.loteParaRemover;
        fecharModalRemocao();

        // Enviar requisição para remover lote
        fetch('/fws/FWS_ADM/estoque/PHP/remover_produtos_vencidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lote_id: lote.id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                alert('✅ ' + data.mensagem);
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('❌ Erro: ' + data.erro);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('❌ Erro ao remover lote vencido!');
        });
    }
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

    // Filtro de pesquisa
    document.getElementById('barraPesquisa').addEventListener('keyup', function() {
        aplicarFiltros();
    });

    document.getElementById('filtroCategoria').addEventListener('change', function() {
        aplicarFiltros();
    });

    document.getElementById('filtroFornecedor').addEventListener('change', function() {
        aplicarFiltros();
    });

    document.getElementById('filtroPreco').addEventListener('keyup', function() {
        aplicarFiltros();
    });

    function aplicarFiltros() {
        const termoPesquisa = document.getElementById('barraPesquisa').value.toLowerCase();
        const categoria = document.getElementById('filtroCategoria').value.toLowerCase();
        const fornecedor = document.getElementById('filtroFornecedor').value.toLowerCase();
        const preco = parseFloat(document.getElementById('filtroPreco').value) || Infinity;
        
        // Filtrar tabela de Lote
        // Estrutura: Foto (0) → ID (1) → Nome (2) → Categoria (3) → Fornecedor (4)
        const tabelaLote = document.getElementById('tabelaLote');
        const linhasLote = tabelaLote.querySelectorAll('tbody tr');
        linhasLote.forEach(linha => {
            const nome = linha.cells[2]?.textContent.toLowerCase() || '';
            const fornecedorCel = linha.cells[4]?.textContent.toLowerCase() || '';
            const categoriaCel = linha.cells[3]?.textContent.toLowerCase() || '';
            
            const coincidePesquisa = nome.includes(termoPesquisa) || fornecedorCel.includes(termoPesquisa);
            const coincideCategoria = !categoria || categoriaCel.includes(categoria);
            const coincideFornecedor = !fornecedor || fornecedorCel.includes(fornecedor);
            
            if (coincidePesquisa && coincideCategoria && coincideFornecedor) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });
        
        // Filtrar tabela de Total
        // Estrutura: Foto (0) → ID (1) → Nome (2) → Categoria (3) → Fornecedor (4) → Preço (5)
        const tabelaTotal = document.getElementById('tabelaTotal');
        const linhasTotal = tabelaTotal.querySelectorAll('tbody tr');
        linhasTotal.forEach(linha => {
            const nome = linha.cells[2]?.textContent.toLowerCase() || '';
            const fornecedorCel = linha.cells[4]?.textContent.toLowerCase() || '';
            const categoriaCel = linha.cells[3]?.textContent.toLowerCase() || '';
            const precoTexto = linha.cells[5]?.textContent || '';
            const precoValor = parseFloat(precoTexto.replace('R$ ', '').replace(',', '.')) || 0;
            
            const coincidePesquisa = nome.includes(termoPesquisa) || fornecedorCel.includes(termoPesquisa);
            const coincideCategoria = !categoria || categoriaCel.includes(categoria);
            const coincideFornecedor = !fornecedor || fornecedorCel.includes(fornecedor);
            const coincidePreco = precoValor <= preco;
            
            if (coincidePesquisa && coincideCategoria && coincideFornecedor && coincidePreco) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });
        
        // Filtrar tabela de Movimentação
        // Estrutura: Foto (0) → ID (1) → Produto (2) → Categoria (3) → Fornecedor (4)
        const tabelaMovimentacao = document.getElementById('tabelaMovimentacao');
        const linhasMovimentacao = tabelaMovimentacao.querySelectorAll('tbody tr');
        linhasMovimentacao.forEach(linha => {
            const nome = linha.cells[2]?.textContent.toLowerCase() || '';
            const fornecedorCel = linha.cells[4]?.textContent.toLowerCase() || '';
            const categoriaCel = linha.cells[3]?.textContent.toLowerCase() || '';
            
            const coincidePesquisa = nome.includes(termoPesquisa) || fornecedorCel.includes(termoPesquisa);
            const coincideCategoria = !categoria || categoriaCel.includes(categoria);
            const coincideFornecedor = !fornecedor || fornecedorCel.includes(fornecedor);
            
            if (coincidePesquisa && coincideCategoria && coincideFornecedor) {
                linha.style.display = '';
            } else {
                linha.style.display = 'none';
            }
        });

        // Filtrar tabela de Retiradas
        // Estrutura: Foto (0) → ID Produto (1) → Produto (2) → Funcionário (3) → Quantidade (4) → Motivo (5) → Descrição (6) → Data (7)
        const tabelaRetiradas = document.getElementById('tabelaRetiradas');
        if (tabelaRetiradas) {
            const linhasRetiradas = tabelaRetiradas.querySelectorAll('tbody tr');
            linhasRetiradas.forEach(linha => {
                const nome = linha.cells[2]?.textContent.toLowerCase() || '';
                const funcionarioCel = linha.cells[3]?.textContent.toLowerCase() || '';
                
                const coincidePesquisa = nome.includes(termoPesquisa) || funcionarioCel.includes(termoPesquisa);
                
                if (coincidePesquisa) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        }
    }

    function confirmarAdicao(produtoId, nomeProduto) {
        document.getElementById('produtoId').value = produtoId;
        document.getElementById('quantidadeInput').value = '24';
        document.getElementById('textoProduto').textContent = 'Adicionar ' + nomeProduto;
        document.getElementById('modalConfirmacao').style.display = 'flex';
    }

    function cancelarAdicao() {
        document.getElementById('modalConfirmacao').style.display = 'none';
    }

    function setarQuantidade() {
        const quantidade = parseInt(document.getElementById('quantidadeInput').value);
        if (quantidade <= 0 || isNaN(quantidade)) {
            alert('⚠️ A quantidade deve ser maior que 0!');
            return false;
        }
        document.getElementById('quantidadeCustom').value = quantidade;
        return true;
    }

    function confirmarRetirada(produtoId, nomeProduto) {
        document.getElementById('produtoIdRetirada').value = produtoId;
        document.getElementById('quantidadeRetiradaInput').value = '1';
        document.getElementById('textoProdutoRetirada').textContent = 'Retirada de ' + nomeProduto;
        document.getElementById('modalRetirada').style.display = 'flex';
    }

    function cancelarRetirada() {
        document.getElementById('modalRetirada').style.display = 'none';
    }

    function setarQuantidadeRetirada() {
        const quantidade = parseInt(document.getElementById('quantidadeRetiradaInput').value);
        if (quantidade <= 0 || isNaN(quantidade)) {
            alert('⚠️ A quantidade deve ser maior que 0!');
            return false;
        }
        document.getElementById('quantidadeRetiradaCustom').value = quantidade;
        document.getElementById('tipoRetiradaCustom').value = document.getElementById('tipoRetiradaInput').value;
        document.getElementById('motivoCustom').value = document.getElementById('motivoInput').value;
        return true;
    }

    // Variáveis de estado de ordenação
    let estadoOrdenacao = {};

    function ordenarTabela(idTabela, coluna, tipo) {
        const tabela = document.getElementById(idTabela);
        const tbody = tabela.querySelector('tbody');
        const linhas = Array.from(tbody.querySelectorAll('tr'));
        
        // Determinar direção de ordenação
        const chave = `${idTabela}-col${coluna}`;
        let ascendente = !estadoOrdenacao[chave];
        estadoOrdenacao[chave] = ascendente;
        
        // Limpar marcações de ordenação anterior
        tabela.querySelectorAll('th').forEach(th => {
            th.classList.remove('sorted-asc', 'sorted-desc');
        });
        
        // Marcar coluna atual
        const headerAtual = tabela.querySelectorAll('th')[coluna];
        if (headerAtual) {
            headerAtual.classList.add(ascendente ? 'sorted-asc' : 'sorted-desc');
        }
        
        // Ordenar linhas
        linhas.sort((a, b) => {
            let valorA = a.cells[coluna]?.textContent.trim() || '';
            let valorB = b.cells[coluna]?.textContent.trim() || '';
            
            if (tipo === 'numero') {
                // Limpar valores de números (remover R$, símbolos, etc)
                valorA = parseFloat(valorA.replace('R$', '').replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
                valorB = parseFloat(valorB.replace('R$', '').replace(/[^0-9.,]/g, '').replace(',', '.')) || 0;
                return ascendente ? valorA - valorB : valorB - valorA;
            } else if (tipo === 'data') {
                // Converter datas para comparação
                valorA = new Date(valorA.split('/').reverse().join('-')) || new Date(0);
                valorB = new Date(valorB.split('/').reverse().join('-')) || new Date(0);
                return ascendente ? valorA - valorB : valorB - valorA;
            } else if (tipo === 'tipo') {
                // Para coluna tipo (ENTRADA/SAÍDA)
                valorA = valorA.toUpperCase();
                valorB = valorB.toUpperCase();
                return ascendente ? valorA.localeCompare(valorB) : valorB.localeCompare(valorA);
            } else if (tipo === 'foto') {
                // Não ordena fotos
                return 0;
            } else {
                // Texto - ordem alfabética
                valorA = valorA.toLowerCase();
                valorB = valorB.toLowerCase();
                return ascendente ? valorA.localeCompare(valorB) : valorB.localeCompare(valorA);
            }
        });
        
        // Reorganizar linhas na tabela
        linhas.forEach(linha => tbody.appendChild(linha));
    }
    </script>

</body>

</html>

<?php $sql->close(); ?>
