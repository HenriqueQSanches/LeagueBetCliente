# 🎮 ONDE OS JOGOS APARECEM NA TELA PRINCIPAL

## 📍 Localização Exata

Os jogos aparecem na **ÁREA CENTRAL** da página, entre:
- **Esquerda**: Menu lateral (Início, Futebol, Ao Vivo, etc.)
- **Direita**: Cupom de apostas (Bilhete)

## 🎯 Estrutura Visual

```
┌─────────────────────────────────────────────────────────────┐
│                    CABEÇALHO DO SITE                        │
├──────────┬──────────────────────────────────┬──────────────┤
│          │                                  │              │
│  MENU    │     BANNER                       │   CUPOM      │
│ LATERAL  │     "BOTE SEU ESPÍRITO..."       │   (Bilhete)  │
│          │                                  │              │
│ • Início ├──────────────────────────────────┤              │
│ • Futebol│  🏆 Jogos Disponíveis            │  Mínimo de   │
│ • Ao Vivo│  [Buscar jogos...]               │  jogos: 2    │
│ • Mais   │                                  │              │
│          │  ┌────────────────────────────┐  │              │
│ [LOGIN]  │  │ 🇧🇷 BRASIL                │  │  [Valor]     │
│          │  ├────────────────────────────┤  │  [Cotação]   │
│          │  │ Campeonato Brasileiro      │  │  [Prêmio]    │
│          │  ├────────────────────────────┤  │              │
│          │  │ Time A x Time B            │  │  [Concluir]  │
│          │  │ 06/11/2025 às 19:00        │  │              │
│          │  │ [1] [X] [2] [+15]          │  │              │
│          │  ├────────────────────────────┤  │              │
│          │  │ Time C x Time D            │  │              │
│          │  │ 06/11/2025 às 20:00        │  │              │
│          │  │ [1] [X] [2] [+15]          │  │              │
│          │  └────────────────────────────┘  │              │
│          │                                  │              │
│          │  ┌────────────────────────────┐  │              │
│          │  │ 🇪🇸 ESPANHA               │  │              │
│          │  ├────────────────────────────┤  │              │
│          │  │ La Liga                    │  │              │
│          │  ├────────────────────────────┤  │              │
│          │  │ Time E x Time F            │  │              │
│          │  │ ...                        │  │              │
│          │  └────────────────────────────┘  │              │
│          │                                  │              │
└──────────┴──────────────────────────────────┴──────────────┘
```

## 🔍 Como os Jogos São Renderizados

### 1. **Vue.js busca os dados**
   - Arquivo: `app/views/website/page/apostar.twig` (linha 391-406)
   - Faz uma chamada AJAX para: `http://localhost/Cliente/LeagueBetCliente-main/apostar/jogos`

### 2. **Template renderiza os jogos**
   - Arquivo: `app/views/website/page/apostar.twig` (linha 136-196)
   - Estrutura:
     ```html
     <div class="table-responsive overflow-jogos" v-if="paises">
         <table v-for="pais in paises">
             <!-- Cabeçalho do país -->
             <tbody v-for="campeonato in getCampeonatos(pais)">
                 <!-- Nome do campeonato -->
                 <tr v-for="jogo in getJogos(campeonato)">
                     <!-- AQUI APARECEM OS JOGOS! -->
                     <td>${jogo.casa} x ${jogo.fora}</td>
                     <td>${jogo.data} às ${jogo.hora}</td>
                     <td>Botões de cotação [1] [X] [2]</td>
                 </tr>
             </tbody>
         </table>
     </div>
     ```

## ❓ Por Que Não Está Aparecendo?

Se a área está VAZIA, pode ser:

### ✅ Problema 1: Vue.js não está carregando os dados
**Solução**: Abra o Console do navegador (F12) e procure por:
- Erros em vermelho
- A mensagem da chamada AJAX para `/apostar/jogos`

### ✅ Problema 2: A API retorna dados mas Vue não renderiza
**Solução**: No Console, digite:
```javascript
app.paises
```
Se retornar `undefined` ou `[]`, o Vue não recebeu os dados.

### ✅ Problema 3: CSS está ocultando os jogos
**Solução**: No Console, digite:
```javascript
document.querySelector('.table-jogos')
```
Se retornar `null`, o elemento não existe. Se retornar um elemento, verifique se está visível.

## 🚀 TESTE RÁPIDO

1. **Abra o site**: `http://localhost/Cliente/LeagueBetCliente-main/`
2. **Pressione F12** (Console do navegador)
3. **Digite**:
   ```javascript
   console.log('Países:', app.paises);
   console.log('Total de jogos:', app.paises ? app.paises.reduce((acc, p) => acc + p.campeonatos.reduce((a, c) => a + c.jogos.length, 0), 0) : 0);
   ```
4. **Me diga o resultado!**

## 📊 O Que Deveria Aparecer

Com 310 jogos no banco de dados, você deveria ver:
- Várias bandeiras de países (🇧🇷 Brasil, 🇪🇸 Espanha, etc.)
- Nomes de campeonatos (Brasileirão, La Liga, etc.)
- Linhas com jogos: "Time A x Time B"
- Botões de cotação: [1.50] [3.20] [2.80] [+15]

---

**🎯 PRÓXIMO PASSO**: Me envie o que aparece no Console quando você digita os comandos acima!

