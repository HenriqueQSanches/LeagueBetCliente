# 📡 DOCUMENTAÇÃO - API DE JOGOS

## ✅ SIM, SEU CLIENTE ESTÁ CORRETO!

Os dados dos jogos **SÃO** puxados via uma **API externa** chamada:

### 🔗 **API Principal**
```
URL: https://apijogos.com/betsports3.php
Tipo: API REST (JSON)
Método: GET
Função: Importar jogos, times, campeonatos e cotações
```

### 🔗 **API de Resultados**
```
URL: http://apijogos.com/resultados/index.php
Tipo: API REST (JSON)
Método: GET
Função: Importar placares finais dos jogos
```

---

## 🏗️ ARQUITETURA DA INTEGRAÇÃO

### **1. Classe Principal: `APIMarjo`**
📁 **Arquivo:** `app/helpers/APIMarjo.php`

```php
class APIMarjo {
    private $client; // GuzzleHttp Client
    
    public function __construct() {
        $this->client = new Client([
            'base_url' => 'https://apijogos.com/betsports3.php',
            'verify' => false,
        ]);
    }
    
    // Principais métodos:
    - importarJogos()      → Busca jogos da API
    - importarPlacares()   → Busca resultados
    - getJogos()           → Faz requisição HTTP
    - atualizaTimes()      → Salva times no banco
    - atualizaCampeonatos()→ Salva campeonatos no banco
}
```

---

## 🔄 FLUXO DE IMPORTAÇÃO

### **Etapa 1: Buscar Dados da API**
```
Cliente → APIMarjo::getJogos()
         ↓
    GuzzleHttp Client (GET https://apijogos.com/betsports3.php/jogos)
         ↓
    Retorna JSON com:
    {
        "times": ["Time A", "Time B", ...],
        "campeonatos": ["Brasileirão Série A", ...],
        "jogos": [
            {
                "refid": "12345",
                "data": "2025-11-05",
                "hora": "19:00",
                "campeonato": "Brasileirão Série A",
                "timecasa": "Red Bull Bragantino SP",
                "timefora": "Corinthians SP",
                "cotacoes": {
                    "90": {
                        "casa": 2.45,
                        "empate": 2.85,
                        "fora": 2.48
                    },
                    "pt": {...},
                    "st": {...}
                }
            }
        ]
    }
```

### **Etapa 2: Processar e Salvar no Banco**
```
1. Atualiza tabela `sis_times` com novos times
2. Atualiza tabela `sis_campeonatos` com novos campeonatos
3. Insere/Atualiza jogos na tabela `sis_jogos`:
   - Se jogo não existe: INSERT (novo)
   - Se jogo existe: UPDATE (atualiza cotações)
```

### **Etapa 3: Aplicar Limite de Cotação**
```php
$limiteCotacao = DadosModel::get()->getLimiteCotacao();

if ($limiteCotacao > 0) {
    foreach ($jogo['cotacoes'] as $tempo => $cotacoes) {
        foreach ($cotacoes as $campo => $valor) {
            // Limita cotação máxima
            $valor_final = min($limiteCotacao, $valor);
        }
    }
}
```

---

## 📊 ESTRUTURA DO JSON RETORNADO

### **Exemplo de Resposta da API:**
```json
{
    "result": 1,
    "message": "Sucesso",
    "times": [
        "Red Bull Bragantino SP",
        "Corinthians SP",
        "EC Vitória BA"
    ],
    "campeonatos": [
        "Brasil - Brasileirão Série A",
        "Brasil - Carioca, Serie B1"
    ],
    "jogos": [
        {
            "refid": "evt_12345",
            "idPartida": "12345",
            "eventid": "12345",
            "campeonato": "Brasil - Brasileirão Série A",
            "mandante": "Red Bull Bragantino SP",
            "visitante": "Corinthians SP",
            "timecasa": "Red Bull Bragantino SP",
            "timefora": "Corinthians SP",
            "data": "2025-11-05",
            "hora": "19:00",
            "tempo": "90",
            "bandeira": "br.png",
            "cotacoes": {
                "90": {
                    "casa": 2.45,
                    "empate": 2.85,
                    "fora": 2.48,
                    "amb": 1.50,
                    "ambn": 2.10,
                    "gmais1": 1.15,
                    "gmais2": 1.55,
                    "gmais3": 2.20,
                    "gmenos2": 2.05,
                    "gmenos3": 1.40,
                    "dplcasa": 1.85,
                    "dplfora": 2.05,
                    "casacasa": 3.80,
                    "casaempate": 4.50,
                    "casafora": 8.50
                    // ... mais cotações
                },
                "pt": {
                    // Cotações do primeiro tempo
                },
                "st": {
                    // Cotações do segundo tempo
                }
            }
        }
    ]
}
```

---

## 🤖 AUTOMATIZAÇÃO - CRON JOBS

