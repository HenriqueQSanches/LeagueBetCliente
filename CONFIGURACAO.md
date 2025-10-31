# 📋 GUIA DE CONFIGURAÇÃO - BANCA ESPORTIVA

## 🔧 Passo 1: Configurar Banco de Dados

### Arquivo: `conexao.php` (Linha 4)

Altere os dados de conexão do banco:

```php
$conexao = new PDO('mysql:host=localhost;dbname=SEU_BANCO', 'SEU_USUARIO', 'SUA_SENHA');
```

**Exemplo:**
```php
$conexao = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', 'minhasenha123');
```

---

## 🔧 Passo 2: Configurar `inc.config.php`

### 📍 LINHAS 27 a 31 - Configurações Gerais

```php
'title' => 'SEU_TITULO',              // Nome do seu site
'dominio' => 'https://SEUDOMINIO.com', // Seu domínio
'email' => 'SEU_EMAIL@DOMINIO.com',    // Email de contato
'uri' => 'https://SEUDOMINIO.com',     // URL do site
'api' => 'https://apijogos.com/betsports2.php', // API dos jogos (manter ou trocar)
```

**Exemplo:**
```php
'title' => 'Minha Banca Esportiva',
'dominio' => 'https://minhabanca.com.br',
'email' => 'contato@minhabanca.com.br',
'uri' => 'https://minhabanca.com.br',
'api' => 'https://apijogos.com/betsports2.php',
```

---

### 📍 LINHAS 58 a 71 - Banco de Dados (Production e Localhost)

**Para PRODUÇÃO (servidor):**
```php
'production' => [
    'host' => 'localhost',           // Host do banco (geralmente localhost)
    'username' => 'SEU_USUARIO',     // Usuário do banco
    'password' => 'SUA_SENHA',       // Senha do banco
    'database' => 'SEU_BANCO',       // Nome do banco
],
```

**Para DESENVOLVIMENTO LOCAL (seu computador):**
```php
'localhost' => [
    'host' => 'localhost',
    'username' => 'root',            // Geralmente 'root' no XAMPP/WAMP
    'password' => '',                // Geralmente vazio no XAMPP/WAMP
    'database' => 'banca_esportiva', // Nome do seu banco local
]
```

---

### 📍 LINHAS 80 e 81 - Configuração por Domínio (Case Principal)

```php
case 'SEUDOMINIO.com':
    $config['config']['uri'] = 'https://SEUDOMINIO.com';
    $config['db']['production']['database'] = 'SEU_BANCO';
```

**Exemplo:**
```php
case 'minhabanca.com.br':
    $config['config']['uri'] = 'https://minhabanca.com.br';
    $config['db']['production']['database'] = 'banca_esportiva';
```

---

### 📍 LINHAS 89 e 90 - Configuração Default (Caso não encontre o domínio)

```php
default:
    $config['config']['uri'] = 'https://SEUDOMINIO.com';
    $config['db']['production']['database'] = 'SEU_BANCO';
```

---

### 📍 LINHA 99 - Escolher Layout do Site 🎨

Esta linha define qual layout visual será usado:

```php
$config['modules']['site'] = $config['modules']['site1'];
```

**Opções disponíveis:**
- `site1` = Layout 1 (arquivo CSS: css/site.css)
- `site2` = Layout 2 (arquivo CSS: css/site2.css)
- `site3` = Layout 3 (arquivo CSS: css/site3.css)

**Para trocar o layout, altere para:**
```php
// Layout 1
$config['modules']['site'] = $config['modules']['site1'];

// Layout 2
$config['modules']['site'] = $config['modules']['site2'];

// Layout 3
$config['modules']['site'] = $config['modules']['site3'];
```

---

## ⏰ Passo 3: Configurar CRON JOBS (no cPanel)

### CRON 1: Atualizar Jogos Diariamente

**Comando:**
```bash
curl -s https://SEUDOMINIO.com/jogos.php
```

**Configuração:** 1 vez por dia (00:00)
```
0 0 * * * curl -s https://SEUDOMINIO.com/jogos.php
```

---

### CRON 2: Atualizar Resultados (a cada 1 minuto)

**Comando:**
```bash
curl -s https://SEUDOMINIO.com/cron/jogos/resultados
```

**Configuração:** A cada 1 minuto
```
* * * * * curl -s https://SEUDOMINIO.com/cron/jogos/resultados
```

---

## 🔐 Acesso ao Sistema

### Login Padrão:
- **Usuário:** admin
- **Senha:** 123456

### URL do Painel Administrativo:
```
https://SEUDOMINIO.com/admin/
```

**⚠️ IMPORTANTE:** Altere a senha padrão assim que fizer o primeiro login!

---

## 📦 Requisitos do Servidor

- **PHP:** 7.4 ou superior
- **MySQL:** 5.7 ou superior
- **Extensões PHP necessárias:**
  - PDO
  - PDO_MySQL
  - cURL
  - GD
  - mbstring
  - OpenSSL

---

## 🗄️ Importar Banco de Dados

Você precisará de um arquivo SQL para criar as tabelas do banco de dados. Procure por arquivos com estas extensões:
- `*.sql`
- `database.sql`
- `banco.sql`
- `banca.sql`

**Como importar:**
1. Acesse o phpMyAdmin
2. Selecione seu banco de dados
3. Clique em "Importar"
4. Escolha o arquivo SQL
5. Clique em "Executar"

---

## 📁 Estrutura de Pastas Importantes

```
/admin/          → Painel administrativo
/app/            → Lógica da aplicação
/imagens/        → Upload de imagens
/arquivos/       → Upload de arquivos
/_temp/          → Cache e sessões
/css/            → Estilos (site.css, site2.css, site3.css)
conexao.php      → Conexão legada do banco
inc.config.php   → Configuração principal
index.php        → Arquivo inicial
jogos.php        → Script de atualização de jogos
```

---

## ✅ Checklist de Instalação

- [ ] Criar banco de dados no MySQL
- [ ] Importar arquivo SQL (se disponível)
- [ ] Configurar `conexao.php` (linha 4)
- [ ] Configurar `inc.config.php` (linhas 27-31, 58-71, 80-81, 89-90)
- [ ] Escolher layout (linha 99 do inc.config.php)
- [ ] Configurar permissões das pastas (777 para _temp, imagens, arquivos)
- [ ] Configurar CRON jobs no cPanel
- [ ] Testar acesso ao site
- [ ] Fazer login no admin (admin/123456)
- [ ] Alterar senha padrão

---

## 🆘 Problemas Comuns

### Erro de conexão com banco de dados
- Verifique se o banco existe
- Confirme usuário e senha
- Verifique se o MySQL está rodando

### Página em branco
- Verifique permissões das pastas
- Ative display_errors no PHP para ver os erros
- Verifique os logs do servidor

### CRON não funciona
- Teste os URLs manualmente no navegador
- Verifique se o cURL está habilitado
- Confirme as permissões de execução

---

## 📞 Próximos Passos

Após a configuração inicial, você deve:
1. Personalizar as cores e logos do site
2. Configurar gateway de pagamento (se aplicável)
3. Testar todas as funcionalidades
4. Fazer backup regular do banco de dados

---

**Boa sorte com sua banca esportiva! 🎰⚽**

