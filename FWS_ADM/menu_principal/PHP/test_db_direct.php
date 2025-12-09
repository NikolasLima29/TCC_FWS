<?php
// Desabilitar redirecionamento automático
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Testar conexão diretamente
$conn = new mysqli("localhost","root","","FWS");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    echo json_encode([
        'success' => false, 
        'error' => 'Conexão falhou: ' . $conn->connect_error,
        'errno' => $conn->connect_errno
    ]);
    exit;
}

// Testar query básica
$result = $conn->query('SELECT COUNT(*) as total FROM produtos');
if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Query falhou: ' . $conn->error
    ]);
    exit;
}

$row = $result->fetch_assoc();
echo json_encode([
    'success' => true,
    'message' => 'Banco conectado com sucesso',
    'produtos_total' => intval($row['total'])
]);
?>
