# 🎮 JOGOS NA TELA PRINCIPAL - LEAGUEBET

## ✅ STATUS ATUAL

O sistema **JÁ ESTÁ CONFIGURADO** para exibir os jogos na tela principal! 

### 📊 Como Funciona

1. **Página Principal** (`http://localhost/Cliente/LeagueBetCliente-main/`)
   - Redireciona automaticamente para o controller de apostas
   - Carrega o template `apostar.twig`

2. **Carregamento dos Jogos**
   - **Vue.js** faz uma requisição AJAX para `/apostar/jogos`
   - A API retorna os jogos em formato JSON
   - Vue.js renderiza os jogos na tela automaticamente

3. **Estrutura Visual**
   - **Sidebar Esquerda:** Menu de navegação e filtros
   - **Área Central:** Lista de jogos com cotações
   - **Sidebar Direita:** Cupom de apostas

---

## 🔍 VERIFICAÇÃO

### 1. Abra o Site
```
http://localhost/Cliente/LeagueBetCliente-main/
```

### 2. O que Você Deve Ver

#### ✅ Se Estiver Funcionando:
- **Banner:** "BOTE SEU ESPÍRITO COMPETITIVO PRA JOGO!"
- **Menu Lateral Esquerdo:**
  - Logo LeagueBet
  - Links: Início, Futebol, Ao Vivo, etc.
  - Botão de Login
- **Área Central:**
  - Cabeçalho "Jogos Disponíveis"
  - Campo de busca
  - **TABELAS DE JOGOS** organizadas por país/campeonato
  - Cada jogo mostra:
    - Times (Casa x Fora)
    - Data e hora
    - Cotações (Casa, Empate, Fora)
    - Botão "Outras" para mais cotações
- **Cupom (Direita):**
  - Área para adicionar apostas
  - Cálculo automático do prêmio

#### ❌ Se NÃO Estiver Funcionando:
- Tela em branco
- Apenas o layout sem jogos
- Erro no console do navegador (F12)
- Loading infinito

---

## 🛠️ COMO OS JOGOS SÃO EXIBIDOS

### Fluxo de Dados

```
1. Usuário acessa: http://localhost/Cliente/LeagueBetCliente-main/
                    ↓
2. indexController redireciona para apostarController
                    ↓
3. apostarController carrega o template apostar.twig
                    ↓
4. Vue.js inicializa e faz requisição AJAX:
   GET /apostar/jogos
                    ↓
5. apostarController::jogosAction() retorna JSON:
   {
     "cotacoes": [...],
     "grupos": {...},
     "paises": [
       {
         "id": 1,
         "title": "Brasil",
         "campeonatos": [
           {
             "id": 123,
             "title": "Brasileirão Série A",
             "jogos": [
               {
                 "id": 641,
                 "casa": "Flamengo",
                 "fora": "Palmeiras",
                 "data": "2025-11-05",
                 "hora": "14:30:00",
                 "cotacoes": {
                   "90": {
                     "casa": 3.00,
                     "empate": 3.30,
                     "fora": 2.10
                   }
                 }
               }
             ]
           }
         ]
       }
     ]
   }
                    ↓
6. Vue.js renderiza os jogos na tela usando o template
                    ↓
7. Usuário vê os jogos e pode clicar para apostar
```

---

## 📋 TEMPLATE DOS JOGOS

O template `apostar.twig` usa Vue.js para renderizar:

```twig
<table class="table table-jogos" v-for="pais in paises">
    <thead>
        <tr class="tr-pais">
            <th>${pais.title}</th>
        </tr>
    </thead>
    <tbody v-for="campeonato in getCampeonatos(pais)">
        <tr class="campeonato">
            <td>${campeonato.title}</td>
        </tr>
        <tr v-for="jogo in getJogos(campeonato)">
            <td>
                ${jogo.casa} x ${jogo.fora}
                <br>
                <small>${jogo.data} ${jogo.hora}</small>
            </td>
            <td v-for="c in getCotacoesPrincipais()">
                <button @click="addJogo(jogo, c)">
                    ${jogo.cotacoes['90'][c.campo]}
                </button>
            </td>
        </tr>
    </tbody>
</table>
```

---

## 🐛 POSSÍVEIS PROBLEMAS E SOLUÇÕES

### Problema 1: Jogos Não Aparecem

**Sintomas:**
- Layout carrega, mas sem jogos
- Console mostra erro 404 ou 500

**Soluções:**
1. Verificar se há jogos no banco:
   ```sql
   SELECT COUNT(*) FROM sis_jogos WHERE status = 1 AND data >= CURDATE();
   ```

2. Testar a API diretamente:
   ```
   http://localhost/Cliente/LeagueBetCliente-main/apostar/jogos
   ```
   Deve retornar JSON com os jogos

3. Verificar console do navegador (F12):
   - Abra as ferramentas de desenvolvedor
   - Vá na aba "Console"
   - Procure por erros em vermelho

### Problema 2: Erro de JavaScript

**Sintomas:**
- Console mostra erros de Vue.js
- "Vue is not defined"
- "axios is not defined"

**Soluções:**
1. Verificar se os arquivos JavaScript estão carregando:
   - `node_modules/vue/dist/vue.min.js`
   - `node_modules/axios/dist/axios.js`
   - `node_modules/lodash/lodash.min.js`

2. Executar `npm install` se necessário:
   ```bash
   cd C:\xampp\htdocs\Cliente\LeagueBetCliente-main
   npm install
   ```

### Problema 3: API Retorna Vazio

**Sintomas:**
- API retorna: `{"cotacoes":[],"grupos":{},"paises":[]}`
- Sem jogos disponíveis

