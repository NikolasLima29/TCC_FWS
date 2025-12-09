<?php
session_start();

include "conn.php";

// 1. Buscar os produtos mais vendidos ativos (quantidade total vendida)
$sql_mais_vendidos = "
    SELECT p.id, p.nome, p.descricao, p.foto_produto, p.preco_venda,
            SUM(iv.quantidade) AS total_vendido
    FROM itens_vendidos iv
    INNER JOIN produtos p ON iv.produto_id = p.id
    WHERE p.status = 'ativo'
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 10
";
$result_mais_vendidos = mysqli_query($conn, $sql_mais_vendidos);

// 2. Se não houver produtos mais vendidos, buscar produtos com MENOR estoque
if (mysqli_num_rows($result_mais_vendidos) == 0) {
    // IDs de categorias proibidas: BEBIDAS ALCOÓLICAS=1, CIGARROS E ITENS DE FUMO=9, OUTROS=11 (ajuste conforme seu BD)
    $ids_excluir = [1, 9, 11];
    $ids_excluir_str = implode(',', $ids_excluir);

    // Agora o ORDER BY é ASC, para pegar os produtos com menos estoque
    $sql_estoque = "
        SELECT p.id, p.nome, p.descricao, p.foto_produto, p.preco_venda, p.estoque
        FROM produtos p
        WHERE p.status = 'ativo'
          AND p.categoria_id NOT IN ($ids_excluir_str)
          AND p.estoque > 0
        ORDER BY p.estoque ASC
        LIMIT 10
    ";
    $result_estoque = mysqli_query($conn, $sql_estoque);
} else {
    $result_estoque = false; // sem fallback
}

// 3. Montar lista final para o carrossel
$produtos_carrossel = [];

if ($result_mais_vendidos && mysqli_num_rows($result_mais_vendidos) > 0) {
    while ($row = mysqli_fetch_assoc($result_mais_vendidos)) {
        $produtos_carrossel[] = $row;
    }
} elseif ($result_estoque && mysqli_num_rows($result_estoque) > 0) {
    while ($row = mysqli_fetch_assoc($result_estoque)) {
        $produtos_carrossel[] = $row;
    }
}

// Agora $produtos_carrossel tem até 10 produtos para exibir no carrossel
?>


<!doctype html>
<html lang="pt-BR">

<head>
  <title>Home</title>
  <link rel="icon" type="image/x-icon" href="cadastro/IMG/Shell.png">
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <!-- Bootstrap CSS v5.3.2 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="index/CSS/index.css" />
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
    @import url('Fonte_Config/fonte_geral.css');

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
  </style>
</head>

