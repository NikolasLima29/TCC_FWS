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
</head>

<body>
    <!-- Header with same nav, style, and behavior -->
    <header id="header">
        <style>
            #header {
                position: sticky;
                top: 0;
                background-color: rgba(255, 255, 255, 0.2);
                /* transparência correta */
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
        </style>
        <div class="logo">
            <a href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <button class="menu-toggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="nav-links">
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
                <li><a href="/fws/FWS_Cliente/meus_pedidos/HTML/Meus_pedidos.php">Meus pedidos</a></li>
                <li><a href="/fws/FWS_Cliente/tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
            </ul>
        </nav>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggleButton = document.querySelector('.menu-toggle');
                const navLinks = document.querySelector('nav.nav-links');

                if (!toggleButton || !navLinks) return;

                toggleButton.setAttribute('aria-expanded', 'false');

                function setMenu(open) {
                    if (open) {
                        navLinks.classList.add('active');
                        toggleButton.innerHTML = '<i class="fas fa-times"></i>';
                        toggleButton.setAttribute('aria-expanded', 'true');
                    } else {
                        navLinks.classList.remove('active');
                        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
                        toggleButton.setAttribute('aria-expanded', 'false');
                    }
                }

                toggleButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    setMenu(!navLinks.classList.contains('active'));
                });

                document.addEventListener('click', (e) => {
                    if (!navLinks.classList.contains('active')) return;
                    if (!navLinks.contains(e.target) && !toggleButton.contains(e.target)) {
                        setMenu(false);
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 768) {
                        navLinks.classList.remove('active');
                    }
                });
            });
        </script>

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
                <i class="fas fa-user-circle fa-2x" style="max width: 90px;"></i>
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
    </header>
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
