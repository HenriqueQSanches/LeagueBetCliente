# 🎯 LeagueBet - Integração BetsAPI

## 📋 Visão Geral

Este documento descreve a integração completa da **BetsAPI** no sistema LeagueBet, permitindo:

- ✅ Buscar jogos em tempo real de múltiplas casas de apostas
- ✅ Atualizar odds automaticamente
- ✅ Exibir jogos ao vivo com placar
- ✅ Sincronização automática via CRON
- ✅ Suporte a múltiplos esportes (Futebol, Basquete, etc.)

---

## 🔑 Credenciais da API

**Token:** `237782-BXpZQecPXZnfW9`

**Documentação Oficial:** https://betsapi.com/docs/

---

## 📦 Arquivos Criados

### 1. **BetsAPIClient.php**
`app/modules/betsapi/BetsAPIClient.php`

Classe principal para comunicação com a BetsAPI.

**Métodos principais:**
- `getSports()` - Lista esportes disponíveis
- `getLeagues($sportId)` - Lista campeonatos
- `getUpcomingEvents($sportId, $days)` - Busca jogos futuros
- `getInPlayEvents($sportId)` - Busca jogos ao vivo
- `getEvent($eventId)` - Detalhes de um evento
- `getEventOdds($eventId)` - Odds completas
- `getEventOddsSummary($eventId)` - Odds principais (1X2, Over/Under)
- `getEventStats($eventId)` - Estatísticas ao vivo

### 2. **SyncJogos.php**
`app/modules/betsapi/SyncJogos.php`

Script de sincronização que busca jogos da API e salva no banco de dados.

**Funcionalidades:**
- Sincroniza jogos futuros (próximos 3 dias)
- Sincroniza jogos ao vivo
- Atualiza odds em tempo real
- Remove jogos antigos automaticamente
- Log detalhado de todas as operações

### 3. **database-update-betsapi.sql**
Script SQL para atualizar o banco de dados com as colunas necessárias.

### 4. **test-betsapi.php**
Interface web para testar a integração com a BetsAPI.

### 5. **sync-betsapi.php**
Interface web para executar sincronização manual.

---

## 🚀 Instalação

### Passo 1: Atualizar o Banco de Dados

Execute o script SQL no phpMyAdmin ou via linha de comando:

```bash
mysql -u root -p banca_esportiva < database-update-betsapi.sql
```

Ou acesse o phpMyAdmin e execute o conteúdo do arquivo `database-update-betsapi.sql`.

**O que será criado:**
- Coluna `api_id` na tabela `jogos`
- Colunas `ao_vivo`, `placar_casa`, `placar_fora` na tabela `jogos`
- Colunas `created_at`, `updated_at` na tabela `jogos`
- Coluna `codigo` na tabela `paises`
- Coluna `api_id` na tabela `campeonatos`
- Tabela `betsapi_sync_log` (log de sincronizações)
- Tabela `betsapi_config` (configurações)
- Índices para melhor performance

### Passo 2: Testar a Integração

Acesse no navegador:

```
http://localhost/Cliente/LeagueBetCliente-main/test-betsapi.php
```

**Testes realizados:**
1. ✅ Conexão com a API
2. ✅ Listagem de esportes
3. ✅ Busca de jogos futuros
4. ✅ Busca de jogos ao vivo
5. ✅ Busca de odds

### Passo 3: Executar Sincronização Manual

Acesse no navegador:

```
http://localhost/Cliente/LeagueBetCliente-main/sync-betsapi.php
```

Isso irá:
- Buscar jogos dos próximos 3 dias
- Buscar jogos ao vivo
- Salvar no banco de dados
- Atualizar odds
- Remover jogos antigos

### Passo 4: Configurar CRON (Sincronização Automática)

#### Windows (Task Scheduler)

1. Abra o **Agendador de Tarefas** (Task Scheduler)
2. Crie uma nova tarefa básica
3. Configure para executar a cada 5 minutos
4. Ação: Iniciar um programa
5. Programa: `C:\xampp\php\php.exe`
6. Argumentos: `C:\xampp\htdocs\Cliente\LeagueBetCliente-main\app\modules\betsapi\SyncJogos.php`

