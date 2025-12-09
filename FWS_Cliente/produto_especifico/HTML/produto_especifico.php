<?php
session_start();
include "../../conn.php";

if (isset($_GET['id'])) {
    $_SESSION['produto_id'] = (int)$_GET['id'];
}
$produto_id = $_SESSION['produto_id'] ?? 0;


$produto_id = $_SESSION['produto_id'] ?? 0;

// Consulta produto, categoria e fornecedor
$result = $conn->query("
  SELECT p.*, c.nome AS categoria_nome, c.cor AS categoria_cor, f.nome AS fornecedor_nome
  FROM produtos p
  JOIN categorias c ON p.categoria_id = c.id
  JOIN fornecedores f ON p.fornecedor_id = f.id
  WHERE p.id = $produto_id
");
if ($result->num_rows === 0) {
    die('Produto não encontrado');
}
$produto = $result->fetch_assoc();

$sinergias = [
    "bebidas não alcoólicas" => ["snacks", "salgados", "doces"],
    "sorvetes"               => ["doces", "biscoitos"],
    "doces"                  => ["biscoitos", "laticínios", "sorvetes"],
    "snacks"                 => ["bebidas não alcoólicas", "salgados"],
    "laticínios"             => ["doces", "biscoitos"],
    "biscoitos"              => ["doces", "laticínios", "bebidas não alcoólicas", "sorvetes"],
    "salgados"               => ["bebidas não alcoólicas", "snacks"]
];

$categorias_sem_sinergia = [
    "bebidas alcoólicas",
    "proteicos",
    "cigarros e itens de fumo",
    "outros"
];


?>

<?php
// Lógica de recomendações
$categoria_atual = strtolower($produto['categoria_nome']);

if (in_array($categoria_atual, $categorias_sem_sinergia)) {

    $titulo_recomendacoes = "Outros produtos que você pode gostar:";
    $sql_recomendados = "
        SELECT id, nome, preco_venda, foto_produto
        FROM produtos
        WHERE id != $produto_id
        AND estoque >= 2
        ORDER BY RAND()
        LIMIT 5
    ";

} elseif (isset($sinergias[$categoria_atual])) {

    $titulo_recomendacoes = "Este produto combina com:";

    // escolhe uma categoria sinérgica aleatória
    $categoria_alvo = $sinergias[$categoria_atual][array_rand($sinergias[$categoria_atual])];

    $sql_recomendados = "
        SELECT p.id, p.nome, p.preco_venda, p.foto_produto
        FROM produtos p
        JOIN categorias c ON p.categoria_id = c.id
        WHERE LOWER(c.nome) = '$categoria_alvo'
        AND p.id != $produto_id
        AND p.estoque >= 2
        ORDER BY p.nome ASC
        LIMIT 5
    ";

} else {

    // fallback (não deve acontecer)
    $titulo_recomendacoes = "Outros produtos que você pode gostar:";
    $sql_recomendados = "
        SELECT id, nome, preco_venda, foto_produto
        FROM produtos
        WHERE id != $produto_id
        AND estoque >= 2
        ORDER BY RAND()
        LIMIT 5
    ";
}

$result_recomendados = $conn->query($sql_recomendados);

if (!$result_recomendados) {
    die("Erro ao buscar recomendações: " . $conn->error);
}

// Se não encontrou produtos na categoria sinérgica, usar fallback aleatório
if ($result_recomendados->num_rows == 0) {
    $titulo_recomendacoes = "Talvez você goste destes produtos:";
    $sql_fallback = "
        SELECT id, nome, preco_venda, foto_produto
        FROM produtos
        WHERE id != $produto_id
        AND estoque >= 2
        ORDER BY RAND()
        LIMIT 5
    ";
    $result_recomendados = $conn->query($sql_fallback);
    
    if (!$result_recomendados) {
        die("Erro ao buscar recomendações (fallback): " . $conn->error);
    }
}

?>
<!doctype html>
<html lang="pt-BR">

<head>
    <title> <?php echo htmlspecialchars($produto['nome']); ?></title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.2.1 for consistency -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous" />

    <link rel="stylesheet" href="../CSS/produto_especifico.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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

        /* Cor dos links do menu - BRANCO */
        .navbar-nav .nav-link {
            color: white !important;
        }

        .navbar-nav .nav-link:focus {
            color: white !important;
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
                        <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                            style="object-fit: contain; filter: brightness(0) invert(1);">
                    </a>

                    <!-- Perfil Mobile (com validação de login) -->
                    <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>"
                        class="me-2">
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
                    <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get"
                        style="margin: 0;">
                        <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
                            style="width: 300px;">
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
                    <ul class="navbar-nav d-flex align-items-center gap-4 justify-content-center w-100"
                        style="margin-right: 40px;">
                        <li class="nav-item">
                            <a class="nav-link" href="../../index.php">
                                <span class="menu-bold">Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../produto/HTML/produto.php">
                                <span class="menu-bold">Produtos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../meus_pedidos/HTML/Meus_pedidos.php">
                                <span class="menu-bold">Meus Pedidos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../tela_sobre_nos/HTML/sobre_nos.php">
                                <span class="menu-bold">Sobre Nós</span>
                            </a>
                        </li>
                    </ul>

                    <!-- ========== SEÇÃO DESKTOP (CARRINHO + BEM-VINDO + PERFIL) ========== -->
                    <div class="d-flex align-items-center ms-auto me-4">
                        <!-- Carrinho Desktop -->
                        <a href="../../carrinho/HTML/carrinho.php" style="margin-left: -70px;">
                            <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                                class="me-4" style="object-fit: contain; filter: brightness(0) invert(1);">
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
                            href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>">
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
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a), <?= $primeiroNome ?></h5>
            <?php else: ?>
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a)</h5>
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
  <!-- Produto - Topo -->
  <div
  class="w-100 py-4 d-flex justify-content-center align-items-center"
  style="background: <?php echo $produto['categoria_cor']; ?>; min-height: 330px; border-bottom: 8px solid rgba(251, 46, 46, 1);">
  <div class="zoom-container" style="position: relative; overflow: hidden; width: 420px; height: 420px; border-radius: 8px; background: white;">
    <img
      id="imagem-zoom"
      src="<?php echo $produto['foto_produto']; ?>"
      alt="Produto"
      class="img-fluid rounded shadow bg-white p-3"
      style="max-width: 420px; width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; transition: transform 0.3s ease;"
    />
  </div>
