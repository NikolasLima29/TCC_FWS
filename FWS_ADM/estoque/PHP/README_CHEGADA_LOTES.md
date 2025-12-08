# 📦 Sistema de Data de Chegada (Recebimento) de Lotes

## 📋 Resumo da Implementação

Implementei um sistema completo que:
1. ✅ Adiciona coluna `chegada` à tabela `lotes_produtos`
2. ✅ Preenche retroativamente com: **Validade - Meses Padrão**
3. ✅ Insere automaticamente a data atual ao adicionar novo lote
4. ✅ Exibe corretamente na tabela de lotes

---

## 🚀 Como Usar

### **Passo 1: Executar a Migração**

Acesse no navegador:
```
http://seu-site/fws/FWS_ADM/estoque/PHP/migrar_chegada.php
```

Isso vai:
- ✅ Adicionar coluna `chegada` ao banco
- ✅ Preencher dados antigos retroativamente

### **Passo 2: Adicionar Novos Lotes**

Quando você adiciona um novo lote:
- A data de chegada é **automaticamente** preenchida com a **data atual**
- Quando lista os lotes, aparece a coluna "Chegada" com a data

---

## 📁 Arquivos Criados

| Arquivo | Descrição |
|---------|-----------|
| `gerenciar_chegada_lote.php` | Funções principais de cálculo |
| `migrar_chegada.php` | Interface de migração (visual) |
| `adicionar_coluna_chegada.php` | Script que adiciona coluna ao banco |
| `estoque.php` (modificado) | Integrado as funções |

---

## 🔧 Funções Disponíveis

### **1. `calcularDataChegada($conn, $produto_id, $data_validade)`**

Calcula a data de chegada baseado em: **Validade - Meses Padrão**

```php
$resultado = calcularDataChegada($conn, 1, '2026-12-06');

// Retorna:
// [
//     'sucesso' => true,
//     'data_chegada' => '2025-12-06',
//     'data_chegada_formatada' => '06/12/2025',
//     'calculo' => 'Validade (2026-12-06) - 12 meses = 2025-12-06'
// ]
```

**Fórmula:**
```
Data de Chegada = Data de Validade - Meses Padrão do Produto
```

**Exemplo Prático:**
- Produto: Vinho com 12 meses de validade padrão
- Validade: 06/12/2026
- **Cálculo:** 06/12/2026 - 12 meses = 06/12/2025
- **Data de Chegada:** 06/12/2025 ← Dia que o lote foi recebido

---

### **2. `preencherChegadaRetroativamente($conn)`**

Preenche a data de chegada para todos os lotes antigos que não têm.

```php
$resultado = preencherChegadaRetroativamente($conn);

// Retorna:
// [
//     'sucesso' => true,
//     'mensagem' => '150 lotes preenchidos com data de chegada retroativa',
//     'atualizados' => 150
// ]
```

---

### **3. `inserirLoteComChegada($conn, $produto_id, $quantidade, ...)`**

Insere um novo lote **com data de chegada automática** (data atual).

```php
$resultado = inserirLoteComChegada($conn, 1, 24, '2026-12-06');

// Retorna:
// [
//     'sucesso' => true,
//     'lote_id' => 169,
//     'data_chegada' => '2025-12-06',
//     'data_validade' => '2026-12-06'
// ]
```

---

## 📊 Estrutura da Tabela

Após a migração, a tabela `lotes_produtos` terá esta estrutura:

```
lotes_produtos
├── id (int)
├── produto_id (int)
├── validade (date)
├── quantidade (int)
├── fornecedor_id (int)
└── chegada (datetime) ← NOVA COLUNA
```

---

## 🔄 Fluxo de Dados

### **Novo Lote Adicionado:**

```
Usuário clica "Adicionar Lote"
    ↓
Sistema calcula:
  ├─ Validade = Hoje + Meses Padrão (ex: 12 meses)
  └─ Chegada = Hoje (data atual)
    ↓
Insere em lotes_produtos:
  ├─ produto_id = 1
  ├─ quantidade = 24
  ├─ validade = 2026-12-06
  └─ chegada = 2025-12-06
    ↓
Tabela mostra:
  ├─ Validade: 06/12/2026
  └─ Chegada: 06/12/2025 ← Data de recebimento do lote
```

