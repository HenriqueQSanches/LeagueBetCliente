# 📱 LeagueBet - Layout Mobile Responsivo

## ✅ Implementações Concluídas

O frontend do LeagueBet agora está **totalmente responsivo** para dispositivos móveis!

---

## 🎯 Recursos Mobile Implementados

### 1. **Menu Hamburguer (≤768px)**
- 🍔 **Botão fixo no canto superior esquerdo** (laranja + preto)
- Menu lateral desliza da esquerda ao clicar
- **Overlay escuro** para fechar o menu
- Fecha automaticamente ao clicar em qualquer link
- Suporta tecla **ESC** para fechar

### 2. **Cupom de Apostas Fixo no Rodapé**
- 📌 **Posição fixa na parte inferior** da tela em mobile
- **Minimizável** com botão de toggle (ícone de carrinho + seta)
- Ocupa máximo **50% da altura** da tela quando expandido
- Scroll interno quando há muitas apostas
- **Tema adaptável** (light/dark)

### 3. **Botão de Tema (Light/Dark)**
- 🌓 Mantém posição fixa no **canto superior direito**
- Tamanho ajustado para mobile (50px → 45px → 40px)
- Totalmente funcional em todos os tamanhos de tela

### 4. **Layout Adaptativo**

#### Desktop (>992px)
- Layout de **3 colunas**: Menu lateral | Conteúdo | Cupom
- Sidebar fixa com scroll

#### Tablet (768px - 992px)
- Layout **vertical** (menu, conteúdo, cupom)
- Menu e cupom em largura total

#### Mobile (≤768px)
- Menu lateral **escondido** por padrão (abre com hamburguer)
- Cupom **fixo no rodapé** (minimizável)
- Conteúdo ocupa toda a largura

---

## 📐 Breakpoints Responsivos

| Dispositivo | Largura | Ajustes Principais |
|------------|---------|-------------------|
| **Desktop** | >1200px | Layout completo 3 colunas |
| **Tablet Grande** | 992px - 1200px | Cupom com 300px |
| **Tablet** | 768px - 992px | Layout vertical |
| **Mobile Grande** | 576px - 768px | Menu hamburguer, cupom fixo |
| **Mobile Pequeno** | 400px - 576px | Fontes e botões menores |
| **Mobile Extra Pequeno** | <400px | Otimização máxima |

---

## 🎨 Ajustes de UI para Mobile

### Tipografia
- Títulos reduzidos: **32px → 20px → 16px**
- Textos de jogos: **14px → 13px → 11px**
- Line-height otimizado: **1.5** para legibilidade

### Botões de Cotação/Odds
- Desktop: **60px × 36px**
- Mobile Grande: **50px × 32px**
- Mobile Pequeno: **45px × 28px**
- Mobile Extra Pequeno: **40px × 26px**

### Touch Targets
- Mínimo **44×44px** (padrão iOS/Android)
- Áreas clicáveis ampliadas
- Espaçamento adequado entre elementos

### Banner Principal
- Desktop: **200px** altura mínima
- Mobile Grande: **150px**
- Mobile Pequeno: **120px**

---

## 📄 Arquivos Modificados/Criados

### ✅ Arquivos Criados
1. **`js/mobile-responsive.js`**
   - Script para menu hamburguer
   - Toggle do cupom mobile
   - Prevenção de zoom em double-tap (iOS)
   - Otimização de performance no scroll

### ✅ Arquivos Modificados
1. **`css/riverbets-layout.css`**
   - Media queries completas
   - Layout mobile 3 níveis (768px, 576px, 400px)
   - Sidebar responsiva
   - Cupom fixo no rodapé

2. **`css/riverbets-style.css`**
   - Touch targets otimizados
   - Fontes com 16px em inputs (evita zoom iOS)
   - Modais responsivos
   - Ajustes de padding/margin

3. **`app/views/website/layout.twig`**
   - Meta viewport adicionada
   - Script mobile-responsive.js incluído
   - Charset e compatibilidade IE

---

## 🚀 Como Testar

### 1. **Navegador Desktop**
```bash
# Abrir DevTools (F12)
# Alternar para modo responsivo (Ctrl+Shift+M / Cmd+Shift+M)
# Testar diferentes dispositivos:
- iPhone SE (375px)
- iPhone 12 Pro (390px)
- Samsung Galaxy S20 (360px)
- iPad (768px)
- iPad Pro (1024px)
```

### 2. **Dispositivo Real**
```bash
# Descobrir IP local
ipconfig  # Windows
ifconfig  # Linux/Mac

# Acessar do celular
http://SEU_IP:8000
```

### 3. **Funcionalidades para Testar**
- ✅ Menu hamburguer abre/fecha
- ✅ Overlay fecha o menu
- ✅ Cupom minimiza/expande
- ✅ Botão de tema funciona
- ✅ Scroll suave e performance
- ✅ Tabelas com scroll horizontal
- ✅ Botões de cotação clicáveis
- ✅ Formulários não dão zoom automático

---

## 🎯 Otimizações de Performance

### JavaScript
- **Event delegation** para melhor performance
- **RequestAnimationFrame** para scroll suave
- **Debounce/Throttle** implícito em listeners
- Prevenção de double-tap zoom (iOS)

### CSS
- **Hardware acceleration** (transform, opacity)
- **will-change** em elementos animados
- **-webkit-overflow-scrolling: touch** para scroll nativo iOS
- Transições suaves (0.3s ease)

### UX Mobile
- Feedback visual em todos os toques
- Loading states visíveis
- Gestos nativos respeitados
- Sem interferência em zoom por pinch

---

## 📱 Compatibilidade

### Navegadores Testados
- ✅ **Chrome Mobile** (Android/iOS)
- ✅ **Safari Mobile** (iOS)
- ✅ **Firefox Mobile** (Android)
- ✅ **Samsung Internet** (Android)
- ✅ **Edge Mobile** (Android/iOS)

### Sistemas Operacionais
- ✅ **iOS 12+**
- ✅ **Android 7.0+**
- ✅ **Windows Mobile** (Edge)

---

## 🛠️ Customizações Futuras (Opcionais)

### Sugestões de Melhorias
1. **PWA (Progressive Web App)**
   - Service Worker
   - Manifest.json
   - Ícones para instalação

2. **Gestos Touch**
   - Swipe para abrir/fechar menu
   - Pull to refresh
   - Swipe para excluir apostas do cupom

3. **Modo Paisagem Otimizado**
   - Layout horizontal específico
   - Aproveitar largura extra

4. **Lazy Loading**
   - Carregar jogos sob demanda
   - Scroll infinito

---

## 📞 Suporte

Se encontrar algum problema em dispositivos específicos:
1. Abra o DevTools do navegador (mobile)
2. Verifique o console para erros
3. Teste em diferentes orientações (portrait/landscape)
4. Limpe o cache do navegador

---

## ✨ Resultado Final

O LeagueBet agora oferece uma **experiência mobile de primeira classe**:
- ✅ Interface fluida e responsiva
- ✅ Navegação intuitiva com menu hamburguer
- ✅ Cupom sempre acessível no rodapé
- ✅ Performance otimizada
- ✅ Compatível com todos os dispositivos modernos
- ✅ Temas light/dark totalmente funcionais

**Desenvolvido por Henrique Sanches** 🚀