</div>

  <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap:32px; margin: 40px 40px 0 40px;">
    <!-- Lado Esquerdo (Descrição e Fornecedor) -->
    <div style="flex: 1; min-width: 340px;">
      <h1 style="font-size: 2.4rem; font-weight: bold; margin-bottom: 0;"> <?php echo htmlspecialchars($produto['nome']); ?> </h1>
      <span class="badge" style="background-color: <?php echo $produto['categoria_cor']; ?>; color: #ffffffff; display:inline-block; padding: 8px 18px; border-radius: 7px; font-size: 1.4rem; font-weight: bold; margin:10px 0 28px 0;">
        <?php echo mb_strtolower(htmlspecialchars($produto['categoria_nome']), 'UTF-8'); ?>
      </span>
      <div style="display: flex; align-items: center; margin-bottom:22px;">
        <span style="color:#00BC5B; font-size:2rem; font-weight: bold;">R$ <?php echo number_format($produto['preco_venda'], 2, ',', '.'); ?></span>
      </div>

      <div style="margin-bottom: 15px; background:#F2F2F2; border-radius: 7px; border:2px solid #222; padding:12px 18px 10px 18px;">
        <span style="font-weight:bold;">Descrição:</span>
        <span style="margin-left: 7px;"><?php echo htmlspecialchars($produto['descricao']); ?></span>
      </div>
      <div style="margin-bottom: 30px; background:#F2F2F2; border-radius: 7px; border:2px solid #222; padding:12px 18px 10px 18px;">
        <span style="font-weight:bold;">Fornecedor:</span>
        <span style="margin-left: 7px;"><?php echo htmlspecialchars($produto['fornecedor_nome']); ?></span>
      </div>

      <!-- Botões de Compartilhamento -->
      <div style="margin-bottom: 30px; display: flex; gap: 12px; align-items: center;">
        <span style="font-weight:bold; font-size: 0.95rem;">Compartilhar:</span>
        <button type="button" class="btn-compartilhar-whatsapp" data-produto-id="<?php echo $produto['id']; ?>" data-produto-nome="<?php echo htmlspecialchars($produto['nome']); ?>" title="Compartilhar no WhatsApp" style="background: white; border: 2px solid #25D366; color: #25D366; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; font-size: 1.2rem;">
          <i class="fab fa-whatsapp"></i>
        </button>
        <button type="button" class="btn-compartilhar-facebook" data-produto-id="<?php echo $produto['id']; ?>" data-produto-nome="<?php echo htmlspecialchars($produto['nome']); ?>" title="Compartilhar no Facebook" style="background: white; border: 2px solid #1877F2; color: #1877F2; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; font-size: 1.2rem;">
          <i class="fab fa-facebook"></i>
        </button>
        <button type="button" class="btn-compartilhar-link" data-produto-id="<?php echo $produto['id']; ?>" title="Copiar link" style="background: white; border: 2px solid #c40000; color: #c40000; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; cursor: pointer; transition: all 0.3s ease; font-size: 1.2rem;">
          <i class="fas fa-link"></i>
        </button>
      </div>

      <!-- Aviso +18 para bebidas/cigarros -->
      <?php if(in_array($produto['categoria_id'], [1,9])): ?>
        <div style="margin-top: 8px; margin-bottom: 0px; background: #FFF; border:2px solid #111; border-radius: 8px; font-size:1rem; padding:8px 16px; font-weight:600; display:inline-block;">
          PARA MAIORES DE 18 anos:
          <span style="font-weight:400;"><?php echo htmlspecialchars($mensagem_etaria ?? 'Necessário RG na entrega/retirada'); ?></span>
          <i class="fas fa-ban" style="color:#C40000; margin-left:10px; font-size:1.1em;"></i>
        </div>
      <?php endif ?>
    </div>

    <!-- Lado Direito (Preço, Carrinho) -->
    <div style="flex: 1; min-width: 340px; display: flex; flex-direction: column; align-items: flex-end;">
      <div style="width:100%; text-align:right; margin-bottom:10px;">
        <span style="font-size:1.25rem; font-weight:500;">Preço total:</span>
        <span style="font-size:1.4rem; color:#00BC5B; font-weight: bold; margin-left:10px;" id="precoTotal">
          R$ <?php echo number_format($produto['preco_venda'], 2, ',', '.'); ?>
        </span>
      </div>
      <div style="width:100%; text-align:right; font-size: 1.15rem; font-weight:600; margin-bottom: 12px;">
        Quantidade: <span id="quantidadeShow">1</span>
      </div>
      <div style="display:flex; gap:0; margin-bottom:18px;">
        <button id="menos"
          style="background:#E53935; color:white; border:none; width:56px; height:56px; font-size:2.2rem; border-radius:12px 0 0 12px; outline:none; border-right:2px solid #222; font-weight:800; cursor:pointer;"
          <?php echo ($produto['estoque'] <= 1) ? 'disabled' : ''; ?>>-</button>
        <button id="mais"
          style="background:#FFD100; color:#111; border:none; width:56px; height:56px; font-size:2.2rem; border-radius:0 12px 12px 0; outline:none; font-weight:800; border-left:2px solid #222; cursor:pointer;"
          <?php echo ($produto['estoque'] <= 1) ? 'disabled' : ''; ?>>+</button>
      </div>
      <button id="adicionar"
        style="background:#11C47E; color:white; border:none; border-radius:9px; font-size:1.26rem; font-weight:700; padding:12px 40px 12px 24px; box-shadow:0 2px 4px #0002; cursor:pointer; position:relative; box-sizing:border-box; display:flex; align-items:center;"
        <?php echo ($produto['estoque'] == 0) ? 'disabled' : ''; ?>>
        adicionar ao carrinho
        <span style="margin-left:7px; font-size:1.3rem;"><i class="fas fa-shopping-cart"></i></span>
      </button>
    </div>
  </div>
 <?php
