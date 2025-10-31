# 🚀 Como Usar os Scripts de Instalação

## ✅ Scripts Atualizados!

Os scripts `IMPORTAR_BANCO.bat` e `INICIAR_CONFIGURACAO.bat` foram **atualizados** para detectar automaticamente o PHP do XAMPP nos seguintes locais:

- `C:\xampp\php\php.exe` ✔️
- `C:\Program Files\xampp\php\php.exe` ✔️
- `D:\xampp\php\php.exe` ✔️
- PATH do sistema ✔️

## 🎯 Como Usar

### 1️⃣ Execute o Importador de Banco
Dê duplo clique em:
```
IMPORTAR_BANCO.bat
```

O script agora deve **detectar automaticamente** o PHP do seu XAMPP!

### 2️⃣ Execute o Configurador
Após importar o banco, dê duplo clique em:
```
INICIAR_CONFIGURACAO.bat
```

---

## 🔧 Se o PHP Ainda Não For Encontrado

### Opção A: XAMPP em Local Personalizado

Se o seu XAMPP está instalado em outro local (ex: `E:\xampp`), você tem 3 opções:

#### **Opção 1: Editar os Scripts .bat** (Recomendado)

1. Clique com botão direito em `IMPORTAR_BANCO.bat`
2. Selecione **"Editar"** ou **"Edit"**
3. Localize a seção com os caminhos do PHP (linhas 26-44)
4. Adicione seu caminho antes das verificações existentes:

```batch
REM Tentar encontrar PHP do XAMPP (locais comuns)
if exist "E:\xampp\php\php.exe" (
    set "PHP_PATH=E:\xampp\php\php.exe"
    goto :php_found
)
```

5. Salve o arquivo
6. Faça o mesmo com `INICIAR_CONFIGURACAO.bat`

#### **Opção 2: Executar Diretamente via Terminal**

1. Abra o PowerShell ou CMD na pasta do projeto
2. Execute:

```cmd
"C:\caminho\para\seu\xampp\php\php.exe" importar-banco.php
```

Substitua `C:\caminho\para\seu\xampp` pelo caminho real do seu XAMPP.

#### **Opção 3: Adicionar PHP ao PATH**

1. Pressione `Win + Pause` (ou clique direito em "Este Computador" > "Propriedades")
2. Clique em **"Configurações avançadas do sistema"**
3. Clique em **"Variáveis de Ambiente"**
4. Na seção **"Variáveis do sistema"**, encontre a variável `Path`
5. Clique em **"Editar"**
6. Clique em **"Novo"**
7. Adicione: `C:\xampp\php` (ou o caminho do seu XAMPP)
8. Clique em **"OK"** em todas as janelas
9. **Feche e reabra** o terminal/CMD
10. Execute os scripts `.bat` novamente

### Opção B: Usar Alternativa Manual via phpMyAdmin

Se preferir não usar os scripts:

1. **Importar Banco:**
   - Acesse `http://localhost/phpmyadmin`
   - Crie um banco: `banca_esportiva`
   - Clique em "Importar"
   - Selecione: `reidoscript bancas.sql`
   - Execute

2. **Configurar Manualmente:**
   - Siga o guia em `CONFIGURACAO.md`
   - Ou abra `painel-configuracao.html` no navegador

---

## 🔍 Como Descobrir o Caminho do XAMPP

### Método 1: Painel de Controle do XAMPP
1. Abra o **XAMPP Control Panel**
2. Clique em **"Config"** ao lado de Apache
3. Selecione **"PHP (php.ini)"**
4. O caminho será mostrado no título da janela do editor

### Método 2: Explorador de Arquivos
1. Abra o **Explorador de Arquivos**
2. Procure por pastas chamadas `xampp` em:
   - `C:\xampp`
   - `C:\Program Files\xampp`
   - `D:\xampp`
   - Outras unidades
3. Dentro da pasta XAMPP, procure pela pasta `php`
4. O executável está em: `xampp\php\php.exe`

### Método 3: Via PowerShell/CMD
Execute no terminal:
```powershell
where /R C:\ php.exe
```
Isso procurará o `php.exe` em todo o disco C: (pode demorar).

---

## ✅ Verificar se Funcionou

Após executar `IMPORTAR_BANCO.bat` com sucesso, você deve ver:

```
✅ PHP encontrado!
📍 Usando PHP em: C:\xampp\php\php.exe

✅ Arquivo SQL encontrado!
✅ Script de importação encontrado!

═══════════════════════════════════════════════════════

📋 INFORMAÇÕES IMPORTANTES:
...
```

Se você ver estas mensagens, **está tudo certo!** 🎉

---

## 🆘 Ainda Com Problemas?

Se mesmo após seguir este guia você ainda tiver problemas:

1. Verifique se o **MySQL** está rodando no XAMPP Control Panel
2. Tente importar manualmente via **phpMyAdmin** (mais simples)
3. Consulte os guias:
   - `IMPORTAR-BANCO.md` - Guia de importação
   - `CONFIGURACAO.md` - Guia de configuração
   - `INICIO-RAPIDO.txt` - Checklist rápido

---

## 📋 Checklist de Verificação

- [ ] XAMPP instalado
- [ ] Apache e MySQL iniciados no XAMPP Control Panel
- [ ] Arquivo `reidoscript bancas.sql` presente na pasta
- [ ] Scripts `.bat` atualizados
- [ ] PHP do XAMPP detectado corretamente
- [ ] Banco importado com sucesso

---

**💡 Dica:** Após importar o banco com sucesso, não esqueça de executar `INICIAR_CONFIGURACAO.bat` para configurar os arquivos do sistema!

🎉 **Boa sorte com sua Banca Esportiva!**