### **1. Importação de Jogos**
📁 **Arquivo:** `app/modules/cron/controllers/jogosController.php`

```php
class jogosController extends Controller {
    
    function indexAction() {
        $result = (new APIMarjo())->importarJogos();
        $ip = getUserIP();
        error_log("Cron: {$ip} {$result['message']}");
        return $result;
    }
}
```

**URL de Acesso:**
```
http://localhost:8000/cron/jogos
```

**Configurar no Cron (Linux/cPanel):**
```bash
# A cada 30 minutos
*/30 * * * * curl -s http://localhost:8000/cron/jogos
```

**Configurar no Task Scheduler (Windows):**
```
Ação: Iniciar um programa
Programa: C:\xampp\php\php.exe
Argumentos: -f "C:\xampp\htdocs\Cliente\LeagueBetCliente-main\jogos.php"
Repetir: A cada 30 minutos
```

---

### **2. Importação de Resultados**
📁 **Arquivo:** `app/modules/cron/controllers/jogosController.php`

```php
function resultadosAction() {
    try {
        Conn::startTransaction();
        $response = (new APIMarjo())->importarPlacares();
        $ip = getUserIP();
        error_log("Cron: {$ip} {$response['message']}");
        
        // Processa apostas após definir placares
        apostasController::instance()->baixaAction();
        
        Conn::commit();
        return $response;
    } catch (\Exception $ex) {
        Conn::rollBack();
        return $ex;
    }
}
```

**URL de Acesso:**
```
http://localhost:8000/cron/jogos/resultados
```

**Configurar no Cron (Linux/cPanel):**
```bash
# A cada hora (após jogos terminarem)
0 * * * * curl -s http://localhost:8000/cron/jogos/resultados
```

---

## 📁 ARQUIVOS RELACIONADOS

### **1. Scripts de Importação Manual**

#### **`jogos.php`** (Raiz do Projeto)
```php
// Script legado de importação
$url = "https://apijogos.com/betsports3.php";
$page_content = file_get_contents($url);
$result = json_decode($page_content);

foreach($result as $key) {
    $idjogo = $key->idPartida;
    $campeonato = $key->campeonato;
    $mandante = $key->mandante;
    $visitante = $key->visitante;
    // ... processa e salva no banco
}
```

**Executar Manualmente:**
```bash
php jogos.php
```

---

#### **`atualiza.php`** (Raiz do Projeto)
```php
// Script para atualizar jogos
$url = "http://apijogos.com/betsports2.php";
$page_content = file_get_contents($url);
$result = json_decode($page_content);

foreach($result as $key) {
    // Atualiza jogos existentes
}
```

**Executar Manualmente:**
```bash
php atualiza.php
```

---

### **2. Painel Admin - Importação Manual**

📁 **Controller:** `app/modules/admin/controllers/importar/marjosportsController.php`

**Funcionalidades:**
- ✅ Listar jogos da API antes de importar
- ✅ Selecionar quais jogos importar
- ✅ Visualizar preview com imagens dos times
- ✅ Importação em lote

**Acesso pelo Painel:**
```
Admin → Importar → MarjoSports
```

---

## 🗄️ ESTRUTURA DO BANCO DE DADOS

### **Tabelas Afetadas:**

#### **1. `sis_times`**
```sql
CREATE TABLE sis_times (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255),
    title VARCHAR(255) NOT NULL,
    status INT DEFAULT 1,
    insert DATETIME,
    update DATETIME,
    UNIQUE KEY (title)
);
```

#### **2. `sis_campeonatos`**
```sql
CREATE TABLE sis_campeonatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255),
    title VARCHAR(255) NOT NULL,
    status INT DEFAULT 1,
    insert DATETIME,
    update DATETIME,
    UNIQUE KEY (title)
);
```

#### **3. `sis_jogos`**
```sql
CREATE TABLE sis_jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) UNIQUE,
    campeonato INT,
    datacadastro DATE,
    data DATE,
    hora TIME,
    timecasa INT,
    timefora INT,
    status INT DEFAULT 1,
    cotacoes TEXT, -- JSON das cotações
    refimport VARCHAR(255), -- ID externo da API
    limite1 DECIMAL(10,2),
    limite2 DECIMAL(10,2),
    limite3 DECIMAL(10,2),
    timecasaplacarprimeiro INT,
    timecasaplacarsegundo INT,
    timeforaplacarprimeiro INT,
    timeforaplacarsegundo INT,
    insert DATETIME,
    update DATETIME,
    UNIQUE KEY (token),
    KEY (refimport)
);
```

---

## 🔧 CONFIGURAÇÕES IMPORTANTES

### **1. Limite de Cotação**
📁 **Tabela:** `sis_dados`

```sql
-- Definir cotação máxima (ex: 999)
UPDATE sis_dados SET limitecotacao = 999 WHERE id = 1;

-- Sem limite
UPDATE sis_dados SET limitecotacao = 0 WHERE id = 1;
```

