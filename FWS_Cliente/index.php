<?php
session_start();
?>

<!doctype html>
<html lang="pt-BR">

<head>
  <title>Home</title>
  <link rel="icon" type="image/x-icon" href="cadastro/IMG/Shell.png">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
  <link rel="stylesheet" href="index/CSS/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css">


  
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
  </style>





</head>

<body>
  <!-- Cabeçalho -->
  <header id="header">
    <!-- Logo -->
    <div class="logo">
      <a href="index.php">
        <img src="index/IMG/shell_select.png" alt="logo" />
      </a>
    </div>

    <!-- Botão do menu hambúrguer (aparece apenas no mobile) -->
    <button class="menu-toggle" aria-label="Abrir menu">
      <i class="fas fa-bars"></i>
    </button>

    <!-- Menu -->
    <nav>
      <ul class="ul align-items-center">
        <li>
          <a href="produto/HTML/produto.php">Produtos</a>
        </li>
        <li>
           <form class="d-flex" role="search" action="produto/HTML/produto.php" method="get"
                        style="margin: 0 10px;">
                        <input id="search" class="form-control form-control-sm me-2" type="search" name="q"
                            placeholder="Pesquisar..." aria-label="Pesquisar">
                        <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
        </li>
        <li>
          <a href="tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a>
        </li>
      </ul>
    </nav>


    <!-- Carrinho -->
    <div class="carrinho">
      <a href="#">
        <img src="index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
      </a>
    </div>

    <!-- Mensagem de boas-vindas -->
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

  <!-- Primeiro corpo -->

  <section class="section">
    <div class="botoes">
      <?php if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true): ?>
        <a href="cadastro/HTML/cadastro.html" class="btn">Cadastre-se</a>
        <a href="login/HTML/login.html" class="btn">Entrar</a>
      <?php endif; ?>
    </div>
    <div class="div-jd-america">
      <img class="jd-america" src="index/IMG/jd_america.png">
    </div>
  </section>

  <!-- Segundo cabeçalho -->
  <section class="mais-vendidos">
    <h1>Mais Vendidos <img src="index/IMG/sacola.png" alt="sacola"></h1>
  </section>

  <!-- Carrossel -->
  <section class="carrossel">
    <div id="carouselExampleCaptions" class="carousel slide">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"
          aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1"
          aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2"
          aria-label="Slide 3"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="3"
          aria-label="Slide 4"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="..." class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5 id="nome">First slide label</h5>
            <p id="descricao">Some representative placeholder content for the first slide.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="..." class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5 id="nome">Second slide label</h5>
            <p id="descricao">Some representative placeholder content for the second slide.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="..." class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5 id="nome">Third slide label</h5>
            <p id="descricao">Some representative placeholder content for the third slide.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="..." class="d-block w-100" alt="...">
          <div class="carousel-caption d-none d-md-block">
            <h5 id="nome">Fourth slide label</h5>
            <p id="descricao">Some representative placeholder content for the fourth slide.</p>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
        data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next"
        id="seta">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>
  </section>

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
          data-mdb-ripple-color="dark"><i class="fab fa-google"></i></a>

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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
    integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggleButton = document.querySelector('.menu-toggle');
      const navLinks = document.querySelector('.nav-links');

      if (!toggleButton || !navLinks) return; // segurança

      // Inicializa estado
      toggleButton.setAttribute('aria-expanded', 'false');

      // Função para abrir/fechar menu
      function setMenu(open) {
        if (open) {
          navLinks.classList.add('active');
          toggleButton.setAttribute('aria-expanded', 'true');
          toggleButton.innerHTML = '<i class="fas fa-times"></i>'; // X
          navLinks.setAttribute('aria-hidden', 'false');

        } else {
          navLinks.classList.remove('active');
          toggleButton.setAttribute('aria-expanded', 'false');
          toggleButton.innerHTML = '<i class="fas fa-bars"></i>'; // hambúrguer
          navLinks.setAttribute('aria-hidden', 'true');
        }
      }

      // Toggle ao clicar no botão
      toggleButton.addEventListener('click', (e) => {
        e.stopPropagation();
        setMenu(!navLinks.classList.contains('active'));
      });

      // Fecha quando clicar em qualquer link do menu (útil em mobile)
      navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => setMenu(false));
      });

      // Fecha ao clicar fora do menu
      document.addEventListener('click', (e) => {
        if (!navLinks.classList.contains('active')) return;
        if (!navLinks.contains(e.target) && !toggleButton.contains(e.target)) {
          setMenu(false);
        }
      });

      // Fecha ao pressionar Esc
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navLinks.classList.contains('active')) {
          setMenu(false);
        }
      });

      // Se a janela for redimensionada para desktop, garante que o menu fique visível (ou oculto corretamente)
      window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
          navLinks.classList.remove('active');
          toggleButton.setAttribute('aria-expanded', 'false');
          toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
          navLinks.setAttribute('aria-hidden',
            'false'); // nav sempre visível no desktop via CSS
        } else {
          navLinks.setAttribute('aria-hidden', 'true');
        }
      });

    });
  </script>
<style>

header nav ul li a {
    font-size: 24px;
}




</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
$(function() {
  var autocomplete = $("#search").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: 'produto/PHP/api-produtos.php',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          response(data);
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      window.location.href = 'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
    }
  }).data('ui-autocomplete') || $("#search").data('autocomplete');

  if (autocomplete) {
    autocomplete._renderItem = function(ul, item) {
      return $("<li>")
        .append("<div><img src='" + item.foto + "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle;  background-color: #FFD100 !important;'/>" + item.label + "</div>")
        .appendTo(ul);
    };
  }
});


</script>

<style>.ui-menu .ui-menu-item.ui-state-focus,
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