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

// Recupera filtros de preço e categorias do GET
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 1;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000;
$categorias = isset($_GET['categorias']) && is_array($_GET['categorias']) ? array_map('intval', $_GET['categorias']) : [];

// Pega todas categorias para marcar por padrão
$res_cats = mysqli_query($conn, "SELECT id FROM categorias");
$all_cat_ids = [];
while ($cat = mysqli_fetch_assoc($res_cats)) {
    $all_cat_ids[] = $cat['id'];
}
// Se nada foi selecionado, marcar todas
if (empty($categorias)) {
    $categorias = $all_cat_ids;
}

// Monta a parte fixa da query SQL
$sql_base = "FROM produtos p
             INNER JOIN categorias c ON p.categoria_id = c.id
             WHERE p.status = 'ativo'
             AND p.estoque >= 2"; // mínimo 2 para aparecer

// Adiciona condição LIKE se tiver busca
if ($busca !== '') {
    $busca_esc = mysqli_real_escape_string($conn, $busca);
    $sql_base .= " AND p.nome LIKE '%$busca_esc%'";
}

// Filtra preço mínimo
$sql_base .= " AND p.preco_venda >= $min_price";

// Filtra preço máximo
$sql_base .= " AND p.preco_venda <= $max_price";

// Filtra categorias
if (!empty($categorias)) {
    $sql_base .= " AND p.categoria_id IN (" . implode(',', $categorias) . ")";
}

// Adiciona ordenação
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : '';
$order_by = "ORDER BY p.nome ASC";

switch ($ordenar) {
    case 'nome_asc':
        $order_by = "ORDER BY p.nome COLLATE utf8mb4_general_ci ASC";
        break;
    case 'nome_desc':
        $order_by = "ORDER BY p.nome COLLATE utf8mb4_general_ci DESC";
        break;
    case 'preco_asc':
        $order_by = "ORDER BY p.preco_venda ASC";
        break;
    case 'preco_desc':
        $order_by = "ORDER BY p.preco_venda DESC";
        break;
    case 'mais_vendidos':
        // Assumindo que existe um campo de vendas ou quantidade vendida
        $order_by = "ORDER BY p.id DESC"; // Pode ajustar se houver campo de vendas
        break;
    default:
        $order_by = "ORDER BY p.nome COLLATE utf8mb4_general_ci ASC";
}

// Consulta total de produtos conforme filtro
$sql_total = "SELECT COUNT(*) as total $sql_base";
$result_total = mysqli_query($conn, $sql_total);
$total_produtos = mysqli_fetch_assoc($result_total)['total'];
$total_paginas = ceil($total_produtos / $produtos_por_pagina);

// Consulta produtos paginados conforme filtro e pagina atual
$sql = "SELECT p.id, p.nome, p.preco_venda, p.descricao, p.foto_produto, p.estoque,
                c.nome AS categoria, c.cor
        $sql_base $order_by LIMIT $produtos_por_pagina OFFSET $offset";

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
          <form class="d-flex" role="search" action="/fws/FWS_Cliente/produto/HTML/produto.php" method="get"
            style="margin: 0 10px;">
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

    <div id="icone-usuario">
          <i class="fas fa-user-circle fa-2x" id="user-menu-toggle"></i>
    </div>

        

    <div id="bem-vindo" style="position: relative; display: inline-block;">
      <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
        <?php
        $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
        $primeiroNome = explode(' ', $nomeCompleto)[0];
        ?>
        <div id="bem-vindo-texto">
          Bem-vindo(a), <?= $primeiroNome ?>
        </div>

      <div id="user-menu"
              style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 0; min-width: 120px; z-index: 1000;">
              <a href="/fws/FWS_Cliente/info_usuario/HTML/info_usuario.php"
              style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
              <a href="/fws/FWS_Cliente/logout.php" id="logout-link"
              style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Sair</a>
      </div>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
          const toggle = document.getElementById("user-menu-toggle");
          const menu = document.getElementById("user-menu");
          const container = document.getElementById("bem-vindo");

      toggle.addEventListener("click", (e) => {
        e.stopPropagation();
        menu.style.display = menu.style.display === "block" ? "none" : "block";
        });

      document.addEventListener("click", (e) => {
          if (!container.contains(e.target) && e.target !== toggle) {
            menu.style.display = "none";
          }
        });
    });
