<?php
date_default_timezone_set('America/Sao_Paulo');
require '../../conn.php';

echo "<h2>Debug - BARRA PROTEICA</h2>";

$sql_check = "SELECT p.id, p.nome, lp.validade, lp.id as lote_id 
              FROM produtos p
              LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
              WHERE p.nome LIKE '%BARRA PROTEICA%'
              ORDER BY lp.validade DESC";

$result = $sql->query($sql_check);

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Produto: " . $row['nome'] . "<br>";
        echo "Lote ID: " . $row['lote_id'] . "<br>";
        echo "Validade: " . $row['validade'] . "<br>";
        echo "---<br>";
    }
} else {
    echo "Produto não encontrado";
}

echo "<br><br><strong>CURDATE() = " . date('Y-m-d') . "</strong>";

?>
