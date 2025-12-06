<?php
session_start();
include "../../conn.php"; // conexão com o banco

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /Fws/FWS_Cliente/login/HTML/login.html");
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
$(document).ready(function() {
    
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
            toast.fadeOut(300, function() { 
                $(this).remove(); 
            });
        }, 4000);
    }

    // Função para recalcular totais dinamicamente
    function recalcularTotais() {
        let subtotal = 0;
        
        // Soma todos os valores (preco_unitario * quantidade) de cada linha
        $("form[data-nome]").each(function() {
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
    $(document).on("click", ".btn-mais", function(e) {
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
        }, function(resp) {
            recalcularTotais();
        });
    });

    // BOTÃO -
    $(document).on("click", ".btn-menos", function(e) {
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
            }, function(resp) {
                recalcularTotais();
            });
        }
    });

    // DIGITAR DIRETO
    $(document).on("change", ".quantidade-input", function() {
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
                input.closest("form").find(".btn-mais").prop('disabled', false).css('opacity', '1');
            }, 4000);
        }

        input.val(val);
        
        // Atualizar no banco via AJAX (sem recarregar)
        $.post('', {
            update_quantidade: 1,
            produto_id: produtoId,
            quantidade: val
        }, function(resp) {
            recalcularTotais();
        });
    });

});
</script>


<!doctype html>
<html lang="pt-br">

<head>
    <title>Carrinho</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/carrinho.css">
    <style>
        /* Botões + e - com estilo parecido ao produto_especifico.php */
        .btn-mais, .btn-menos {
            width:40px;
            height:40px;
            font-size:1.4rem;
            font-weight:800;
            border-radius:0;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0;
            transition: all 0.18s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .btn-mais {
            background: linear-gradient(145deg, #FFD100, #FFEB3B) !important;
            color: #111 !important;
            border: 2px solid #FFC107 !important;
            border-radius: 0 8px 8px 0 !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.08) !important;
        }

        .btn-menos {
            background: linear-gradient(145deg, #E53935, #D32F2F) !important;
            color: #fff !important;
            border: 2px solid #D32F2F !important;
            border-radius: 8px 0 0 8px !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.18) !important;
        }

        .btn-mais:hover:not(:disabled), .btn-menos:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .btn-mais:active:not(:disabled), .btn-menos:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .btn-mais:disabled, .btn-menos:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        /* Ajuste do input para combinar altura */
        .quantidade-input {
            height:40px;
            max-width:70px;
            padding:0;
            font-size:0.95rem;
            text-align:center;
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
        .input-group .btn-menos { border-right: none !important; }
        .input-group .btn-mais { border-left: none !important; }
        
        /* Botão Reservar personalizado (combina com o header vermelho) */
        .btn-reservar {
            background: linear-gradient(180deg, #c40000 0%, #a50000 100%);
            color: #fff !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 12px 18px !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(196,0,0,0.18), inset 0 -2px 0 rgba(0,0,0,0.08);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }

        .btn-reservar:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(196,0,0,0.26), inset 0 -2px 0 rgba(0,0,0,0.06);
        }

        .btn-reservar:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }

        .btn-reservar:disabled {
            opacity: 0.6 !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        /* Spinner customizado: anel predominantemente vermelho, animação suave */
        .custom-spinner {
            width:64px;
            height:64px;
            border-radius:50%;
            /* anel vermelho com variações sutis para profundidade */
            background: conic-gradient(#c40000 0deg, #a30000 120deg, #b80000 220deg, #c40000 360deg);
            position: relative;
            box-shadow: 0 10px 30px rgba(196,0,0,0.22), inset 0 0 18px rgba(180,0,0,0.12);
            display:inline-block;
            transform-origin:50% 50%;
            animation: spinner-rotate 1.0s linear infinite, spinner-pulse 1.6s ease-in-out infinite;
        }
        .custom-spinner::after {
            content: '';
            position: absolute;
            left:12px; top:12px; right:12px; bottom:12px;
            border-radius:50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.08), rgba(0,0,0,0.02));
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.18);
        }
        @keyframes spinner-rotate { to { transform: rotate(360deg); } }
        @keyframes spinner-pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(0.96); } }

        /* Pequeno glow animado por trás do spinner (tom vermelho) */
        .spinner-glow {
            width:96px; height:96px; border-radius:50%; position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); z-index:-1;
            background: radial-gradient(circle at 30% 30%, rgba(196,0,0,0.22), rgba(196,0,0,0.04) 40%, transparent 60%);
            filter: blur(6px);
            animation: glow-fade 1.8s ease-in-out infinite;
        }
        @keyframes glow-fade { 0%,100% { opacity:0.9 } 50% { opacity:0.5 } }

        /* Modal action buttons */
        .modal-btn-red {
            background: linear-gradient(180deg, #c40000 0%, #a50000 100%);
            color: #fff !important;
            border: none !important;
            border-radius: 10px !important;
            padding: 10px 18px !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(196,0,0,0.18), inset 0 -2px 0 rgba(0,0,0,0.06);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }
        .modal-btn-red:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(196,0,0,0.26); }
        .modal-btn-red:active:not(:disabled) { transform: translateY(0); box-shadow: 0 6px 14px rgba(0,0,0,0.12); }

        .modal-btn-yellow {
            background: linear-gradient(145deg, #FFD100, #FFEB3B) !important;
            color: #111 !important;
            border: 2px solid #FFC107 !important;
            border-radius: 10px !important;
            padding: 10px 18px !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            box-shadow: 0 8px 20px rgba(255,187,51,0.12);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease;
        }
        .modal-btn-yellow:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(255,187,51,0.22); }
        .modal-btn-yellow:active:not(:disabled) { transform: translateY(0); box-shadow: 0 6px 14px rgba(0,0,0,0.08); }
    </style>
