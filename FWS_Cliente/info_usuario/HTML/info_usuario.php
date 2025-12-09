<?php
include "../../conn.php";
session_start();

$id = $_SESSION['usuario_id'] ?? 1;
$status = "";
$msg = "";

// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $data_nascimento = $_POST['data_nascimento'];

    $check = $conn->prepare("SELECT id FROM usuarios WHERE (email=? OR telefone=?) AND id<>?");
    $check->bind_param("ssi", $email, $telefone, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $status = "erro";
        $msg = "E-mail ou telefone já cadastrados.";
    } else {
        $sql = "UPDATE usuarios SET nome=?, email=?, telefone=?, data_nascimento=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $data_nascimento, $id);
        $stmt->execute();

        $status = "sucesso";
        $msg = "Dados atualizados com sucesso!";
    }
}

// Buscar dados do usuário
$sql = "SELECT * FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) die("Usuário não encontrado.");
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <title>Informações do Usuário</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../CSS/info_usuario.css">
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
                                <h5 class="m-0 text-white menu-bold">Home</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../produto/HTML/produto.php">
                                <h5 class="m-0 text-white menu-bold">Produtos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../meus_pedidos/HTML/Meus_pedidos.php">
                                <h5 class="m-0 text-white menu-bold">Meus Pedidos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../tela_sobre_nos/HTML/sobre_nos.php">
                                <h5 class="m-0 text-white menu-bold">Sobre Nós</h5>
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

    <!-- Alerta -->
    <div class="container mt-3">
        <div id="alerta" class="d-none text-center p-3 rounded"></div>
    </div>

    <!-- Conteúdo -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="../IMG/usuario.png" alt="avatar" class="rounded-circle img-fluid"
                            style="width: 140px;">
                        <h5 class="my-3"><?= htmlspecialchars($usuario['nome']) ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <form method="POST" id="formUsuario">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-3"><label class="mb-0">Nome completo</label></div>
                                <div class="col-sm-9"><input type="text" name="nome" class="form-control"
                                        value="<?= htmlspecialchars($usuario['nome']) ?>" required readonly></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><label class="mb-0">E-mail</label></div>
                                <div class="col-sm-9"><input type="email" name="email" class="form-control"
                                        value="<?= htmlspecialchars($usuario['email']) ?>" required readonly></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><label class="mb-0">Telefone</label></div>
                                <div class="col-sm-9"><input type="text" name="telefone" id="telefone"
                                        class="form-control" value="<?= htmlspecialchars($usuario['telefone']) ?>"
                                        required readonly></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><label class="mb-0">CPF</label></div>
                                <div class="col-sm-9"><input type="text" id="cpf" class="form-control"
                                        value="<?= htmlspecialchars($usuario['cpf']) ?>" readonly disabled></div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"><label class="mb-0">Data de nascimento</label></div>
                                <div class="col-sm-9"><input type="date" name="data_nascimento" id="data_nascimento"
                                        class="form-control"
                                        value="<?= htmlspecialchars($usuario['data_nascimento']) ?>" required readonly>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="button" class="btn btn-outline-primary" id="btnEditar">Editar</button>
                                <button type="submit" class="btn btn-success d-none" id="btnSalvar">Salvar
                                    alterações</button>
                                <button type="button" class="btn btn-secondary d-none"
                                    id="btnCancelar">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <script>
        // Função de máscara
        function mascaraTelefone(valor) {
            let v = valor.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d{4,5})(\d{4})$/, "$1-$2");
            return v;
        }

        function mascaraCPF(valor) {
            let v = valor.replace(/\D/g, "");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            return v;
        }

        // Aplica máscaras ao carregar a página
        window.addEventListener("DOMContentLoaded", () => {
            const tel = document.getElementById("telefone");
            const cpf = document.getElementById("cpf");
            tel.value = mascaraTelefone(tel.value);
            cpf.value = mascaraCPF(cpf.value);
        });

        const form = document.getElementById("formUsuario");
        const btnEditar = document.getElementById("btnEditar");
        const btnSalvar = document.getElementById("btnSalvar");
        const btnCancelar = document.getElementById("btnCancelar");
        const alerta = document.getElementById("alerta");

        // Ativa edição
        btnEditar.addEventListener("click", () => {
            form.querySelectorAll("input:not([disabled])").forEach(input => input.removeAttribute("readonly"));
            btnEditar.classList.add("d-none");
            btnSalvar.classList.remove("d-none");
            btnCancelar.classList.remove("d-none");

            // Habilita máscaras dinâmicas
            document.getElementById("telefone").addEventListener("input", (e) => {
                e.target.value = mascaraTelefone(e.target.value);
            });
        });

        // Cancelar edição
        btnCancelar.addEventListener("click", () => {
            window.location.reload();
        });

        // Validação antes de salvar
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            const telefone = document.getElementById("telefone").value.trim();
            const dataNasc = document.getElementById("data_nascimento").value;
            const hoje = new Date();
            const nascimento = new Date(dataNasc);
            const idade = hoje.getFullYear() - nascimento.getFullYear() - ((hoje < new Date(hoje.getFullYear(),
                nascimento.getMonth(), nascimento.getDate())) ? 1 : 0);

            const telNumeros = telefone.replace(/\D/g, "");
            if (telNumeros.length < 10 || telNumeros.length > 11) {
                showAlert("❌ Telefone inválido. Use um número com DDD.", "erro");
                return;
            }

            if (idade < 18) {
                showAlert("⚠️ Você deve ter pelo menos 18 anos para atualizar seus dados.", "erro");
                return;
            }

            const formData = new FormData(form);
            let resumo = "";
            for (const [campo, valor] of formData.entries()) {
                resumo += `• ${campo}: ${valor}\n`;
            }

            if (confirm("Tem certeza que deseja salvar as alterações?\n\nAlterações:\n" + resumo)) {
                form.submit();
            }
        });

        // Alerta visual
        function showAlert(msg, status) {
            alerta.classList.remove("d-none", "alert-success", "alert-danger");
            alerta.classList.add("alert", status === "sucesso" ? "alert-success" : "alert-danger");
            alerta.innerHTML = msg;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>

    <?php if ($status && $msg): ?>
    <script>
        showAlert("<?= addslashes($msg) ?>", "<?= $status ?>");
        if ("<?= $status ?>" === "sucesso") {
            setTimeout(() => {
                alerta.innerHTML += `
        <div class="mt-2 d-flex justify-content-center align-items-center">
            <div class="spinner-border text-dark me-2" role="status"></div>
            <span>Atualizando página...</span>
        </div>`;
            }, 2000);
            setTimeout(() => window.location.href = "info_usuario.php", 3500);
        }
    </script>
    <?php endif; ?>

    <!-- Bootstrap JS and Popper for 5.2.1 -->
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
                minLength: 1,
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>