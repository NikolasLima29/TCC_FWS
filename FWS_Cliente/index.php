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

  <style>
 #header {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  background-color: #c40000; /* vermelho */
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
  font-size: 18px;
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

    <!-- Menu -->
    <nav>
      <ul class="ul">
        <li><a href="produto/HTML/produto.html">Produtos</a></li>
        <li><a href="tela_sobre_nos/HTML/sobre_nos.html">Sobre nós</a></li>
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
          echo "Bem-vindo, " . htmlspecialchars($_SESSION['usuario_nome']);
      } else {
          echo "Bem-vindo.";
      }
      ?>
    </div>
  </header>

  <!-- Primeiro corpo -->
   
  <section class="section">
    <div class="botoes">
    <?php if(!isset($_SESSION['logado']) || $_SESSION['logado'] !== true):?>
      <a href="cadastro/HTML/cadastro.html" class="btn">Cadastre-se</a>
      <a href="login/HTML/login.html" class="btn">Entrar</a>
      <?php endif;?>
    </div>
    <div class="div-jd-america">
      <img class="jd-america" src="index/IMG/jd_america.png">
    </div></section>

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

  <footer>
    <!-- place footer here -->
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
    integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
  </script>
</body>

</html>
