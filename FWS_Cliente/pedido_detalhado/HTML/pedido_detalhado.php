<?php
session_start();
include "../../conn.php";

// Receber o ID da venda via GET
$venda_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Buscar informações da venda
$venda = null;
$itens = [];
$usuario_nome = '';
$usuario_telefone = '';
$codigo = '';

if ($venda_id > 0) {
    $sql_venda = "SELECT * FROM vendas WHERE id = $venda_id";
    $result_venda = mysqli_query($conn, $sql_venda);
    if ($result_venda && $row = mysqli_fetch_assoc($result_venda)) {
        $venda = $row;
        $usuario_id = intval($venda['usuario_id']);
        
        // Buscar dados do usuário (nome e telefone)
        $sql_usuario = "SELECT nome, telefone FROM usuarios WHERE id = $usuario_id LIMIT 1";
        $result_usuario = mysqli_query($conn, $sql_usuario);
        if ($result_usuario && $row_usuario = mysqli_fetch_assoc($result_usuario)) {
            $usuario_nome = htmlspecialchars($row_usuario['nome']);
            $usuario_telefone = preg_replace('/[^0-9]/', '', $row_usuario['telefone']);
            // Gerar código único para este pedido: últimos 2 dígitos do telefone + últimos 2 dígitos do ID da venda
            $telefone_ultimos = $usuario_telefone ? substr($usuario_telefone, -2) : '00';
            $venda_id_ultimos = str_pad($venda_id % 100, 2, '0', STR_PAD_LEFT);
            $codigo = $telefone_ultimos . $venda_id_ultimos;
        }
        
        // Buscar itens da venda
        $sql_itens = "SELECT iv.*, p.nome, p.foto_produto FROM itens_vendidos iv INNER JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = $venda_id";
        $result_itens = mysqli_query($conn, $sql_itens);
        if ($result_itens) {
            while ($row_item = mysqli_fetch_assoc($result_itens)) {
                $itens[] = $row_item;
            }
        }
    }
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <title>Meus pedidos</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <link rel="stylesheet" href="../CSS/pedido_detalhado.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- JQuery UI css -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />


    <style>
        #header {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #c40000;
            /* vermelho */
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        nav {
            margin-left: 40px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 15px;
        }

        .carrinho {
            margin-left: auto;
            margin-right: 20px;
        }

        .carrinho img {
            height: 25px;
        }

        #bem-vindo {
            font-weight: bold;
            font-size: 16px;
            color: white;
        }

        .btn-acoes {
            background-color: #f37a27;
            color: white !important;
            border-radius: 6px;
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-acoes:hover {
            opacity: 0.9;
        }

        /* Paleta do site (used by header accents) */
        :root {
            --primary-red: #c40000;
            --accent-yellow: #FFD100;
            --accent-orange: #f37a27;
        }

        /* Header styles (kept minimal for this page) */
        #header {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: var(--primary-red);
            color: white;
            position: relative;
        }

        .logo img {
            height: 45px;
        }

        .menu-toggle {
            display: none;
            font-size: 28px;
            background: none;
            border: none;
            color: white;
            margin-left: auto;
            cursor: pointer;
        }

        nav {
            margin-left: 40px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            font-size: 15px;
        }

        .carrinho img {
            height: 27px;
        }

        #bem-vindo {
            font-weight: bold;
            font-size: 16px;
            color: white;
        }

        .no-underline {
            text-decoration: none !important;
        }

        /* Acentos usados nos outros arquivos do site */
        .accent-text {
            color: var(--primary-red) !important;
        }

        .card-accent {
            border-color: var(--accent-yellow) !important;
        }

        .btn-accent {
            background-color: var(--accent-yellow) !important;
            color: #111 !important;
            border: none;
        }

        /* Responsividade: header adjustments for mobile */
        @media (max-width: 768px) {
            nav {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background: var(--primary-red);
                padding: 20px 0;
                display: none;
                z-index: 2000;
            }

            nav.active {
                display: block;
            }

            nav ul {
                flex-direction: column;
                text-align: center;
                gap: 14px;
            }

            .menu-toggle {
                display: block;
            }

            .carrinho,
            #bem-vindo {
                display: none;
            }
        }

        /* Responsividade: Card de Pedido */
        @media (max-width: 768px) {

            /* Cabeçalho do Card */
            .card-header-pedido {
                flex-direction: column !important;
                align-items: center !important;
                gap: 15px !important;
                padding: 15px 20px !important;
                text-align: center !important;
            }

            .card-header-pedido>div:first-child {
                width: 100%;
                text-align: left !important;
            }

            .card-header-pedido>div:nth-child(2) {
                width: 100%;
                text-align: center !important;
            }

            .card-header-pedido>div:last-child {
                width: 100%;
                text-align: center !important;
            }

            .card-header-pedido h2 {
                font-size: 1.2rem !important;
            }

            /* Destaque do código */
            .card-header-pedido>div:nth-child(2)>div {
                margin-bottom: 10px !important;
            }

            /* Título da página */
            main h1 {
                font-size: 1.5rem !important;
                margin-bottom: 20px !important;
            }

            /* Seções do Card */
            .card-section h3 {
                font-size: 1rem !important;
                margin-bottom: 12px !important;
            }

            /* Grid de informações do pedido */
            .info-grid {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }

            /* Tabela de itens */
            .items-table {
                font-size: 0.85rem !important;
            }

            .items-table th,
            .items-table td {
                padding: 8px !important;
            }

            .items-table th {
                font-size: 0.75rem !important;
            }

            /* Resumo financeiro */
            .financial-summary {
                padding: 15px !important;
                border-left-width: 3px !important;
            }

            .financial-row {
                font-size: 0.95rem !important;
            }

            .financial-row span:last-child {
                font-size: 0.95rem !important;
            }

            .financial-total {
                font-size: 1.1rem !important;
            }

            .financial-total span:last-child {
                font-size: 1.1rem !important;
            }

            /* Botões de ação */
            .action-buttons {
                flex-direction: column !important;
                gap: 10px !important;
            }

            .action-buttons button,
            .action-buttons a {
                min-width: 100% !important;
                flex: 1 !important;
                font-size: 0.9rem !important;
                padding: 10px 15px !important;
            }

            /* Conteúdo do card */
            .card-content {
                padding: 20px !important;
            }
        }

        @media (max-width: 480px) {

            /* Ajustes adicionais para telas muito pequenas */
            .card {
                border-width: 2px !important;
            }

            .card-header-pedido {
                padding: 12px 15px !important;
            }

            .card-header-pedido h2 {
                font-size: 1rem !important;
            }

            .card-header-pedido p {
                font-size: 0.8rem !important;
            }

            /* Destaque do código em mobile */
            .card-header-pedido>div:first-child>div {
                padding: 8px 12px !important;
                margin-bottom: 8px !important;
            }

            .card-header-pedido>div:first-child>div p:first-child {
                font-size: 0.75rem !important;
            }

            .card-header-pedido>div:first-child>div p:last-child {
                font-size: 1.3rem !important;
                margin-top: 4px !important;
            }

            main {
                padding: 20px 10px !important;
            }

            main h1 {
                font-size: 1.3rem !important;
            }

            .card-section h3 {
                font-size: 0.95rem !important;
            }

            .card-content {
                padding: 15px !important;
            }

            /* Tabela responsiva para telas pequenas */
            .items-table thead {
                font-size: 0.7rem !important;
            }

            .items-table th {
                padding: 6px 4px !important;
            }

            .items-table td {
                padding: 6px 4px !important;
                font-size: 0.8rem !important;
            }

            .financial-summary {
                padding: 12px !important;
            }

            .financial-row {
                font-size: 0.85rem !important;
            }

            .financial-total {
                font-size: 1rem !important;
            }
        }

        /* Responsividade: Modal de Pagamento */
        @media (max-width: 768px) {
            #modal-pagamento>div {
                max-width: 90% !important;
                width: 90% !important;
            }

            #modal-pagamento>div h3 {
                font-size: 1.1rem !important;
                margin-bottom: 15px !important;
            }

            .payment-option {
                padding: 12px !important;
                gap: 10px !important;
            }

            .payment-option i {
                font-size: 1.1rem !important;
            }

            .payment-option p {
                font-size: 0.9rem !important;
            }

            .payment-option p:first-child {
                font-size: 0.95rem !important;
            }

            #btn-fechar-modal {
                font-size: 0.9rem !important;
                padding: 8px !important;
            }
        }

        @media (max-width: 480px) {
            #modal-pagamento>div {
                max-width: 95% !important;
                padding: 20px !important;
            }

            #modal-pagamento>div h3 {
                font-size: 1rem !important;
                margin-bottom: 12px !important;
            }

            .payment-option {
                padding: 10px !important;
                gap: 8px !important;
            }

            .payment-option i {
                font-size: 1rem !important;
            }

            .payment-option p {
                font-size: 0.85rem !important;
            }

            .payment-option p:first-child {
                font-size: 0.9rem !important;
            }
        }
    </style>