### **Lotes Antigos (Retroativo):**

```
Lotes já existentes SEM data de chegada
    ↓
Sistema calcula para cada um:
  Chegada = Validade - Meses Padrão
    ↓
Exemplo:
  Lote: Validade 2026-12-06, Produto com 12 meses
  Cálculo: 2026-12-06 - 12 meses = 2025-12-06
  ↓
Preenche chegada = 2025-12-06
```

---

## 📈 Exemplos de Cálculo

| Produto | Meses | Validade | Chegada (calculada) | Descrição |
|---------|-------|----------|---------------------|-----------|
| Vinho | 12 | 06/12/2026 | 06/12/2025 | Recebido 1 ano antes do vencimento |
| Leite | 1 | 06/01/2026 | 06/12/2025 | Recebido 1 mês antes do vencimento |
| Biscoito | 24 | 06/12/2027 | 06/12/2025 | Recebido 2 anos antes do vencimento |
| Água | 0 | NULL | NULL | Sem validade padrão |

---

## ✅ Checklist de Implementação

- [x] Funções PHP para calcular chegada
- [x] Script de migração com interface visual
- [x] Integração em estoque.php
- [x] Preencher dados antigos retroativamente
- [x] Adicionar coluna ao banco automaticamente
- [x] Exibir corretamente na tabela
- [x] Documentação completa

---

## 🎯 Testes

### **Teste 1: Calcular Data de Chegada**

```bash
curl "http://localhost/fws/FWS_ADM/estoque/PHP/gerenciar_chegada_lote.php?action=calcular&produto_id=1&validade=2026-12-06"
```

Resposta esperada:
```json
{
    "sucesso": true,
    "data_chegada": "2025-12-06",
    "data_chegada_formatada": "06/12/2025",
    "meses_padrao": 12
}
```

### **Teste 2: Preencher Retroativamente**

```bash
curl "http://localhost/fws/FWS_ADM/estoque/PHP/gerenciar_chegada_lote.php?action=preencher_retroativo"
```

---

## 💡 Casos de Uso

### **Caso 1: Novo Lote Recebido Hoje**
- Data de chegada = Data de hoje
- Data de validade = Hoje + 12 meses (ex)
- Na tabela mostra ambas as datas

### **Caso 2: Verificar Quando Lotes Foram Recebidos**
- Coluna "Chegada" mostra a data de recebimento
- Útil para rastrear histórico de estoque

### **Caso 3: Preencher Dados Históricos**
- Execute o script de migração
- Calcula automaticamente para todos os lotes antigos

---

## 🔐 Segurança

- ✅ Migração protegida: apenas super admin (nível 3)
- ✅ Validação de entrada em todas as funções
- ✅ Preparação de queries quando possível
- ✅ Trata erros graciosamente

---

## 📞 Como Migrar

### **Passo a Passo:**

1. **Acesse a página de migração:**
   ```
   http://seu-site/fws/FWS_ADM/estoque/PHP/migrar_chegada.php
   ```

2. **Clique em "Executar Passo 1"**
   - Adiciona coluna `chegada` ao banco

3. **Clique em "Executar Passo 2"**
   - Preenche dados antigos retroativamente
   - Mostra quantos lotes foram preenchidos

4. **Pronto!** ✅
   - Coluna agora está ativa
   - Novos lotes terão chegada preenchida automaticamente

---

## 🚀 Próximas Melhorias

- [ ] Relatório de lotes por data de chegada
- [ ] Gráfico de evolução de recebimentos
- [ ] Notificação quando lote é recebido
- [ ] Editar data de chegada se necessário
- [ ] Exportar histórico de chegadas

---

## ⚠️ Importante

- A coluna `chegada` é preenchida **automaticamente** em:
  - ✅ Novos lotes adicionados (data de hoje)
  - ✅ Lotes antigos (retroativamente: validade - meses)
  
- A migração é **segura** e pode ser executada quantas vezes quiser
  - Se coluna já existe, não faz nada
  - Se registro já tem chegada, não sobrescreve

Tudo pronto! 🎉
