<?php
date_default_timezone_set('America/Sao_Paulo');

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

if (!$sql){
    die("conexão falhou: " . mysqli_error($sql));
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
    <title>Relatório Financeiro</title>
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
            background-color: #FF8C00;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .btn-reposicao:hover {
            background-color: #E67E00;
        }

        .btn-excel {
            background-color: #52c41a;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .btn-excel:hover {
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
                    <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px; justify-content:space-between;">
                        <a href="menu_financeiro.php" class="btn btn-warning" style="display:flex; align-items:center; gap:8px; white-space:nowrap;">
                            <span style="font-size:18px;">←</span> Voltar
                        </a>
                        <h2 style="margin:0; flex:1; text-align:center;">Relatório Financeiro</h2>
                        <div style="width:120px;"></div>
                    </div>
                    <!-- Gráficos lado a lado -->
                    <div style="display:flex; gap:40px; justify-content:center; align-items:flex-start; flex-wrap:wrap; margin-bottom:30px;">
                        <!-- Gráfico de Linha -->
                        <div style="flex:1; min-width:350px; max-width:500px;">
                            <h3 style="text-align:center; color:#1890ff; font-weight:bold; font-size:1.5rem;">Movimentação Diária</h3>
                            <!-- Filtro de mês/ano para o gráfico de linha -->
                            <form method="get" id="formMesAno" style="max-width:350px; margin:0 auto 15px auto; display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
                                <div style="flex:1; min-width:100px;">
                                    <label for="mes_linha" style="font-size:0.9rem; color:#333;">Mês:</label>
                                    <select name="mes_linha" id="mes_linha" class="form-control" style="font-size:0.9rem;">
                                        <?php
                                        $mes_atual = isset($_GET['mes_linha']) ? intval($_GET['mes_linha']) : intval(date('m'));
                                        for ($m = 1; $m <= 12; $m++) {
                                            $selected = ($m === $mes_atual) ? 'selected' : '';
                                            echo "<option value='$m' $selected>" . str_pad($m, 2, '0', STR_PAD_LEFT) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="flex:1; min-width:100px;">
                                    <label for="ano_linha" style="font-size:0.9rem; color:#333;">Ano:</label>
                                    <select name="ano_linha" id="ano_linha" class="form-control" style="font-size:0.9rem;">
                                        <?php
                                        $ano_atual = isset($_GET['ano_linha']) ? intval($_GET['ano_linha']) : intval(date('Y'));
                                        $ano_inicio = 2022;
                                        $ano_fim = intval(date('Y'));
                                        for ($a = $ano_fim; $a >= $ano_inicio; $a--) {
                                            $selected = ($a === $ano_atual) ? 'selected' : '';
                                            echo "<option value='$a' $selected>$a</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="flex:0 0 80px;">
                                    <button type="submit" class="btn-reposicao" style="width:100%;">Ver mês</button>
                                </div>
                            </form>
                            <canvas id="graficoLinha" width="500" height="400" style="max-width:500px; margin:auto auto auto -30px; display:block;"></canvas>
                            <div id="legendaLinha" style="max-width:500px; margin:12px auto 0 auto; text-align:center; font-size:1rem;"></div>
                            <div id="margemLucro" style="max-width:500px; margin:15px auto 0 auto; text-align:center; font-size:1.1rem; font-weight:bold;"></div>
                        </div>
                        <!-- Gráfico de Pizza -->
                        <div style="flex:1; min-width:350px; max-width:400px;">
                            <h3 style="text-align:center; color:#d11b1b; font-weight:bold; font-size:1.5rem;">Despesas</h3>
                            <!-- Filtro de período para o gráfico de pizza -->
                            <form method="get" id="formPeriodo" style="max-width:400px; margin:0 auto 20px auto; display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                                <div style="flex:1; min-width:120px;">
                                    <label for="data_ini" style="font-size:0.95rem; color:#333;">De:</label>
                                    <input type="date" name="data_ini" id="data_ini" class="form-control" value="<?= isset($_GET['data_ini']) ? htmlspecialchars($_GET['data_ini']) : '' ?>">
                                </div>
                                <div style="flex:1; min-width:120px;">
                                    <label for="data_fim" style="font-size:0.95rem; color:#333;">Até:</label>
                                    <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : '' ?>">
                                </div>
                                <div style="flex:0 0 80px;">
                                    <button type="submit" class="btn-reposicao" style="width:100%;">Filtrar</button>
                                </div>
                            </form>
                            <canvas id="graficoDespesas" width="320" height="320" style="max-width:320px; margin:auto; display:block;"></canvas>
                            <div id="legendaDespesas" style="max-width:400px; margin:10px auto 0 auto; text-align:center;"></div>
                            <div id="totalDespesas" style="text-align:center; margin-top:20px; font-size:1.2rem; font-weight:bold; color:#333;"></div>
                        </div>
                    </div>

                    <?php

                    // --- Preparar dados para gráfico de linha ---
                    // Determinar mês/ano selecionado
                    $mes_linha = isset($_GET['mes_linha']) ? intval($_GET['mes_linha']) : intval(date('m'));
                    $ano_linha = isset($_GET['ano_linha']) ? intval($_GET['ano_linha']) : intval(date('Y'));
                    $data_ini_linha = sprintf('%04d-%02d-01', $ano_linha, $mes_linha);
                    $data_fim_linha = date('Y-m-t', strtotime($data_ini_linha));

                    // Gerar array de dias do mês
                    $dias = [];
                    $dt = new DateTime($data_ini_linha);
                    $dt_fim = new DateTime($data_fim_linha);
                    while ($dt <= $dt_fim) {
                        $dias[] = $dt->format('Y-m-d');
                        $dt->modify('+1 day');
                    }

                    // Inicializar arrays de valores
                    $linha_vermelha = array_fill(0, count($dias), 0);
                    $linha_verde = array_fill(0, count($dias), 0);

                    // Buscar despesas por dia (para o mês do gráfico de linha)
                    $res_despesas = $sql->query("SELECT DATE(data_despesa) as dia, SUM(valor) as total FROM despesas WHERE data_despesa >= '$data_ini_linha' AND data_despesa <= '$data_fim_linha' GROUP BY dia");
                    $despesas_por_dia = [];
                    while($row = $res_despesas->fetch_assoc()) {
                        $despesas_por_dia[$row['dia']] = (float)$row['total'];
                    }


                    // Buscar entradas de produtos por dia (movimentacao_estoque tipo 'entrada', usando preco_compra)
                    $res_entradas = $sql->query("SELECT DATE(data_movimentacao) as dia, SUM(p.preco_compra * m.quantidade) as total FROM movimentacao_estoque m LEFT JOIN produtos p ON m.produto_id = p.id WHERE m.tipo_movimentacao = 'entrada' AND data_movimentacao >= '$data_ini_linha' AND data_movimentacao <= '$data_fim_linha' GROUP BY dia");
                    $entradas_por_dia = [];
                    while($row = $res_entradas->fetch_assoc()) {
                        $entradas_por_dia[$row['dia']] = (float)$row['total'];
                    }

                    // Buscar saídas de produtos por dia (movimentacao_estoque tipo 'saida' com venda finalizada, usando preco_venda)
                    $res_saidas = $sql->query("SELECT DATE(m.data_movimentacao) as dia, SUM(p.preco_venda * m.quantidade) as total FROM movimentacao_estoque m LEFT JOIN produtos p ON m.produto_id = p.id LEFT JOIN vendas v ON m.venda_id = v.id WHERE m.tipo_movimentacao = 'saida' AND v.situacao_compra = 'finalizada' AND m.data_movimentacao >= '$data_ini_linha' AND m.data_movimentacao <= '$data_fim_linha' GROUP BY dia");
                    $saidas_por_dia = [];
                    while($row = $res_saidas->fetch_assoc()) {
                        $saidas_por_dia[$row['dia']] = (float)$row['total'];
                    }

                    // Preencher arrays de valores acumulados para cada dia
                    $acum_vermelha = 0;
                    $acum_verde = 0;
                    foreach ($dias as $i => $dia) {
                        $acum_vermelha += ($despesas_por_dia[$dia] ?? 0) + ($entradas_por_dia[$dia] ?? 0);
                        $acum_verde += $saidas_por_dia[$dia] ?? 0;
                        $linha_vermelha[$i] = $acum_vermelha;
                        $linha_verde[$i] = $acum_verde;
                    }

                    // Calcular margem de lucro após preencher os arrays
                    $lucro = 0;
                    if (count($linha_verde) > 0 && count($linha_vermelha) > 0) {
                        $lucro = $linha_verde[count($linha_verde) - 1] - $linha_vermelha[count($linha_vermelha) - 1];
                    }
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    // Dados do gráfico de linha
                    const dias = <?php echo json_encode($dias); ?>;
                    const linhaVermelha = <?php echo json_encode($linha_vermelha); ?>;
                    const linhaVerde = <?php echo json_encode($linha_verde); ?>;
                    const lucro = <?php echo json_encode($lucro); ?>;

                    if (document.getElementById('graficoLinha')) {
                        const ctxLinha = document.getElementById('graficoLinha').getContext('2d');
                        const chartLinha = new Chart(ctxLinha, {
                            type: 'line',
                            data: {
                                labels: dias.map(d => d.slice(-2)), // mostra só o dia
                                datasets: [
                                    {
                                        label: 'Despesas + Entradas',
                                        data: linhaVermelha,
                                        borderColor: '#E53935',
                                        backgroundColor: 'rgba(229,57,53,0.1)',
                                        fill: true,
                                        tension: 0.2,
                                        pointRadius: 2
                                    },
                                    {
                                        label: 'Vendas Finalizadas (Saídas)',
                                        data: linhaVerde,
                                        borderColor: '#52c41a',
                                        backgroundColor: 'rgba(82,196,26,0.1)',
                                        fill: true,
                                        tension: 0.2,
                                        pointRadius: 2
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: true }
                                },
                                scales: {
                                    y: {
                                        title: { display: true, text: 'Reais (R$)' },
                                        beginAtZero: true
                                    },
                                    x: {
                                        title: { display: true, text: 'Dia do mês' }
                                    }
                                }
                            }
                        });
                        // Legenda customizada
                        document.getElementById('legendaLinha').innerHTML = `<span style='color:#E53935;font-weight:bold;'>Despesas + Entradas</span> | <span style='color:#52c41a;font-weight:bold;'>Vendas Finalizadas (Saídas)</span>`;
                        
                        // Exibir margem de lucro
                        const corLucro = lucro >= 0 ? '#52c41a' : '#E53935';
                        const lucroFormatado = lucro.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                        document.getElementById('margemLucro').innerHTML = `Lucro/Prejuízo do mês: <span style="color:${corLucro};">R$ ${lucroFormatado}</span>`;
                    }
                    </script>

                    <?php
                    // Buscar despesas agrupadas por tipo, com filtro de período
                    $where = [];
                    if (!empty($_GET['data_ini'])) {
                        $data_ini = $sql->real_escape_string($_GET['data_ini']);
                        $where[] = "data_despesa >= '$data_ini'";
                    }
                    if (!empty($_GET['data_fim'])) {
                        $data_fim = $sql->real_escape_string($_GET['data_fim']);
                        $where[] = "data_despesa <= '$data_fim'";
                    }
                    $where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
                    $res = $sql->query("SELECT tipo, SUM(valor) as total FROM despesas $where_sql GROUP BY tipo");
                    $tipos = [];
                    $valores = [];
                    $total_despesas = 0;
                    while($row = $res->fetch_assoc()) {
                        $tipos[] = ucfirst($row['tipo']);
                        $valores[] = (float)$row['total'];
                        $total_despesas += (float)$row['total'];
                    }
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    // Dados do PHP para JS
                    const tipos = <?php echo json_encode($tipos); ?>;
                    const valores = <?php echo json_encode($valores); ?>;
                    const total = <?php echo json_encode($total_despesas); ?>;

                    // Cores para cada fatia
                    const cores = [
                        '#f5222d', '#1890ff', '#52c41a', '#faad14', '#722ed1', '#13c2c2', '#eb2f96', '#bfbfbf'
                    ];

                    // Montar gráfico
                    if (document.getElementById('graficoDespesas')) {
                        const ctx = document.getElementById('graficoDespesas').getContext('2d');
                        const chart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: tipos,
                                datasets: [{
                                    data: valores,
                                    backgroundColor: cores.slice(0, tipos.length),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: { display: false }
                                }
                            }
                        });

                        // Legenda customizada com porcentagem
                        let legenda = '';
                        for (let i = 0; i < tipos.length; i++) {
                            const perc = total > 0 ? ((valores[i] / total) * 100).toFixed(1) : 0;
                            legenda += `<div style=\"margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:8px;\">\n` +
                                `<span style=\"display:inline-block;width:18px;height:18px;background:${cores[i]};border-radius:3px;\"></span>` +
                                `<span style=\"font-weight:bold;\">${tipos[i]}</span>` +
                                `<span style=\"color:#888;\">- ${perc}%</span>` +
                            `</div>`;
                        }
                        document.getElementById('legendaDespesas').innerHTML = legenda;
                        document.getElementById('totalDespesas').innerHTML = 'Despesa total: <span style=\"color:#d11b1b;\">R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span>';
                    }
                    </script>

                    <!-- Gráficos adicionais -->
                    <div style="display:flex; gap:40px; justify-content:center; align-items:flex-start; flex-wrap:wrap; margin-bottom:30px; margin-top:80px;">
                        <!-- Gráfico de Pizza - Produtos Mais Vendidos -->
                        <div style="flex:1; min-width:320px; max-width:420px;">
                            <h3 style="text-align:center; color:#722ed1; font-weight:bold; font-size:1.5rem;">Projeção de Lucro</h3>
                            <!-- Input de porcentagem do estoque -->
                            <form method="get" id="formProjecaoPerc" style="max-width:280px; margin:0 auto 15px auto; display:flex; gap:8px; align-items:end;">
                                <div style="flex:1;">
                                    <label for="perc_estoque" style="font-size:0.9rem; color:#333;">% Estoque:</label>
                                    <input type="number" name="perc_estoque" id="perc_estoque" class="form-control" min="0" max="100" step="5" value="<?= isset($_GET['perc_estoque']) ? intval($_GET['perc_estoque']) : 100 ?>" style="font-size:0.9rem;">
                                </div>
                                <div style="flex:0 0 70px;">
                                    <button type="submit" class="btn-reposicao" style="width:100%; font-size:0.85rem;">Aplicar</button>
                                </div>
                            </form>
                            <div style="position:relative; width:240px; height:240px; margin:0 auto;">
                                <canvas id="graficoProjecao" width="240" height="240"></canvas>
                                <div id="gaugeText" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center; z-index:10;">
                                    <div id="percentualGauge" style="font-size:2rem; font-weight:bold; color:#722ed1;">0%</div>
                                    <div style="font-size:0.8rem; color:#666;">Alcançado</div>
                                </div>
                            </div>
                            <div id="legendaProjecao" style="max-width:420px; margin:15px auto 0 auto; text-align:center; font-size:0.9rem;"></div>
                        </div>

                        <!-- Gráfico de Barras - Categorias com Maior Faturamento -->
                        <div style="flex:1; min-width:350px; max-width:500px;">
                            <h3 style="text-align:center; color:#13c2c2; font-weight:bold; font-size:1.5rem;">Faturamento por Categoria</h3>
                            <!-- Filtro de mês/ano para faturamento por categoria -->
                            <form method="get" id="formCategorias" style="max-width:400px; margin:0 auto 20px auto; display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                                <div style="flex:1; min-width:120px;">
                                    <label for="mes_categorias" style="font-size:0.95rem; color:#333;">Mês:</label>
                                    <select name="mes_categorias" id="mes_categorias" class="form-control">
                                        <?php
                                        $mes_categorias = isset($_GET['mes_categorias']) ? intval($_GET['mes_categorias']) : intval(date('m'));
                                        for ($m = 1; $m <= 12; $m++) {
                                            $selected = ($m === $mes_categorias) ? 'selected' : '';
                                            echo "<option value='$m' $selected>" . str_pad($m, 2, '0', STR_PAD_LEFT) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="flex:1; min-width:120px;">
                                    <label for="ano_categorias" style="font-size:0.95rem; color:#333;">Ano:</label>
                                    <select name="ano_categorias" id="ano_categorias" class="form-control">
                                        <?php
                                        $ano_categorias = isset($_GET['ano_categorias']) ? intval($_GET['ano_categorias']) : intval(date('Y'));
                                        $ano_inicio = 2022;
                                        $ano_fim = intval(date('Y'));
                                        for ($a = $ano_fim; $a >= $ano_inicio; $a--) {
                                            $selected = ($a === $ano_categorias) ? 'selected' : '';
                                            echo "<option value='$a' $selected>$a</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="flex:0 0 80px;">
                                    <button type="submit" class="btn-reposicao" style="width:100%;">Ver mês</button>
                                </div>
                            </form>
                            <canvas id="graficoCategorias" width="500" height="350" style="max-width:500px; margin:auto; display:block;"></canvas>
                            <div id="legendaCategorias" style="max-width:500px; margin:15px auto 0 auto; text-align:center; font-size:0.95rem;"></div>
                        </div>
                    </div>

                    <?php
                    // --- Dados para Projeção de Lucro ---
                    $mes_projecao = intval(date('m'));
                    $ano_projecao = intval(date('Y'));
                    $data_ini_projecao = sprintf('%04d-%02d-01', $ano_projecao, $mes_projecao);
                    $data_fim_projecao = date('Y-m-t', strtotime($data_ini_projecao));
                    
                    // Porcentagem do estoque a usar
                    $perc_estoque = isset($_GET['perc_estoque']) ? intval($_GET['perc_estoque']) : 100;
                    $perc_estoque = max(0, min(100, $perc_estoque)); // Limitar entre 0 e 100

                    // Lucro real do mês (vendas - custo dos produtos vendidos - despesas)
                    $sql_lucro_real = "SELECT 
                                         SUM(iv.quantidade * p.preco_venda) as faturamento_real,
                                         SUM(iv.quantidade * p.preco_compra) as custo_real,
                                         (SELECT SUM(valor) FROM despesas WHERE DATE(data_despesa) >= '$data_ini_projecao' AND DATE(data_despesa) <= '$data_fim_projecao') as despesas_mes
                                       FROM itens_vendidos iv
                                       LEFT JOIN produtos p ON iv.produto_id = p.id
                                       LEFT JOIN vendas v ON iv.venda_id = v.id
                                       WHERE v.situacao_compra = 'finalizada' 
                                       AND DATE(v.data_criacao) >= '$data_ini_projecao' 
                                       AND DATE(v.data_criacao) <= '$data_fim_projecao'";
                    $res_lucro_real = $sql->query($sql_lucro_real);
                    $row_lucro_real = $res_lucro_real->fetch_assoc();
                    $faturamento_real = (float)($row_lucro_real['faturamento_real'] ?? 0);
                    $custo_real = (float)($row_lucro_real['custo_real'] ?? 0);
                    $despesas_mes = (float)($row_lucro_real['despesas_mes'] ?? 0);
                    $lucro_real = $faturamento_real - $custo_real - $despesas_mes;

                    // Lucro potencial se vendesse a porcentagem selecionada do estoque
                    $sql_lucro_potencial = "SELECT 
                                             SUM(p.estoque * p.preco_venda) as faturamento_potencial,
                                             SUM(p.estoque * p.preco_compra) as custo_potencial
                                            FROM produtos p
                                            WHERE p.estoque > 0";
                    $res_lucro_potencial = $sql->query($sql_lucro_potencial);
                    $row_lucro_potencial = $res_lucro_potencial->fetch_assoc();
                    $faturamento_potencial = (float)($row_lucro_potencial['faturamento_potencial'] ?? 0) * ($perc_estoque / 100);
                    $custo_potencial = (float)($row_lucro_potencial['custo_potencial'] ?? 0) * ($perc_estoque / 100);
                    $lucro_potencial = $faturamento_potencial - $custo_potencial - $despesas_mes;

                    // --- Dados para Faturamento por Categoria ---
                    $mes_categorias = isset($_GET['mes_categorias']) ? intval($_GET['mes_categorias']) : intval(date('m'));
                    $ano_categorias = isset($_GET['ano_categorias']) ? intval($_GET['ano_categorias']) : intval(date('Y'));
                    $data_ini_categorias = sprintf('%04d-%02d-01', $ano_categorias, $mes_categorias);
                    $data_fim_categorias = date('Y-m-t', strtotime($data_ini_categorias));

                    $sql_categorias = "SELECT c.nome, SUM(iv.quantidade * p.preco_venda) as faturamento 
                                       FROM itens_vendidos iv
                                       LEFT JOIN produtos p ON iv.produto_id = p.id
                                       LEFT JOIN categorias c ON p.categoria_id = c.id
                                       LEFT JOIN vendas v ON iv.venda_id = v.id
                                       WHERE v.situacao_compra = 'finalizada' 
                                       AND DATE(v.data_criacao) >= '$data_ini_categorias' 
                                       AND DATE(v.data_criacao) <= '$data_fim_categorias'
                                       GROUP BY c.id
                                       ORDER BY faturamento DESC";
                    $res_categorias = $sql->query($sql_categorias);
                    $categorias_nomes = [];
                    $categorias_faturamento = [];
                    while($row = $res_categorias->fetch_assoc()) {
                        $categorias_nomes[] = $row['nome'];
                        $categorias_faturamento[] = (float)$row['faturamento'];
                    }
                    ?>
                    <script>
                    // Cores para gráficos
                    const coresProdutos = [
                        '#722ed1', '#1890ff', '#52c41a', '#faad14', '#f5222d', '#13c2c2', '#eb2f96', '#bfbfbf', '#4a4a4a', '#ffc069'
                    ];

                    const coresCategorias = [
                        '#13c2c2', '#1890ff', '#52c41a', '#faad14', '#f5222d', '#722ed1', '#eb2f96', '#bfbfbf'
                    ];

                    // --- Gráfico de Projeção de Lucro (Gauge) ---
                    const lucroReal = <?php echo json_encode($lucro_real); ?>;
                    const lucroPotencial = <?php echo json_encode($lucro_potencial); ?>;
                    const diferenca = lucroPotencial - lucroReal;
                    const percentualAlcancado = lucroPotencial > 0 ? Math.max(0, Math.min(100, (lucroReal / lucroPotencial * 100))) : 0;

                    if (document.getElementById('graficoProjecao')) {
                        const ctxProjecao = document.getElementById('graficoProjecao').getContext('2d');
                        const chartProjecao = new Chart(ctxProjecao, {
                            type: 'doughnut',
                            data: {
                                datasets: [
                                    {
                                        data: [percentualAlcancado, 100 - percentualAlcancado],
                                        backgroundColor: ['#52c41a', '#f0f0f0'],
                                        borderColor: ['#52c41a', '#f0f0f0'],
                                        borderWidth: 0
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { enabled: false }
                                },
                                cutout: '75%'
                            }
                        });
                        
                        // Atualizar percentual no centro do gauge
                        document.getElementById('percentualGauge').textContent = percentualAlcancado.toFixed(1) + '%';
                        
                        // Exibir dados descritivos
                        const lucroRealFmt = lucroReal.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                        const lucroPotencialFmt = lucroPotencial.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                        const diferenca_fmt = diferenca.toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
                        
                        document.getElementById('legendaProjecao').innerHTML = `
                            <div style="margin-bottom:12px; padding:10px; background:#f0f8ff; border-radius:5px;">
                                <div style="font-weight:bold; color:#52c41a; font-size:1.1rem;">Lucro Realizado: R$ ${lucroRealFmt}</div>
                                <div style="font-size:0.85rem; color:#666;">${percentualAlcancado.toFixed(1)}% do potencial</div>
                            </div>
                            <div style="margin-bottom:12px; padding:10px; background:#e6f7ff; border-radius:5px;">
                                <div style="font-weight:bold; color:#1890ff; font-size:1.1rem;">Lucro Potencial: R$ ${lucroPotencialFmt}</div>
                            </div>
                            <div style="padding:10px; background:#fff7e6; border-radius:5px;">
                                <div style="font-weight:bold; color:#faad14; font-size:1.1rem;">Oportunidade de Ganho: R$ ${diferenca_fmt}</div>
                            </div>
                        `;
                    }

                    // --- Gráfico de Faturamento por Categoria ---
                    const categoriasNomes = <?php echo json_encode($categorias_nomes); ?>;
                    const categoriasFaturamento = <?php echo json_encode($categorias_faturamento); ?>;

                    if (document.getElementById('graficoCategorias')) {
                        const ctxCategorias = document.getElementById('graficoCategorias').getContext('2d');
                        
                        // Criar datasets individuais para cada categoria (para mostrar na legenda)
                        const datasetsCategories = categoriasNomes.map((cat, idx) => ({
                            label: cat,
                            data: [categoriasFaturamento[idx]],
                            backgroundColor: coresCategorias[idx % coresCategorias.length],
                            borderWidth: 1
                        }));
                        
                        const chartCategorias = new Chart(ctxCategorias, {
                            type: 'bar',
                            data: {
                                labels: ['Faturamento'],
                                datasets: datasetsCategories
                            },
                            options: {
                                responsive: true,
                                indexAxis: 'y',
                                plugins: {
                                    legend: { 
                                        display: true,
                                        position: 'bottom',
                                        labels: { font: { size: 12 }, padding: 15 }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        ticks: { callback: function(value) { return 'R$ ' + value.toLocaleString('pt-BR'); } },
                                        title: { display: true, text: 'Faturamento (R$)', font: { size: 12 } }
                                    }
                                }
                            }
                        });
                    }
                    </script>

                    <script>
                    // Auto-submit ao mudar mês ou ano
                    document.addEventListener('DOMContentLoaded', function() {
                        // Movimentação Diária (mes_linha e ano_linha)
                        const mesLinha = document.getElementById('mes_linha');
                        const anoLinha = document.getElementById('ano_linha');
                        const formMesAno = document.getElementById('formMesAno');
                        
                        if (mesLinha) {
                            mesLinha.addEventListener('change', function() {
                                if (formMesAno) {
                                    sessionStorage.setItem('scrollPos', window.scrollY);
                                    formMesAno.submit();
                                }
                            });
                        }
                        
                        if (anoLinha) {
                            anoLinha.addEventListener('change', function() {
                                if (formMesAno) {
                                    sessionStorage.setItem('scrollPos', window.scrollY);
                                    formMesAno.submit();
                                }
                            });
                        }
                        
                        // Faturamento por Categoria (mes_categorias e ano_categorias)
                        const mesCategorias = document.getElementById('mes_categorias');
                        const anoCategorias = document.getElementById('ano_categorias');
                        const formCategorias = document.getElementById('formCategorias');
                        
                        if (mesCategorias) {
                            mesCategorias.addEventListener('change', function() {
                                if (formCategorias) {
                                    sessionStorage.setItem('scrollPos', window.scrollY);
                                    formCategorias.submit();
                                }
                            });
                        }
                        
                        if (anoCategorias) {
                            anoCategorias.addEventListener('change', function() {
                                if (formCategorias) {
                                    sessionStorage.setItem('scrollPos', window.scrollY);
                                    formCategorias.submit();
                                }
                            });
                        }
                    });
                    </script>

                    <script>
                    // Manter scroll na posição ao aplicar filtros
                    document.addEventListener('DOMContentLoaded', function() {
                        // Recuperar posição do scroll do sessionStorage
                        const scrollPos = sessionStorage.getItem('scrollPos');
                        if (scrollPos !== null) {
                            window.scrollTo(0, parseInt(scrollPos));
                            sessionStorage.removeItem('scrollPos');
                        }
                    });

                    // Salvar scroll ao clicar em botões de filtro
                    const botoesFiltro = document.querySelectorAll('.btn-reposicao');
                    botoesFiltro.forEach(btn => {
                        btn.addEventListener('click', function() {
                            sessionStorage.setItem('scrollPos', window.scrollY);
                        });
                    });

                    // Salvar scroll ao submeter formulários de filtro
                    const formularios = document.querySelectorAll('form[method="get"]');
                    formularios.forEach(form => {
                        form.addEventListener('submit', function() {
                            sessionStorage.setItem('scrollPos', window.scrollY);
                        });
                    });
                    </script>

                    <!-- Botões de exportação -->
                    <div style="text-align:center; margin-top:40px; padding-top:20px; border-top:2px solid #f0f0f0;">
                        <form method="post" action="exportar.php" style="display:inline;">
                            <input type="hidden" name="tipo" value="excel">
                            <input type="hidden" name="mes_linha" value="<?= $mes_linha ?>">
                            <input type="hidden" name="ano_linha" value="<?= $ano_linha ?>">
                            <input type="hidden" name="data_ini" value="<?= isset($_GET['data_ini']) ? htmlspecialchars($_GET['data_ini']) : '' ?>">
                            <input type="hidden" name="data_fim" value="<?= isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : '' ?>">
                            <input type="hidden" name="perc_estoque" value="<?= isset($_GET['perc_estoque']) ? intval($_GET['perc_estoque']) : 100 ?>">
                            <input type="hidden" name="mes_categorias" value="<?= $mes_categorias ?>">
                            <input type="hidden" name="ano_categorias" value="<?= $ano_categorias ?>">
                            <button type="submit" class="btn-excel" style="margin-right:15px; padding:10px 20px; font-size:1rem;">
                                📊 Exportar Excel
                            </button>
                        </form>
                        <form method="post" action="exportar.php" style="display:inline;">
                            <input type="hidden" name="tipo" value="pdf">
                            <input type="hidden" name="mes_linha" value="<?= $mes_linha ?>">
                            <input type="hidden" name="ano_linha" value="<?= $ano_linha ?>">
                            <input type="hidden" name="data_ini" value="<?= isset($_GET['data_ini']) ? htmlspecialchars($_GET['data_ini']) : '' ?>">
                            <input type="hidden" name="data_fim" value="<?= isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : '' ?>">
                            <input type="hidden" name="perc_estoque" value="<?= isset($_GET['perc_estoque']) ? intval($_GET['perc_estoque']) : 100 ?>">
                            <input type="hidden" name="mes_categorias" value="<?= $mes_categorias ?>">
                            <input type="hidden" name="ano_categorias" value="<?= $ano_categorias ?>">
                            <button type="submit" class="btn-reposicao" style="padding:10px 20px; font-size:1rem; background-color:#f5222d;">
                                📄 Exportar PDF
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>

</html>

<?php $sql->close(); ?>
