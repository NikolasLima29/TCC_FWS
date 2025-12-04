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

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

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
        <section class="h-custom" style="background-color: #eee;">
            <div class="container py-3">
                <div class="row d-flex justify-content-center align-items-center">
                    <div class="container-fluid py-3">
                        <div class="row g-4 align-items-stretch">
                            <!-- g-4 adiciona espaço entre os cards -->

                            <!-- CARD 1 -->
                            <div class="col-12 col-lg-6 d-flex">
                                <div class="card h-100 w-100 border-top border-bottom border-3 card-accent">
                                    <div class="card-body p-5 d-flex flex-column h-100">
                                        <div class="row g-0 w-100 flex-grow-1">

                                            <!-- Coluna esquerda do card -->
                                            <div class="col-12 col-lg-6 pe-lg-4 d-flex flex-column"
                                                style="border-right: 2px solid #ddd;">
                                                <p class="lead fw-bold mb-4 accent-text">Código do Pedido:
                                                    136ADG</p>
                                                <p><strong>Data:</strong> 03/12/2025</p>
                                                <p><strong>Pedido por:</strong> Pedro Henrique Souza Brito</p>
                                                <p><strong>Pagamento:</strong> Cartão Débito</p>
                                                <div class="pedido-box px-3 py-3">
                                                    <div class="row pedido-item">
                                                        <div class="col-2">1</div>
                                                        <div class="col-7">Água de Coco Kero Coco 1L</div>
                                                        <div class="col-3 text-end">R$31,99</div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pedido-item">
                                                        <div class="col-2">4</div>
                                                        <div class="col-7">Água de Coco Kero Coco 1L</div>
                                                        <div class="col-3 text-end">R$31,99</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Coluna direita do card -->
                                            <div class="col-12 col-lg-6 ps-lg-4 mt-4 mt-lg-0 d-flex flex-column">
                                                <p class="lead fw-bold mt-4 accent-text">Total geral: R$61,39
                                                </p>
                                                <p class="lead fw-bold accent-text">Tempo restante: 30 Minutos
                                                </p>
                                                <p class="lead fw-bold accent-text">Horário do pedido: 19:00</p>

                                                <ul class="list-inline mt-4 d-flex flex-wrap gap-3">
                                                    <li class="list-inline-item items-list">
                                                        <select class="py-1 px-2 rounded btn-accent">
                                                            <option>Alterar pagamento:</option>
                                                            <option value="Cartão Crédito">Cartão Crédito</option>
                                                            <option value="Cartão Débito">Cartão Débito</option>
                                                            <option value="Pix">Pix</option>
                                                            <option value="Dinheiro">Dinheiro</option>
                                                        </select>
                                                    </li>

                                                    <li class="list-inline-item items-list">
                                                        <button class="py-1 px-2 rounded btn-accent">
                                                            Adicionar 15 Minutos
                                                        </button>
                                                    </li>

                                                    <li class="list-inline-item items-list">
                                                        <a href="../../pedido_detalhado/pedido_detalhado.php"
                                                            class="py-1 px-2 rounded no-underline btn-accent">
                                                            Visualizar pedido
                                                        </a>
                                                    </li>
                                                </ul>

                                                <p class="order-status">Status: Não retirado</p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 2 (igual ao card 1, só altere os dados) -->
                            <div class="col-12 col-lg-6 d-flex">
                                <div class="card h-100 w-100 border-top border-bottom border-3 card-accent">
                                    <div class="card-body p-5 d-flex flex-column h-100">
                                        <div class="row g-0 w-100 flex-grow-1">

                                            <!-- Coluna esquerda do card -->
                                            <div class="col-12 col-lg-6 pe-lg-4 d-flex flex-column"
                                                style="border-right: 2px solid #ddd;">
                                                <p class="lead fw-bold mb-4 accent-text">Código do Pedido:
                                                    137BHF</p>
                                                <p><strong>Data:</strong> 03/12/2025</p>
                                                <p><strong>Pedido por:</strong> Maria Silva</p>
                                                <p><strong>Pagamento:</strong> Pix</p>
                                                <div class="pedido-box px-3 py-3">
                                                    <div class="row pedido-item">
                                                        <div class="col-2">2</div>
                                                        <div class="col-7">Suco Natural 500ml</div>
                                                        <div class="col-3 text-end">R$15,50</div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pedido-item">
                                                        <div class="col-2">3</div>
                                                        <div class="col-7">Água Mineral 500ml</div>
                                                        <div class="col-3 text-end">R$5,99</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Coluna direita do card -->
                                            <div class="col-12 col-lg-6 ps-lg-4 mt-4 mt-lg-0 d-flex flex-column">
                                                <p class="lead fw-bold mt-4 accent-text">Total geral: R$21,49
                                                </p>
                                                <p class="lead fw-bold accent-text">Tempo restante: 25 Minutos
                                                </p>
                                                <p class="lead fw-bold accent-text">Horário do pedido: 19:30</p>

                                                <ul class="list-inline mt-4 d-flex flex-wrap gap-3">
                                                    <li class="list-inline-item items-list">
                                                        <select class="py-1 px-2 rounded btn-accent">
                                                            <option>Alterar pagamento:</option>
                                                            <option value="Cartão Crédito">Cartão Crédito</option>
                                                            <option value="Cartão Débito">Cartão Débito</option>
                                                            <option value="Pix">Pix</option>
                                                            <option value="Dinheiro">Dinheiro</option>
                                                        </select>
                                                    </li>

                                                    <li class="list-inline-item items-list">
                                                        <button class="py-1 px-2 rounded btn-accent">
                                                            Adicionar 15 Minutos
                                                        </button>
                                                    </li>

                                                    <li class="list-inline-item items-list">
                                                        <a href="../../pedido_detalhado/pedido_detalhado.php"
                                                            class="py-1 px-2 rounded no-underline btn-accent">
                                                            Visualizar pedido
                                                        </a>
                                                    </li>
                                                </ul>

                                                <p class="order-status">Status: Não retirado</p>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
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
</body>

</html>