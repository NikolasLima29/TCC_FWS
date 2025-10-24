<?php
session_start();
include "../../conn.php";
if (!isset($_SESSION['usuario_id'])) {
  http_response_code(403); exit;
}
$userId = (int)$_SESSION['usuario_id'];
$id_produto = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;
$qtd = max(1, min(10, (int)($_POST['quantidade'] ?? 1)));
$qry = mysqli_query($conn,"SELECT preco_venda FROM produtos WHERE id=$id_produto");
if (!($row = mysqli_fetch_assoc($qry))) { http_response_code(400); exit; }
$preco = $row['preco_venda'];
$sql = "INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario)
  VALUES ($userId, $id_produto, $qtd, $preco)
  ON DUPLICATE KEY UPDATE quantidade = LEAST(quantidade+$qtd,10)";
mysqli_query($conn, $sql) or die("Erro ao inserir no carrinho");
echo "OK";
?>