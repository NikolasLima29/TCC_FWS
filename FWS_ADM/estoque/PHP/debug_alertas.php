<?php
date_default_timezone_set('America/Sao_Paulo');
require '../../conn.php';

echo "<h2>Debug - Alertas de Estoque</h2>";

// Verifica estoque baixo
echo "<h3>1. Estoque Baixo (< 15)</h3>";
$sqli_baixo = "SELECT id, nome, estoque FROM produtos WHERE estoque < 15 ORDER BY estoque ASC LIMIT 5";
$result_baixo = $sql->query($sqli_baixo);
if($result_baixo->num_rows > 0) {
    while($row = $result_baixo->fetch_assoc()) {
        echo "✓ " . $row['nome'] . " - Estoque: " . $row['estoque'] . "<br>";
    }
} else {
    echo "Nenhum produto com estoque baixo<br>";
}

// Verifica validade próxima (10 dias)
echo "<h3>2. Validade Próxima (hoje até 10 dias)</h3>";
$sqli_validade = "SELECT DISTINCT p.id, p.nome, lp.validade FROM produtos p
                  LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
                  WHERE lp.validade IS NOT NULL 
                  AND lp.validade <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)
                  AND lp.validade >= CURDATE()
                  ORDER BY lp.validade ASC";
$result_validade = $sql->query($sqli_validade);
echo "CURDATE(): " . date('Y-m-d') . "<br>";
echo "Procurando: validade >= " . date('Y-m-d') . " AND validade <= " . date('Y-m-d', strtotime('+10 days')) . "<br><br>";
if($result_validade->num_rows > 0) {
    while($row = $result_validade->fetch_assoc()) {
        echo "✓ " . $row['nome'] . " - Validade: " . $row['validade'] . "<br>";
    }
} else {
    echo "Nenhum produto vencendo nos próximos 10 dias<br>";
}

// Verifica vencidos
echo "<h3>3. Produtos Vencidos</h3>";
$sqli_vencidos = "SELECT DISTINCT p.id, p.nome, lp.validade FROM produtos p
                  LEFT JOIN lotes_produtos lp ON p.id = lp.produto_id
                  WHERE lp.validade IS NOT NULL 
                  AND lp.validade < CURDATE()
                  ORDER BY lp.validade DESC";
$result_vencidos = $sql->query($sqli_vencidos);
echo "Procurando: validade < " . date('Y-m-d') . "<br><br>";
if($result_vencidos->num_rows > 0) {
    while($row = $result_vencidos->fetch_assoc()) {
        echo "✓ " . $row['nome'] . " - Validade: " . $row['validade'] . "<br>";
    }
} else {
    echo "Nenhum produto vencido<br>";
}

?>
