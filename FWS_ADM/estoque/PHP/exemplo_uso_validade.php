<?php

/**
 * EXEMPLO DE INTEGRAÇÃO: Função para usar no formulário de adicionar lote
 * 
 * Este arquivo mostra como integrar a função calcularValidadeLote()
 * no seu formulário de adição de lotes
 */

// Assumindo que você tem a conexão e as funções importadas
// include "../../conn.php";
// include "calcular_validade_lote.php";

/**
 * Função auxiliar para processar a adição de lote com cálculo automático
 * Substitui a lógica manual de cálculo de validade
 */
function adicionarLoteComValidadeAutomatica($conn, $produto_id, $quantidade) {
    try {
        // Validar entrada
        $produto_id = intval($produto_id);
        $quantidade = intval($quantidade);
        
        if ($produto_id <= 0 || $quantidade <= 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'lote_id' => null
            ];
        }
        
        // 1. Buscar dados do produto
        $sql_produto = "SELECT validade_padrao_meses, fornecedor_id, nome FROM produtos WHERE id = $produto_id";
        $res_produto = $conn->query($sql_produto);
        
        if (!$res_produto || $res_produto->num_rows === 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado',
                'lote_id' => null
            ];
        }
        
        $produto = $res_produto->fetch_assoc();
        $fornecedor_id = $produto['fornecedor_id'] ?? null;
        $nome_produto = $produto['nome'] ?? 'Produto desconhecido';
        
        // 2. Calcular validade automaticamente
        $resultado_validade = calcularValidadeLote($conn, $produto_id);
        
        if (!$resultado_validade['sucesso']) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao calcular validade: ' . $resultado_validade['mensagem'],
                'lote_id' => null
            ];
        }
        
        $data_validade = $resultado_validade['validade']; // Pode ser NULL
        
        // 3. Inserir novo lote em lotes_produtos
        $validade_sql = $data_validade ? "'{$data_validade}'" : "NULL";
        $fornecedor_sql = $fornecedor_id ? $fornecedor_id : "NULL";
        
        $sql_lote = "INSERT INTO lotes_produtos (produto_id, quantidade, validade, fornecedor_id) 
                     VALUES ($produto_id, $quantidade, {$validade_sql}, {$fornecedor_sql})";
        
        if (!$conn->query($sql_lote)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao inserir lote: ' . $conn->error,
                'lote_id' => null
            ];
        }
        
        $lote_id = $conn->insert_id;
        
        // 4. Registrar entrada na tabela movimentacao_estoque
        $sql_movimento = "INSERT INTO movimentacao_estoque (produto_id, tipo_movimentacao, quantidade, data_movimentacao) 
                         VALUES ($produto_id, 'entrada', $quantidade, NOW())";
        
        if (!$conn->query($sql_movimento)) {
            // Deletar o lote se falhar a movimentação
            $conn->query("DELETE FROM lotes_produtos WHERE id = $lote_id");
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao registrar movimentação: ' . $conn->error,
                'lote_id' => null
            ];
        }
        
        // Sucesso!
        return [
            'sucesso' => true,
            'mensagem' => "Lote adicionado com sucesso! " . 
                         ($data_validade ? 
                            "Validade: {$resultado_validade['validade_formatada']}" : 
                            "Sem data de validade"),
            'lote_id' => $lote_id,
            'produto_id' => $produto_id,
            'nome_produto' => $nome_produto,
            'quantidade' => $quantidade,
            'validade' => $data_validade,
            'validade_formatada' => $resultado_validade['validade_formatada'],
            'meses' => $resultado_validade['meses']
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Exceção: ' . $e->getMessage(),
            'lote_id' => null
        ];
    }
}

/**
 * EXEMPLO DE USO EM estoque.php
 * 
 * Adicione isto no seu bloco de POST:
 */

