<?php
    $sql =  new mysqli("localhost","root","","FWS");
    $sql->set_charset("utf8");

    if ($sql->connect_error) {
        // Erro na conexão — logar e mostrar mensagem genérica
        error_log("Erro na conexão com o banco: " . $sql->connect_error);
        header("Location: ../../index.html?status=erro&msg=Erro interno. Tente novamente.");
        exit;
    }

?>