</script>

      <?php else: ?>
        Bem-vindo(a).
      <?php endif; ?>
    </div>
  </header>

  <main class="my-5">
    <div class="container">
      <!-- Título e subtítulo -->
      <div style="text-align: center; margin-bottom: 30px;">
        <?php
          $hora = date('H');
          if ($hora >= 6 && $hora < 12) {
            $saudacao = "Bom dia";
          } elseif ($hora >= 12 && $hora < 18) {
            $saudacao = "Boa tarde";
          } else {
            $saudacao = "Boa noite";
          }
        ?>
        <h1 style="font-size: 2.5rem; font-weight: bold; color: #c40000; margin-bottom: 8px;"><?php echo $saudacao; ?>! O que você está procurando?</h1>
      </div>

      <!-- Barra de pesquisa -->
    <form class="d-flex mb-4" role="search" method="get" action="">
  <style>
    /* Espaçamento de 1px entre Buscar e Filtro */
    #search + .btn.btn-warning {
        margin-right: 5px;
    }
    
    /* Borda vermelha na barra de pesquisa */
    #search {
      border: 2px solid #c40000 !important;
    }
    
    /* Dropdown de ordenação */
    #ordenar {
      border: 2px solid #c40000 !important;
      border-radius: 4px;
      padding: 0.25rem 0.5rem;
      margin-left: 5px;
      font-size: 0.85rem;
      max-width: 150px;
    }
    

  </style>

  <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
    aria-label="Pesquisar" value="<?php echo htmlspecialchars($busca); ?>" />

  <button class="btn btn-warning" type="submit">Buscar</button>
  <button type="button" id="btn-filtro" class="btn btn-outline-secondary">
    <i class="fas fa-filter"></i>
  </button>

  <select id="ordenar" class="form-select" name="ordenar">
    <option value="">Ordenar por</option>
    <option value="nome_asc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] === 'nome_asc') ? 'selected' : ''; ?>>Nome (A-Z)</option>
    <option value="nome_desc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] === 'nome_desc') ? 'selected' : ''; ?>>Nome (Z-A)</option>
    <option value="preco_asc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] === 'preco_asc') ? 'selected' : ''; ?>>Menor preço</option>
    <option value="preco_desc" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] === 'preco_desc') ? 'selected' : ''; ?>>Maior preço</option>
    <option value="mais_vendidos" <?php echo (isset($_GET['ordenar']) && $_GET['ordenar'] === 'mais_vendidos') ? 'selected' : ''; ?>>Mais vendidos</option>
  </select>

  <script>
    document.getElementById('ordenar').addEventListener('change', function() {
      this.form.submit();
    });
  </script>

  <div id="popup-filtro" class="custom-modal" style="display:none;">
    <h5>Filtrar produtos</h5>
    <div>
      <label>Preço mínimo:</label>
      <input type="number" id="min_price" name="min_price" class="form-control" placeholder="R$"
        value="<?php echo isset($_GET['min_price']) ? floatval($_GET['min_price']) : 1; ?>" />
    </div>
    <div>
      <label>Preço máximo:</label>
      <input type="number" id="max_price" name="max_price" class="form-control" placeholder="R$"
        value="<?php echo isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000; ?>" />
    </div>
    <div style="margin-top:10px;">
      <label>Categorias:</label>
      <div id="categorias_filtro" style="
        display: flex;
        flex-wrap: wrap;
        gap: 1px;
        justify-content: center;
        max-width: 400px;
        margin: 0 auto;">
        <?php
        $sql_cats = "SELECT id, nome, cor FROM categorias";
        $res_cats = mysqli_query($conn, $sql_cats);
        // Pega categorias selecionadas do GET, se houver
        $categorias_selecionadas = isset($_GET['categorias']) ? array_map('intval', $_GET['categorias']) : [];
        // Se nenhuma selecionada, seleciona todas
        $all_cat_ids = [];
        while ($cat = mysqli_fetch_assoc($res_cats)) {
            $all_cat_ids[] = $cat['id'];
        }
        if (empty($categorias_selecionadas)) {
            $categorias_selecionadas = $all_cat_ids;
        }

        // Reset do ponteiro para gerar os inputs
        mysqli_data_seek($res_cats, 0);
        while ($cat = mysqli_fetch_assoc($res_cats)) {
    echo '<label style="
            flex: 1 1 calc(50% - 1px);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 2px;
            box-sizing: border-box;
            cursor:pointer;">
            <input type="checkbox" name="categorias[]" value="' . $cat['id'] . '" style="margin-right:5px; transform: scale(1.3);">
            <span class="badge" style="background-color: ' . $cat['cor'] . '; color:white; border-radius:12px; padding:2px 6px;">' . $cat['nome'] . '</span>
          </label>';
}


        ?>
      </div>
    </div>
    <div class="modal-actions" style="margin-top:15px;">
      <button type="button" class="btn-popup cancel" id="cancel-filtro">Cancelar</button>
      <button type="submit" class="btn-popup add" id="aplicar-filtro">Buscar</button>
    </div>
  </div>
  
  <!-- Hidden inputs para manter parâmetros -->
  <input type="hidden" name="page" value="<?php echo $pagina_atual; ?>">
  
