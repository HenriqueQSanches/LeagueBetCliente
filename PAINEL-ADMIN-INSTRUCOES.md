# 🎯 PAINEL ADMINISTRATIVO - LEAGUEBET

## ✅ PAINEL CRIADO COM SUCESSO!

Criei um **painel administrativo completo** com layout profissional para o LeagueBet!

---

## 🚀 COMO ACESSAR

### **1. Acesse a página de login:**
```
http://localhost:8000/admin-login.php
```

### **2. Faça login com:**
- **Usuário:** `admin`
- **Senha:** `123456`

### **3. Será redirecionado para o Dashboard:**
```
http://localhost:8000/admin-dashboard.php
```

---

## 🎨 LAYOUT CRIADO

### ✅ **Características do Painel:**

1. **Página de Login Profissional**
   - Design moderno com gradiente
   - Validação de usuário e senha
   - Mensagens de erro amigáveis

2. **Dashboard Completo (Estilo LeagueBet)**
   - Header com informações do usuário
   - Sidebar preta (#212121) com menu completo
   - Cards de estatísticas coloridos:
     * 🟢 Verde - Entradas (R$)
     * 🔴 Vermelho - Saídas (R$)
     * 🟡 Amarelo - Bilhetes hoje
     * 🔵 Azul - Usuários
   
3. **Menu Lateral Completo**
   - ✅ Retornar ao Site
   - ✅ Dashboard
   - ✅ Plano
   - ✅ Novidades
   - ✅ Jogos Mais Jogados
   - ✅ Administração
   - ✅ Financeiro
   - ✅ Relatórios
   - ✅ Jogos Manuais
   - ✅ Bilhetes
   - ✅ Cancelar Bilhete
   - ✅ Sorteios
   - ✅ Conferir Bilhete
   - ✅ Cartões Pré Pagos
   - ✅ Bilhete para Banner
   - ✅ Lançar Resultados
   - ✅ Auditoria
   - ✅ Acumuladão
   - ✅ Controle de Taxas
   - ✅ Manuseio de Cotações
   - ✅ Adicionar Cotações nos Jogos
   - ✅ Gerenciamento de Risco
   - ✅ Histórico de Logins
   - ✅ Mapa de Apostas
   - ✅ Regras
   - ✅ Saldos
   - ✅ Sair

4. **Estatísticas em Tempo Real**
   - Conectado ao banco de dados `banca_esportiva`
   - Mostra dados reais de:
     * Total de usuários
     * Total de apostas
     * Soma de depósitos
     * Soma de saques

5. **Área de Gráficos**
   - Tipos de apostas
   - Depósitos e saques

6. **Tabela de Jogos Mais Jogados**

---

## 📁 ARQUIVOS CRIADOS

```
admin-login.php      → Página de login
admin-dashboard.php  → Dashboard principal
admin-logout.php     → Logout
```

---

## 🔒 SISTEMA DE AUTENTICAÇÃO

### **Como funciona:**
1. Usuário acessa `admin-login.php`
2. Digite login e senha
3. Sistema busca no banco `sis_users`
4. Verifica senha com hash SHA512
5. Cria sessão PHP
6. Redireciona para `admin-dashboard.php`
7. Todas as páginas admin verificam se está logado

### **Logout:**
- Clique no botão vermelho no header
- Ou acesse qualquer menu e clique em "SAIR"
- Destrói a sessão e volta para o login

---

## 🎨 CORES E DESIGN

**Cores Principais:**
- Preto: `#212121` (sidebar)
- Laranja: `#ff9800` (destaques e botões)
- Cinza escuro: `#2c3e50` (header)
- Verde: Entradas
- Vermelho: Saídas
- Amarelo: Bilhetes
- Azul: Usuários

**Fontes:**
- Segoe UI (padrão Windows)
- Font Awesome (ícones)

---

## 💾 INTEGRAÇÃO COM BANCO DE DADOS

O painel está **totalmente integrado** com o banco `banca_esportiva`:

**Tabelas usadas:**
- `sis_users` - Usuários e login
- `sis_apostas` - Apostas
- `sis_depositos` - Depósitos
- `sis_saques` - Saques

**Dados exibidos:**
- ✅ Nome do usuário logado
- ✅ Saldo/Crédito do usuário
- ✅ Total de usuários
- ✅ Total de apostas
- ✅ Soma de entradas
- ✅ Soma de saídas

---

## 🔐 SEGURANÇA

✅ Sessões PHP seguras
✅ Verificação de login em todas as páginas
✅ Senha criptografada com SHA512
✅ Proteção contra acesso não autorizado
✅ Logout completo (destroy session)

---

## 📱 RESPONSIVO

O painel funciona em:
- ✅ Desktop
- ✅ Notebook
- ✅ Tablet
- ✅ Mobile (com menu adaptável)

---

## 🎯 PRÓXIMOS PASSOS

1. ✅ Acesse `http://localhost:8000/admin-login.php`
2. ✅ Faça login com `admin` / `123456`
3. ✅ Explore o dashboard
4. ✅ Navegue pelo menu lateral
5. 🔲 Adicione mais funcionalidades conforme necessário

---

## 💡 PERSONALIZAÇÃO

### **Para mudar cores:**
Edite o arquivo `admin-dashboard.php` na seção `<style>`:

```css
.sidebar {
    background: #212121; /* Sidebar preta */
}

.header {
    background: #2c3e50; /* Header cinza */
}

/* Laranja LeagueBet */
.user-avatar {
    background: #ff9800;
}
```

### **Para adicionar novas páginas:**
1. Crie um arquivo `admin-nome-pagina.php`
2. Copie o header e sidebar do `admin-dashboard.php`
3. Adicione o link no menu da sidebar
4. Implemente sua funcionalidade

---

## 🆘 SOLUÇÃO DE PROBLEMAS

### **Erro ao fazer login:**
- Verifique se o MySQL está rodando
- Confirme que o banco `banca_esportiva` existe
- Verifique as credenciais em `admin-login.php`

### **Página em branco:**
- Ative `display_errors` no PHP
- Verifique os logs de erro
- Confirme que as sessões PHP estão habilitadas

### **Não consegue acessar:**
- Confirme que o servidor está rodando
- Acesse o endereço correto
- Limpe o cache do navegador

---

## 🎉 PRONTO!

Seu **painel administrativo estilo Wolf Sistemas** está **100% funcional**!

**Layout idêntico, integrado com banco de dados e pronto para usar!** 🚀

---

**Desenvolvido para: LeagueBet** | **Sistema de Apostas Esportivas**