**Soluções:**
1. Importar jogos:
   ```
   http://localhost/Cliente/LeagueBetCliente-main/importar-agora.php
   ```

2. Ou via terminal:
   ```bash
   C:\xampp\php\php.exe jogos.php
   ```

### Problema 4: Layout Quebrado

**Sintomas:**
- Jogos aparecem, mas layout está desorganizado
- CSS não carrega corretamente

**Soluções:**
1. Limpar cache do navegador (Ctrl + Shift + Delete)
2. Forçar reload (Ctrl + F5)
3. Verificar se os arquivos CSS existem:
   - `css/riverbets-layout.css`
   - `css/riverbets-style.css`

---

## 🎨 PERSONALIZAÇÃO DA EXIBIÇÃO

### Alterar Cores dos Botões de Cotação

Edite `css/riverbets-style.css`:

```css
.table-jogos .btn-cotacao {
    background: #ff8000; /* Laranja */
    color: white;
}

.table-jogos .btn-cotacao:hover {
    background: #ff6000; /* Laranja escuro */
}
```

### Alterar Quantidade de Jogos Exibidos

Edite `app/modules/website/controllers/apostarController.php`:

```php
// Linha 116 - Adicionar LIMIT
ORDER BY
    d.title ASC, a.data ASC, a.hora ASC
LIMIT 100  // <-- Adicione esta linha
```

### Adicionar Filtros Personalizados

Edite `app/views/website/page/apostar.twig`:

```javascript
// Adicionar novo filtro no Vue.js
methods: {
    filterByLeague(league) {
        this.findCampeonato = league;
    },
    filterByDate(date) {
        this.findData = date;
    }
}
```

---

## 📱 VISUALIZAÇÃO MOBILE

Os jogos são **automaticamente responsivos**:

- **Desktop:** Tabela completa com todas as cotações
- **Tablet:** Tabela adaptada com scroll horizontal
- **Mobile:** 
  - Tabela compacta
  - Dropdown para selecionar campeonatos
  - Campo de busca
  - Botões maiores para facilitar o toque

---

## 🔄 ATUALIZAÇÃO AUTOMÁTICA

Para atualizar os jogos automaticamente a cada X minutos:

### Adicionar no `apostar.twig`:

```javascript
created() {
    // Carregar jogos inicialmente
    this.loadGames();
    
    // Atualizar a cada 5 minutos
    setInterval(() => {
        this.loadGames();
    }, 300000); // 300000ms = 5 minutos
},
methods: {
    loadGames() {
        axios.get(url('apostar/jogos'))
            .then((response) => {
                this.paises = response.data.paises;
                this.cotacoes = response.data.cotacoes;
                this.grupos = response.data.grupos;
            });
    }
}
```

---

## ✅ CHECKLIST DE VERIFICAÇÃO

Marque cada item conforme verifica:

- [ ] Site abre em `http://localhost/Cliente/LeagueBetCliente-main/`
- [ ] Layout LeagueBet (laranja e preto) aparece
- [ ] Menu lateral esquerdo está visível
- [ ] Banner "BOTE SEU ESPÍRITO COMPETITIVO" aparece
- [ ] Cabeçalho "Jogos Disponíveis" está presente
- [ ] **JOGOS APARECEM NA TELA** (principal verificação!)
- [ ] Cada jogo mostra: Times, Data, Hora, Cotações
- [ ] Botões de cotação são clicáveis
- [ ] Cupom de apostas aparece na direita
- [ ] Campo de busca funciona
- [ ] Filtros de campeonato funcionam
- [ ] Console do navegador (F12) não mostra erros

---

## 🎯 TESTE RÁPIDO

### 1. Abra o Console do Navegador (F12)

### 2. Execute este comando:

```javascript
// Verificar se Vue.js está carregado
console.log('Vue:', typeof Vue);

// Verificar se há jogos carregados
console.log('Jogos:', app.paises);

// Contar total de jogos
let total = 0;
app.paises.forEach(p => {
    p.campeonatos.forEach(c => {
        total += c.jogos.length;
    });
});
console.log('Total de jogos:', total);
```

### 3. Resultado Esperado:

```
Vue: "function"
Jogos: Array(110) [...]
Total de jogos: 310
```

---

## 📞 SUPORTE

### Se os Jogos NÃO Aparecerem:

1. **Verifique o Console (F12):**
   - Procure por erros em vermelho
   - Anote a mensagem de erro

2. **Teste a API Diretamente:**
   ```
   http://localhost/Cliente/LeagueBetCliente-main/apostar/jogos
   ```
   - Deve retornar JSON
   - Deve conter `"paises": [...]` com dados

3. **Verifique o Banco de Dados:**
   - Abra phpMyAdmin
   - Execute: `SELECT COUNT(*) FROM sis_jogos WHERE status = 1 AND data >= CURDATE()`
   - Deve retornar um número > 0

4. **Execute o Script de Diagnóstico:**
   ```
   http://localhost/Cliente/LeagueBetCliente-main/teste-jogos-direto.php
   ```

---

## 🎉 CONCLUSÃO

O sistema **já está pronto** para exibir os jogos! 

Se você abriu o site e viu:
- ✅ Layout carregado
- ✅ Menu lateral
- ✅ Banner
- ✅ **JOGOS NA TELA**

**Então está tudo funcionando perfeitamente!** 🚀

Se os jogos **não aparecerem**, siga a seção "Possíveis Problemas e Soluções" acima.

---

**Última Atualização:** 05/11/2025 18:30  
**Status:** ✅ Sistema Configurado e Pronto  
**Jogos Disponíveis:** 310 jogos de 110 campeonatos

