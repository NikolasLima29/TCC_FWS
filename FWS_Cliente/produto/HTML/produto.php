<?php
session_start();
include "../../conn.php";

$produtos_por_pagina = 30;
$pagina_atual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

// Se não houve termo de busca vindo pelo GET, limpar a sessão
if (!isset($_GET['q'])) {
    unset($_SESSION['busca']);
}
// Se houve termo via GET, atualiza a variável de sessão
if (isset($_GET['q'])) {
    $_SESSION['busca'] = trim($_GET['q']);
}

// Recupera o termo salvo na sessão ou vazio
$busca = isset($_SESSION['busca']) ? $_SESSION['busca'] : '';

// Monta a parte fixa da query SQL
$sql_base = "FROM produtos p 
             INNER JOIN categorias c ON p.categoria_id = c.id 
             WHERE p.status = 'ativo'";

// Adiciona condição LIKE se tiver busca
if ($busca !== '') {
    $busca_esc = mysqli_real_escape_string($conn, $busca);
    $sql_base .= " AND p.nome LIKE '%$busca_esc%'";
}

// Consulta total de produtos conforme filtro
$sql_total = "SELECT COUNT(*) as total $sql_base";
$result_total = mysqli_query($conn, $sql_total);
$total_produtos = mysqli_fetch_assoc($result_total)['total'];
$total_paginas = ceil($total_produtos / $produtos_por_pagina);

// Consulta produtos paginados conforme filtro e pagina atual
$sql = "SELECT p.id, p.nome, p.preco_venda, p.descricao, p.foto_produto, c.nome AS categoria, c.cor 
        $sql_base LIMIT $produtos_por_pagina OFFSET $offset";

$resultado = mysqli_query($conn, $sql);

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

    <link rel="stylesheet" href="../CSS/produto.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />



    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- JQuery UI css -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

