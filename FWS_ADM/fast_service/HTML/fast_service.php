<?php
include '../../conn.php';
$conn = $sql;

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

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];

// Processar POST primeiro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar_estado'], $_POST['pedido_id'], $_POST['novo_estado'])) {
        $pedido_id = intval($_POST['pedido_id']);
        $novo_estado = $_POST['novo_estado'];
        $conn->query("UPDATE vendas SET situacao_compra = '".$conn->real_escape_string($novo_estado)."' WHERE id = $pedido_id LIMIT 1");
        header('Location: fast_service.php');
        exit;
    }
    if (isset($_POST['realizar_pagamento'], $_POST['pedido_id'])) {
        $pedido_id = intval($_POST['pedido_id']);
        // Descontar do estoque e dos lotes
        $sql_itens = "SELECT produto_id, quantidade FROM itens_vendidos WHERE venda_id = $pedido_id";
        $res_itens = $conn->query($sql_itens);
        while ($item = $res_itens->fetch_assoc()) {
            $produto_id = intval($item['produto_id']);
            $qtd = intval($item['quantidade']);
            // Desconta do estoque principal
            $conn->query("UPDATE produtos SET estoque = estoque - $qtd WHERE id = $produto_id");
            // Registrar a saída na tabela movimentacao_estoque com o id da venda
            $conn->query("INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao, venda_id) 
                            VALUES ($produto_id, 'saida', $qtd, NOW(), $pedido_id)");
            // Desconta do lote com validade mais próxima
            $lote = $conn->query("SELECT id, quantidade FROM lotes_produtos WHERE produto_id = $produto_id AND quantidade > 0 ORDER BY validade ASC LIMIT 1");
            if ($lote && $lote->num_rows > 0) {
                $lote_row = $lote->fetch_assoc();
                $lote_id = intval($lote_row['id']);
                $lote_qtd = intval($lote_row['quantidade']);
                $qtd_descontar = min($qtd, $lote_qtd);
                $conn->query("UPDATE lotes_produtos SET quantidade = quantidade - $qtd_descontar WHERE id = $lote_id");
                $qtd -= $qtd_descontar;
                // Se ainda restar quantidade, desconta dos próximos lotes
                while ($qtd > 0) {
                    $lote = $conn->query("SELECT id, quantidade FROM lotes_produtos WHERE produto_id = $produto_id AND quantidade > 0 ORDER BY validade ASC LIMIT 1");
                    if ($lote && $lote->num_rows > 0) {
                        $lote_row = $lote->fetch_assoc();
                        $lote_id = intval($lote_row['id']);
                        $lote_qtd = intval($lote_row['quantidade']);
                        $qtd_descontar = min($qtd, $lote_qtd);
                        $conn->query("UPDATE lotes_produtos SET quantidade = quantidade - $qtd_descontar WHERE id = $lote_id");
                        $qtd -= $qtd_descontar;
                    } else {
                        break;
                    }
                }
            }
        }
        $conn->query("UPDATE vendas SET situacao_compra = 'finalizada', data_finalizacao = NOW() WHERE id = $pedido_id LIMIT 1");
        header('Location: fast_service.php');
        exit;
    }
}

$pagina = 'fast';

