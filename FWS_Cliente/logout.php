<?php
session_start();

// Remove o cookie de sessão (ajuste de acordo com seu domínio se precisar)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Limpa variáveis específicas da sessão relacionadas ao usuário
unset($_SESSION['usuario_id']);
unset($_SESSION['usuario_nome']);
// Se quiser apagar outras variáveis, faça aqui

// Opcional: mantem a sessão ativa, mas limpa dados - o que dá logout para o usuário
// session_regenerate_id(true);

header('Location: /TCC_FWS/FWS_Cliente/index.php');
exit;
?>