echo "<div class='container px-3'>";

echo "<h4 class='mt-4 mb-3'>$titulo_recomendacoes</h4>";

// grid com spacing perfeitinho
echo '<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">';

while ($rec = $result_recomendados->fetch_assoc()) {

    $foto = !empty($rec["foto_produto"]) ? htmlspecialchars($rec["foto_produto"]) : "/fws/IMG_Produtos/sem_imagem.png";
    $nome = ucwords(strtolower(htmlspecialchars($rec["nome"])));
    $preco = number_format($rec["preco_venda"], 2, ',', '.');
    $id = $rec["id"];

    echo '
    <div class="col">
        <div class="card h-100 p-2">
            <img src="' . $foto . '" 
                 class="card-img-top" 
                 style="object-fit:cover; height:140px; border-radius:6px;">

            <div class="card-body d-flex flex-column justify-content-between">
                <div>
                    <h6 class="card-title" style="font-size:0.95rem; min-height:38px;">
                        <a href="produto_especifico.php?id=' . $id . '" 
                           style="text-decoration:none; color:inherit;">'
                           . $nome .
                        '</a>
                    </h6>
                    <p class="card-text" style="color:green; font-weight:bold;">R$ ' . $preco . '</p>
                </div>

                <div class="mt-2 d-flex flex-column gap-2">

                    <a href="produto_especifico.php?id=' . $id . '" 
                       class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">Ver mais</a>

                    <button type="button"
                        class="Carrinho btn btn-outline-success btn-sm" style="width: 100%;"
                        data-produto=\'' . htmlspecialchars(json_encode([
                            "id" => $id,
                            "nome" => $nome,
                            "foto" => $foto,
                            "descricao" => "",
                            "preco" => $rec["preco_venda"],
                            "estoque" => $rec["estoque"] ?? 0,
                            "no_carrinho" => $rec["no_carrinho"] ?? 0
                        ]), ENT_QUOTES, "UTF-8") . '\'>
                        Adicionar ao Carrinho <i class="bi bi-cart-plus-fill"></i>
                    </button>

                </div>
            </div>
        </div>
    </div>';
}