</head>



<body>

    <header id="header">

        <div class="logo">
            <a href="../../index.php">
                <img src="../../index/IMG/shell_select.png" alt="logo" />
            </a>
        </div>

        <button class="menu-toggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav>
            <ul class="ul align-items-center">
                <li><a href="/Fws/FWS_Cliente/produto/HTML/produto.php">Produtos</a></li>
                <li>
                    <form class="d-flex" role="search" action="/Fws/FWS_Cliente/produto/HTML/produto.php"
                        method="get" style="margin: 0 10px;">
                        <input id="search" class="form-control form-control-sm me-2" type="search" name="q"
                            placeholder="Pesquisar..." aria-label="Pesquisar">
                        <button class="btn btn-warning btn-sm" type="submit" style="padding: 0.25rem 0.6rem;">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </li>
                <li><a href="/Fws/FWS_Cliente/meus_pedidos/HTML/Meus_pedidos.php">Meus pedidos</a></li>
                <li><a href="/Fws/FWS_Cliente/tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
            </ul>
        </nav>

        <div class="carrinho">
            <a href="/Fws/FWS_Cliente/carrinho/HTML/carrinho.php">
                <img src="/Fws/FWS_Cliente/index/IMG/carrinho.png" alt="carrinho" id="carrinho" />
            </a>
        </div>

        <div id="bem-vindo" style="position: relative; display: inline-block;">
            <?php if (isset($_SESSION['usuario_nome']) && !empty($_SESSION['usuario_nome'])): ?>
                <?php
                $nomeCompleto = htmlspecialchars($_SESSION['usuario_nome']);
                $primeiroNome = explode(' ', $nomeCompleto)[0];
                ?>
                Bem-vindo(a), <?= $primeiroNome ?>
                <div style="display: inline-block; margin-left: 8px; cursor: pointer;" id="user-menu-toggle">
                    <i class="fas fa-user-circle fa-2x" style="max-width: 90px;"></i>
                </div>

                <div id="user-menu"
                    style="display: none; position: absolute; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 0; min-width: 120px; z-index: 1000;">
                    <a href="/Fws/FWS_Cliente/info_usuario/HTML/info_usuario.php"
                        style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Ver perfil</a>
                    <a href="/Fws/FWS_Cliente/logout.php" id="logout-link"
                        style="display: block; padding: 8px 16px; color: black; text-decoration: none;">Sair</a>
                </div>

                <script>
                    document.getElementById('user-menu-toggle').addEventListener('click', function () {
                        var menu = document.getElementById('user-menu');
                        if (menu.style.display === 'none') {
                            menu.style.display = 'block';
                        } else {
                            menu.style.display = 'none';
                        }
                    });

                    // Fecha o menu se clicar fora
                    document.addEventListener('click', function (event) {
                        var container = document.getElementById('bem-vindo');
                        var menu = document.getElementById('user-menu');
                        if (!container.contains(event.target)) {
                            menu.style.display = 'none';
                        }
                    });
                </script>
            <?php else: ?>
                Bem-vindo(a).
            <?php endif; ?>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

        <script>
            $(function () {
                var autocomplete = $("#search").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: '/Fws/FWS_Cliente/produto/PHP/api-produtos.php',
                            dataType: 'json',
                            data: { q: request.term },
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 2,
                    select: function (event, ui) {
                        window.location.href = 'produto_especifico/HTML/produto_especifico.php?id=' + ui.item.id;
                    }
                }).data('ui-autocomplete') || $("#search").data('autocomplete');

                if (autocomplete) {
                    autocomplete._renderItem = function (ul, item) {
                        return $("<li>")
                            .append("<div><img src='" + item.foto + "' style='width:100px; height:auto; margin-right:5px; vertical-align:middle;  background-color: #FFD100 !important;'/>" + item.label + "</div>")
                            .appendTo(ul);
                    };
                }
            });

        </script>
        <script>
            // copia as seleções atuais para o form de cupom antes de submeter
            $(document).on('submit', '#form-cupom', function(e) {
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
    </header>

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
         name="quantidade"
         value="<?= $item['quantidade'] ?>"
         min="1"
         max="<?= $limite ?>"
         data-limite="<?= $limite ?>"
         data-nome="<?= htmlspecialchars($item['nome'], ENT_QUOTES) ?>">

  <button type="button" class="btn btn-outline-secondary btn-mais">+</button>
</div>

<!-- botão submit escondido: será acionado automaticamente pelo JS para enviar o form -->
<button type="submit" name="update_quantidade" class="d-none btn-submit-real"></button>

<!-- Input oculto com preço unitário para cálculos JS -->
<input type="hidden" name="preco_unitario" class="preco-unitario-value" value="<?= $item['preco_unitario'] ?>">

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
                            <strong id="desconto-valor">- R$ <?= number_format($subtotal * ($desconto / 100), 2, ',', '.') ?></strong>
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
                                <option value="dinheiro" <?= ($metodo_pagamento_selected === 'dinheiro') ? 'selected' : '' ?>>Dinheiro</option>
                                <option value="cartao_credito" <?= ($metodo_pagamento_selected === 'cartao_credito') ? 'selected' : '' ?>>Cartão de Crédito</option>
                                <option value="cartao_debito" <?= ($metodo_pagamento_selected === 'cartao_debito') ? 'selected' : '' ?>>Cartão de Débito</option>
                                <option value="pix" <?= ($metodo_pagamento_selected === 'pix') ? 'selected' : '' ?>>PIX</option>
                            </select>
                            <select name="tempo_reserva" id="tempo_reserva" class="form-select mb-3" required>
                                <option value="" disabled <?= ($tempo_reserva_selected === '') ? 'selected' : '' ?>>selecione o tempo limite até sua chegada</option>
                                <option value="15" <?= ($tempo_reserva_selected === '15') ? 'selected' : '' ?>>15 minutos</option>
                                <option value="30" <?= ($tempo_reserva_selected === '30') ? 'selected' : '' ?>>30 minutos</option>
                                <option value="45" <?= ($tempo_reserva_selected === '45') ? 'selected' : '' ?>>45 minutos</option>
                                <option value="60" <?= ($tempo_reserva_selected === '60') ? 'selected' : '' ?>>1 hora</option>
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
                            <input type="hidden" name="metodo_pagamento" id="cup_metodo_pagamento" value="<?= htmlspecialchars($metodo_pagamento_selected) ?>">
                            <input type="hidden" name="tempo_reserva" id="cup_tempo_reserva" value="<?= htmlspecialchars($tempo_reserva_selected) ?>">
                            <button type="submit" class="btn btn-outline-secondary" name="aplicar_cupom">Aplicar</button>
                            <button type="submit" class="btn btn-outline-danger" name="remover_cupom" title="Remover cupom" style="margin-left:6px;">&times;</button>
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

<!-- Modal/Popup de confirmação de reserva -->
<div id="reserva-modal-backdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:2000;"></div>
<div id="reserva-modal" style="display:none; position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); z-index:2100; background:#fff; border-radius:12px; padding:22px; width:360px; max-width:92%; box-shadow:0 10px 40px rgba(0,0,0,0.35); text-align:center;">
    <div id="reserva-modal-content">
        <!-- Conteúdo preenchido dinamicamente -->
        <div id="reserva-loading" style="display:flex; flex-direction:column; align-items:center; gap:14px;">
            <div class="spinner-border text-primary" role="status" style="width:54px;height:54px;"><span class="visually-hidden">Loading...</span></div>
            <div style="font-weight:700;">Processando sua reserva...</div>
            <div style="font-size:13px; color:#666;">Aguarde um instante, estamos gravando seu pedido.</div>
        </div>
        <div id="reserva-success" style="display:none;">
            <div id="reserva-success-title" style="font-size:1.25rem; font-weight:800; color:#00A65A; margin-bottom:8px;">Reserva feita com sucesso</div>
            <div id="reserva-success-body" style="color:#333; margin-bottom:12px; font-size:0.95rem;">O seu pedido já está sendo preparado, você tem <span id="reserva-countdown" style="font-weight:700;">00:00</span> para chegar no estabelecimento e retirar seu pedido, caso contrário, sua reserva será cancelada.</div>
            <div style="display:flex; gap:10px; justify-content:center;">
                <button id="reserva-continuar" class="modal-btn-red">Continuar comprando</button>
                <button id="reserva-meuspedidos" class="modal-btn-yellow">Meus pedidos</button>
            </div>
        </div>
    </div>
</div>

<script>
// Intercepta o submit do form-reserva, envia via AJAX e mostra modal com loading + contagem
(function(){
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
        if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
    }

    btnContinuar.addEventListener('click', function(){
        window.location.href = '/Fws/FWS_Cliente/produto/HTML/produto.php';
    });
    btnMeusPedidos.addEventListener('click', function(){
        window.location.href = '/Fws/FWS_Cliente/meus_pedidos/HTML/Meus_pedidos.php';
    });
    // NOTA: deliberadamente não registramos listener para fechar o modal via backdrop
    // e não exibimos botão de fechar. O modal só é removido pelas ações internas (botões).

    function parseTimeToMs(timestr) {
        // espera formato HH:MM:SS
        const parts = timestr.split(':').map(Number);
        if (parts.length !== 3) return 0;
        return ((parts[0]*3600) + (parts[1]*60) + parts[2]) * 1000;
    }

    function formatRemaining(ms) {
        if (ms <= 0) return '00:00:00';
        const totalSec = Math.floor(ms/1000);
        const h = Math.floor(totalSec/3600);
        const m = Math.floor((totalSec%3600)/60);
        const s = totalSec % 60;
        if (h>0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    }

    form.addEventListener('submit', function(e){
        e.preventDefault();

        // Verifica se o carrinho está vazio (nenhum form com data-nome)
        const itensNoCarrinho = document.querySelectorAll("form[data-nome]").length;
        if (itensNoCarrinho === 0) {
            // mostra toast parecido com limite de estoque
            (function showEmptyCartToast(){
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
        fetch('', { method: 'POST', body: fd })
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
                    const created = data.data_criacao ? new Date(data.data_criacao.replace(' ', 'T')) : new Date();
                    const msLimit = parseTimeToMs(data.tempo_chegada || '00:30:00');
                    const deadline = new Date(created.getTime() + msLimit);

                    function updateCountdown(){
                        const now = new Date();
                        const rem = deadline.getTime() - now.getTime();
                        countdownEl.textContent = formatRemaining(rem);
                        if (rem <= 0) {
                            countdownEl.textContent = '00:00:00';
                            if (countdownInterval) { clearInterval(countdownInterval); countdownInterval = null; }
                        }
                    }

                    updateCountdown();
                    countdownInterval = setInterval(updateCountdown, 500);

                }, 4000);
            }).catch(err => {
                console.error('Erro ao enviar reserva', err);
                loading.style.display = 'none';
                success.style.display = 'block';
                document.getElementById('reserva-success-title').textContent = 'Erro ao efetuar reserva';
                // mostrar mensagem de erro mais informativa (trunca para evitar html enorme)
                const msg = err.message ? err.message : 'Ocorreu um erro ao processar seu pedido.';
                document.getElementById('reserva-success-body').textContent = msg.substring(0, 300);
            });
    });

})();
</script>