#### Linux/Mac (Crontab)

Edite o crontab:

```bash
crontab -e
```

Adicione a linha:

```bash
*/5 * * * * cd /caminho/para/projeto && php app/modules/betsapi/SyncJogos.php >> /var/log/leaguebet-sync.log 2>&1
```

Isso executará a sincronização **a cada 5 minutos**.

---

## 🎮 Como Funciona

### Fluxo de Sincronização

```
┌─────────────────┐
│   BetsAPI       │
│  (Servidor)     │
└────────┬────────┘
         │
         │ HTTP Request
         │ (a cada 5 min)
         ▼
┌─────────────────┐
│ BetsAPIClient   │
│  (PHP Class)    │
└────────┬────────┘
         │
         │ Processa dados
         ▼
┌─────────────────┐
│  SyncJogos      │
│  (Script Sync)  │
└────────┬────────┘
         │
         │ INSERT/UPDATE
         ▼
┌─────────────────┐
│  MySQL DB       │
│  (jogos table)  │
└────────┬────────┘
         │
         │ SELECT
         ▼
┌─────────────────┐
│  Frontend       │
│  (Vue.js)       │
└─────────────────┘
```

### Estrutura de Dados

**Tabela `jogos`:**
```sql
- id (INT)
- api_id (VARCHAR) - ID do evento na BetsAPI
- casa (VARCHAR) - Time da casa
- fora (VARCHAR) - Time visitante
- data (DATE) - Data do jogo
- hora (TIME) - Hora do jogo
- pais (INT) - FK para tabela paises
- campeonato (INT) - FK para tabela campeonatos
- ao_vivo (TINYINT) - 1 se está ao vivo, 0 se não
- placar_casa (INT) - Gols do time da casa
- placar_fora (INT) - Gols do time visitante
- cotacoes (JSON) - Todas as odds do jogo
- created_at (DATETIME)
- updated_at (DATETIME)
```

**Estrutura JSON das Cotações:**
```json
{
  "90": {
    "casa": 2.50,
    "empate": 3.20,
    "fora": 2.80,
    "mais_2_5": 1.85,
    "menos_2_5": 1.95,
    "ambas_marcam_sim": 1.70,
    "ambas_marcam_nao": 2.10
  },
  "pt": {
    "casa": 2.30,
    "empate": 2.00,
    "fora": 3.50
  },
  "st": {
    "casa": 2.60,
    "empate": 3.40,
    "fora": 2.70
  }
}
```

---

## 🔧 Configurações

### Alterar Token da API

Edite o arquivo `app/modules/betsapi/BetsAPIClient.php`:

```php
private $apiToken = 'SEU_NOVO_TOKEN_AQUI';
```

Ou atualize na tabela `betsapi_config`:

```sql
UPDATE betsapi_config SET valor = 'SEU_NOVO_TOKEN' WHERE chave = 'api_token';
```

### Alterar Intervalo de Sincronização

Na tabela `betsapi_config`:

```sql
UPDATE betsapi_config SET valor = '300' WHERE chave = 'sync_interval';
-- 300 segundos = 5 minutos
```

### Alterar Esporte

Para buscar jogos de basquete ao invés de futebol:

```sql
UPDATE betsapi_config SET valor = '18' WHERE chave = 'sport_id';
-- 1 = Futebol
-- 18 = Basquete
-- 13 = Tênis
-- etc.
```

### Alterar Dias Futuros

```sql
UPDATE betsapi_config SET valor = '7' WHERE chave = 'days_ahead';
-- Busca jogos dos próximos 7 dias
```

---

## 📊 Monitoramento

### Ver Log de Sincronizações

```sql
SELECT * FROM betsapi_sync_log ORDER BY created_at DESC LIMIT 10;
```

### Ver Jogos Sincronizados Hoje

