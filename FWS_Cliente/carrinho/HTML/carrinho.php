<?php
session_start();
include "../../conn.php"; // conexão com o banco

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$desconto = 0;
$codigo_cupom = "";
$total_final = 0;

// Aplicar cupom se enviado
if (isset($_POST['aplicar_cupom'])) {
    $codigo_cupom = trim($_POST['codigo_cupom']);
    $cupom_sql = "SELECT * FROM cupom WHERE nome = ?";
    $stmt = $conn->prepare($cupom_sql);
    $stmt->bind_param("s", $codigo_cupom);
    $stmt->execute();
    $cupom = $stmt->get_result()->fetch_assoc();

    if ($cupom) {
        $desconto = $cupom['desconto'];
    } else {
        $erro_cupom = "Cupom inválido.";
    }
}

// Atualizar quantidades
if (isset($_POST['update_quantidade'])) {
    $produto_id = $_POST['produto_id'];
    $nova_qtd = $_POST['quantidade'];

    $estoque = $conn->query("SELECT estoque FROM produtos WHERE id = $produto_id")->fetch_assoc()['estoque'];

    if ($nova_qtd > 0 && $nova_qtd <= $estoque) {
        $conn->query("UPDATE carrinho SET quantidade = $nova_qtd WHERE usuario_id = $usuario_id AND produto_id = $produto_id");
    }
}

// Remover produto do carrinho
if (isset($_POST['remover_item'])) {
    $produto_id = $_POST['produto_id'];
    $conn->query("DELETE FROM carrinho WHERE usuario_id = $usuario_id AND produto_id = $produto_id");
}

// Carregar itens do carrinho
$sql = "
SELECT c.produto_id, c.quantidade, c.preco_unitario, p.nome, p.foto_produto, p.estoque 
FROM carrinho c
JOIN produtos p ON c.produto_id = p.id
WHERE c.usuario_id = $usuario_id
";
$itens = $conn->query($sql);

$subtotal = 0;
foreach ($itens as $item) {
    $subtotal += $item['preco_unitario'] * $item['quantidade'];
}
$total_final = $subtotal;

if ($desconto > 0) {
    $total_final = $subtotal - ($subtotal * ($desconto / 100));
}

// Reservar pedido
if (isset($_POST['reservar'])) {
    $tempo_reserva = $_POST['tempo_reserva'];
    if ($tempo_reserva == "15") $tempo_chegada = "00:15:00";
    elseif ($tempo_reserva == "30") $tempo_chegada = "00:30:00";
    elseif ($tempo_reserva == "45") $tempo_chegada = "00:45:00";
    else $tempo_chegada = "01:00:00";

    // Buscar funcionário ativo
    $func = $conn->query("SELECT id FROM funcionarios WHERE ativo = 1 LIMIT 1")->fetch_assoc();
    $func_id = $func ? $func['id'] : 1;

    // Criar venda
    $stmt = $conn->prepare("INSERT INTO vendas (funcionario_id, usuario_id, total, tempo_chegada) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $func_id, $usuario_id, $total_final, $tempo_chegada);
    $stmt->execute();
    $venda_id = $conn->insert_id;

    // Inserir itens vendidos
    foreach ($itens as $item) {
        $stmt = $conn->prepare("INSERT INTO itens_vendidos (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $venda_id, $item['produto_id'], $item['quantidade'], $item['preco_unitario']);
        $stmt->execute();
    }

    // Limpar carrinho
    $conn->query("DELETE FROM carrinho WHERE usuario_id = $usuario_id");

    header("Location: reserva_confirmada.php?venda_id=$venda_id");
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
    <title>Carrinho</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/carrinho.css">
</head>
<body>

<header id="header" class="d-flex justify-content-between align-items-center p-3 bg-light">
    <div class="logo">
        <a href="../../index.php"><img src="../../index/IMG/shell_select.png" alt="logo" height="50"></a>
    </div>
    <nav>
        <ul class="nav">
            <li class="nav-item"><a class="nav-link" href="../../produto/HTML/produto.html">Produtos</a></li>
            <li class="nav-item"><a class="nav-link" href="../../tela_sobre_nos/HTML/sobre_nos.html">Sobre nós</a></li>
        </ul>
    </nav>
    <div class="carrinho">
        <img src="../../index/IMG/carrinho.png" alt="Carrinho" height="40">
    </div>
</header>

<div class="container py-5">
    <h1 class="mb-5">Seu carrinho</h1>
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <?php if ($itens->num_rows > 0): ?>
                        <?php foreach ($itens as $item): ?>
                            <form method="post" class="row align-items-center mb-4 border-bottom pb-3">
                                <div class="col-md-3 text-center">
                                    <img src="<?= $item['foto_produto'] ?>" class="img-fluid rounded" style="max-height:120px;">
                                </div>
                                <div class="col-md-4">
                                    <h5><?= $item['nome'] ?></h5>
                                    <p class="text-muted">Preço unitário: R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" name="update_quantidade" value="-" onclick="this.form.quantidade.value=Math.max(1,parseInt(this.form.quantidade.value)-1);">-</button>
                                        <input type="number" class="form-control text-center" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>">
                                        <button class="btn btn-outline-secondary" name="update_quantidade" value="+" onclick="this.form.quantidade.value=Math.min(<?= $item['estoque'] ?>,parseInt(this.form.quantidade.value)+1);">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <p><strong>R$ <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></strong></p>
                                    <input type="hidden" name="produto_id" value="<?= $item['produto_id'] ?>">
                                    <button type="submit" name="remover_item" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i> Remover
                                    </button>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">Seu carrinho está vazio.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5>Sumário do pedido</h5>
                    <div class="d-flex justify-content-between mt-3">
                        <span>Subtotal</span>
                        <strong>R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                    </div>
                    <?php if ($desconto > 0): ?>
                        <div class="d-flex justify-content-between text-success mt-2">
                            <span>Desconto (<?= $desconto ?>%)</span>
                            <strong>- R$ <?= number_format($subtotal * ($desconto / 100), 2, ',', '.') ?></strong>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Total</span>
                        <strong>R$ <?= number_format($total_final, 2, ',', '.') ?></strong>
                    </div>

                    <form method="post" class="mt-3">
                        <select name="tempo_reserva" class="form-select mb-3" required>
                            <option value="">Selecione o tempo de reserva</option>
                            <option value="15">15 minutos</option>
                            <option value="30">30 minutos</option>
                            <option value="45">45 minutos</option>
                            <option value="60">1 hora</option>
                        </select>
                        <button name="reservar" class="btn btn-primary w-100">Reservar</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5>Código de promoção</h5>
                    <form method="post" class="input-group">
                        <input type="text" name="codigo_cupom" class="form-control" placeholder="Digite o cupom">
                        <button class="btn btn-outline-secondary" name="aplicar_cupom">Aplicar</button>
                    </form>
                    <?php if (isset($erro_cupom)): ?>
                        <p class="text-danger mt-2"><?= $erro_cupom ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
