<?php

/**
 * FUNÇÕES PARA GERENCIAR DATA DE CHEGADA (DATA DE RECEBIMENTO) DOS LOTES
 * 
 * Arquivo: gerenciar_chegada_lote.php
 * 
 * Calcula e gerencia a data de chegada dos lotes baseado em:
 * - Data de validade MENOS os meses padrão do produto
 * - Ou data atual quando um novo lote é adicionado
 */

/**
 * Calcula a data de chegada (data de recebimento) do lote
 * 
 * Fórmula: Data de Chegada = Data de Validade - Meses Padrão
 * 
 * @param mysqli $conn Conexão com o banco
 * @param int $produto_id ID do produto
 * @param string|null $data_validade Data de validade no formato Y-m-d (opcional)
 * @return array Com 'sucesso', 'data_chegada', 'data_chegada_formatada', 'calculo'
 */
function calcularDataChegada($conn, $produto_id, $data_validade = null) {
    try {
        $produto_id = intval($produto_id);
        
        if ($produto_id <= 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'ID inválido',
                'data_chegada' => null
            ];
        }
        
        // Buscar meses padrão do produto
        $query = "SELECT validade_padrao_meses FROM produtos WHERE id = $produto_id LIMIT 1";
        $resultado = $conn->query($query);
        
        if (!$resultado || $resultado->num_rows === 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado',
                'data_chegada' => null
            ];
        }
        
        $produto = $resultado->fetch_assoc();
        $meses_padrao = intval($produto['validade_padrao_meses']);
        
        // Se não passou data de validade, pega hoje
        if ($data_validade === null) {
            $data_chegada = date('Y-m-d');
        } else {
            // Calcula: Validade - Meses Padrão = Data de Chegada
            $data_chegada = date('Y-m-d', strtotime("-$meses_padrao months", strtotime($data_validade)));
        }
        
        $data_chegada_formatada = date('d/m/Y', strtotime($data_chegada));
        
        return [
            'sucesso' => true,
            'mensagem' => 'Data de chegada calculada',
            'data_chegada' => $data_chegada,
            'data_chegada_formatada' => $data_chegada_formatada,
            'meses_padrao' => $meses_padrao,
            'calculo' => $data_validade ? "Validade ($data_validade) - $meses_padrao meses = $data_chegada" : "Hoje = $data_chegada"
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Erro: ' . $e->getMessage(),
            'data_chegada' => null
        ];
    }
}

/**
 * Preenche a data de chegada para lotes que não têm (registros antigos)
 * 
 * Usa a fórmula: Data de Chegada = Validade - Meses Padrão
 * 
 * @param mysqli $conn Conexão com o banco
 * @return array Com informações sobre quantos registros foram atualizados
 */
function preencherChegadaRetroativamente($conn) {
    try {
        // Query para encontrar lotes SEM data de chegada
        $query = "
            SELECT lp.id, lp.produto_id, lp.validade, p.validade_padrao_meses
            FROM lotes_produtos lp
            JOIN produtos p ON lp.produto_id = p.id
            WHERE lp.chegada IS NULL
            AND lp.validade IS NOT NULL
            LIMIT 1000
        ";
        
        $resultado = $conn->query($query);
        
        if (!$resultado) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro na query: ' . $conn->error,
                'atualizados' => 0
            ];
        }
        
        $atualizados = 0;
        $erros = [];
        
        while ($lote = $resultado->fetch_assoc()) {
            $lote_id = $lote['id'];
            $meses = intval($lote['validade_padrao_meses']);
            $validade = $lote['validade'];
            
            // Calcular: Validade - Meses = Data de Chegada
            $data_chegada = date('Y-m-d', strtotime("-$meses months", strtotime($validade)));
            
            // Atualizar o lote
            $update_query = "UPDATE lotes_produtos SET chegada = '$data_chegada' WHERE id = $lote_id";
            
            if ($conn->query($update_query)) {
                $atualizados++;
            } else {
                $erros[] = "Lote $lote_id: " . $conn->error;
            }
        }
        
        return [
            'sucesso' => true,
            'mensagem' => "$atualizados lotes preenchidos com data de chegada retroativa",
            'atualizados' => $atualizados,
            'erros' => $erros
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Exceção: ' . $e->getMessage(),
            'atualizados' => 0
        ];
    }
}