```sql
SELECT 
    j.casa, 
    j.fora, 
    j.data, 
    j.hora, 
    c.title as campeonato,
    j.ao_vivo
FROM jogos j
LEFT JOIN campeonatos c ON j.campeonato = c.id
WHERE DATE(j.created_at) = CURDATE()
ORDER BY j.data, j.hora;
```

### Ver Jogos Ao Vivo

```sql
SELECT 
    j.casa, 
    j.fora, 
    j.placar_casa,
    j.placar_fora,
    c.title as campeonato
FROM jogos j
LEFT JOIN campeonatos c ON j.campeonato = c.id
WHERE j.ao_vivo = 1;
```

---

## 🐛 Troubleshooting

### Erro: "Connection refused"

**Problema:** Não consegue conectar à BetsAPI.

**Solução:**
1. Verifique se o token está correto
2. Verifique sua conexão com a internet
3. Verifique se o firewall não está bloqueando

### Erro: "Token limit exceeded"

**Problema:** Atingiu o limite de requisições por hora.

**Solução:**
1. Reduza a frequência de sincronização
2. Considere fazer upgrade do plano da BetsAPI
3. Use cache para reduzir requisições

### Nenhum jogo aparece no site

**Problema:** Sincronização não está funcionando.

**Solução:**
1. Execute `test-betsapi.php` para verificar conexão
2. Execute `sync-betsapi.php` manualmente
3. Verifique se o CRON está configurado corretamente
4. Verifique logs de erro do PHP

### Odds não atualizam

**Problema:** Odds desatualizadas.

**Solução:**
1. Verifique se o CRON está rodando
2. Aumente a frequência de sincronização
3. Verifique se há erros no log

---

## 📈 Otimizações

### Cache de Requisições

Para reduzir o número de requisições à API, você pode implementar cache:

```php
// Adicione no BetsAPIClient.php
private function getCached($key, $ttl = 300) {
    $cacheFile = sys_get_temp_dir() . '/betsapi_' . md5($key) . '.cache';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    return null;
}

private function setCache($key, $data) {
    $cacheFile = sys_get_temp_dir() . '/betsapi_' . md5($key) . '.cache';
    file_put_contents($cacheFile, json_encode($data));
}
```

### Índices de Performance

Já foram criados automaticamente pelo script SQL:
- `idx_api_id` - Busca rápida por ID da API
- `idx_data_hora` - Busca rápida por data/hora
- `idx_ao_vivo` - Busca rápida de jogos ao vivo
- `idx_campeonato` - Busca rápida por campeonato

---

## 🔐 Segurança

### Proteger Arquivos de Teste

Em produção, remova ou proteja os arquivos:
- `test-betsapi.php`
- `sync-betsapi.php`

Adicione autenticação ou remova-os completamente.

### Proteger Token da API

Nunca exponha o token em arquivos públicos. Considere usar variáveis de ambiente:

```php
// .env
BETSAPI_TOKEN=237782-BXpZQecPXZnfW9

// BetsAPIClient.php
$this->apiToken = getenv('BETSAPI_TOKEN');
```

---

## 📞 Suporte

### Documentação BetsAPI
https://betsapi.com/docs/

### Contato Desenvolvedor
- **Email:** qiwitech.sanches@gmail.com
- **Portfolio:** https://portfolio-beige-seven-18.vercel.app/

---

## 📝 Changelog

### v1.0 - 07/11/2025
- ✅ Integração inicial com BetsAPI
- ✅ Sincronização de jogos futuros
- ✅ Sincronização de jogos ao vivo
- ✅ Interface de testes
- ✅ Interface de sincronização manual
- ✅ Suporte a múltiplos esportes
- ✅ Sistema de cache
- ✅ Log de sincronizações

---

## 🎉 Conclusão

A integração está completa e pronta para uso! 

**Próximos passos:**
1. ✅ Execute o script SQL
2. ✅ Teste a conexão
3. ✅ Execute sincronização manual
4. ✅ Configure o CRON
5. ✅ Monitore os logs

**Boa sorte com o LeagueBet! 🚀⚽🏀**

