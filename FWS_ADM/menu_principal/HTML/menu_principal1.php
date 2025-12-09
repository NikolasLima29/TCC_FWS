<?php
include "../../conn.php";
session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Busca nome do ADM
$stmt = $sql->prepare("SELECT nome FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome_adm);
$stmt->fetch();
$stmt->close();

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];

$pagina = 'painel';

?>

<!doctype html>
<html lang="pt-br">

<head>
    <title>Principal</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../CSS/menu_principal.css">

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">

</head>

<body>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <div class="col-auto px-sm-2 px-0 bg-dark" id="fund" style="width:250px; min-width:250px; max-width:250px;">
                    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                            <a id="logo-linha"><img src="../IMG/logo_linhas.png"></a>
                            <li class="nav-item">
                                <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php" class="nav-link align-middle px-0 <?php if($pagina=='painel') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/painelgeral.png"> <span class="ms-1 d-none d-sm-inline">Painel Geral</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0 <?php if($pagina=='fast') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/fastservice.png"> <span class="ms-1 d-none d-sm-inline">Fast Service</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0 <?php if($pagina=='financeiro') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/financeiro.png"> <span class="ms-1 d-none d-sm-inline">Financeiro</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0 <?php if($pagina=='vendas') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/vendaspai.png"> <span class="ms-1 d-none d-sm-inline">Vendas</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0 <?php if($pagina=='estoque') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/estoque.png"> <span class="ms-1 d-none d-sm-inline">Estoque</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/produtos/HTML/cadastro_produto.php" class="nav-link align-middle px-0 <?php if($pagina=='produtos') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/produtos.png"> <span class="ms-1 d-none d-sm-inline">Produtos</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php" class="nav-link align-middle px-0 <?php if($pagina=='fornecedores') echo 'active'; ?>" id="cor-fonte">
                                    <img src="../../menu_principal/IMG/fornecedor.png">
                                    <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                                </a>
                            </li>
                            <li>
                                <a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0 <?php if($pagina=='funcionarios') echo 'active'; ?>" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/funcionarios.png"> <span class="ms-1 d-none d-sm-inline">Funcionários</span></img>
                                </a>
                            </li>
                        </ul>
                        <hr>
                        <div class="dropdown pb-4">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../../fotodeperfiladm.png " width="30" height="30" class="rounded-circle">
                                <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                                <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair da conta</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col py-3" style="background: #fff8e1;">
                    <div class="container" style="max-width: 1200px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);">
                        <h3 id="texto">Painel Geral</h3>
                        <div class="mb-4" style="text-align:center; font-size:1.35rem; color:#444; font-weight:500;">
                            Olá, <span style="color:#ff9100; font-weight:bold;"><?= htmlspecialchars($nome_adm) ?></span>!<br>
                            <span style="font-size:1.05rem; color:#666; font-weight:400;">Que seu dia seja produtivo e repleto de conquistas. Conte conosco para alcançar grandes resultados!</span>
                        </div>
                        <div class="row g-4 mb-4 dashboard-cards">
                            <!-- Card 1: Estoque Baixo -->
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100 border-warning border-2 dashboard-card">
                                    <div class="card-header bg-warning text-dark fw-bold fs-5">Estoque Baixo</div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                        <?php
                                        $res = $sql->query("SELECT nome, estoque, foto_produto FROM produtos ORDER BY estoque ASC LIMIT 3");
                                        if ($res && $res->num_rows > 0) {
                                            while ($row = $res->fetch_assoc()) {
                                                $img = !empty($row['foto_produto']) ? $row['foto_produto'] : '../../IMG/sem-imagem.png';
                                                echo '<li class="list-group-item d-flex align-items-center justify-content-between">'
                                                    .'<div class="d-flex align-items-center">'
                                                    .'<img src="'.htmlspecialchars($img).'" alt="img" class="rounded me-2" style="width:38px;height:38px;object-fit:cover;border:1px solid #eee;">'
                                                    .'<span>'.htmlspecialchars($row['nome']).'</span>'
                                                    .'</div>'
                                                    .'<span class="badge bg-danger">'.intval($row['estoque']).'</span>'
                                                .'</li>';
                                            }
                                        } else {
                                            echo '<li class="list-group-item">Nenhum produto encontrado</li>';
                                        }
                                        ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 2: Validade/Vencidos -->
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100 border-danger border-2 dashboard-card">
                                    <div class="card-header bg-danger text-white fw-bold fs-5">Próximos do Vencimento</div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                        <?php
                                        $res = $sql->query("
                                            SELECT p.nome, l.validade, l.quantidade, p.foto_produto
                                            FROM lotes_produtos l
                                            JOIN produtos p ON l.produto_id = p.id
                                            WHERE l.quantidade > 0
                                            ORDER BY (l.validade < CURDATE()) DESC, l.validade ASC
                                            LIMIT 3
                                        ");
                                        if ($res && $res->num_rows > 0) {
                                            while ($row = $res->fetch_assoc()) {
                                                $vencido = (strtotime($row['validade']) < strtotime(date('Y-m-d')));
                                                $badge = $vencido ? '<span class=\'badge bg-danger\'>Vencido</span>' : '<span class=\'badge bg-warning text-dark\'>'.date('d/m/Y', strtotime($row['validade'])).'</span>';
                                                $img = !empty($row['foto_produto']) ? $row['foto_produto'] : '../../IMG/sem-imagem.png';
                                                echo '<li class="list-group-item d-flex align-items-center justify-content-between">'
                                                    .'<div class="d-flex align-items-center">'
                                                    .'<img src="'.htmlspecialchars($img).'" alt="img" class="rounded me-2" style="width:38px;height:38px;object-fit:cover;border:1px solid #eee;">'
                                                    .'<span>'.htmlspecialchars($row['nome']).'</span>'
                                                    .'</div>'
                                                    .$badge
                                                .'</li>';
                                            }
                                        } else {
                                            echo '<li class="list-group-item">Nenhum lote encontrado</li>';
                                        }
                                        ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Card 3: Mais Vendidos -->
                            <div class="col-md-4">
                                <div class="card shadow-sm h-100 border-success border-2 dashboard-card">
                                    <div class="card-header bg-success text-white fw-bold fs-5">Mais Vendidos</div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                        <?php
                                        $res = $sql->query("
                                            SELECT p.nome, p.foto_produto, SUM(iv.quantidade) as total_vendido
                                            FROM itens_vendidos iv
                                            JOIN produtos p ON iv.produto_id = p.id
                                            JOIN vendas v ON iv.venda_id = v.id
                                            WHERE MONTH(v.data_criacao) = MONTH(CURDATE()) AND YEAR(v.data_criacao) = YEAR(CURDATE())
                                            GROUP BY iv.produto_id
                                            ORDER BY total_vendido DESC
                                            LIMIT 3
                                        ");
                                        if ($res && $res->num_rows > 0) {
                                            while ($row = $res->fetch_assoc()) {
                                                $img = !empty($row['foto_produto']) ? $row['foto_produto'] : '../../IMG/sem-imagem.png';
                                                echo '<li class="list-group-item d-flex align-items-center justify-content-between">'
                                                    .'<div class="d-flex align-items-center">'
                                                    .'<img src="'.htmlspecialchars($img).'" alt="img" class="rounded me-2" style="width:38px;height:38px;object-fit:cover;border:1px solid #eee;">'
                                                    .'<span>'.htmlspecialchars($row['nome']).'</span>'
                                                    .'</div>'
                                                    .'<span class="badge bg-success">'.intval($row['total_vendido']).'</span>'
                                                .'</li>';
                                            }
                                        } else {
                                            echo '<li class="list-group-item">Nenhuma venda registrada</li>';
                                        }
                                        ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat IA Gemini -->
                        <div class="row g-4 mt-4">
                            <div class="col-12">
                                <div class="card shadow-lg border-0 chat-ia-container">
                                    <div class="card-header chat-ia-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-robot" viewBox="0 0 16 16">
                                                    <path d="M8 1a.5.5 0 0 1 .5.5v1.5H6V1.5a.5.5 0 0 1 1-0zM4 9a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm8 0a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm.007-4.951a.5.5 0 0 1 .124.994c-.5.048-1.045.157-1.518.315a.5.5 0 1 1-.216-.979c.533-.129 1.127-.228 1.763-.252zM3.68 4.063a.5.5 0 0 1 .216.979A7.01 7.01 0 0 0 3.051 4.1a.5.5 0 0 1 .424-.979zM1 13.5a.5.5 0 0 1 .5-.5h3v1.5h-3a.5.5 0 0 1-.5-.5zm12 0a.5.5 0 0 1 .5-.5h2v1h-2.5a.5.5 0 0 1-.5-.5z"/>
                                                </svg>
                                                <h5 class="m-0 text-white fw-bold">Assistente IA - FWS</h5>
                                            </div>
                                            <button type="button" class="btn-close btn-close-white" id="chat-close-btn" aria-label="Fechar"></button>
                                        </div>
                                    </div>
                                    <div class="chat-ia-body" id="chat-messages">
                                        <div class="chat-message bot-message">
                                            <div class="message-content">
                                                Olá! 👋 Sou seu assistente IA do sistema FWS. Posso responder perguntas sobre estoque, vendas, produtos, e como usar o sistema. O que você gostaria de saber?
                                            </div>
                                        </div>
                                    </div>
                                    <div class="chat-ia-footer">
                                        <div class="input-group">
                                            <input type="text" class="form-control chat-input" id="chat-input" placeholder="Digite sua pergunta..." autocomplete="off">
                                            <button class="btn chat-send-btn" type="button" id="chat-send-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="white" class="bi bi-send-fill" viewBox="0 0 16 16">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm0 3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>

    <!-- Chat IA Script -->
    <style>
        /* Chat IA Styles */
        .chat-ia-container {
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 600px;
            background: white;
            margin-bottom: 30px;
        }

        .chat-ia-header {
            background: linear-gradient(135deg, #ff9100 0%, #1a1a1a 100%);
            color: white;
            padding: 20px;
            border-bottom: 3px solid #ff9100;
        }

        .chat-ia-header h5 {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .chat-ia-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .chat-message {
            display: flex;
            margin-bottom: 12px;
            animation: slideIn 0.3s ease-in;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bot-message {
            justify-content: flex-start;
        }

        .user-message {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 12px;
            line-height: 1.6;
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
        }

        .bot-message .message-content {
            background-color: white;
            color: #1a1a1a;
            border-bottom-left-radius: 0;
            border: 2px solid #ff9100;
        }

        .user-message .message-content {
            background-color: #ff9100;
            color: #1a1a1a;
            border-bottom-right-radius: 0;
            font-weight: 500;
        }

        .chat-ia-footer {
            padding: 16px 20px;
            background-color: white;
            border-top: 2px solid #eee;
        }

        .chat-input {
            border: 2px solid #ff9100;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1rem;
            font-family: 'Ubuntu', sans-serif;
        }

        .chat-input:focus {
            border-color: #ff9100;
            box-shadow: 0 0 0 0.2rem rgba(255, 145, 0, 0.25);
            outline: none;
        }

        .chat-send-btn {
            background: linear-gradient(135deg, #ff9100 0%, #1a1a1a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 145, 0, 0.4);
            color: white;
        }

        .chat-send-btn:active {
            transform: scale(0.98);
        }

        .chat-send-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        #chat-close-btn {
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        #chat-close-btn:hover {
            opacity: 1;
        }

        .loading-spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #ff9100;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .chat-ia-container {
                max-height: 500px;
            }

            .message-content {
                max-width: 90%;
            }

            .chat-ia-header h5 {
                font-size: 1rem;
            }

            .chat-input {
                font-size: 0.95rem;
                padding: 10px 12px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatInput = document.getElementById('chat-input');
            const chatSendBtn = document.getElementById('chat-send-btn');
            const chatMessages = document.getElementById('chat-messages');

            chatSendBtn.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            function formatBotResponse(text) {
                // Remover espaços em branco no início (inclui espaços não-quebrantes)
                text = text.replace(/^[\s\u00A0\u2000-\u200B\uFEFF]+/, '');
                
                // Remover espaços em branco no final
                text = text.replace(/[\s\u00A0\u2000-\u200B\uFEFF]+$/, '');
                
                // Remover asteriscos de formatação markdown
                let formatted = text
                    .replace(/\*\*(.*?)\*\*/g, '$1')  // Remover **negrito**
                    .replace(/\*(.*?)\*/g, '$1')      // Remover *itálico*
                    .replace(/__(.*?)__/g, '$1')      // Remover __negrito__
                    .replace(/_(.*?)_/g, '$1');       // Remover _itálico_

                // Converter quebras de linha em <br>
                formatted = formatted
                    .replace(/\n/g, '<br>')
                    .replace(/<br><br>/g, '<br>');

                // Converter listas (* ou - no início da linha)
                formatted = formatted.replace(/^([\*\-])\s+/gm, '• ');

                return formatted;
            }

            function sendMessage() {
                const message = chatInput.value.trim();
                if (!message) return;

                // Adicionar mensagem do usuário
                const userMsgDiv = document.createElement('div');
                userMsgDiv.className = 'chat-message user-message';
                userMsgDiv.innerHTML = `<div class="message-content">${escapeHtml(message)}</div>`;
                chatMessages.appendChild(userMsgDiv);

                // Limpar input
                chatInput.value = '';
                chatInput.focus();

                // Desabilitar botão
                chatSendBtn.disabled = true;

                // Scroll para o final
                chatMessages.scrollTop = chatMessages.scrollHeight;

                // Enviar mensagem para a API
                fetch('../PHP/api_chat_gemini.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    chatSendBtn.disabled = false;
                    
                    if (data.success) {
                        const botMsgDiv = document.createElement('div');
                        botMsgDiv.className = 'chat-message bot-message';
                        const formattedResponse = formatBotResponse(data.response);
                        botMsgDiv.innerHTML = `<div class="message-content">${formattedResponse}</div>`;
                        chatMessages.appendChild(botMsgDiv);
                    } else {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'chat-message bot-message';
                        errorDiv.innerHTML = `<div class="message-content" style="border-color:#e74c3c; background-color: rgba(231, 76, 60, 0.1); color: #c0392b;">❌ Erro: ${escapeHtml(data.error || 'Erro desconhecido. Verifique sua chave da API Gemini.')}</div>`;
                        chatMessages.appendChild(errorDiv);
                    }
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => {
                    chatSendBtn.disabled = false;
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'chat-message bot-message';
                    errorDiv.innerHTML = `<div class="message-content" style="border-color:#e74c3c; background-color: rgba(231, 76, 60, 0.1); color: #c0392b;">❌ Erro de conexão: ${escapeHtml(error.message)}</div>`;
                    chatMessages.appendChild(errorDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }
        });
    </script>
</body>

</html>
