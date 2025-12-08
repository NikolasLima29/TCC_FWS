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

    <!-- Bootstrap CSS v5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../CSS/sobre_nos.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-papm6QpQKQwQvQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQ==" crossorigin="anonymous" />

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
        }

        /* Media Query - Desktop (577px ou maior) */
        @media (min-width: 577px) {
            .d-flex.align-items-center.ms-auto.me-4 h5 {
                font-size: 16.8px !important;
                margin-bottom: 9px !important;
                font-family: 'Ubuntu', sans-serif !important;
                font-weight: bold !important;
                margin-left: 0px !important;
            }
            /* Aumenta 30% o tamanho dos títulos do menu */
            .navbar-nav .menu-bold {
                font-size: 23.1px !important;
            }
        }
        /* ========== FIM DO CSS DO HEADER ========== */

        /* ========== CSS DO RESTO DA PÁGINA ========== */
        /* Estiliza os botões do carrossel */
        .carousel-indicators button {
            background-color: #c40000 !important;
            margin-top: 0 !important;
            transform: translateY(10px);
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
                        <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30" style="object-fit: contain; filter: brightness(0) invert(1);">
                    </a>

                    <!-- Perfil Mobile (com validação de login) -->
                    <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>" class="me-2">
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
                    <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get" style="margin: 0;">
                        <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..." style="width: 300px;">
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
                    <ul class="navbar-nav d-flex align-items-center gap-4 justify-content-center w-100" style="margin-right: 40px;">
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
                        <a href="../../carrinho/HTML/carrinho.php">
                            <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30" class="me-4" style="object-fit: contain; filter: brightness(0) invert(1);">
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
                        <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>">
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
                <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">Bem-vindo(a), <?= $primeiroNome ?></h5>
            <?php else: ?>
                <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">Bem-vindo(a)</h5>
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
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="3" aria-label="Slide 4"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="4" aria-label="Slide 5"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="5" aria-label="Slide 6"></button>
                <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="6" aria-label="Slide 7"></button>
            </div>
            <div class="carousel-inner rounded shadow-lg" style="max-width: 700px; margin: 0 auto;">
                <div class="carousel-item active">
                    <img src="../IMG/exterior1.jpeg" class="d-block w-100" id="carrossel" alt="Fachada da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior1.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior2.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior3.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior4.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior5.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
                <div class="carousel-item">
                    <img src="../IMG/interior6.jpeg" class="d-block w-100" id="carrossel" alt="Interior da loja" style="border-radius: 16px; height: 350px; object-fit: cover;">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
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
        <div id="mapa-container" style="margin-top: 40px; margin-bottom: 40px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <h2 style="text-align: center; margin-bottom: 20px; width: 100%; font-weight: bold; color: #c40000; letter-spacing: 1px;">Nos Visite!</h2>
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3657.0842014343346!2d-46.65555!3d-23.55614!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94ce59c73d6c5555%3A0x1c1c1c1c1c1c1c!2sR.%20Col%C3%B4mbia%2C%2026%20-%20Jardim%20Am%C3%A9rica%2C%20S%C3%A3o%20Paulo%20-%20SP%2C%2001438-000!5e0!3m2!1spt-BR!2sbr!4v1734012345678"
                width="70%" height="300" style="border:0; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.12);" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </main>

    <footer class="text-center bg-body-tertiary">
        <div class="container pt-4">
            <section class="mb-4 d-flex justify-content-center gap-3">
                <a class="btn btn-link btn-floating btn-lg text-body m-1" href="https://www.facebook.com/ShellBrasil?locale=pt_BR" role="button" style="padding: 0;">
                    <img src="../IMG/face.png" alt="Facebook" style="width: 32px; height: 32px; object-fit: contain;">
                </a>
                <a class="btn btn-link btn-floating btn-lg text-body m-1" href="tel:+5511999999999" role="button" style="padding: 0;">
                    <img src="../IMG/telefone.png" alt="Telefone" style="width: 32px; height: 32px; object-fit: contain;">
                </a>
                <a class="btn btn-link btn-floating btn-lg text-body m-1" href="https://www.instagram.com/shell.brasil/" role="button" style="padding: 0;">
                    <img src="../IMG/insatgram.png" alt="Instagram" style="width: 32px; height: 32px; object-fit: contain;">
                </a>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

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
                    window.location.href = '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search").data('autocomplete');

            if (autocomplete) {
                autocomplete._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append("<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" + item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label + "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>")
                        .appendTo(ul);
                };
            }

            // Autocomplete para mobile
            var autocompleteMobile = $("#search-mobile").autocomplete({
                source: function (request, response) {
                    console.log('AJAX mobile chamado:', request.term);
                    $.ajax({
                        url: '../../produto/PHP/api-produtos.php',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function (data) {
                            console.log('Dados recebidos mobile:', data);
                            response(data);
                        }
                    });
                },
                minLength: 1, // Forçar abrir com 1 caractere para testar
                select: function (event, ui) {
                    window.location.href = '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search-mobile").data('autocomplete');

            if (autocompleteMobile) {
                autocompleteMobile._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append("<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" + item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label + "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>")
                        .appendTo(ul);
                };
            }
        });
    </script>

    <style>
        .ui-autocomplete {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            padding: 0 !important;
            max-height: 400px;
            overflow-y: auto;
            z-index: 9999 !important;
        }
        /* Garante que o autocomplete do mobile fique com a largura do input */
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('nav ul.ul');

            if (!toggleButton || !navLinks) return;

            toggleButton.setAttribute('aria-expanded', 'false');

            function setMenu(open) {
                if (open) {
                    navLinks.classList.add('active');
                    toggleButton.setAttribute('aria-expanded', 'true');
                    toggleButton.innerHTML = '<i class="fas fa-times"></i>';
                    navLinks.setAttribute('aria-hidden', 'false');
                } else {
                    navLinks.classList.remove('active');
                    toggleButton.setAttribute('aria-expanded', 'false');
                    toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                    navLinks.setAttribute('aria-hidden', 'true');
                }
            }

            toggleButton.addEventListener('click', (e) => {
                e.stopPropagation();
                setMenu(!navLinks.classList.contains('active'));
            });

            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => setMenu(false));
            });

            document.addEventListener('click', (e) => {
                if (!navLinks.classList.contains('active')) return;
                if (!navLinks.contains(e.target) && !toggleButton.contains(e.target)) {
                    setMenu(false);
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && navLinks.classList.contains('active')) {
                    setMenu(false);
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    navLinks.classList.remove('active');
                    toggleButton.setAttribute('aria-expanded', 'false');
                    toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                    navLinks.setAttribute('aria-hidden', 'false');
                } else {
                    navLinks.setAttribute('aria-hidden', 'true');
                }
            });
        });
    </script>

    <style>
        #header {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #c40000;
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

        header nav ul li a {
            font-size: 24px;
        }
    </style>


</body>

</html>
