<?php
session_start();
include "../../conn.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    header("Location: /fws/FWS_Cliente/login/HTML/login.html");
    exit;
}

// Checagem de estoque AJAX
if (isset($_POST['checar_estoque']) && isset($_POST['itens']) && isset($_SESSION['usuario_id'])) {
    $itens = json_decode($_POST['itens'], true);
    if (is_array($itens)) {
        foreach ($itens as $item) {
            $produto_id = (int)$item['produto_id'];
            $quantidade = (int)$item['quantidade'];
            $q = $conn->query("SELECT nome, estoque FROM produtos WHERE id = $produto_id");
            if ($row = $q->fetch_assoc()) {
                $estoque = (int)$row['estoque'];
                $limite = ($estoque >= 10) ? 10 : max(1, floor($estoque / 2));
                if ($quantidade > $limite) {
                    echo json_encode(["ok"=>false, "falta_nome"=>$row['nome']]);
                    exit;
                }
            } else {
                echo json_encode(["ok"=>false, "falta_nome"=>"Produto desconhecido"]);
                exit;
            }
        }
        echo json_encode(["ok"=>true]);
        exit;
    }
}

// Lógica do botão "Pedir novamente"
if (isset($_POST['pedir_novamente']) && isset($_POST['itens']) && isset($_SESSION['usuario_id'])) {
    $usuario_id = (int)$_SESSION['usuario_id'];
    $itens = json_decode($_POST['itens'], true);
    if (is_array($itens)) {
        // Esvazia o carrinho do usuário antes de adicionar os novos itens
        $conn->query("DELETE FROM carrinho WHERE usuario_id = $usuario_id");
        foreach ($itens as $item) {
            $produto_id = (int)$item['produto_id'];
            $quantidade = (int)$item['quantidade'];
            if ($produto_id > 0 && $quantidade > 0) {
                // Busca preço atual do produto
                $q = $conn->query("SELECT preco_venda FROM produtos WHERE id = $produto_id");
                if ($row = $q->fetch_assoc()) {
                    $preco = $row['preco_venda'];
                    $conn->query("INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario) VALUES ($usuario_id, $produto_id, $quantidade, $preco)");
                }
            }
        }
        // Se for AJAX, responde JSON
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(["ok"=>true]);
            exit;
        }
        // Redireciona para o carrinho (fallback)
        header("Location: /fws/FWS_Cliente/carrinho/HTML/carrinho.php");
        exit;
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
    <link rel="stylesheet" href="../CSS/Meus_pedidos.css">
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

        /* Paleta do site */
        :root {
            --primary-red: #c40000;
            --accent-yellow: #FFD100;
            --accent-orange: #f37a27;
        }

        /* Estilos para os itens do pedido (restaurado para versão inicial)
           Mantemos apenas espaçamento simples; removemos regras flex/widths customizadas. */
        .pedido-box {
            padding: 0.5rem 0.25rem;
        }

        .pedido-item {
            font-size: 1.03rem;
        }

        .pedido-item>[class*="col-"] {
            padding: .5rem .75rem;
        }

        .pedido-item .col-2 {
            /* volta ao comportamento padrão do grid (quantidade pequena) */
            text-align: center;
        }

        .pedido-item .col-7 {
            /* ocupa o espaço restante via grid; sem regras flex customizadas */
        }

        .pedido-item .col-3 {
            text-align: right;
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

        /* Status style: bottom-right badge-like text */
        .order-status {
            margin-top: auto;
            align-self: flex-end;
            color: var(--primary-red);
            font-weight: 700;
            font-size: 0.95rem;
            background: rgba(196, 0, 0, 0.06);
            padding: .25rem .6rem;
            border-radius: 6px;
        }

        /* Responsividade: ajustes para mobile */
        @media (max-width: 767.98px) {
            .card-body {
                padding: 1rem !important;
            }

            /* Empilha os itens do pedido verticalmente */
            .pedido-item {
                flex-direction: column;
                align-items: flex-start;
                gap: .35rem;
            }

            .pedido-item>[class*="col-"] {
                padding: .35rem 0 !important;
            }

            .pedido-item .col-2,
            .pedido-item .col-3 {
                flex: none !important;
                max-width: none !important;
                width: auto !important;
            }

            /* Botões e selects ficam em bloco e ocupam toda a largura */
            .list-inline {
                flex-direction: column;
                gap: .5rem;
                padding-left: 0;
            }

            .list-inline-item.items-list {
                width: 100%;
            }

            .btn-accent,
            .no-underline.btn-accent,
            .btn-acoes {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
            }

            /* Ajuste do badge de status para não colidir com conteúdo */
            .order-status {
                align-self: flex-end;
                margin-top: .8rem;
            }

            /* Responsividade dos cards de pedidos */
            .card {
                margin-bottom: 1.5rem !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            }

            .card-body {
                padding: 1.1rem !important;
            }

            .d-flex.justify-content-between.align-items-center.mb-2 {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 6px !important;
            }

            .badge {
                font-size: 0.95rem !important;
                padding: 6px 12px !important;
                margin-bottom: 8px !important;
            }

            .mb-2 {
                margin-bottom: 0.7rem !important;
            }

            .row.align-items-center.mb-2 {
                flex-direction: row !important;
                gap: 0 !important;
            }

            .col-3.text-center img {
                max-width: 48px !important;
                max-height: 48px !important;
            }

            .col-5 {
                font-size: 0.95rem !important;
            }

            .col-4.text-end {
                font-size: 0.9rem !important;
            }

            .d-flex.justify-content-between.mt-3 {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 4px !important;
            }

            .btn-success.btn-sm,
            .btn.btn-accent {
                width: 100% !important;
                font-size: 1rem !important;
                padding: 10px 0 !important;
                margin-bottom: 8px !important;
            }

            /* Responsividade dos botões de ação */
            .d-flex.gap-2.justify-content-end.mt-2 {
                flex-direction: column !important;
                gap: 0.5rem !important;
            }
        }

        @media (max-width: 480px) {
            .card {
                border-radius: 10px !important;
                margin-bottom: 1.2rem !important;
            }

            .card-body {
                padding: 0.7rem !important;
            }

            .badge {
                font-size: 0.85rem !important;
                padding: 5px 10px !important;
            }

            .mb-2 {
                margin-bottom: 0.5rem !important;
            }

            .col-3.text-center img {
                max-width: 38px !important;
                max-height: 38px !important;
            }

            .col-5 {
                font-size: 0.9rem !important;
            }

            .col-4.text-end {
                font-size: 0.85rem !important;
            }

            .btn-success.btn-sm,
            .btn.btn-accent {
                font-size: 0.95rem !important;
                padding: 8px 0 !important;
                margin-bottom: 6px !important;
            }

            .d-flex.justify-content-between.align-items-center.mb-2 {
                gap: 4px !important;
            }

            .small {
                font-size: 0.75rem !important;
            }

            .fw-bold {
                font-size: 0.9rem !important;
            }

            /* Garantir que o container respeita a largura */
            .row {
                margin-right: -6px;
                margin-left: -6px;
            }

            .col-md-6 {
                padding-right: 6px;
                padding-left: 6px;
            }
        }
    </style>
</head>

<body>
    <!-- Cabeçalho -->
    <header id="header">
        <div class="logo">
            <a href="../../index.php">
                <img src="/fws/FWS_Cliente/index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <button class="menu-toggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav>
            <ul class="ul align-items-center">
                <li><a href="/fws/FWS_Cliente/produto/HTML/produto.php">Produtos</a></li>
                <li>
                    <form class="d-flex" role="search" action="/fws/FWS_Cliente/produto/HTML/produto.php"
                        method="get" style="margin: 0 10px;">
                        <input id="search" class="form-control form-control-sm me-2" type="search" name="q"
                            placeholder="Pesquisar..." aria-label="Pesquisar">
                        <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </li>
                <li><a href="Meus_pedidos.php">Meus pedidos</a></li>
                <li><a href="/fws/FWS_Cliente/tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
            </ul>
        </nav>

        <div class="carrinho">
            <a href="/fws/FWS_Cliente/carrinho/HTML/carrinho.php">
                <img src="/fws/FWS_Cliente/index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
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
                <a href="/fws/FWS_Cliente/info_usuario/HTML/info_usuario.php"
                    style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
                <a href="/fws/FWS_Cliente/logout.php" id="logout-link"
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

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

        <script>
        $(function() {
            var autocomplete = $("#search").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/fws/FWS_Cliente/produto/PHP/api-produtos.php',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    window.location.href =
                        'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search").data('autocomplete');

                if (autocomplete) {
                    autocomplete._renderItem = function (ul, item) {
                        return $("<li>")
                            .append("<div><img src='" + item.foto +
                                "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle;  background-color: #FFD100 !important;'/>" +
                                item.label + "</div>")
                            .appendTo(ul);
                    };
                }
            });
        </script>
    </header>

    <!-- Corpo principal -->
    <main>
        <section class="h-100 h-custom" style="background-color: #eee;">
            <div class="container py-5 h-100">
                <div class="row">
                    <?php
                    $usuario_id = isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 0;
                    if ($usuario_id > 0) {
                        // Buscar vendas do usuário
                        $sql_vendas = "SELECT * FROM vendas WHERE usuario_id = $usuario_id ORDER BY data_criacao DESC";
                        $res_vendas = mysqli_query($conn, $sql_vendas);
                        $vendas = [];
                        while ($venda = mysqli_fetch_assoc($res_vendas)) {
                            $vendas[] = $venda;
                        }

                        // Buscar telefone do usuário para código
                        $telefone = '';
                        $sql_tel = mysqli_query($conn, "SELECT telefone FROM usuarios WHERE id = $usuario_id LIMIT 1");
                        if ($row_tel = mysqli_fetch_assoc($sql_tel)) {
                            $telefone = preg_replace('/[^0-9]/', '', $row_tel['telefone']);
                        }

                        $col_count = 0;
                        
                        // Verificar se há pedidos
                        if (empty($vendas)) {
                            // Mensagem quando não tem pedidos
                            echo '<div class="col-12">';
                            echo '<div class="alert alert-info text-center" style="margin-top: 40px; padding: 60px 20px; background-color: #FFD100; border: none;">';
                            echo '<i class="bi bi-inbox" style="font-size: 4rem; color: #c40000; margin-bottom: 20px; display: block;"></i>';
                            echo '<h3 style="color: #c40000; margin-bottom: 15px; font-weight: bold;">Nenhum pedido encontrado</h3>';
                            echo '<p style="font-size: 1.1rem; color: #c40000; margin-bottom: 30px;">Você ainda não realizou nenhum pedido. Que tal começar agora?</p>';
                            echo '<a href="/fws/FWS_Cliente/produto/HTML/produto.php" class="btn btn-primary btn-lg" style="background-color: #c40000; border-color: #c40000; font-weight: bold; padding: 12px 40px;">';
                            echo '<i class="bi bi-shop" style="margin-right: 8px;"></i>Ir aos Produtos';
                            echo '</a>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            // Exibir pedidos normalmente
                            foreach ($vendas as $venda) {
                                // Gerar código: últimos 4 dígitos do telefone
                                $codigo = $telefone ? substr($telefone, -4) : '0000';

                                if ($col_count % 2 == 0) echo '<div class="row mb-4">';
                                echo '<div class="col-md-6">';
                                echo '<div class="card mb-4">';
                                echo '<div class="card-body">';
                                // Data e método de pagamento
                                echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                echo '<span class="small text-muted">'.date('d/m/Y H:i', strtotime($venda['data_criacao'])).'</span>';
                                echo '<span class="small">'.ucwords(str_replace('_',' ',$venda['metodo_pagamento'])).'</span>';
                            echo '</div>';
                            // Status com cor
                            $status = $venda['situacao_compra'];
                            $status_map = [
                                'em_preparo' => ['Em andamento', '#FFD100', 'black'],
                                'pronto_para_retirar' => ['Pronto para retirar', '#11C47E', 'white'],
                                'finalizada' => ['Finalizado', '#0a7c3a', 'white'],
                                'cancelada' => ['Cancelado', '#E53935', 'white']
                            ];
                            $status_label = isset($status_map[$status]) ? $status_map[$status][0] : ucfirst($status);
                            $status_bg = isset($status_map[$status]) ? $status_map[$status][1] : '#ccc';
                            $status_color = isset($status_map[$status]) ? $status_map[$status][2] : 'black';
                            echo '<span class="badge" style="background:'.$status_bg.';color:'.$status_color.';font-size:1rem;padding:7px 16px;margin-bottom:10px;">'.$status_label.'</span>';

                            // Tempo restante (só se em_preparo ou pronto_para_retirar)
                            if (in_array($status, ['em_preparo','pronto_para_retirar'])) {
                                // Tempo restante
                                $tempo_restante = '';
                                $id_timer = '';
                                if (!empty($venda['tempo_chegada']) && $status == 'em_preparo') {
                                    $data_criacao = strtotime($venda['data_criacao']);
                                    list($h, $m, $s) = explode(':', $venda['tempo_chegada']);
                                    $tempo_chegada = $h * 3600 + $m * 60 + $s;
                                    $deadline = $data_criacao + $tempo_chegada;
                                    $restante = $deadline - time();
                                    $tempo_restante = ($restante > 0) ? sprintf('%02d:%02d:%02d', floor($restante/3600), floor(($restante%3600)/60), $restante%60) : '00:00:00';
                                    $id_timer = 'timer_' . $venda['id'];
                                    echo '<div class="mb-2"><span class="small text-muted">Tempo restante:</span> <span class="fw-bold" id="'.$id_timer.'">'.$tempo_restante.'</span></div>';
                                    // Passa data_criacao e tempo_chegada para o JS
                                    echo '<script>window._timers = window._timers || []; window._timers.push({id:"'.$id_timer.'", data_criacao:"'.$venda['data_criacao'].'", tempo_chegada:"'.$venda['tempo_chegada'].'"});</script>';
                                } else {
                                    echo '<div class="mb-2"><span class="small text-muted">Tempo restante:</span> <span class="fw-bold">00:00:00</span></div>';
                                }
                            }
                            // Código (em todos os pedidos)
                            echo '<div class="mb-2"><span class="small text-muted">Código:</span> <span class="fw-bold">'.$codigo.'</span></div>';

                            // Produtos escolhidos
                            $venda_id = intval($venda['id']);
                            $sql_itens = "SELECT iv.*, p.nome, p.foto_produto FROM itens_vendidos iv INNER JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = $venda_id";
                            $res_itens = mysqli_query($conn, $sql_itens);
                            while ($item = mysqli_fetch_assoc($res_itens)) {
                                echo '<div class="row align-items-center mb-2">';
                                echo '<div class="col-3 text-center">';
                                echo '<img src="'.htmlspecialchars($item['foto_produto']).'" class="img-fluid rounded" style="max-height:60px;">';
                                echo '</div>';
                                echo '<div class="col-5">';
                                echo '<div style="font-size:1rem;font-weight:500;">'.htmlspecialchars($item['nome']).'</div>';
                                echo '<div class="text-muted">Qtd: '.$item['quantidade'].'</div>';
                                echo '</div>';
                                echo '<div class="col-4 text-end">';
                                echo '<div style="font-size:0.85rem; color:#666;">Unit: R$ '.number_format($item['preco_unitario'],2,',','.').'</div>';
                                echo '<div style="font-size:1rem;">Total: <b>R$ '.number_format($item['preco_unitario'] * $item['quantidade'],2,',','.').'</b></div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            // Preço total
                            echo '<div class="d-flex justify-content-between mt-3">';
                            echo '<span class="fw-bold">Total</span>';
                            echo '<span class="fw-bold">R$ '.number_format($venda['total'],2,',','.').'</span>';
                            echo '</div>';
                            // Botão Pedir Novamente (apenas para finalizada ou cancelada)
                            if (in_array($status, ['finalizada', 'cancelada'])) {
                                $sql_itens = "SELECT produto_id, quantidade FROM itens_vendidos WHERE venda_id = $venda_id";
                                $res_itens = mysqli_query($conn, $sql_itens);
                                $produtos = [];
                                while ($it = mysqli_fetch_assoc($res_itens)) {
                                    $produtos[] = [
                                        'produto_id' => $it['produto_id'],
                                        'quantidade' => $it['quantidade']
                                    ];
                                }
                                $produtos_json = htmlspecialchars(json_encode($produtos), ENT_QUOTES, 'UTF-8');
                                echo '<div class="row mt-2">';
                                echo '<div class="col text-end">';
                                echo '<div class="d-flex gap-2 justify-content-end mt-2">';
                                echo '<form method="post" class="form-pedir-novamente" style="margin:0;">';
                                echo '<input type="hidden" name="itens" value=\''.$produtos_json.'\' />';
                                echo '<button type="submit" name="pedir_novamente" class="btn btn-success btn-sm" style="min-width:140px; font-size:0.93rem; font-weight:bold; border-radius:6px; padding:6px 0;">Pedir novamente</button>';
                                echo '</form>';
                                echo '<a href="../../pedido_detalhado/HTML/pedido_detalhado.php?id=' . $venda['id'] . '" class="btn btn-accent no-underline" style="background-color: var(--accent-yellow); color: #111; font-weight: bold; border-radius: 6px; min-width:120px; font-size:0.93rem; padding:6px 0; border: none; display: inline-block; transition: all 0.3s;">Ver detalhes</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                            $col_count++;
                            if ($col_count % 2 == 0) echo '</div>';
                            }
                        }
                        if ($col_count % 2 != 0) echo '</div>';
                    } else {
                        echo '<div class="alert alert-warning">Faça login para ver seus pedidos.</div>';
                    }
                    ?>

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

    <script>
        // Contagem regressiva dos timers dos pedidos (igual ao modal do carrinho)
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

        window._timers = window._timers || [];
        let shouldReload = false;
        if (window._timers.length) {
            setInterval(function () {
                var now = Date.now();
                window._timers.forEach(function (t) {
                    // Calcula deadline usando data_criacao + tempo_chegada
                    var created = t.data_criacao ? new Date(t.data_criacao.replace(' ', 'T')) :
                        new Date();
                    var msLimit = parseTimeToMs(t.tempo_chegada || '00:30:00');
                    var deadline = new Date(created.getTime() + msLimit);
                    var restante = deadline.getTime() - now;
                    var el = document.getElementById(t.id);
                    if (el) {
                        el.textContent = formatRemaining(restante);
                        if (restante <= 0) shouldReload = true;
                    }
                });
            }, 500);
        }
        // Atualiza a página a cada 30 segundos ou se algum timer zerar
        setInterval(function () {
            if (shouldReload) {
                location.reload();
            }
        }, 30000);
    </script>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous">
    </script>

    <div id="modal-backdrop" class="custom-backdrop" style="display:none"></div>
    <div id="modal-estoque-falta" class="custom-modal" style="display:none"></div>
    <style>
        .custom-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 2000;
        }

        .custom-modal {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            min-width: 340px;
            max-width: 90vw;
            background: #fff;
            border-radius: 16px;
            padding: 28px 32px 22px 32px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            z-index: 2100;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: inherit;
        }

        .custom-modal .modal-actions {
            margin-top: 20px;
            display: flex;
            gap: 14px;
            width: 100%;
            justify-content: center;
        }

        .custom-modal .btn-popup {
            border: none;
            padding: 7px 20px;
            border-radius: 7px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            background: #c40000;
            color: #fff;
        }
    </style>
    <script>
        $(function () {
            $('.form-pedir-novamente').on('submit', function (e) {
                e.preventDefault();
                var form = $(this);
                var itens = JSON.parse(form.find('input[name="itens"]').val());
                // Checar estoque via AJAX
                $.post('', {
                    checar_estoque: 1,
                    itens: JSON.stringify(itens)
                }, function (resp) {
                    try {
                        var data = typeof resp === 'string' ? JSON.parse(resp) : resp;
                        if (data.ok) {
                            // Agora faz o pedido via AJAX também
                            $.ajax({
                                url: '',
                                type: 'POST',
                                data: {
                                    pedir_novamente: 1,
                                    itens: JSON.stringify(itens)
                                },
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                success: function (resp2) {
                                    try {
                                        var data2 = typeof resp2 === 'string' ? JSON
                                            .parse(resp2) : resp2;
                                        if (data2 && data2.ok) {
                                            window.location.href =
                                                '/Fws/FWS_Cliente/carrinho/HTML/carrinho.php';
                                        } else {
                                            mostrarToastSucesso('Pedido refeito!');
                                            setTimeout(function () {
                                                window.location.reload();
                                            }, 1200);
                                        }
                                    } catch (e) {
                                        window.location.href =
                                            '/fws/FWS_Cliente/carrinho/HTML/carrinho.php';
                                    } else {
                                        mostrarToastSucesso('Pedido refeito!');
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 1200);
                                    }
                                },
                                error: function () {
                                    window.location.href =
                                        '/fws/FWS_Cliente/carrinho/HTML/carrinho.php';
                                }
                            },
                            error: function() {
                                window.location.href =
                                    '/fws/FWS_Cliente/carrinho/HTML/carrinho.php';
                            }
                        });
                    } else if (data.falta_nome) {
                        mostrarModalEstoqueFalta(data.falta_nome);
                    }
                });
            });

            function mostrarToastSucesso(msg) {
                var toast = $('<div></div>').css({
                    position: 'fixed',
                    top: '50%',
                    left: '50%',
                    transform: 'translate(-50%,-50%)',
                    background: '#11C47E',
                    color: '#fff',
                    padding: '18px 32px',
                    borderRadius: '12px',
                    fontWeight: '700',
                    zIndex: 9999,
                    boxShadow: '0 8px 25px rgba(0,0,0,0.4)',
                    fontSize: '1.1rem',
                    textAlign: 'center',
                    minWidth: '220px'
                }).text(msg).appendTo('body');
                setTimeout(function () {
                    toast.fadeOut(300, function () {
                        toast.remove();
                    });
                }, 1200);
            }

            function mostrarModalEstoqueFalta(nomeProd) {
                $("#modal-estoque-falta").html(`
              <div style="color:#b30000;font-weight:700;font-size:1.15rem;margin-bottom:14px;text-align:center">
                  Produto em falta
              </div>
              <p style="text-align:center;margin-bottom:10px">
                  O produto <b>${nomeProd}</b> está em falta ou não possui estoque suficiente.<br>Não foi possível adicionar ao carrinho.
              </p>
              <div class="modal-actions">
                  <button class="btn-popup ok-close">Fechar</button>
              </div>
            `).show();
                $("#modal-backdrop").show();
                $(".ok-close").on("click", function () {
                    $("#modal-estoque-falta, #modal-backdrop").hide();
                });
            }
        });
    </script>
</body>

</html>