/*
// Processar reposição de estoque COM CÁLCULO AUTOMÁTICO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repor_estoque'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = isset($_POST['quantidade_custom']) && $_POST['quantidade_custom'] > 0 ? 
                  intval($_POST['quantidade_custom']) : 24;
    
    // Usar a função que calcula a validade automaticamente
    $resultado = adicionarLoteComValidadeAutomatica($sql, $produto_id, $quantidade);
    
    if ($resultado['sucesso']) {
        // Log ou notificação de sucesso
        $_SESSION['sucesso'] = $resultado['mensagem'];
        header('Location: estoque.php?sucesso=lote_adicionado');
    } else {
        $_SESSION['erro'] = $resultado['mensagem'];
        header('Location: estoque.php?erro=1');
    }
    exit;
}
*/

/**
 * EXEMPLO NA TABELA: Exibir validade calculada
 */

/*
<?php while ($row = $result->fetch_assoc()): 
    $produto_id = $row['id'];
    $validade_exibicao = getValidadeFormatada($sql, $produto_id);
?>
    <tr>
        <td>...</td>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <!-- ... mais colunas ... -->
        <td><?= $validade_exibicao ?></td>
    </tr>
<?php endwhile; ?>
*/

/**
 * EXEMPLO COMPLETO: Formulário Modal para Adicionar Lote
 * 
 * HTML do Modal:
 */

/*
<div id="modalConfirmacao" style="display:none; position:fixed; ...">
    <div style="background-color:white; padding:30px; ...">
        <h3>Adicionar Lote</h3>
        
        <!-- Exibir validade calculada ANTES de confirmar -->
        <div id="infoValidadeCalculada" style="display:none; background-color:#f0f5ff; padding:15px; border-radius:4px; margin-bottom:15px;">
            <p style="margin:0; color:#0050b3;">
                <strong>Validade calculada:</strong> <span id="validadeCalculada">-</span>
            </p>
        </div>
        
        <p id="textoProduto" style="margin-bottom:20px;"></p>
        
        <div style="margin-bottom:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:bold;">Quantidade:</label>
            <input type="number" id="quantidadeInput" min="1" value="24" 
                   onchange="atualizarValidadePreview()">
        </div>
        
        <div style="display:flex; gap:10px; justify-content:center;">
            <button onclick="cancelarAdicao()" style="padding:10px 30px; ...">Cancelar</button>
            <button onclick="confirmarAdicaoComValidade()" style="padding:10px 30px; ...">Confirmar</button>
        </div>
    </div>
</div>

<script>
function atualizarValidadePreview() {
    const produtoId = document.getElementById('produtoId').value;
    
    if (!produtoId) return;
    
    // Chamar via AJAX para calcular a validade
    fetch('/fws/FWS_ADM/estoque/PHP/calcular_validade_lote.php?action=calcular&produto_id=' + produtoId)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                const infoDiv = document.getElementById('infoValidadeCalculada');
                const validadeSpan = document.getElementById('validadeCalculada');
                
                if (data.validade) {
                    validadeSpan.textContent = data.validade_formatada;
                    infoDiv.style.display = 'block';
                } else {
                    validadeSpan.textContent = 'Produto sem data de validade padrão';
                    infoDiv.style.display = 'block';
                }
            }
        });
}
</script>
*/

// Se for chamado via GET para testes
if (isset($_GET['teste'])) {
    header('Content-Type: application/json');
    
    // Exemplo de teste
    $teste = [
        'exemplo_1' => [
            'descricao' => 'Produto com 12 meses de validade',
            'resultado' => 'Irá calcular 12 meses a partir de hoje'
        ],
        'exemplo_2' => [
            'descricao' => 'Produto sem validade padrão',
            'resultado' => 'Irá retornar NULL (Sem validade)'
        ],
        'exemplo_3' => [
            'descricao' => 'Uso na tabela',
            'resultado' => 'Mostrar em verde se válido, laranja se próximo ao vencimento'
        ]
    ];
    
    echo json_encode($teste);
}

?>