?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fast Service</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../menu_principal/CSS/menu_principal.css">
    <style>
        @import url('../../Fonte_Config/fonte_geral.css');
    body {
        background-color: #fff8e1;
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

    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }
    .container {
        max-width: 1200px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    /* ESTILOS PARA CARDS DE PEDIDOS */
    .venda-card {
        background: white;
        border: 2px solid #ff9100;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .venda-card-imagens {
        flex: 0 0 120px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        justify-content: center;
        align-items: center;
    }

    .venda-card-imagem {
        width: 120px;
        height: 90px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .venda-card-conteudo {
        flex: 1;
    }

    .venda-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #ff9100;
    }

    .venda-card-codigo {
        background: #ff9100;
        color: white;
        padding: 12px 18px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 1.1rem;
        box-shadow: 0 4px 8px rgba(255, 145, 0, 0.3);
        text-align: center;
    }

    .venda-card-codigo-label {
        font-size: 0.7rem;
        display: block;
        opacity: 0.9;
        margin-bottom: 4px;
        letter-spacing: 1px;
    }

    .venda-card-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .venda-card-info-item {
        display: flex;
        flex-direction: column;
    }

    .venda-card-info-label {
        color: #ff9100;
        font-weight: bold;
        font-size: 0.85rem;
    }

    .venda-card-info-valor {
        color: #333;
    }

    .venda-card-items {
        margin-bottom: 15px;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 5px;
        border-left: 4px solid #ff9100;
    }

    .venda-card-items-title {
        font-weight: bold;
        color: #ff9100;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .venda-item {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #ddd;
        font-size: 0.9rem;
    }

    .venda-item:last-child {
        border-bottom: none;
    }

    .venda-item-nome {
        flex: 1;
        color: #333;
        font-size: 0.95rem;
    }

    .venda-item-qty {
        margin: 0 15px;
        color: #ff9100;
        font-weight: bold;
        min-width: 60px;
        text-align: center;
    }

    .venda-item-preco {
        min-width: 90px;
        text-align: right;
        color: #333;
    }

    .venda-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 10px;
        border-top: 2px solid #ff9100;
        font-weight: bold;
        flex-wrap: wrap;
        gap: 10px;
    }

    .venda-total {
        color: #52c41a;
        font-size: 1.4rem;
    }

    .venda-status {
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: bold;
        color: white;
    }

    .venda-status.em-preparo,
    .venda-status.em_preparo {
        background-color: #FFD100;
        color: black;
    }

    .venda-status.pronto-para-retirar,
    .venda-status.pronto_para_retirar {
        background-color: #11C47E;
        color: white;
    }

    .venda-status.finalizada {
        background-color: #0a7c3a;
        color: white;
    }

    .venda-status.cancelada {
        background-color: #E53935;
        color: white;
    }

    .btn-acao {
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s;
    }

    .btn-acao-alterar {
        background-color: #ff9100;
        color: white;
        margin-right: 5px;
    }

    .btn-acao-alterar:hover {
        background-color: #ff7a00;
    }

    .btn-acao-pagar {
        background-color: #52c41a;
        color: white;
    }

    .btn-acao-pagar:hover {
        background-color: #389e0d;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #ff9100;
        font-weight: bold;
    }
        padding: 7px 16px;
        margin-left: 12px;
        background: #FFD100;
        color: #111;
        display: inline-block;
    }
    .pedido-info {
        display: flex;
        gap: 32px;
        margin-bottom: 10px;
    }

    .pedido-actions {
        margin-top: 12px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

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
                            <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0 <?php if($pagina=='painel') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0 <?php if($pagina=='fast') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0 <?php if($pagina=='financeiro') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0 <?php if($pagina=='vendas') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0 <?php if($pagina=='estoque') echo 'active'; ?>"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0 <?php if($pagina=='painel') echo 'produtos'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0 <?php if($pagina=='fornecedores') echo 'active'; ?>" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0 <?php if($pagina=='funcionarios') echo 'active'; ?>" id="cor-fonte">
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

            <!-- Conteúdo principal -->
            <div class="col py-3">
                <div class="container" style="max-width:1000px; margin-left:375px;">
                    <h3 id="texto" style="color: #ff9100;">Fast Service</h3>
                    <hr>
                    <h2>Gerenciar Pedidos</h2>

                    <!-- FILTROS -->
                    <div style="background: #fff8e1; border: 2px solid #ff9100; border-radius: 10px; padding: 20px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                        <form method="get" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; width: 100%;">
                            <!-- Filtro por Número do Pedido (Ordem) -->
                            <div>
                                <label for="filtro_numero" style="display: block; font-weight: bold; color: #ff9100; margin-bottom: 5px; font-size: 0.9rem;">Nº do Pedido</label>
                                <select name="filtro_numero" id="filtro_numero" class="form-select form-select-sm" style="width: 180px;">
                                    <option value="desc" <?= isset($_GET['filtro_numero']) && $_GET['filtro_numero'] === 'desc' ? 'selected' : 'selected' ?>>Decrescente (maior)</option>
                                    <option value="asc" <?= isset($_GET['filtro_numero']) && $_GET['filtro_numero'] === 'asc' ? 'selected' : '' ?>>Crescente (menor)</option>
                                </select>
                            </div>

                            <!-- Filtro por Status -->
                            <div>
                                <label for="filtro_status" style="display: block; font-weight: bold; color: #ff9100; margin-bottom: 5px; font-size: 0.9rem;">Status</label>
                                <select name="filtro_status" id="filtro_status" class="form-select form-select-sm" style="width: 180px;">
                                    <option value="">-- Todos os status --</option>
                                    <option value="em_preparo" <?= isset($_GET['filtro_status']) && $_GET['filtro_status'] === 'em_preparo' ? 'selected' : '' ?>>Em Preparação</option>
                                    <option value="pronto_para_retirar" <?= isset($_GET['filtro_status']) && $_GET['filtro_status'] === 'pronto_para_retirar' ? 'selected' : '' ?>>Pronto para Retirar</option>
                                    <option value="finalizada" <?= isset($_GET['filtro_status']) && $_GET['filtro_status'] === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                                    <option value="cancelada" <?= isset($_GET['filtro_status']) && $_GET['filtro_status'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>

                            <!-- Filtro por Tempo Restante (mais próximo) -->
                            <div>
                                <label for="filtro_tempo" style="display: block; font-weight: bold; color: #ff9100; margin-bottom: 5px; font-size: 0.9rem;">Tempo Restante</label>
                                <select name="filtro_tempo" id="filtro_tempo" class="form-select form-select-sm" style="width: 180px;">
                                    <option value="">-- Sem filtro --</option>
                                    <option value="proximo" <?= isset($_GET['filtro_tempo']) && $_GET['filtro_tempo'] === 'proximo' ? 'selected' : '' ?>>Mais próximo</option>
                                    <option value="distante" <?= isset($_GET['filtro_tempo']) && $_GET['filtro_tempo'] === 'distante' ? 'selected' : '' ?>>Mais distante</option>
                                </select>
                            </div>

                            <!-- Filtro por Quantidade de Itens -->
                            <div>
                                <label for="filtro_quantidade" style="display: block; font-weight: bold; color: #ff9100; margin-bottom: 5px; font-size: 0.9rem;">Quantidade de Itens</label>
                                <select name="filtro_quantidade" id="filtro_quantidade" class="form-select form-select-sm" style="width: 180px;">
                                    <option value="">-- Sem filtro --</option>
                                    <option value="maior" <?= isset($_GET['filtro_quantidade']) && $_GET['filtro_quantidade'] === 'maior' ? 'selected' : '' ?>>Maior quantidade</option>
                                    <option value="menor" <?= isset($_GET['filtro_quantidade']) && $_GET['filtro_quantidade'] === 'menor' ? 'selected' : '' ?>>Menor quantidade</option>
                                </select>
                            </div>

                            <!-- Botões -->
                            <div style="display: flex; gap: 8px;">
                                <button type="submit" class="btn btn-sm" style="background-color: #ff9100; color: white; border: none; padding: 8px 16px; border-radius: 5px; font-weight: bold;">Filtrar</button>
                                <a href="fast_service.php" class="btn btn-sm" style="background-color: #ddd; color: #333; border: none; padding: 8px 16px; border-radius: 5px; font-weight: bold; text-decoration: none;">Limpar</a>
                            </div>
                        </form>
                    </div>

                    <?php
                    // Aplicar filtros
                    $where_conditions = [];

                    // Ordenação por Número do Pedido
                    $ordem_numero = isset($_GET['filtro_numero']) && $_GET['filtro_numero'] === 'asc' ? 'ASC' : 'DESC';

                    // Filtro por Status
                    if (!empty($_GET['filtro_status'])) {
                        $status = $conn->real_escape_string($_GET['filtro_status']);
                        $where_conditions[] = "v.situacao_compra = '$status'";
                    }

                    // Filtro por Tempo Restante (precisa contar itens primeiro)
                    $filtro_tempo = !empty($_GET['filtro_tempo']) ? $_GET['filtro_tempo'] : '';

                    // Filtro por Quantidade de Itens (precisa contar itens)
                    $filtro_quantidade = !empty($_GET['filtro_quantidade']) ? $_GET['filtro_quantidade'] : '';

                    // Montar a query base
                    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
                    
                    // Buscar todos os pedidos com contagem de itens (ordenado conforme seleção)
                    $sql_all = "SELECT v.id, v.data_criacao, v.total, v.situacao_compra, v.usuario_id, v.metodo_pagamento, v.tempo_chegada,
                                       u.telefone, u.nome as usuario_nome,
                                       COUNT(iv.id) as qtd_itens
                                FROM vendas v 
                                LEFT JOIN usuarios u ON v.usuario_id = u.id 
                                LEFT JOIN itens_vendidos iv ON v.id = iv.venda_id
                                $where_clause
                                GROUP BY v.id
                                ORDER BY v.id $ordem_numero";
                    $res_all = $conn->query($sql_all);
                    
                    // Processar filtros adicionais em PHP
                    $pedidos_processados = [];
                    while ($pedido = $res_all->fetch_assoc()) {
                        $include = true;

                        // Aplicar filtro de tempo (apenas se status é em_preparo ou pronto_para_retirar)
                        if ($filtro_tempo && in_array($pedido['situacao_compra'], ['em_preparo', 'pronto_para_retirar']) && !empty($pedido['tempo_chegada'])) {
                            $data_criacao = strtotime($pedido['data_criacao']);
                            list($h, $m, $s) = explode(':', $pedido['tempo_chegada']);
                            $tempo_chegada = $h * 3600 + $m * 60 + $s;
                            $deadline = $data_criacao + $tempo_chegada;
                            $restante = $deadline - time();
                            $pedido['tempo_restante'] = max(0, $restante);
                        } else {
                            $pedido['tempo_restante'] = null;
                        }

                        // Aplicar filtro de quantidade
                        if ($filtro_quantidade === 'maior') {
                            if ($pedido['qtd_itens'] < 3) $include = false;
                        } elseif ($filtro_quantidade === 'menor') {
                            if ($pedido['qtd_itens'] >= 3) $include = false;
                        }

                        if ($include) {
                            $pedidos_processados[] = $pedido;
                        }
                    }

                    // Aplicar ordenação por tempo (mais próximo ou distante)
                    if ($filtro_tempo === 'proximo') {
                        usort($pedidos_processados, function($a, $b) {
                            $tempo_a = $a['tempo_restante'] !== null ? $a['tempo_restante'] : PHP_INT_MAX;
                            $tempo_b = $b['tempo_restante'] !== null ? $b['tempo_restante'] : PHP_INT_MAX;
                            return $tempo_a <=> $tempo_b;
                        });
                    } elseif ($filtro_tempo === 'distante') {
                        usort($pedidos_processados, function($a, $b) {
                            $tempo_a = $a['tempo_restante'] !== null ? $a['tempo_restante'] : 0;
                            $tempo_b = $b['tempo_restante'] !== null ? $b['tempo_restante'] : 0;
                            return $tempo_b <=> $tempo_a;
                        });
                    }
                    
                    // Função para mapear status
                    function get_status_label($status) {
                        $map = [
                            'em_preparo' => 'Em Preparação',
                            'em_preparacao' => 'Em Preparação',
                            'pronto_para_retirar' => 'Pronto para Retirar',
                            'finalizada' => 'Finalizada',
                            'cancelada' => 'Cancelada',
                            'pendente' => 'Pendente'
                        ];
                        return isset($map[$status]) ? $map[$status] : ucfirst(str_replace('_', ' ', $status));
                    }

                    // Função para mapear método pagamento
                    function formatar_pagamento($metodo) {
                        $map = [
                            'cartao_credito' => 'Cartão de Crédito',
                            'cartao_debito' => 'Cartão de Débito',
                            'pix' => 'PIX',
                            'dinheiro' => 'Dinheiro'
                        ];
                        return isset($map[$metodo]) ? $map[$metodo] : ucfirst(str_replace('_', ' ', $metodo));
                    }
                    
                    if (!empty($pedidos_processados)) {
                        foreach ($pedidos_processados as $pedido) {
                            $telefone = preg_replace('/\D/', '', $pedido['telefone']);
                            $ultimos_4_telefone = substr($telefone, -4);
                            $codigo_venda = $ultimos_4_telefone . '-' . $pedido['id'];
                            $pedido_id = intval($pedido['id']);

                            // Buscar itens da venda
                            $sql_itens = "SELECT iv.produto_id, iv.quantidade, p.nome, p.foto_produto, p.preco_venda
                                         FROM itens_vendidos iv
                                         LEFT JOIN produtos p ON iv.produto_id = p.id
                                         WHERE iv.venda_id = $pedido_id";
                            $result_itens = $conn->query($sql_itens);
                            $itens = [];
                            if ($result_itens && $result_itens->num_rows > 0) {
                                while ($item = $result_itens->fetch_assoc()) {
                                    $itens[] = $item;
                                }
                            }
                            ?>
                            <div class="venda-card">
                                <!-- Imagens dos produtos -->
                                <div class="venda-card-imagens">
                                    <?php 
                                    $imgs_count = 0;
                                    foreach ($itens as $item):
                                        if ($imgs_count < 3):
                                            ?>
                                            <img src="<?= !empty($item['foto_produto']) ? $item['foto_produto'] : '../../IMG/sem-imagem.png' ?>" 
                                                 alt="<?= htmlspecialchars($item['nome']) ?>" class="venda-card-imagem">
                                            <?php 
                                            $imgs_count++;
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>

                                <!-- Conteúdo -->
                                <div class="venda-card-conteudo">
                                    <!-- Header com código e status -->
                                    <div class="venda-card-header">
                                        <div>
                                            <div style="font-size:0.8rem; color:#666;">Nº do Pedido</div>
                                            <div style="font-weight:bold; font-size:1.4rem;"><?= $pedido['id'] ?></div>
                                        </div>
                                        <div style="display:flex; gap:15px; align-items:flex-end; flex-wrap:wrap;">
                                            <?php if (in_array($pedido['situacao_compra'], ['em_preparo', 'pronto_para_retirar']) && !empty($pedido['tempo_chegada'])): ?>
                                                <?php 
                                                    $data_criacao = strtotime($pedido['data_criacao']);
                                                    list($h, $m, $s) = explode(':', $pedido['tempo_chegada']);
                                                    $tempo_chegada = $h * 3600 + $m * 60 + $s;
                                                    $deadline = $data_criacao + $tempo_chegada;
                                                    $restante = $deadline - time();
                                                    $tempo_restante = ($restante > 0) ? sprintf('%02d:%02d:%02d', floor($restante/3600), floor(($restante%3600)/60), $restante%60) : '00:00:00';
                                                    $id_timer = 'timer_' . $pedido['id'];
                                                ?>
                                                <div style="display: flex; flex-direction: column; align-items: center;">
                                                    <div style="font-size: 0.75rem; color: #666; margin-bottom: 3px; font-weight: bold;">⏱️ TEMPO RESTANTE</div>
                                                    <div style="background: #FFD100; color: black; padding: 8px 14px; border-radius: 6px; font-weight: bold; font-size: 1rem; font-family: 'Courier New', monospace;" id="<?= $id_timer ?>">
                                                        <?= $tempo_restante ?>
                                                    </div>
                                                </div>
                                                <script>
                                                    window._timers = window._timers || [];
                                                    window._timers.push({
                                                        id: "<?= $id_timer ?>",
                                                        data_criacao: "<?= $pedido['data_criacao'] ?>",
                                                        tempo_chegada: "<?= $pedido['tempo_chegada'] ?>"
                                                    });
                                                </script>
                                            <?php endif; ?>
                                            <div class="venda-status <?= strtolower(str_replace('_', '-', $pedido['situacao_compra'])) ?>" style="padding: 10px 18px; font-size: 1rem;">
                                                <?= get_status_label($pedido['situacao_compra']) ?>
                                            </div>
                                            <div class="venda-card-codigo">
                                                <div class="venda-card-codigo-label">CÓDIGO DO PEDIDO</div>
                                                <?= $codigo_venda ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informações -->
                                    <div class="venda-card-info">
                                        <div class="venda-card-info-item">
                                            <span class="venda-card-info-label">Data do Pedido</span>
                                            <span class="venda-card-info-valor"><?= date('d/m/Y H:i', strtotime($pedido['data_criacao'])) ?></span>
                                        </div>
                                        <div class="venda-card-info-item">
                                            <span class="venda-card-info-label">Cliente</span>
                                            <span class="venda-card-info-valor"><?= htmlspecialchars($pedido['usuario_nome']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Itens vendidos -->
                                    <div class="venda-card-items">
                                        <div class="venda-card-items-title">📦 Itens do Pedido</div>
                                        <?php 
                                        foreach ($itens as $item): 
                                            $subtotal = $item['preco_venda'] * $item['quantidade'];
                                        ?>
                                            <div class="venda-item">
                                                <span class="venda-item-nome"><?= htmlspecialchars($item['nome']) ?></span>
                                                <span class="venda-item-qty">Qtd: <?= intval($item['quantidade']) ?></span>
                                                <span style="min-width:80px; text-align:right; color:#666; font-size:0.85rem;">R$ <?= number_format($item['preco_venda'], 2, ',', '.') ?></span>
                                                <span class="venda-item-preco">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Footer com total e ações -->
                                    <div class="venda-card-footer">
                                        <div>
                                            <span style="color:#666; font-size:0.9rem;">Total: </span>
                                            <span class="venda-total">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
                                        </div>
                                        <div class="pedido-actions">
                                            <?php if (!in_array($pedido['situacao_compra'], ['finalizada', 'cancelada'])): ?>
                                                <!-- Botão Alterar Estado -->
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                                    <select name="novo_estado" class="form-select form-select-sm" style="width:auto;display:inline-block;margin-right:8px;">
                                                        <option value="">-- Alterar Estado --</option>
                                                        <option value="em_preparo" <?= $pedido['situacao_compra'] === 'em_preparo' ? 'selected' : '' ?>>Em Preparação</option>
                                                        <option value="pronto_para_retirar" <?= $pedido['situacao_compra'] === 'pronto_para_retirar' ? 'selected' : '' ?>>Pronto para Retirar</option>
                                                    </select>
                                                    <button type="submit" name="alterar_estado" class="btn-acao btn-acao-alterar">Alterar</button>
                                                </form>
                                                <!-- Botão Finalizar Pedido -->
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                                    <button type="button" class="btn-acao btn-acao-pagar" onclick="confirmarPagamento(this)">Finalizar Pedido</button>
                                                </form>
                                            <?php endif; ?>
                                            <!-- Forma de Pagamento (mostrar em todos os pedidos) -->
                                            <div style="display: inline-block; margin-left: 15px; padding: 8px 14px; background: #f0f0f0; border-radius: 5px; font-size: 0.9rem; color: #333; border: 1px solid #ddd;">
                                                <span style="color: #666; font-weight: bold;">Pagamento:</span> <?= formatar_pagamento($pedido['metodo_pagamento']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div style="text-align:center; padding:40px; color:#999;">Nenhum pedido encontrado</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação para finalizar pedido -->
    <div id="modal-confirm-pagamento" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:40px;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.2);text-align:center;max-width:400px;margin:auto;">
            <h3 style="color:#ff9100; margin-bottom:20px; font-weight:bold;">Confirmar Finalização do Pedido</h3>
            <p style="font-size:1.1rem; margin-bottom:25px; color:#333;">
                <strong><?= htmlspecialchars($nome_adm) ?></strong>, você tem certeza que deseja finalizar este pedido?
            </p>
            <form id="form-confirm-pagamento" method="post">
                <input type="hidden" name="pedido_id" id="modal-pedido-id" value="">
                <div style="display:flex; gap:10px; justify-content:center;">
                    <button type="submit" name="realizar_pagamento" class="btn-acao btn-acao-pagar" style="margin:0; padding:10px 30px;">Finalizar</button>
                    <button type="button" class="btn-acao" style="background-color:#999; color:white; margin:0; padding:10px 30px;" onclick="document.getElementById('modal-confirm-pagamento').style.display='none'">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarPagamento(btn) {
        var form = btn.closest('form');
        var pedidoId = form.querySelector('input[name="pedido_id"]').value;
        document.getElementById('modal-pedido-id').value = pedidoId;
        document.getElementById('modal-confirm-pagamento').style.display = 'flex';
    }

    // Contagem regressiva do tempo
    function atualizarTimers() {
        if (window._timers && window._timers.length > 0) {
            window._timers.forEach(function(timer) {
                var el = document.getElementById(timer.id);
                if (el) {
                    var data_criacao = new Date(timer.data_criacao).getTime();
                    var parts = timer.tempo_chegada.split(':');
                    var tempo_chegada = parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + parseInt(parts[2]);
                    var deadline = data_criacao + (tempo_chegada * 1000);
                    var agora = new Date().getTime();
                    var restante = deadline - agora;

                    if (restante > 0) {
                        var horas = Math.floor(restante / 3600000);
                        var minutos = Math.floor((restante % 3600000) / 60000);
                        var segundos = Math.floor((restante % 60000) / 1000);
                        el.textContent = String(horas).padStart(2, '0') + ':' + String(minutos).padStart(2, '0') + ':' + String(segundos).padStart(2, '0');
                    } else {
                        el.textContent = '00:00:00';
                    }
                }
            });
        }
    }

    // Atualizar a cada segundo
    setInterval(atualizarTimers, 1000);
    // Atualizar imediatamente
    atualizarTimers();
    
    // Recarregar a página a cada 1 minuto (60000 ms)
    setInterval(function() {
        location.reload();
    }, 60000);
    </script>
</body>
</html>

<?php $sql->close(); ?>
