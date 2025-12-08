<?php
session_start();
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <title>Sobre Nós</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous" />

    <link rel="stylesheet" href="../CSS/sobre_nos.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- LINKS PARA FUNCIONAR A PESQUISA INSTANTANEA -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- JQuery UI css -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <style>
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

        /* Mostrar a busca dentro do menu mobile */
        @media (max-width: 576px) {
            .navbar-collapse .search-area-mobile {
                display: flex !important;
                width: 100%;
                margin-bottom: 15px;
            }

            .navbar-collapse .search-area-mobile input {
                width: 100% !important;
            }
        }

        /* Alinha ícones e toggle à esquerda no mobile */
        @media (max-width: 576px) {
            .navbar .d-flex.align-items-center.ms-3 {
                justify-content: flex-start;
                width: 100%;
                gap: 0.5rem;
            }

            /* Oculta duplicidade dentro do container-fluid */
            .container-fluid .d-flex.align-items-center.ms-auto.me-4 {
                display: none !important;
            }
        }

        /* Ícone hamburguer branco */
        .navbar-toggler-icon {
            filter: invert(100%);
        }

        /* Ajuste opcional para melhorar o layout mobile */
        .search-mobile-container input {
            font-size: 16px;
        }

        .search-mobile-container button {
            font-size: 16px;
            background-color: #FFD100 !important;
            border-color: #FFD100 !important;
        }

        .search-area button {
            background-color: #FFD100 !important;
            border-color: #FFD100 !important;
        }

        /* Ajustes MOBILE */
        @media (max-width: 576px) {
            .navbar-nav {
                gap: 1rem !important;
            }

            .navbar-nav .nav-item h5 {
                font-size: 16px;
                text-align: center;
            }

            /* Input ocupa largura total no mobile */
            .navbar-nav input {
                width: 100% !important;
                margin-bottom: 10px;
            }

            /* Área do carrinho + texto + pessoa fica em coluna */
            .user-area {
                width: 100%;
                justify-content: center !important;
                margin-top: 15px;
            }

            .logo-shell {
                width: 120px !important;
            }
        }

        /* Corrige o botão hamburguer (ele estava invisível porque navbar-light no fundo vermelho) */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.6) !important;
        }

        .navbar-toggler-icon {
            filter: invert(100%);
        }

        .menu-bold {
            font-weight: 700 !important;
        }

        .logo-shell {
            width: 150px;
            height: auto;
        }

        /* Altera a cor do ícone de busca para preto */
        .bi-search {
            fill: #000000 !important;
        }
    </style>
</head>

