# 🎰 Banca Esportiva - Sistema de Apostas

Sistema completo de apostas esportivas com múltiplos layouts e painel administrativo.

## 🚀 Instalação Rápida

### Opção 1: Configuração Automática (Recomendado)

Execute o script de configuração automática via terminal:

```bash
php configurar.php
```

O script irá:
- ✅ Verificar requisitos do sistema
- ✅ Criar pastas necessárias
- ✅ Configurar arquivos automaticamente
- ✅ Criar backups dos originais

### Opção 2: Configuração Manual

Consulte o arquivo detalhado: **[CONFIGURACAO.md](CONFIGURACAO.md)**

## 📋 Checklist Rápido

- [ ] **Criar banco de dados MySQL**
- [ ] **Importar arquivo SQL** (se disponível)
- [ ] **Configurar `conexao.php`** (linha 4)
  ```php
  $conexao = new PDO('mysql:host=localhost;dbname=SEU_BANCO', 'USUARIO', 'SENHA');
  ```
- [ ] **Configurar `inc.config.php`**
  - Linhas 27-31: Domínio e informações do site
  - Linhas 58-71: Dados do banco
  - Linha 99: Layout (site1, site2 ou site3)
- [ ] **Configurar permissões** (chmod 777):
  - `_temp/`
  - `imagens/`
  - `arquivos/`
- [ ] **Configurar CRON jobs** no cPanel
- [ ] **Testar acesso** ao site e admin

## 🔐 Acesso ao Sistema

**Painel Admin:** `https://seudominio.com/admin/`

**Login Padrão:**
- Usuário: `admin`
- Senha: `123456`

⚠️ **Altere a senha após o primeiro login!**

## ⏰ CRON Jobs Necessários

### 1. Atualizar Jogos (1x por dia)
```bash
0 0 * * * curl -s https://seudominio.com/jogos.php
```

### 2. Atualizar Resultados (a cada 1 minuto)
```bash
* * * * * curl -s https://seudominio.com/cron/jogos/resultados
```

## 🎨 Layouts Disponíveis

O sistema possui 3 layouts diferentes. Para trocar, edite a linha 99 do `inc.config.php`:

```php
// Layout 1
$config['modules']['site'] = $config['modules']['site1'];

// Layout 2
$config['modules']['site'] = $config['modules']['site2'];

// Layout 3
$config['modules']['site'] = $config['modules']['site3'];
```

## 📦 Requisitos do Servidor

- **PHP:** 7.4 ou superior
- **MySQL:** 5.7 ou superior
- **Extensões PHP:**
  - PDO
  - PDO_MySQL
  - cURL
  - GD
  - mbstring
  - OpenSSL
  - JSON

## 📁 Estrutura Principal

```
/admin/          → Painel administrativo
/app/            → Núcleo da aplicação
/imagens/        → Upload de imagens
/arquivos/       → Upload de arquivos
/_temp/          → Cache e sessões
/css/            → Estilos dos layouts
conexao.php      → Conexão do banco
inc.config.php   → Arquivo de configuração principal
index.php        → Página inicial
jogos.php        → Atualização de jogos
```

## 🆘 Problemas Comuns

### Erro de conexão com banco
✅ Verifique as credenciais em `conexao.php` e `inc.config.php`

### Página em branco
✅ Verifique permissões das pastas `_temp`, `imagens`, `arquivos`

### CRON não funciona
✅ Teste os URLs manualmente no navegador primeiro

## 📞 Suporte

Para mais detalhes, consulte a documentação completa em **[CONFIGURACAO.md](CONFIGURACAO.md)**

---

**Desenvolvido para Banca Esportiva** | Versão 1.0

