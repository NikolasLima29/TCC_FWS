# Cálculo Automático de Validade de Lotes

## 📋 Descrição

Este sistema calcula automaticamente a data de validade dos lotes de produtos baseado na **validade padrão em meses** cadastrada no produto, sem necessidade do TRIGGER SQL.

## 📁 Arquivo

`FWS_ADM/estoque/PHP/calcular_validade_lote.php`

---

## 🔧 Funções Disponíveis

### 1. `calcularValidadeLote($conn, $produto_id)`

**Descrição:** Calcula a data de validade baseada nos meses padrão do produto.

**Parâmetros:**
- `$conn` (mysqli): Conexão com o banco de dados
- `$produto_id` (int): ID do produto

**Retorno:** Array com a estrutura:

```php
[
    'sucesso' => true/false,
    'mensagem' => 'string descritiva',
    'validade' => 'YYYY-MM-DD' | null,
    'validade_formatada' => 'dd/mm/YYYY' | 'Sem validade' | 'N/A',
    'meses' => int (número de meses)
]
```

**Exemplos:**

```php
// Exemplo 1: Produto com validade
$resultado = calcularValidadeLote($conn, 1);
// Resultado (produto com 12 meses de validade):
// [
//     'sucesso' => true,
//     'validade' => '2026-12-06',
//     'validade_formatada' => '06/12/2026',
//     'meses' => 12
// ]

// Exemplo 2: Produto sem validade padrão
$resultado = calcularValidadeLote($conn, 12);
// Resultado:
// [
//     'sucesso' => true,
//     'validade' => null,
//     'validade_formatada' => 'Sem validade',
//     'meses' => 0
// ]

// Exemplo 3: Produto não encontrado
$resultado = calcularValidadeLote($conn, 99999);
// Resultado:
// [
//     'sucesso' => false,
//     'mensagem' => 'Produto não encontrado'
// ]
```

---

### 2. `getValidadeFormatada($conn, $produto_id)`

**Descrição:** Retorna HTML formatado com a validade para exibição em tabelas.

**Retorno:** String HTML com cores e ícones

**Exemplos de retorno:**

- ✓ Produto válido: `<span style="color:#52c41a;">✓ 06/12/2026</span>`
- ⏰ Vencendo em breve: `<span style="color:#ff9500; font-weight:bold;">⏰ 06/12/2025 (5d)</span>`
- ⚠️ Vencido: `<span style="color:#d11b1b; font-weight:bold;">⚠️ VENCIDO</span>`
- Sem validade: `<span style="color:#999; font-style:italic;">Sem validade</span>`

**Uso em HTML:**

```php
<?php
$validade_html = getValidadeFormatada($conn, $produto_id);
?>
<td><?= $validade_html ?></td>
```

---

### 3. `dateDifference($data)`

**Descrição:** Calcula a diferença de dias entre hoje e uma data futura.

**Parâmetros:**
- `$data` (string): Data no formato 'Y-m-d'

**Retorno:** int (número de dias restantes, negativo se já passou)

```php
$dias = dateDifference('2026-12-06');
// Se hoje é 06/12/2025, retorna: 365
// Se já passou, retorna: -5
```

---

## 🔌 Integração na Página de Estoque

### Passo 1: Adicionar o Include

No início do arquivo `estoque.php`:

```php
<?php
include "../../conn.php";
include "../PHP/calcular_validade_lote.php";  // ← Adicionar esta linha

session_start();
// ... resto do código
```

### Passo 2: Usar na Tabela

```php
<?php while ($row = $result->fetch_assoc()): 
    $produto_id = $row['id'];
    
    // Calcular validade
    $resultado_validade = calcularValidadeLote($sql, $produto_id);
    
    // Se há validade no banco, usar ela; caso contrário, usar a calculada
    if ($row['validade']) {
        $validade_exibicao = date('d/m/Y', strtotime($row['validade']));
    } else {
        $validade_exibicao = $resultado_validade['validade_formatada'];
    }
?>
    <tr>
        <!-- ... outras colunas ... -->
        <td><?= getValidadeFormatada($sql, $produto_id) ?></td>
    </tr>
<?php endwhile; ?>
```

---