<body>
  <!-- ========== INÍCIO DO HEADER ========== -->
  <header>
    <!-- ========== NAVBAR PRINCIPAL ========== -->
    <nav class="navbar navbar-expand-sm navbar-light" style="background-color: #c40000;">
      <!-- ========== LOGO ========== -->
      <a class="navbar-brand ms-3" href="index.php">
        <img src="index/IMG/shell_select.png" alt="Logo" class="logo-shell">
      </a>

      <!-- ========== SEÇÃO MOBILE (BOTÃO TOGGLE + CARRINHO + PERFIL) ========== -->
      <div class="d-flex align-items-center ms-3">
        <!-- Botão do menu hambúrguer -->
        <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavId"
          aria-controls="collapsibleNavId" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"><span></span></span>
        </button>

        <!-- Ícones de Carrinho e Perfil (apenas mobile) -->
        <div class="d-flex align-items-center d-sm-none">
          <!-- Carrinho Mobile -->
          <a href="carrinho/HTML/carrinho.php" class="me-2" style="margin-left: 2px;">
            <img src="carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
              style="object-fit: contain; filter: brightness(0) invert(1);">
          </a>

          <!-- Perfil Mobile (com validação de login) -->
          <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? 'info_usuario/HTML/info_usuario.php' : 'login/HTML/login.html'; ?>"
            class="me-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-person-circle"
              viewBox="0 0 16 16">
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
          <form class="d-flex" role="search" action="produto/HTML/produto.php" method="get" style="margin: 0;">
            <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
              style="width: 300px;">
            <button class="btn btn-outline-light" type="submit"
              style="background-color: #FFD100; border-color: #FFD100;">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="bi bi-search" viewBox="0 0 16 16">
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
              <a class="nav-link" href="index.php">
                <h5 class="m-0 text-white menu-bold">Home</h5>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="produto/HTML/produto.php">
                <h5 class="m-0 text-white menu-bold">Produtos</h5>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="meus_pedidos/HTML/Meus_pedidos.php">
                <h5 class="m-0 text-white menu-bold">Meus Pedidos</h5>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="tela_sobre_nos/HTML/sobre_nos.php">
                <h5 class="m-0 text-white menu-bold">Sobre Nós</h5>
              </a>
            </li>
          </ul>

          <!-- ========== SEÇÃO DESKTOP (CARRINHO + BEM-VINDO + PERFIL) ========== -->
          <div class="d-flex align-items-center ms-auto me-4">
            <!-- Carrinho Desktop -->
            <a href="carrinho/HTML/carrinho.php" style="margin-left: -70px;">
              <img src="carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30" class="me-4"
                style="object-fit: contain; filter: brightness(0) invert(1);">
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
              href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? 'info_usuario/HTML/info_usuario.php' : 'login/HTML/login.html'; ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white" class="bi bi-person-circle"
                viewBox="0 0 16 16">
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
      <form class="d-flex" role="search" action="produto/HTML/produto.php" method="get">
        <input id="search-mobile" class="form-control me-2" type="search" name="q" placeholder="Pesquisar...">
        <button class="btn btn-outline-light" type="submit" style="background-color: #FFD100; border-color: #FFD100;">
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

  <!-- Bootstrap JS and Popper for 5.2.1 -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
  </script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

  <script>
    $(function () {
      var autocomplete = $("#search").autocomplete({
        source: function (request, response) {
          $.ajax({
            url: 'produto/PHP/api-produtos.php',
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
            url: 'produto/PHP/api-produtos.php',
            dataType: 'json',
            data: {
              q: request.term
            },
            success: function (data) {
              response(data);
            }
          });
        },
        minLength: 1,
        select: function (event, ui) {
          window.location.href =
            'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
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
      const toggleButton = document.querySelector('.navbar-toggler');
      const navLinks = document.querySelector('.navbar-collapse');

      if (!toggleButton || !navLinks) return;

      toggleButton.addEventListener('click', () => {
        // Bootstrap cuida de abrir e fechar
      });

      document.addEventListener('click', (e) => {
        if (!navLinks.contains(e.target) && !toggleButton.contains(e.target)) {
          // Fechar se clicar fora
          const bsCollapse = new bootstrap.Collapse(navLinks, {
            toggle: false
          });
          bsCollapse.hide();
        }
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          const bsCollapse = new bootstrap.Collapse(navLinks, {
            toggle: false
          });
          bsCollapse.hide();
        }
      });
    });
  </script>

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

  <section>
    <div id="carrossel-custom" style="max-width:480px; margin:0 auto; padding:24px 0;">
      <div class="carrossel-slide" style="position: relative; height: 220px; min-height: 140px;">
        <button id="btnPrev"
          style="position:absolute; left:0; top:50%; transform: translateY(-50%); z-index:10; font-size: 1.5rem; background: transparent; border: none; cursor: pointer;">&#9664;</button>
        <div style="overflow:hidden; width: 100%;">
          <img class="carrossel-img left" src="index/IMG/desconto_vitor.jpeg" alt="Promo 1" />
          <img class="carrossel-img center" src="index/IMG/promo_carrinho.png" alt="Promo 2" />
          <img class="carrossel-img right" src="index/IMG/F1.png" alt="Promo 3" />
        </div>
        <button id="btnNext"
          style="position:absolute; right:0; top:50%; transform: translateY(-50%); z-index:10; font-size: 1.5rem; background: transparent; border: none; cursor: pointer;">&#9654;</button>
      </div>
    </div>
  </section>

  <style>
    #carrossel-custom {
      width: 90vw;
      max-width: 480px;
    }

    .carrossel-slide {
      width: 100%;
      position: relative;
      height: 220px;
      min-height: 140px;
      margin: 0 auto;
      user-select: none;
    }

    .carrossel-img {
      position: absolute;
      top: 50%;
      left: 50%;
      width: auto;
      max-height: 170px;
      /* 10px menor */
      border-radius: 14px;
      object-fit: contain;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.10);
      opacity: 0;
      z-index: 1;
      filter: blur(1.5px) grayscale(0.3) brightness(0.9);
      /* menos borrado */
      pointer-events: none;
      transform: translate(-50%, -50%);
      transition: opacity 1s ease, filter 0.7s ease, transform 0.7s ease, box-shadow 0.7s ease;
    }

    .carrossel-img.center {
      opacity: 1;
      z-index: 3;
      filter: none;
      pointer-events: auto;
      box-shadow: 0 10px 42px rgba(255, 220, 30, 0.22);
      transform: translate(-50%, -50%) scale(1.08);
      cursor: pointer;
    }

    .carrossel-img.center:hover {
      transform: translate(-50%, -50%) scale(1.18);
      box-shadow: 0 12px 52px rgba(255, 220, 30, 0.35);
    }

    .carrossel-img.left {
      opacity: 1;
      z-index: 2;
      transform: translate(calc(-50% - 120%), -50%) scale(0.95) rotate(-8deg);
    }

    .carrossel-img.right {
      opacity: 1;
      z-index: 2;
      transform: translate(calc(-50% + 120%), -50%) scale(0.95) rotate(8deg);
    }
  </style>

  <script>
    const imgs = [
      "index/IMG/desconto_vitor.jpeg",
      "index/IMG/promo_carrinho.png",
      "index/IMG/F1.png",
      "index/IMG/Promo_coxinha.png"
    ];
    let idx = 0;
    const imgTags = document.querySelectorAll('.carrossel-img');
    let timeout;

    function showCarrossel() {
      const total = imgs.length;
      let pos = [
        (idx + total - 1) % total, // esquerda
        idx, // central
        (idx + 1) % total // direita
      ];

      imgTags.forEach(img => img.className = "carrossel-img");

      imgTags[0].src = imgs[pos[0]];
      imgTags[0].classList.add("left");

      imgTags[1].src = imgs[pos[1]];
      imgTags[1].classList.add("center");

      imgTags[2].src = imgs[pos[2]];
      imgTags[2].classList.add("right");
    }

    function proximo() {
      idx = (idx + 1) % imgs.length;
      showCarrossel();
    }

    function anterior() {
      idx = (idx + imgs.length - 1) % imgs.length;
      showCarrossel();
    }

    function startAuto() {
      timeout = setInterval(proximo, 5000);
    }

    function pauseAuto() {
      clearInterval(timeout);
    }

    showCarrossel();
    startAuto();

    const carrossel = document.querySelector('.carrossel-slide');
    carrossel.addEventListener('mouseenter', pauseAuto);
    carrossel.addEventListener('mouseleave', startAuto);


    // Botões next e prev
    document.getElementById('btnNext').addEventListener('click', () => {
      proximo();
      pauseAuto();
      startAuto();
    });
    document.getElementById('btnPrev').addEventListener('click', () => {
      anterior();
      pauseAuto();
      startAuto();
    });
  </script>



  <!-- Segundo cabeçalho -->
  <section class="mais-vendidos">
    <h1>Mais Vendidos <img src="index/IMG/sacola.png" alt="sacola"></h1>
  </section>

  <!-- Carrossel -->
  <section class="carrossel">
    <div id="carouselExampleCaptions" class="carousel slide">
      <div class="carousel-inner">
        <?php foreach($produtos_carrossel as $index => $produto): ?>
        <div class="carousel-item <?= $index===0 ? 'active' : '' ?>">
          <a href="/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>">
            <img src="<?= htmlspecialchars($produto['foto_produto']) ?>" class="d-block w-100"
              alt="<?= htmlspecialchars($produto['nome']) ?>">
          </a>
          <div class="carousel-caption d-block">
            <a href="/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>"
              style="color:#c40000; text-decoration:none;">
              <h5 id="nome"><?= htmlspecialchars($produto['nome']) ?></h5>
            </a>
            <p id="descricao"><?= htmlspecialchars($produto['descricao']) ?></p>
            <p><strong>R$ <?= number_format($produto['preco_venda'],2,',','.') ?></strong></p>
            <div class="carrossel-buttons">
              <a class="ver"
                href="/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>"
                style="margin-right:7px;">Ver Mais</a>
              <button type="button" class="Carrinho btn btn-outline-success btn-sm" data-produto='<?= htmlspecialchars(json_encode([
      "id"=>$produto["id"],
      "nome"=>$produto["nome"],
      "foto"=>$produto["foto_produto"],
      "descricao"=>$produto["descricao"],
      "preco"=>$produto["preco_venda"]
  ]), ENT_QUOTES, "UTF-8") ?>'>
                Adicionar ao Carrinho <i class="bi bi-cart-plus-fill"></i>
              </button>

            </div>
          </div>
        </div>

        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
        data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
        data-bs-slide="next">
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
    integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
  </script>

  <script>
    $(function () {
      var usuario_id = < ? php echo isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 'null'; ? > ;


      $(".Carrinho").on("click", function () {
        const dados = JSON.parse($(this).attr('data-produto'));
        if (!usuario_id) {
          showLoginModal();
          return;
        }
        // Checar estoque antes de abrir modal (AJAX correto)
        $.post('/fws/FWS_Cliente/carrinho/PHP/adicionar_ao_carrinho.php', {
          id_produto: dados.id,
          verificar_limite: 1
        }, function (resp) {
          let data;
          try {
            data = typeof resp === 'string' ? JSON.parse(resp) : resp;
          } catch (e) {
            data = {};
          }
          if (typeof data.restante !== 'undefined' && data.restante > 0) {
            abrirPopupAdicionar(dados, data.restante);
          } else {
            mostrarAvisoLimite(dados.nome);
          }
        });
      });

      // Função igual à tela de produtos
      function mostrarAvisoLimite(nomeProd) {
        $("#modal-add-carrinho").html(`
          <div style="color:#b30000;font-weight:700;font-size:1.15rem;margin-bottom:14px;text-align:center">
              Limite atingido
          </div>
          <p style="text-align:center;margin-bottom:10px">
              Você já adicionou o máximo permitido pelo estoque para <b>${nomeProd}</b>.
          </p>
          <div class="modal-actions">
              <button class="btn-popup cancel ok-close">Fechar</button>
          </div>
        `).show();
        $("#modal-backdrop").show();
        $(".ok-close").on("click", function () {
          $("#modal-add-carrinho, #modal-backdrop").hide();
        });
      }

      function abrirPopupAdicionar(dados, maxPermitido) {
        let qtd = 1;
        const preco = parseFloat(dados.preco);

        function atualizarPreco() {
          $("#valor-unit").text(preco.toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
          }));
          $("#valor-total").text((preco * qtd).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
          }));
          $(".quantidade-number").text(qtd);
        }
        $("#modal-backdrop").show();
        $("#modal-add-carrinho").html(`
          <div style="margin-bottom:10px">Você está adicionando ao carrinho:</div>
          <img src="${dados.foto}" alt="${dados.nome}">
          <div class="produto-titulo">${dados.nome}</div>
          <div class="produto-descricao">${dados.descricao}</div>
          <div style="margin:7px 0 3px 0"><b>Preço unitário: <span id="valor-unit"></span></b></div>
          <div class="contador-box">
            <button class="contador-btn menos">-</button>
            <span class="quantidade-number">1</span>
            <button class="contador-btn mais">+</button>
          </div>
          <div style="margin:6px 0;"><b>Total: <span id="valor-total"></span></b></div>
          <div class="modal-actions">
            <button class="btn-popup add">Adicionar</button>
            <button class="btn-popup cancel">Cancelar</button>
          </div>
        `).show();
        atualizarPreco();
        $(".contador-btn.menos").on("click", function () {
          if (qtd > 1) {
            qtd--;
            atualizarPreco();
          }
        });
        $(".contador-btn.mais").on("click", function () {
          if (qtd < maxPermitido) {
            qtd++;
            atualizarPreco();
          }
        });
        $(".btn-popup.cancel").on("click", function () {
          $("#modal-add-carrinho, #modal-backdrop").hide();
        });
        $(".btn-popup.add").on("click", function () {
          $.post('/fws/FWS_Cliente/carrinho/PHP/adicionar_ao_carrinho.php', {
            id_produto: dados.id,
            quantidade: qtd,
            ajax: 1
          }, function (resp) {
            $("#modal-add-carrinho").html(`
                <div style="color:#090;font-weight:600;font-size:1.08rem;margin-bottom:10px;">
                    ✔️ ${dados.nome} foi adicionado ao seu carrinho!
                </div>
                <img src="${dados.foto}" style="max-width:110px;margin-bottom:8px;">
                <div>Quantidade: ${qtd}</div>
                <div>Total: <b>${(preco * qtd).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })}</b></div>
                <div class="modal-actions"><button class="btn-popup add ok-close">Fechar</button></div>
            `);
            $(".ok-close").on("click", function () {
              $("#modal-add-carrinho, #modal-backdrop").hide();
            });
          });
        });
      }

      function showLoginModal() {
        $("#modal-login-alert").html(`
      <div style="color:#c40000;font-weight:700;font-size:1.1rem;margin-bottom:14px;text-align:center">É necessário fazer login para adicionar produtos ao carrinho</div>
      <div class="modal-actions" style="margin-bottom:16px;">
        <a href="/fws/FWS_Cliente/login/HTML/login.html" class="btn-login">Login</a>
        <a id="btn_modal_cadastrar"href="/fws/FWS_Cliente/cadastro/HTML/cadastro.html" class="btn-cadastrar">Cadastrar</a>
 <button class="btn-popup btn-voltar">Voltar</button>    
    
    `).show();
        $("#modal-backdrop").show();
        $(".btn-voltar").on("click", function () {
          $("#modal-login-alert, #modal-backdrop").hide();
        });
      }

      $("#modal-backdrop").on("click", function () {
        $(".custom-modal").hide();
        $(this).hide();
      });
    });
  </script>

  <div id="modal-backdrop" class="custom-backdrop" style="display:none"></div>
  <div id="modal-add-carrinho" class="custom-modal" style="display:none"></div>
  <div id="modal-login-alert" class="custom-modal" style="display:none"></div>


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

    body {
      background: #fff;
      /* Garantir fundo branco total */
    }

    /* Container do carrossel */
    section.carrossel {
      background: #fff;
      border-radius: 1.5rem;
      box-shadow: 0 8px 25px rgba(196, 0, 0, 0.09);
      padding: 2rem;
      max-width: 1200px;
      margin: 3rem auto;
    }

    /* Imagem arredondada, limpa, centralizada */
    .carousel-item img {
      max-height: 350px;
      object-fit: contain;
      width: auto;
      margin: 0 auto;
      border-radius: 1rem;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s;
    }

    .carousel-item img:hover {
      transform: scale(1.04);
    }

    /* Legenda fora da imagem, bonita e centralizada */
    .carousel-caption {
      position: static !important;
      background: none !important;
      margin-top: 14px;
      padding: 0;
      text-align: center;
      color: #111 !important;
    }

    .carousel-caption h5 {
      color: #c40000;
      font-weight: 700;
      font-size: 1.6rem;
      margin-bottom: .3rem;
    }

    .carousel-caption p#descricao {
      font-size: 1.03rem;
      color: #444;
      margin-bottom: 0.5rem;
      margin-top: 0;
    }

    .carousel-caption p strong {
      color: #008000;
      font-size: 1.18rem;
    }

    /* Botões de navegação elegantes e suaves */

    /* Responsividade simples */
    @media (max-width: 600px) {
      section.carrossel {
        padding: 0.5rem;
        border-radius: .65rem;
      }

      .carousel-item img {
        max-height: 180px;
        border-radius: 8px;
      }

      .carousel-caption h5 {
        font-size: 1.12rem;
      }
    }

    .carousel-caption .carrossel-buttons {
      margin-top: 17px;
      display: flex;
      justify-content: center;
      gap: 10px;
    }

    .carousel-caption .btn {
      font-weight: 500;
    }

    .carousel-caption a#nome,
    .carousel-caption h5#nome {
      color: #c40000;
      text-decoration: none;
      transition: color 0.2s;
    }

    .carousel-caption a#nome:hover,
    .carousel-caption h5#nome:hover {
      color: #3a0000;
      text-decoration: underline;
    }

    .ver {
      background: #FFD100;
      border: 3px solid #FFD100;
      color: black;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
    }

    .ver:hover {
      background: white;
      color: black;
      border-color: #FFD100;
    }


    .Carrinho {
      background: white;
      border: 2px solid #00BC5B;
      color: #00BC5B;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
    }

    .Carrinho:hover {
      background: #00BC5B;
      color: white;
      border-color: #00BC5B !important;
    }

    .carousel-control-prev,
    .carousel-control-next {
      width: 50px;
      height: 50px;

      top: 25%;
      /* um pouco mais pra cima */
      opacity: 0.9;
      /* leve transparência */
      transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
      transform: scale(1.1);
      /* efeito de leve aumento no hover */
      /* tom mais escuro ao passar o mouse */
    }

    .carousel-control-prev {
      left: -10%;
      /* um pouco mais pra dentro da esquerda */
    }

    .carousel-control-next {
      right: -10%;
      /* um pouco mais pra dentro da direita */
    }

    /* Remove o ícone padrão e deixa o conteúdo visível se quiser personalizar depois */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
      filter: invert(100%);
      /* deixa o ícone branco pra contraste */
    }

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

    .custom-modal img {
      max-width: 400px;

      margin-bottom: 10px;
      border-radius: 8px;
    }

    .custom-modal .produto-titulo {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 10px;
      color: #c40000;
    }

    .custom-modal .produto-descricao {
      font-size: 0.97rem;
      color: #444;
    }

    .custom-modal .contador-box {
      display: flex;
      gap: 8px;
      align-items: center;
      margin: 18px 0;
    }

    .custom-modal .contador-btn {
      border: none;
      background: #f4f4f4;
      border-radius: 5px;
      font-size: 1.29rem;
      width: 36px;
      height: 36px;
      cursor: pointer;
      color: #c40000;
      font-weight: bold;
      transition: background 0.18s;
    }

    .custom-modal .contador-btn:active {
      background: #ffe5e5;
    }

    .custom-modal .quantidade-number {
      font-size: 1.18rem;
      font-weight: 600;
      width: 38px;
      text-align: center;
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
      transition: background 0.18s;
    }

    .custom-modal .btn-popup.add {
      background: #009900;
      color: #fff;
    }

    .custom-modal .btn-popup.cancel {
      background: #c40000;
      color: #fff;
    }

    .custom-modal .btn-popup.add:active {
      background: #42bb42;
    }

    .custom-modal .btn-popup.cancel:active {
      background: #9d0909;
    }

    /* Modal login */
    .custom-modal .modal-actions a {
      color: #fff;
      text-decoration: none;
      padding: 7px 17px;
      border-radius: 6px;
      font-weight: 500;
      display: inline-block;
    }

    .modal-actions a,
    .modal-actions button {
      display: inline-block;
      width: 120px;

      text-align: center;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      color: #000;
      text-decoration: none;
      cursor: pointer;
      margin: 0 6px;
    }

    /* cores específicas */
    .btn-login {
      background: #c40000;
      color: #000000ff !;
    }

    .btn-cadastrar {
      background: #FFD100;
      color: #000000ff;
    }

    .btn-voltar {
      background: #999;
      color: #000;
    }
  </style>

  <style>
    @media (max-width: 768px) {

      .carousel-item img {
        max-height: 180px;
      }

      .carousel-caption h5 {
        font-size: 1.2rem;
      }

      .carousel-caption p {
        font-size: .95rem;
      }

      .carousel-caption .carrossel-buttons {
        flex-direction: column;
      }
    }
  </style>

</body>

</html>