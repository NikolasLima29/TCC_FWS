<?php
session_start();
include "../../conn.php";

// 1 — Verifica login
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit;
}

$userId = (int)$_SESSION['usuario_id'];
$id_produto = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;

// -----------------------------------------------------------------------------------
// 2 — MODO DE VERIFICAÇÃO (antes de qualquer coisa)
// -----------------------------------------------------------------------------------
if (isset($_POST['verificar_limite'])) {

    // Busca estoque
    $q = mysqli_query($conn, "SELECT estoque FROM produtos WHERE id=$id_produto");
    if (!($row = mysqli_fetch_assoc($q))) {
        echo json_encode(["erro" => "produto_nao_existe"]);
        exit;
    }

    $estoque = (int)$row['estoque'];

    // Aplica regra de limite
    if ($estoque <= 1) {
        $limite = 0;
    } elseif ($estoque <= 9) {
        $limite = floor($estoque / 2);
    } elseif ($estoque == 10) {
        $limite = 5;
    } else {
        $limite = 10;
    }

    // Busca quantidade atual no carrinho
    $q2 = mysqli_query($conn, "
        SELECT quantidade 
        FROM carrinho 
        WHERE usuario_id=$userId AND produto_id=$id_produto
    ");

    $no_carrinho = 0;
    if ($r2 = mysqli_fetch_assoc($q2)) {
        $no_carrinho = (int)$r2['quantidade'];
    }

    $restante = max(0, $limite - $no_carrinho);

    echo json_encode([
        "limite" => $limite,
        "no_carrinho" => $no_carrinho,
        "restante" => $restante
    ]);
    exit;
}

// -----------------------------------------------------------------------------------
// 3 — Fluxo normal: ADICIONAR AO CARRINHO
// -----------------------------------------------------------------------------------

$qtd = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
$qtd = max(1, min(10, $qtd));

// Busca preço e estoque real
$qry = mysqli_query($conn, "SELECT preco_venda, estoque FROM produtos WHERE id = $id_produto");
if (!($row = mysqli_fetch_assoc($qry))) {
    http_response_code(400);
    echo "erro: produto_inexistente";
    exit;
}

$preco = $row['preco_venda'];
$estoque = (int)$row['estoque'];

// Estoque mínimo
if ($estoque <= 1) {
    http_response_code(409);
    echo "erro: estoque_insuficiente";
    exit;
}

// Regra de limite
if ($estoque <= 9) {
    $limite = floor($estoque / 2);
} elseif ($estoque == 10) {
    $limite = 5;
} else {
    $limite = 10;
}

if ($limite < 1) {
    http_response_code(409);
    echo "erro: estoque_insuficiente";
    exit;
}

$qtd = min($qtd, $limite);

// Já existe no carrinho?
$qAtual = mysqli_query($conn, "
    SELECT quantidade 
    FROM carrinho 
    WHERE usuario_id = $userId AND produto_id = $id_produto
");

if ($rowCarrinho = mysqli_fetch_assoc($qAtual)) {
    $quantidade_atual = (int)$rowCarrinho['quantidade'];
    $nova_quantidade = min($quantidade_atual + $qtd, $limite);

    $sql = "
        UPDATE carrinho 
        SET quantidade = $nova_quantidade 
        WHERE usuario_id = $userId AND produto_id = $id_produto
    ";
} else {
    $sql = "
        INSERT INTO carrinho (usuario_id, produto_id, quantidade, preco_unitario)
        VALUES ($userId, $id_produto, $qtd, $preco)
    ";
}

mysqli_query($conn, $sql) or die("erro_sql");

echo "OK";
?>