**Efeito:**
```php
// Se limitecotacao = 50
cotacao_api = 75.00  → salva: 50.00 (limitado)
cotacao_api = 30.00  → salva: 30.00 (mantém)
```

---

### **2. Limites de Aposta por Jogo**
```sql
-- Padrão na importação:
limite1 = 300   -- Limite para apostas simples
limite2 = 500   -- Limite para apostas múltiplas
limite3 = 1000  -- Limite máximo
```

---

## 📊 COTAÇÕES DISPONÍVEIS

### **Tipos de Cotações (campo 'cotacoes' JSON):**

| Campo | Descrição | Exemplo |
|-------|-----------|---------|
| `casa` | Vitória do time da casa | 2.45 |
| `empate` | Empate | 2.85 |
| `fora` | Vitória do time visitante | 2.48 |
| `amb` | Ambos marcam: Sim | 1.50 |
| `ambn` | Ambos marcam: Não | 2.10 |
| `gmais1` | Mais de 0.5 gols | 1.15 |
| `gmais2` | Mais de 1.5 gols | 1.55 |
| `gmais3` | Mais de 2.5 gols | 2.20 |
| `gmenos2` | Menos de 1.5 gols | 2.05 |
| `gmenos3` | Menos de 2.5 gols | 1.40 |
| `dplcasa` | Casa + Ambos marcam | 1.85 |
| `dplfora` | Fora + Ambos marcam | 2.05 |
| `casacasa` | Casa/Casa (Meio-tempo/Final) | 3.80 |
| `casaempate` | Casa/Empate | 4.50 |
| `casafora` | Casa/Fora | 8.50 |
| `pc1x0c` | Placar exato: 1x0 Casa | 7.50 |
| `pc2x1c` | Placar exato: 2x1 Casa | 9.00 |

**Total:** Mais de **150+ tipos de cotações** diferentes!

---

## 🚀 COMO USAR A API

### **Opção 1: Cron Automático (Recomendado)**
```bash
# Importar jogos a cada 30min
*/30 * * * * curl http://localhost:8000/cron/jogos

# Importar resultados a cada hora
0 * * * * curl http://localhost:8000/cron/jogos/resultados
```

### **Opção 2: Script Manual**
```bash
# Importar jogos
php jogos.php

# Atualizar jogos
php atualiza.php
```

### **Opção 3: Painel Admin**
```
1. Login: http://localhost:8000/admin-login.php
2. Menu: Importar → MarjoSports
3. Clicar em "Buscar Jogos"
4. Selecionar jogos desejados
5. Clicar em "Importar Selecionados"
```

---

## 📈 ESTATÍSTICAS DE IMPORTAÇÃO

### **Retorno Típico:**
```json
{
    "novos": 45,      // Jogos novos inseridos
    "antigos": 30,    // Jogos atualizados
    "erros": 2,       // Erros durante importação
    "message": "Jogos importados",
    "result": 1
}
```

---

## ⚠️ IMPORTANTE - ODS x APIJogos

### **NÃO é "ODS", é "apijogos.com"**

Seu cliente pode ter confundido com:
- ✅ **API Jogos** (apijogos.com) - URL CORRETA encontrada no código
- ❌ **ODS** - Não encontrado em nenhum arquivo

### **Possíveis Confusões:**
1. **ODD** (cotação em inglês) → ele pode ter dito "ODS" pensando em "ODDS"
2. **API de outro provedor** → Talvez usou ODS em sistema anterior
3. **Nome antigo** → A API pode ter mudado de nome

---

## 🔐 SEGURANÇA

### **Atenção:**
```php
$this->client = new Client([
    'base_url' => 'https://apijogos.com/betsports3.php',
    'verify' => false,  // ⚠️ SSL desabilitado
]);
```

**Recomendação:**
- ✅ Habilitar verificação SSL em produção
- ✅ Validar resposta da API antes de processar
- ✅ Adicionar tratamento de erros robusto

---

## 📞 RESUMO PARA O CLIENTE

✅ **SIM, os dados vêm de uma API externa:**
- **URL:** https://apijogos.com/betsports3.php
- **Tipo:** API REST JSON
- **Dados:** Jogos, times, campeonatos, cotações em tempo real
- **Atualização:** Automática via Cron ou manual pelo painel

❌ **NÃO é chamada "ODS":**
- O nome correto é **API Jogos** (apijogos.com)
- Talvez ele tenha confundido com "ODDS" (cotações)

🎯 **Como funciona:**
1. Sistema busca jogos da API a cada 30 minutos
2. Processa e salva no banco local
3. Frontend exibe os jogos salvos
4. Após jogos terminarem, busca resultados e processa apostas

---

**Desenvolvido por Henrique Sanches** 🚀  
*Documentação Completa da Integração com API de Jogos*

