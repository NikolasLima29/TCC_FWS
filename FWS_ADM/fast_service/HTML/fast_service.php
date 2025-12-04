<?php
include '../../conn.php';
$conn = $sql;

// Processar POST primeiro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alterar_estado'], $_POST['pedido_id'], $_POST['novo_estado'])) {
        $pedido_id = intval($_POST['pedido_id']);
        $novo_estado = $_POST['novo_estado'];
        $conn->query("UPDATE vendas SET situacao_compra = '".$conn->real_escape_string($novo_estado)."' WHERE id = $pedido_id LIMIT 1");
        header('Location: fast_service.php');
        exit;
    }
    if (isset($_POST['realizar_pagamento'], $_POST['pedido_id'])) {
        $pedido_id = intval($_POST['pedido_id']);
        // Descontar do estoque e dos lotes
        $sql_itens = "SELECT produto_id, quantidade FROM itens_vendidos WHERE venda_id = $pedido_id";
        $res_itens = $conn->query($sql_itens);
        while ($item = $res_itens->fetch_assoc()) {
            $produto_id = intval($item['produto_id']);
            $qtd = intval($item['quantidade']);
            // Desconta do estoque principal
            $conn->query("UPDATE produtos SET estoque = estoque - $qtd WHERE id = $produto_id");
            // Desconta do lote com validade mais próxima
            $lote = $conn->query("SELECT id, quantidade FROM lotes_produtos WHERE produto_id = $produto_id AND quantidade > 0 ORDER BY validade ASC LIMIT 1");
            if ($lote && $lote->num_rows > 0) {
                $lote_row = $lote->fetch_assoc();
                $lote_id = intval($lote_row['id']);
                $lote_qtd = intval($lote_row['quantidade']);
                $qtd_descontar = min($qtd, $lote_qtd);
                $conn->query("UPDATE lotes_produtos SET quantidade = quantidade - $qtd_descontar WHERE id = $lote_id");
                $qtd -= $qtd_descontar;
                // Se ainda restar quantidade, desconta dos próximos lotes
                while ($qtd > 0) {
                    $lote = $conn->query("SELECT id, quantidade FROM lotes_produtos WHERE produto_id = $produto_id AND quantidade > 0 ORDER BY validade ASC LIMIT 1");
                    if ($lote && $lote->num_rows > 0) {
                        $lote_row = $lote->fetch_assoc();
                        $lote_id = intval($lote_row['id']);
                        $lote_qtd = intval($lote_row['quantidade']);
                        $qtd_descontar = min($qtd, $lote_qtd);
                        $conn->query("UPDATE lotes_produtos SET quantidade = quantidade - $qtd_descontar WHERE id = $lote_id");
                        $qtd -= $qtd_descontar;
                    } else {
                        break;
                    }
                }
            }
        }
        $conn->query("UPDATE vendas SET situacao_compra = 'finalizada' WHERE id = $pedido_id LIMIT 1");
        header('Location: fast_service.php');
        exit;
    }
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Fast Service</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
        margin: 0;
    }
    #fund {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background-color: black !important;
        overflow-y: auto;
        z-index: 1000;
    }
    #menu {
        background-color: black;
    }
    #cor-fonte {
        color: #ff9100;
        font-size: 23px;
        padding-bottom: 30px;
    }
    #cor-fonte:hover {
        background-color: #f4a21d67 !important;
    }
    #cor-fonte img {
        width: 44px;
    }
    #logo-linha img {
        width: 170px;
    }
    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }
    .container {
        max-width: 1000px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .pedido-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        margin-bottom: 24px;
        padding: 24px 32px 18px 32px;
        font-family: inherit;
    }
    .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    .pedido-status {
        font-weight: 700;
        font-size: 1rem;
        border-radius: 7px;
        padding: 7px 16px;
        margin-left: 12px;
        background: #FFD100;
        color: #111;
        display: inline-block;
    }
    .pedido-info {
        display: flex;
        gap: 32px;
        margin-bottom: 10px;
    }
    .pedido-label {
        color: #c40000;
        font-weight: 600;
        margin-right: 6px;
    }
    .pedido-actions {
        margin-top: 12px;
        display: flex;
        gap: 10px;
    }
    .btn-acoes {
        background-color: #f37a27;
        color: white !important;
        border-radius: 6px;
        padding: 6px 10px;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-acoes:hover {
        opacity: 0.9;
    }
    @import url('../../Fonte_Config/fonte_geral.css');
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Barra lateral -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo"></li>
                        <li class="nav-item">
                            <a href="../../menu_principal/HTML/menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>
                        <li>
                            <a href="/TCC_FWS/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a>
                        </li>
                        <li>
                            <a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a>
                        </li>
                        <li>
                            <a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a>
                        </li>
                        <li>
                            <a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/listar_fornecedores.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://github.com/mdo.png" alt="usuário" width="30" height="30" class="rounded-circle">
                            <span class="d-none d-sm-inline mx-1">Usuário</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Sair da conta</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Conteúdo principal -->
            <div class="col py-3">
                <div class="container" style="max-width:1000px; margin-left:375px;">
                    <h3 id="texto">Fast Service</h3>
                    <hr>
                    <h4 class="mt-4 mb-3">Últimos Pedidos</h4>

                    <?php
                    // Buscar pedidos
                    $sql_pedidos = "SELECT v.*, u.telefone, u.nome FROM vendas v INNER JOIN usuarios u ON v.usuario_id = u.id ORDER BY v.data_criacao DESC LIMIT 20";
                    $res_pedidos = $conn->query($sql_pedidos);
                    $status_map = [
                        'em_preparo' => ['Em preparo', '#FFD100', '#111'],
                        'pronto_para_retirar' => ['Pronto para retirar', '#11C47E', '#fff'],
                        'finalizada' => ['Finalizada', '#0a7c3a', '#fff'],
                        'cancelada' => ['Cancelada', '#E53935', '#fff']
                    ];
                    
                    if ($res_pedidos && $res_pedidos->num_rows > 0) {
                        while ($pedido = $res_pedidos->fetch_assoc()) {
                            $telefone = preg_replace('/[^0-9]/', '', $pedido['telefone']);
                            $codigo = $telefone ? substr($telefone, -4) : '';
                            $status = $pedido['situacao_compra'];
                            $status_label = isset($status_map[$status]) ? $status_map[$status][0] : ucfirst($status);
                            $status_bg = isset($status_map[$status]) ? $status_map[$status][1] : '#FFD100';
                            $status_color = isset($status_map[$status]) ? $status_map[$status][2] : '#111';
                            
                            echo '<div class="pedido-card">';
                            echo '<div class="pedido-header">';
                            echo '<span class="pedido-label">Pedido #'.$pedido['id'].'</span>';
                            echo '<span class="pedido-status" style="background:'.$status_bg.';color:'.$status_color.';">'.$status_label.'</span>';
                            echo '</div>';
                            echo '<div class="pedido-info">';
                            echo '<div><span class="pedido-label">Usuário:</span> '.htmlspecialchars($pedido['nome']).'</div>';
                            echo '<div><span class="pedido-label">Código:</span> '.$codigo.'</div>';
                            echo '<div><span class="pedido-label">Data:</span> '.date('d/m/Y H:i', strtotime($pedido['data_criacao'])).'</div>';
                            echo '<div><span class="pedido-label">Total:</span> R$ '.number_format($pedido['total'],2,',','.').'</div>';
                            echo '</div>';
                            
                            // Produtos do pedido
                            $pedido_id = intval($pedido['id']);
                            $sql_itens = "SELECT iv.*, p.nome, p.foto_produto FROM itens_vendidos iv INNER JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = $pedido_id";
                            $res_itens = $conn->query($sql_itens);
                            if ($res_itens && $res_itens->num_rows > 0) {
                                echo '<div class="mt-2">';
                                while ($item = $res_itens->fetch_assoc()) {
                                    echo '<div class="row align-items-center mb-2">';
                                    echo '<div class="col-3 text-center">';
                                    echo '<img src="'.htmlspecialchars($item['foto_produto']).'" class="img-fluid rounded" style="max-height:60px;">';
                                    echo '</div>';
                                    echo '<div class="col-5">';
                                    echo '<div style="font-size:1rem;font-weight:500;">'.htmlspecialchars($item['nome']).'</div>';
                                    echo '<div class="text-muted">Qtd: '.$item['quantidade'].'</div>';
                                    echo '</div>';
                                    echo '<div class="col-4 text-end">';
                                    echo '<div style="font-size:0.85rem; color:#666;">Unit: R$ '.number_format($item['preco_unitario'],2,',','.').'</div>';
                                    echo '<div style="font-size:1rem;">Total: <b>R$ '.number_format($item['preco_unitario'] * $item['quantidade'],2,',','.').'</b></div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                            
                            // Preço total
                            echo '<div class="d-flex justify-content-between mt-3">';
                            echo '<span class="fw-bold">Total</span>';
                            echo '<span class="fw-bold">R$ '.number_format($pedido['total'],2,',','.').'</span>';
                            echo '</div>';
                            echo '<div class="pedido-actions">';
                            
                            // Alternar estado (apenas se não finalizada/cancelada)
                            if (!in_array($status, ['finalizada','cancelada'])) {
                                echo '<form method="post" style="display:inline-block;margin-right:6px;">';
                                echo '<input type="hidden" name="pedido_id" value="'.$pedido['id'].'">';
                                echo '<select name="novo_estado" class="form-select form-select-sm" style="width:auto;display:inline-block;">';
                                foreach ($status_map as $key => $arr) {
                                    $selected = ($key == $status) ? 'selected' : '';
                                    echo '<option value="'.$key.'" '.$selected.'>'.$arr[0].'</option>';
                                }
                                echo '</select> ';
                                echo '<button type="submit" name="alterar_estado" class="btn-acoes">Alterar</button>';
                                echo '</form>';
                            }
                            
                            // Realizar pagamento (se não finalizada/cancelada)
                            if (!in_array($status, ['finalizada','cancelada'])) {
                                echo '<form method="post" class="form-pagamento" style="display:inline-block;">';
                                echo '<input type="hidden" name="pedido_id" value="'.$pedido['id'].'">';
                                echo '<button type="button" class="btn-acoes btn-pagamento" onclick="confirmarPagamento(this)">Realizar Pagamento</button>';
                                echo '</form>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-info">Nenhum pedido encontrado.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div id="modal-confirm-pagamento" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:32px 38px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;max-width:340px;margin:auto;">
            <div style="font-size:1.18rem;font-weight:600;margin-bottom:18px;">Tem certeza que deseja finalizar o pagamento?</div>
            <form id="form-confirm-pagamento" method="post">
                <input type="hidden" name="pedido_id" id="modal-pedido-id" value="">
                <button type="submit" name="realizar_pagamento" class="btn-acoes btn-pagamento" style="margin-right:12px;">Confirmar</button>
                <button type="button" class="btn-acoes" onclick="document.getElementById('modal-confirm-pagamento').style.display='none'">Cancelar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmarPagamento(btn) {
        var form = btn.closest('form');
        var pedidoId = form.querySelector('input[name="pedido_id"]').value;
        document.getElementById('modal-pedido-id').value = pedidoId;
        document.getElementById('modal-confirm-pagamento').style.display = 'flex';
    }
    </script>
</body>
</html>

<?php $sql->close(); ?>
