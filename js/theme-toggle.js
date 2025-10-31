// THEME TOGGLE - Dark/Light Mode
// Sistema de alternância entre tema claro e escuro

(function() {
    'use strict';
    
    // Verificar preferência salva ou usar tema claro como padrão
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Aplicar tema ao carregar a página
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    // Criar botão de toggle quando o DOM estiver pronto
    function createToggleButton() {
        // Verificar se já existe
        if (document.getElementById('theme-toggle')) return;
        
        const button = document.createElement('button');
        button.id = 'theme-toggle';
        button.className = 'theme-toggle-btn';
        button.setAttribute('aria-label', 'Alternar tema');
        button.setAttribute('data-tooltip', 'Alternar Tema');
        
        // Ícones (usando Font Awesome)
        button.innerHTML = `
            <span class="icon-light">☀️</span>
            <span class="icon-dark">🌙</span>
        `;
        
        // Adicionar ao body
        document.body.appendChild(button);
        
        // Adicionar evento de clique
        button.addEventListener('click', toggleTheme);
    }
    
    // Função para alternar tema
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Aplicar novo tema
        document.documentElement.setAttribute('data-theme', newTheme);
        
        // Salvar preferência
        localStorage.setItem('theme', newTheme);
        
        // Feedback visual
        const button = document.getElementById('theme-toggle');
        button.style.transform = 'scale(0.8) rotate(180deg)';
        
        setTimeout(() => {
            button.style.transform = '';
        }, 300);
        
        // Disparar evento customizado (para outros scripts reagirem)
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: newTheme } 
        }));
    }
    
    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createToggleButton);
    } else {
        createToggleButton();
    }
    
    // Suporte para atalho de teclado (Ctrl+Shift+T)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'T') {
            e.preventDefault();
            toggleTheme();
        }
    });
    
})();

