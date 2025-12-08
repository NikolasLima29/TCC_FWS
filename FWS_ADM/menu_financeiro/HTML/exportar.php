<?php
date_default_timezone_set('America/Sao_Paulo');

include "../../conn.php";
session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tipo'])) {
    header('Location: relatorio_financeiro.php');
    exit;
}

$tipo = $_POST['tipo'];
$mes_linha = isset($_POST['mes_linha']) ? intval($_POST['mes_linha']) : intval(date('m'));
$ano_linha = isset($_POST['ano_linha']) ? intval($_POST['ano_linha']) : intval(date('Y'));
$data_ini = isset($_POST['data_ini']) ? $_POST['data_ini'] : '';
$data_fim = isset($_POST['data_fim']) ? $_POST['data_fim'] : '';
$perc_estoque = isset($_POST['perc_estoque']) ? intval($_POST['perc_estoque']) : 100;
$mes_categorias = isset($_POST['mes_categorias']) ? intval($_POST['mes_categorias']) : intval(date('m'));
$ano_categorias = isset($_POST['ano_categorias']) ? intval($_POST['ano_categorias']) : intval(date('Y'));

// Datas para gráfico de linha
$data_ini_linha = sprintf('%04d-%02d-01', $ano_linha, $mes_linha);
$data_fim_linha = date('Y-m-t', strtotime($data_ini_linha));

// Lucro real
$sql_lucro_real = "SELECT 
                     SUM(iv.quantidade * p.preco_venda) as faturamento_real,
                     SUM(iv.quantidade * p.preco_compra) as custo_real,
                     (SELECT SUM(valor) FROM despesas WHERE DATE(data_despesa) >= '$data_ini_linha' AND DATE(data_despesa) <= '$data_fim_linha') as despesas_mes
                   FROM itens_vendidos iv
                   LEFT JOIN produtos p ON iv.produto_id = p.id
                   LEFT JOIN vendas v ON iv.venda_id = v.id
                   WHERE v.situacao_compra = 'finalizada' 
                   AND DATE(v.data_criacao) >= '$data_ini_linha' 
                   AND DATE(v.data_criacao) <= '$data_fim_linha'";
$res_lucro_real = $sql->query($sql_lucro_real);
$row_lucro_real = $res_lucro_real->fetch_assoc();
$faturamento_real = (float)($row_lucro_real['faturamento_real'] ?? 0);
$custo_real = (float)($row_lucro_real['custo_real'] ?? 0);
$despesas_mes = (float)($row_lucro_real['despesas_mes'] ?? 0);
$lucro_real = $faturamento_real - $custo_real - $despesas_mes;

// Lucro potencial
$sql_lucro_potencial = "SELECT 
                         SUM(p.estoque * p.preco_venda) as faturamento_potencial,
                         SUM(p.estoque * p.preco_compra) as custo_potencial
                        FROM produtos p
                        WHERE p.estoque > 0";
$res_lucro_potencial = $sql->query($sql_lucro_potencial);
$row_lucro_potencial = $res_lucro_potencial->fetch_assoc();
$faturamento_potencial = (float)($row_lucro_potencial['faturamento_potencial'] ?? 0) * ($perc_estoque / 100);
$custo_potencial = (float)($row_lucro_potencial['custo_potencial'] ?? 0) * ($perc_estoque / 100);
$lucro_potencial = $faturamento_potencial - $custo_potencial - $despesas_mes;
$diferenca = $lucro_potencial - $lucro_real;

