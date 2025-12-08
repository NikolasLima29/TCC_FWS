<?php
date_default_timezone_set('America/Sao_Paulo');
include "../../conn.php";

session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

if (!isset($_POST['tipo']) || !isset($_POST['data_ini']) || !isset($_POST['data_fim'])) {
    die("Dados incompletos");
}

$tipo = $_POST['tipo'];
$data_ini = $_POST['data_ini'];
$data_fim = $_POST['data_fim'];

// Buscar vendas no período
$sql_vendas = "SELECT v.id, v.data_criacao, v.funcionario_id, v.total, f.nome as funcionario_nome
    FROM vendas v
    LEFT JOIN funcionarios f ON v.funcionario_id = f.id
    WHERE v.situacao_compra = 'finalizada'
    AND DATE(v.data_criacao) >= '$data_ini'
    AND DATE(v.data_criacao) <= '$data_fim'
    ORDER BY v.data_criacao DESC";
$res_vendas = $sql->query($sql_vendas);

// Preparar dados
$vendas_data = [];
if ($res_vendas && $res_vendas->num_rows > 0) {
    while ($venda = $res_vendas->fetch_assoc()) {
        $venda_id = intval($venda['id']);
        // Calcular lucro
        $sql_lucro = "SELECT SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro
            FROM itens_vendidos iv
            LEFT JOIN produtos p ON iv.produto_id = p.id
            WHERE iv.venda_id = $venda_id";
        $res_lucro = $sql->query($sql_lucro);
        $lucro = 0;
        if ($res_lucro && $row_lucro = $res_lucro->fetch_assoc()) {
            $lucro = floatval($row_lucro['lucro']);
        }
        $vendas_data[] = [
            'id' => $venda_id,
            'data' => $venda['data_criacao'],
            'funcionario' => $venda['funcionario_nome'],
            'total' => floatval($venda['total']),
            'lucro' => $lucro
        ];
    }
}