</form>




      <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-start">
        <?php
        if ($total_produtos == 0 && $busca !== '') {
          echo '<p class="text-center w-100" style="margin-top: 40px;">Nenhum produto encontrado para "' . htmlspecialchars($busca) . '". Mas veja nossos outros produtos:</p>';

          $sql_sem_filtro = "SELECT p.id, p.nome, p.preco_venda, p.descricao, p.foto_produto, p.estoque,
        c.nome AS categoria, c.cor
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
            $foto = !empty($produto["foto_produto"]) ? htmlspecialchars($produto["foto_produto"]) : "/fws/IMG_Produtos/sem_imagem.png";
            $descricao = isset($produto["descricao"]) ? htmlspecialchars($produto["descricao"]) : "Produto sem descrição.";
            $categoria = htmlspecialchars($produto["categoria"]);
            $cor = htmlspecialchars($produto["cor"]);

            echo '
            <div class="col">
              <div class="card h-100" data-produto-id="' . $id . '">
                <img src="' . $foto . '" class="card-img-top" alt="' . $nome . '">
                <div class="card-body">
                  <h6 class="card-title mb-2" style="font-weight: 600 !important; font-size: 1rem; min-height: 2.4em; line-height: 1.2;">
                    <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '"
                      style="text-decoration: none; color: inherit;">' . $nome . '</a>
                  </h6>
                  <p class="card-text mb-2" style="font-size: 0.85rem; color: #777; min-height: 1.6em; line-height: 1.1; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.5rem !important;">' . substr($descricao, 0, 60) . (strlen($descricao) > 60 ? '...' : '') . '</p>
                  <p class="card-text mb-2" style="font-weight: bold; color: green; font-size: 1.1rem; margin-bottom: 0.5rem !important;">R$ ' . $preco . '</p>
                  <span class="badge" style="background-color: ' . $cor . '; color: white; padding: 6px 10px; border-radius: 12px;">' . $categoria . '</span>
                  <div class="mt-3 carrossel-buttons d-flex flex-column gap-2">
                    <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '"
                      class="btn btn-primary btn-sm" style="margin-right:0; width: 100%; text-align: center;">Ver mais sobre</a>
                    <button type="button"
                      class="Carrinho btn btn-outline-success btn-sm" style="width: 100%;"
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
              $foto = !empty($produto["foto_produto"]) ? htmlspecialchars($produto["foto_produto"]) : "/fws/IMG_Produtos/sem_imagem.png";
              $descricao = isset($produto["descricao"]) ? htmlspecialchars($produto["descricao"]) : "Produto sem descrição.";
              $categoria = htmlspecialchars($produto["categoria"]);
              $cor = htmlspecialchars($produto["cor"]);

              echo '
              <div class="col">
                <div class="card h-100" data-produto-id="' . $id . '">
                  <img src="' . $foto . '" class="card-img-top" alt="' . $nome . '">
                  <div class="card-body">
                    <h6 class="card-title mb-2" style="font-weight: 600 !important; font-size: 1rem; min-height: 2.4em; line-height: 1.2;">
                      <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '"
                        style="text-decoration: none; color: inherit;">' . $nome . '</a>
                    </h6>
                    <p class="card-text mb-2" style="font-size: 0.85rem; color: #777; min-height: 1.6em; line-height: 1.1; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.5rem !important;">' . substr($descricao, 0, 60) . (strlen($descricao) > 60 ? '...' : '') . '</p>
                    <p class="card-text mb-2" style="font-weight: bold; color: green; font-size: 1.1rem; margin-bottom: 0.5rem !important;">R$ ' . $preco . '</p>
                    <span class="badge" style="background-color: ' . $cor . '; color: white; padding: 6px 10px; border-radius: 12px;">' . $categoria . '</span>
                    <div class="mt-2 carrossel-buttons  d-flex flex-column gap-2">
                      <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '"
                        class="btn btn-primary btn-sm" style="margin-right:0; width: 100%; text-align: center;">Ver mais sobre</a>
                      <button type="button"
  class="Carrinho btn btn-outline-success btn-sm" style="width: 100%;"
  data-produto=\'' . htmlspecialchars(json_encode([
                  "id" => $id,
                  "nome" => $nome,
                  "foto" => $foto,
                  "descricao" => $descricao,
                  "preco" => $produto["preco_venda"],
                  "estoque" => $produto["estoque"],
                  "no_carrinho" => isset($produto["no_carrinho"]) ? $produto["no_carrinho"] : 0
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
      $pagina_query = '';
      if ($busca !== '') {
        $pagina_query .= '&q=' . urlencode($busca);
      }
      if (isset($_GET['ordenar']) && $_GET['ordenar'] !== '') {
        $pagina_query .= '&ordenar=' . urlencode($_GET['ordenar']);
      }
      if (isset($_GET['min_price'])) {
        $pagina_query .= '&min_price=' . $_GET['min_price'];
      }
      if (isset($_GET['max_price'])) {
        $pagina_query .= '&max_price=' . $_GET['max_price'];
      }
      if (isset($_GET['categorias']) && is_array($_GET['categorias'])) {
        foreach ($_GET['categorias'] as $cat) {
          $pagina_query .= '&categorias[]=' . intval($cat);
        }
      }
      
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
  <!-- Bootstrap JS and Popper for 5.2.1 -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
    integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Fazer os cards inteiros serem clicáveis
      const cards = document.querySelectorAll('.card');
      cards.forEach(card => {
        card.addEventListener('click', function(e) {
          // Não redirecionar se clicou em um botão
          if (e.target.closest('button')) return;

          // Pega o link do "Ver mais sobre"
          const link = this.querySelector('a.btn-primary');
          if (link) {
            window.location.href = link.href;
          }
        });
      });
    });
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
$(function () {

  var usuario_id = <?php echo isset($_SESSION['usuario_id']) ? intval($_SESSION['usuario_id']) : 'null'; ?>;

  // ------------------------------------------------------------
  // AO CLICAR NO BOTÃO DE CARRINHO
  // ------------------------------------------------------------
  $(".Carrinho").on("click", function () {
    const dados = JSON.parse($(this).attr('data-produto'));

    // PRIMEIRO: verifica limite no backend com tratamento de 403
    $.ajax({
      url: '/fws/FWS_Cliente/carrinho/PHP/adicionar_ao_carrinho.php',
      method: 'POST',
      data: {
        verificar_limite: 1,
        id_produto: dados.id
      },
      success: function(raw) {
        let resp;
        try {
          resp = JSON.parse(raw);
        } catch (e) {
          console.error("Erro ao ler resposta:", raw);
          return;
        }

        if (resp.restante <= 0) {
          mostrarAvisoLimite(dados.nome);
          return;
        }

        abrirPopupAdicionar(dados, resp.restante);
      },
      error: function(xhr) {
        if (xhr.status === 403) {
          showLoginModal();
        } else {
          console.error("Erro inesperado:", xhr.status, xhr.responseText);
        }
      }
    });

    return;
  });

  // ------------------------------------------------------------
  // FUNÇÃO: Aviso quando bate limite
  // ------------------------------------------------------------
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

  // ------------------------------------------------------------
  // FUNÇÃO: Abrir popup de adicionar ao carrinho
  // ------------------------------------------------------------
  function abrirPopupAdicionar(dados, maxPermitido) {

    if (!usuario_id) {
      showLoginModal();
      return;
    }

    let qtd = 1;
    const preco = parseFloat(dados.preco);

    function atualizarPreco() {
      $("#valor-unit").text(preco.toLocaleString("pt-BR", { style: "currency", currency: "BRL" }));
      $("#valor-total").text((preco * qtd).toLocaleString("pt-BR", { style: "currency", currency: "BRL" }));
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

    // Botão diminuir
    $(".contador-btn.menos").on("click", function () {
      if (qtd > 1) {
        qtd--;
        atualizarPreco();
      }
    });

    // Botão aumentar com limite real
    $(".contador-btn.mais").on("click", function () {
      if (qtd < maxPermitido) {
        qtd++;
        atualizarPreco();
      }
    });

    $(".btn-popup.cancel").on("click", function () {
      $("#modal-add-carrinho, #modal-backdrop").hide();
    });

    // CONFIRMAR ADIÇÃO
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

            <div class="modal-actions">
                <button class="btn-popup add ok-close">Fechar</button>
            </div>
        `);

        $(".ok-close").on("click", function () {
          $("#modal-add-carrinho, #modal-backdrop").hide();
        });
      });
    });

  }

  // ------------------------------------------------------------
  // FUNÇÃO: Exibir modal de login
  // ------------------------------------------------------------
  function showLoginModal() {
    $("#modal-backdrop").show();
    $("#modal-add-carrinho").html(`
        <div style="color:#c40000;font-weight:700;font-size:1.15rem;margin-bottom:14px;text-align:center">
            É necessário fazer login para adicionar produtos
        </div>
        <div class="modal-actions" style="justify-content:center;gap:15px;margin-top:20px;display:flex;flex-wrap:wrap;">
            <a href="../../login/HTML/login.html" class="btn-login" style="background:#11C47E;color:white;border:none;padding:10px 25px;border-radius:6px;text-decoration:none;font-weight:600;">Login</a>
            <a href="../../cadastro/HTML/cadastro.html" class="btn-cadastrar" style="background:#FFD100;color:#111;border:none;padding:10px 25px;border-radius:6px;text-decoration:none;font-weight:600;">Cadastrar</a>
            <button class="btn-popup cancel btn-voltar" style="background:#E53935;color:white;border:none;padding:10px 25px;border-radius:6px;">Voltar</button>
        </div>
    `).show();

    $(".btn-voltar").off('click').click(function() {
        $("#modal-add-carrinho, #modal-backdrop").hide();
    });
}

});
</script>



  <script>$(function () {
      $('#btn-filtro').on('click', function () {
        $('#popup-filtro, #modal-backdrop').show();
      });

      $('#cancel-filtro').on('click', function () {
        $('#popup-filtro, #modal-backdrop').hide();
      });

      $('#aplicar-filtro').on('click', function () {
        let min_price = $('#min_price').val();
        let max_price = $('#max_price').val();
        let categorias = [];
        $('#categorias_filtro input:checked').each(function () {
          categorias.push($(this).val());
        });

        // Monta URL de GET
        let url = 'produto.php?';
        let params = [];
        if (min_price) params.push('min_price=' + min_price);
        if (max_price) params.push('max_price=' + max_price);
        if (categorias.length > 0) params.push('categorias[]=' + categorias.join('&categorias[]='));
        if ($('#search').val()) params.push('q=' + encodeURIComponent($('#search').val()));
        url += params.join('&');
        window.location.href = url;
      });
    });
  </script>



  <div id="modal-backdrop" class="custom-backdrop" style="display:none"></div>
  <div id="modal-add-carrinho" class="custom-modal" style="display:none"></div>
  <div id="modal-login-alert" class="custom-modal" style="display:none"></div>


  <style>
    #header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 25px;
      background-color: #c40000;
      color: white;
    }

    #icone-usuario {
      display: flex;
      align-items: center;
      cursor: pointer;
    }

    .carrinho img {
      height: 27px !important;
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
      box-shadow: 0 8px 16px rgba(196, 0, 0, 0.5);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card {
      border: 2px solid #c40000;
    }

    .btn-primary.btn-sm {
      background: #FFD100;
      border: 3px solid #FFD100;
      color: black;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
    }

    .btn-primary.btn-sm:hover {
      background: white;
      color: black;
      border-color: #FFD100;
    }

    .btn-outline-success.btn-sm {
      background: white;
      border: 2px solid #00BC5B;
      color: #00BC5B;
      padding: 5px 10px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-outline-success.btn-sm:hover {
      background: #00BC5B;
      color: white;
      border-color: #00BC5B;
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

    #popup-filtro {
      position: fixed;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      z-index: 3000;
    }

  
/* ---------------------------- */
/* Botão filtro */
#btn-filtro {
    background-color: #c82333; /* vermelho */
    border: 2px solid #991f2b;
    color: white;
}

/* Ícone dentro do botão filtro herda cor */
#btn-filtro i {
    color: inherit;
}

/* Hover do botão filtro: inverte cores */
#btn-filtro:hover {
    background-color: white;
    color: #c82333;
}

/* ---------------------------- */
/* Botão buscar */
.btn.btn-warning {
    background-color: #ffc107; /* amarelo */
    border: 2px solid #d39e00;
    color: black;
}

/* Hover botão buscar: inverte cores */
.btn.btn-warning:hover {
    background-color: white;
    color: black;
}

/* Paginação com cores Shell */
.pagination {
  display: flex;
  gap: 5px;
  justify-content: center;
  margin-top: 30px;
}

.page-item .page-link {
  background-color: #FFD100;
  color: #c40000;
  border: 1px solid #c40000;
  border-radius: 5px;
  padding: 8px 12px;
  text-decoration: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.page-item .page-link:hover {
  background-color: #c40000;
  color: #FFD100;
  border-color: #c40000;
}

.page-item.active .page-link {
  background-color: #c40000;
  color: #FFD100;
  border-color: #c40000;
}

.page-item.disabled .page-link {
  background-color: #e0e0e0;
  color: #999;
  cursor: not-allowed;
  border-color: #999;
}

.page-item:first-child .page-link,
.page-item:last-child .page-link {
  background-color: #FFD100;
  color: #c40000;
  border-color: #c40000;
  font-weight: 700;
}

.page-item:first-child .page-link:hover,
.page-item:last-child .page-link:hover {
  background-color: #c40000;
  color: #FFD100;
  border-color: #c40000;
}

  </style>

</body>

</html>
