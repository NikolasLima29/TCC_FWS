<?php
session_start();
include "../../conn.php"; // conexão com o banco

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /fws/FWS_Cliente/login/HTML/login.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$total_final = 0;
$codigo_cupom = "";
$desconto = 0;

// Se um cupom já foi aplicado em requisições anteriores, carregue da sessão
if (isset($_SESSION['cupom_desconto'])) {
    $desconto = (float) $_SESSION['cupom_desconto'];
    $codigo_cupom = isset($_SESSION['cupom_codigo']) ? $_SESSION['cupom_codigo'] : "";
}

// selecionados atuais de forma de pagamento e tempo (mantém entre posts via SESSION)
$metodo_pagamento_selected = isset($_POST['metodo_pagamento']) ? $_POST['metodo_pagamento'] : (isset($_SESSION['metodo_pagamento']) ? $_SESSION['metodo_pagamento'] : 'dinheiro');
$tempo_reserva_selected = isset($_POST['tempo_reserva']) ? $_POST['tempo_reserva'] : (isset($_SESSION['tempo_reserva']) ? $_SESSION['tempo_reserva'] : '');

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
        // salvar cupom em sessão para manter o desconto entre requisições
        $_SESSION['cupom_desconto'] = $desconto;
        $_SESSION['cupom_codigo'] = $codigo_cupom;
        // marca que o usuário aplicou o cupom nesta sessão
        $_SESSION['cupom_aplicado'] = true;
        // salvar seleções atuais do usuário (se enviadas) para não perder os selects
        if (isset($_POST['metodo_pagamento'])) {
            $_SESSION['metodo_pagamento'] = $_POST['metodo_pagamento'];
            $metodo_pagamento_selected = $_POST['metodo_pagamento'];
        }
        if (isset($_POST['tempo_reserva'])) {
            $_SESSION['tempo_reserva'] = $_POST['tempo_reserva'];
            $tempo_reserva_selected = $_POST['tempo_reserva'];
        }
    } else {
        $erro_cupom = "Cupom inválido.";
        // remover cupom da sessão se inválido
        unset($_SESSION['cupom_desconto'], $_SESSION['cupom_codigo'], $_SESSION['cupom_aplicado']);
    }
}

