<?php
session_start();
include "../../conn.php";
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

        .logo img { height: 45px; }
        .menu-toggle { display: none; font-size: 28px; background: none; border: none; color: white; margin-left: auto; cursor: pointer; }
        nav { margin-left: 40px; }
        nav ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
        nav ul li a { text-decoration: none; color: white; font-weight: bold; font-size: 15px; }
        .carrinho img { height: 27px; }
        #bem-vindo { font-weight: bold; font-size: 16px; color: white; }

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
            nav { position: fixed; top: 70px; left: 0; width: 100%; background: var(--primary-red); padding: 20px 0; display: none; z-index: 2000; }
            nav.active { display: block; }
            nav ul { flex-direction: column; text-align: center; gap: 14px; }
            .menu-toggle { display: block; }
            .carrinho, #bem-vindo { display: none; }
        }

        /* Responsividade: Card de Pedido */
        @media (max-width: 768px) {
            /* Cabeçalho do Card */
            .card-header-pedido {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 15px !important;
                padding: 15px 20px !important;
            }

            .card-header-pedido > div:last-child {
                width: 100%;
                text-align: left !important;
            }

            .card-header-pedido h2 {
                font-size: 1.2rem !important;
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
            #modal-pagamento > div {
                max-width: 90% !important;
                width: 90% !important;
            }

            #modal-pagamento > div h3 {
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
            #modal-pagamento > div {
                max-width: 95% !important;
                padding: 20px !important;
            }

            #modal-pagamento > div h3 {
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
                            data: { q: request.term },
                            success: function (data) { response(data); }
                        });
                    },
                    minLength: 2,
                    select: function (event, ui) {
                        window.location.href = 'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                    }
                }).data('ui-autocomplete') || $("#search").data('autocomplete');

                if (autocomplete) {
                    autocomplete._renderItem = function (ul, item) {
                        return $("<li>")
                            .append("<div><img src='" + item.foto + "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle; background-color: #FFD100 !important;'/>" + item.label + "</div>")
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
            <h1 style="font-size: 2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 30px; text-align: center;">
                Detalhes do Pedido
            </h1>

            <!-- Card Principal do Pedido -->
            <div class="card" style="border: 3px solid var(--accent-yellow); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 12px; overflow: hidden;">
                
                <!-- Cabeçalho do Card -->
                <div class="card-header-pedido" style="background: linear-gradient(135deg, var(--primary-red) 0%, #a00000 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: bold;">Pedido #136ADG</h2>
                        <p style="margin: 5px 0 0 0; font-size: 0.95rem; opacity: 0.95;">Realizado em 03/12/2025 às 19:00</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 10px 18px; border-radius: 8px; font-weight: bold; text-align: right;">
                        <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Status</p>
                        <p style="margin: 5px 0 0 0; font-size: 1.1rem;">Não retirado</p>
                    </div>
                </div>

                <!-- Conteúdo do Card -->
                <div class="card-content" style="padding: 30px;">
                    
                    <!-- Seção: Informações do Pedido -->
                    <div class="card-section" style="margin-bottom: 30px;">
                        <h3 style="font-size: 1.2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 15px; border-bottom: 2px solid var(--accent-yellow); padding-bottom: 10px;">
                            📋 Informações do Pedido
                        </h3>
                        <div class="info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Cliente:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">Pedro Henrique Souza Brito</p>
                            </div>
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Pagamento:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">Cartão Débito</p>
                            </div>
                            <div>
                                <label style="font-weight: bold; color: #333; font-size: 0.9rem;">Tempo Restante:</label>
                                <p style="margin: 5px 0 0 0; font-size: 1rem; color: #555;">
                                    <i class="fas fa-hourglass-end" style="color: var(--accent-orange);"></i> 30 minutos
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Seção: Itens do Pedido -->
                    <div class="card-section" style="margin-bottom: 30px;">
                        <h3 style="font-size: 1.2rem; font-weight: bold; color: var(--primary-red); margin-bottom: 15px; border-bottom: 2px solid var(--accent-yellow); padding-bottom: 10px;">
                            🛒 Itens do Pedido
                        </h3>
                        <div style="background: #f9f9f9; border-radius: 8px; overflow-x: auto; border: 1px solid #e0e0e0;">
                            <table class="items-table" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background-color: var(--accent-yellow); color: #111;">
                                        <th style="padding: 12px; text-align: left; font-weight: bold;">Qtd</th>
                                        <th style="padding: 12px; text-align: left; font-weight: bold;">Produto</th>
                                        <th style="padding: 12px; text-align: right; font-weight: bold;">Valor Unit.</th>
                                        <th style="padding: 12px; text-align: right; font-weight: bold;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #e0e0e0;">
                                        <td style="padding: 12px; text-align: left;">1</td>
                                        <td style="padding: 12px; text-align: left; color: #333;">Água de Coco Kero Coco 1L</td>
                                        <td style="padding: 12px; text-align: right; color: #555;">R$ 31,99</td>
                                        <td style="padding: 12px; text-align: right; font-weight: bold; color: var(--primary-red);">R$ 31,99</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; text-align: left;">4</td>
                                        <td style="padding: 12px; text-align: left; color: #333;">Água de Coco Kero Coco 1L</td>
                                        <td style="padding: 12px; text-align: right; color: #555;">R$ 31,99</td>
                                        <td style="padding: 12px; text-align: right; font-weight: bold; color: var(--primary-red);">R$ 127,96</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Seção: Resumo Financeiro -->
                    <div class="financial-summary" style="margin-bottom: 30px; background: linear-gradient(to right, rgba(255, 209, 0, 0.1), rgba(196, 0, 0, 0.05)); padding: 20px; border-radius: 8px; border-left: 4px solid var(--accent-yellow);">
                        <div class="financial-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Subtotal:</span>
                            <span style="color: #555;">R$ 159,95</span>
                        </div>
                        <div class="financial-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Frete:</span>
                            <span style="color: #555;">R$ 0,00</span>
                        </div>
                        <div class="financial-row" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 500; color: #333;">Desconto:</span>
                            <span style="color: #555;">-R$ 0,00</span>
                        </div>
                        <hr style="margin: 10px 0; border: none; border-top: 1px solid rgba(0,0,0,0.1);">
                        <div class="financial-total" style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold; font-size: 1.1rem; color: var(--primary-red);">Total:</span>
                            <span style="font-weight: bold; font-size: 1.3rem; color: var(--primary-red);">R$ 159,95</span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="action-buttons" style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button style="flex: 1; min-width: 150px; padding: 12px 20px; background-color: var(--accent-yellow); color: #111; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: all 0.3s;">
                            ⏱️ Adicionar 15 Minutos
                        </button>
                        <button id="btn-alterar-pagamento" style="flex: 1; min-width: 150px; padding: 12px 20px; background-color: var(--accent-orange); color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: all 0.3s;">
                            💳 Alterar Pagamento
                        </button>
                        <a href="../../meus_pedidos/HTML/Meus_pedidos.php" style="flex: 1; min-width: 150px; padding: 12px 20px; background-color: var(--primary-red); color: white; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; transition: all 0.3s; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                            ← Voltar aos Pedidos
                        </a>
                    </div>

                    <!-- Modal de Pagamento -->
                    <div id="modal-pagamento" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
                        <div style="background: white; border-radius: 12px; padding: 30px; max-width: 400px; width: 90%; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
                            <h3 style="font-size: 1.3rem; font-weight: bold; color: var(--primary-red); margin-bottom: 20px; text-align: center;">
                                Selecionar Método de Pagamento
                            </h3>

                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <!-- Crédito -->
                                <button class="payment-option" data-payment="cartao-credito" style="padding: 15px; border: 2px solid #ddd; border-radius: 8px; background-color: #f9f9f9; cursor: pointer; text-align: left; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-credit-card" style="font-size: 1.3rem; color: var(--accent-orange);"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Cartão de Crédito</p>
                                        <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #999;">Pague depois</p>
                                    </div>
                                </button>

                                <!-- Débito -->
                                <button class="payment-option" data-payment="cartao-debito" style="padding: 15px; border: 2px solid #FFD100; border-radius: 8px; background-color: rgba(255, 209, 0, 0.1); cursor: pointer; text-align: left; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-university" style="font-size: 1.3rem; color: var(--accent-orange);"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Cartão de Débito</p>
                                        <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #999;">Método atual</p>
                                    </div>
                                </button>

                                <!-- PIX -->
                                <button class="payment-option" data-payment="pix" style="padding: 15px; border: 2px solid #ddd; border-radius: 8px; background-color: #f9f9f9; cursor: pointer; text-align: left; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-qrcode" style="font-size: 1.3rem; color: var(--accent-orange);"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: bold; color: #333;">PIX</p>
                                        <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #999;">Instantâneo</p>
                                    </div>
                                </button>

                                <!-- Dinheiro -->
                                <button class="payment-option" data-payment="dinheiro" style="padding: 15px; border: 2px solid #ddd; border-radius: 8px; background-color: #f9f9f9; cursor: pointer; text-align: left; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-money-bill-wave" style="font-size: 1.3rem; color: var(--accent-orange);"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Dinheiro</p>
                                        <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #999;">Na retirada</p>
                                    </div>
                                </button>

                                <!-- Boleto -->
                                <button class="payment-option" data-payment="boleto" style="padding: 15px; border: 2px solid #ddd; border-radius: 8px; background-color: #f9f9f9; cursor: pointer; text-align: left; transition: all 0.3s; display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-receipt" style="font-size: 1.3rem; color: var(--accent-orange);"></i>
                                    <div>
                                        <p style="margin: 0; font-weight: bold; color: #333;">Boleto Bancário</p>
                                        <p style="margin: 3px 0 0 0; font-size: 0.85rem; color: #999;">Até 3 dias úteis</p>
                                    </div>
                                </button>
                            </div>

                            <button id="btn-fechar-modal" style="width: 100%; margin-top: 20px; padding: 10px; background-color: #ddd; color: #333; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; font-size: 0.95rem; transition: all 0.3s;">
                                Cancelar
                            </button>
                        </div>
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

    <!-- Script para gerenciar modal de pagamento -->
    <script>
        // Elementos do modal
        const modal = document.getElementById('modal-pagamento');
        const btnAbrirModal = document.getElementById('btn-alterar-pagamento');
        const btnFecharModal = document.getElementById('btn-fechar-modal');
        const paymentOptions = document.querySelectorAll('.payment-option');

        // Abrir modal
        btnAbrirModal.addEventListener('click', function() {
            modal.style.display = 'flex';
        });

        // Fechar modal (botão cancelar)
        btnFecharModal.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Fechar modal ao clicar fora
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Selecionar opção de pagamento
        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                const paymentMethod = this.getAttribute('data-payment');
                const paymentText = this.querySelector('p').textContent;

                // Atualizar o método de pagamento na página
                updatePaymentMethod(paymentText, paymentMethod);

                // Fechar modal
                modal.style.display = 'none';

                // Mostrar mensagem de sucesso
                showSuccessMessage(`Pagamento alterado para: ${paymentText}`);
            });

            // Efeito visual ao passar o mouse
            option.addEventListener('mouseenter', function() {
                this.style.borderColor = 'var(--accent-orange)';
                this.style.backgroundColor = 'rgba(243, 122, 39, 0.05)';
            });

            option.addEventListener('mouseleave', function() {
                if (this.getAttribute('data-payment') !== 'cartao-debito') {
                    this.style.borderColor = '#ddd';
                    this.style.backgroundColor = '#f9f9f9';
                }
            });
        });

        // Função para atualizar o método de pagamento exibido
        function updatePaymentMethod(paymentText, paymentMethod) {
            // Encontrar o elemento que exibe o pagamento
            const paymentDisplay = document.querySelector('[style*="Pagamento"]')?.parentElement;
            if (paymentDisplay) {
                const paymentValue = paymentDisplay.querySelector('p:last-of-type');
                if (paymentValue) {
                    paymentValue.textContent = paymentText;
                }
            }

            // Aqui você pode adicionar uma requisição AJAX para atualizar no backend se necessário
            // Por exemplo:
            // fetch('update-pagamento.php', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ metodo: paymentMethod })
            // });
        }

        // Função para mostrar mensagem de sucesso
        function showSuccessMessage(message) {
            // Criar div de notificação
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #4caf50;
                color: white;
                padding: 15px 20px;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: bold;
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Remover após 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Adicionar animações CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }

            .payment-option:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>