<body>
    <header>
        <!-- NAVBAR COM COR VERMELHA -->
        <nav class="navbar navbar-expand-sm navbar-light" style="background-color: #c40000;">

            <!-- LOGO REDUZIDA PARA 40PX -->
            <a class="navbar-brand ms-3" href="#">
                <img src="../../index/IMG/shell_select.png" alt="Logo" class="logo-shell">
            </a>

            <div class="d-flex align-items-center ms-3">

                <!-- TOGGLER -->
                <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- ÁREA USUÁRIO MOBILE (aparece somente no mobile) -->
                <div class="d-flex align-items-center d-sm-none">
                    <!-- Carrinho -->
                    <a href="" class="me-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white"
                            class="bi bi-cart-fill" viewBox="0 0 16 16">
                            <path
                                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4" />
                        </svg>
                    </a>

                    <!-- Texto -->
                    <h5 class="text-white me-2 m-0">Bem-vindo(a)</h5>

                    <!-- Ícone pessoa -->
                    <a href="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white"
                            class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                    </a>
                </div>

                <!-- BARRA DESKTOP -->
                <div class="search-area d-none d-sm-flex align-items-center ms-auto">
                    <input class="form-control me-2" type="text" placeholder="Pesquisar..." style="width: 300px;">
                    <button class="btn btn-outline-light" type="submit"
                        style="background-color: #FFD100; border-color: #FFD100;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-search"
                            viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="collapsibleNavId">

                    <!-- ITENS ALINHADOS NA MESMA LINHA -->
                    <ul class="navbar-nav d-flex align-items-center gap-4">

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <h5 class="m-0 text-white menu-bold">Produtos</h5>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <h5 class="m-0 text-white menu-bold">Meus Pedidos</h5>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <h5 class="m-0 text-white menu-bold">Sobre Nós</h5>
                            </a>
                        </li>
                    </ul>

                    <!-- ÁREA DO CARRINHO + TEXTO + ÍCONE PESSOA -->
                    <div class="d-flex align-items-center ms-auto me-4">
                        <!-- CARRINHO COM ESPAÇO -->
                        <a href="">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white"
                                class="bi bi-cart-fill me-4" viewBox="0 0 16 16">
                                <path
                                    d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2" />
                            </svg>
                        </a>

                        <!-- ESPAÇO ENTRE TEXTO E ÍCONES -->
                        <h5 class="text-white me-2">Bem-vindo(a)</h5>

                        <a href="">
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
        <!-- BARRA DE BUSCA MOBILE (ABAIXO DA HEADER) -->
        <div class="search-mobile-container d-sm-none px-3 py-2" style="background-color: #c40000;">
            <div class="d-flex">
                <input class="form-control me-2" type="text" placeholder="Pesquisar...">
                <button class="btn btn-outline-light" type="submit"
                    style="background-color: #FFD100; border-color: #FFD100;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" class="bi bi-search"
                        viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <div id="bem-vindo" style="position: relative; display: inline-block;">
        <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
        <?php
                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                $primeiroNome = explode(' ', $nomeCompleto)[0];
            ?>
        Bem-vindo(a), <?= $primeiroNome ?>
        <div style="display: inline-block; margin-left: 8px; cursor: pointer;" id="user-menu-toggle">
            <i class="fas fa-user-circle fa-2x" style="max width: 90px;"></i>
        </div>

        <div id="user-menu"
            style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 0; min-width: 120px; z-index: 1000;">
            <a href="/Fws/FWS_Cliente/info_usuario/HTML/info_usuario.php"
                style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
            <a href="/Fws/FWS_Cliente/logout.php" id="logout-link"
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

    <main>
        <h1 id="sobre_nos">Sobre Nós</h1>
        <p>
            A Shell Select representa a evolução das lojas de conveniência no Brasil, trazendo praticidade, qualidade e
            um ambiente acolhedor para o dia a dia. Desde que as primeiras lojas surgiram, nos anos 1920 nos Estados
            Unidos, o conceito de conveniência passou a significar mais do que apenas produtos rápidos: tornou-se uma
            extensão da rotina urbana.
        </p>
        <p>
            No Brasil, as lojas de conveniência ganharam força nos anos 1990, e a Shell foi uma das pioneiras ao
            transformar os postos de combustíveis em pontos completos de serviço. Hoje, a Shell Select combina o que há
            de melhor em snacks, bebidas, cafés e refeições rápidas, com um atendimento pensado para oferecer mais que
            uma compra rápida — uma experiência prática e agradável a qualquer hora do dia.
        </p>
        <div id="carouselExample" class="carousel slide">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="../IMG/exterior1.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior1.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior2.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior3.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior4.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior5.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior6.jpeg" class="d-block w-100" id="carrossel" alt="...">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <p>
            Você sabia que a ideia de loja de conveniência surgiu porque as pessoas queriam comprar o essencial sem
            precisar entrar em um supermercado enorme? Lá nos anos 20, nos EUA, nasceu esse conceito — e desde então ele
            só cresceu!
        </p>
        <p>
            Aqui no Brasil, a Shell Select reinventou o que significa parar no posto. É café quente, snack gostoso,
            wi-fi e aquele atendimento que salva o seu corre. Seja no caminho do trabalho, voltando da balada ou só
            dando uma escapada rápida, a Shell Select tá sempre ali, pronta pra te atender com qualidade e
            praticidade.Aqui no Brasil, a Shell Select reinventou o que significa parar no posto. É café quente, snack
            gostoso, wi-fi e aquele atendimento que salva o seu corre. Seja no caminho do trabalho, voltando da balada
            ou só dando uma escapada rápida, a Shell Select tá sempre ali, pronta pra te atender com qualidade e
            praticidade.
        </p>
        <img src="../IMG/interior7.jpeg" id="imagem">
        <div id="mapa-container" style="margin-top: 40px; margin-bottom: 40px; 
            display: flex; flex-direction: column; 
            justify-content: center; align-items: center;">

            <h2 style="text-align: center; margin-bottom: 20px; width: 100%;">Nos Visite!</h2>

            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.0842014343346!2d-46.65555!3d-23.55614!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94ce59c73d6c5555%3A0x1c1c1c1c1c1c1c1c!2sR.%20Col%C3%B4mbia%2C%2026%20-%20Jardim%20Am%C3%A9rica%2C%20S%C3%A3o%20Paulo%20-%20SP%2C%2001438-000!5e0!3m2!1spt-BR!2sbr!4v1734012345678"
                width="70%" height="300" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </main>

    <footer class="text-center bg-body-tertiary">
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

    <!-- Bootstrap JS and Popper for 5.2.1 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>

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
                    return $("<li>")
                        .append("<div><img src='" + item.foto +
                            "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle;  background-color: #FFD100 !important;'/>" +
                            item.label + "</div>")
                        .appendTo(ul);
                };
            }
        });
    </script>

    <style>
        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover {
            background-color: #FFD100 !important;
            background-image: none !important;
            color: #000 !important;
            cursor: pointer;
        }

        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover {
            box-shadow: none !important;
        }
    </style>

</body>

</html>