// Remover cupom (botão X)
if (isset($_POST['remover_cupom'])) {
    unset($_SESSION['cupom_desconto'], $_SESSION['cupom_codigo'], $_SESSION['cupom_aplicado']);
    $desconto = 0;
    $codigo_cupom = "";
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

$lista_itens = [];
while ($row = $itens->fetch_assoc()) {
    $lista_itens[] = $row;
}

// calcula subtotal
$subtotal = 0;
foreach ($lista_itens as $item) {
    $subtotal += $item['preco_unitario'] * $item['quantidade'];
}

// calcula total final
$total_final = $subtotal;
if ($desconto > 0) {
    $total_final = $subtotal - ($subtotal * ($desconto / 100));
}


// Reservar pedido
if (isset($_POST['reservar'])) {
    $tempo_reserva = $_POST['tempo_reserva'];
    if ($tempo_reserva == "15")
        $tempo_chegada = "00:15:00";
    elseif ($tempo_reserva == "30")
        $tempo_chegada = "00:30:00";
    elseif ($tempo_reserva == "45")
        $tempo_chegada = "00:45:00";
    else
        $tempo_chegada = "01:00:00";

    // Buscar funcionário ativo
    $func = $conn->query("SELECT id FROM funcionarios WHERE ativo = 1 LIMIT 1")->fetch_assoc();
    $func_id = $func ? $func['id'] : 1;
    // forma de pagamento enviada pelo usuário (padrao 'dinheiro')
    $metodo_pagamento = isset($_POST['metodo_pagamento']) ? $_POST['metodo_pagamento'] : 'dinheiro';
    $metodos_permitidos = ['dinheiro','cartao_credito','cartao_debito','pix','boleto','outros'];
    if (!in_array($metodo_pagamento, $metodos_permitidos)) {
        $metodo_pagamento = 'dinheiro';
    }

    // Antes de criar a venda, recalcule subtotal e total_final com o desconto (garante que o valor salvo é o total efetivo)
    $subtotal_calc = 0;
    foreach ($lista_itens as $it) {
        $subtotal_calc += $it['preco_unitario'] * $it['quantidade'];
    }
    // atualiza desconto a partir da sessão caso tenha sido aplicado anteriormente
    $desconto_atual = isset($_SESSION['cupom_desconto']) ? (float) $_SESSION['cupom_desconto'] : $desconto;
    $total_final = $subtotal_calc;
    if ($desconto_atual > 0) {
        $total_final = $subtotal_calc - ($subtotal_calc * ($desconto_atual / 100));
    }

    // Criar venda (inclui metodo_pagamento) usando $total_final já com desconto
    $stmt = $conn->prepare("INSERT INTO vendas (funcionario_id, usuario_id, total, tempo_chegada, metodo_pagamento) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidss", $func_id, $usuario_id, $total_final, $tempo_chegada, $metodo_pagamento);
    $stmt->execute();
    $venda_id = $conn->insert_id;

    // Inserir itens vendidos
    foreach ($lista_itens as $item) {
        $stmt = $conn->prepare("INSERT INTO itens_vendidos (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $venda_id, $item['produto_id'], $item['quantidade'], $item['preco_unitario']);
        $stmt->execute();
    }

    // Limpar carrinho
    $conn->query("DELETE FROM carrinho WHERE usuario_id = $usuario_id");

    // Se a requisição veio via AJAX (frontend), retornar JSON com info da venda
    if (!empty($_POST['ajax'])) {
        // Buscar data_criacao do registro inserido
        $res = $conn->query("SELECT DATE_FORMAT(data_criacao, '%Y-%m-%d %H:%i:%s') as data_criacao FROM vendas WHERE id = $venda_id");
        $row = $res ? $res->fetch_assoc() : null;
        $data_criacao = $row ? $row['data_criacao'] : date('Y-m-d H:i:s');

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'venda_id' => $venda_id,
            'tempo_chegada' => $tempo_chegada,
            'data_criacao' => $data_criacao
        ]);
        exit;
    }

    // Fluxo padrão (não-AJAX): redireciona para página de confirmação
    header("Location: reserva_confirmada.php?venda_id=$venda_id");
    exit;
}
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {

        // Função para mostrar TOAST centralizado
        function mostrarToastLimite(nomeProd) {
            const toast = $(`
            <div id="toast-limite" style="
                position:fixed;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                background:#E53935;
                color:white;
                padding:15px 25px;
                border-radius:12px;
                font-weight:600;
                z-index:9999;
                box-shadow:0 8px 25px rgba(0,0,0,0.4);
                display:flex;
                align-items:center;
                gap:12px;
                max-width:320px;
                text-align:center;
                font-size:14px;
            ">
                🚫 Impossível adicionar mais, limite do estoque atingido<br>
                
            </div>
        `).appendTo('body');

            setTimeout(() => {
                toast.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 4000);
        }

        // Função para recalcular totais dinamicamente
        function recalcularTotais() {
            let subtotal = 0;

            // Soma todos os valores (preco_unitario * quantidade) de cada linha
            $("form[data-nome]").each(function () {
                const qtd = parseInt($(this).find(".quantidade-input").val()) || 0;
                const preco = parseFloat($(this).find(".preco-unitario-value").val()) || 0;
                const total_item = preco * qtd;

                // Atualiza o valor total exibido na linha
                $(this).find(".total-item").text('R$ ' + total_item.toFixed(2).replace('.', ','));
                subtotal += total_item;
            });

            // Atualiza subtotal
            $("#subtotal-valor").text('R$ ' + subtotal.toFixed(2).replace('.', ','));

            // Recalcula desconto e total
            const descontoPercent = parseFloat($("#desconto-percent").val()) || 0;
            const desconto = subtotal * (descontoPercent / 100);
            const total_final = subtotal - desconto;

            if (descontoPercent > 0) {
                $("#desconto-valor").text('- R$ ' + desconto.toFixed(2).replace('.', ','));
                $("#desconto-row").show();
            } else {
                $("#desconto-row").hide();
            }

            $("#total-valor").text('R$ ' + total_final.toFixed(2).replace('.', ','));
        }

        // Atualiza os totais na carga inicial da página
        recalcularTotais();

        // BOTÃO +
        $(document).on("click", ".btn-mais", function (e) {
            e.preventDefault();
            const form = $(this).closest("form");
            const input = form.find(".quantidade-input");
            const nome = input.data("nome");
            const limite = parseInt(input.data("limite"));
            const produtoId = form.find("input[name='produto_id']").val();
            let val = parseInt(input.val());

            if (val >= limite) {
                // Botão fica inativo + TOAST
                $(this).prop('disabled', true).css('opacity', '0.6');
                mostrarToastLimite(nome);

                setTimeout(() => {
                    $(this).prop('disabled', false).css('opacity', '1');
                }, 4000);

                return;
            }

            val++;
            input.val(val);

            // Atualizar no banco via AJAX (sem recarregar)
            $.post('', {
                update_quantidade: 1,
                produto_id: produtoId,
                quantidade: val
            }, function (resp) {
                recalcularTotais();
            });
        });

        // BOTÃO -
        $(document).on("click", ".btn-menos", function (e) {
            e.preventDefault();
            const form = $(this).closest("form");
            const input = form.find(".quantidade-input");
            const produtoId = form.find("input[name='produto_id']").val();
            let val = parseInt(input.val());

            if (val > 1) {
                val--;
                input.val(val);

                // Atualizar no banco via AJAX (sem recarregar)
                $.post('', {
                    update_quantidade: 1,
                    produto_id: produtoId,
                    quantidade: val
                }, function (resp) {
                    recalcularTotais();
                });
            }
        });

        // DIGITAR DIRETO
        $(document).on("change", ".quantidade-input", function () {
            const input = $(this);
            const nome = input.data("nome");
            const limite = parseInt(input.data("limite"));
            const form = input.closest("form");
            const produtoId = form.find("input[name='produto_id']").val();
            let val = parseInt(input.val()) || 1;

            if (val < 1) {
                val = 1;
            } else if (val > limite) {
                val = limite;
                // Botão fica inativo + TOAST
                input.closest("form").find(".btn-mais").prop('disabled', true).css('opacity', '0.6');
                mostrarToastLimite(nome);

                setTimeout(() => {
                    input.closest("form").find(".btn-mais").prop('disabled', false).css(
                        'opacity', '1');
                }, 4000);
            }

            input.val(val);

            // Atualizar no banco via AJAX (sem recarregar)
            $.post('', {
                update_quantidade: 1,
                produto_id: produtoId,
                quantidade: val
            }, function (resp) {
                recalcularTotais();
            });
        });

    });
