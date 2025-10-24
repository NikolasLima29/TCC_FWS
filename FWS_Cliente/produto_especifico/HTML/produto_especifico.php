<?php
session_start();
include "../../conn.php";


if (isset($_GET['id'])) {
    $_SESSION['produto_id'] = $_GET['id'];
    echo "Produto ID armazenado na sessão: " . $_SESSION['produto_id'];
}


?>

<!doctype html>
<html lang="pt-BR">

<head>
    <title>Produtos</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous" />

    <link rel="stylesheet" href="../CSS/produto_especifico.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
    <!-- Header with same nav, style, and behavior -->
    <header id="header">
        <div class="logo">
            <a href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <button class="menu-toggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav>
            <ul class="ul align-items-center">
                <li>
                    <a href="../../produto/HTML/produto.php">Produtos</a>
                </li>
                <li>
                    <form class="d-flex" role="search" action="busca.php" method="get" style="margin: 0 10px;">
                        <input class="form-control form-control-sm me-2" type="search" name="q"
                            placeholder="Pesquisar..." aria-label="Pesquisar">
                        <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </li>
                <li>
                    <a href="../../tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a>
                </li>
            </ul>
        </nav>

        <div class="carrinho">
            <a href="#">
                <img src="../../index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
            </a>
        </div>

        <div id="bem-vindo">
            <?php
            if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) {
                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                $primeiroNome = explode(' ', $nomeCompleto)[0];
                echo "Bem-vindo(a), " . $primeiroNome;
            } else {
                echo "Bem-vindo(a).";
            }
            ?>
        </div>


    </header>
    <main>

<main class="my-5">
  <div class="container">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
      <!-- Card 1 -->
      <div class="col">
        <div class="card h-100">
          <img src="/TCC_FWS/IMG_Produtos/19.png" class="card-img-top" alt="Produto 1">
          <div class="card-body">
            <h5 class="card-title">Produto 1</h5>
            <p class="card-text" style="color green;">Preço produto</p>
            <a href="#" class="btn btn-primary">Ver mais</a>
          </div>
        </div>
      </div>
      <!-- Copie mais cards conforme necessário -->
    </div>
  </div>
</main>



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

</body>

</html>