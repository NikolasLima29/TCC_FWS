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

// Definir fornecedor_id no início
$fornecedor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Processar adição geral de lote para todos os produtos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repor_estoque_geral'])) {
    $quantidade = isset($_POST['quantidade_geral']) && $_POST['quantidade_geral'] > 0 ? intval($_POST['quantidade_geral']) : 24;
    
    // Buscar todos os produtos do fornecedor
    $sql_produtos = "SELECT id, validade_padrao_meses, fornecedor_id FROM produtos WHERE fornecedor_id = $fornecedor_id";
    $res_produtos = $sql->query($sql_produtos);
    
    if ($res_produtos && $res_produtos->num_rows > 0) {
        while ($row_prod = $res_produtos->fetch_assoc()) {
            $produto_id = $row_prod['id'];
            $validade_padrao = $row_prod['validade_padrao_meses'];
            $fornecedor_id_prod = $row_prod['fornecedor_id'];
            
            // Calcular data de validade
            $data_validade = NULL;
            if ($validade_padrao && $validade_padrao > 0) {
                $data_validade = date('Y-m-d', strtotime("+$validade_padrao months"));
            }
            
            // Inserir novo lote em lotes_produtos
            $sql_lote = "INSERT INTO lotes_produtos (produto_id, quantidade, validade, fornecedor_id) 
                         VALUES ($produto_id, $quantidade, " . ($data_validade ? "'$data_validade'" : "NULL") . ", " . ($fornecedor_id_prod ? $fornecedor_id_prod : "NULL") . ")";
            if (!$sql->query($sql_lote)) {
                die("Erro ao criar lote: " . $sql->error);
            }
            
            // Registrar entrada na tabela movimentacao_estoque
            $sql_insert = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                           VALUES ($produto_id, 'entrada', $quantidade, NOW())";
            if (!$sql->query($sql_insert)) {
                die("Erro ao registrar movimentação: " . $sql->error);
            }
        }
    }
    
    // Redirecionar para atualizar a página
    header("Location: produtos_por_fornecedor.php?id=" . $_GET['id'] . "&sucesso=lote_geral");
    exit;
}

// Processar reposição de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repor_estoque'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = isset($_POST['quantidade_custom']) && $_POST['quantidade_custom'] > 0 ? intval($_POST['quantidade_custom']) : 24;
    
    // 1. Buscar dados do produto
    $sql_produto = "SELECT validade_padrao_meses, fornecedor_id FROM produtos WHERE id = $produto_id";
    $res_produto = $sql->query($sql_produto);
    if ($res_produto && $res_produto->num_rows > 0) {
        $produto = $res_produto->fetch_assoc();
        $validade_padrao = $produto['validade_padrao_meses'];
        $fornecedor_id = $produto['fornecedor_id'];
        
        // 2. Calcular data de validade
        $data_validade = NULL;
        if ($validade_padrao && $validade_padrao > 0) {
            $data_validade = date('Y-m-d', strtotime("+$validade_padrao months"));
        }
        
        // 3. Inserir novo lote em lotes_produtos
        $sql_lote = "INSERT INTO lotes_produtos (produto_id, quantidade, validade, fornecedor_id) 
                     VALUES ($produto_id, $quantidade, " . ($data_validade ? "'$data_validade'" : "NULL") . ", " . ($fornecedor_id ? $fornecedor_id : "NULL") . ")";
        if (!$sql->query($sql_lote)) {
            die("Erro ao criar lote: " . $sql->error);
        }
        
        // 4. Registrar entrada na tabela movimentacao_estoque
        $sql_insert = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                       VALUES ($produto_id, 'entrada', $quantidade, NOW())";
        if (!$sql->query($sql_insert)) {
            die("Erro ao registrar movimentação: " . $sql->error);
        }
    } else {
        die("Produto não encontrado");
    }
    
    // Redirecionar para atualizar a página
    header("Location: produtos_por_fornecedor.php?id=" . $_GET['id'] . "&sucesso=lote_individual");
    exit;
}

