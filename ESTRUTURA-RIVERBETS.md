# 🎨 Como Deixar IDÊNTICO ao RIVERBETS

## ✅ O Que JÁ Foi Feito:

1. **Cores roxo + verde neon** aplicadas ✅
2. **CSS de estilo** criado (riverbets-style.css) ✅
3. **CSS de layout** criado (riverbets-layout.css) ✅

---

## 🚧 O Que FALTA Para Ficar Idêntico:

A estrutura HTML precisa ser modificada para ter **3 colunas**:
- **Coluna 1:** Menu lateral esquerdo
- **Coluna 2:** Área central com jogos
- **Coluna 3:** Cupom de apostas

---

## 📝 Solução Rápida: Adicionar HTML no Início do Body

Para deixar IDÊNTICO ao RIVERBETS sem modificar muito código, adicione este código no arquivo:

`app/views/website/page/apostar.twig`

**ANTES da linha 1, adicione:**

```html
<div class="riverbets-layout">
    <!-- Coluna 1: Menu Lateral Esquerdo -->
    <aside class="riverbets-sidebar">
        <div class="riverbets-sidebar-logo">
            {{ dados.getBanca() }}
        </div>
        <nav>
            <ul class="riverbets-sidebar-menu">
                <li><a href="{{ url() }}" class="active"><i class="fa fa-home"></i> Início</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-futbol-o"></i> Futebol</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-play-circle"></i> Ao Vivo</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-list"></i> Mais Esportes</a></li>
                <li><a href="{{ url('regras') }}"><i class="fa fa-book"></i> Regulamento</a></li>
                <li><a href="{{ url('bilhete') }}"><i class="fa fa-ticket"></i> Conferir Bilhete</a></li>
            </ul>
        </nav>
        <div class="riverbets-sidebar-login">
            {% if user %}
                <a href="{{ url('logout') }}" class="btn">Sair</a>
                <div style="text-align: center; color: white; margin-top: 10px;">
                    Saldo: R$ {{ user.getCredito(true) }}
                </div>
            {% else %}
                <a href="#" data-toggle="modal" data-target="#modal-login" class="btn">LOGIN</a>
            {% endif %}
        </div>
    </aside>

    <!-- Coluna 2: Área Central -->
    <div class="riverbets-content">
        <!-- Banner -->
        <div class="riverbets-banner">
            <div style="text-align: center; padding: 20px;">
                <h1>BOTE SEU ESPÍRITO COMPETITIVO<br>PRA JOGO!</h1>
            </div>
        </div>

        <!-- Cabeçalho dos Jogos -->
        <div class="riverbets-games-header">
            <h2>
                <i class="fa fa-futbol-o"></i>
                Quinta-Feira - Jogos Disponíveis ({{ jogos|length }})
            </h2>
            <div class="riverbets-search">
                <form style="display: flex;">
                    <input type="text" placeholder="Buscar jogos e campeonatos">
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>

        <!-- Aqui começa o conteúdo normal do apostar.twig -->
```

**E NO FINAL do arquivo (antes da última linha), adicione:**

```html
    </div> <!-- fecha riverbets-content -->
    
    <!-- Coluna 3: Cupom de Apostas (já existe, só mover) -->
    <aside class="riverbets-sidebar-right">
        <!-- O cupom já existe no código, vai aparecer aqui -->
    </aside>
</div> <!-- fecha riverbets-layout -->
```

---

## 🔧 Modificação Alternativa Simples:

Se não quiser mexer no código Twig, adicione este JavaScript no final do arquivo `layout.twig` (antes do `</body>`):

```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Criar estrutura RIVERBETS
    const appMain = document.querySelector('.app-main');
    if (!appMain) return;
    
    // Criar menu lateral
    const sidebar = document.createElement('aside');
    sidebar.className = 'riverbets-sidebar';
    sidebar.innerHTML = `
        <div class="riverbets-sidebar-logo">{{ dados.getBanca() }}</div>
        <nav>
            <ul class="riverbets-sidebar-menu">
                <li><a href="{{ url() }}" class="active"><i class="fa fa-home"></i> Início</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-futbol-o"></i> Futebol</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-play-circle"></i> Ao Vivo</a></li>
                <li><a href="{{ url() }}"><i class="fa fa-list"></i> Mais Esportes</a></li>
                <li><a href="{{ url('regras') }}"><i class="fa fa-book"></i> Regulamento</a></li>
                <li><a href="{{ url('bilhete') }}"><i class="fa fa-ticket"></i> Conferir Bilhete</a></li>
            </ul>
        </nav>
        <div class="riverbets-sidebar-login">
            <button class="btn" onclick="$('#modal-login').modal('show')">LOGIN</button>
        </div>
    `;
    
    // Criar layout de 3 colunas
    const layout = document.createElement('div');
    layout.className = 'riverbets-layout';
    
    // Mover conteúdo
    const content = document.createElement('div');
    content.className = 'riverbets-content';
    
    // Banner
    const banner = document.createElement('div');
    banner.className = 'riverbets-banner';
    banner.innerHTML = '<div style="text-align: center; padding: 20px;"><h1>BOTE SEU ESPÍRITO COMPETITIVO<br>PRA JOGO!</h1></div>';
    content.appendChild(banner);
    
    // Cabeçalho
    const header = document.createElement('div');
    header.className = 'riverbets-games-header';
    header.innerHTML = `
        <h2><i class="fa fa-futbol-o"></i> Jogos Disponíveis</h2>
        <div class="riverbets-search">
            <input type="text" placeholder="Buscar jogos e campeonatos">
            <button><i class="fa fa-search"></i></button>
        </div>
    `;
    content.appendChild(header);
    
    // Mover conteúdo existente
    while (appMain.firstChild) {
        content.appendChild(appMain.firstChild);
    }
    
    // Montar estrutura
    layout.appendChild(sidebar);
    layout.appendChild(content);
    
    // Mover bilhete para a direita
    const bilhete = document.querySelector('.bilhete');
    if (bilhete) {
        const rightSidebar = document.createElement('aside');
        rightSidebar.className = 'riverbets-sidebar-right';
        rightSidebar.appendChild(bilhete);
        layout.appendChild(rightSidebar);
    }
    
    appMain.appendChild(layout);
});
</script>
```

---

## ✅ Resultado Final Esperado:

Após aplicar, você terá:
- ✅ Menu lateral esquerdo (roxo)
- ✅ Área central com banner e jogos
- ✅ Cupom na direita
- ✅ Cores roxo + verde idênticas ao RIVERBETS
- ✅ Layout de 3 colunas

---

## 🎯 Próximos Passos:

1. Escolha uma das opções acima
2. Recarregue a página (Ctrl + F5)
3. Teste o layout

---

**💡 Dica:** A opção JavaScript é a mais rápida, pois não mexe nos arquivos Twig!

