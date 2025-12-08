# Verificação de Tempo Limite de Pedidos

## Descrição
Este arquivo contém duas funções que verificam se um pedido (venda) ultrapassou o tempo limite de preparação e cancela automaticamente se necessário.

## Arquivo
`FWS_ADM/PHP/verificar_tempo_limite.php`

## Funções

### 1. `verificarECancelarPedidosComTempoExpirado($conn)`

**Descrição:** Verifica todos os pedidos que estão em preparação e ultrapassaram o tempo limite.

**Parâmetros:**
- `$conn` (mysqli): Conexão com o banco de dados

**Retorno:** Array com a seguinte estrutura:
```php
[
    'sucesso' => true/false,
    'mensagem' => 'string com descrição do resultado',
    'pedidos_cancelados' => [
        [
            'venda_id' => int,
            'usuario_id' => int,
            'data_criacao' => 'datetime',
            'tempo_limite' => 'HH:MM:SS',
            'segundos_decorridos' => int,
            'tempo_limite_segundos' => int
        ],
        // ... mais pedidos se houver
    ]
]
```

**Uso em PHP:**
```php
require_once 'FWS_ADM/PHP/verificar_tempo_limite.php';
$resultado = verificarECancelarPedidosComTempoExpirado($conn);

if ($resultado['sucesso']) {
    foreach ($resultado['pedidos_cancelados'] as $pedido) {
        echo "Pedido #{$pedido['venda_id']} foi cancelado\n";
    }
}
```

**Via URL (GET):**
```
GET /fws/FWS_ADM/PHP/verificar_tempo_limite.php?action=verificar_todos
```

---

### 2. `verificarECancelarPedidoPorId($conn, $vendaId)`

**Descrição:** Verifica um pedido específico e cancela se o tempo limite foi ultrapassado.

**Parâmetros:**
- `$conn` (mysqli): Conexão com o banco de dados
- `$vendaId` (int): ID da venda a verificar

**Retorno:** Array com informações do resultado

**Caso 1 - Pedido cancelado por expiração:**
```php
[
    'sucesso' => true,
    'mensagem' => 'Venda cancelada por tempo expirado',
    'cancelado' => true,
    'venda_id' => 25,
    'tempo_limite' => '00:15:00',
    'segundos_decorridos' => 900
]
```

**Caso 2 - Pedido ainda dentro do tempo limite:**
```php
[
    'sucesso' => true,
    'mensagem' => 'Venda ainda está dentro do tempo limite',
    'cancelado' => false,
    'venda_id' => 25,
    'tempo_restante' => 300,
    'tempo_restante_formatado' => '5m 0s'
]
```

**Caso 3 - Venda não encontrada:**
```php
[
    'sucesso' => false,
    'mensagem' => 'Venda não encontrada',
    'cancelado' => false
]
```

**Caso 4 - Venda já finalizada:**
```php
[
    'sucesso' => false,
    'mensagem' => 'Não é possível cancelar uma venda finalizada',
    'cancelado' => false,
    'venda_id' => 25
]
```

**Uso em PHP:**
```php
require_once 'FWS_ADM/PHP/verificar_tempo_limite.php';
$resultado = verificarECancelarPedidoPorId($conn, 25);

if ($resultado['cancelado']) {
    echo "Pedido foi cancelado por expiração de tempo!";
} elseif ($resultado['sucesso']) {
    echo "Tempo restante: " . $resultado['tempo_restante_formatado'];
}
```

**Via URL (GET):**
```
GET /fws/FWS_ADM/PHP/verificar_tempo_limite.php?action=verificar&venda_id=25
```

---

## Integração com Eventos do MySQL

O banco de dados já possui um evento MySQL que executa automaticamente esta verificação:

```sql
CREATE EVENT `ev_expirar_pre_compras` ON SCHEDULE EVERY 10 SECOND
DO BEGIN
    UPDATE vendas
    SET situacao_compra = 'cancelada'
    WHERE situacao_compra = 'em_preparo'
      AND TIMESTAMPDIFF(SECOND, data_criacao, NOW()) > TIME_TO_SEC(tempo_chegada);
END;
```

**Este evento executa a cada 10 segundos** e cancela automaticamente os pedidos com tempo expirado.

---

## Como Usar em seu Sistema

### Opção 1: Verificação Automática (via Evento MySQL)
Nenhuma alteração necessária - o evento MySQL já faz isso automaticamente a cada 10 segundos.

