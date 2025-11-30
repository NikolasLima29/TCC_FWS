<?php
session_start();                        
include "../../conn.php";                
// Se não existir usuário logado, retorna código 403 (proibido) e encerra o script
if (!isset($_SESSION['usuario_id'])) {
  http_response_code(403); exit;
}

// Garante que o ID do usuário é um inteiro (evita injeção e erros de tipo)
$userId = (int)$_SESSION['usuario_id'];

// Pega o id do produto enviado via POST, convertendo para inteiro; se não vier, assume 0
$id_produto = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;

// Pega a quantidade enviada via POST, converte para inteiro, default = 1, 
// depois limita o valor mínimo em 1 e máximo em 10
$qtd = max(1, min(10, (int)($_POST['quantidade'] ?? 1)));

// Busca o preço de venda do produto no banco pelo ID informado
$qry = mysqli_query($conn,"SELECT preco_venda FROM produtos WHERE id=$id_produto");

// Se não encontrar o produto (consulta sem resultado), devolve 400 (requisição inválida) e encerra
if (!($row = mysqli_fetch_assoc($qry))) { http_response_code(400); exit; }

// Armazena o preço de venda do produto retornado do banco
$preco = $row['preco_venda'];

// Monta o SQL para inserir o item no carrinho.
// Se já existir um registro com a mesma chave (ex.: UNIQUE em usuario_id + produto_id),
// o ON DUPLICATE KEY UPDATE soma a quantidade atual com a nova quantidade,
// mas nunca deixando passar de 10 (LEAST(quantidade+$qtd,10)).
$sql = "INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario)
  VALUES ($userId, $id_produto, $qtd, $preco)
  ON DUPLICATE KEY UPDATE quantidade = LEAST(quantidade+$qtd,10)";

// Executa o comando SQL; em caso de erro, interrompe e mostra a mensagem
mysqli_query($conn, $sql) or die("Erro ao inserir no carrinho");

// Se deu tudo certo, imprime "OK" para o AJAX/front saber que funcionou
echo "OK";
?>