// Exportar Excel
if ($tipo === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="fluxo_caixa_' . date('Y-m-d_H-i-s') . '.xls"');
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th { background-color: #ff9100; color: white; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .title { font-size: 16px; font-weight: bold; color: #ff9100; margin-top: 20px; margin-bottom: 10px; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h2 style="color: #ff9100; text-align: center;">FLUXO DE CAIXA</h2>
    <p style="text-align: center; font-size: 12px; color: #666;">
        Período: ' . date('d/m/Y', strtotime($data_ini)) . ' a ' . date('d/m/Y', strtotime($data_fim)) . ' | 
        Gerado em: ' . date('d/m/Y H:i:s') . '
    </p>
    
    <p class="title">📋 DETALHAMENTO DAS VENDAS</p>
    <table>
        <tr>
            <th>Nº Venda</th>
            <th>Data da Venda</th>
            <th>Funcionário</th>
            <th class="number">Valor da Compra</th>
            <th class="number">Lucro</th>
        </tr>';
    
    $total_vendas = 0;
    $total_lucro = 0;
    
    foreach ($vendas_data as $venda) {
        $total_vendas += $venda['total'];
        $total_lucro += $venda['lucro'];
        $html .= '<tr>
            <td>' . $venda['id'] . '</td>
            <td>' . date('d/m/Y H:i', strtotime($venda['data'])) . '</td>
            <td>' . htmlspecialchars($venda['funcionario']) . '</td>
            <td class="number">R$ ' . number_format($venda['total'], 2, ',', '.') . '</td>
            <td class="number" style="color:#52c41a; font-weight:bold;">R$ ' . number_format($venda['lucro'], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html .= '    </table>
    
    <p class="title" style="margin-top:30px;">📊 RESUMO DO PERÍODO</p>
    <table>
        <tr>
            <th>Métrica</th>
            <th class="number">Valor</th>
        </tr>
        <tr>
            <td>Total de Vendas</td>
            <td class="number">R$ ' . number_format($total_vendas, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Total de Lucro</td>
            <td class="number" style="color:#52c41a; font-weight:bold;">R$ ' . number_format($total_lucro, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Quantidade de Vendas</td>
            <td class="number">' . count($vendas_data) . '</td>
        </tr>
        <tr>
            <td>Ticket Médio</td>
            <td class="number">R$ ' . number_format(count($vendas_data) > 0 ? $total_vendas / count($vendas_data) : 0, 2, ',', '.') . '</td>
        </tr>
    </table>
</body>
</html>';
    
    echo $html;
    exit;
}

// Exportar PDF
if ($tipo === 'pdf') {
    $html_pdf = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background:#fff; }
        .page { page-break-after: always; page-break-inside: avoid; padding: 20px; min-height:297mm; }
        .logo { text-align: center; margin-bottom: 20px; }
        .logo img { max-width: 100px; height: auto; }
        h1 { color: #ff9100; text-align: center; font-size: 24px; margin: 20px 0; }
        h2 { color: #ff9100; text-align: center; font-size: 18px; margin-top: 30px; margin-bottom: 15px; }
        h4 { color: #333; text-align: center; font-size:14px; margin-top:20px; margin-bottom:10px; }
        .header { text-align: center; font-size: 12px; color: #666; margin-bottom: 20px; }
        .title { font-size: 14px; font-weight: bold; color: #ff9100; margin-top: 20px; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; font-size:11px; }
        th { background-color: #ff9100; color: white; border: 1px solid #ddd; padding: 6px; font-weight: bold; text-align: left; }
        td { border: 1px solid #ddd; padding: 6px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .number { text-align: right; }
        .resume { color: #52c41a; font-weight: bold; }
        .chart-container { text-align: center; margin: 20px 0; display: flex; justify-content: center; }
        canvas { max-width: 100%; height: auto; }
        .flex-charts { display: flex; gap: 20px; margin-top: 30px; }
        .chart-box { flex: 1; }
    </style>
</head>
<body>
    <!-- PÁGINA 1 -->
    <div class="page">
        <div class="logo">
            <img src="../../logotipo.png" alt="Logo FWS">
        </div>
        <h1>FLUXO DE CAIXA</h1>
        <h2>JD AMÉRICA SHELL SELECT</h2>
        <div class="header">
            Período: ' . date('d/m/Y', strtotime($data_ini)) . ' a ' . date('d/m/Y', strtotime($data_fim)) . '<br>
            Gerado em: ' . date('d/m/Y H:i:s') . '
        </div>
        
        <p class="title">📋 DETALHAMENTO DAS VENDAS</p>
        <table>
            <tr>
                <th>Nº Venda</th>
                <th>Data da Venda</th>
                <th>Funcionário</th>
                <th class="number">Valor da Compra</th>
                <th class="number">Lucro</th>
            </tr>';
    
    $total_vendas = 0;
    $total_lucro = 0;
    
    foreach ($vendas_data as $venda) {
        $total_vendas += $venda['total'];
        $total_lucro += $venda['lucro'];
        $html_pdf .= '<tr>
            <td>' . $venda['id'] . '</td>
            <td>' . date('d/m/Y H:i', strtotime($venda['data'])) . '</td>
            <td>' . htmlspecialchars($venda['funcionario']) . '</td>
            <td class="number">R$ ' . number_format($venda['total'], 2, ',', '.') . '</td>
            <td class="number resume">R$ ' . number_format($venda['lucro'], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html_pdf .= '        </table>
        
        <p class="title">📊 RESUMO DO PERÍODO</p>
        <table>
            <tr>
                <th>Métrica</th>
                <th class="number">Valor</th>
            </tr>
            <tr>
                <td>Total de Vendas</td>
                <td class="number">R$ ' . number_format($total_vendas, 2, ',', '.') . '</td>
            </tr>
            <tr>
                <td>Total de Lucro</td>
                <td class="number resume">R$ ' . number_format($total_lucro, 2, ',', '.') . '</td>
            </tr>
            <tr>
                <td>Quantidade de Vendas</td>
                <td class="number">' . count($vendas_data) . '</td>
            </tr>
            <tr>
                <td>Ticket Médio</td>
                <td class="number">R$ ' . number_format(count($vendas_data) > 0 ? $total_vendas / count($vendas_data) : 0, 2, ',', '.') . '</td>
            </tr>
        </table>
    </div>

    <!-- PÁGINA 2 -->
    <div class="page">
        <h2 style="margin-top: 0;">📈 ANÁLISE DE PRODUTOS</h2>
        
        <div class="flex-charts">
            <div class="chart-box">
                <h4 style="color:#52c41a; text-align:center; margin-bottom:20px; padding:10px; background:#f0f0f0; border-left:4px solid #52c41a;">
                    📊 Top 3 Produtos Mais Lucrativos
                </h4>
                <table style="margin-top:15px; font-size:11px;">
                    <thead>
                        <tr style="background:#52c41a; color:white;">
                            <th style="color:white;"></th>
                            <th style="color:white;">Produto</th>
                            <th class="number" style="color:white;">Vendidos</th>
                            <th class="number" style="color:white;">Lucro (R$)</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Buscar top 3 produtos mais lucrativos
    $sql_top_lucro = "SELECT p.nome, SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro, SUM(iv.quantidade) as vendidos
        FROM itens_vendidos iv
        LEFT JOIN produtos p ON iv.produto_id = p.id
        LEFT JOIN vendas v ON iv.venda_id = v.id
        WHERE v.situacao_compra = 'finalizada'
        AND DATE(v.data_criacao) >= '$data_ini'
        AND DATE(v.data_criacao) <= '$data_fim'
        GROUP BY p.id
        ORDER BY lucro DESC, vendidos DESC
        LIMIT 3";
    $res_top_lucro = $sql->query($sql_top_lucro);
    $produtos_top = [];
    while ($row = $res_top_lucro->fetch_assoc()) {
        $produtos_top[] = $row;
    }
    
    $cores_top = ['#52c41a', '#13c2c2', '#faad14'];
    foreach ($produtos_top as $i => $p) {
        $html_pdf .= '<tr>
            <td><span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:' . $cores_top[$i % count($cores_top)] . '; border:1px solid #ccc;"></span></td>
            <td>' . htmlspecialchars($p['nome']) . '</td>
            <td class="number">' . intval($p['vendidos']) . '</td>
            <td class="number" style="color:#52c41a; font-weight:bold;">R$ ' . number_format($p['lucro'], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html_pdf .= '                    </tbody>
                </table>
            </div>
            
            <div class="chart-box">
                <h4 style="color:#E53935; text-align:center; margin-bottom:20px; padding:10px; background:#f0f0f0; border-left:4px solid #E53935;">
                    📊 Top 3 Produtos Menos Lucrativos
                </h4>
                <table style="margin-top:15px; font-size:11px;">
                    <thead>
                        <tr style="background:#E53935; color:white;">
                            <th style="color:white;"></th>
                            <th style="color:white;">Produto</th>
                            <th class="number" style="color:white;">Vendidos</th>
                            <th class="number" style="color:white;">Lucro (R$)</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    // Buscar top 3 produtos menos lucrativos
    $sql_low_lucro = "SELECT p.nome, SUM((p.preco_venda - p.preco_compra) * iv.quantidade) as lucro, SUM(iv.quantidade) as vendidos
        FROM itens_vendidos iv
        LEFT JOIN produtos p ON iv.produto_id = p.id
        LEFT JOIN vendas v ON iv.venda_id = v.id
        WHERE v.situacao_compra = 'finalizada'
        AND DATE(v.data_criacao) >= '$data_ini'
        AND DATE(v.data_criacao) <= '$data_fim'
        GROUP BY p.id
        ORDER BY lucro ASC, vendidos DESC
        LIMIT 3";
    $res_low_lucro = $sql->query($sql_low_lucro);
    $produtos_low = [];
    while ($row = $res_low_lucro->fetch_assoc()) {
        $produtos_low[] = $row;
    }
    
    $cores_low = ['#E53935', '#faad14', '#bfbfbf'];
    foreach ($produtos_low as $i => $p) {
        $html_pdf .= '<tr>
            <td><span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:' . $cores_low[$i % count($cores_low)] . '; border:1px solid #ccc;"></span></td>
            <td>' . htmlspecialchars($p['nome']) . '</td>
            <td class="number">' . intval($p['vendidos']) . '</td>
            <td class="number" style="color:#E53935; font-weight:bold;">R$ ' . number_format($p['lucro'], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html_pdf .= '                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>';
    
    // Retornar HTML para ser processado pelo cliente com jsPDF
    header('Content-Type: application/json');
    echo json_encode(['html' => $html_pdf, 'filename' => 'fluxo_caixa_' . date('Y-m-d_H-i-s') . '.pdf']);
    exit;
}

$sql->close();
?>
