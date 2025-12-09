<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../../conn.php';

$message = "olá";

// Função para buscar informações do banco
function getDatabaseInfo($sql) {
    $info = [
        'estoque_baixo' => [],
        'produtos_vencidos' => [],
        'vencimento_proximo' => [],
        'mais_vendidos' => [],
        'total_produtos' => 0,
        'vendas_mes' => 0,
        'receita_mes' => 'N/A'
    ];
    
    try {
        $res = $sql->query("SELECT nome, estoque FROM produtos WHERE estoque < 10 ORDER BY estoque ASC LIMIT 10");
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $info['estoque_baixo'][] = htmlspecialchars($row['nome']) . ' (estoque: ' . intval($row['estoque']) . ')';
            }
        }
        
        $res = $sql->query("SELECT COUNT(*) as total FROM produtos");
        if ($res) {
            $row = $res->fetch_assoc();
            $info['total_produtos'] = intval($row['total']);
        }
        
        $res = $sql->query("SELECT COUNT(*) as total FROM vendas WHERE MONTH(data_criacao) = MONTH(CURDATE()) AND YEAR(data_criacao) = YEAR(CURDATE())");
        if ($res) {
            $row = $res->fetch_assoc();
            $info['vendas_mes'] = intval($row['total']);
        }
        
        $res = $sql->query("SELECT SUM(total) as total FROM vendas WHERE MONTH(data_criacao) = MONTH(CURDATE()) AND YEAR(data_criacao) = YEAR(CURDATE())");
        if ($res) {
            $row = $res->fetch_assoc();
            $receita = floatval($row['total'] ?? 0);
            $info['receita_mes'] = 'R$ ' . number_format($receita, 2, ',', '.');
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
    
    return $info;
}

$dbInfo = getDatabaseInfo($sql);

$systemContext = "Você é um assistente IA especializado no sistema FWS (Faster Way Service). Responda de forma direta e concisa. NUNCA adicione quebras de linha ou espaços no início da resposta.

INSTRUÇÕES RIGOROSAS:
- Comece a resposta diretamente, sem quebras de linha antes
- Não use asteriscos (*), sublinhados (_) ou emojis
- Não adicione espaços extras em nenhum lugar
- Use quebras de linha APENAS entre paragrafos diferentes
- Seja conciso e direto
- Responda em português brasileiro

INFORMAÇÕES ATUAIS DO BANCO DE DADOS:
Total de Produtos: " . ($dbInfo['total_produtos'] ?? 0) . "
Vendas este Mês: " . ($dbInfo['vendas_mes'] ?? 0) . "
Receita este Mês: " . ($dbInfo['receita_mes'] ?? 'N/A') . "

Você pode consultar dados em tempo real, responder sobre produtos, estoque, vendas e finanças.";

$requestBody = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $systemContext . "\n\nPergunta do usuário: " . $message
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

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-goog-api-key: AIzaSyCV7ZaUazXDACLiXnwBdYa9UuYiNPDDuXY'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseData = json_decode($response, true);

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    $aiResponse = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
    
    // Debug: mostrar com repr
    echo "Resposta bruta (length: " . strlen($aiResponse) . "):<br>";
    echo "<pre>";
    echo var_export($aiResponse, true);
    echo "</pre>";
    
    echo "<br>Primeiros 100 caracteres (ord):<br>";
    for ($i = 0; $i < min(100, strlen($aiResponse)); $i++) {
        echo "[$i]: '" . $aiResponse[$i] . "' (ord: " . ord($aiResponse[$i]) . ")<br>";
    }
}
?>