### Opção 2: Verificação Manual em uma Página
Adicione esta chamada em um arquivo PHP (como em um dashboard do administrador):

```php
<?php
require_once 'FWS_ADM/conn.php';
require_once 'FWS_ADM/PHP/verificar_tempo_limite.php';

// Verificar todos os pedidos expirados
$resultado = verificarECancelarPedidosComTempoExpirado($conn);

if ($resultado['sucesso'] && count($resultado['pedidos_cancelados']) > 0) {
    echo "<div class='alert alert-warning'>";
    echo $resultado['mensagem'];
    echo "</div>";
}
?>
```

### Opção 3: Verificação via AJAX
No JavaScript do cliente:

```javascript
// Verificar um pedido específico
fetch('/fws/FWS_ADM/PHP/verificar_tempo_limite.php?action=verificar&venda_id=25')
    .then(response => response.json())
    .then(data => {
        if (data.cancelado) {
            alert('Seu pedido foi cancelado por expiração de tempo!');
        } else if (data.sucesso) {
            console.log('Tempo restante: ' + data.tempo_restante_formatado);
        }
    });
```

### Opção 4: Verificação Periódica com Cron Job
Crie um script que executa a função periodicamente:

```bash
# A cada 5 minutos, verificar pedidos expirados
*/5 * * * * curl "http://seu-dominio.com/fws/FWS_ADM/PHP/verificar_tempo_limite.php?action=verificar_todos"
```

---

## Fluxo de Funcionamento

```
Pedido Criado (em_preparo)
         ↓
Cada 10 segundos (Evento MySQL)
         ↓
Verifica se: data_criacao + tempo_chegada < NOW()
         ↓
Se TRUE → situacao_compra = 'cancelada'
         ↓
Se FALSE → permanece 'em_preparo'
```

---

## Estrutura da Tabela `vendas`

As colunas relevantes para essa verificação são:

| Coluna | Tipo | Descrição |
|--------|------|-----------|
| `id` | INT | ID único da venda |
| `usuario_id` | INT | ID do usuário que fez o pedido |
| `data_criacao` | DATETIME | Data/hora de criação do pedido |
| `tempo_chegada` | TIME | Tempo máximo para preparação (HH:MM:SS) |
| `situacao_compra` | ENUM | Estado do pedido: 'em_preparo', 'pronto_para_retirar', 'finalizada', 'cancelada' |
| `status_pagamento` | ENUM | Estado do pagamento: 'pago', 'pendente', 'cancelado' |

---

## Exemplo Completo

```php
<?php
require_once 'FWS_ADM/conn.php';
require_once 'FWS_ADM/PHP/verificar_tempo_limite.php';

// Verificar todos os pedidos expirados
$resultadoTodos = verificarECancelarPedidosComTempoExpirado($conn);

// Verificar um pedido específico
$resultadoEspecifico = verificarECancelarPedidoPorId($conn, 25);

// Exibir resultados
if ($resultadoTodos['sucesso']) {
    echo "Verifcação geral: " . $resultadoTodos['mensagem'] . "\n";
    echo "Total cancelado: " . count($resultadoTodos['pedidos_cancelados']) . "\n\n";
}

if ($resultadoEspecifico['sucesso']) {
    if ($resultadoEspecifico['cancelado']) {
        echo "Pedido #25 foi cancelado\n";
    } else {
        echo "Pedido #25 ainda está válido por: " . $resultadoEspecifico['tempo_restante_formatado'] . "\n";
    }
}
?>
```

---

## Observações Importantes

1. **O evento MySQL é independente** - Ele funciona automaticamente mesmo que nenhuma requisição PHP seja feita
2. **Trigger de proteção** - Existe um TRIGGER que impede cancelar vendas já finalizadas
3. **Transações** - As atualizações são feitas direto no banco (não há transação)
4. **Formato de tempo** - O `tempo_chegada` deve estar no formato HH:MM:SS (ex: 00:15:00 para 15 minutos)
5. **Horário do servidor** - A verificação usa NOW() do servidor MySQL, não do PHP

---

## Possíveis Melhorias Futuras

- [ ] Registrar um log de cancelamentos em uma tabela separada
- [ ] Enviar notificação para o usuário quando seu pedido é cancelado
- [ ] Permitir "estender" o tempo limite
- [ ] Adicionar informações de cancelamento (motivo, timestamp)
- [ ] Dashboard com estatísticas de cancelamentos
