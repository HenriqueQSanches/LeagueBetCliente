# 🔐 ACESSO ADMINISTRATIVO

## ✅ COMO O ADMIN FAZ LOGIN

### **Opção 1: Página de Login Direta**
```
http://localhost:8000/admin-login.php
```

### **Opção 2: Através da Página Normal**
```
http://localhost:8000/entrar
```
**Esta página redireciona automaticamente para admin-login.php**

---

## 👤 **CREDENCIAIS PADRÃO:**

- **Usuário:** `admin`
- **Senha:** `123456`

⚠️ **Altere a senha após o primeiro login!**

---

## 🎨 **O QUE ACONTECE:**

1. Admin acessa `/entrar` ou `/admin-login.php`
2. Sistema mostra tela de login (design limpo e profissional)
3. Admin digita: admin / 123456
4. Sistema valida no banco de dados
5. **Redireciona automaticamente para o Dashboard administrativo** (layout vermelho Wolf Sistemas)

---

## 📍 **URLs IMPORTANTES:**

**Para Admin:**
- Login: `http://localhost:8000/entrar` ou `http://localhost:8000/admin-login.php`
- Dashboard: `http://localhost:8000/admin-dashboard.php` (após login)
- Logout: `http://localhost:8000/admin-logout.php`

**Para Usuários Normais:**
- Site: `http://localhost:8000/`
- Consultar Bilhete: `http://localhost:8000/bilhete`
- Regulamento: `http://localhost:8000/regras`

---

## 💡 **DIFERENÇAS:**

| Característica | Sistema Original | Novo Painel Admin |
|----------------|-----------------|-------------------|
| Login | Via AJAX (com problemas) | Server-side (funciona 100%) |
| Layout | Usa layout do site | Layout próprio Wolf Sistemas |
| Cores | Laranja/Amarelo | Vermelho escuro profissional |
| Acesso | /entrar → redireciona | admin-login.php direto |
| Dashboard | Layout site | Layout administrativo completo |

---

## 🎯 **FLUXO COMPLETO:**

```
1. Admin digita no navegador: localhost:8000/entrar
   ↓
2. Sistema redireciona automaticamente para: admin-login.php
   ↓
3. Admin vê tela de login profissional
   ↓
4. Admin digita: admin / 123456
   ↓
5. Sistema valida no banco (sis_users)
   ↓
6. Cria sessão PHP
   ↓
7. Redireciona para: admin-dashboard.php
   ↓
8. Admin vê dashboard com layout Wolf Sistemas (vermelho)
```

---

## ✅ **VANTAGENS:**

1. ✅ **Funciona 100%** - Sem erros de AJAX
2. ✅ **Redirecionamento automático** - Admin não precisa saber URL específica
3. ✅ **Layout profissional** - Visual moderno e limpo
4. ✅ **Integrado** - Conectado ao banco de dados real
5. ✅ **Seguro** - Validação server-side, sessões PHP
6. ✅ **Intuitivo** - Fluxo natural de login

---

## 🔒 **SEGURANÇA:**

- ✅ Senha criptografada com SHA512
- ✅ Validação no banco de dados
- ✅ Sessões PHP seguras
- ✅ Logout completo (destroy session)
- ✅ Verificação em todas as páginas admin

---

## 📝 **NOTAS:**

- O redirecionamento de `/entrar` para `admin-login.php` é **automático**
- Funciona tanto para admins quanto para gerentes
- Layout Wolf Sistemas aparece **apenas após login**
- Sistema original do site **não foi modificado** (continua funcionando para usuários normais)

---

**Desenvolvido para facilitar o acesso administrativo! 🚀**

