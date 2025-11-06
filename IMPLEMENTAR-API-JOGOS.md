# 🚀 GUIA COMPLETO - IMPLEMENTAR API DE JOGOS

## 📋 ÍNDICE
1. [Teste Manual (Primeiro Passo)](#1-teste-manual)
2. [Configuração Automática - Windows](#2-windows-task-scheduler)
3. [Configuração Automática - Linux/cPanel](#3-linux-cpanel-cron)
4. [Verificação e Monitoramento](#4-verificação)
5. [Troubleshooting](#5-troubleshooting)

---

## 🎯 O QUE VAMOS FAZER:

✅ **Importar jogos automaticamente** a cada 30 minutos  
✅ **Atualizar resultados** a cada hora  
✅ **Processar apostas** após definir placares  
✅ **Monitorar** o funcionamento  

---

# 1️⃣ TESTE MANUAL (PRIMEIRO PASSO)

Antes de automatizar, vamos testar se funciona! 🧪

## **Opção A: Via Navegador (Mais Fácil)** 🌐

### **Passo 1: Importar Jogos**
```
URL: http://localhost:8000/cron/jogos
```

**Como fazer:**
1. Abra o navegador
2. Acesse: `http://localhost:8000/cron/jogos`
3. Aguarde (pode demorar 30-60 segundos)
4. Você verá uma resposta JSON:

```json
{
    "novos": 45,
    "antigos": 30,
    "erros": 0,
    "message": "Jogos importados",
    "result": 1
}
```

✅ **Se ver isso, FUNCIONOU!** 🎉

---

### **Passo 2: Verificar no Site**
```
URL: http://localhost:8000
```

**O que verificar:**
- ✅ Jogos aparecendo na tela principal
- ✅ Cotações visíveis (Casa, Empate, Fora)
- ✅ Data e hora dos jogos
- ✅ Times e campeonatos corretos

---

### **Passo 3: Importar Resultados (Opcional)**
```
URL: http://localhost:8000/cron/jogos/resultados
```

**Quando usar:**
- Após jogos terem terminado
- Para processar apostas antigas
- Para testar o sistema de resultados

---

## **Opção B: Via Terminal (Alternativo)** 💻

### **Windows (PowerShell):**
```powershell
cd C:\xampp\htdocs\Cliente\LeagueBetCliente-main

# Importar jogos
C:\xampp\php\php.exe jogos.php

# OU via URL
curl http://localhost:8000/cron/jogos
```

### **Linux/Mac:**
```bash
cd /var/www/html/LeagueBetCliente-main

# Importar jogos
php jogos.php

# OU via URL
curl http://localhost:8000/cron/jogos
```

---

# 2️⃣ WINDOWS - TASK SCHEDULER (Agendador de Tarefas)

## 🪟 **Configuração Completa para Windows**

### **Tarefa 1: Importar Jogos (A cada 30 minutos)**

#### **Passo a Passo:**

1. **Abrir Agendador de Tarefas:**
   - Pressione `Win + R`
   - Digite: `taskschd.msc`
   - Pressione Enter

2. **Criar Nova Tarefa:**
   - Clique em **"Criar Tarefa Básica"** (no menu direito)
   - Nome: `LeagueBet - Importar Jogos`
   - Descrição: `Importa jogos da API a cada 30 minutos`
   - Clique em **Avançar**

3. **Configurar Disparador:**
   - Selecione: **"Diariamente"**
   - Clique em **Avançar**
   - Hora de início: `00:00` (meia-noite)
   - Repetir a cada: `1` dia
   - Clique em **Avançar**

4. **Configurar Ação:**
   - Selecione: **"Iniciar um programa"**
   - Clique em **Avançar**
   - Programa/script: `C:\xampp\php\php.exe`
   - Argumentos: `-f "C:\xampp\htdocs\Cliente\LeagueBetCliente-main\jogos.php"`
   - Clique em **Avançar**

5. **Configurar Repetição:**
   - Clique em **Concluir**
   - **IMPORTANTE:** Clique com botão direito na tarefa criada
   - Selecione **"Propriedades"**
   - Vá para a aba **"Gatilhos"**
   - Clique em **"Editar"**
   - Marque: **"Repetir a tarefa a cada:"**
   - Selecione: **30 minutos**
   - Por uma duração de: **Indefinidamente**
   - Clique em **OK**

6. **Configurações Avançadas:**
   - Aba **"Geral"**:
     - ☑️ Executar estando o usuário conectado ou não
     - ☑️ Executar com privilégios mais altos
   - Aba **"Condições"**:
     - ☐ Desmarque "Iniciar a tarefa apenas se o computador estiver conectado à energia CA"
   - Clique em **OK**

---

### **Tarefa 2: Importar Resultados (A cada 1 hora)**

**Repita os passos acima com estas alterações:**

- **Nome:** `LeagueBet - Importar Resultados`
- **Descrição:** `Importa resultados e processa apostas a cada hora`
- **Programa/script:** `curl.exe` (se disponível) ou `C:\xampp\php\php.exe`
- **Argumentos (Opção 1 - CURL):** `http://localhost:8000/cron/jogos/resultados`
- **Argumentos (Opção 2 - PHP):** Criar script `resultados.php` (veja abaixo)
- **Repetir a cada:** **1 hora**

---

### **📄 Criar Script `resultados.php` (Se não usar CURL)**

Criar arquivo na raiz: `resultados.php`

```php
<?php
// Importar resultados via script
$url = "http://localhost:8000/cron/jogos/resultados";

// Opção 1: file_get_contents
$response = file_get_contents($url);
echo $response;

// Opção 2: CURL (se disponível)
/*
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
*/
?>
```

**Usar no Task Scheduler:**
- Programa: `C:\xampp\php\php.exe`
- Argumentos: `-f "C:\xampp\htdocs\Cliente\LeagueBetCliente-main\resultados.php"`

---

### **✅ Testar Tarefa Criada:**

1. No Agendador de Tarefas
2. Localize a tarefa: `LeagueBet - Importar Jogos`
3. Clique com botão direito
4. Selecione **"Executar"**
5. Aguarde alguns segundos
6. Verifique o site: `http://localhost:8000`

---

# 3️⃣ LINUX / cPANEL - CRON JOBS

## 🐧 **Configuração Completa para Linux/cPanel**

### **Opção A: Via cPanel (Hospedagem)**

#### **Passo 1: Acessar Cron Jobs**
1. Login no cPanel
2. Procure: **"Cron Jobs"** ou **"Tarefas Cron"**
3. Clique para acessar

#### **Passo 2: Adicionar Cron - Importar Jogos**

**Configuração:**
```
Minuto:    */30  (A cada 30 minutos)
Hora:      *     (Toda hora)
Dia:       *     (Todo dia)
Mês:       *     (Todo mês)
Dia Semana: *    (Todos dias da semana)

Comando:
curl -s https://seudominio.com/cron/jogos
```

**OU com caminho absoluto:**
```
/usr/bin/php /home/usuario/public_html/jogos.php
```

#### **Passo 3: Adicionar Cron - Importar Resultados**

**Configuração:**
```
Minuto:    0     (No minuto 0)
Hora:      *     (A cada hora)
Dia:       *     (Todo dia)
Mês:       *     (Todo mês)
Dia Semana: *    (Todos dias da semana)

Comando:
curl -s https://seudominio.com/cron/jogos/resultados
```

**OU:**
```
/usr/bin/php /home/usuario/public_html/resultados.php
```

---

### **Opção B: Via SSH (Servidor Linux)**

#### **Passo 1: Editar Crontab**
```bash
crontab -e
```

#### **Passo 2: Adicionar Linhas**
```bash
# Importar jogos a cada 30 minutos
*/30 * * * * curl -s http://localhost:8000/cron/jogos >> /var/log/leaguebet-jogos.log 2>&1

# Importar resultados a cada hora
0 * * * * curl -s http://localhost:8000/cron/jogos/resultados >> /var/log/leaguebet-resultados.log 2>&1
```

**OU com PHP direto:**
```bash
# Importar jogos a cada 30 minutos
*/30 * * * * /usr/bin/php /var/www/html/LeagueBetCliente-main/jogos.php >> /var/log/leaguebet-jogos.log 2>&1

# Importar resultados a cada hora
0 * * * * /usr/bin/php /var/www/html/LeagueBetCliente-main/resultados.php >> /var/log/leaguebet-resultados.log 2>&1
```

#### **Passo 3: Salvar e Sair**
- Pressione `Ctrl + O` (salvar)
- Pressione `Enter`
- Pressione `Ctrl + X` (sair)

#### **Passo 4: Verificar Crontab**
```bash
crontab -l
```

---

### **📊 Entendendo a Sintaxe do Cron:**

```
* * * * * comando
│ │ │ │ │
│ │ │ │ └─── Dia da semana (0-7, 0=Domingo)
│ │ │ └───── Mês (1-12)
│ │ └─────── Dia do mês (1-31)
│ └───────── Hora (0-23)
└─────────── Minuto (0-59)
```

**Exemplos Práticos:**
```bash
*/30 * * * *    # A cada 30 minutos
0 * * * *       # A cada hora (no minuto 0)
0 0 * * *       # Todo dia à meia-noite
0 */6 * * *     # A cada 6 horas
0 0 * * 0       # Todo domingo à meia-noite
*/5 * * * *     # A cada 5 minutos
```

---

# 4️⃣ VERIFICAÇÃO E MONITORAMENTO

## 🔍 **Como Verificar se Está Funcionando:**

### **Método 1: Verificar Logs**

#### **Windows:**
```powershell
# Logs do PHP (XAMPP)
Get-Content C:\xampp\apache\logs\error.log -Tail 50

# Ou abra o arquivo:
C:\xampp\apache\logs\error.log
```

**Procure por:**
```
Cron: 127.0.0.1 Jogos importados
Cron: 127.0.0.1 45/80 jogos foram definidos os placares
```

#### **Linux:**
```bash
# Logs personalizados
tail -f /var/log/leaguebet-jogos.log
tail -f /var/log/leaguebet-resultados.log

# Logs do Cron
tail -f /var/log/cron
grep CRON /var/log/syslog
```

---

### **Método 2: Verificar no Banco de Dados**

```sql
-- Ver jogos importados hoje
SELECT COUNT(*) as total 
FROM sis_jogos 
WHERE DATE(insert) = CURDATE();

-- Ver últimos jogos importados
SELECT 
    j.id,
    j.data,
    j.hora,
    tc.title as time_casa,
    tf.title as time_fora,
    c.title as campeonato,
    j.insert as importado_em
FROM sis_jogos j
LEFT JOIN sis_times tc ON j.timecasa = tc.id
LEFT JOIN sis_times tf ON j.timefora = tf.id
LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
ORDER BY j.insert DESC
LIMIT 10;

-- Ver frequência de importação
SELECT 
    DATE(insert) as data,
    HOUR(insert) as hora,
    COUNT(*) as total_importacoes
FROM sis_jogos
WHERE DATE(insert) = CURDATE()
GROUP BY DATE(insert), HOUR(insert)
ORDER BY hora;
```

---

### **Método 3: Verificar no Site**

**Checklist:**
- ✅ Acessar: `http://localhost:8000`
- ✅ Verificar se há jogos listados
- ✅ Confirmar que as cotações aparecem
- ✅ Verificar data/hora dos jogos
- ✅ Tentar fazer uma aposta teste

---

### **Método 4: Criar Página de Status**

Criar arquivo: `status-api.php` na raiz

```php
<?php
include('conexao.php');

echo "<h1>📊 Status da API - LeagueBet</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
</style>";

// Total de jogos
$total_jogos = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
echo "<div class='box'>";
echo "<h3>🎮 Total de Jogos: <span class='info'>$total_jogos</span></h3>";
echo "</div>";

// Jogos importados hoje
$hoje = $conexao->query("SELECT COUNT(*) FROM sis_jogos WHERE DATE(insert) = CURDATE()")->fetchColumn();
echo "<div class='box'>";
echo "<h3>📅 Importados Hoje: <span class='success'>$hoje</span></h3>";
echo "</div>";

// Jogos disponíveis (futuros)
$disponiveis = $conexao->query("
    SELECT COUNT(*) FROM sis_jogos 
    WHERE status = 1 
    AND (data > CURDATE() OR (data = CURDATE() AND hora > CURTIME()))
")->fetchColumn();
echo "<div class='box'>";
echo "<h3>✅ Disponíveis para Apostar: <span class='success'>$disponiveis</span></h3>";
echo "</div>";

// Última importação
$ultima = $conexao->query("SELECT MAX(insert) FROM sis_jogos")->fetchColumn();
echo "<div class='box'>";
echo "<h3>⏰ Última Importação: <span class='info'>$ultima</span></h3>";
echo "</div>";

// Total de times
$times = $conexao->query("SELECT COUNT(*) FROM sis_times")->fetchColumn();
echo "<div class='box'>";
echo "<h3>👥 Total de Times: <span class='info'>$times</span></h3>";
echo "</div>";

// Total de campeonatos
$campeonatos = $conexao->query("SELECT COUNT(*) FROM sis_campeonatos")->fetchColumn();
echo "<div class='box'>";
echo "<h3>🏆 Total de Campeonatos: <span class='info'>$campeonatos</span></h3>";
echo "</div>";

// Testar API (último teste)
echo "<div class='box'>";
echo "<h3>🔗 Testar Importação:</h3>";
echo "<a href='http://localhost:8000/cron/jogos' target='_blank' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>🎮 Importar Jogos</a>";
echo "<a href='http://localhost:8000/cron/jogos/resultados' target='_blank' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;'>📊 Importar Resultados</a>";
echo "</div>";

echo "<div class='box'>";
echo "<h3>📈 Últimos 5 Jogos Importados:</h3>";
$ultimos = $conexao->query("
    SELECT 
        j.data, j.hora,
        tc.title as casa,
        tf.title as fora,
        j.insert
    FROM sis_jogos j
    LEFT JOIN sis_times tc ON j.timecasa = tc.id
    LEFT JOIN sis_times tf ON j.timefora = tf.id
    ORDER BY j.insert DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>Data</th><th>Hora</th><th>Jogo</th><th>Importado em</th></tr>";
foreach ($ultimos as $jogo) {
    echo "<tr>";
    echo "<td>{$jogo['data']}</td>";
    echo "<td>{$jogo['hora']}</td>";
    echo "<td>{$jogo['casa']} x {$jogo['fora']}</td>";
    echo "<td>{$jogo['insert']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";
?>
```

**Acessar:**
```
http://localhost:8000/status-api.php
```

---

# 5️⃣ TROUBLESHOOTING (Solução de Problemas)

## ❌ **Problema 1: Nenhum jogo é importado**

**Sintomas:**
- URL retorna `{"novos": 0, "antigos": 0, "erros": 0}`
- Site não exibe jogos

**Soluções:**

### **A) Verificar conexão com API:**
```php
// Criar arquivo: teste-api.php
<?php
$url = "https://apijogos.com/betsports3.php/jogos";
$response = file_get_contents($url);
$dados = json_decode($response, true);

echo "<pre>";
print_r($dados);
echo "</pre>";
?>
```

**Acessar:** `http://localhost:8000/teste-api.php`

### **B) Verificar extensões PHP:**
```bash
# Verificar se CURL está habilitado
php -m | grep curl

# Verificar se JSON está habilitado
php -m | grep json
```

**Habilitar no php.ini:**
```ini
extension=curl
extension=json
extension=openssl
```

### **C) Verificar firewall:**
```powershell
# Windows: Permitir conexões de saída na porta 443
New-NetFirewallRule -DisplayName "LeagueBet API" -Direction Outbound -Protocol TCP -RemotePort 443 -Action Allow
```

---

## ❌ **Problema 2: Erro de permissão/timeout**

**Sintomas:**
- Erro: `Maximum execution time exceeded`
- Erro: `Allowed memory size exhausted`

**Soluções:**

### **Editar `php.ini`:**
```ini
max_execution_time = 300
memory_limit = 1024M
```

### **Reiniciar Apache:**
```bash
# Windows (XAMPP)
# Painel XAMPP → Stop Apache → Start Apache

# Linux
sudo service apache2 restart
```

---

## ❌ **Problema 3: Cron não executa (Windows)**

**Soluções:**

### **A) Verificar se a tarefa está ativa:**
1. Agendador de Tarefas
2. Biblioteca do Agendador de Tarefas
3. Localizar `LeagueBet - Importar Jogos`
4. Coluna "Status" deve estar: **"Pronto"**

### **B) Verificar últimas execuções:**
1. Clique com botão direito na tarefa
2. Propriedades → Aba "Histórico"
3. Verificar se há erros

### **C) Testar manualmente:**
1. Botão direito na tarefa
2. **"Executar"**
3. Verificar resultado

---

## ❌ **Problema 4: Cron não executa (Linux)**

**Soluções:**

### **A) Verificar se Cron está rodando:**
```bash
sudo service cron status
# ou
sudo systemctl status cron
```

### **B) Verificar logs:**
```bash
grep CRON /var/log/syslog | tail -20
```

### **C) Testar comando manualmente:**
```bash
curl -s http://localhost:8000/cron/jogos
```

---

## ✅ **CHECKLIST FINAL**

Antes de considerar implementado, verifique:

- [ ] ✅ Teste manual funcionou (via navegador)
- [ ] ✅ Jogos aparecem no site após importar
- [ ] ✅ Cron/Task Scheduler configurado
- [ ] ✅ Tarefa executa automaticamente
- [ ] ✅ Logs mostram importações bem-sucedidas
- [ ] ✅ Banco de dados recebe novos jogos
- [ ] ✅ Site exibe jogos atualizados
- [ ] ✅ Cotações aparecem corretamente
- [ ] ✅ Apostas podem ser feitas
- [ ] ✅ Resultados são importados e processados

---

## 🎉 RESUMO RÁPIDO

### **Windows (Task Scheduler):**
1. ✅ Criar tarefa: `LeagueBet - Importar Jogos`
2. ✅ Programa: `C:\xampp\php\php.exe`
3. ✅ Argumentos: `-f "C:\xampp\htdocs\Cliente\LeagueBetCliente-main\jogos.php"`
4. ✅ Repetir: A cada 30 minutos

### **Linux/cPanel (Cron):**
```bash
*/30 * * * * curl -s http://localhost:8000/cron/jogos
0 * * * * curl -s http://localhost:8000/cron/jogos/resultados
```

### **Verificar:**
```
Status: http://localhost:8000/status-api.php
Site:   http://localhost:8000
```

---

**Pronto! A API de jogos está implementada e funcionando automaticamente!** 🚀🎉

**Desenvolvido por Henrique Sanches**  
*Guia Completo de Implementação - LeagueBet*

