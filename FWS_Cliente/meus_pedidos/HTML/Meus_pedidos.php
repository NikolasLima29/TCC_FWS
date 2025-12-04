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
        <section class="h-100 h-custom" style="background-color: #eee;">
            <div class="container py-5 h-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-lg-6 col-xl-5">
                        <div class="card border-top border-bottom border-3" style="border-color: #f37a27 !important;">
                            <div class="card-body p-5">

                                <p class="lead fw-bold mb-5" style="color: #f37a27;">Código: 136ADG</p>

                                <div class="row">
                                    <div class="col mb-3">
                                        <p class="small text-muted mb-1">Data:</p>
                                        <p>03/12/2025</p>
                                    </div>
                                    <div class="col mb-3">
                                        <p class="small text-muted mb-1">Pedido por:</p>
                                        <p>Pedro Henrique Souza Brito</p>
                                    </div>
                                    <div class="col mb-3">
                                        <p class="small text-muted mb-1">Modo de pagamento:</p>
                                        <p>Cartão Débito</p>
                                    </div>
                                </div>

                                <div class="mx-n5 px-5 py-4" style="background-color: #f2f2f2;">
                                    <div class="row pedido-item">
                                        <div class="col-2">
                                            <p>1</p>
                                        </div>
                                        <div class="col-7">
                                            <p>Água de Coco Kero Coco 1L</p>
                                        </div>
                                        <div class="col-3 text-end">
                                            <p>R$31,99</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row pedido-item">
                                        <div class="col-2">
                                            <p>4</p>
                                        </div>
                                        <div class="col-7">
                                            <p>Água de Coco Kero Coco 1L</p>
                                        </div>
                                        <div class="col-3 text-end">
                                            <p>R$31,99</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row pedido-item">
                                        <div class="col-2">
                                            <p>5</p>
                                        </div>
                                        <div class="col-7">
                                            <p>Água de Coco Kero Coco 1L</p>
                                        </div>
                                        <div class="col-3 text-end">
                                            <p>R$31,99</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row pedido-item">
                                        <div class="col-2">
                                            <p>2</p>
                                        </div>
                                        <div class="col-7">
                                            <p>Água de Coco Kero Coco 1L</p>
                                        </div>
                                        <div class="col-3 text-end">
                                            <p>R$31,99</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row my-4">
                                    <p class="lead fw-bold mb-4 pb-2" style="color: #f37a27;">Total: R$61,39</p>
                                    <p class="lead fw-bold mb-4 pb-2" style="color: #f37a27;">Tempo restante: 30 Minutos
                                    </p>
                                    <p class="lead fw-bold mb-4 pb-2" style="color: #f37a27;">Horário do pedido: 19:00
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">

                                        <div class="horizontal-timeline">

                                            <ul class="list-inline items d-flex justify-content-between">
                                                <li class="list-inline-item items-list">
                                                    <select class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">
                                                        <option value="0">Alterar pagamento:</option>
                                                        <option value="Cartão Crédito">Cartão Crédito</option>
                                                        <option value="Cartão Débito">Cartão Débito</option>
                                                        <option value="Pix">Pix</option>
                                                        <option value="Dinheiro">Dinheiro</option>
                                                    </select class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">
                                                </li>
                                                <li class="list-inline-item items-list">
                                                    <button type="submit" class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">Adicionar 15 Minutos</button
                                                        class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">
                                                </li>
                                                <li class="list-inline-item items-list">
                                                    <a href="../../pedido_detalhado/pedido_detalhado.php"
                                                        class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">Vizualizar pedido</a
                                                        class="py-1 px-2 rounded text-white"
                                                        style="background-color: #f37a27;">
                                                </li>
                                            </ul>
                                            <li class="list-inline-item items-list text-end" style="margin-right: 8px;">
                                                <p style="margin-right: -8px;">Status: Não retirado</p>
                                            </li>
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

    <footer>
        <!-- place footer here -->
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