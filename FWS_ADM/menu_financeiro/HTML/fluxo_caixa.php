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

// Definir datas padrão para filtros
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t');

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
    <title>Fluxo de Caixa</title>
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

        /* ESTILOS PARA FILTRO DE DATAS */
        .filtro-periodo {
            background: #f9f9f9;
            border: 2px solid #ff9100;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: end;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filtro-campo {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filtro-campo label {
            font-weight: bold;
            color: #ff9100;
            font-size: 0.9rem;
        }

        .filtro-campo input[type="date"] {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 0.95rem;
            transition: border 0.3s;
        }

        .filtro-campo input[type="date"]:focus {
            outline: none;
            border-color: #ff9100;
        }

        .btn-filtrar {
            background-color: #ff9100;
            color: white;
            border: none;
            padding: 8px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filtrar:hover {
            background-color: #e68000;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 145, 0, 0.3);
        }

        .btn-limpar {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 25px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-limpar:hover {
            background-color: #5a6268;
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
                            <span style="font-size:18px;"></span> ← Voltar
                        </a>
                        <h2 style="margin:0; flex:1; text-align:center;">Fluxo de Caixa</h2>
                        <div style="width:120px;"></div>
                    </div>

                    <!-- Filtro de Período -->
                    <form method="GET" action="">
                        <div class="filtro-periodo">
                            <div class="filtro-campo">
                                <label for="data_inicio">Data Inicial</label>
                                <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" required>
                            </div>
                            <div class="filtro-campo">
                                <label for="data_fim">Data Final</label>
                                <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" required>
                            </div>
                            <button type="submit" class="btn-filtrar">Filtrar</button>
                            <a href="fluxo_caixa.php" class="btn-limpar" style="text-decoration:none;">Limpar</a>
                        </div>
                    </form>

                    <?php
                    // Buscar vendas no período
                    $sql_vendas = "SELECT v.id, v.data_criacao, v.funcionario_id, v.total, f.nome as funcionario_nome
                        FROM vendas v
                        LEFT JOIN funcionarios f ON v.funcionario_id = f.id
                        WHERE v.situacao_compra = 'finalizada'
                        AND DATE(v.data_criacao) >= '$data_inicio'
                        AND DATE(v.data_criacao) <= '$data_fim'
                        ORDER BY v.data_criacao DESC";
                    $res_vendas = $sql->query($sql_vendas);
                    ?>

                    <div style="overflow-x:auto;">
                    <table class="table table-bordered" style="margin-top:10px; background:#fff;">
                        <thead class="table-dark">
                            <tr>
                                <th>Nº Venda</th>
                                <th>Data da Venda</th>
                                <th>Funcionário</th>
                                <th>Valor da Compra</th>
                                <th>Lucro</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ($res_vendas && $res_vendas->num_rows > 0) {
                            while ($venda = $res_vendas->fetch_assoc()) {
                                $venda_id = intval($venda['id']);
                                // Calcular lucro: soma (preco_venda - preco_compra) * quantidade de todos produtos da venda
                                $sql_lucro = "SELECT SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro
                                    FROM itens_vendidos iv
                                    LEFT JOIN produtos p ON iv.produto_id = p.id
                                    WHERE iv.venda_id = $venda_id";
                                $res_lucro = $sql->query($sql_lucro);
                                $lucro = 0;
                                if ($res_lucro && $row_lucro = $res_lucro->fetch_assoc()) {
                                    $lucro = floatval($row_lucro['lucro']);
                                }
                                echo '<tr>';
                                echo '<td>' . $venda_id . '</td>';
                                echo '<td>' . date('d/m/Y H:i', strtotime($venda['data_criacao'])) . '</td>';
                                echo '<td>' . htmlspecialchars($venda['funcionario_nome']) . '</td>';
                                echo '<td>R$ ' . number_format($venda['total'], 2, ',', '.') . '</td>';
                                echo '<td style="color:' . ($lucro >= 0 ? '#52c41a' : '#E53935') . '; font-weight:bold;">R$ ' . number_format($lucro, 2, ',', '.') . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align:center; color:#999;">Nenhuma venda encontrada para o período selecionado.</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                    </div>

                    <!-- Gráficos de produtos mais/menos lucrativos -->
                    <?php
                    // Buscar top 3 produtos mais lucrativos
                    $sql_top_lucro = "SELECT p.nome, SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro, SUM(iv.quantidade) as vendidos
                        FROM itens_vendidos iv
                        LEFT JOIN produtos p ON iv.produto_id = p.id
                        LEFT JOIN vendas v ON iv.venda_id = v.id
                        WHERE v.situacao_compra = 'finalizada'
                        AND DATE(v.data_criacao) >= '$data_inicio'
                        AND DATE(v.data_criacao) <= '$data_fim'
                        GROUP BY p.id
                        ORDER BY lucro DESC, vendidos DESC
                        LIMIT 3";
                    $res_top_lucro = $sql->query($sql_top_lucro);
                    $produtos_top = [];
                    if ($res_top_lucro && $res_top_lucro->num_rows > 0) {
                        while ($row = $res_top_lucro->fetch_assoc()) {
                            $produtos_top[] = $row;
                        }
                    }

                    // Buscar top 3 produtos menos lucrativos (mas vendidos)
                    $sql_low_lucro = "SELECT p.nome, SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro, SUM(iv.quantidade) as vendidos
                        FROM itens_vendidos iv
                        LEFT JOIN produtos p ON iv.produto_id = p.id
                        LEFT JOIN vendas v ON iv.venda_id = v.id
                        WHERE v.situacao_compra = 'finalizada'
                        AND DATE(v.data_criacao) >= '$data_inicio'
                        AND DATE(v.data_criacao) <= '$data_fim'
                        GROUP BY p.id
                        ORDER BY lucro ASC, vendidos DESC
                        LIMIT 3";
                    $res_low_lucro = $sql->query($sql_low_lucro);
                    $produtos_low = [];
                    if ($res_low_lucro && $res_low_lucro->num_rows > 0) {
                        while ($row = $res_low_lucro->fetch_assoc()) {
                            $produtos_low[] = $row;
                        }
                    }
                    ?>

                    <div style="display:flex; gap:40px; justify-content:center; align-items:flex-start; flex-wrap:wrap; margin-top:40px;">
                        <div style="flex:1; min-width:320px; max-width:500px;">
                            <h4 style="text-align:center; color:#52c41a; font-weight:bold; font-size:1.2rem;">Top 3 Produtos Mais Lucrativos</h4>
                            <canvas id="graficoTopLucro" width="400" height="300"></canvas>
                            
                            <!-- Tabela Top Lucro -->
                            <table class="table table-bordered" style="background:#fff; margin-top:15px;">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Produto</th>
                                        <th>Vendidos</th>
                                        <th>Lucro (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $cores_top = ['#52c41a', '#13c2c2', '#faad14'];
                                foreach ($produtos_top as $i => $p) {
                                    echo '<tr>';
                                    echo '<td><span style="display:inline-block; width:18px; height:18px; border-radius:4px; background:' . $cores_top[$i % count($cores_top)] . '; border:1px solid #ccc;"></span></td>';
                                    echo '<td>' . htmlspecialchars($p['nome']) . '</td>';
                                    echo '<td>' . intval($p['vendidos']) . '</td>';
                                    echo '<td style="color:#52c41a; font-weight:bold;">R$ ' . number_format($p['lucro'], 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="flex:1; min-width:320px; max-width:500px;">
                            <h4 style="text-align:center; color:#E53935; font-weight:bold; font-size:1.2rem;">Top 3 Produtos Menos Lucrativos</h4>
                            <canvas id="graficoLowLucro" width="400" height="300"></canvas>
                            
                            <!-- Tabela Low Lucro -->
                            <table class="table table-bordered" style="background:#fff; margin-top:15px;">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width:40px;"></th>
                                        <th>Produto</th>
                                        <th>Vendidos</th>
                                        <th>Lucro (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $cores_low = ['#E53935', '#faad14', '#bfbfbf'];
                                foreach ($produtos_low as $i => $p) {
                                    echo '<tr>';
                                    echo '<td><span style="display:inline-block; width:18px; height:18px; border-radius:4px; background:' . $cores_low[$i % count($cores_low)] . '; border:1px solid #ccc;"></span></td>';
                                    echo '<td>' . htmlspecialchars($p['nome']) . '</td>';
                                    echo '<td>' . intval($p['vendidos']) . '</td>';
                                    echo '<td style="color:#E53935; font-weight:bold;">R$ ' . number_format($p['lucro'], 2, ',', '.') . '</td>';
                                    echo '</tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Exportação -->
                    <div style="text-align:center; margin-top:40px; padding-top:20px; border-top:2px solid #f0f0f0;">
                        <form method="post" action="exportar_fluxo.php" style="display:inline;">
                            <input type="hidden" name="tipo" value="excel">
                            <input type="hidden" name="data_ini" value="<?= $data_inicio ?>">
                            <input type="hidden" name="data_fim" value="<?= $data_fim ?>">
                            <button type="submit" style="background-color:#52c41a; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:bold; cursor:pointer; margin-right:15px; font-size:1rem;">
                                📊 Exportar Excel
                            </button>
                        </form>
                        <button type="button" id="btnExportarPDF" style="background-color:#f5222d; color:white; border:none; padding:10px 20px; border-radius:6px; font-weight:bold; cursor:pointer; font-size:1rem;">
                            📄 Exportar PDF
                        </button>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                    <script>
                    // Dados PHP para JS
                    const produtosTop = <?= json_encode($produtos_top ?? []) ?>;
                    const produtosLow = <?= json_encode($produtos_low ?? []) ?>;
                    const dataIni = '<?= htmlspecialchars($data_inicio) ?>';
                    const dataFim = '<?= htmlspecialchars($data_fim) ?>';

                    // Botão exportar PDF
                    document.getElementById('btnExportarPDF').addEventListener('click', function() {
                        $.ajax({
                            url: 'exportar_fluxo.php',
                            method: 'POST',
                            data: {
                                tipo: 'pdf',
                                data_ini: dataIni,
                                data_fim: dataFim
                            },
                            dataType: 'json',
                            success: function(response) {
                                const { jsPDF } = window.jspdf;
                                const doc = new jsPDF();
                                
                                const div = document.createElement('div');
                                div.innerHTML = response.html;
                                div.style.position = 'fixed';
                                div.style.top = '-9999px';
                                div.style.left = '-9999px';
                                div.style.width = '210mm';
                                div.style.padding = '10mm';
                                div.style.background = 'white';
                                document.body.appendChild(div);
                                
                                html2canvas(div, { scale: 2, useCORS: true, logging: false }).then(canvas => {
                                    const imgData = canvas.toDataURL('image/png');
                                    const pageHeight = doc.internal.pageSize.getHeight();
                                    const pageWidth = doc.internal.pageSize.getWidth();
                                    const imgHeight = (canvas.height * pageWidth) / canvas.width;
                                    let heightLeft = imgHeight;
                                    let position = 0;
                                    
                                    doc.addImage(imgData, 'PNG', 0, position, pageWidth, imgHeight);
                                    heightLeft -= pageHeight;
                                    
                                    while (heightLeft >= 0) {
                                        position = heightLeft - imgHeight;
                                        doc.addPage();
                                        doc.addImage(imgData, 'PNG', 0, position, pageWidth, imgHeight);
                                        heightLeft -= pageHeight;
                                    }
                                    
                                    doc.save(response.filename);
                                    document.body.removeChild(div);
                                });
                            },
                            error: function() {
                                alert('Erro ao gerar PDF');
                            }
                        });
                    });

                    // Gráfico Top Lucro
                    const ctxTop = document.getElementById('graficoTopLucro').getContext('2d');
                    new Chart(ctxTop, {
                        type: 'bar',
                        data: {
                            labels: ['Produto 1', 'Produto 2', 'Produto 3'],
                            datasets: [{
                                label: 'Lucro (R$)',
                                data: produtosTop.map(p => Number(p.lucro)),
                                backgroundColor: ['#52c41a', '#13c2c2', '#faad14'],
                            }, {
                                label: 'Vendidos',
                                data: produtosTop.map(p => Number(p.vendidos)),
                                backgroundColor: 'rgba(0,0,0,0.08)',
                                type: 'line',
                                borderColor: '#1890ff',
                                fill: false,
                                yAxisID: 'y1',
                            }]
                        },
                        options: {
                            plugins: { legend: { display: true, position: 'top' } },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Lucro (R$)' } },
                                y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Vendidos' }, grid: { drawOnChartArea: false } },
                                x: { ticks: { display: false } }
                            }
                        }
                    });

                    // Gráfico Low Lucro
                    const ctxLow = document.getElementById('graficoLowLucro').getContext('2d');
                    new Chart(ctxLow, {
                        type: 'bar',
                        data: {
                            labels: ['Produto 1', 'Produto 2', 'Produto 3'],
                            datasets: [{
                                label: 'Lucro (R$)',
                                data: produtosLow.map(p => Number(p.lucro)),
                                backgroundColor: ['#E53935', '#faad14', '#bfbfbf'],
                            }, {
                                label: 'Vendidos',
                                data: produtosLow.map(p => Number(p.vendidos)),
                                backgroundColor: 'rgba(0,0,0,0.08)',
                                type: 'line',
                                borderColor: '#1890ff',
                                fill: false,
                                yAxisID: 'y1',
                            }]
                        },
                        options: {
                            plugins: { legend: { display: true, position: 'top' } },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Lucro (R$)' } },
                                y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Vendidos' }, grid: { drawOnChartArea: false } },
                                x: { ticks: { display: false } }
                            }
                        }
                    });
                    </script>

                </div>
            </div>
        </div>
    </div>

           

</body>

</html>

<?php $sql->close(); ?>
