<?php
// Tratamento global para erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro fatal: ' . $error['message']]);
        exit;
    }
});
// Desabilitar exibição de erros
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Header JSON obrigatório
header('Content-Type: application/json; charset=utf-8');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Método não permitido']));
}

// Obter e validar entrada
$input = file_get_contents('php://input');
if (empty($input)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Corpo da requisição vazio']));
}

$data = json_decode($input, true);
if ($data === null) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'JSON inválido']));
}

$userMessage = trim($data['message'] ?? '');
if (empty($userMessage)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Mensagem vazia']));
}

// Conectar ao banco de dados diretamente
$sql = new mysqli("localhost","root","","FWS");
$sql->set_charset("utf8");

// Verificar se conexão com banco funcionou
if ($sql->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Erro ao conectar com banco: ' . $sql->connect_error]));
}

// Função para buscar informações do banco
function getDatabaseInfo($sql) {
    $info = [
        'estoque_baixo' => [],
        'produtos_vencidos' => [],
        'vencimento_proximo' => [],
        'mais_vendidos' => [],
        'total_produtos' => 0,
        'vendas_mes' => 0,
        'receita_mes' => 'N/A',
        'ultimas_vendas' => []
    ];
    try {
        // Contar total de vendas
        $countRes = $sql->query("SELECT COUNT(*) as total FROM vendas");
        if ($countRes && $countRes->num_rows > 0) {
            $countRow = $countRes->fetch_assoc();
            $info['vendas_mes'] = $countRow['total'];
        }

        // Consulta das 5 últimas vendas
        $res = $sql->query("SELECT v.id, v.data_criacao, v.total, v.metodo_pagamento, v.situacao_compra, v.usuario_id, v.tempo_chegada, v.data_finalizacao, v.tempo_adicionado, u.nome as cliente, u.telefone FROM vendas v LEFT JOIN usuarios u ON v.usuario_id = u.id ORDER BY v.data_criacao DESC LIMIT 5");
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $venda_id = $row['id'];
                $itens = [];
                $resItens = $sql->query("SELECT p.nome, iv.quantidade FROM itens_vendidos iv JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = " . intval($venda_id));
                if ($resItens && $resItens->num_rows > 0) {
                    while ($item = $resItens->fetch_assoc()) {
                        $itens[] = $item['nome'] . ' (x' . $item['quantidade'] . ')';
                    }
                }
                // Calcular tempo desde a venda
                $dataVenda = strtotime($row['data_criacao']);
                $agora = time();
                $tempoSegundos = $agora - $dataVenda;
                $tempoMinutos = floor($tempoSegundos / 60);
                $tempoStr = $tempoMinutos . ' min atrás';
                // Código da venda: 4 últimos do telefone + '-' + id
                $codigoVenda = '';
                if (!empty($row['telefone'])) {
                    $tel = preg_replace('/\D/', '', $row['telefone']);
                    $codigoVenda = substr($tel, -4) . '-' . $venda_id;
                } else if (!empty($row['usuario_id'])) {
                    $codigoVenda = '----' . $venda_id;
                } else {
                    $codigoVenda = 'sem-cliente-' . $venda_id;
                }
                $cliente_nome = $row['cliente'];
                if (empty($cliente_nome) && !empty($row['usuario_id'])) {
                    $resCliente = $sql->query("SELECT nome FROM usuarios WHERE id = " . intval($row['usuario_id']) . " LIMIT 1");
                    if ($resCliente && $resCliente->num_rows > 0) {
                        $clienteRow = $resCliente->fetch_assoc();
                        $cliente_nome = $clienteRow['nome'];
                    } else {
                        $cliente_nome = 'Desconhecido';
                    }
                } elseif (empty($cliente_nome)) {
                    $cliente_nome = 'Desconhecido';
                }
                $info['ultimas_vendas'][] = [
                    'id' => $venda_id,
                    'cliente' => $cliente_nome,
                    'total' => 'R$ ' . number_format($row['total'], 2, ',', '.'),
                    'data' => date('d/m/Y H:i', $dataVenda),
                    'tempo' => $tempoStr,
                    'itens' => $itens,
                    'metodo_pagamento' => $row['metodo_pagamento'] ?? 'N/A',
                    'codigo_venda' => $codigoVenda,
                    'situacao_compra' => $row['situacao_compra'] ?? 'N/A',
                    'tempo_chegada' => $row['tempo_chegada'] ?? '',
                    'data_finalizacao' => $row['data_finalizacao'] ?? '',
                    'tempo_adicionado' => $row['tempo_adicionado'] ?? ''
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
    return $info;
}

// Obter informações do banco
$dbInfo = getDatabaseInfo($sql);

// Construir contexto com informações reais do banco
$systemContext = "Você é um assistente IA especializado no sistema FWS (Faster Way Service). Responda de forma visual, amigável e bem formatada.

INSTRUÇÕES DE FORMATAÇÃO:
- Use emojis para tornar a resposta mais visual e atrativa
- Para VENDAS, use este formato EXATO com muita separação:
  
  📦 PEDIDO #{id}
  👤 Cliente: {cliente}
  💰 Valor: {total}
  📅 Data: {data}
  ⏱️ Tempo: {tempo}
  
  📋 ITENS:
  {itens separados por quebra de linha}
  
  💳 PAGAMENTO: {metodo}
  🔖 Código: {codigo}
  📊 Status: {situacao}
  {tempo_chegada se existir}
  {data_finalizacao se existir}
  {tempo_adicional se existir}

- Use quebras de linha generosas entre seções
- Seja conciso mas informativo
- Use emojis relevantes para cada tipo de informação
- Responda em português brasileiro

INFORMAÇÕES ATUAIS DO BANCO DE DADOS:
Vendas este Mês: " . ($dbInfo['vendas_mes'] ?? 0) . "
Receita este Mês: " . ($dbInfo['receita_mes'] ?? 'N/A') . "

PRODUTOS COM ESTOQUE BAIXO:
" . (count($dbInfo['estoque_baixo']) > 0 ? implode("\n", $dbInfo['estoque_baixo']) : "Nenhum") . "

PRODUTOS VENCIDOS:
" . (count($dbInfo['produtos_vencidos']) > 0 ? implode("\n", $dbInfo['produtos_vencidos']) : "Nenhum") . "

PRÓXIMOS DO VENCIMENTO:
" . (count($dbInfo['vencimento_proximo']) > 0 ? implode("\n", $dbInfo['vencimento_proximo']) : "Nenhum") . "

TOP 10 MAIS VENDIDOS:
" . (count($dbInfo['mais_vendidos']) > 0 ? implode("\n", $dbInfo['mais_vendidos']) : "Nenhum") . "

        ÚLTIMAS VENDAS REGISTRADAS:
        " . (count($dbInfo['ultimas_vendas']) > 0 ? implode("\n\n", array_map(function($v){
            return "Venda #" . $v['id'] . " | Cliente: " . $v['cliente'] . " | Data: " . $v['data'] . " | Total: " . $v['total'] . " | Tempo: " . $v['tempo'] .
            "\nItens: " . implode(", ", $v['itens']) .
            "\nMétodo de pagamento: " . $v['metodo_pagamento'] .
            "\nCódigo da venda: " . $v['codigo_venda'] .
            "\nSituação da compra: " . $v['situacao_compra'] .
            (!empty($v['tempo_chegada']) ? "\nTempo para chegada: " . $v['tempo_chegada'] : '') .
            (!empty($v['data_finalizacao']) ? "\nData de finalização: " . $v['data_finalizacao'] : '') .
            (!empty($v['tempo_adicionado']) ? "\nTempo adicional: " . $v['tempo_adicionado'] : '');
        }, $dbInfo['ultimas_vendas'])) : "Nenhuma venda registrada") . "

        Você pode consultar dados em tempo real, responder sobre produtos, estoque, vendas, clientes, itens vendidos, método de pagamento, código da venda, status do pedido, tempo para chegada, data de finalização e tempo adicional.";

