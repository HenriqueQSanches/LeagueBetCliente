# 🗄️ GUIA DE IMPORTAÇÃO DO BANCO DE DADOS

## 📋 Informações do Banco de Dados

**Arquivo SQL:** `reidoscript bancas.sql`  
**Nome do Banco Original:** `reiscrip_bancas`  
**Tamanho:** 38.220 linhas  
**Usuário Admin Padrão:** `admin` / Senha: `123456`

---

## 🚀 IMPORTAÇÃO RÁPIDA

### Opção 1: Via phpMyAdmin (Recomendado para Iniciantes)

#### Passo 1: Criar o Banco de Dados
1. Acesse o **phpMyAdmin** (geralmente em: `http://localhost/phpmyadmin`)
2. Clique em **"Novo"** ou **"New"** no menu lateral
3. Digite o nome do banco: `banca_esportiva` (ou o nome que preferir)
4. Escolha **utf8mb4_unicode_ci** como collation
5. Clique em **"Criar"**

#### Passo 2: Importar o Arquivo SQL
1. Selecione o banco criado no menu lateral
2. Clique na aba **"Importar"** (Import)
3. Clique em **"Escolher arquivo"** (Choose file)
4. Selecione o arquivo: `reidoscript bancas.sql`
5. Role até o final e clique em **"Executar"** (Go)
6. Aguarde a importação (pode levar alguns minutos)

**✅ Pronto! Banco importado com sucesso!**

---

### Opção 2: Via Terminal/CMD (Mais Rápido)

#### Windows (CMD/PowerShell):
```cmd
cd "caminho\para\xampp\mysql\bin"
mysql -u root -p -e "CREATE DATABASE banca_esportiva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p banca_esportiva < "C:\caminho\para\reidoscript bancas.sql"
```

#### Linux/Mac:
```bash
mysql -u root -p -e "CREATE DATABASE banca_esportiva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p banca_esportiva < "/caminho/para/reidoscript bancas.sql"
```

**Observação:** Digite sua senha do MySQL quando solicitado.

---

### Opção 3: Via cPanel (Hospedagem)

#### Passo 1: Criar o Banco
1. Acesse o **cPanel** da sua hospedagem
2. Procure por **"MySQL Databases"** ou **"Bancos de Dados MySQL"**
3. Crie um novo banco: `SEU_USER_banca_esportiva`
4. Anote o nome completo do banco criado

#### Passo 2: Criar Usuário do Banco
1. Na mesma página, crie um novo usuário
2. Defina uma senha forte
3. Anote usuário e senha

#### Passo 3: Associar Usuário ao Banco
1. Na seção **"Add User to Database"**
2. Selecione o usuário e o banco
3. Marque **"ALL PRIVILEGES"** (Todos os privilégios)
4. Clique em **"Make Changes"**

#### Passo 4: Importar via phpMyAdmin
1. Volte ao cPanel e abra o **phpMyAdmin**
2. Selecione seu banco de dados
3. Clique em **"Importar"**
4. **⚠️ IMPORTANTE:** Se o arquivo for muito grande (> 50MB):
   - Você pode precisar compactar em **.zip** ou **.gz**
   - Ou usar o **MySQL Databases** > **Import** do cPanel diretamente
5. Selecione o arquivo `reidoscript bancas.sql`
6. Clique em **"Executar"**

---

## 🔍 ESTRUTURA DO BANCO

O banco contém **43 tabelas principais:**

### Tabelas Importantes:
- `sis_users` - Usuários do sistema (admin, cambistas, gerentes)
- `sis_apostas` - Apostas realizadas
- `sis_jogos` - Jogos esportivos
- `sis_campeonatos` - Campeonatos/Ligas
- `sis_times` - Times/Equipes
- `sis_bancos` - Dados bancários
- `sis_depositos` - Depósitos de clientes
- `sis_saques` - Solicitações de saque
- `sis_clientes` - Clientes cadastrados
- `sis_pagamentos` - Controle de pagamentos
- `sis_financeiro_*` - Sistema financeiro
- `sis_historico` - Log de atividades

---

## 🔐 USUÁRIO ADMINISTRADOR

Após importar o banco, você pode fazer login com:

**URL:** `http://seudominio.com/admin/`

**Credenciais:**
- **Usuário:** `admin`
- **Senha:** `123456`
- **Email:** `contato@reidoscript.com`
- **Nome:** `Rei do Script`

**⚠️ ALTERE ESSES DADOS IMEDIATAMENTE APÓS O PRIMEIRO LOGIN!**

---

## ⚙️ CONFIGURAR ARQUIVOS APÓS IMPORTAÇÃO

Após importar o banco, você precisa configurar os arquivos:

### 1️⃣ Arquivo: `conexao.php` (linha 4)

```php
$conexao = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'seu_usuario', 'sua_senha');
```

**Exemplos:**

**XAMPP/WAMP (Local):**
```php
$conexao = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', '');
```

**Hospedagem (cPanel):**
```php
$conexao = new PDO('mysql:host=localhost;dbname=usuario_banca', 'usuario_banco', 'senha_forte_123');
```

---

### 2️⃣ Arquivo: `inc.config.php`

**Linhas 58 a 71** - Configure o banco:

```php
$config['db'] = [
    'production' => [
        'host' => 'localhost',
        'username' => 'seu_usuario',
        'password' => 'sua_senha',
        'database' => 'banca_esportiva',
    ],
    'localhost' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'banca_esportiva',
    ]
];
```

**Linha 81 e 90** - Altere o nome do banco:

```php
$config['db']['production']['database'] = 'banca_esportiva';
```

---

## ✅ VERIFICAR SE A IMPORTAÇÃO FOI BEM SUCEDIDA

### Via phpMyAdmin:
1. Abra o phpMyAdmin
2. Selecione o banco `banca_esportiva`
3. Você deve ver **43 tabelas** no menu lateral
4. Clique na tabela `sis_users`
5. Você deve ver pelo menos 1 usuário (admin)

### Via SQL:
```sql
USE banca_esportiva;
SHOW TABLES;
SELECT * FROM sis_users WHERE login = 'admin';
```

Se você ver o usuário admin, está tudo certo! ✅

---

## 🆘 PROBLEMAS COMUNS

### ❌ Erro: "MySQL server has gone away"
**Solução:** Aumente o `max_allowed_packet` no MySQL
```
max_allowed_packet=512M
```

### ❌ Erro: "Unknown database"
**Solução:** Crie o banco de dados primeiro antes de importar

### ❌ Erro: "Access denied"
**Solução:** Verifique usuário e senha do MySQL

### ❌ Erro: "Timeout"
**Solução:** Arquivo muito grande, use importação via terminal

### ❌ Arquivo muito grande para upload
**Solução:** 
1. Compacte em .zip ou .gz
2. Ou aumente `upload_max_filesize` e `post_max_size` no php.ini
3. Ou use importação via terminal/SSH

---

## 📊 BACKUP DO BANCO

**⚠️ IMPORTANTE:** Sempre faça backup antes de qualquer alteração!

### Exportar Backup:
```bash
mysqldump -u root -p banca_esportiva > backup_$(date +%Y%m%d).sql
```

### Restaurar Backup:
```bash
mysql -u root -p banca_esportiva < backup_20250101.sql
```

---

## 🔄 PRÓXIMOS PASSOS

Após importar o banco com sucesso:

1. ✅ Configurar `conexao.php`
2. ✅ Configurar `inc.config.php`
3. ✅ Testar acesso ao site
4. ✅ Fazer login no admin
5. ✅ Alterar senha padrão
6. ✅ Alterar email e dados do admin
7. ✅ Configurar CRON jobs
8. ✅ Testar funcionalidades

---

## 📞 CHECKLIST DE IMPORTAÇÃO

- [ ] Banco de dados criado
- [ ] Arquivo SQL importado sem erros
- [ ] 43 tabelas criadas
- [ ] Usuário admin existe na tabela sis_users
- [ ] conexao.php configurado
- [ ] inc.config.php configurado
- [ ] Site acessível no navegador
- [ ] Login no admin funcionando
- [ ] Senha alterada

---

**🎉 Banco de dados importado e configurado com sucesso!**

Consulte `CONFIGURACAO.md` para o restante da configuração do sistema.