// Despesas
$where = [];
if (!empty($data_ini)) {
    $data_ini = $sql->real_escape_string($data_ini);
    $where[] = "data_despesa >= '$data_ini'";
}
if (!empty($data_fim)) {
    $data_fim = $sql->real_escape_string($data_fim);
    $where[] = "data_despesa <= '$data_fim'";
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$res_despesas = $sql->query("SELECT tipo, SUM(valor) as total FROM despesas $where_sql GROUP BY tipo");
$tipos = [];
$valores_despesas = [];
$total_despesas = 0;
while($row = $res_despesas->fetch_assoc()) {
    $tipos[] = ucfirst($row['tipo']);
    $valores_despesas[] = (float)$row['total'];
    $total_despesas += (float)$row['total'];
}

// Faturamento por categoria
$data_ini_categorias = sprintf('%04d-%02d-01', $ano_categorias, $mes_categorias);
$data_fim_categorias = date('Y-m-t', strtotime($data_ini_categorias));

$sql_categorias = "SELECT c.nome, SUM(iv.quantidade * p.preco_venda) as faturamento 
                   FROM itens_vendidos iv
                   LEFT JOIN produtos p ON iv.produto_id = p.id
                   LEFT JOIN categorias c ON p.categoria_id = c.id
                   LEFT JOIN vendas v ON iv.venda_id = v.id
                   WHERE v.situacao_compra = 'finalizada' 
                   AND DATE(v.data_criacao) >= '$data_ini_categorias' 
                   AND DATE(v.data_criacao) <= '$data_fim_categorias'
                   GROUP BY c.id
                   ORDER BY faturamento DESC";
$res_categorias = $sql->query($sql_categorias);
$categorias_nomes = [];
$categorias_faturamento = [];
while($row = $res_categorias->fetch_assoc()) {
    $categorias_nomes[] = $row['nome'];
    $categorias_faturamento[] = (float)$row['faturamento'];
}

if ($tipo === 'excel') {
    // Exportar Excel (HTML Table)
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_financeiro_' . date('Y-m-d_H-i-s') . '.xls"');
    
    $html_excel = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th { background-color: #FF8C00; color: white; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .title { font-size: 16px; font-weight: bold; color: #FF8C00; margin-top: 20px; margin-bottom: 10px; }
        .subtitle { font-size: 12px; color: #666; margin-bottom: 30px; }
        .header-section { font-size: 14px; font-weight: bold; color: #000; background-color: #fff9f0; padding: 10px; margin-top: 15px; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h1 style="color: #FF8C00; text-align: center;">SHELL SELECT JARDIM AMÉRICA</h1>
    <p class="subtitle" style="text-align: center;">Gerado em: ' . date('d/m/Y H:i:s') . '</p>
    
    <!-- Tabela 1: Movimentação Diária -->
    <p class="header-section">📈 MOVIMENTAÇÃO DIÁRIA DO MÊS</p>
    <table>
        <tr>
            <th>Dia</th>
            <th class="number">Faturamento Acumulado</th>
            <th class="number">Custo Acumulado</th>
            <th class="number">Lucro</th>
        </tr>';
    
    // Dados de movimentação
    $sql_movimentacao = "SELECT DATE(v.data_criacao) as data, 
                                SUM(iv.quantidade * p.preco_venda) as faturamento,
                                SUM(iv.quantidade * p.preco_compra) as custo
                        FROM itens_vendidos iv
                        LEFT JOIN produtos p ON iv.produto_id = p.id
                        LEFT JOIN vendas v ON iv.venda_id = v.id
                        WHERE v.situacao_compra = 'finalizada' 
                        AND DATE(v.data_criacao) >= '$data_ini_linha' 
                        AND DATE(v.data_criacao) <= '$data_fim_linha'
                        GROUP BY DATE(v.data_criacao)
                        ORDER BY data ASC";
    
    $res_movimentacao = $sql->query($sql_movimentacao);
    $acumulado_fat = 0;
    $acumulado_custo = 0;
    $dia_num = 1;
    
    while($row = $res_movimentacao->fetch_assoc()) {
        $acumulado_fat += (float)$row['faturamento'];
        $acumulado_custo += (float)$row['custo'];
        $lucro_dia = $acumulado_fat - $acumulado_custo;
        $html_excel .= '<tr>
            <td>Dia ' . $dia_num . '</td>
            <td class="number">R$ ' . number_format($acumulado_fat, 2, ',', '.') . '</td>
            <td class="number">R$ ' . number_format($acumulado_custo, 2, ',', '.') . '</td>
            <td class="number">R$ ' . number_format($lucro_dia, 2, ',', '.') . '</td>
        </tr>';
        $dia_num++;
    }
    
    $html_excel .= '    </table>
    
    <!-- Tabela 2: Despesas -->
    <p class="header-section">💰 DESPESAS</p>
    <table>
        <tr>
            <th>Tipo de Despesa</th>
            <th class="number">Valor</th>
        </tr>';
    
    for ($i = 0; $i < count($tipos); $i++) {
        $html_excel .= '<tr>
            <td>' . htmlspecialchars($tipos[$i]) . '</td>
            <td class="number">R$ ' . number_format($valores_despesas[$i], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html_excel .= '<tr style="background-color: #f0f0f0; font-weight: bold;">
            <td>Total de Despesas</td>
            <td class="number">R$ ' . number_format($total_despesas, 2, ',', '.') . '</td>
        </tr>
    </table>
    
    <!-- Tabela 3: Projeção de Lucro -->
    <p class="header-section">🎯 PROJEÇÃO DE LUCRO (com ' . $perc_estoque . '% de estoque)</p>
    <table>
        <tr>
            <th>Métrica</th>
            <th class="number">Valor</th>
        </tr>
        <tr>
            <td>Lucro Realizado</td>
            <td class="number">R$ ' . number_format($lucro_real, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Lucro Potencial</td>
            <td class="number">R$ ' . number_format($lucro_potencial, 2, ',', '.') . '</td>
        </tr>
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td>Oportunidade de Ganho</td>
            <td class="number">R$ ' . number_format($diferenca, 2, ',', '.') . '</td>
        </tr>
    </table>
    
    <!-- Tabela 4: Faturamento por Categoria -->
    <p class="header-section">🏆 FATURAMENTO POR CATEGORIA</p>
    <table>
        <tr>
            <th>Categoria</th>
            <th class="number">Faturamento</th>
        </tr>';
    
    for ($i = 0; $i < count($categorias_nomes); $i++) {
        $html_excel .= '<tr>
            <td>' . htmlspecialchars($categorias_nomes[$i]) . '</td>
            <td class="number">R$ ' . number_format($categorias_faturamento[$i], 2, ',', '.') . '</td>
        </tr>';
    }
    
    $html_excel .= '    </table>
    
    <!-- Tabela 5: Resumo Financeiro -->
    <p class="header-section">📋 RESUMO FINANCEIRO</p>
    <table>
        <tr>
            <th>Métrica</th>
            <th class="number">Valor</th>
        </tr>
        <tr>
            <td>Faturamento Real</td>
            <td class="number">R$ ' . number_format($faturamento_real, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Custo Real</td>
            <td class="number">R$ ' . number_format($custo_real, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Lucro Bruto</td>
            <td class="number">R$ ' . number_format($faturamento_real - $custo_real, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Total de Despesas</td>
            <td class="number">R$ ' . number_format($total_despesas, 2, ',', '.') . '</td>
        </tr>
        <tr style="background-color: #fff9f0; font-weight: bold;">
            <td>🎯 Lucro Líquido</td>
            <td class="number" style="color: #FF8C00; font-size: 14px;">R$ ' . number_format($lucro_real, 2, ',', '.') . '</td>
        </tr>
    </table>
</body>
</html>';

    echo $html_excel;
    exit;
    
} elseif ($tipo === 'pdf') {
    // Gerar array com todos os dias do mês
    $data_ini_linha = sprintf('%04d-%02d-01', $ano_linha, $mes_linha);
    $data_fim_linha = date('Y-m-t', strtotime($data_ini_linha));
    $dias_no_mes = date('d', strtotime($data_fim_linha));
    
    // Inicializar arrays com todos os dias
    $dados_por_dia = [];
    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
        $dados_por_dia[sprintf('%04d-%02d-%02d', $ano_linha, $mes_linha, $dia)] = ['faturamento' => 0, 'custo' => 0];
    }
    
    // Buscar dados de vendas do mês
    $sql_movimentacao = "SELECT DATE(v.data_criacao) as data, 
                                SUM(iv.quantidade * p.preco_venda) as faturamento,
                                SUM(iv.quantidade * p.preco_compra) as custo
                        FROM itens_vendidos iv
                        LEFT JOIN produtos p ON iv.produto_id = p.id
                        LEFT JOIN vendas v ON iv.venda_id = v.id
                        WHERE v.situacao_compra = 'finalizada' 
                        AND DATE(v.data_criacao) >= '$data_ini_linha' 
                        AND DATE(v.data_criacao) <= '$data_fim_linha'
                        GROUP BY DATE(v.data_criacao)
                        ORDER BY data ASC";
    
    $res_movimentacao = $sql->query($sql_movimentacao);
    while($row = $res_movimentacao->fetch_assoc()) {
        $dados_por_dia[$row['data']]['faturamento'] = (float)$row['faturamento'];
        $dados_por_dia[$row['data']]['custo'] = (float)$row['custo'];
    }
    
    // Calcular valores acumulados
    $linha_verde = [];
    $linha_vermelha = [];
    $acumulado_fat = 0;
    $acumulado_custo = 0;
    
    ksort($dados_por_dia); // Ordenar por data
    foreach ($dados_por_dia as $data => $valores) {
        $acumulado_fat += $valores['faturamento'];
        $acumulado_custo += $valores['custo'];
        $linha_verde[] = $acumulado_fat;
        $linha_vermelha[] = $acumulado_custo;
    }
    
    // Se não houver dados, adicionar zero
    if (empty($linha_verde)) {
        $linha_verde = [0];
        $linha_vermelha = [0];
    }
    
    // Gerar página HTML com gráficos e capturar para PDF
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #fff; }
        .container { width: 100%; max-width: 1000px; margin: 0 auto; padding: 30px 20px; background: white; }
        h1 { color: #FF8C00; text-align: center; margin-bottom: 10px; font-size: 28px; font-weight: bold; }
        .subtitle { text-align: center; color: #333; font-size: 13px; margin-bottom: 30px; }
        .section { margin-bottom: 50px; page-break-inside: avoid; }
        h2 { color: #000; font-size: 18px; margin-bottom: 20px; border-bottom: 3px solid #FF8C00; padding-bottom: 10px; font-weight: bold; }
        .chart-box { background: white; border: 1px solid #e0e0e0; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .chart-container { position: relative; width: 100%; height: 400px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; background: white; }
        th { background-color: #FF8C00; color: white; border: 1px solid #FF8C00; padding: 12px; text-align: left; font-weight: bold; }
        td { border: 1px solid #ddd; padding: 10px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .valor { text-align: right; font-weight: 500; }
        .total { background-color: #fff9f0; font-weight: bold; }
        @media print { 
            body { margin: 0; padding: 0; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container" id="pdfContent">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 3px solid #FF8C00;">
            <div style="display: flex; gap: 20px; align-items: center;">
                <img src="../../logotipo.png" alt="Logo FWS" style="height: 80px; width: auto;">
            </div>
            <div style="text-align: center; flex: 1;">
                <h1 style="margin: 0; color: #000; font-size: 24px;">RELATÓRIO FINANCEIRO</h1>
                <p style="margin: 5px 0 0 0; color: #333; font-size: 14px; font-weight: bold;">JD AMÉRICA SHELL SELECT</p>
            </div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <img src="../../../FWS_Cliente/index/IMG/shell_select.png" alt="Logo Shell" style="height: 80px; width: auto;">
            </div>
        </div>
        <p class="subtitle">Gerado em ' . date('d/m/Y às H:i:s') . '</p>
        
        <!-- Seção Introdutória -->
        <div class="section" style="page-break-after: always;">
            <h2>📋 O que é este Relatório?</h2>
            <div style="font-size: 13px; line-height: 1.8; color: #333;">
                <p style="margin-bottom: 15px;">
                    Este Relatório Financeiro fornece uma análise completa e visual do desempenho financeiro da <strong>JD AMÉRICA SHELL SELECT</strong>. 
                    Ele consolida informações sobre movimentação de caixa, despesas operacionais, projeções de lucro e faturamento por categoria de produtos.
                </p>
                
                <p style="margin-bottom: 15px; background: #fffaf0; padding: 12px; border-left: 4px solid #FF8C00; border-radius: 5px;">
                    <strong>🎯 Objetivo:</strong> Fornecer ao gestor uma visão clara e estratégica dos resultados financeiros, 
                    permitindo identificar oportunidades de crescimento, controlar gastos e otimizar a lucratividade do negócio 
                    através de dados precisos e visualizações intuitivas.
                </p>
                
                <p style="margin-bottom: 0;">
                    <strong>📑 Seções incluídas:</strong>
                </p>
                <ul style="margin: 10px 0 0 20px; padding-left: 0;">
                    <li><strong>Movimentação Diária:</strong> Tendência de vendas vs custos ao longo do mês</li>
                    <li><strong>Despesas:</strong> Distribuição de gastos operacionais por tipo</li>
                    <li><strong>Projeção de Lucro:</strong> Potencial de lucro com estoque disponível</li>
                    <li><strong>Faturamento por Categoria:</strong> Produtos e categorias mais lucrativas</li>
                    <li><strong>Resumo Financeiro:</strong> Consolidação de métricas do mês</li>
                </ul>
            </div>
        </div>
        
        <!-- Introdução -->
        <div class="section" style="background: #fffaf0; padding: 20px; border-radius: 8px; border-left: 5px solid #FF8C00; margin-bottom: 40px;">
            <h2 style="margin-top: 0; color: #000; border: none;">📋 O que é este Relatório?</h2>
            <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 0;">
                Este <strong>Relatório Financeiro</strong> fornece uma análise completa e visual do desempenho financeiro da JD AMÉRICA SHELL SELECT. 
                Ele consolida informações sobre movimentação de caixa, despesas operacionais, projeções de lucro e faturamento por categoria de produtos.
            </p>
            <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 15px 0 0 0;">
                <strong>Objetivo:</strong> Fornecer ao gestor uma visão clara e estratégica dos resultados financeiros, permitindo identificar oportunidades de crescimento, 
                controlar gastos e otimizar a lucratividade do negócio através de dados precisos e visualizações intuitivas.
            </p>
            <p style="font-size: 14px; line-height: 1.8; color: #333; margin: 15px 0 0 0;">
                <strong>Seções incluídas:</strong> Movimentação Diária (tendência de vendas vs custos), Despesas (distribuição de gastos), 
                Projeção de Lucro (potencial com estoque disponível) e Faturamento por Categoria (produtos mais lucrativos).
            </p>
        </div>
        
        <!-- Seção 1: Movimentação Diária -->
        <div class="section" style="page-break-after: always; page-break-inside: avoid;">
            <h2>📈 Movimentação Diária</h2>
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="chartMovimentacao"></canvas>
                </div>
            </div>
            <div class="info-text">
                <strong>📊 O que representa:</strong> Este gráfico mostra a evolução acumulada das vendas (linha verde) e custos (linha vermelha) ao longo dos dias do mês. A distância entre as duas linhas representa o lucro bruto por dia. Quanto maior a diferença, melhor o desempenho.
            </div>
        </div>
        
        <!-- Seção 2: Despesas -->
        <div class="section" style="page-break-after: always; page-break-inside: avoid;">
            <h2>💰 Despesas</h2>
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="chartDespesas"></canvas>
                </div>
            </div>
            <div class="info-text">
                <strong>📊 O que representa:</strong> Visualiza a distribuição percentual das despesas por tipo. Cada cor representa um tipo diferente de despesa, permitindo identificar quais são as maiores fontes de gastos.
            </div>
            <table>
                <tr>
                    <th>Tipo de Despesa</th>
                    <th class="valor">Valor</th>
                </tr>';
    
    for ($i = 0; $i < count($tipos); $i++) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($tipos[$i]) . '</td>
                    <td class="valor">R$ ' . number_format($valores_despesas[$i], 2, ',', '.') . '</td>
                </tr>';
    }
    
    $html .= '<tr class="total">
                    <td><strong>Total de Despesas</strong></td>
                    <td class="valor"><strong>R$ ' . number_format($total_despesas, 2, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Seção 3: Projeção de Lucro -->
        <div class="section" style="page-break-after: always; page-break-inside: avoid;">
            <h2>🎯 Projeção de Lucro (com ' . $perc_estoque . '% de estoque)</h2>
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="chartGauge"></canvas>
                </div>
            </div>
            <div class="info-text">
                <strong>📊 O que representa:</strong> O gráfico mostra a proporção entre o lucro já realizado (laranja) e o potencial a ser conquistado (cinza). Quanto maior o preenchimento laranja, melhor está o desempenho em relação ao potencial.
            </div>
            <table>
                <tr class="total">
                    <td><strong>Métrica</strong></td>
                    <td class="valor"><strong>Valor</strong></td>
                </tr>
                <tr>
                    <td>Lucro Realizado</td>
                    <td class="valor">R$ ' . number_format($lucro_real, 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>Lucro Potencial</td>
                    <td class="valor">R$ ' . number_format($lucro_potencial, 2, ',', '.') . '</td>
                </tr>
                <tr class="total">
                    <td><strong>Oportunidade de Ganho</strong></td>
                    <td class="valor"><strong>R$ ' . number_format($diferenca, 2, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Seção 4: Faturamento por Categoria -->
        <div class="section" style="page-break-after: always; page-break-inside: avoid;">
            <h2>🏆 Faturamento por Categoria</h2>
            <div class="chart-box">
                <div class="chart-container">
                    <canvas id="chartCategorias"></canvas>
                </div>
            </div>
            <table>
                <tr>
                    <th>Categoria</th>
                    <th class="valor">Faturamento</th>
                </tr>';
    
    for ($i = 0; $i < count($categorias_nomes); $i++) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($categorias_nomes[$i]) . '</td>
                    <td class="valor">R$ ' . number_format($categorias_faturamento[$i], 2, ',', '.') . '</td>
                </tr>';
    }
    
    $html .= '<tr>
                    <td colspan="2" style="text-align: center; padding: 5px; background: white;"></td>
                </tr>
            </table>
        </div>
        
        <!-- Seção 5: Resumo Final -->
        <div class="section" style="page-break-inside: avoid;">
            <h2>📋 Resumo Financeiro do Mês</h2>
            <table>
                <tr class="total">
                    <td><strong>Métrica</strong></td>
                    <td class="valor"><strong>Valor</strong></td>
                </tr>
                <tr>
                    <td>Faturamento Real</td>
                    <td class="valor">R$ ' . number_format($faturamento_real, 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>Custo Real dos Produtos</td>
                    <td class="valor">R$ ' . number_format($custo_real, 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>Lucro Bruto</td>
                    <td class="valor">R$ ' . number_format($faturamento_real - $custo_real, 2, ',', '.') . '</td>
                </tr>
                <tr>
                    <td>Total de Despesas</td>
                    <td class="valor">R$ ' . number_format($total_despesas, 2, ',', '.') . '</td>
                </tr>
                <tr class="total" style="background: #fffaf0;">
                    <td><strong>🎯 Lucro Líquido</strong></td>
                    <td class="valor"><strong style="color: #FF8C00; font-size: 16px;">R$ ' . number_format($lucro_real, 2, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>
    </div>
    
    <script>
        // Configurações dos gráficos
        const chartConfigs = {
            movimentacao: {
                type: "line",
                data: {
                    labels: Array.from({length: ' . count($linha_verde) . '}, (_, i) => "D" + (i + 1)),
                    datasets: [
                        {
                            label: "Faturamento Acumulado",
                            data: ' . json_encode($linha_verde) . ',
                            borderColor: "#52c41a",
                            backgroundColor: "rgba(82, 196, 26, 0.08)",
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: "#52c41a",
                            pointBorderColor: "#fff",
                            pointBorderWidth: 2
                        },
                        {
                            label: "Custo Acumulado",
                            data: ' . json_encode($linha_vermelha) . ',
                            borderColor: "#ff4d4f",
                            backgroundColor: "rgba(255, 77, 79, 0.08)",
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointBackgroundColor: "#ff4d4f",
                            pointBorderColor: "#fff",
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { display: true, position: "top", labels: { font: { size: 14, weight: "bold" }, padding: 20 } }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { font: { size: 12 }, callback: function(value) { return "R$ " + value.toLocaleString("pt-BR"); } },
                            grid: { color: "#e8e8e8" },
                            title: { display: true, text: "Valor (R$)", font: { size: 12, weight: "bold" } }
                        },
                        x: { 
                            ticks: { font: { size: 12 } },
                            grid: { display: false },
                            title: { display: true, text: "Dias do Mês", font: { size: 12, weight: "bold" } }
                        }
                    }
                }
            },
            despesas: {
                type: "doughnut",
                data: {
                    labels: ' . json_encode($tipos) . ',
                    datasets: [{
                        data: ' . json_encode($valores_despesas) . ',
                        backgroundColor: ["#ff4d4f", "#faad14", "#1890ff", "#52c41a", "#FF8C00", "#13c2c2", "#eb2f96"]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: "bottom", 
                            labels: { font: { size: 12 }, padding: 15 }
                        }
                    }
                }
            },
            gauge: {
                type: "doughnut",
                data: {
                    labels: ["Realizado", "Potencial"],
                    datasets: [{
                        data: [' . ($lucro_potencial > 0 ? min(100, ($lucro_real / $lucro_potencial) * 100) : 0) . ', ' . ($lucro_potencial > 0 ? max(0, 100 - min(100, ($lucro_real / $lucro_potencial) * 100)) : 100) . '],
                        backgroundColor: ["#FF8C00", "#f0f0f0"],
                        borderWidth: 2,
                        borderColor: ["#FF8C00", "#ddd"]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            position: "bottom", 
                            labels: { font: { size: 12 }, padding: 15 }
                        }
                    }
                }
            },
            categorias: {
                type: "bar",
                data: {
                    labels: ' . json_encode($categorias_nomes) . ',
                    datasets: [{
                        label: "Faturamento",
                        data: ' . json_encode($categorias_faturamento) . ',
                        backgroundColor: "#13c2c2",
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: "y",
                    plugins: { 
                        legend: { display: true, labels: { font: { size: 12 } } }
                    },
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            ticks: { font: { size: 12 }, callback: function(value) { return "R$ " + value.toLocaleString("pt-BR"); } },
                            grid: { color: "#e8e8e8" },
                            title: { display: true, text: "Faturamento (R$)", font: { size: 12, weight: "bold" } }
                        },
                        y: { 
                            ticks: { font: { size: 12 } },
                            grid: { display: false },
                            title: { display: true, text: "Categorias", font: { size: 12, weight: "bold" } }
                        }
                    }
                }
            }
        };
        
        // Renderizar gráficos
        const ctxMovimentacao = document.getElementById("chartMovimentacao").getContext("2d");
        new Chart(ctxMovimentacao, chartConfigs.movimentacao);
        
        const ctxDespesas = document.getElementById("chartDespesas").getContext("2d");
        new Chart(ctxDespesas, chartConfigs.despesas);
        
        const ctxGauge = document.getElementById("chartGauge").getContext("2d");
        new Chart(ctxGauge, chartConfigs.gauge);
        
        const ctxCategorias = document.getElementById("chartCategorias").getContext("2d");
        new Chart(ctxCategorias, chartConfigs.categorias);
        
        // Aguardar gráficos renderizarem e gerar PDF
        setTimeout(() => {
            const { jsPDF } = window.jspdf;
            const element = document.getElementById("pdfContent");
            
            html2canvas(element, { 
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: "#ffffff"
            }).then(canvas => {
                const pdf = new jsPDF({
                    orientation: "portrait",
                    unit: "mm",
                    format: "a4"
                });
                
                const imgData = canvas.toDataURL("image/png");
                const imgWidth = 210;
                const pageHeight = 295;
                let imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;
                
                pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }
                
                pdf.save("relatorio_financeiro_' . date('Y-m-d_H-i-s') . '.pdf");
            });
        }, 3000);
    </script>
</body>
</html>';
    
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}

?>