// Configuração da API
define('GEMINI_API_KEY', 'AIzaSyA9viVghivat6E32r_e-o6GHmsIuaWfLS8');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

// Preparar payload para a API
$requestBody = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $systemContext . "\n\nPergunta do usuário: " . $userMessage
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'topP' => 0.95,
        'topK' => 40,
        'maxOutputTokens' => 1024
    ]
];

// Fazer requisição CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, GEMINI_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-goog-api-key: ' . GEMINI_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Validar resposta
if ($curlError) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Erro CURL: ' . $curlError]));
}

if (empty($response)) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Resposta vazia da API. Code: ' . $httpCode]));
}

// Log de debug (remova em produção)
error_log("Response received: " . substr($response, 0, 200));

// Decodificar resposta JSON
$responseData = @json_decode($response, true);
if ($responseData === null && $httpCode === 200) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'JSON inválido. Response: ' . substr($response, 0, 500)]));
}

// Tratamento de erros da API Gemini
if ($httpCode !== 200) {
    $errorMsg = $responseData['error']['message'] ?? 'Erro desconhecido';
    http_response_code($httpCode);
    die(json_encode(['success' => false, 'error' => 'API Gemini: ' . $errorMsg]));
}

// Extrair resposta da IA
if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Estrutura de resposta inesperada: ' . json_encode($responseData)]));
}

$aiResponse = trim($responseData['candidates'][0]['content']['parts'][0]['text']);

// Fechar conexão com banco
$sql->close();

// Retornar sucesso
http_response_code(200);
echo json_encode([
    'success' => true,
    'response' => $aiResponse
]);
?>
