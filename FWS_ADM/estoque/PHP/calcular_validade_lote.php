<?php

/**
 * Calcula a data de validade de um lote baseado nos meses padrão do produto
 * 
 * Equivalente à lógica do trigger: trg_insert_lote
 * 
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $produto_id ID do produto
 * @return array Array associativo com:
 *         - 'validade': Data da validade (Y-m-d) ou NULL se produto sem validade
 *         - 'validade_formatada': Data formatada (dd/mm/YYYY) ou "Sem validade"
 *         - 'meses': Número de meses da validade padrão
 *         - 'sucesso': true/false
 *         - 'mensagem': Descrição do resultado
 */
function calcularValidadeLote($conn, $produto_id) {
    try {
        // Validar o ID
        $produto_id = intval($produto_id);
        
        if ($produto_id <= 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'ID do produto inválido',
                'validade' => null,
                'validade_formatada' => 'N/A',
                'meses' => 0
            ];
        }
        
        // Buscar os meses de validade padrão do produto
        $query = "SELECT validade_padrao_meses FROM produtos WHERE id = $produto_id LIMIT 1";
        $resultado = $conn->query($query);
        
        if (!$resultado) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro na query: ' . $conn->error,
                'validade' => null,
                'validade_formatada' => 'N/A',
                'meses' => 0
            ];
        }
        
        if ($resultado->num_rows === 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado',
                'validade' => null,
                'validade_formatada' => 'N/A',
                'meses' => 0
            ];
        }
        
        $produto = $resultado->fetch_assoc();
        $meses = intval($produto['validade_padrao_meses']);
        
        // Se meses for NULL ou igual a 0, validade deve ser NULL (sem validade)
        if ($meses === null || $meses === 0) {
            return [
                'sucesso' => true,
                'mensagem' => 'Produto sem data de validade padrão',
                'validade' => null,
                'validade_formatada' => 'Sem validade',
                'meses' => 0
            ];
        }
        
        // Calcular a data de validade
        $data_validade = date('Y-m-d', strtotime("+$meses months"));
        $data_validade_formatada = date('d/m/Y', strtotime($data_validade));
        
        return [
            'sucesso' => true,
            'mensagem' => 'Validade calculada com sucesso',
            'validade' => $data_validade,
            'validade_formatada' => $data_validade_formatada,
            'meses' => $meses
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Erro: ' . $e->getMessage(),
            'validade' => null,
            'validade_formatada' => 'N/A',
            'meses' => 0
        ];
    }
}

/**
 * Busca a informação de validade formatada para exibição em tabelas
 * 
 * @param mysqli $conn Conexão com o banco de dados
 * @param int $produto_id ID do produto
 * @return string Texto formatado da validade para exibição
 */
function getValidadeFormatada($conn, $produto_id) {
    $resultado = calcularValidadeLote($conn, $produto_id);
    
    if ($resultado['sucesso']) {
        if ($resultado['validade'] === null) {
            return '<span style="color:#999; font-style:italic;">Sem validade</span>';
        } else {
            $dias_restantes = dateDifference($resultado['validade']);
            
            if ($dias_restantes < 0) {
                // Produto vencido
                return '<span style="color:#d11b1b; font-weight:bold;">⚠️ VENCIDO</span>';
            } elseif ($dias_restantes <= 10) {
                // Produto vencendo em breve
                return '<span style="color:#ff9500; font-weight:bold;">⏰ ' . $resultado['validade_formatada'] . ' (' . $dias_restantes . 'd)</span>';
            } else {
                // Produto válido
                return '<span style="color:#52c41a;">✓ ' . $resultado['validade_formatada'] . '</span>';
            }
        }
    } else {
        return '<span style="color:#999;">N/A</span>';
    }
}

/**
 * Calcula a diferença de dias entre hoje e uma data futura
 * 
 * @param string $data Data no formato Y-m-d
 * @return int Número de dias restantes (negativo se já passou)
 */
function dateDifference($data) {
    $data_obj = new DateTime($data);
    $hoje = new DateTime(date('Y-m-d'));
    $intervalo = $hoje->diff($data_obj);
    
    return $intervalo->invert ? -$intervalo->days : $intervalo->days;
}

// Se for chamado via GET para debug/teste
if (isset($_GET['action']) && isset($_GET['produto_id'])) {
    header('Content-Type: application/json');
    require_once '../../conn.php';
    
    if ($_GET['action'] === 'calcular') {
        $resultado = calcularValidadeLote($conn, $_GET['produto_id']);
        echo json_encode($resultado);
    } elseif ($_GET['action'] === 'teste') {
        // Teste com um produto
        $resultado = calcularValidadeLote($conn, intval($_GET['produto_id']));
        echo json_encode($resultado);
    }
}

?>
