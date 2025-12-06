<?php
session_start();

// Se não houver usuário logado, redireciona para login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html");
    exit;
}

// Nome do funcionário
$nomeFuncionario = isset($_SESSION['usuario_nome_ADM']) ? $_SESSION['usuario_nome_ADM'] : 'Usuário';

// Se confirmou logout via GET
if (isset($_GET['confirm']) && $_GET['confirm'] === 'sim') {
    session_unset();
    session_destroy();
    header("Location: ../../index.html?status=logout&msg=Saiu com sucesso!");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Logout</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* Fundo semi-transparente */
.modal-fundo {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Caixa do modal */
.modal-box {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    max-width: 400px;
    text-align: center;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
    animation: popup 0.3s ease-out;
}

@keyframes popup {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.modal-box h2 {
    color: #d11b1b;
    margin-bottom: 20px;
}

.modal-box button {
    margin: 10px;
    width: 120px;
    font-weight: bold;
}

.btn-sim {
    background-color: #28a745;
    color: #fff;
    border: none;
}

.btn-nao {
    background-color: #606060;
    color: #fff;
    border: none;
}
</style>
</head>
<body>

<div class="modal-fundo">
    <div class="modal-box">
        <h2>Deseja realmente sair, <?= htmlspecialchars($nomeFuncionario) ?>?</h2>
        <div>
            <button class="btn btn-sim" onclick="sair()">Sim</button>
            <button class="btn btn-nao" onclick="cancelar()">Cancelar</button>
        </div>
    </div>
</div>

<script>
function sair() {
    window.location.href = "logout.php?confirm=sim";
}

function cancelar() {
    // Redireciona para a página anterior ou para o menu principal
    const voltar = document.referrer || "/Fws/FWS_ADM/menu_principal/HTML/menu_principal1.html";
    window.location.href = voltar;
}
</script>

</body>
</html>