</script>


<!doctype html>
<html lang="pt-BR">

<head>
    <title>Carrinho</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS v5.3.2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="../CSS/carrinho.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-papm6QpQKQwQvQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQpQwQ=="
        crossorigin="anonymous" />

    <!-- LINKS PARA FUNCIONAR A PESQUISA INSTANTANEA -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- JQuery UI css -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <style>
        /* ========== CSS DO HEADER ========== */
        @import url('../../Fonte_Config/fonte_geral.css');

        html,
        body,
        * {
            font-family: 'Ubuntu', sans-serif !important;
        }

        html {
            font-family: 'Ubuntu', sans-serif !important;
        }

        body {
            font-family: 'Ubuntu', sans-serif !important;
            background-color: white;
        }

        p,
        div,
        span,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        a,
        button,
        input,
        select,
        textarea,
        label {
            font-family: 'Ubuntu', sans-serif !important;
        }

        /* Estilos da HEADER */
        header {
            width: 100%;
        }

        nav.navbar {
            width: 100%;
            min-width: 100%;
            margin: 0;
            padding: 0;
            border-radius: 0;
        }

        .container-fluid {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Logo */
        .logo-shell {
            width: 150px;
            height: auto;
        }

        /* Menu Bold */
        .menu-bold {
            font-weight: 700 !important;
        }

        /* Icone de Pesquisa */
        .bi-search {
            fill: #000000 !important;
        }

        /* Ícone do Menu Hambúrguer */
        .navbar-toggler-icon {
            background-image: none !important;
            width: 1.7em;
            height: 1.7em;
            display: inline-block;
            position: relative;
        }

        .navbar-toggler-icon,
        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after {
            box-sizing: border-box;
        }

        .navbar-toggler-icon::before,
        .navbar-toggler-icon::after,
        .navbar-toggler-icon span {
            content: '';
            display: block;
            height: 3.5px;
            width: 100%;
            background: #FFD100;
            margin: 5px 0;
            border-radius: 2px;
        }

        .navbar-toggler-icon span {
            margin: 0;
        }

        /* Media Query - Mobile (até 576px) */
        @media (max-width: 576px) {
            .navbar-toggler {
                position: absolute;
                right: -55px;
                top: 0px;
                margin-right: 0 !important;
                margin-left: 0 !important;
                z-index: 1050;
                background: #c40000 !important;
                border-color: #c40000 !important;
                box-shadow: none !important;
            }

            .navbar .d-flex.align-items-center.ms-3 {
                position: relative;
                justify-content: flex-start;
                width: auto;
                gap: 0.5rem;
                position: absolute;
                right: 60px;
                top: 15px;
                z-index: 1051;
            }

            .navbar .d-flex.align-items-center.ms-3 .d-flex.align-items-center.d-sm-none a.me-2:first-child {
                margin-left: 0px !important;
                transform: translateX(-6px);
            }

            .navbar .d-flex.align-items-center.ms-3 .me-2 {
                display: flex;
            }

            .navbar .d-flex.align-items-center.ms-3 h5 {
                display: none !important;
            }

            .navbar .d-flex.align-items-center.ms-3 a:last-child {
                display: none !important;
            }

            .container-fluid .d-flex.align-items-center.ms-auto.me-4 {
                display: none !important;
            }

            .navbar-collapse .search-area-mobile {
                display: flex !important;
                width: 100%;
                margin-bottom: 15px;
            }

            .navbar-collapse .search-area-mobile input {
                width: 100% !important;
            }

            /* Centraliza os títulos do menu no mobile */
            .navbar-nav {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
                flex-direction: column !important;
                gap: 1rem !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .navbar-nav .menu-bold {
                text-align: center !important;
                width: 100% !important;
            }
        }

        /* Media Query - Desktop (577px ou maior) */
        @media (min-width: 577px) {
            .search-area {
                margin-right: -50px !important;
            }

            .d-flex.align-items-center.ms-auto.me-4 h5 {
                font-size: 14px !important;
                margin-bottom: 0px !important;
                font-family: 'Ubuntu', sans-serif !important;
                font-weight: bold !important;
                margin-left: 0px !important;
                white-space: nowrap !important;
                margin-top: -2px !important;
            }
        }

        /* Aumenta 30% o tamanho dos títulos do menu */
        .navbar-nav .menu-bold {
            font-size: 23.1px !important;
        }

        /* ========== FIM DO CSS DO HEADER ========== */

        /* ========== CSS DO AUTOCOMPLETE ========== */
        .ui-autocomplete {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            padding: 0 !important;
            max-height: 400px;
            overflow-y: auto;
            z-index: 9999 !important;
        }

        @media (max-width: 576px) {
            #search-mobile.ui-autocomplete-input {
                width: 100% !important;
            }

            .ui-autocomplete {
                min-width: 90vw !important;
                left: calc(5vw - 5px) !important;
            }
        }

        .ui-menu .ui-menu-item {
            padding: 0 !important;
            border-bottom: 1px solid #eee;
        }

        .ui-menu .ui-menu-item:last-child {
            border-bottom: none;
        }

        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover,
        .autocomplete-item:hover {
            background-color: #FFD100 !important;
            background-image: none !important;
            color: #000 !important;
            cursor: pointer;
            border-radius: 0 !important;
        }

        .ui-menu .ui-menu-item.ui-state-focus,
        .ui-menu .ui-menu-item:hover {
            box-shadow: none !important;
        }

        .autocomplete-item {
            list-style: none;
        }

        /* ========== CSS DO CARRINHO (CARDS E BOTÕES) ========== */
        /* Bordas dos cards em vermelho */
        .card {
            border: 2px solid #c40000 !important;
        }

        /* Botões + e - com estilo parecido ao produto_especifico.php */
        .btn-mais,
        .btn-menos {
            width: 40px;
            height: 40px;
            font-size: 1.4rem;
            font-weight: 800;
            border-radius: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.18s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .btn-mais {
            background: linear-gradient(145deg, #FFD100, #FFEB3B) !important;
            color: #111 !important;
            border: 2px solid #FFC107 !important;
            border-radius: 0 8px 8px 0 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.08) !important;
        }

        .btn-menos {
            background: linear-gradient(145deg, #E53935, #D32F2F) !important;
            color: #fff !important;
            border: 2px solid #D32F2F !important;
            border-radius: 8px 0 0 8px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18) !important;
        }

        .btn-mais:hover:not(:disabled),
        .btn-menos:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-mais:active:not(:disabled),
        .btn-menos:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-mais:disabled,
        .btn-menos:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        /* Ajuste do input para combinar altura */
        .quantidade-input {
            height: 40px;
            max-width: 70px;
            padding: 0;
            font-size: 0.95rem;
            text-align: center;
        }

        /* Remove as setas do input numérico (Chrome, Safari, Edge) */
        .quantidade-input::-webkit-outer-spin-button,
        .quantidade-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Remove as setas do input numérico (Firefox) */
        .quantidade-input[type=number] {
            -moz-appearance: textfield;
        }

        /* Remove borda duplicada entre botões e input-group quando aplicável */
        .input-group .btn-menos {
            border-right: none !important;
        }

        .input-group .btn-mais {
            border-left: none !important;
        }

        /* Botão Reservar personalizado (combina com o header vermelho) */
        .btn-reservar {
            background: linear-gradient(180deg, #c40000 0%, #a50000 100%);
            color: #fff !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 12px 18px !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(196, 0, 0, 0.18), inset 0 -2px 0 rgba(0, 0, 0, 0.08);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .btn-reservar:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(196, 0, 0, 0.26), inset 0 -2px 0 rgba(0, 0, 0, 0.06);
        }

        .btn-reservar:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .btn-reservar:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        /* Spinner customizado: anel predominantemente vermelho, animação suave */
        .custom-spinner {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            /* anel vermelho com variações sutis para profundidade */
            background: conic-gradient(#c40000 0deg, #a30000 120deg, #b80000 220deg, #c40000 360deg);
            position: relative;
            box-shadow: 0 10px 30px rgba(196, 0, 0, 0.22), inset 0 0 18px rgba(180, 0, 0, 0.12);
            display: inline-block;
            transform-origin: 50% 50%;
            animation: spinner-rotate 1.0s linear infinite, spinner-pulse 1.6s ease-in-out infinite;
        }

        .custom-spinner::after {
            content: '';
            position: absolute;
            left: 12px;
            top: 12px;
            right: 12px;
            bottom: 12px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.08), rgba(0, 0, 0, 0.02));
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        @keyframes spinner-rotate {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes spinner-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.96);
            }
        }

        /* Pequeno glow animado por trás do spinner (tom vermelho) */
        .spinner-glow {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            background: radial-gradient(circle at 30% 30%, rgba(196, 0, 0, 0.22), rgba(196, 0, 0, 0.04) 40%, transparent 60%);
            filter: blur(6px);
            animation: glow-fade 1.8s ease-in-out infinite;
        }

        @keyframes glow-fade {

            0%,
            100% {
                opacity: 0.9
            }

            50% {
                opacity: 0.5
            }
        }

        /* Modal action buttons */
        .modal-btn-red {
            background: linear-gradient(180deg, #c40000 0%, #a50000 100%);
            color: #fff !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 10px 18px !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(196, 0, 0, 0.18), inset 0 -2px 0 rgba(0, 0, 0, 0.06);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .modal-btn-red:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(196, 0, 0, 0.26);
        }

        .modal-btn-red:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .modal-btn-yellow {
            background: linear-gradient(145deg, #FFD100, #FFEB3B) !important;
            color: #111 !important;
            border: 2px solid #FFC107 !important;
            border-radius: 10px !important;
            padding: 10px 18px !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(255, 187, 51, 0.12);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .modal-btn-yellow:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 187, 51, 0.22);
        }

        .modal-btn-yellow:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>



<body>
    <!-- ========== INÍCIO DO HEADER ========== -->
    <header>
        <!-- ========== NAVBAR PRINCIPAL ========== -->
        <nav class="navbar navbar-expand-sm navbar-light" style="background-color: #c40000;">
            <!-- ========== LOGO ========== -->
            <a class="navbar-brand ms-3" href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="Logo" class="logo-shell">
            </a>

            <!-- ========== SEÇÃO MOBILE (BOTÃO TOGGLE + CARRINHO + PERFIL) ========== -->
            <div class="d-flex align-items-center ms-3">
                <!-- Botão do menu hambúrguer -->
                <button class="navbar-toggler me-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsibleNavId" aria-controls="collapsibleNavId" aria-expanded="false"
                    aria-label="Toggle navigation" onclick="toggleMenu(this)">
                    <span class="navbar-toggler-icon"><span></span></span>
                </button>

                <!-- Ícones de Carrinho e Perfil (apenas mobile) -->
                <div class="d-flex align-items-center d-sm-none">
                    <!-- Carrinho Mobile -->
                    <a href="../../carrinho/HTML/carrinho.php" class="me-2" style="margin-left: 2px;">
                        <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                            style="object-fit: contain; filter: brightness(0) invert(1);">
                    </a>

                    <!-- Perfil Mobile (com validação de login) -->
                    <a href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>"
                        class="me-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white"
                            class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                    </a>

                    <!-- Texto "Bem-vindo(a)" Mobile -->
                    <h5 class="text-white me-2 m-0">Bem-vindo(a)</h5>
                </div>

                <!-- ========== BARRA DE PESQUISA (Desktop) ========== -->
                <div class="search-area d-none d-sm-flex align-items-center ms-auto">
                    <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get"
                        style="margin: 0;">
                        <input id="search" class="form-control me-2" type="search" name="q" placeholder="Pesquisar..."
                            style="width: 300px;">
                        <button class="btn btn-outline-light" type="submit"
                            style="background-color: #FFD100; border-color: #FFD100;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ========== MENU TOGGLE (DESKTOP) ========== -->
            <div class="container-fluid">
                <div class="collapse navbar-collapse" id="collapsibleNavId">
                    <!-- Itens de Menu Centralizados: Home, Produtos, Meus Pedidos, Sobre Nós -->
                    <ul class="navbar-nav d-flex align-items-center gap-4 justify-content-center w-100"
                        style="margin-right: 40px;">
                        <li class="nav-item">
                            <a class="nav-link" href="../../index.php" data-bs-toggle="collapse" data-bs-target="#collapsibleNavId">
                                <h5 class="m-0 text-white menu-bold">Home</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../produto/HTML/produto.php" data-bs-toggle="collapse" data-bs-target="#collapsibleNavId">
                                <h5 class="m-0 text-white menu-bold">Produtos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../meus_pedidos/HTML/Meus_pedidos.php" data-bs-toggle="collapse" data-bs-target="#collapsibleNavId">
                                <h5 class="m-0 text-white menu-bold">Meus Pedidos</h5>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../tela_sobre_nos/HTML/sobre_nos.php" data-bs-toggle="collapse" data-bs-target="#collapsibleNavId">
                                <h5 class="m-0 text-white menu-bold">Sobre Nós</h5>
                            </a>
                        </li>
                    </ul>

                    <!-- ========== SEÇÃO DESKTOP (CARRINHO + BEM-VINDO + PERFIL) ========== -->
                    <div class="d-flex align-items-center ms-auto me-4">
                        <!-- Carrinho Desktop -->
                        <a href="../../carrinho/HTML/carrinho.php" style="margin-left: -70px;">
                            <img src="../../carrinho/IMG/carrinho.png" alt="Carrinho" width="30" height="30"
                                class="me-4" style="object-fit: contain; filter: brightness(0) invert(1);">
                        </a>

                        <!-- Texto "Bem-vindo(a)" Desktop (com nome se logado) -->
                        <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
                        <?php
                                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                                $primeiroNome = explode(' ', $nomeCompleto)[0];
                            ?>
                        <h5 class="text-white me-2" style="margin-top: 10px;">Bem-vindo(a), <?= $primeiroNome ?></h5>
                        <?php else: ?>
                        <h5 class="text-white me-2" style="margin-top: 10px;">Bem-vindo(a)</h5>
                        <?php endif; ?>

                        <!-- Perfil Desktop (com validação de login) -->
                        <a
                            href="<?php echo (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])) ? '../../info_usuario/HTML/info_usuario.php' : '../../login/HTML/login.html'; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="white"
                                class="bi bi-person-circle" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                <path fill-rule="evenodd"
                                    d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ========== BARRA DE PESQUISA MOBILE ========== -->
        <div class="search-mobile-container d-sm-none px-3 py-2" style="background-color: #c40000;">
            <!-- Texto "Bem-vindo(a)" Mobile com nome se logado -->
            <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
            <?php
                    $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                    $primeiroNome = explode(' ', $nomeCompleto)[0];
                ?>
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a), <?= $primeiroNome ?></h5>
            <?php else: ?>
            <h5 class="text-white mb-2 w-100 text-center" style="font-size: 1.1rem; position: relative; left: -2px;">
                Bem-vindo(a)</h5>
            <?php endif; ?>

            <!-- Formulário de Pesquisa Mobile -->
            <form class="d-flex" role="search" action="../../produto/HTML/produto.php" method="get">
                <input id="search-mobile" class="form-control me-2" type="search" name="q" placeholder="Pesquisar...">
                <button class="btn btn-outline-light" type="submit"
                    style="background-color: #FFD100; border-color: #FFD100;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" class="bi bi-search"
                        viewBox="0 0 16 16">
                        <path
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </button>
            </form>
        </div>
    </header>
    <!-- ========== FIM DO HEADER ========== -->

    <script>
        $(function () {
            // Comportamento de fechamento do menu mobile (collapse)
            const collapseEl = document.getElementById('collapsibleNavId');
            const toggler = document.querySelector('.navbar-toggler');
            
            if (collapseEl && toggler && window.bootstrap) {
                const navLinks = collapseEl.querySelectorAll('.navbar-nav .nav-link');
                let bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });

                // Alterna o collapse ao clicar no toggle (abre/fecha)
                toggler.addEventListener('click', (e) => {
                    bsCollapse.toggle();
                });

                // Fecha ao clicar em qualquer item de menu (em mobile)
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth < 577) {
                            bsCollapse.hide();
                        }
                    });
                });

                // Fecha ao clicar fora do menu quando aberto
                document.addEventListener('click', (e) => {
                    const isOpen = collapseEl.classList.contains('show');
                    if (!isOpen) return;
                    if (toggler.contains(e.target)) return;
                    if (!collapseEl.contains(e.target)) {
                        bsCollapse.hide();
                    }
                });
            }
            
            // Enviar formulário ao clicar no botão de busca (desktop)
            $('form[role="search"]').on('submit', function(e) {
                const q = $(this).find('input[name="q"]').val().trim();
                if (q) {
                    return true;
                } else {
                    e.preventDefault();
                    return false;
                }
            });
            
            var autocomplete = $("#search").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '../../produto/PHP/api-produtos.php',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    window.location.href =
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search").data('autocomplete');

            if (autocomplete) {
                autocomplete._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append(
                            "<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" +
                            item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label +
                            "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>"
                            )
                        .appendTo(ul);
                };
            }

            // Autocomplete para mobile
            var autocompleteMobile = $("#search-mobile").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '../../produto/PHP/api-produtos.php',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    window.location.href =
                        '../../produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                }
            }).data('ui-autocomplete') || $("#search-mobile").data('autocomplete');

            if (autocompleteMobile) {
                autocompleteMobile._renderItem = function (ul, item) {
                    return $("<li class='autocomplete-item'>")
                        .append(
                            "<div style='display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee;'><img src='" +
                            item.foto +
                            "' style='width: 70px; height: 70px; object-fit: cover; margin-right: 12px; background-color: #FFD100; border-radius: 4px;'/><div style='flex: 1;'><div style='font-weight: 500; color: #333; font-size: 14px;'>" +
                            item.label +
                            "</div><div style='color: #999; font-size: 12px; margin-top: 4px;'>Clique para ver detalhes</div></div></div>"
                            )
                        .appendTo(ul);
                };
            }
        });
    </script>

    <script>
        // Toggle de menu mobile com visual feedback
        function toggleMenu(button) {
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isExpanded);
        }
    </script>

    <script>
        // copia as seleções atuais para o form de cupom antes de submeter
        $(document).on('submit', '#form-cupom', function (e) {
            // pega os selects atuais (se existirem)
            var metodo = $('#metodo_pagamento').val();
            var tempo = $('#tempo_reserva').val();
            if (metodo !== undefined) {
                $('#cup_metodo_pagamento').val(metodo);
            }
            if (tempo !== undefined) {
                $('#cup_tempo_reserva').val(tempo);
            }
            return true; // permite o submit
        });
    </script>

    <div class="container py-5">
        <h1 class="mb-5">Seu carrinho</h1>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if ($itens->num_rows > 0): ?>
                        <?php foreach ($lista_itens as $item):
                                $estoque = (int) $item['estoque'];

                                // regra de limite: se estoque >=10 -> 10, senão metade (floor)
