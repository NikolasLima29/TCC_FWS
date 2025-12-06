<?php
// ARQUIVO: cadastrar_funcionario.php
include "../../conn.php";
session_start();

/* ==========================================
   AUTENTICAÇÃO ADM
========================================== */
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

/* ==========================================
   BUSCAR DADOS ADM PARA NAVBAR
========================================== */
$adm_id = $_SESSION['usuario_id_ADM'];
$stmtAdm = $sql->prepare("SELECT nome, CPF, email, nivel_permissao FROM funcionarios WHERE id = ?");
$stmtAdm->bind_param("i", $adm_id);
$stmtAdm->execute();
$stmtAdm->bind_result($adm_nome, $adm_cpf, $adm_email, $adm_nivel);
$stmtAdm->fetch();
$stmtAdm->close();



/* ==========================================
   PROCESSAR CADASTRO
========================================== */
$mensagem = "";
$erro_msg = "";
$alertas_form = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $nome  = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $email = trim($_POST['email']);
    $nivel = intval($_POST['nivel']);

    if (empty($nome) || empty($cpf) || empty($email) || empty($nivel)) {
        $alertas_form[] = "Preencha todos os campos obrigatórios.";
    } else {
        // CPF duplicado
        $chk = $sql->prepare("SELECT id FROM funcionarios WHERE CPF = ? LIMIT 1");
        $chk->bind_param("s", $cpf);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) { $alertas_form[] = "CPF já cadastrado."; }
        $chk->close();

        // Email duplicado
        $chk2 = $sql->prepare("SELECT id FROM funcionarios WHERE email = ? LIMIT 1");
        $chk2->bind_param("s", $email);
        $chk2->execute();
        $chk2->store_result();
        if ($chk2->num_rows > 0) { $alertas_form[] = "E-mail já existe."; }
        $chk2->close();
    }

    if (count($alertas_form) === 0) {
        $senha = "";

        $stmt = $sql->prepare("
            INSERT INTO funcionarios (nome, CPF, email, nivel_permissao, senha)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssis", $nome, $cpf, $email, $nivel, $senha);

        if ($stmt->execute()) {
            $novo_id = $stmt->insert_id;
            $stmt->close();

            $_SESSION['novo_funcionario'] = [
                'id' => $novo_id,
                'nome' => $nome,
                'cpf' => $cpf,
                'email' => $email,
                'nivel' => $nivel
            ];

            $mensagem = "sucesso";
        } else {
            $mensagem = "erro";
            $erro_msg = $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Funcionário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <style>
    /* NAVBAR */
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
    }

    #fund {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background-color: black !important;
        overflow-y: auto;
    }

    #menu {
        background-color: black;
    }

    #cor-fonte {
        color: #ff9100;
        font-size: 23px;
        padding-bottom: 30px;
    }

    #cor-fonte:hover {
        background-color: #f4a21d67 !important;
    }

    #cor-fonte img {
        width: 44px;
    }

    #logo-linha img {
        width: 170px;
    }

    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container-form {
        max-width: 650px;
        margin: 50px auto;
        background: white;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
    }

    h2.form-title {
        text-align: center;
        color: #ff9100;
        margin-bottom: 30px;
        font-weight: 900;
    }

    /* ======= BOTÃO ======= */
    .btn-cadastrar {
        background-color: #ff9100;
        color: black;
        font-weight: 700;
        border: none;
        width: 100%;
        padding: 12px;
        font-size: 18px;
        border-radius: 10px;
    }

    .btn-cadastrar:hover {
        background-color: #ff9e22;
    }
    .btn-voltar {
        margin-bottom: 20px;
        background-color: #d11b1b;
        color: white;
        font-weight: bold;
    }
    .btn-voltar:hover { 
        background-color: #a00f0f; 
    }


    


    /* ======= MODAL ======= */
    .modal-fundo {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.65);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999999;
    }

    .modal-box {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        width: 420px;
        text-align: center;
        box-shadow: 0 6px 30px rgba(0, 0, 0, 0.25);
    }

    /* === SENHA DESTACADA (NOVO) === */
    #senhaBox {
        font-size: 30px;
        font-weight: 900;
        color: #1a4d8f;
        background: #e6f0ff;
        padding: 12px 20px;
        border-radius: 10px;
        margin: 12px auto 22px;
        letter-spacing: 2px;
        font-family: "Courier New", monospace;
        border: 3px solid #1a4d8f;
        width: fit-content;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- NAVBAR -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">

                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo"></li>

                        <li class="nav-item">
                            <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.html"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png" alt="Painel Geral">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png" alt="Fast Service">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png" alt="Financeiro">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png" alt="Vendas">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png" alt="Estoque">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png" alt="Produtos">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png" alt="Fornecedores">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png" alt="Funcionários">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a></li>
                    </ul>

                    <hr>

                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <img src="../../fotodeperfiladm.png" width="30" height="30" class="rounded-circle" alt="Foto Perfil">
                            <span class="d-none d-sm-inline mx-1"><?= htmlspecialchars($adm_nome) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col py-3" id="conteudo-principal">

                <div class="container-form">
                     <a href="javascript:history.back()" class="btn btn-voltar">← Voltar</a>
                    <h2 class="form-title">Cadastrar Funcionário</h2>

                    <?php if (!empty($alertas_form)): ?>
                    <?php foreach ($alertas_form as $a): ?>
                    <div class="alert alert-warning"><?= htmlspecialchars($a) ?></div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($mensagem === "erro"): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erro_msg) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <label>Nome *</label>
                        <input class="form-control" name="nome" required>

                        <label class="mt-3">CPF *</label>
                        <input class="form-control" name="cpf" id="cpf" required>

                        <label class="mt-3">Email *</label>
                        <input class="form-control" name="email" required>

                        <label class="mt-3">Nível *</label>
                        <select class="form-control" name="nivel" required>
                            <option value="">Selecione</option>
                            <option value="1">Atendente</option>
                            <option value="2">Gerente</option>
                        </select>

                        <button class="btn-cadastrar mt-4">Cadastrar</button>
                    </form>
                </div>

            </div>
        </div>
    </div>


    <!-- ================== SCRIPTS ================== -->
    <script>
    $(document).ready(function() {
        $("#cpf").mask("000.000.000-00");

        <?php if ($mensagem === "sucesso"): ?>

        /* ==========================
           MODAL 1 — Criar senha?
        ========================== */
        const modal1 = document.createElement("div");
        modal1.className = "modal-fundo";
        modal1.innerHTML = `
                <div class="modal-box">
                    <h4>Funcionário cadastrado!</h4>
                    <p>Deseja criar uma senha agora?</p>
                    <button id="m1sim" class="btn btn-success w-100 mt-2">Sim</button>
                    <button id="m1nao" class="btn btn-danger w-100 mt-2">Não</button>
                </div>
                `;
        document.body.appendChild(modal1);

        document.getElementById("m1sim").onclick = () => window.location.href = "criar_senha.php";

        document.getElementById("m1nao").onclick = () => {

            modal1.remove();

            function gerarSenha() {
                const esp = "!@#$%&*?";
                const mai = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                const min = "abcdefghijklmnopqrstuvwxyz";
                const num = "0123456789";

                let s = [
                    mai[Math.floor(Math.random() * mai.length)],
                    min[Math.floor(Math.random() * min.length)],
                    num[Math.floor(Math.random() * num.length)],
                    esp[Math.floor(Math.random() * esp.length)]
                ];

                const all = mai + min + num + esp;
                while (s.length < 6) {
                    s.push(all[Math.floor(Math.random() * all.length)]);
                }
                return s.sort(() => Math.random() - 0.5).join("");
            }

            const senha = gerarSenha();

            const modal2 = document.createElement("div");
            modal2.className = "modal-fundo";
            modal2.innerHTML = `
                    <div class="modal-box">
                        <h4>Senha pré-definida</h4>

                        <div id="senhaBox">${senha}</div>

                        <p>Deseja entrar no painel com esta conta?</p>

                        <button id="m2sim" class="btn btn-success w-100 mt-2">Entrar</button>
                        <button id="m2nao" class="btn btn-secondary w-100 mt-2">Cancelar</button>
                    </div>
                    `;
            document.body.appendChild(modal2);

            document.getElementById("m2nao").onclick = () => {

    fetch("salvar_senha_funcionario.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            senha_auto: senha // ← A senha automática gerada no JS
        })
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.status === "ok") {
            modal2.remove();
            alert("Senha automática salva com sucesso!");
        } else {
            alert("Erro ao salvar senha: " + resp.msg);
        }
    });
};


            document.getElementById("m2sim").onclick = () => {
                fetch("logar_novo_funcionario.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            senha_auto: senha
                        })
                    })
                    .then(r => r.json())
                    .then(resp => {
                        if (resp.status === "ok") {
                            window.location.href =
                                "/fws/FWS_ADM/menu_principal/HTML/menu_principal1.html";
                        } else {
                            alert("Erro ao logar: " + resp.msg);
                        }
                    });
            }
        };

        <?php endif; ?>
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