## 📊 Lógica de Cálculo

### Fluxo:

```
1. Buscar produto pelo ID
   ↓
2. Obter validade_padrao_meses do produto
   ↓
3. Se meses = NULL ou 0:
      → Retorna validade = NULL ("Sem validade")
   ↓
4. Se meses > 0:
      → Calcula: DATA_HOJE + X MESES
      → Retorna data no formato YYYY-MM-DD
   ↓
5. Formata para exibição (dd/mm/YYYY)
```

### Exemplo Prático:

- Produto ID 1: `validade_padrao_meses = 12`
- Data de hoje: 06/12/2025
- Validade calculada: 06/12/2026 (um ano depois)
- Exibição: `06/12/2026`

---

## 🎨 Formatação de Exibição

A função `getValidadeFormatada()` retorna HTML com cores automáticas:

| Situação | Cor | Ícone | Exemplo |
|----------|-----|-------|---------|
| Válido (>10 dias) | Verde (#52c41a) | ✓ | ✓ 06/12/2026 |
| Próximo ao vencimento (≤10 dias) | Laranja (#ff9500) | ⏰ | ⏰ 06/12/2025 (5d) |
| Vencido | Vermelho (#d11b1b) | ⚠️ | ⚠️ VENCIDO |
| Sem validade | Cinza (#999) | - | Sem validade |

---

## 🧪 Teste via URL

Para testar a função via requisição GET:

```
GET /fws/FWS_ADM/estoque/PHP/calcular_validade_lote.php?action=calcular&produto_id=1
```

**Resposta JSON:**

```json
{
    "sucesso": true,
    "mensagem": "Validade calculada com sucesso",
    "validade": "2026-12-06",
    "validade_formatada": "06/12/2026",
    "meses": 12
}
```

---

## 💡 Casos de Uso

### Caso 1: Adicionar um Novo Lote

Quando o usuário clica em "Adicionar Lote":

```php
if ($_POST['repor_estoque']) {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade_custom'];
    
    // Calcular validade
    $resultado = calcularValidadeLote($conn, $produto_id);
    
    // Inserir lote com a validade calculada
    $sql = "INSERT INTO lotes_produtos (produto_id, quantidade, validade) 
            VALUES ($produto_id, $quantidade, " . 
            ($resultado['validade'] ? "'" . $resultado['validade'] . "'" : "NULL") . ")";
    $conn->query($sql);
}
```

### Caso 2: Exibir na Tabela de Lotes

```php
<td><?= getValidadeFormatada($conn, $produto_id) ?></td>
```

### Caso 3: Alertas de Validade

```php
$resultado = calcularValidadeLote($conn, $produto_id);

if ($resultado['sucesso'] && $resultado['validade']) {
    $dias_restantes = dateDifference($resultado['validade']);
    
    if ($dias_restantes < 0) {
        echo "⚠️ Produto VENCIDO!";
    } elseif ($dias_restantes <= 10) {
        echo "⏰ Vence em {$dias_restantes} dias";
    }
}
```

---

## ✅ Vantagens da Abordagem em PHP

1. **Sem TRIGGER SQL** - Lógica no PHP, mais fácil de debugar
2. **Flexibilidade** - Pode se adaptar a diferentes regras de cálculo
3. **Reutilizável** - Mesma função em várias páginas
4. **Formatação Automática** - Inclui cores e ícones na exibição
5. **Testes Fáceis** - Pode ser testado via URL

---

## ⚠️ Diferenças do Trigger Original

| Aspecto | Trigger SQL | PHP Function |
|---------|-------------|---------------|
| Execução | Automática no INSERT | Manual, chamada no código |
| Flexibilidade | Fixa no banco | Adapta conforme necessário |
| Debug | Difícil | Fácil (pode ver resultados) |
| Exibição | Apenas data | Data + cores + ícones |
| Performance | Automática | Sob demanda |

---

## 🚀 Próximas Melhorias

- [ ] Cache de validação para melhorar performance
- [ ] API endpoint para consultar validades
- [ ] Relatório de produtos próximos ao vencimento
- [ ] Notificação automática quando estão próximos ao vencimento
- [ ] Gráfico de validades dos produtos