// Processar retirada de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retirar_estoque'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade_retirada = isset($_POST['quantidade_retirada']) && $_POST['quantidade_retirada'] > 0 ? intval($_POST['quantidade_retirada']) : 1;
    $tipo_retirada = isset($_POST['tipo_retirada']) ? $_POST['tipo_retirada'] : 'outros';
    $motivo_retirada = isset($_POST['motivo_retirada']) && !empty($_POST['motivo_retirada']) ? $_POST['motivo_retirada'] : NULL;
    
    // Buscar estoque atual do produto
    $sql_check = "SELECT estoque FROM produtos WHERE id = $produto_id";
    $res_check = $sql->query($sql_check);
    if ($res_check && $res_check->num_rows > 0) {
        $row_check = $res_check->fetch_assoc();
        $estoque_atual = $row_check['estoque'];
        
        // Verificar se há quantidade suficiente
        if ($estoque_atual >= $quantidade_retirada) {
            // Registrar saída na tabela movimentacao_estoque
            $sql_saida = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                         VALUES ($produto_id, 'saida', $quantidade_retirada, NOW())";
            if (!$sql->query($sql_saida)) {
                die("Erro ao registrar movimentação: " . $sql->error);
            }
            
            // Registrar na tabela retiradas
            $motivo_sql = $motivo_retirada ? "'" . $sql->real_escape_string($motivo_retirada) . "'" : "NULL";
            $sql_retirada = "INSERT INTO retiradas (produto_id, funcionario_id, quantidade, tipo_motivo, motivo) 
                            VALUES ($produto_id, $id, $quantidade_retirada, '$tipo_retirada', $motivo_sql)";
            if (!$sql->query($sql_retirada)) {
                die("Erro ao registrar retirada: " . $sql->error);
            }
        } else {
            die("Quantidade insuficiente em estoque. Disponível: $estoque_atual");
        }
    } else {
        die("Produto não encontrado");
    }
    
    // Redirecionar para atualizar a página
    header("Location: produtos_por_fornecedor.php?id=" . $_GET['id']);
    exit;
}

// Buscar nome do fornecedor
$fornecedor_query = "SELECT nome FROM fornecedores WHERE id = $fornecedor_id LIMIT 1";
$fornecedor_result = $sql->query($fornecedor_query);
$fornecedor_nome = ($fornecedor_result->num_rows > 0) ? $fornecedor_result->fetch_assoc()['nome'] : "Fornecedor desconhecido";

// Buscar produtos deste fornecedor
$query = "SELECT p.id, p.nome, p.preco_venda, p.estoque
          FROM produtos p
          WHERE p.fornecedor_id = $fornecedor_id
          ORDER BY p.id ASC";
$result = $sql->query($query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos do Fornecedor - <?php echo htmlspecialchars($fornecedor_nome); ?></title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #fff8e1;
    font-family: "Poppins", sans-serif;
}

/* 🔹 Barra lateral fixa */
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

/* 🔹 Conteúdo */
#conteudo-principal {
    margin-left: 250px;
    padding: 40px;
}

.container {
    max-width: 900px;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #ff9100;
    font-weight: bold;
}

table th, table td {
    text-align: center;
}

.table thead.table-dark {
    background-color: #ff9100;
}

.table thead.table-dark th {
    background-color: #ff9100;
    color: white;
    border-color: #ff9100;
    border-right: 1px solid #e68000;
}

.table thead.table-dark th:last-child {
    border-right: none;
}

.btn-reposicao {
    background-color: #52c41a;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.85rem;
    cursor: pointer;
}

.btn-reposicao:hover {
    background-color: #389e0d;
}
</style>
</head>
<body>