echo '</div>'; // row
echo '</div>'; // container
?>

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
                    window.location.href =
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
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
                    window.location.href =
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
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
$(function () {
    const precoUnitario = <?php echo $produto['preco_venda']; ?>;
    const produtoId = <?php echo $produto['id']; ?>;
    const dadosProduto = {
        id: <?php echo $produto['id']; ?>,
        nome: "<?php echo addslashes($produto['nome']); ?>",
        foto: "<?php echo $produto['foto_produto']; ?>",
        preco: <?php echo $produto['preco_venda']; ?>,
        descricao: "<?php echo addslashes($produto['descricao']); ?>"
    };
    let quantidade = 1;
    let limiteMaximo = 10; // ← PADRÃO

    // Controles +/-
    function atualizaPreco() {
        document.getElementById('precoTotal').innerText = 'R$ ' + (precoUnitario * quantidade).toFixed(2).replace('.', ',');
        document.getElementById('quantidadeShow').innerText = quantidade;
    }

    // 🔥 BUSCA LIMITE REAL DO BACKEND NA CARGA DA PÁGINA
    $.ajax({
        url: '../../carrinho/PHP/adicionar_ao_carrinho.php',
        method: 'POST',
        data: {
            verificar_limite: 1,
            id_produto: dadosProduto.id
        },
        xhrFields: { withCredentials: true },
        success: function(raw) {
            let resp;
            try {
                resp = JSON.parse(raw);
                limiteMaximo = resp.limite; // ← PEGA LIMITE REAL DO PHP
            } catch (e) {
                console.error("Erro ao buscar limite:", raw);
                limiteMaximo = 10;
            }
        },
        error: function() {
            limiteMaximo = 10; // Fallback
        }
    });

    // ✅ BOTÃO + INATIVO COM TOAST 4 SEGUNDOS
 $('#mais').click(function() {
    if (quantidade < limiteMaximo) {
        quantidade++;
        atualizaPreco();
    } else {
        // BOTÃO FICA INATIVO + TOAST CENTRALIZADO
        $(this).prop('disabled', true).css('opacity', '0.6');
        
        // ✅ TOAST NO CENTRO DA TELA
        const toast = $('<div id="toast-limite" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#E53935;color:white;padding:15px 25px;border-radius:12px;font-weight:600;z-index:9999;box-shadow:0 8px 25px rgba(0,0,0,0.4);display:flex;align-items:center;gap:12px;max-width:320px;text-align:center;">🚫 Impossível adicionar mais, limite do estoque atingido</div>').appendTo('body');
        
        setTimeout(() => {
            toast.fadeOut(300, function() { $(this).remove(); });
            $(this).prop('disabled', false).css('opacity', '1');
        }, 4000);
    }
});


    $('#menos').click(function() {
        if (quantidade > 1) {
            quantidade--;
            atualizaPreco();
        }
    });

    // 🔥 CLIQUE PRINCIPAL - VERIFICA LIMITE FINAL + ADICIONA
    $('#adicionar').click(function() {
        // 1. Verifica limite FINAL no backend
        $.ajax({
            url: '../../carrinho/PHP/adicionar_ao_carrinho.php',
            method: 'POST',
            data: {
                verificar_limite: 1,
                id_produto: dadosProduto.id
            },
            xhrFields: { withCredentials: true },
            success: function(raw) {
                let resp;
                try {
                    resp = JSON.parse(raw);
                } catch (e) {
                    console.error("Erro ao ler resposta:", raw);
                    return;
                }

                // 2. Se já atingiu limite TOTAL
                if (resp.restante <= 0) {
                    mostrarAvisoLimite(dadosProduto.nome);
                    return;
                }

                // 3. Se quantidade > restante disponível
                if (quantidade > resp.restante) {
                    mostrarAvisoLimite(dadosProduto.nome);
                    return;
                }

                // 4. ADICIONA AO CARRINHO
                $.ajax({
                    url: '../../carrinho/PHP/adicionar_ao_carrinho.php',
                    method: 'POST',
                    data: {
                        id_produto: dadosProduto.id,
                        quantidade: quantidade
                    },
                    xhrFields: { withCredentials: true },
                    success: function(finalResp) {
                        if (finalResp.trim() === "OK") {
                            $("#modal-backdrop").show();
                            $("#modal-add-carrinho").html(`
                                <div style="color:#090;font-weight:600;font-size:1.08rem;margin-bottom:10px;">
                                    ✔️ ${dadosProduto.nome} foi adicionado ao seu carrinho!
                                </div>
                                <img src="${dadosProduto.foto}" style="max-width:110px;margin-bottom:8px;border-radius:8px;">
                                <div>Quantidade: <b>${quantidade}</b></div>
                                <div>Total: <b>${(precoUnitario * quantidade).toLocaleString("pt-BR", {style:"currency", currency:"BRL"})}</b></div>
                                <div class="modal-actions" style="text-align: right; margin-top: 15px;">
                                    <button class="btn btn-primary ok-close" style="background:#11C47E;color:white;border:none;padding:8px 20px;border-radius:6px;">Fechar</button>
                                </div>
                            `).show();

                            $(".ok-close").off('click').on("click", function() {
                                $("#modal-add-carrinho, #modal-backdrop").hide();
                            });
                        } else {
                            alert("Erro ao adicionar: " + finalResp);
                        }
                    },
                    error: function(xhr) {
                        alert('Erro ao adicionar: ' + xhr.status);
                    }
                });
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    showLoginModal();
                } else {
                    alert('Erro de conexão: ' + xhr.status);
                }
            }
        });
    });

    // AVISO DE LIMITE ATINGIDO
    function mostrarAvisoLimite(nomeProd) {
        $("#modal-backdrop").show();
        $("#modal-add-carrinho").html(`
            <div style="color:#b30000;font-weight:700;font-size:1.15rem;margin-bottom:14px;text-align:center">
                Limite atingido
            </div>
            <p style="text-align:center;margin-bottom:10px">
                Você já adicionou o máximo permitido pelo estoque para <b>${nomeProd} ou está indisponível</b>.
            </p>
            <div class="modal-actions" style="text-align: center; margin-top: 15px;">
                <button class="btn-popup cancel ok-close" style="background:#E53935;color:white;border:none;padding:8px 20px;border-radius:6px;">Fechar</button>
            </div>
        `).show();

        $(".ok-close").off('click').on("click", function() {
            $("#modal-add-carrinho, #modal-backdrop").hide();
        });
    }

    // MODAL DE LOGIN (420px)
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

    // Inicializa
    atualizaPreco();
});
</script>





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
      <img src="${dados.foto}" style="max-width:300px; alt="${dados.nome}">
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
            <img src="${dados.foto}" style="max-width:300px;margin-bottom:8px;">
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


