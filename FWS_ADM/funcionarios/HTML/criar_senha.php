<?php
include "../../conn.php";
session_start();

/* =====================================================
   Verifica se existe um funcionário recém cadastrado
===================================================== */
if (!isset($_SESSION['novo_funcionario'])) {
    header("Location: ../funcionarios/HTML/lista_funcionarios.php?status=erro&msg=Nenhum funcionário selecionado");
    exit;
}

$func = $_SESSION['novo_funcionario'];
$func_id = $func['id'];

/* =====================================================
   PROCESSAR FORMULÁRIO
===================================================== */
$erro = "";
$sucesso = false;
$senha_final = ""; // <-- ADICIONADO: vai guardar a senha correta

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha = trim($_POST["senha"] ?? "");
    $confirmar = trim($_POST["confirmar"] ?? "");

    // Critérios
    $maiuscula = preg_match('/[A-Z]/', $senha);
    $minuscula = preg_match('/[a-z]/', $senha);
    $numero    = preg_match('/[0-9]/', $senha);
    $especial  = preg_match('/[\W_]/', $senha);

    if (strlen($senha) < 6) {
        $erro = "A senha deve ter no mínimo 6 caracteres.";
    } elseif (!$maiuscula) {
        $erro = "A senha deve conter pelo menos 1 letra maiúscula.";
    } elseif (!$minuscula) {
        $erro = "A senha deve conter pelo menos 1 letra minúscula.";
    } elseif (!$numero) {
        $erro = "A senha deve conter pelo menos 1 número.";
    } elseif (!$especial) {
        $erro = "A senha deve conter pelo menos 1 caractere especial.";
    } elseif ($senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } else {

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $sql->prepare("UPDATE funcionarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $func_id);

        if ($stmt->execute()) {
            $sucesso = true;
            $senha_final = $senha; // <-- ADICIONADO: guarda a senha real para enviar no fetch
        } else {
            $erro = "Erro ao salvar senha: " . $stmt->error;
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Senha</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
      <link rel="stylesheet" href="../../menu_principal/CSS/menu_principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container-box {
        width: 420px;
        background: white;
        padding: 35px;
        border-radius: 15px;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
    }

    h2 {
        text-align: center;
        color: #d11b1b;
        margin-bottom: 25px;
        font-weight: bold;
    }

    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 2px solid #f4a01d;
        border-radius: 7px;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .btn-primary {
        background-color: #f4a01d;
        border: none;
        color: black;
        font-weight: bold;
        width: 100%;
        padding: 10px;
        transition: .3s;
        border-radius: 7px;
        font-size: 17px;
    }

    .btn-primary:hover {
        background-color: #d68c19;
        color: white;
    }

    /* Modal */
    .modal-fundo {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.6);
        z-index: 9999;
    }

    .modal-box {
        background: white;
        padding: 30px;
        width: 350px;
        text-align: center;
        border-radius: 12px;
    }

    .modal-box button {
        width: 100%;
        margin-top: 8px;
    }
    </style>
</head>

<body>

    <div class="container-box">

        <h2>Criar Senha</h2>

        <?php if ($erro): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>

        <form method="POST" id="formSenha">

            <label>Nova Senha *</label>
            <input type="password" name="senha" id="senha" required>

            <label>Confirmar Senha *</label>
            <input type="password" name="confirmar" id="confirmar" required>

            <button class="btn btn-primary mt-2">Salvar Senha</button>
        </form>

        <?php endif; ?>

    </div>


    <!-- MODAL FINAL -->
    <?php if ($sucesso): ?>
    <div class="modal-fundo">
        <div class="modal-box">
            <h4>Senha criada com sucesso!</h4>
            <p>Deseja entrar no sistema com a conta do funcionário?</p>

            <button class="btn btn-success" id="btnSim">Sim, entrar</button>
            <button class="btn btn-secondary" id="btnNao">Continuar como ADM</button>
        </div>
    </div>

    <script>
    document.getElementById("btnSim").onclick = function() {
        fetch("logar_novo_funcionario.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    senha_auto: "<?= $senha_final ?>" // <-- CORRIGIDO: envia a senha REAL
                })
            })
            .then(r => r.json())
            .then(resp => {
                if (resp.status === "ok") {
                    window.location.href = "/fws/FWS_ADM/menu_principal/HTML/menu_principal1.html";
                } else {
                    alert("Erro ao logar: " + resp.msg);
                }
            });
    };

    document.getElementById("btnNao").onclick = function() {
        window.location.href = "/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php";
    };
    </script>
    <?php endif; ?>


    <!-- VALIDAÇÃO EM TEMPO REAL -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const form = document.getElementById("formSenha");
        if (!form) return;

        form.addEventListener("submit", function(e) {
            const senha = document.getElementById("senha").value;
            const confirmar = document.getElementById("confirmar").value;

            let erros = [];

            if (senha.length < 6) erros.push("A senha deve ter no mínimo 6 caracteres.");
            if (!/[A-Z]/.test(senha)) erros.push("A senha deve conter ao menos 1 letra maiúscula.");
            if (!/[a-z]/.test(senha)) erros.push("A senha deve conter ao menos 1 letra minúscula.");
            if (!/[0-9]/.test(senha)) erros.push("A senha deve conter ao menos 1 número.");
            if (!/[\W_]/.test(senha)) erros.push("A senha deve conter ao menos 1 caractere especial.");
            if (senha !== confirmar) erros.push("As senhas não coincidem.");

            if (erros.length > 0) {
                e.preventDefault();

                let alertBox = document.querySelector(".alert-danger");

                if (!alertBox) {
                    alertBox = document.createElement("div");
                    alertBox.classList.add("alert", "alert-danger", "mt-2");
                    form.parentNode.insertBefore(alertBox, form);
                }

                alertBox.innerHTML = erros.join("<br>");
            }
        });
    });
    </script>

</body>

</html>
