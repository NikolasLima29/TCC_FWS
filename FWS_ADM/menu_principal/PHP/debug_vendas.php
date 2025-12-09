<?php
$sql = new mysqli("localhost","root","","FWS");
$sql->set_charset("utf8");
$res = $sql->query("SELECT * FROM vendas ORDER BY data_criacao DESC LIMIT 5");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo '<pre>';
        print_r($row);
        echo '</pre><hr>';
    }
} else {
    echo 'Nenhuma venda encontrada.';
}
?>