<div id="modal-backdrop" class="custom-backdrop" style="display:none; position: fixed; top:0; left:0; width:100vw; height:100vh; background-color: rgba(0,0,0,0.5); z-index: 1500;"></div>


<div id="modal-add-carrinho" class="custom-modal" style="display:none; position: fixed; z-index: 1600; background:#fff; border-radius: 12px; padding: 20px; max-width: 300px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); left: 50%; top:50%; transform: translate(-50%, -50%);">

</div>


    <style>
        /* Container principal do produto */
.produto-topo {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 20px;
  padding: 20px;
  flex-wrap: wrap;
}

/* Imagem do produto */
.produto-topo img {
  width: 1000px;
  height: 700px;
  object-fit: cover;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

/* Informações do produto ao lado direito */
.produto-info {
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Título do produto */
.produto-info h1 {
  margin: 0;
  font-size: 2rem;
  color: #c40000;
}

/* Badge da categoria já tem estilo inline, pode manter */

/* Preço */
.produto-info p, .produto-info span {
  font-size: 1.2rem;
}

/* Controles de quantidade */
.quantidade-controle {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 20px;
}

.quantidade-controle button {
  width: 40px;
  height: 40px;
  font-size: 1.5rem;
  background-color: #c40000;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.quantidade-controle button:disabled {
  background-color: #888;
  cursor: not-allowed;
}

.quantidade-controle input[type="number"] {
  width: 60px;
  font-size: 1.2rem;
  text-align: center;
  border: 1px solid #ccc;
  border-radius: 5px;
  padding: 5px;
}

/* Botão adicionar ao carrinho: manter cor/fonte/tamanho atuais, alterar apenas estilo/hover */
#adicionar {
  margin-top: 15px;
  /* não sobrescrever `background`, `color` ou `font-size` (são definidos inline para garantir consistência) */
  border: none;
  border-radius: 9px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.12);
  transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.12s ease;
  will-change: transform, box-shadow;
}