</head>
<script>
    $(function () {
        var autocomplete = $("#search").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: '../../produto/PHP/api-produtos.php',
                    dataType: 'json',
                    data: { q: request.term },
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
                return $("<li>")
                    .append("<div><img src='" + item.foto + "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle;  background-color: #FFD100 !important;'/>" + item.label + "</div>")
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

<body>
    <!-- Header with same nav, style, and behavior -->
     <header id="header">
<style>
#header {
  background-color: rgba(255, 255, 255, 0.2); /* transparência correta */
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
</header>
    
        <main class="my-5">
            <div class="container">
                <!-- Barra de pesquisa -->
                <form class="d-flex mb-4" role="search" method="get" action="">
                    <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
                        aria-label="Pesquisar" value="<?php echo htmlspecialchars($busca); ?>" />
                    <button class="btn btn-warning" type="submit">Buscar</button>
                </form>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-start">
                    <?php
                    if ($total_produtos == 0 && $busca !== '') {
                        echo '<p class="text-center w-100" style="margin-top: 40px;">Nenhum produto encontrado para "' . htmlspecialchars($busca) . '". Mas veja nossos outros produtos:</p>';

                        $sql_sem_filtro = "SELECT p.id, p.nome, p.preco_venda, p.foto_produto, p.descricao, c.nome AS categoria, c.cor 
                           FROM produtos p 
                           INNER JOIN categorias c ON p.categoria_id = c.id 
                           WHERE p.status = 'ativo' 
                           LIMIT $produtos_por_pagina OFFSET $offset";

                        $resultado_sem_filtro = mysqli_query($conn, $sql_sem_filtro);

                        $sql_total_sem_filtro = "SELECT COUNT(*) as total FROM produtos WHERE status = 'ativo'";
                        $resultado_total_sem_filtro = mysqli_query($conn, $sql_total_sem_filtro);
                        $total_produtos = mysqli_fetch_assoc($resultado_total_sem_filtro)['total'];
                        $total_paginas = ceil($total_produtos / $produtos_por_pagina);

                        while ($produto = mysqli_fetch_assoc($resultado_sem_filtro)) {
                            $id = $produto["id"];
                            $nome = ucwords(strtolower(htmlspecialchars($produto["nome"])));
                            $preco = number_format($produto["preco_venda"], 2, ',', '.');
                            $foto = !empty($produto["foto_produto"]) ? htmlspecialchars($produto["foto_produto"]) : "/TCC_FWS/IMG_Produtos/sem_imagem.png";
                            $descricao = isset($produto["descricao"]) ? htmlspecialchars($produto["descricao"]) : "Produto sem descrição.";
                            $categoria = htmlspecialchars($produto["categoria"]);
                            $cor = htmlspecialchars($produto["cor"]);

                            echo '
            <div class="col">
              <div class="card h-100">
                <img src="' . $foto . '" class="card-img-top" alt="' . $nome . '">
                <div class="card-body">
                  <h6 class="card-title mb-2 fs-7" style="font-weight: normal !important;">
                    <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                      style="text-decoration: none; color: inherit;">' . $nome . '</a>
                  </h6>
                  <p class="card-text mb-2" style="font-weight: bold; color: green;">R$ ' . $preco . '</p>
                  <span class="badge" style="background-color: ' . $cor . '; color: white; padding: 6px 10px; border-radius: 12px;">' . $categoria . '</span>
                  <div class="mt-3 carrossel-buttons">
                    <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                      class="btn btn-primary btn-sm" style="margin-right:7px;">Ver mais</a>
                    <button type="button"
                      class="Carrinho btn btn-outline-success btn-sm"
                      data-produto=\'' . htmlspecialchars(json_encode([
                                    "id" => $id,
                                    "nome" => $nome,
                                    "foto" => $foto,
                                    "descricao" => $descricao,
                                    "preco" => $produto["preco_venda"]
                                ]), ENT_QUOTES, "UTF-8") . '\'>
                      Adicionar ao Carrinho <i class="bi bi-cart-plus-fill"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ';
                        }
                    } else {
                        if (mysqli_num_rows($resultado) > 0) {
                            while ($produto = mysqli_fetch_assoc($resultado)) {
                                $id = $produto["id"];
                                $nome = ucwords(strtolower(htmlspecialchars($produto["nome"])));
                                $preco = number_format($produto["preco_venda"], 2, ',', '.');
                                $foto = !empty($produto["foto_produto"]) ? htmlspecialchars($produto["foto_produto"]) : "/TCC_FWS/IMG_Produtos/sem_imagem.png";
                                $descricao = isset($produto["descricao"]) ? htmlspecialchars($produto["descricao"]) : "Produto sem descrição.";
                                $categoria = htmlspecialchars($produto["categoria"]);
                                $cor = htmlspecialchars($produto["cor"]);

                                echo '
              <div class="col">
                <div class="card h-100">
                  <img src="' . $foto . '" class="card-img-top" alt="' . $nome . '">
                  <div class="card-body">
                    <h6 class="card-title mb-2 fs-7" style="font-weight: normal !important;">
                      <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                        style="text-decoration: none; color: inherit;">' . $nome . '</a>
                    </h6>
                    <p class="card-text mb-2" style="font-weight: bold; color: green;">R$ ' . $preco . '</p>
                    <span class="badge" style="background-color: ' . $cor . '; color: white; padding: 6px 10px; border-radius: 12px;">' . $categoria . '</span>
                    <div class="mt-2 carrossel-buttons  d-flex flex-column gap-2">
                      <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                        class="btn btn-primary btn-sm" style="margin-right:7px;">Ver mais</a>
                      <button type="button"
                        class="Carrinho btn btn-outline-success btn-sm"
                        data-produto=\'' . htmlspecialchars(json_encode([
                                        "id" => $id,
                                        "nome" => $nome,
                                        "foto" => $foto,
                                        "descricao" => $descricao,
                                        "preco" => $produto["preco_venda"]
                                    ]), ENT_QUOTES, "UTF-8") . '\'>
                        Adicionar ao Carrinho <i class="bi bi-cart-plus-fill"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            ';
                            }
                        }
                    }
                    ?>
                </div>

                <?php
                $pagina_query = $busca !== '' ? '&q=' . urlencode($busca) : '';
                echo '<nav aria-label="Page navigation">';
                echo '<ul class="pagination justify-content-center mt-4">';

                if ($pagina_atual > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($pagina_atual - 1) . $pagina_query . '">Anterior</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
                }

                for ($i = 1; $i <= $total_paginas; $i++) {
                    $active = ($i == $pagina_atual) ? 'active' : '';
                    echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . $pagina_query . '">' . $i . '</a></li>';
                }

                if ($pagina_atual < $total_paginas) {
                    echo '<li class="page-item"><a class="page-link" href="?page=' . ($pagina_atual + 1) . $pagina_query . '">Próximo</a></li>';
                } else {
                    echo '<li class="page-item disabled"><span class="page-link">Próximo</span></li>';
                }

                echo '</ul></nav>';
                ?>
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
      <div style="color:#c40000;font-weight:700;font-size:1.1rem;margin-bottom:14px;text-align:center">É necessário fazer login para adicionar produtos ao carrinho
      <div class="modal-actions" style="margin-bottom:16px;">
        <a href="/TCC_FWS/FWS_Cliente/login/HTML/login.html" class="btn-login">Login</a>
        <a id="btn_modal_cadastrar"href="/TCC_FWS/FWS_Cliente/cadastro/HTML/cadastro.html" class="btn-cadastrar">Cadastrar</a>
 <button class="btn-popup btn-voltar">Voltar</button> 
      
     </div>
    
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

            .card:hover {
                transform: scale(1.05);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
                transition: transform 0.3s ease, box-shadow 0.3s ease;

            }

            .btn-primary.btn-sm {
                background: white;
                border: 3px solid #FFD100;

                color: black;
                padding: 5px 10px;
                border-radius: 4px;
                text-decoration: none;
            }

            .btn-primary.btn-sm:hover {
                background: #FFD100;
                color: black;
                border-color: black;
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