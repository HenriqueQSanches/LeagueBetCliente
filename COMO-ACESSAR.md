# 🚀 COMO ACESSAR O PROJETO

## ✅ CONFIGURAÇÃO CONCLUÍDA!

O banco de dados foi importado, a biblioteca Browser.php foi instalada e tudo está configurado com sucesso!

---

## 🌐 FORMAS DE ACESSAR

### **OPÇÃO 1: Via XAMPP (Recomendado para você)**

1. Certifique-se que o **Apache** e **MySQL** estão rodando no XAMPP
2. Acesse no navegador:

**Site Principal:**
```
http://localhost/Cliente/LeagueBetCliente-main/
```

**Painel Admin:**
```
http://localhost/Cliente/LeagueBetCliente-main/admin/
```

---

### **OPÇÃO 2: Servidor PHP Embutido**

Execute no terminal (PowerShell):
```bash
C:\xampp\php\php.exe -S localhost:8000
```

Depois acesse:
- **Site:** http://localhost:8000
- **Admin:** http://localhost:8000/admin/

---

## 🔐 CREDENCIAIS DE ACESSO

**Painel Administrativo:**
- **Login:** admin
- **Senha:** 123456

⚠️ **IMPORTANTE:** Altere a senha após o primeiro login!

---

## 📊 INFORMAÇÕES DO BANCO

- **Host:** localhost
- **Banco:** banca_esportiva
- **Usuário:** root
- **Senha:** (vazia)
- **Tabelas:** 43 tabelas criadas

---

## 🎨 TROCAR LAYOUT

O projeto possui 3 layouts diferentes. Para trocar, edite o arquivo `inc.config.php` na **linha 117**:

```php
// Layout 1 (padrão - ativo atualmente)
$config['modules']['site'] = $config['modules']['site1'];

// Layout 2
$config['modules']['site'] = $config['modules']['site2'];

// Layout 3
$config['modules']['site'] = $config['modules']['site3'];
```

---

## 🆘 PROBLEMAS COMUNS

### ❌ Página em branco
- Verifique se Apache e MySQL estão rodando no XAMPP
- Verifique o arquivo de erros: `error.log`

### ❌ Erro de conexão com banco
- Confirme que o MySQL está rodando
- Verifique as credenciais em `conexao.php` e `inc.config.php`

### ❌ Admin não funciona
- Limpe o cache do navegador (Ctrl + Shift + Delete)
- Verifique a pasta `_temp/` existe e tem permissões

---

## 📁 ESTRUTURA IMPORTANTE

```
/admin/          → Painel administrativo
/app/            → Código da aplicação
/imagens/        → Upload de imagens
/arquivos/       → Upload de arquivos
/_temp/          → Cache e sessões
/css/            → Estilos dos 3 layouts
conexao.php      → Conexão com banco
inc.config.php   → Configurações principais
index.php        → Página inicial
```

---

## 🎯 PRÓXIMOS PASSOS

1. ✅ Banco importado
2. ✅ Configurações ajustadas
3. ✅ Dependências instaladas
4. 🔲 Acessar o site
5. 🔲 Fazer login no admin
6. 🔲 Alterar senha padrão
7. 🔲 Personalizar o sistema

---

## 💡 DICAS

- **Teste os 3 layouts** para escolher o melhor
- **Configure as redes sociais** no admin
- **Adicione seu logo** personalizado
- **Configure métodos de pagamento** se necessário

---

🎉 **Seu projeto está pronto para uso!**

Para mais detalhes, consulte:
- `README.md` - Documentação completa
- `CONFIGURACAO.md` - Guia de configuração
- `INICIO-RAPIDO.txt` - Guia rápido

