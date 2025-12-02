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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
  <link rel="stylesheet" href="index/CSS/index.css">
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
  </style>

</head>

<body>
  <!-- Cabeçalho -->
  <header id="header">
<style>

</style>
    <div class="logo">
        <a href="index.php">
            <img src="index/IMG/shell_select.png" alt="logo" />
        </a>
    </div>

    <button class="menu-toggle" aria-label="Abrir menu">
        <i class="fas fa-bars"></i>
    </button>

    <nav>
        <ul class="ul align-items-center">
            <li><a href="/TCC_FWS/FWS_Cliente/produto/HTML/produto.php">Produtos</a></li>
            <li>
                <form class="d-flex" role="search" action="/TCC_FWS/FWS_Cliente/produto/HTML/produto.php" method="get" style="margin: 0 10px;">
                    <input id="search" class="form-control form-control-sm me-2" type="search" name="q" placeholder="Pesquisar..." aria-label="Pesquisar">
                    <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </li>
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

            <div id="user-menu" style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 0; min-width: 120px; z-index: 1000;">
                <a href="/TCC_FWS/FWS_Cliente/info_usuario/HTML/info_usuario.php" style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
                <a href="/TCC_FWS/FWS_Cliente/logout.php" id="logout-link" style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Sair</a>
            </div>

            <script>
                document.getElementById('user-menu-toggle').addEventListener('click', function() {
                    var menu = document.getElementById('user-menu');
                    if (menu.style.display === 'none') {
                        menu.style.display = 'block';
                    } else {
                        menu.style.display = 'none';
                    }
                });

                // Fecha o menu se clicar fora
                document.addEventListener('click', function(event) {
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

<section>
  <div id="carrossel-custom" style="max-width:480px; margin:0 auto; padding:24px 0;">
    <div class="carrossel-slide" style="position: relative; height: 220px; min-height: 140px;">
      <button id="btnPrev" style="position:absolute; left:0; top:50%; transform: translateY(-50%); z-index:10; font-size: 1.5rem; background: transparent; border: none; cursor: pointer;">&#9664;</button>
      <div style="overflow:hidden; width: 100%;">
        <img class="carrossel-img left" src="index/IMG/desconto_vitor.jpeg" alt="Promo 1" />
        <img class="carrossel-img center" src="index/IMG/promo_carrinho.png" alt="Promo 2" />
        <img class="carrossel-img right" src="index/IMG/F1.png" alt="Promo 3" />
      </div>
      <button id="btnNext" style="position:absolute; right:0; top:50%; transform: translateY(-50%); z-index:10; font-size: 1.5rem; background: transparent; border: none; cursor: pointer;">&#9654;</button>
    </div>
  </div>
</section>

<style>
#carrossel-custom {
  width: 90vw; max-width: 480px;
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
  max-height: 170px; /* 10px menor */
  border-radius: 14px;
  object-fit: contain;
  box-shadow: 0 4px 18px rgba(0,0,0,0.10);
  opacity: 0;
  z-index: 1;
  filter: blur(1.5px) grayscale(0.3) brightness(0.9); /* menos borrado */
  pointer-events: none;
  transform: translate(-50%, -50%);
  transition: opacity 1s ease, filter 0.7s ease, transform 0.7s ease, box-shadow 0.7s ease;
}
.carrossel-img.center {
  opacity: 1;
  z-index: 3;
  filter: none;
  pointer-events: auto;
  box-shadow: 0 10px 42px rgba(255,220,30,0.22);
  transform: translate(-50%, -50%) scale(1.08);
  cursor: pointer;
}
.carrossel-img.center:hover {
  transform: translate(-50%, -50%) scale(1.18);
  box-shadow: 0 12px 52px rgba(255,220,30,0.35);
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
    (idx + total -1) % total, // esquerda
    idx,                      // central
    (idx + 1) % total         // direita
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
  <a href="/TCC_FWS/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>">
    <img src="<?= htmlspecialchars($produto['foto_produto']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($produto['nome']) ?>">
  </a>
  <div class="carousel-caption d-block">
    <a href="/TCC_FWS/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>" style="color:#c40000; text-decoration:none;">
      <h5 id="nome"><?= htmlspecialchars($produto['nome']) ?></h5>
    </a>
    <p id="descricao"><?= htmlspecialchars($produto['descricao']) ?></p>
    <p><strong>R$ <?= number_format($produto['preco_venda'],2,',','.') ?></strong></p>
    <div class="carrossel-buttons">
      <a class="ver" href="/TCC_FWS/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=<?= $produto['id'] ?>" style="margin-right:7px;">Ver Mais</a>
      <button type="button" 
  class="Carrinho btn btn-outline-success btn-sm"
  data-produto='<?= htmlspecialchars(json_encode([
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
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
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


<script>
$(function() {
  var usuario_id = <?php echo isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 'null'; ?>;

  $(".Carrinho").on("click", function () {
    const dados = JSON.parse($(this).attr('data-produto'));
    if (!usuario_id) {
      showLoginModal();
      return;
    }
    let qtd = 1;
    // Pegue o preço do produto aqui, ex: dados.preco
    const preco = parseFloat(dados.preco); // o atributo .preco deve vir no seu data-produto

    function atualizarPreco() {
      $("#valor-unit").text(preco.toLocaleString("pt-BR", {style:"currency",currency:"BRL"}));
      $("#valor-total").text((preco*qtd).toLocaleString("pt-BR", {style:"currency",currency:"BRL"}));
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

    $(".custom-modal .contador-btn.menos").on("click", function () {
      if (qtd > 1) { qtd--; atualizarPreco(); }
    });
    $(".custom-modal .contador-btn.mais").on("click", function () {
      if (qtd < 10) { qtd++; atualizarPreco(); }
    });

    $(".custom-modal .btn-popup.cancel").on("click", function () {
      $("#modal-add-carrinho, #modal-backdrop").hide();
    });

    // Adicionar ao carrinho AJAX
    $(".custom-modal .btn-popup.add").on("click", function () {
      $.post('/TCC_FWS/FWS_Cliente/carrinho/PHP/adicionar_ao_carrinho.php', {
        id_produto: dados.id,
        quantidade: qtd,
        ajax: 1
      }, function(resp) {
        $("#modal-add-carrinho").html(`<div style="color:#090;font-weight:600;font-size:1.08rem;margin-bottom:10px;">✔️ ${dados.nome} foi adicionado ao seu carrinho!</div>
          <img src="${dados.foto}" style="max-width:110px;margin-bottom:8px;">
          <div>Quantidade: ${qtd}</div>
          <div>Total: <b>${(preco*qtd).toLocaleString("pt-BR",{style:"currency",currency:"BRL"})}</b></div>
          <div class="modal-actions"><button class="btn-popup add ok-close">Fechar</button></div>
        `);
        $(".ok-close").on("click", function(){ $("#modal-add-carrinho, #modal-backdrop").hide(); });
      });
    });
  });

  function showLoginModal() {
    $("#modal-login-alert").html(`
      <div style="color:#c40000;font-weight:700;font-size:1.1rem;margin-bottom:14px;text-align:center">É necessário fazer login para adicionar produtos ao carrinho</div>
      <div class="modal-actions" style="margin-bottom:16px;">
        <a href="/TCC_FWS/FWS_Cliente/login/HTML/login.html" class="btn-login">Login</a>
        <a id="btn_modal_cadastrar"href="/TCC_FWS/FWS_Cliente/cadastro/HTML/cadastro.html" class="btn-cadastrar">Cadastrar</a>
 <button class="btn-popup btn-voltar">Voltar</button>    
    
    `).show();
    $("#modal-backdrop").show();
    $(".btn-voltar").on("click", function(){ $("#modal-login-alert, #modal-backdrop").hide(); });
  }

  $("#modal-backdrop").on("click",function(){
    $(".custom-modal").hide(); $(this).hide();
  });
});
</script>

<div id="modal-backdrop" class="custom-backdrop" style="display:none"></div>
<div id="modal-add-carrinho" class="custom-modal" style="display:none"></div>
<div id="modal-login-alert" class="custom-modal" style="display:none"></div>


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

body {
    background: #fff; /* Garantir fundo branco total */
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
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
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

.carousel-caption a#nome, .carousel-caption h5#nome {
    color: #c40000;
    text-decoration: none;
    transition: color 0.2s;
}
.carousel-caption a#nome:hover, .carousel-caption h5#nome:hover {
    color: #3a0000;
    text-decoration: underline;
}

.ver {
  background: white;
  border: 3px solid #FFD100;
  color: black;
  padding: 5px 10px;
  border-radius: 4px;
  text-decoration: none;
}

.ver:hover {
   background: #FFD100;
  color: black;
    border-color: black;
}


.Carrinho {
  background: white;
  border: 3px solid #23d44cff;
  color: black;
  padding: 5px 10px;
  border-radius: 4px;
  text-decoration: none;
}

.Carrinho:hover {
  background: #23d44cff;
  color: black;
  border-color: black !important;
}

.carousel-control-prev,
.carousel-control-next {
  width: 50px;
  height: 50px;

  top: 25%;                       /* um pouco mais pra cima */
  opacity: 0.9;                   /* leve transparência */
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
  transform: scale(1.1);          /* efeito de leve aumento no hover */
      /* tom mais escuro ao passar o mouse */
}

.carousel-control-prev {
  left: -10%;                       /* um pouco mais pra dentro da esquerda */
}

.carousel-control-next {
  right: -10%;                      /* um pouco mais pra dentro da direita */
}

/* Remove o ícone padrão e deixa o conteúdo visível se quiser personalizar depois */
.carousel-control-prev-icon,
.carousel-control-next-icon {
  filter: invert(100%);           /* deixa o ícone branco pra contraste */
}

.custom-backdrop {
  position: fixed; top:0; left:0; right:0; bottom:0;
  background: rgba(0,0,0,0.55);
  z-index: 2000;
}
.custom-modal {
  position: fixed; left: 50%; top: 50%;
  transform: translate(-50%, -50%);
  min-width: 340px;
  max-width:90vw;
  background: #fff;
  border-radius: 16px;
  padding: 28px 32px 22px 32px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.25);
  z-index: 2100;
  display: flex; flex-direction:column; align-items:center;
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
  gap:8px;
  align-items: center;
  margin:18px 0;
}
.custom-modal .contador-btn {
  border: none;
  background: #f4f4f4;
  border-radius: 5px;
  font-size: 1.29rem;
  width: 36px; height:36px;
  cursor:pointer;
  color:#c40000;
  font-weight: bold;
  transition: background 0.18s;
}
.custom-modal .contador-btn:active {
  background: #ffe5e5;
}
.custom-modal .quantidade-number {
  font-size: 1.18rem;
  font-weight: 600;
  width:38px; text-align:center;
}
.custom-modal .modal-actions {
  margin-top:20px;
  display: flex; gap: 14px;
  width:100%; justify-content: center;
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
  color:#fff;
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
  color:#fff;
  text-decoration:none;
  padding: 7px 17px;
  border-radius:6px;
  font-weight:500;
  display:inline-block;
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
.btn-login { background: #c40000; color: #000000ff !; }
.btn-cadastrar { background: #FFD100; color: #000000ff; }
.btn-voltar { background: #999; color: #000; }
</style>

</body>

</html>