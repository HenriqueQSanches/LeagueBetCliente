# 🚀 PRIMEIRA IMPORTAÇÃO - GUIA RÁPIDO

## ⚠️ IMPORTANTE: LEIA ISTO PRIMEIRO!

**Os jogos NÃO aparecem automaticamente ao iniciar o site pela primeira vez!**

Você precisa fazer a **PRIMEIRA IMPORTAÇÃO MANUAL** e depois configurar a automação.

---

## 📋 PASSO A PASSO (5 MINUTOS)

### **PASSO 1: Verificar se o Servidor Está Rodando** ✅

**XAMPP (Windows):**
1. Abra o **Painel XAMPP**
2. Verifique se **Apache** e **MySQL** estão com luz verde
3. Se não estiverem, clique em **"Start"** em cada um

**Ou via terminal:**
```bash
# Verificar se está rodando
curl http://localhost:8000
```

---

### **PASSO 2: Fazer a Primeira Importação** 🎮

**Escolha UMA das opções abaixo:**

#### **OPÇÃO A: Via Navegador (RECOMENDADO)** 🌐

**1. Abra o navegador e acesse:**
```
http://localhost:8000/cron/jogos
```

**2. Aguarde 30-60 segundos**

Você verá algo assim:
```json
{
    "novos": 78,
    "antigos": 0,
    "erros": 0,
    "message": "Jogos importados",
    "result": 1
}
```

✅ **Se aparecer "novos" > 0, FUNCIONOU!**

---

#### **OPÇÃO B: Via Terminal (Windows)** 💻

**1. Abra PowerShell ou CMD:**
```powershell
# Navegar até a pasta do projeto
cd C:\xampp\htdocs\Cliente\LeagueBetCliente-main

# Executar importação
C:\xampp\php\php.exe jogos.php
```

**2. Aguarde a mensagem:**
```
✅ Jogos importados com sucesso!
Novos: 78
Antigos: 0
```

---

#### **OPÇÃO C: Via Terminal (Linux/Mac)** 🐧

```bash
# Navegar até a pasta
cd /var/www/html/LeagueBetCliente-main

# Executar importação
php jogos.php
```

---

### **PASSO 3: Verificar no Site** 🌐

**1. Acesse o site:**
```
http://localhost:8000
```

**2. Você deve ver:**
- ✅ Jogos listados por campeonato
- ✅ Times com escudos
- ✅ Cotações (Casa, Empate, Fora)
- ✅ Data e hora dos jogos

**Se NÃO aparecer nada:**
- ⚠️ A importação pode ter falhado
- ⚠️ Veja a seção "Troubleshooting" abaixo

---

### **PASSO 4: Verificar Status** 📊

**Acesse o dashboard de status:**
```
http://localhost:8000/status-api.php
```

**Você deve ver:**
- ✅ Total de jogos: **XX jogos**
- ✅ Jogos importados hoje: **XX**
- ✅ Jogos disponíveis: **XX**
- ✅ Total de times: **XXX**
- ✅ Total de campeonatos: **XX**

---

### **PASSO 5: Configurar Automação** ⚙️

**Agora que funcionou, configure para atualizar automaticamente!**

Siga o guia completo:
```
Abra o arquivo: IMPLEMENTAR-API-JOGOS.md
Seção: "2️⃣ WINDOWS - TASK SCHEDULER" ou "3️⃣ LINUX/cPANEL"
```

**Resumo rápido:**
- ✅ **Windows:** Task Scheduler (a cada 30 minutos)
- ✅ **Linux:** Cron Job (*/30 * * * *)

---

## 🆘 TROUBLESHOOTING

### **Problema 1: "novos": 0, "erros": 0**

**Possíveis causas:**
- API fora do ar
- Conexão de internet bloqueada
- Firewall bloqueando

**Soluções:**

1. **Testar conexão com a API:**
```php
// Criar arquivo: teste-api.php
<?php
$url = "https://apijogos.com/betsports3.php/jogos";
$response = @file_get_contents($url);

if ($response) {
    echo "✅ API respondendo!\n";
    $data = json_decode($response, true);
    echo "Total de jogos disponíveis: " . count($data['jogos']) . "\n";
} else {
    echo "❌ API não respondeu!\n";
    echo "Verifique conexão de internet e firewall.\n";
}
?>
```

**Executar:**
```bash
php teste-api.php
```

2. **Verificar extensões PHP:**
```bash
# Verificar se CURL está ativo
php -m | grep curl

# Verificar se OpenSSL está ativo
php -m | grep openssl
```

**Se não aparecer, edite `php.ini`:**
```ini
extension=curl
extension=openssl
```

**Reinicie Apache!**

---

