<?php
session_start();
include "FWS_Cliente/conn.php";

$result = $conn->query("SELECT id, data_criacao, situacao_compra FROM vendas WHERE HOUR(data_criacao) = 10 AND MINUTE(data_criacao) = 22 LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . "\n";
    echo "Data: " . $row['data_criacao'] . "\n";
    echo "Status: " . $row['situacao_compra'] . "\n";
} else {
    echo "Nenhum pedido encontrado nesse horário\n";
}

// Mostrar todos os pedidos para referência
echo "\n\n=== TODOS OS PEDIDOS ===\n";
$result = $conn->query("SELECT id, data_criacao, situacao_compra FROM vendas ORDER BY data_criacao DESC LIMIT 10");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Data: {$row['data_criacao']}, Status: {$row['situacao_compra']}\n";
}

$conn->close();
?>