// (se metade for 0, define 1 para permitir pelo menos 1 unidade)
                                if ($estoque >= 10) {
                                    $limite = 10;
                                } else {
                                    $limite = (int) floor($estoque / 2);
                                    if ($limite < 1)
                                        $limite = 1;
                                } ?>
                        <form method="post" class="row align-items-center mb-4 border-bottom pb-3"
                            data-nome="<?= $item['nome'] ?>">
                            <div class="col-md-3 text-center">
                                <img src="<?= $item['foto_produto'] ?>" class="img-fluid rounded"
                                    style="max-height:120px;">
                            </div>
                            <div class="col-md-4">
                                <h5><?= $item['nome'] ?></h5>
                                <p class="text-muted">Preço unitário: R$
                                    <?= number_format($item['preco_unitario'], 2, ',', '.') ?></p>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="input-group input-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-menos">-</button>

                                    <input type="number" class="form-control text-center quantidade-input"
                                        name="quantidade" value="<?= $item['quantidade'] ?>" min="1"
                                        max="<?= $limite ?>" data-limite="<?= $limite ?>"
                                        data-nome="<?= htmlspecialchars($item['nome'], ENT_QUOTES) ?>">

                                    <button type="button" class="btn btn-outline-secondary btn-mais">+</button>
                                </div>

                                <!-- botão submit escondido: será acionado automaticamente pelo JS para enviar o form -->
                                <button type="submit" name="update_quantidade" class="d-none btn-submit-real"></button>

                                <!-- Input oculto com preço unitário para cálculos JS -->
                                <input type="hidden" name="preco_unitario" class="preco-unitario-value"
                                    value="<?= $item['preco_unitario'] ?>">

                                <!-- mantenha o hidden produto_id como já tem -->
                                <input type="hidden" name="produto_id" value="<?= $item['produto_id'] ?>">

                            </div>
                            <div class="col-md-2 text-end">
                                <p><strong class="total-item">R$
                                        <?= number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.') ?></strong>
                                </p>
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
                            <strong id="subtotal-valor">R$ <?= number_format($subtotal, 2, ',', '.') ?></strong>
                        </div>
                        <?php if ($desconto > 0): ?>
                        <div class="d-flex justify-content-between text-success mt-2" id="desconto-row">
                            <span>Desconto (<span id="desconto-percent-text"><?= $desconto ?></span>%)</span>
                            <strong id="desconto-valor">- R$
                                <?= number_format($subtotal * ($desconto / 100), 2, ',', '.') ?></strong>
                        </div>
                        <?php endif; ?>
                        <input type="hidden" id="desconto-percent" value="<?= $desconto ?>">
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong id="total-valor">R$ <?= number_format($total_final, 2, ',', '.') ?></strong>
                        </div>

                        <form method="post" id="form-reserva" class="mt-3">
                            <label for="metodo_pagamento" class="form-label">Forma de pagamento</label>
                            <select name="metodo_pagamento" id="metodo_pagamento" class="form-select mb-3" required>
                                <option value="dinheiro"
                                    <?= ($metodo_pagamento_selected === 'dinheiro') ? 'selected' : '' ?>>Dinheiro
                                </option>
                                <option value="cartao_credito"
                                    <?= ($metodo_pagamento_selected === 'cartao_credito') ? 'selected' : '' ?>>Cartão de
                                    Crédito</option>
                                <option value="cartao_debito"
                                    <?= ($metodo_pagamento_selected === 'cartao_debito') ? 'selected' : '' ?>>Cartão de
                                    Débito</option>
                                <option value="pix" <?= ($metodo_pagamento_selected === 'pix') ? 'selected' : '' ?>>PIX
                                </option>
                            </select>
                            <select name="tempo_reserva" id="tempo_reserva" class="form-select mb-3" required>
                                <option value="" disabled <?= ($tempo_reserva_selected === '') ? 'selected' : '' ?>>
                                    selecione o tempo limite até sua chegada</option>
                                <option value="15" <?= ($tempo_reserva_selected === '15') ? 'selected' : '' ?>>15
                                    minutos</option>
                                <option value="30" <?= ($tempo_reserva_selected === '30') ? 'selected' : '' ?>>30
                                    minutos</option>
                                <option value="45" <?= ($tempo_reserva_selected === '45') ? 'selected' : '' ?>>45
                                    minutos</option>
                                <option value="60" <?= ($tempo_reserva_selected === '60') ? 'selected' : '' ?>>1 hora
                                </option>
                            </select>
                            <button name="reservar" class="btn btn-reservar w-100">Reservar</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5>Código de promoção</h5>
                        <form method="post" class="input-group" id="form-cupom">
                            <input type="text" name="codigo_cupom" class="form-control" placeholder="Digite o cupom">
                            <!-- hidden inputs para preservar seleções ao aplicar/remover cupom -->
                            <input type="hidden" name="metodo_pagamento" id="cup_metodo_pagamento"
                                value="<?= htmlspecialchars($metodo_pagamento_selected) ?>">
                            <input type="hidden" name="tempo_reserva" id="cup_tempo_reserva"
                                value="<?= htmlspecialchars($tempo_reserva_selected) ?>">
                            <button type="submit" class="btn btn-outline-secondary"
                                name="aplicar_cupom">Aplicar</button>
                            <button type="submit" class="btn btn-outline-danger" name="remover_cupom"
                                title="Remover cupom" style="margin-left:6px;">&times;</button>
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

    <!-- Modal/Popup de confirmação de reserva -->
    <div id="reserva-modal-backdrop"
        style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:2000;"></div>
    <div id="reserva-modal"
        style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); z-index:2100; background:#fff; border-radius:12px; padding:22px; width:360px; max-width:92%; box-shadow:0 10px 40px rgba(0,0,0,0.35); text-align:center;">
        <div id="reserva-modal-content">
        <!-- Conteúdo preenchido dinamicamente -->
        <div id="reserva-loading" style="display:flex; flex-direction:column; align-items:center; gap:14px;">
            <div class="spinner-border text-primary" role="status" style="width:54px;height:54px;"><span
                    class="visually-hidden">Loading...</span></div>
            <div style="font-weight:700;">Processando sua reserva...</div>
            <div style="font-size:13px; color:#666;">Aguarde um instante, estamos gravando seu pedido.</div>
        </div>
        <div id="reserva-success" style="display:none;">
            <div id="reserva-success-title"
                style="font-size:1.25rem; font-weight:800; color:#00A65A; margin-bottom:8px;">Reserva feita com sucesso
            </div>
            <div id="reserva-success-body" style="color:#333; margin-bottom:12px; font-size:0.95rem;">O seu pedido já
                está sendo preparado, você tem <span id="reserva-countdown" style="font-weight:700;">00:00</span> para
                chegar no estabelecimento e retirar seu pedido, caso contrário, sua reserva será cancelada.</div>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button id="reserva-continuar" class="modal-btn-red">Continuar comprando</button>
                <button id="reserva-meuspedidos" class="modal-btn-yellow">Meus pedidos</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Intercepta o submit do form-reserva, envia via AJAX e mostra modal com loading + contagem
    (function () {
        const form = document.getElementById('form-reserva');
        if (!form) return;

        const backdrop = document.getElementById('reserva-modal-backdrop');
        const modal = document.getElementById('reserva-modal');
        const loading = document.getElementById('reserva-loading');
        const success = document.getElementById('reserva-success');
        const countdownEl = document.getElementById('reserva-countdown');
        const btnContinuar = document.getElementById('reserva-continuar');
        const btnMeusPedidos = document.getElementById('reserva-meuspedidos');

        let countdownInterval = null;

        function showModal() {
            backdrop.style.display = 'block';
            modal.style.display = 'block';
        }

        function hideModal() {
            backdrop.style.display = 'none';
            modal.style.display = 'none';
            // limpar interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        btnContinuar.addEventListener('click', function () {
            window.location.href = '/fws/FWS_Cliente/produto/HTML/produto.php';
        });
        btnMeusPedidos.addEventListener('click', function () {
            window.location.href = '/fws/FWS_Cliente/meus_pedidos/HTML/Meus_pedidos.php';
        });
        // NOTA: deliberadamente não registramos listener para fechar o modal via backdrop
        // e não exibimos botão de fechar. O modal só é removido pelas ações internas (botões).

        function parseTimeToMs(timestr) {
            // espera formato HH:MM:SS
            const parts = timestr.split(':').map(Number);
            if (parts.length !== 3) return 0;
            return ((parts[0] * 3600) + (parts[1] * 60) + parts[2]) * 1000;
        }

        function formatRemaining(ms) {
            if (ms <= 0) return '00:00:00';
            const totalSec = Math.floor(ms / 1000);
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            if (h > 0)
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Verifica se o carrinho está vazio (nenhum form com data-nome)
            const itensNoCarrinho = document.querySelectorAll("form[data-nome]").length;
            if (itensNoCarrinho === 0) {
                // mostra toast parecido com limite de estoque
                (function showEmptyCartToast() {
                    // evita duplicar toasts
                    if (document.getElementById('toast-empty-cart')) return;
                    const toast = document.createElement('div');
                    toast.id = 'toast-empty-cart';
                    toast.style.position = 'fixed';
                    toast.style.top = '50%';
                    toast.style.left = '50%';
                    toast.style.transform = 'translate(-50%,-50%)';
                    toast.style.background = '#E53935';
                    toast.style.color = 'white';
                    toast.style.padding = '15px 25px';
                    toast.style.borderRadius = '12px';
                    toast.style.fontWeight = '600';
                    toast.style.zIndex = '9999';
                    toast.style.boxShadow = '0 8px 25px rgba(0,0,0,0.4)';
                    toast.style.display = 'flex';
                    toast.style.alignItems = 'center';
                    toast.style.gap = '12px';
                    toast.style.maxWidth = '360px';
                    toast.style.textAlign = 'center';
                    toast.style.fontSize = '14px';
                    toast.innerHTML = `
                    <div style="font-size:20px">🚫</div>
                    <div style="text-align:left">Não é possível fazer a reserva — não há nenhum produto no carrinho.</div>
                `;
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.style.transition = 'opacity 0.3s ease';
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 300);
                    }, 4000);
                })();
                return; // não prosseguir com o submit
            }

            // prepara FormData e adiciona flags necessárias (inclui 'reservar' para o backend)
            const fd = new FormData(form);
            fd.append('ajax', '1');
            // O backend espera a presença de 'reservar' para executar a criação da venda
            fd.append('reservar', '1');

            showModal();
            loading.style.display = 'flex';
            success.style.display = 'none';

            // envia para mesma URL (carrinho.php)
            fetch('', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.text())
                .then(text => {
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (err) {
                        throw new Error('Resposta inválida do servidor: ' + text);
                    }
                    // agora 'data' é o JSON esperado



                    // exibe loading por 4s, depois sucesso
                    setTimeout(() => {
                        loading.style.display = 'none';
                        success.style.display = 'block';

                        // calcula deadline: data_criacao + tempo_chegada
                        // data_criacao vem no formato 'YYYY-MM-DD HH:MM:SS'
                        const created = data.data_criacao ? new Date(data.data_criacao.replace(
                            ' ', 'T')) : new Date();
                        const msLimit = parseTimeToMs(data.tempo_chegada || '00:30:00');
                        const deadline = new Date(created.getTime() + msLimit);

                        function updateCountdown() {
                            const now = new Date();
                            const rem = deadline.getTime() - now.getTime();
                            countdownEl.textContent = formatRemaining(rem);
                            if (rem <= 0) {
                                countdownEl.textContent = '00:00:00';
                                if (countdownInterval) {
                                    clearInterval(countdownInterval);
                                    countdownInterval = null;
                                }
                            }
                        }

                        updateCountdown();
                        countdownInterval = setInterval(updateCountdown, 500);

                    }, 4000);
                }).catch(err => {
                    console.error('Erro ao enviar reserva', err);
                    loading.style.display = 'none';
                    success.style.display = 'block';
                    document.getElementById('reserva-success-title').textContent =
                        'Erro ao efetuar reserva';
                    // mostrar mensagem de erro mais informativa (trunca para evitar html enorme)
                    const msg = err.message ? err.message : 'Ocorreu um erro ao processar seu pedido.';
                    document.getElementById('reserva-success-body').textContent = msg.substring(0, 300);
                });
        });

    })();
</script>
</body>

</html>