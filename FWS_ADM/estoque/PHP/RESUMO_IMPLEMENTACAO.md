# ✅ Sistema de Cálculo de Validade de Lotes - Implementação Completa

## 📌 Resumo do que foi criado

Você pediu para substituir a lógica do **TRIGGER SQL** por uma **função PHP** que calcula a validade automaticamente quando um novo lote é adicionado e exibe na tabela com formatação (cores e ícones).

### ✨ Solução Implementada

#### **1. Arquivo Principal: `calcular_validade_lote.php`**

```
📁 FWS_ADM/estoque/PHP/calcular_validade_lote.php
```

**Funções:**
- ✅ `calcularValidadeLote($conn, $produto_id)` - Calcula a validade
- ✅ `getValidadeFormatada($conn, $produto_id)` - Retorna HTML formatado com cores
- ✅ `dateDifference($data)` - Calcula dias até vencimento

---

## 🔄 Como Funciona

### **Fluxo Automático:**

```
Usuário clica em "Adicionar Lote"
           ↓
Abre modal pedindo quantidade
           ↓
Backend calcula validade do produto:
  └─ Se produto tem X meses padrão
     └─ Validade = Hoje + X meses
     └─ Se 0 meses = Sem validade
           ↓
Insere na tabela lotes_produtos com validade calculada
           ↓
Exibe na tabela com cores:
  ├─ Verde ✓ = Válido (>10 dias)
  ├─ Laranja ⏰ = Próximo ao vencimento (≤10 dias)
  └─ Vermelho ⚠️ = Vencido
```

---

## 📋 Arquivos Criados

| Arquivo | Descrição |
|---------|-----------|
| `calcular_validade_lote.php` | **Funções principais** para calcular validade |
| `README_VALIDADE_LOTES.md` | **Documentação completa** com exemplos |
| `exemplo_uso_validade.php` | **Exemplos práticos** de como usar |
| `estoque.php` (modificado) | **Integração** na página de estoque |

---

## 🚀 Uso Rápido

### **Passo 1: Import**
```php
<?php
include "../../conn.php";
include "../PHP/calcular_validade_lote.php";  // ← Adicione isto

session_start();
?>
```

### **Passo 2: Calcular**
```php
$resultado = calcularValidadeLote($conn, $produto_id);

// $resultado contém:
// [
//     'sucesso' => true,
//     'validade' => '2026-12-06',
//     'validade_formatada' => '06/12/2026',
//     'meses' => 12
// ]
```

### **Passo 3: Exibir na Tabela**
```php
<td><?= getValidadeFormatada($conn, $produto_id) ?></td>

// Saída:
// ✓ 06/12/2026  (verde, se válido)
// ⏰ 06/12/2025 (laranja, se próximo ao vencimento)
// ⚠️ VENCIDO    (vermelho, se vencido)
```

---

## 🎯 Comparação: TRIGGER vs PHP

| Aspecto | TRIGGER SQL | PHP Function |
|---------|-------------|---------------|
| **Execução** | Automática no INSERT | Sob demanda |
| **Localização** | No banco de dados | No código |
| **Formatação** | Apenas data | Data + cores + ícones |
| **Debug** | Difícil de testar | Fácil (pode ver logs) |
| **Reutilização** | Só funciona em INSERT | Funciona em qualquer lugar |
| **Flexibilidade** | Fixa | Pode adaptar conforme necessário |

---

## 📊 Exemplo de Exibição na Tabela

```
┌────────┬─────────────┬────────────────────────┐
│ Produto│ Quantidade  │ Validade               │
├────────┼─────────────┼────────────────────────┤
│ Água   │ 50          │ ✓ 15/06/2026          │
│ Vinho  │ 20          │ ⏰ 10/12/2025 (3d)    │
│ Leite  │ 5           │ ⚠️ VENCIDO             │
│ Óleo   │ 100         │ Sem validade           │
└────────┴─────────────┴────────────────────────┘
```

---

## 🧪 Testar via URL

```
GET /fws/FWS_ADM/estoque/PHP/calcular_validade_lote.php?action=calcular&produto_id=1
```

**Resposta:**
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

## 💡 Exemplos de Uso

### **Exemplo 1: Adicionar Lote com Validade Automática**

```php
if ($_POST['repor_estoque']) {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    
    // Calcular validade
    $val = calcularValidadeLote($conn, $produto_id);
    
    // Inserir com a validade calculada
    $sql = "INSERT INTO lotes_produtos (produto_id, quantidade, validade) 
            VALUES ($produto_id, $quantidade, " . 
            ($val['validade'] ? "'{$val['validade']}'" : "NULL") . ")";
    
    $conn->query($sql);
}
```

### **Exemplo 2: Exibir na Tabela com Formatação**

```php
<tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['nome']) ?></td>
        <td><?= $row['estoque'] ?></td>
        <td><?= getValidadeFormatada($conn, $row['produto_id']) ?></td>
    </tr>
    <?php endwhile; ?>
</tbody>
```

### **Exemplo 3: Alertas de Vencimento**

```php
$val = calcularValidadeLote($conn, $produto_id);
$dias = dateDifference($val['validade']);

if ($dias < 0) {
    echo "❌ Produto vencido há " . abs($dias) . " dias!";
} elseif ($dias <= 10) {
    echo "⚠️ Vence em " . $dias . " dias!";
} else {
    echo "✅ Produto válido por " . $dias . " dias";
}
```

---

## 🔧 Integração Atual (Estoque.php)

A função já foi **integrada automaticamente** na página de estoque:

✅ Include adicionado  
✅ Tabela de lotes atualizada para usar `getValidadeFormatada()`  
✅ Cores e ícones automáticos  
✅ Cálculo realizado em PHP (sem trigger)  

---

## 📚 Documentação Disponível

1. **`README_VALIDADE_LOTES.md`** - Documentação detalhada
2. **`exemplo_uso_validade.php`** - Exemplos práticos
3. **Comentários no código** - Explicações inline

---

## ✅ Checklist

- [x] Função PHP para calcular validade
- [x] Função para formatação com cores
- [x] Integração na página estoque.php
- [x] Suporte a produtos com/sem validade
- [x] Alertas de vencimento
- [x] Documentação completa
- [x] Exemplos de uso

---

## 🎉 Resultado Final

Agora quando você adiciona um novo lote:

1. ✅ A validade é **calculada automaticamente** baseada nos meses padrão
2. ✅ Aparece na tabela **com cores e ícones**
3. ✅ Verde se válido, laranja se próximo ao vencimento, vermelho se vencido
4. ✅ Sem necessidade de TRIGGER no banco de dados
5. ✅ Totalmente reutilizável em outras páginas

---

## 📞 Próximas Melhorias Sugeridas

- [ ] Notificar usuário quando lote está próximo ao vencimento
- [ ] Relatório de produtos para descartar (vencidos)
- [ ] Dashboard com gráfico de validez dos produtos
- [ ] API para consultar validez via mobile
- [ ] Sistema de alertas automáticos

Tudo pronto! 🚀