<div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- NAVBAR -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">

                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                        <li class="nav-item">
                            <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a></li>
                    </ul>

                    <hr>

                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <img src="../../fotodeperfiladm.png" width="30" height="30" class="rounded-circle">
                            <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>
        <!-- 🔹 FIM DA NAVBAR -->

        <!-- 🔹 CONTEÚDO PRINCIPAL -->
        <div class="col py-3" id="conteudo-principal">
            <div class="container">

                <h2>Produtos do Fornecedor: <span style="color:#f4a01d;"><?php echo htmlspecialchars($fornecedor_nome); ?></span></h2>

                <button type="button" class="btn btn-warning" style="margin-bottom:20px; font-weight:bold;" onclick="abrirModalLoteGeral()">📦 Adicionar Lote Geral</button>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td>R$ <?php echo number_format($row['preco_venda'],2,',','.'); ?></td>
                                <td><?php echo $row['estoque']; ?></td>
                                <td>
                                    <button type="button" class="btn-reposicao" onclick="confirmarAdicao(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nome']) ?>')">+1 lote</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nenhum produto encontrado para este fornecedor.</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>

                <a href="lista_fornecedores.php" class="btn btn-dark mt-3">⬅ Voltar para Fornecedores</a>

                <!-- Modal de Confirmação de Adição -->
                <div id="modalConfirmacao" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background-color:white; padding:30px; border-radius:8px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:400px;">
                        <h3 style="color:#ff9100; margin-bottom:20px;">Adicionar Lote</h3>
                        <p id="textoProduto" style="margin-bottom:20px; font-size:1rem; color:#333;"></p>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:10px; font-weight:bold; color:#333;">Quantidade de unidades:</label>
                            <input type="number" id="quantidadeInput" min="1" value="24" style="width:100%; padding:10px; border:2px solid #ddd; border-radius:4px; font-size:1rem; text-align:center;">
                        </div>
                        <div style="display:flex; gap:10px; justify-content:center;">
                            <button onclick="cancelarAdicao()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                            <form id="formConfirmacao" method="post" style="display:inline;">
                                <input type="hidden" name="produto_id" id="produtoId">
                                <input type="hidden" name="quantidade_custom" id="quantidadeCustom">
                                <button type="submit" name="repor_estoque" onclick="return setarQuantidade()" style="padding:10px 30px; background-color:#52c41a; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Confirmar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Retirada -->
                <div id="modalRetirada" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background-color:white; padding:30px; border-radius:8px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:450px;">
                        <h3 style="color:#ff7875; margin-bottom:20px;">Retirada de Estoque</h3>
                        <p id="textoProdutoRetirada" style="margin-bottom:20px; font-size:1rem; color:#333;"></p>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:10px; font-weight:bold; color:#333;">Quantidade de unidades a retirar:</label>
                            <input type="number" id="quantidadeRetiradaInput" min="1" value="1" style="width:100%; padding:10px; border:2px solid #ddd; border-radius:4px; font-size:1rem; text-align:center;">
                        </div>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; margin-bottom:10px; font-weight:bold; color:#333;">Tipo de Retirada:</label>
                            <select id="tipoRetiradaInput" style="width:100%; padding:10px; border:2px solid #ddd; border-radius:4px; font-size:1rem;">
                                <option value="uso_interno">Uso Interno</option>
                                <option value="roubo">Roubo</option>
                                <option value="quebra">Quebra</option>
                                <option value="doacao">Doação</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:10px; font-weight:bold; color:#333;">Descrição (opcional):</label>
                            <textarea id="motivoInput" placeholder="Descreva o motivo da retirada..." style="width:100%; padding:10px; border:2px solid #ddd; border-radius:4px; font-size:1rem; resize:vertical; min-height:80px;"></textarea>
                        </div>
                        <div style="display:flex; gap:10px; justify-content:center;">
                            <button onclick="cancelarRetirada()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                            <form id="formRetirada" method="post" style="display:inline;">
                                <input type="hidden" name="produto_id" id="produtoIdRetirada">
                                <input type="hidden" name="quantidade_retirada" id="quantidadeRetiradaCustom">
                                <input type="hidden" name="tipo_retirada" id="tipoRetiradaCustom">
                                <input type="hidden" name="motivo_retirada" id="motivoCustom">
                                <button type="submit" name="retirar_estoque" onclick="setarQuantidadeRetirada()" style="padding:10px 30px; background-color:#ff7875; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Confirmar</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Lote Geral -->
                <div id="modalLoteGeral" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background-color:white; padding:30px; border-radius:8px; text-align:center; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-width:400px;">
                        <h3 style="color:#ff9100; margin-bottom:20px;">Adicionar Lote Geral</h3>
                        <p style="margin-bottom:20px; font-size:1rem; color:#333;">Adicionar a mesma quantidade de lote para <strong>todos os produtos</strong> do fornecedor?</p>
                        <div style="margin-bottom:25px;">
                            <label style="display:block; margin-bottom:10px; font-weight:bold; color:#333;">Quantidade de unidades:</label>
                            <input type="number" id="quantidadeGeralInput" min="1" value="24" style="width:100%; padding:10px; border:2px solid #ddd; border-radius:4px; font-size:1rem; text-align:center;">
                        </div>
                        <div style="display:flex; gap:10px; justify-content:center;">
                            <button onclick="fecharModalLoteGeral()" style="padding:10px 30px; background-color:#999; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Cancelar</button>
                            <form id="formLoteGeral" method="post" style="display:inline;">
                                <input type="hidden" name="quantidade_geral" id="quantidadeGeralCustom">
                                <button type="submit" name="repor_estoque_geral" onclick="return setarQuantidadeGeral()" style="padding:10px 30px; background-color:#faad14; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">Confirmar</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Verificar se há mensagem de sucesso na URL
    const urlParams = new URLSearchParams(window.location.search);
    const sucesso = urlParams.get('sucesso');

    if (sucesso === 'lote_geral') {
        alert('✅ Lote geral adicionado com sucesso para todos os produtos!');
        // Remover o parâmetro da URL para não aparecer novamente
        window.history.replaceState({}, document.title, window.location.pathname + '?id=' + urlParams.get('id'));
    } else if (sucesso === 'lote_individual') {
        alert('✅ Lote adicionado com sucesso!');
        // Remover o parâmetro da URL para não aparecer novamente
        window.history.replaceState({}, document.title, window.location.pathname + '?id=' + urlParams.get('id'));
    }

    function confirmarAdicao(produtoId, nomeProduto) {
        document.getElementById('produtoId').value = produtoId;
        document.getElementById('quantidadeInput').value = '24';
        document.getElementById('textoProduto').textContent = 'Adicionar ' + nomeProduto;
        document.getElementById('modalConfirmacao').style.display = 'flex';
    }

    function cancelarAdicao() {
        document.getElementById('modalConfirmacao').style.display = 'none';
    }

    function setarQuantidade() {
        const quantidade = parseInt(document.getElementById('quantidadeInput').value);
        if (quantidade <= 0) {
            alert('⚠️ A quantidade deve ser maior que 0!');
            return false;
        }
        document.getElementById('quantidadeCustom').value = quantidade;
        return true;
    }

    function confirmarRetirada(produtoId, nomeProduto) {
        document.getElementById('produtoIdRetirada').value = produtoId;
        document.getElementById('quantidadeRetiradaInput').value = '1';
        document.getElementById('textoProdutoRetirada').textContent = 'Retirada de ' + nomeProduto;
        document.getElementById('modalRetirada').style.display = 'flex';
    }

    function cancelarRetirada() {
        document.getElementById('modalRetirada').style.display = 'none';
    }

    function setarQuantidadeRetirada() {
        document.getElementById('quantidadeRetiradaCustom').value = document.getElementById('quantidadeRetiradaInput').value;
        document.getElementById('tipoRetiradaCustom').value = document.getElementById('tipoRetiradaInput').value;
        document.getElementById('motivoCustom').value = document.getElementById('motivoInput').value;
    }

    function abrirModalLoteGeral() {
        document.getElementById('quantidadeGeralInput').value = '24';
        document.getElementById('modalLoteGeral').style.display = 'flex';
    }

    function fecharModalLoteGeral() {
        document.getElementById('modalLoteGeral').style.display = 'none';
    }

    function setarQuantidadeGeral() {
        const quantidade = parseInt(document.getElementById('quantidadeGeralInput').value);
        if (quantidade <= 0) {
            alert('⚠️ A quantidade deve ser maior que 0!');
            return false;
        }
        document.getElementById('quantidadeGeralCustom').value = quantidade;
        return true;
    }
</script>

</body>
</html>

<?php $sql->close(); ?>
