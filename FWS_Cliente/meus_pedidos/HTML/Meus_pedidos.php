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

// Lógica para adicionar 15 minutos ao tempo limite
if (isset($_POST['adicionar_tempo']) && isset($_POST['venda_id']) && isset($_SESSION['usuario_id'])) {
    $venda_id = (int)$_POST['venda_id'];
    $usuario_id = (int)$_SESSION['usuario_id'];
    
    // Verificar se o pedido pertence ao usuário e se já foi adicionado tempo
    $check = $conn->query("SELECT tempo_chegada, tempo_adicionado FROM vendas WHERE id = $venda_id AND usuario_id = $usuario_id");
    if ($check && $row = $check->fetch_assoc()) {
        // Verificar se já foi adicionado tempo (se for 's', não permite adicionar novamente)
        if (strtolower(trim($row['tempo_adicionado'])) === 's') {
            echo json_encode(["ok" => false, "erro" => "Tempo já foi adicionado para este pedido"]);
            exit;
        }
        
        $tempo_atual = $row['tempo_chegada'];
        // Converter HH:MM:SS para segundos
        list($h, $m, $s) = explode(':', $tempo_atual);
        $segundos = $h * 3600 + $m * 60 + $s;
        // Adicionar 15 minutos (900 segundos)
        $segundos += 900;
        // Converter de volta para HH:MM:SS
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundos_restantes = $segundos % 60;
        $novo_tempo = sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos_restantes);
        
        // Atualizar no banco (tempo_chegada e marcar que tempo foi adicionado)
        $update = $conn->query("UPDATE vendas SET tempo_chegada = '$novo_tempo', tempo_adicionado = 's' WHERE id = $venda_id");
        if ($update) {
            echo json_encode(["ok" => true, "novo_tempo" => $novo_tempo]);
        } else {
            echo json_encode(["ok" => false, "erro" => "Erro ao atualizar"]);
        }
    } else {
        echo json_encode(["ok" => false, "erro" => "Pedido não encontrado"]);
    }
    exit;
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../CSS/Meus_pedidos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-papm6QpQKQwQvQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQ=="
        crossorigin="anonymous" />

    <!-- LINKS PARA FUNCIONAR A PESQUISA INSTANTANEA -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- JQuery UI css -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <style>
        /* ========== CSS DO HEADER ========== */
        @import url('../../Fonte_Config/fonte_geral.css');

        html,
        body,
        * {
            font-family: 'Ubuntu', sans-serif !important;
        }

        html {
            font-family: 'Ubuntu', sans-serif !important;
        }

        body {
            font-family: 'Ubuntu', sans-serif !important;
            background-color: white;
        }

        p,
        div,
        span,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        a,
        button,
        input,
        select,
        textarea,
        label {
            font-family: 'Ubuntu', sans-serif !important;
        }

        /* Estilos da HEADER */
        header {
            width: 100%;
        }

        nav.navbar {
            width: 100%;
            min-width: 100%;
            margin: 0;
            padding: 0;
            border-radius: 0;
        }

        .container-fluid {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Logo */
        .logo-shell {
            width: 150px;
            height: auto;
        }

        /* Menu Bold */
        .menu-bold {
            font-weight: 700 !important;
        }

        /* Icone de Pesquisa */
        .bi-search {
            fill: #000000 !important;
        }

        /* Ícone do Menu Hambúrguer */
        .navbar-toggler-icon {
            background-image: none !important;
            width: 1.7em;
            height: 1.7em;
            display: inline-block;
            position: relative;
        }

        .navbar-toggler-icon,
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after {
            box-sizing: border-box;
        }

        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after,
        .navbar-toggler-icon span {
            content: '';
            display: block;
            height: 3.5px;
            width: 100%;
            background: #FFD100;
            margin: 5px 0;
            border-radius: 2px;
        }

        .navbar-toggler-icon span {
            margin: 0;
        }

        /* Media Query - Mobile (até 576px) */
        @media (max-width: 576px) {
            .navbar-toggler {
                position: absolute;
                right: -55px;
                top: 0px;
                margin-right: 0 !important;
                margin-left: 0 !important;
                z-index: 1050;
                background: #c40000 !important;
                border-color: #c40000 !important;
                box-shadow: none !important;
            }

            .navbar .d-flex.align-items-center.ms-3 {
                position: relative;
                justify-content: flex-start;
                width: auto;
                gap: 0.5rem;
                position: absolute;
                right: 60px;
                top: 15px;
                z-index: 1051;
            }

            .navbar .d-flex.align-items-center.ms-3 .d-flex.align-items-center.d-sm-none a.me-2:first-child {
                margin-left: 0px !important;
                transform: translateX(-6px);
            }

            .navbar .d-flex.align-items-center.ms-3 .me-2 {
                display: flex;
            }

            .navbar .d-flex.align-items-center.ms-3 h5 {
                display: none !important;
            }

            .navbar .d-flex.align-items-center.ms-3 a:last-child {
                display: none !important;
            }

            .container-fluid .d-flex.align-items-center.ms-auto.me-4 {
                display: none !important;
            }

            .navbar-collapse .search-area-mobile {
                display: flex !important;
                width: 100%;
                margin-bottom: 15px;
            }

            .navbar-collapse .search-area-mobile input {
                width: 100% !important;
            }

            /* Centraliza os títulos do menu no mobile */
            .navbar-nav {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
                flex-direction: column !important;
                gap: 1rem !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .navbar-nav .menu-bold {
                text-align: center !important;
                width: 100% !important;
            }
        }

        /* Media Query - Desktop (577px ou maior) */
        @media (min-width: 577px) {
            .search-area {
                margin-right: -50px !important;
            }

            .d-flex.align-items-center.ms-auto.me-4 h5 {
                font-size: 14px !important;
                margin-bottom: 0px !important;
                font-family: 'Ubuntu', sans-serif !important;
                font-weight: bold !important;
                margin-left: 0px !important;
                white-space: nowrap !important;
                margin-top: -2px !important;
            }
        }

        /* Aumenta 30% o tamanho dos títulos do menu */
        .navbar-nav .menu-bold {
            font-size: 23.1px !important;
        }

        /* ========== FIM DO CSS DO HEADER ========== */

        /* ========== CSS DO AUTOCOMPLETE ========== */
        .ui-autocomplete {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            padding: 0 !important;
            max-height: 400px;
            overflow-y: auto;
            z-index: 9999 !important;
        }

        @media (max-width: 576px) {
            #search-mobile.ui-autocomplete-input {
                width: 100% !important;
            }

            .ui-autocomplete {
                min-width: 90vw !important;
                left: calc(5vw - 5px) !important;
            }
        }

        .ui-menu .ui-menu-item {
            padding: 0 !important;
            border-bottom: 1px solid #eee;
        }

        .ui-menu .ui-menu-item:last-child {
            border-bottom: none;
        }

        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover,
        .autocomplete-item:hover {
            background-color: #FFD100 !important;
            background-image: none !important;
            color: #000 !important;
            cursor: pointer;
            border-radius: 0 !important;
        }

        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover {
            box-shadow: none !important;
        }

        .autocomplete-item {
            list-style: none;
        }

        /* ========== CSS DO RESTO DA PÁGINA ========== */
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

        /* ========== RESPONSIVIDADE DO CARD DE PEDIDOS ========== */
        
        /* Desktop (1200px+) */
        @media (min-width: 1200px) {
            .col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
                padding: 0.75rem;
            }

            .card {
                margin-bottom: 1.5rem !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
                overflow: hidden;
            }

            .card-header-pedido {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 2rem !important;
                flex-wrap: wrap;
            }

            .card-content {
                padding: 1.5rem !important;
            }

            .row.align-items-center.mb-2 {
                display: flex;
                gap: 1rem;
            }

            .col-3.text-center img {
                max-width: 70px !important;
                max-height: 70px !important;
            }

            .col-5 {
                font-size: 1rem !important;
                flex: 1;
            }

            .col-4.text-end {
                text-align: right !important;
                font-size: 0.95rem !important;
            }

            .d-flex.gap-2.justify-content-end.mt-2 {
                flex-direction: row !important;
                gap: 0.75rem !important;
                justify-content: flex-end;
            }

            .btn-accent,
            .btn-info,
            .btn-success {
                min-width: 140px;
                padding: 8px 16px !important;
                font-size: 0.95rem !important;
            }
        }

        /* Tablet/Médio (768px - 1199px) */
        @media (min-width: 768px) and (max-width: 1199px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0.5rem;
            }

            .card {
                margin-bottom: 1.2rem !important;
                border-radius: 10px !important;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
            }

            .card-header-pedido {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
                align-items: flex-start !important;
            }

            .card-header-pedido > div:last-child {
                display: flex !important;
                width: 100% !important;
                gap: 1rem !important;
                justify-content: space-between !important;
                flex-wrap: wrap;
            }

            .card-content {
                padding: 1.2rem !important;
            }

            .row.align-items-center.mb-2 {
                display: flex;
                gap: 0.75rem;
            }

            .col-3.text-center img {
                max-width: 60px !important;
                max-height: 60px !important;
                flex: 0 0 auto;
            }

            .col-5 {
                font-size: 0.95rem !important;
                flex: 1;
            }

            .col-4.text-end {
                text-align: right !important;
                font-size: 0.9rem !important;
            }

            .d-flex.gap-2.justify-content-end.mt-2 {
                flex-direction: row !important;
                gap: 0.5rem !important;
                width: 100%;
                flex-wrap: wrap;
            }

            .btn-accent,
            .btn-info,
            .btn-success {
                flex: 1;
                min-width: 120px;
                padding: 8px 12px !important;
                font-size: 0.9rem !important;
            }
        }

        /* Mobile Grande (577px - 767px) */
        @media (min-width: 577px) and (max-width: 767px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0.5rem;
            }

            .card {
                margin-bottom: 1rem !important;
                border-radius: 10px !important;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08) !important;
            }

            .card-header-pedido {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.8rem !important;
                align-items: flex-start !important;
            }

            .card-header-pedido > div:last-child {
                display: flex !important;
                width: 100% !important;
                gap: 0.8rem !important;
                justify-content: space-between !important;
                flex-wrap: wrap;
            }

            .card-content {
                padding: 1rem !important;
            }

            .row.align-items-center.mb-2 {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 0.75rem;
            }

            .col-3.text-center img {
                max-width: 50px !important;
                max-height: 50px !important;
                flex: 0 0 auto;
            }

            .col-5 {
                font-size: 0.9rem !important;
                flex: 1;
            }

            .col-4.text-end {
                text-align: right !important;
                font-size: 0.85rem !important;
            }

            .d-flex.gap-2.justify-content-end.mt-2 {
                flex-direction: row !important;
                gap: 0.4rem !important;
                width: 100%;
                flex-wrap: wrap;
            }

            .btn-accent,
            .btn-info,
            .btn-success {
                flex: 1;
                min-width: 100px;
                padding: 6px 8px !important;
                font-size: 0.85rem !important;
            }

            .d-flex.justify-content-between.mt-3 {
                gap: 0.5rem !important;
                flex-direction: column;
            }
        }

        /* Mobile Pequeno (até 576px) */
        @media (max-width: 576px) {
            .container {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0.25rem;
            }

            .card {
                margin-bottom: 0.8rem !important;
                border-radius: 8px !important;
                box-shadow: 0 1px 8px rgba(0, 0, 0, 0.08) !important;
                border: 2px solid var(--primary-red) !important;
            }

            .card-header-pedido {
                display: block !important;
                padding: 1rem !important;
                background: linear-gradient(135deg, var(--primary-red) 0%, #a00000 100%) !important;
            }

            .card-header-pedido h2 {
                font-size: 1.1rem !important;
                margin: 0 0 0.5rem 0 !important;
            }

            .card-header-pedido p {
                font-size: 0.8rem !important;
                margin: 0 !important;
            }

            .card-header-pedido > div:last-child {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.5rem !important;
                margin-top: 0.8rem !important;
                width: 100%;
            }

            .card-header-pedido > div:last-child > div {
                width: 100% !important;
            }

            .card-content {
                padding: 0.8rem !important;
            }

            .row.align-items-center.mb-2 {
                display: flex;
                gap: 0.4rem;
                margin-bottom: 0.6rem;
            }

            .col-3.text-center img {
                max-width: 45px !important;
                max-height: 45px !important;
                flex: 0 0 auto;
            }

            .col-5 {
                font-size: 0.8rem !important;
                flex: 1;
            }

            .col-5 > div:first-child {
                font-weight: 500 !important;
                margin-bottom: 0.2rem;
            }

            .col-4.text-end {
                text-align: right !important;
                font-size: 0.8rem !important;
            }

            .col-4.text-end > div:first-child {
                color: #666 !important;
                font-size: 0.7rem !important;
            }

            .d-flex.justify-content-between.mt-3 {
                font-size: 0.95rem !important;
                flex-direction: column;
                gap: 0.3rem;
            }

            .d-flex.gap-2.justify-content-end.mt-2 {
                flex-direction: column !important;
                gap: 0.3rem !important;
                width: 100%;
            }

            .btn-accent,
            .btn-info,
            .btn-success {
                width: 100% !important;
                padding: 6px 10px !important;
                font-size: 0.8rem !important;
                margin-bottom: 0.3rem !important;
            }

            .small {
                font-size: 0.7rem !important;
            }

            .fw-bold {
                font-weight: 600 !important;
                font-size: 0.85rem !important;
            }

            .mb-2 {
                margin-bottom: 0.4rem !important;
            }

            .badge {
                font-size: 0.8rem !important;
                padding: 4px 8px !important;
            }
        }

        /* Remove espaço abaixo do footer no mobile */
        @media (max-width: 767.98px) {
            html, body {
                height: auto !important;
                min-height: auto !important;
            }
            
            .h-100, .h-custom {
                height: auto !important;
                min-height: auto !important;
            }
            
            footer {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
        }
    </style>
</head>

<body>
    <!-- ========== INÍCIO DO HEADER ========== -->
    <header>
        <!-- ========== NAVBAR PRINCIPAL ========== -->
        <nav class="navbar navbar-expand-sm navbar-light" style="background-color: #c40000;">
            <!-- ========== LOGO ========== -->
            <a class="navbar-brand ms-3" href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="Logo" class="logo-shell">
            </a>

            <!-- ========== SEÇÃO MOBILE (BOTÃO TOGGLE + CARRINHO + PERFIL) ========== -->
            <div class="d-flex align-items-center ms-3">
                <!-- Botão do menu hambúrguer -->
                <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><span></span></span>
                </button>

                <!-- Ícones de Carrinho e Perfil (apenas mobile) -->
                <div class="d-flex align-items-center d-sm-none">
                    <!-- Carrinho Mobile -->
                    <a href="../../carrinho/HTML/carrinho.php" class="me-2" style="margin-left: 2px;">
                        <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                            style="object-fit: contain; filter: brightness(0) invert(1);">
                    </a>

                    <!-- Perfil Mobile (com validação de login) -->
                    <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>"
                        class="me-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white"
                            class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                    </a>

                    <!-- Texto "Bem-vindo(a)" Mobile -->
                    <h5 class="text-white me-2 m-0">Bem-vindo(a)</h5>
                </div>

                <!-- ========== BARRA DE PESQUISA (Desktop) ========== -->
                <div class="search-area d-none d-sm-flex align-items-center ms-auto">
                    <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get"
                        style="margin: 0;">
                        <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
                            style="width: 300px;">
                        <button class="btn btn-outline-light" type="submit"
                            style="background-color: #FFD100; border-color: #FFD100;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-search"
                                viewBox="0 0 16 16">
                                <path
                                    d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ========== MENU TOGGLE (DESKTOP) ========== -->
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="collapsibleNavId">
                    <!-- Itens de Menu Centralizados: Home, Produtos, Meus Pedidos, Sobre Nós -->
                    <ul class="navbar-nav d-flex align-items-center gap-4 justify-content-center w-100"
                        style="margin-right: 40px;">
                        <li class="nav-item">
                            <a class="nav-link" href="../../index.php">
                                <h5 class="m-0 text-white menu-bold">Home</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../produto/HTML/produto.php">
                                <h5 class="m-0 text-white menu-bold">Produtos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../meus_pedidos/HTML/Meus_pedidos.php">
                                <h5 class="m-0 text-white menu-bold">Meus Pedidos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../tela_sobre_nos/HTML/sobre_nos.php">
                                <h5 class="m-0 text-white menu-bold">Sobre Nós</h5>
                            </a>
                        </li>
                    </ul>

                    <!-- ========== SEÇÃO DESKTOP (CARRINHO + BEM-VINDO + PERFIL) ========== -->
                    <div class="d-flex align-items-center ms-auto me-4">
                        <!-- Carrinho Desktop -->
                        <a href="../../carrinho/HTML/carrinho.php" style="margin-left: -70px;">
                            <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                                class="me-4" style="object-fit: contain; filter: brightness(0) invert(1);">
                        </a>

                        <!-- Texto "Bem-vindo(a)" Desktop (com nome se logado) -->
                        <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
                        <?php
                                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                                $primeiroNome = explode(' ', $nomeCompleto)[0];
                            ?>
                        <h5 class="text-white me-2" style="margin-top: 10px;">Bem-vindo(a), <?= $primeiroNome ?></h5>
                        <?php else: ?>
                        <h5 class="text-white me-2" style="margin-top: 10px;">Bem-vindo(a)</h5>
                        <?php endif; ?>

                        <!-- Perfil Desktop (com validação de login) -->
                        <a
                            href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white"
                                class="bi bi-person-circle" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                <path fill-rule="evenodd"
                                    d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ========== BARRA DE PESQUISA MOBILE ========== -->
        <div class="search-mobile-container d-sm-none px-3 py-2" style="background-color: #c40000;">
            <!-- Texto "Bem-vindo(a)" Mobile com nome se logado -->
            <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
            <?php
                    $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                    $primeiroNome = explode(' ', $nomeCompleto)[0];
                ?>
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a), <?= $primeiroNome ?></h5>
            <?php else: ?>
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a)</h5>
            <?php endif; ?>

            <!-- Formulário de Pesquisa Mobile -->
            <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get">
                <input id="search-mobile" class="form-control me-2" type="search" name="q" placeholder="Pesquisar...">
                <button class="btn btn-outline-light" type="submit"
                    style="background-color: #FFD100; border-color: #FFD100;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" class="bi bi-search"
                        viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </button>
            </form>
        </div>
    </header>
    <!-- ========== FIM DO HEADER ========== -->

    <script>
        $(function () {
            var autocomplete = $("#search").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '../../produto/PHP/api-produtos.php',
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
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search").data('autocomplete');

            if (autocomplete) {
                autocomplete._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append(
                            "<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" +
                            item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label +
                            "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>"
                        )
                        .appendTo(ul);
                };
            }

            // Autocomplete para mobile
            var autocompleteMobile = $("#search-mobile").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '../../produto/PHP/api-produtos.php',
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
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search-mobile").data('autocomplete');

            if (autocompleteMobile) {
                autocompleteMobile._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append(
                            "<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" +
                            item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label +
                            "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>"
                        )
                        .appendTo(ul);
                };
            }
        });
    </script>

    <!-- Corpo principal -->
    <main>
        <section class="h-100 h-custom" style="background-color: #eee;">
            <div class="container py-5 h-100">
                <div class="row justify-content-center">
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
                                $codigo_tel = $telefone ? substr($telefone, -4) : '0000';
                                $codigo_ped = substr(strval($venda['id']), -2);
                                $codigo = $codigo_tel . '-' . $codigo_ped;

                                if ($col_count % 2 == 0) echo '<div class="row mb-4">';
                                echo '<div class="col-md-6">';
                                echo '<div class="card mb-4" style="border: 3px solid var(--primary-red); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden;">';
                                // Cabeçalho do Card
                                echo '<div class="card-header-pedido" style="background: linear-gradient(135deg, var(--primary-red) 0%, #a00000 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;">';
                                echo '<div>';
                                echo '<h2 style="margin: 0; font-size: 1.3rem; font-weight: bold;">Pedido #'.htmlspecialchars($venda['id']).'</h2>';
                                echo '<p style="margin: 5px 0 0 0; font-size: 0.95rem; opacity: 0.95;">'.date('d/m/Y H:i', strtotime($venda['data_criacao'])).'</p>';
                                echo '</div>';
                                echo '<div style="display: flex; gap: 20px; align-items: center;">';
                                echo '<div style="background: rgba(255, 209, 0, 0.25); padding: 10px 16px; border-radius: 8px; text-align: center;">';
                                echo '<p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Código</p>';
                                echo '<p style="margin: 5px 0 0 0; font-size: 1.1rem; font-weight: bold; color: #FFD100; letter-spacing: 2px;">'.$codigo.'</p>';
                                echo '</div>';
                                echo '<div style="background: rgba(255,255,255,0.8); padding: 10px 18px; border-radius: 8px; font-weight: bold; text-align: center; white-space: nowrap;">';
                                echo '<p style="margin: 0; font-size: 0.85rem; opacity: 0.9; color: var(--primary-red);">Status</p>';
                                $status = $venda['situacao_compra'];
                                $status_map = [
                                    'em_preparo' => ['Em preparação', '#FFD100', 'black'],
                                    'pronto_para_retirar' => ['Pronto para retirar', '#11C47E', 'white'],
                                    'finalizada' => ['Finalizado', '#0a7c3a', 'white'],
                                    'cancelada' => ['Cancelado', '#E53935', 'white']
                                ];
                                $status_label = isset($status_map[$status]) ? $status_map[$status][0] : ucfirst($status);
                                $status_bg = isset($status_map[$status]) ? $status_map[$status][1] : '#ccc';
                                $status_color = isset($status_map[$status]) ? $status_map[$status][2] : 'black';
                                echo '<p style="margin: 5px 0 0 0; font-size: 1.1rem; color:'.$status_bg.'; background:'.$status_bg.'; color:'.$status_color.'; border-radius:6px; padding:4px 12px; display:inline-block;">'.$status_label.'</p>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                                // Conteúdo do Card
                                echo '<div class="card-content" style="padding: 20px; background: #f9f9f9;">';
                                // Tempo restante (só se em_preparo ou pronto_para_retirar)
                                if (in_array($status, ['em_preparo','pronto_para_retirar'])) {
                                    $tempo_restante = '';
                                    $id_timer = '';
                                    if (!empty($venda['tempo_chegada'])) {
                                        $data_criacao = strtotime($venda['data_criacao']);
                                        list($h, $m, $s) = explode(':', $venda['tempo_chegada']);
                                        $tempo_chegada = $h * 3600 + $m * 60 + $s;
                                        $deadline = $data_criacao + $tempo_chegada;
                                        $restante = $deadline - time();
                                        $tempo_restante = ($restante > 0) ? sprintf('%02d:%02d:%02d', floor($restante/3600), floor(($restante%3600)/60), $restante%60) : '00:00:00';
                                        $id_timer = 'timer_' . $venda['id'];
                                        echo '<div class="mb-2"><span class="small text-muted">Tempo restante:</span> <span class="fw-bold" id="'.$id_timer.'">'.$tempo_restante.'</span></div>';
                                        echo '<script>window._timers = window._timers || []; window._timers.push({id:"'.$id_timer.'", data_criacao:"'.$venda['data_criacao'].'", tempo_chegada:"'.$venda['tempo_chegada'].'"});</script>';
                                    } else {
                                        echo '<div class="mb-2"><span class="small text-muted">Tempo restante:</span> <span class="fw-bold">00:00:00</span></div>';
                                    }
                                }
                                // Produtos escolhidos
                                $venda_id = intval($venda['id']);
                                $sql_itens = "SELECT iv.*, p.nome, p.foto_produto FROM itens_vendidos iv INNER JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = $venda_id";
                                $res_itens = mysqli_query($conn, $sql_itens);
                                echo '<div style="margin-bottom: 18px;">';
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
                                echo '</div>';
                                // Preço total
                                echo '<div class="d-flex justify-content-between mt-3" style="font-size:1.1rem; font-weight:bold; color:var(--primary-red);">';
                                echo '<span>Total</span>';
                                echo '<span>R$ '.number_format($venda['total'],2,',','.').'</span>';
                                echo '</div>';
                            // Botões de ação
                            echo '<div class="row mt-3">';
                            echo '<div class="col text-end">';
                            echo '<div class="d-flex gap-2 justify-content-end">';
                            
                            // Botão Ver Detalhes (sempre visível para pedidos em preparação ou pronto para retirar)
                            if (in_array($status, ['em_preparo', 'pronto_para_retirar'])) {
                                echo '<a href="../../pedido_detalhado/HTML/pedido_detalhado.php?id=' . $venda['id'] . '" class="btn btn-accent no-underline" style="background-color: var(--accent-yellow); color: #111; font-weight: bold; border-radius: 6px; min-width:130px; font-size:0.93rem; padding:6px 0; border: none; display: inline-block;">Ver detalhes</a>';
                                // Botão Adicionar 15 min (apenas se ainda não foi adicionado)
                                // Verifica se tempo_adicionado NÃO é 's' (case-insensitive, sem espaços)
                                if (strtolower(trim($venda['tempo_adicionado'])) !== 's') {
                                    echo '<button class="btn btn-info btn-sm adicionar-tempo" data-venda-id="' . $venda['id'] . '" style="min-width:140px; font-size:0.93rem; font-weight:bold; border-radius:6px; padding:6px 0; color:white; display:inline-block;">Adicionar 15 min</button>';
                                }
                            }
                            
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
                                echo '<form method="post" class="form-pedir-novamente" style="margin:0;">';
                                echo '<input type="hidden" name="itens" value=\''.$produtos_json.'\' />';
                                echo '<button type="submit" name="pedir_novamente" class="btn btn-success btn-sm" style="min-width:140px; font-size:0.93rem; font-weight:bold; border-radius:6px; padding:6px 0;">Pedir novamente</button>';
                                echo '</form>';
                                echo '<a href="../../pedido_detalhado/HTML/pedido_detalhado.php?id=' . $venda['id'] . '" class="btn btn-accent no-underline" style="background-color: var(--accent-yellow); color: #111; font-weight: bold; border-radius: 6px; min-width:120px; font-size:0.93rem; padding:6px 0; border: none; display: inline-block;">Ver detalhes</a>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
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

    <footer class="text-center bg-body-tertiary">
    <div class="container pt-4">
      <!-- Section: Redes sociais -->
      <section class="mb-4">
        <!-- Facebook -->
        <a data-mdb-ripple-init class="btn btn-link btn-floating btn-lg text-body m-1"
          href="https://www.facebook.com/ShellBrasil?locale=pt_BR" role="button" data-mdb-ripple-color="dark"><i
            class="fab fa-facebook-f"></i></a>

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
        setInterval(function() {
            var now = Date.now();
            window._timers.forEach(function(t) {
                // Calcula deadline usando data_criacao + tempo_chegada
                var created = t.data_criacao ? new Date(t.data_criacao.replace(' ', 'T')) : new Date();
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
    setInterval(function() {
        if (shouldReload) {
            location.reload();
        }
    }, 30000);
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
    $(function() {
        $('.form-pedir-novamente').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var itens = JSON.parse(form.find('input[name="itens"]').val());
            // Checar estoque via AJAX
            $.post('', {
                checar_estoque: 1,
                itens: JSON.stringify(itens)
            }, function(resp) {
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
                            success: function(resp2) {
                                try {
                                    var data2 = typeof resp2 === 'string' ? JSON
                                        .parse(resp2) : resp2;
                                    if (data2 && data2.ok) {
                                        window.location.href =
                                            '/fws/FWS_Cliente/carrinho/HTML/carrinho.php';
                                    } else {
                                        mostrarToastSucesso('Pedido refeito!');
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 1200);
                                    }
                                } catch (e) {
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
                } catch (e) {
                    alert('Erro inesperado.');
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
            setTimeout(function() {
                toast.fadeOut(300, function() {
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
            $(".ok-close").on("click", function() {
                $("#modal-estoque-falta, #modal-backdrop").hide();
            });
        }

        // Evento para adicionar 15 minutos ao tempo limite
        $(document).on('click', '.adicionar-tempo', function(e) {
            e.preventDefault();
            var btn = $(this);
            var venda_id = btn.data('venda-id');
            
            // Desabilitar o botão para evitar múltiplos cliques
            btn.prop('disabled', true);
            btn.css('opacity', '0.5');
            btn.css('cursor', 'not-allowed');
            
            $.ajax({
                url: '',
                method: 'POST',
                data: {
                    adicionar_tempo: 1,
                    venda_id: venda_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.ok) {
                        // Ocultar o botão
                        btn.hide();
                        
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
                        }).text('15 minutos adicionados!').appendTo('body');
                        setTimeout(function() {
                            toast.fadeOut(300, function() {
                                toast.remove();
                            });
                        }, 1200);
                        
                        // Esperar 2 segundos e atualizar a página
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        btn.prop('disabled', false);
                        btn.css('opacity', '1');
                        btn.css('cursor', 'pointer');
                        alert('Erro ao adicionar tempo: ' + response.erro);
                    }
                },
                error: function() {
                    btn.prop('disabled', false);
                    btn.css('opacity', '1');
                    btn.css('cursor', 'pointer');
                    alert('Erro ao processar requisição.');
                }
            });
        });

        // Chama o script de verificação/cancelamento a cada 30 segundos


        // Adiciona mensagem fixa de atualização

        // Adiciona mensagem fixa de atualização logo abaixo do header
        var $atualizando = $('<div id="atualizando-pedidos"></div>').css({
            position: 'absolute',
            top: $('#header').outerHeight() + 'px',
            left: '0',
            width: '100%',
            background: '#11C47E',
            color: '#fff',
            padding: '10px 0',
            fontWeight: '700',
            fontSize: '1.1rem',
            textAlign: 'center',
            zIndex: 1000,
            display: 'none'
        }).text('Atualizando pedidos...');
        // Insere após o header
        $('#header').after($atualizando);

        function mostrarAtualizando() {
            $atualizando.stop(true, true).fadeIn(200);
        }
        function esconderAtualizando() {
            $atualizando.stop(true, true).delay(3000).fadeOut(400); // 3 segundos
        }

        // Mostra ao abrir a página
        mostrarAtualizando();
        $.ajax({
            url: '/fws/FWS_ADM/PHP/verificar_tempo_limite.php',
            method: 'GET',
            data: { action: 'verificar_todos' },
            dataType: 'json',
            complete: function() {
                esconderAtualizando();
            }
        });

        // Só executa a verificação se houver pedidos em andamento
        function temPedidoEmAndamento() {
            var existe = false;
            $('.badge').each(function() {
                var txt = $(this).text().trim().toLowerCase();
                if (txt === 'em preparação') {
                    existe = true;
                    return false;
                }
            });
            return existe;
        }

        setInterval(function() {
            if (temPedidoEmAndamento()) {
                mostrarAtualizando();
                $.ajax({
                    url: '/fws/FWS_ADM/PHP/verificar_tempo_limite.php',
                    method: 'GET',
                    data: { action: 'verificar_todos' },
                    dataType: 'json',
                    complete: function() {
                        esconderAtualizando();
                    }
                });
            }
        }, 20000); // 20 segundos

        // Recarrega a página a cada 20 segundos
        setInterval(function() {
            location.reload();
        }, 20000);
    });
    </script>

    <!-- Bootstrap JavaScript Bundle with Popper - DEVE estar no final do body -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