/**
 * Insere um novo lote COM data de chegada automática
 * 
 * @param mysqli $conn Conexão com o banco
 * @param int $produto_id ID do produto
 * @param int $quantidade Quantidade do lote
 * @param string|null $data_validade Data de validade (opcional)
 * @param int|null $fornecedor_id ID do fornecedor (opcional)
 * @return array Com informações do lote inserido
 */
function inserirLoteComChegada($conn, $produto_id, $quantidade, $data_validade = null, $fornecedor_id = null) {
    try {
        $produto_id = intval($produto_id);
        $quantidade = intval($quantidade);
        
        if ($produto_id <= 0 || $quantidade <= 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Dados inválidos',
                'lote_id' => null
            ];
        }
        
        // Buscar dados do produto
        $query = "SELECT validade_padrao_meses, fornecedor_id, nome FROM produtos WHERE id = $produto_id";
        $resultado = $conn->query($query);
        
        if (!$resultado || $resultado->num_rows === 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado',
                'lote_id' => null
            ];
        }
        
        $produto = $resultado->fetch_assoc();
        $meses_padrao = intval($produto['validade_padrao_meses']);
        $fornecedor_id = $fornecedor_id ?? $produto['fornecedor_id'];
        
        // Calcular validade se não foi passada
        if ($data_validade === null && $meses_padrao > 0) {
            $data_validade = date('Y-m-d', strtotime("+$meses_padrao months"));
        }
        
        // Calcular data de chegada (sempre HOJE para novos lotes)
        $data_chegada = date('Y-m-d');
        
        // Preparar SQL
        $validade_sql = $data_validade ? "'$data_validade'" : "NULL";
        $fornecedor_sql = $fornecedor_id ? intval($fornecedor_id) : "NULL";
        
        $insert_query = "
            INSERT INTO lotes_produtos (produto_id, quantidade, validade, chegada, fornecedor_id)
            VALUES ($produto_id, $quantidade, $validade_sql, '$data_chegada', $fornecedor_sql)
        ";
        
        if (!$conn->query($insert_query)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao inserir lote: ' . $conn->error,
                'lote_id' => null
            ];
        }
        
        $lote_id = $conn->insert_id;
        
        return [
            'sucesso' => true,
            'mensagem' => 'Lote adicionado com sucesso',
            'lote_id' => $lote_id,
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'data_chegada' => $data_chegada,
            'data_chegada_formatada' => date('d/m/Y', strtotime($data_chegada)),
            'data_validade' => $data_validade,
            'data_validade_formatada' => $data_validade ? date('d/m/Y', strtotime($data_validade)) : 'N/A'
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
 * Formata a data de chegada para exibição
 * 
 * @param string $data_chegada Data no formato Y-m-d
 * @return string Data formatada dd/mm/YYYY
 */
function formatarDataChegada($data_chegada) {
    if (!$data_chegada) {
        return 'N/A';
    }
    return date('d/m/Y', strtotime($data_chegada));
}

// Se for chamado via GET para testes
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    require_once '../../conn.php';
    
    if ($_GET['action'] === 'calcular' && isset($_GET['produto_id'])) {
        $resultado = calcularDataChegada($conn, $_GET['produto_id'], $_GET['validade'] ?? null);
        echo json_encode($resultado);
    } elseif ($_GET['action'] === 'preencher_retroativo') {
        $resultado = preencherChegadaRetroativamente($conn);
        echo json_encode($resultado);
    } elseif ($_GET['action'] === 'inserir' && isset($_GET['produto_id'])) {
        $resultado = inserirLoteComChegada(
            $conn, 
            $_GET['produto_id'], 
            $_GET['quantidade'] ?? 24,
            $_GET['validade'] ?? null,
            $_GET['fornecedor_id'] ?? null
        );
        echo json_encode($resultado);
    }
}

?>
