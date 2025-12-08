<?php
// Incluir a conexão com o banco de dados
require_once __DIR__ . '/../conn.php';

/**
 * Verifica se algum pedido ultrapassou o tempo limite e o cancela automaticamente
 * Atualiza tanto a tabela 'vendas' como a situação do pedido se necessário
 * 
 * @param mysqli $sql Conexão com o banco de dados
 * @return array Array com informações sobre os pedidos cancelados
 */
function verificarECancelarPedidosComTempoExpirado($sql) {
    try {
        // Query para encontrar vendas que ultrapassaram o tempo limite
        $query = "
            SELECT 
                v.id,
                v.usuario_id,
                v.data_criacao,
                v.tempo_chegada,
                v.situacao_compra,
                TIMESTAMPDIFF(SECOND, v.data_criacao, NOW()) AS segundos_decorridos,
                TIME_TO_SEC(v.tempo_chegada) AS tempo_limite_segundos
            FROM vendas v
            WHERE v.situacao_compra IN ('em_preparo', 'pronto_para_retirar')
              AND v.tempo_chegada IS NOT NULL
              AND TIMESTAMPDIFF(SECOND, v.data_criacao, NOW()) > TIME_TO_SEC(v.tempo_chegada)
        ";
        
        $resultado = $sql->query($query);
        
        if (!$resultado) {
            throw new Exception("Erro na query: " . $sql->error);
        }
        
        $pedidosCancelados = [];
        
        // Processa cada venda que ultrapassou o tempo limite
        while ($venda = $resultado->fetch_assoc()) {
            // Atualizar o status da venda para cancelada
            $updateQuery = "
                UPDATE vendas 
                SET situacao_compra = 'cancelada'
                WHERE id = " . intval($venda['id']) . "
            ";
            if (!$sql->query($updateQuery)) {
                throw new Exception("Erro ao atualizar venda: " . $sql->error);
            }

            // Apagar itens vendidos relacionados à venda cancelada
            // $deleteItensQuery = "DELETE FROM itens_vendidos WHERE venda_id = " . intval($venda['id']);
            // if (!$sql->query($deleteItensQuery)) {
            //     throw new Exception("Erro ao apagar itens_vendidos: " . $sql->error);
            // }

            // Registrar na tabela expiracoes_pre_compras
            $insertExpiracaoQuery = "
                INSERT INTO expiracoes_pre_compras (usuario_id, venda_id, data_expiracao)
                VALUES (" . intval($venda['usuario_id']) . ", " . intval($venda['id']) . ", NOW())
            ";
            if (!$sql->query($insertExpiracaoQuery)) {
                throw new Exception("Erro ao registrar expiração: " . $sql->error);
            }
            
            // Registrar a venda cancelada
            $pedidosCancelados[] = [
                'venda_id' => $venda['id'],
                'usuario_id' => $venda['usuario_id'],
                'data_criacao' => $venda['data_criacao'],
                'tempo_limite' => $venda['tempo_chegada'],
                'segundos_decorridos' => $venda['segundos_decorridos'],
                'tempo_limite_segundos' => $venda['tempo_limite_segundos']
            ];
        }
        
        return [
            'sucesso' => true,
            'mensagem' => count($pedidosCancelados) . ' pedido(s) cancelado(s) por tempo expirado',
            'pedidos_cancelados' => $pedidosCancelados
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Erro: ' . $e->getMessage(),
            'pedidos_cancelados' => []
        ];
    }
}

/**
 * Verifica um pedido específico e cancela se o tempo limite foi ultrapassado
 * 
 * @param mysqli $sql Conexão com o banco de dados
 * @param int $vendaId ID da venda a verificar
 * @return array Array com informações sobre o resultado da verificação
 */
function verificarECancelarPedidoPorId($sql, $vendaId) {
    try {
        // Validar o ID
        $vendaId = intval($vendaId);
        
        // Query para buscar informações da venda
        $query = "
            SELECT 
                v.id,
                v.usuario_id,
                v.data_criacao,
                v.tempo_chegada,
                v.situacao_compra,
                TIMESTAMPDIFF(SECOND, v.data_criacao, NOW()) AS segundos_decorridos,
                TIME_TO_SEC(v.tempo_chegada) AS tempo_limite_segundos
            FROM vendas v
            WHERE v.id = " . $vendaId . "
        ";
        
        $resultado = $sql->query($query);
        
        if (!$resultado || $resultado->num_rows === 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Venda não encontrada',
                'cancelado' => false
            ];
        }
        
        $venda = $resultado->fetch_assoc();
        
        // Verificar se já está cancelada ou finalizada
        if ($venda['situacao_compra'] === 'cancelada') {
            return [
                'sucesso' => true,
                'mensagem' => 'Venda já estava cancelada',
                'cancelado' => false,
                'venda_id' => $vendaId
            ];
        }
        
        if ($venda['situacao_compra'] === 'finalizada') {
            return [
                'sucesso' => false,
                'mensagem' => 'Não é possível cancelar uma venda finalizada',
                'cancelado' => false,
                'venda_id' => $vendaId
            ];
        }
        
        // Verificar se ultrapassou o tempo limite
        $tempoLimiteSegundos = intval($venda['tempo_limite_segundos']);
        $segundosDecorridos = intval($venda['segundos_decorridos']);
        
        if ($segundosDecorridos > $tempoLimiteSegundos) {
            // Tempo expirou, cancelar a venda
            $updateQuery = "
                UPDATE vendas 
                SET situacao_compra = 'cancelada'
                WHERE id = " . $vendaId . "
            ";
            
            if (!$sql->query($updateQuery)) {
                throw new Exception("Erro ao atualizar venda: " . $sql->error);
            }
            
            // Registrar na tabela expiracoes_pre_compras
            $insertExpiracaoQuery = "
                INSERT INTO expiracoes_pre_compras (usuario_id, venda_id, data_expiracao)
                VALUES (" . intval($venda['usuario_id']) . ", " . $vendaId . ", NOW())
            ";
            
            if (!$sql->query($insertExpiracaoQuery)) {
                throw new Exception("Erro ao registrar expiração: " . $sql->error);
            }
            
            return [
                'sucesso' => true,
                'mensagem' => 'Venda cancelada por tempo expirado',
                'cancelado' => true,
                'venda_id' => $vendaId,
                'tempo_limite' => $venda['tempo_chegada'],
                'segundos_decorridos' => $segundosDecorridos
            ];
        } else {
            // Tempo ainda não expirou
            $tempoRestanteSegundos = $tempoLimiteSegundos - $segundosDecorridos;
            $minutos = intval($tempoRestanteSegundos / 60);
            $segundos = $tempoRestanteSegundos % 60;
            
            return [
                'sucesso' => true,
                'mensagem' => 'Venda ainda está dentro do tempo limite',
                'cancelado' => false,
                'venda_id' => $vendaId,
                'tempo_restante' => $tempoRestanteSegundos,
                'tempo_restante_formatado' => "{$minutos}m {$segundos}s"
            ];
        }
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'mensagem' => 'Erro: ' . $e->getMessage(),
            'cancelado' => false
        ];
    }
}

// Se o arquivo for chamado diretamente via GET (para testes)
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'verificar_todos') {
        echo json_encode(verificarECancelarPedidosComTempoExpirado($sql));
    } elseif ($_GET['action'] === 'verificar' && isset($_GET['venda_id'])) {
        echo json_encode(verificarECancelarPedidoPorId($sql, $_GET['venda_id']));
    } else {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Ação não reconhecida'
        ]);
    }
}
?>