#adicionar:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 14px 36px rgba(0,0,0,0.18);
  filter: brightness(0.98);
}

#adicionar:active:not(:disabled) {
  transform: translateY(-1px);
}

#adicionar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

/* Preço total */
#precoTotal {
  font-weight: bold;
  font-size: 1.3rem;
  color: #333;
  margin-top: 15px;
  display: inline-block;
}

/* Seção de descrição e fornecedor */
main h3 {
  margin-top: 40px;
  color: #c40000;
  font-size: 1.5rem;
}

main p {
  font-size: 1rem;
  color: #555;
  line-height: 1.5;
}

/* Aviso de restrição */
#aviso-restricao {
  margin-top: 30px;
  padding: 15px;
  background-color: #fee;
  border: 1px solid #c40000;
  border-radius: 8px;
  color: #c40000;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.1rem;
}

#aviso-restricao i {
  color: #c40000;
  font-size: 1.5rem;
}

/* Responsividade */
@media (max-width: 1100px) {
  .produto-topo {
    flex-direction: column;
    align-items: center;
  }
  .produto-topo img {
    width: 100%;
    height: auto;
  }
  .produto-info {
    max-width: 100%;
    margin-top: 20px;
  }
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
  max-width: 90vw;
  background: #fff;
  border-radius: 16px;
  padding: 28px 32px 22px 32px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.25);
  z-index: 2100;
  display: flex; flex-direction: column; align-items: center;
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
  text-align: center;
  margin-bottom: 15px;
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
.custom-modal .btn-popup.add:active {
  background: #42bb42;
}
.custom-modal .btn-popup.cancel {
  background: #c40000;
  color: #fff;
}
.custom-modal .btn-popup.cancel:active {
  background: #9d0909;
}

