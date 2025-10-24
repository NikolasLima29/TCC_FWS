<?php
session_start();
include "../../conn.php";

$produtos_por_pagina = 30;
$pagina_atual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($pagina_atual - 1) * $produtos_por_pagina;

// Captura o termo de busca enviada pelo formulário "search" e salva na sessão
if (isset($_GET['q'])) {
    $_SESSION['busca'] = trim($_GET['q']);
}
// Recupera o termo salvo na sessão, ou vazio se não existir
$busca = isset($_SESSION['busca']) ? $_SESSION['busca'] : '';

// Monta a parte fixa da query SQL
$sql_base = "FROM produtos p 
             INNER JOIN categorias c ON p.categoria_id = c.id 
             WHERE p.status = 'ativo'";

// Se houver termo de busca, adiciona condição LIKE seguro
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
$sql = "SELECT p.id, p.nome, p.preco_venda, p.foto_produto, c.nome AS categoria, c.cor 
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
$(function() {
  var autocomplete = $("#search").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: '../../produto/PHP/api-produtos.php',
        dataType: 'json',
        data: { q: request.term },
        success: function(data) {
          response(data);
        }
      });
    },
    minLength: 2,
    select: function(event, ui) {
      window.location.href = '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
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
                    <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get"
                        style="margin: 0 10px;">
                        <input id="search" class="form-control form-control-sm me-2" type="search" name="q"
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
    <main class="my-5">
        <div class="container">
            <!-- Barra de pesquisa -->
            <form class="d-flex mb-4" role="search" method="get" action="">
                <input id="search"class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
                    aria-label="Pesquisar" value="<?php echo htmlspecialchars($busca); ?>" />
                <button class="btn btn-warning" type="submit">Buscar</button>
            </form>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-start">

                <?php
                // Se não encontrar produtos com o termo pesquisado
                if ($total_produtos == 0 && $busca !== '') {
                    echo '<p class="text-center w-100" style="margin-top: 40px;">Nenhum produto encontrado para "'
                        . htmlspecialchars($busca) . '". Mas veja nossos outros produtos:</p>';

                    // Faz a consulta sem filtro
                    $sql_sem_filtro = "SELECT p.id, p.nome, p.preco_venda, p.foto_produto, c.nome AS categoria, c.cor
                                   FROM produtos p
                                   INNER JOIN categorias c ON p.categoria_id = c.id
                                   WHERE p.status = 'ativo'
                                   LIMIT $produtos_por_pagina OFFSET $offset";

                    $resultado_sem_filtro = mysqli_query($conn, $sql_sem_filtro);

                    // Atualiza total e total_paginas baseados na consulta sem filtro
                    $sql_total_sem_filtro = "SELECT COUNT(*) as total FROM produtos WHERE status = 'ativo'";
                    $resultado_total_sem_filtro = mysqli_query($conn, $sql_total_sem_filtro);
                    $total_produtos = mysqli_fetch_assoc($resultado_total_sem_filtro)['total'];
                    $total_paginas = ceil($total_produtos / $produtos_por_pagina);

                    // Exibe os produtos sem filtro
                    while ($produto = mysqli_fetch_assoc($resultado_sem_filtro)) {
                        $id = $produto["id"];
                        $nome = ucwords(strtolower(htmlspecialchars($produto["nome"])));
                        $preco = number_format($produto["preco_venda"], 2, ',', '.');
                        $foto = htmlspecialchars($produto["foto_produto"]);
                        $categoria = htmlspecialchars($produto["categoria"]);
                        $cor = htmlspecialchars($produto["cor"]);

                        if (empty($foto)) {
                            $foto = "/TCC_FWS/IMG_Produtos/sem_imagem.png";
                        }

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
                                <div class="mt-3">
                                    <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                                       class="btn btn-primary btn-sm" style="background:#c40000; border-color:#c40000;">Ver mais</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                    }

                } else {
                    // Exibe resultados normais da busca (ou todos produtos se busca vazia)
                    if (mysqli_num_rows($resultado) > 0) {
                        while ($produto = mysqli_fetch_assoc($resultado)) {
                            $id = $produto["id"];
                            $nome = ucwords(strtolower(htmlspecialchars($produto["nome"])));
                            $preco = number_format($produto["preco_venda"], 2, ',', '.');
                            $foto = htmlspecialchars($produto["foto_produto"]);
                            $categoria = htmlspecialchars($produto["categoria"]);
                            $cor = htmlspecialchars($produto["cor"]);

                            if (empty($foto)) {
                                $foto = "/TCC_FWS/IMG_Produtos/sem_imagem.png";
                            }

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
                                    <div class="mt-3">
                                        <a href="../../produto_especifico/HTML/produto_especifico.php?id=' . $id . '" 
                                           class="btn btn-primary btn-sm" style="background:#c40000; border-color:#c40000;">Ver mais</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                        }
                    }
                }
                ?>
            </div>


            <?php
            // Paginação com preservação do termo busca na URL
            $pagina_query = $busca !== '' ? '&q=' . urlencode($busca) : '';

            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination justify-content-center mt-4">';

            // Link anterior
            if ($pagina_atual > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($pagina_atual - 1) . $pagina_query . '">Anterior</a></li>';
            } else {
                echo '<li class="page-item disabled"><span class="page-link">Anterior</span></li>';
            }

            // Links numéricos das páginas (pode limitar a algumas, se desejar)
            for ($i = 1; $i <= $total_paginas; $i++) {
                $active = ($i == $pagina_atual) ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . $pagina_query . '">' . $i . '</a></li>';
            }

            // Link próximo
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
    </style>



</body>

</html>