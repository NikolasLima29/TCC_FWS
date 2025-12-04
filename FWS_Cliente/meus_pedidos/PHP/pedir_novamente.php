<?php
session_start();
include "../../conn.php";

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(["erro" => "login"]);
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itens'])) {
    $itens = json_decode($_POST['itens'], true);
    if (!is_array($itens)) {
        echo json_encode(["erro" => "dados_invalidos"]);
        exit;
    }
    $adicionados = [];
    foreach ($itens as $item) {
        $produto_id = (int)$item['produto_id'];
        $quantidade = (int)$item['quantidade'];
        if ($produto_id > 0 && $quantidade > 0) {
            // Busca preço atual do produto
            $q = $conn->query("SELECT preco_venda FROM produtos WHERE id = $produto_id");
            if ($row = $q->fetch_assoc()) {
                $preco = $row['preco_venda'];
                // Se já existe no carrinho, atualiza quantidade
                $q2 = $conn->query("SELECT quantidade FROM carrinho WHERE usuario_id = $usuario_id AND produto_id = $produto_id");
                if ($row2 = $q2->fetch_assoc()) {
                    $nova_qtd = $row2['quantidade'] + $quantidade;
                    $conn->query("UPDATE carrinho SET quantidade = $nova_qtd, preco_unitario = $preco WHERE usuario_id = $usuario_id AND produto_id = $produto_id");
                } else {
                    $conn->query("INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario) VALUES ($usuario_id, $produto_id, $quantidade, $preco)");
                }
                $adicionados[] = $produto_id;
            }
        }
    }
    echo json_encode(["ok" => true, "adicionados" => $adicionados]);
    exit;
}
echo json_encode(["erro" => "requisicao_invalida"]);