<style>
    /* ... todo o CSS atual ... */
    
    /* ✅ BOTÕES + e - ULTRA BONITOS - COLE AQUI 👇 */
    #mais, #menos {
        transition: all 0.2s ease !important;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }

    #mais {
        background: linear-gradient(145deg, #FFD100, #FFEB3B) !important;
        color: #111 !important;
        border: 2px solid #FFC107 !important;
        font-weight: 900 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
    }

    #menos {
        background: linear-gradient(145deg, #E53935, #D32F2F) !important;
        color: white !important;
        border: 2px solid #D32F2F !important;
        font-weight: 900 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
    }

    /* HOVER ANIMAÇÃO */
    #mais:hover:not(:disabled), #menos:hover:not(:disabled) {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
    }

    #mais:active:not(:disabled), #menos:active:not(:disabled) {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    }

    /* DISABLED */
    #mais:disabled, #menos:disabled {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
        transform: none !important;
    }

    /* FOCUS */
    #mais:focus, #menos:focus {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(255,193,7,0.3) !important;
    }
    /* 👆 FIM DOS BOTÕES BONITOS */
    
    .custom-backdrop {
        /* ... resto do CSS ... */
    }

    .btn-primary.btn-sm {
      background: #FFD100 !important;
      border: 3px solid #FFD100 !important;
      color: black !important;
      padding: 5px 10px !important;
      border-radius: 4px !important;
      text-decoration: none !important;
      font-weight: 600 !important;
    }

    .btn-primary.btn-sm:hover {
      background: white !important;
      color: black !important;
      border-color: #FFD100 !important;
    }

    .btn-outline-success.btn-sm {
      background: white !important;
      border: 2px solid #00BC5B !important;
      color: #00BC5B !important;
      padding: 5px 10px !important;
      border-radius: 4px !important;
      text-decoration: none !important;
      font-weight: 600 !important;
      transition: all 0.3s ease !important;
    }

    .btn-outline-success.btn-sm:hover {
      background: #00BC5B !important;
      color: white !important;
      border-color: #00BC5B !important;
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

    /* Zoom de imagem */
    .zoom-container {
      position: relative;
      overflow: hidden;
    }

    .zoom-container img {
      transition: transform 0.3s ease;
      transform-origin: center;
    }

    .zoom-container img:hover {
      transform: scale(1.5);
      cursor: zoom-out;
    }

    .modal-zoom {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.9);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .modal-zoom.active {
      display: flex;
    }

    .modal-zoom img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
    }

    .modal-zoom .close-zoom {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 40px;
      font-weight: bold;
      color: white;
      cursor: pointer;
      z-index: 10000;
    }

    .modal-zoom .close-zoom:hover {
      color: #ccc;
    }
</style>


    </style>

  <div id="modal-zoom" class="modal-zoom">
    <span class="close-zoom">&times;</span>
    <img id="imagem-zoom-modal" src="" alt="Zoom da imagem">
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const imagemZoom = document.getElementById('imagem-zoom');
      const modalZoom = document.getElementById('modal-zoom');
      const imagemZoomModal = document.getElementById('imagem-zoom-modal');
      const closeZoom = document.querySelector('.close-zoom');

      if (!imagemZoom) return;

      // Abrir modal com zoom
      imagemZoom.addEventListener('click', function() {
        imagemZoomModal.src = this.src;
        modalZoom.classList.add('active');
        document.body.style.overflow = 'hidden';
      });

      // Fechar modal
      closeZoom.addEventListener('click', function() {
        modalZoom.classList.remove('active');
        document.body.style.overflow = 'auto';
      });

      // Fechar modal clicando fora da imagem
      modalZoom.addEventListener('click', function(e) {
        if (e.target === this) {
          this.classList.remove('active');
          document.body.style.overflow = 'auto';
        }
      });

      // Fechar com ESC
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalZoom.classList.contains('active')) {
          modalZoom.classList.remove('active');
          document.body.style.overflow = 'auto';
        }
      });

      // Compartilhamento no WhatsApp
      document.querySelectorAll('.btn-compartilhar-whatsapp').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const produtoId = this.dataset.produtoId;
          const produtoNome = this.dataset.produtoNome;
          const url = `${window.location.origin}/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=${produtoId}`;
          const mensagem = encodeURIComponent(`Confira este produto:\n\n${produtoNome}\n\n${url}`);
          window.open(`https://wa.me/?text=${mensagem}`, '_blank');
        });
      });

      // Compartilhamento no Facebook
      document.querySelectorAll('.btn-compartilhar-facebook').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const produtoId = this.dataset.produtoId;
          const url = `${window.location.origin}/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=${produtoId}`;
          window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
        });
      });

      // Copiar link para clipboard
      document.querySelectorAll('.btn-compartilhar-link').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const produtoId = this.dataset.produtoId;
          const url = `${window.location.origin}/fws/FWS_Cliente/produto_especifico/HTML/produto_especifico.php?id=${produtoId}`;
          
          navigator.clipboard.writeText(url).then(() => {
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
              this.innerHTML = originalHTML;
            }, 2000);
          }).catch(() => {
            // Fallback para browsers antigos
            const textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
              this.innerHTML = originalHTML;
            }, 2000);
          });
        });
      });
    });
  </script>


</body>

</html>