### **Problema 2: Nenhum jogo aparece no site**

**Verificar no banco de dados:**

1. **Acesse phpMyAdmin:**
```
http://localhost/phpmyadmin
```

2. **Selecione banco:** `banca_esportiva`

3. **Execute SQL:**
```sql
-- Ver total de jogos
SELECT COUNT(*) as total FROM sis_jogos;

-- Ver últimos jogos importados
SELECT * FROM sis_jogos ORDER BY insert DESC LIMIT 10;
```

**Se retornar 0:**
- ❌ Importação não funcionou
- Volte ao Passo 2

**Se retornar > 0 mas site não mostra:**
- ⚠️ Problema no frontend
- Limpe cache do navegador (Ctrl+F5)
- Verifique console do navegador (F12)

---

### **Problema 3: Erro de timeout**

**Sintoma:**
```
Maximum execution time of 30 seconds exceeded
```

**Solução:**

Edite `php.ini`:
```ini
max_execution_time = 300
memory_limit = 1024M
```

**Reinicie Apache!**

---

### **Problema 4: Erro de permissão (Linux)**

**Sintoma:**
```
Permission denied
```

**Solução:**
```bash
# Dar permissão para executar
chmod +x jogos.php

# Dar permissão de escrita (logs)
chmod 777 logs/
```

---

## ✅ CHECKLIST COMPLETO

Marque cada item após completar:

- [ ] 1. Apache e MySQL rodando
- [ ] 2. Acessei `http://localhost:8000/cron/jogos`
- [ ] 3. Vi resposta JSON com "novos" > 0
- [ ] 4. Acessei `http://localhost:8000`
- [ ] 5. Jogos aparecem na tela principal
- [ ] 6. Cotações estão visíveis
- [ ] 7. Acessei `http://localhost:8000/status-api.php`
- [ ] 8. Estatísticas corretas no dashboard
- [ ] 9. Configurei Task Scheduler ou Cron
- [ ] 10. Testei execução automática

---

## 🎯 RESUMO VISUAL

```
┌─────────────────────────────────────────┐
│ ANTES DA PRIMEIRA IMPORTAÇÃO           │
├─────────────────────────────────────────┤
│ Site: http://localhost:8000             │
│ Resultado: ❌ SEM JOGOS                 │
└─────────────────────────────────────────┘
                    │
                    ↓
┌─────────────────────────────────────────┐
│ EXECUTAR PRIMEIRA IMPORTAÇÃO            │
├─────────────────────────────────────────┤
│ URL: /cron/jogos                        │
│ OU: php jogos.php                       │
│ Aguardar: 30-60 segundos                │
└─────────────────────────────────────────┘
                    │
                    ↓
┌─────────────────────────────────────────┐
│ DEPOIS DA PRIMEIRA IMPORTAÇÃO           │
├─────────────────────────────────────────┤
│ Site: http://localhost:8000             │
│ Resultado: ✅ JOGOS APARECEM!           │
│ Status: 78 jogos importados             │
└─────────────────────────────────────────┘
                    │
                    ↓
┌─────────────────────────────────────────┐
│ CONFIGURAR AUTOMAÇÃO                    │
├─────────────────────────────────────────┤
│ Task Scheduler: A cada 30 minutos       │
│ Resultado: ✅ SEMPRE ATUALIZADO!        │
└─────────────────────────────────────────┘
```

---

## 🚀 COMANDOS RÁPIDOS

**Importar Jogos:**
```bash
# Via URL
curl http://localhost:8000/cron/jogos

# Via PHP
php jogos.php
```

**Ver Status:**
```bash
# Abrir navegador
start http://localhost:8000/status-api.php  # Windows
open http://localhost:8000/status-api.php   # Mac
xdg-open http://localhost:8000/status-api.php  # Linux
```

**Ver Jogos:**
```bash
curl http://localhost:8000
```

---

## 📞 PRECISA DE AJUDA?

Se ainda não funcionar:

1. **Verifique logs do Apache:**
   - Windows: `C:\xampp\apache\logs\error.log`
   - Linux: `/var/log/apache2/error.log`

2. **Execute com debug:**
```bash
php -d display_errors=1 jogos.php
```

3. **Teste conexão:**
```bash
curl -I https://apijogos.com/betsports3.php
```

---

## 🎉 RESULTADO ESPERADO

**Após seguir este guia, você terá:**

✅ Jogos importados no banco de dados  
✅ Site exibindo jogos e cotações  
✅ Dashboard de status funcionando  
✅ Sistema pronto para automatizar  

**Tempo total:** 5-10 minutos

---

**Desenvolvido por Henrique Sanches** 🚀  
*Guia de Primeira Importação - LeagueBet*