</head>

<body>
    <!-- Cabeçalho -->
    <header id="header">
        <div class="logo">
            <a href="../../index.php">
                <img src="/TCC_FWS/FWS_Cliente/index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <button class="menu-toggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav>
            <ul class="ul align-items-center">
                <li><a href="/TCC_FWS/FWS_Cliente/produto/HTML/produto.php">Produtos</a></li>
                <li>
                    <form class="d-flex" role="search" action="/TCC_FWS/FWS_Cliente/produto/HTML/produto.php"
                        method="get" style="margin: 0 10px;">
                        <input id="search" class="form-control form-control-sm me-2" type="search" name="q"
                            placeholder="Pesquisar..." aria-label="Pesquisar">
                        <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </li>
                <li><a href="Meus_pedidos.php">Meus pedidos</a></li>
                <li><a href="/TCC_FWS/FWS_Cliente/tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
            </ul>
        </nav>

        <div class="carrinho">
            <a href="/TCC_FWS/FWS_Cliente/carrinho/HTML/carrinho.php">
                <img src="/TCC_FWS/FWS_Cliente/index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
            </a>
        </div>

        <div id="bem-vindo" style="position: relative; display: inline-block;">
            <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
            <?php
                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                $primeiroNome = explode(' ', $nomeCompleto)[0];
            ?>
            Bem-vindo(a), <?= $primeiroNome ?>
            <div style="display: inline-block; margin-left: 8px; cursor: pointer;" id="user-menu-toggle">
                <i class="fas fa-user-circle fa-2x" style="max-width: 90px;"></i>
            </div>

            <div id="user-menu"
                style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 0; min-width: 120px; z-index: 1000;">
                <a href="/TCC_FWS/FWS_Cliente/info_usuario/HTML/info_usuario.php"
                    style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
                <a href="/TCC_FWS/FWS_Cliente/logout.php" id="logout-link"
                    style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Sair</a>
            </div>

            <script>
                document.getElementById('user-menu-toggle').addEventListener('click', function () {
                    var menu = document.getElementById('user-menu');
                    if (menu.style.display === 'none') {
                        menu.style.display = 'block';
                    } else {
                        menu.style.display = 'none';
                    }
                });

                // Fecha o menu se clicar fora
                document.addEventListener('click', function (event) {
                    var container = document.getElementById('bem-vindo');
                    var menu = document.getElementById('user-menu');
                    if (!container.contains(event.target)) {
                        menu.style.display = 'none';
                    }
                });
            </script>
            <?php else: ?>
            Bem-vindo(a).
            <?php endif; ?>
        </div>

        <script>
            $(function () {
                var autocomplete = $("#search").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: '/TCC_FWS/FWS_Cliente/produto/PHP/api-produtos.php',
                            dataType: 'json',
                            data: {
                                q: request.term
                            },
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 2,
                    select: function (event, ui) {
                        window.location.href =
                            'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                    }
                }).data('ui-autocomplete') || $("#search").data('autocomplete');

                if (autocomplete) {
                    autocomplete._renderItem = function (ul, item) {
                        return $("<li>")
                            .append("<div><img src='" + item.foto +
                                "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle; background-color: #FFD100 !important;'/>" +
                                item.label + "</div>")
                            .appendTo(ul);
                    };
                }
            });
        </script>
    </header>

    <!-- Corpo principal -->
    <main style="background-color: #eee; min-height: calc(100vh - 200px); padding: 40px 20px;">
        <div class="container" style="max-width: 900px;">
            <!-- Título da página -->
            <h1
                style="font-size: 2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 30px; text-align: center;">
                Detalhes do Pedido
            </h1>

            <!-- Card Principal do Pedido -->
            <div class="card"
                style="border: 3px solid var(--accent-yellow); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden;">

                <!-- Cabeçalho do Card -->
                <div class="card-header-pedido"
                    style="background: linear-gradient(135deg, var(--primary-red) 0%, #a00000 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold;">Pedido
                            #<?= $venda ? htmlspecialchars($venda['id']) : 'N/A' ?></h2>
                        <p style="margin: 5px 0 0 0; font-size: 0.95rem; opacity: 0.95;">Realizado em
                            <?= $venda ? date('d/m/Y H:i', strtotime($venda['data_criacao'])) : 'N/A' ?></p>
                    </div>
                    <div style="display: flex; gap: 20px; align-items: center;">
                        <div
                            style="background: rgba(255, 209, 0, 0.25); padding: 10px 16px; border-radius: 8px; text-align: center;">
                            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Código</p>
                            <p
                                style="margin: 5px 0 0 0; font-size: 1.3rem; font-weight: bold; color: #FFD100; letter-spacing: 2px;">
                                <?= $codigo ?: 'N/A' ?></p>
                        </div>
                        <div
                            style="background: rgba(255,255,255,0.2); padding: 10px 18px; border-radius: 8px; font-weight: bold; text-align: center; white-space: nowrap;">
                            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Status</p>
                            <p style="margin: 5px 0 0 0; font-size: 1.1rem;">
                                <?php
                                if ($venda) {
                                    $status_map = [
                                        'em_preparo' => 'Ainda não foi retirado',
                                        'pronto_para_retirar' => 'Pronto para retirar',
                                        'finalizada' => 'Finalizado',
                                        'cancelada' => 'Cancelado'
                                    ];
                                    echo isset($status_map[$venda['situacao_compra']]) ? $status_map[$venda['situacao_compra']] : ucfirst($venda['situacao_compra']);
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo do Card -->
                <div class="card-content" style="padding: 30px;">

                    <!-- Seção: Informações do Pedido -->
                    <div class="card-section" style="margin-bottom: 30px;">
                        <h3
                            style="font-size: 1.2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 15px; border-bottom: 2px solid var(--accent-yellow); padding-bottom: 10px;">
                            📋 Informações do Pedido
                        </h3>
                        <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Cliente:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">
                                    <?= $usuario_nome ?: 'N/A' ?></p>
                            </div>
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Pagamento:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">
                                    <?= $venda ? ucwords(str_replace('_', ' ', $venda['metodo_pagamento'])) : 'N/A' ?>
                                </p>
                            </div>
                            <?php if ($venda && in_array($venda['situacao_compra'], ['em_preparo', 'pronto_para_retirar']) && !empty($venda['tempo_chegada'])): ?>
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Tempo
                                    Restante:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">
                                    <i class="fas fa-hourglass-end" style="color: var(--accent-orange);"></i>
                                    <span id="timer-display"><?= $venda['tempo_chegada'] ?></span>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Seção: Itens do Pedido -->
                    <div class="card-section" style="margin-bottom: 30px;">
                        <h3
                            style="font-size: 1.2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 15px; border-bottom: 2px solid var(--accent-yellow); padding-bottom: 10px;">
                            🛒 Itens do Pedido
                        </h3>
                        <div
                            style="background: #f9f9f9; border-radius: 8px; overflow-x: auto; border: 1px solid #e0e0e0;">
                            <table class="items-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background-color: var(--accent-yellow); color: #111;">
                                        <th style="padding: 12px; text-align: left; font-weight: bold;">Qtd</th>
                                        <th style="padding: 12px; text-align: left; font-weight: bold;">Produto</th>
                                        <th style="padding: 12px; text-align: right; font-weight: bold;">Valor Unit.
                                        </th>
                                        <th style="padding: 12px; text-align: right; font-weight: bold;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($itens)) {
                                        foreach ($itens as $item) {
                                            $subtotal = floatval($item['preco_unitario']) * intval($item['quantidade']);
                                            echo '<tr style="border-bottom: 1px solid #e0e0e0;">';
                                            echo '<td style="padding: 12px; text-align: left;">' . intval($item['quantidade']) . '</td>';
                                            echo '<td style="padding: 12px; text-align: left; color: #333;">' . htmlspecialchars($item['nome']) . '</td>';
                                            echo '<td style="padding: 12px; text-align: right; color: #555;">R$ ' . number_format(floatval($item['preco_unitario']), 2, ',', '.') . '</td>';
                                            echo '<td style="padding: 12px; text-align: right; font-weight: bold; color: var(--primary-red);">R$ ' . number_format($subtotal, 2, ',', '.') . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" style="padding: 12px; text-align: center; color: #999;">Nenhum item encontrado.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção: Resumo Financeiro -->
                    <div class="financial-summary"
                        style="margin-bottom: 30px; background: linear-gradient(to right, rgba(255, 209, 0, 0.1), rgba(196, 0, 0, 0.05)); padding: 20px; border-radius: 8px; border-left: 4px solid var(--accent-yellow);">
                        <?php
                        $subtotal = 0;
                        $frete = 0;
                        $desconto = 0;
                        if ($venda) {
                            foreach ($itens as $item) {
                                $subtotal += floatval($item['preco_unitario']) * intval($item['quantidade']);
                            }
                            // Você pode adicionar lógica de frete e desconto aqui se necessário
                            $frete = floatval($venda['frete'] ?? 0);
                            $desconto = floatval($venda['desconto'] ?? 0);
                        }
                        $total = $subtotal + $frete - $desconto;
                        ?>
                        <div class="financial-row"
                            style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Subtotal:</span>
                            <span style="color: #555;">R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                        </div>
                        <div class="financial-row"
                            style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Frete:</span>
                            <span style="color: #555;">R$ <?= number_format($frete, 2, ',', '.') ?></span>
                        </div>
                        <div class="financial-row"
                            style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Desconto:</span>
                            <span style="color: #555;">-R$ <?= number_format($desconto, 2, ',', '.') ?></span>
                        </div>
                        <hr style="margin: 10px 0; border: none; border-top: 1px solid rgba(0,0,0,0.1);">
                        <div class="financial-total" style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold; font-size: 1.1rem; color: var(--primary-red);">Total:</span>
                            <span style="font-weight: bold; font-size: 1.3rem; color: var(--primary-red);">R$
                                <?= number_format($total, 2, ',', '.') ?></span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="action-buttons" style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <?php if ($venda && $venda['situacao_compra'] !== 'cancelada'): ?>
                        <button
                            style="flex: 1; min-width: 150px; padding: 12px 20px; background-color: var(--accent-yellow); color: #111; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: all 0.3s;">
                            ⏱️ Adicionar 15 Minutos
                        </button>
                        <?php endif; ?>
                        <a href="../../meus_pedidos/HTML/Meus_pedidos.php"
                            style="flex: 1; min-width: 150px; padding: 12px 20px; background-color: var(--primary-red); color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            ← Voltar aos Pedidos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center" style="background-color: #eee;">
        <div class="container pt-4">
            <!-- Section: Redes sociais -->
            <section class="mb-4">
                <!-- Facebook -->
                <a data-mdb-ripple-init class="btn btn-link btn-floating btn-lg text-body m-1"
                    href="https://www.facebook.com/ShellBrasil?locale=pt_BR" role="button"
                    data-mdb-ripple-color="dark"><i class="fab fa-facebook-f"></i></a>

                <!-- Google -->
                <a data-mdb-ripple-init class="btn btn-link btn-floating btn-lg text-body m-1" href="#!" role="button"
                    data-mdb-ripple-color="dark"><i class="fa-solid fa-phone"></i></a>

                <!-- Instagram -->
                <a data-mdb-ripple-init class="btn btn-link btn-floating btn-lg text-body m-1"
                    href="https://www.instagram.com/shell.brasil/" role="button" data-mdb-ripple-color="dark"><i
                        class="fab fa-instagram"></i></a>
            </section>
        </div>

        <!-- Copyright -->
        <div class="text-center p-3" style="background-color: #FFD100;">
            © 2025 Copyright:
            <a class="text-body">FWS - Faster Way Service</a>
        </div>
        <!-- Copyright -->
    </footer>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
    </script>

    <!-- Script para gerenciar funcionalidades da página -->
    <script>
        // Funcionalidade do timer
        function parseTimeToMs(timestr) {
            // espera formato HH:MM:SS
            const parts = timestr.split(':').map(Number);
            if (parts.length !== 3) return 0;
            return ((parts[0] * 3600) + (parts[1] * 60) + parts[2]) * 1000;
        }

        function formatRemaining(ms) {
            if (ms <= 0) return '00:00:00';
            const totalSec = Math.floor(ms / 1000);
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        }

        // Atualizar timer se existir
        const timerDisplay = document.getElementById('timer-display'); <
        ? php
        if ($venda && in_array($venda['situacao_compra'], ['em_preparo', 'pronto_para_retirar']) && !empty($venda[
                'tempo_chegada'])): ? >
            const vendaCreatedAt = new Date('<?= $venda['
                data_criacao '] ?>'.replace(' ', 'T'));
        const tempoChegada = '<?= $venda['
        tempo_chegada '] ?>';
        const msLimit = parseTimeToMs(tempoChegada);
        const deadline = new Date(vendaCreatedAt.getTime() + msLimit);

        setInterval(function () {
            const now = Date.now();
            const restante = deadline.getTime() - now;
            if (timerDisplay) {
                timerDisplay.textContent = formatRemaining(restante);
            }
            if (restante <= 0) {
                location.reload();
            }
        }, 500); <
        ? php endif; ? >
    </script>
</body>

</html>