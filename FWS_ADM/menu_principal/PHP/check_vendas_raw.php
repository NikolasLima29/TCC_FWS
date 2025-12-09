<?php
header('Content-Type: application/json; charset=utf-8');

$sql = new mysqli("localhost", "root", "", "FWS");
$sql->set_charset("utf8");

if ($sql->connect_error) {
    die(json_encode(['error' => 'Erro conexão: ' . $sql->connect_error]));
}

// Verificar quantas vendas existem
$count = $sql->query("SELECT COUNT(*) as cnt FROM vendas");
$countRow = $count->fetch_assoc();

// Buscar as 5 últimas vendas com todos os dados
$result = $sql->query("
    SELECT v.id, v.data_criacao, v.total, v.metodo_pagamento, v.usuario_id, u.nome as cliente
    FROM vendas v
    LEFT JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.data_criacao DESC
    LIMIT 5
");

$vendas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vendas[] = $row;
    }
}

echo json_encode([
    'total_vendas' => $countRow['cnt'],
    'ultimas_5' => $vendas,
    'estrutura_tabelas' => [
        'vendas_colunas' => $sql->query("DESCRIBE vendas")->fetch_all(MYSQLI_ASSOC),
        'usuarios_colunas' => $sql->query("DESCRIBE usuarios")->fetch_all(MYSQLI_ASSOC)
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$sql->close();
?>
