<?php
// Desabilitar exibição de erros
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Header JSON obrigatório
header('Content-Type: application/json; charset=utf-8');

// Conectar ao banco de dados
include '../../../conn.php';

if (!$conn) {
    die(json_encode(['success' => false, 'error' => 'Erro ao conectar com banco de dados']));
}

// Teste de consulta simples ao banco
echo json_encode([
    'success' => true,
    'message' => 'Conexão com banco OK',
    'test_query' => 'SELECT COUNT(*) FROM produtos',
]);

$result = $conn->query('SELECT COUNT(*) as total FROM produtos');
if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['produtos_count' => $row['total']]);
}
?>
