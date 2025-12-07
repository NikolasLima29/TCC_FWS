<?php
date_default_timezone_set('America/Sao_Paulo');
include('../../conn.php');

// Receber dados JSON
$data = json_decode(file_get_contents('php://input'), true);

try {
    $sql->begin_transaction();

    // Caso 1: Remover um lote específico
    if (isset($data['lote_id']) && !empty($data['lote_id'])) {
        $lote_id = intval($data['lote_id']);

        // Buscar informações do lote
        $query_lote = "SELECT produto_id, quantidade FROM lotes_produtos WHERE id = $lote_id";
        $result_lote = $sql->query($query_lote);
        
        if (!$result_lote || $result_lote->num_rows == 0) {
            throw new Exception("Lote não encontrado");
        }

        $row_lote = $result_lote->fetch_assoc();
        $produto_id = intval($row_lote['produto_id']);
        $quantidade_removida = intval($row_lote['quantidade']);

        // Remover o lote
        $delete_lote = "DELETE FROM lotes_produtos WHERE id = $lote_id";
        if (!$sql->query($delete_lote)) {
            throw new Exception("Erro ao remover lote: " . $sql->error);
        }

        // Atualizar estoque
        $update_estoque = "UPDATE produtos SET estoque = estoque - $quantidade_removida WHERE id = $produto_id";
        if (!$sql->query($update_estoque)) {
            throw new Exception("Erro ao atualizar estoque: " . $sql->error);
        }

        // Registrar movimentação
        $data_atual = date('Y-m-d H:i:s');
        $insert_mov = "INSERT INTO movimentacao_estoque (produto_id, quantidade, tipo_movimentacao, data_movimentacao) 
                       VALUES ($produto_id, $quantidade_removida, 'saida', '$data_atual')";
        if (!$sql->query($insert_mov)) {
            throw new Exception("Erro ao registrar movimentação: " . $sql->error);
        }

        $sql->commit();
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => "Lote removido com sucesso! $quantidade_removida unidade(s) retirada(s)."
        ]);
        exit;
    }

    // Caso 2: Remover todos os lotes vencidos
    if (isset($data['remover_todos'])) {
        // Buscar todos os lotes vencidos agrupados por produto
        $query_vencidos = "SELECT produto_id, COALESCE(SUM(quantidade), 0) as quantidade_total
                          FROM lotes_produtos 
                          WHERE DATE(validade) < CURDATE()
                          GROUP BY produto_id";
        
        $result_vencidos = $sql->query($query_vencidos);
        
        if (!$result_vencidos) {
            throw new Exception("Erro ao buscar lotes vencidos: " . $sql->error);
        }

        $removidos = 0;
        $total_registros = 0;

        // Processar cada produto com lotes vencidos
        while ($row = $result_vencidos->fetch_assoc()) {
            $produto_id = intval($row['produto_id']);
            $quantidade_removida = intval($row['quantidade_total']);

            if ($quantidade_removida <= 0) {
                continue;
            }

            // Buscar estoque atual
            $query_estoque = "SELECT estoque FROM produtos WHERE id = $produto_id";
            $result_estoque = $sql->query($query_estoque);
            $row_estoque = $result_estoque->fetch_assoc();
            $estoque_atual = intval($row_estoque['estoque']);

            // Validar estoque
            if ($estoque_atual < $quantidade_removida) {
                $quantidade_removida = $estoque_atual;
            }

            if ($quantidade_removida <= 0) {
                continue;
            }

            // Remover lotes vencidos
            $delete_lotes = "DELETE FROM lotes_produtos 
                           WHERE produto_id = $produto_id 
                           AND DATE(validade) < CURDATE()
                           LIMIT $quantidade_removida";
            
            if (!$sql->query($delete_lotes)) {
                throw new Exception("Erro ao remover lotes do produto ID $produto_id: " . $sql->error);
            }

            // Atualizar estoque
            $update_estoque = "UPDATE produtos 
                            SET estoque = estoque - $quantidade_removida 
                            WHERE id = $produto_id";
            
            if (!$sql->query($update_estoque)) {
                throw new Exception("Erro ao atualizar estoque: " . $sql->error);
            }

            // Registrar movimentação
            $data_atual = date('Y-m-d H:i:s');
            $insert_movimentacao = "INSERT INTO movimentacao_estoque 
                                  (produto_id, quantidade, tipo_movimentacao, data_movimentacao) 
                                  VALUES 
                                  ($produto_id, $quantidade_removida, 'saida', '$data_atual')";
            
            if (!$sql->query($insert_movimentacao)) {
                throw new Exception("Erro ao registrar movimentação: " . $sql->error);
            }

            $removidos++;
            $total_registros += $quantidade_removida;
        }

        $sql->commit();

        if ($removidos > 0) {
            echo json_encode([
                'sucesso' => true,
                'mensagem' => "$removidos produto(s) teve(ram) lotes vencidos removido(s)! Total de $total_registros unidade(s) retirada(s)."
            ]);
        } else {
            echo json_encode([
                'sucesso' => true,
                'mensagem' => "Nenhum lote vencido encontrado para remover."
            ]);
        }
        exit;
    }

    throw new Exception("Requisição inválida");

} catch (Exception $e) {
    $sql->rollback();
    echo json_encode([
        'sucesso' => false,
        'erro' => $e->getMessage()
    ]);
}
?>
