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

</head>

<body>
    <!-- Header with same nav, style, and behavior -->
  <header id="header">
<style>
#header {
  position: sticky;
  top: 0;
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

    <nav class="nav-links">
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
            <li><a href="/TCC_FWS/FWS_Cliente/meus_pedidos/HTML/Meus_pedidos.php">Meus pedidos</a></li>
            <li><a href="/TCC_FWS/FWS_Cliente/tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
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
                <i class="fas fa-user-circle fa-2x" style="max width: 90px;"></i>
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

    <main>
  <!-- Produto - Topo -->
  <div
  class="w-100 py-4 d-flex justify-content-center align-items-center"
  style="background: <?php echo $produto['categoria_cor']; ?>; min-height: 330px; border-bottom: 8px solid rgba(251, 46, 46, 1);">
  <img
    src="<?php echo $produto['foto_produto']; ?>"
    alt="Produto"
    class="img-fluid rounded shadow bg-white p-3"
    style="max-width: 420px; background: white;"
  />
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
                Você já adicionou o máximo permitido pelo estoque para <b>${nomeProd}</b>.
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


<div id="modal-backdrop" class="custom-backdrop" style="display:none; position: fixed; top:0; left:0; width:100vw; height:100vh; background-color: rgba(0,0,0,0.5); z-index: 1500;"></div>


<div id="modal-add-carrinho" class="custom-modal" style="display:none; position: fixed; z-index: 1600; background:#fff; border-radius: 12px; padding: 20px; max-width: 300px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); left: 50%; top:50%; transform: translate(-50%, -50%);">

</div>


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

/* Botão adicionar ao carrinho */
#adicionar {
  margin-top: 15px;
  background-color: #c40000;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

#adicionar:disabled {
  background-color: #888;
  cursor: not-allowed;
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
</style>


    </style>


</body>

</html>