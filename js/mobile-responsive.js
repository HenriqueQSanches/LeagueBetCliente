/* ===== MOBILE RESPONSIVE SCRIPT - LeagueBet ===== */

(function() {
    'use strict';
    
    // Esperar o DOM carregar
    document.addEventListener('DOMContentLoaded', function() {
        initMobileFeatures();
    });
    
    function initMobileFeatures() {
        // Criar elementos mobile se não existirem
        createMobileElements();
        
        // Inicializar menu mobile
        initMobileMenu();
        
        // Inicializar bilhete mobile
        initMobileBilhete();
        
        // Ajustar tema toggle para mobile
        adjustThemeToggleForMobile();
    }
    
    function createMobileElements() {
        // Criar botão de menu mobile
        if (!document.querySelector('.mobile-menu-btn')) {
            const menuBtn = document.createElement('button');
            menuBtn.className = 'mobile-menu-btn';
            menuBtn.innerHTML = '<i class="fa fa-bars"></i>';
            menuBtn.setAttribute('aria-label', 'Menu');
            menuBtn.style.display = 'none'; // Escondido por padrão, aparece em mobile via CSS
            document.body.appendChild(menuBtn);
        }
        
        // Criar overlay
        if (!document.querySelector('.mobile-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'mobile-overlay';
            document.body.appendChild(overlay);
        }
        
        // Criar botão de toggle do bilhete (se bilhete existir)
        const bilhete = document.querySelector('.riverbets-sidebar-right');
        if (bilhete && !bilhete.querySelector('.bilhete-toggle-btn')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'bilhete-toggle-btn';
            toggleBtn.innerHTML = '<i class="fa fa-shopping-cart"></i> <span>CUPOM DE APOSTAS</span> <i class="fa fa-chevron-up"></i>';
            toggleBtn.style.display = 'none'; // Escondido por padrão, aparece em mobile via CSS
            bilhete.insertBefore(toggleBtn, bilhete.firstChild);
        }
    }
    
    function initMobileMenu() {
        const menuBtn = document.querySelector('.mobile-menu-btn');
        const sidebar = document.querySelector('.riverbets-sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        if (!menuBtn || !sidebar || !overlay) return;
        
        // Abrir menu
        menuBtn.addEventListener('click', function() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Impedir scroll do body
        });
        
        // Fechar menu ao clicar no overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = ''; // Restaurar scroll
        });
        
        // Fechar menu ao clicar em um link
        const menuLinks = sidebar.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Timeout para permitir navegação antes de fechar
                setTimeout(() => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }, 300);
            });
        });
        
        // Fechar menu com tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    function initMobileBilhete() {
        const bilhete = document.querySelector('.riverbets-sidebar-right');
        const toggleBtn = document.querySelector('.bilhete-toggle-btn');
        
        if (!bilhete || !toggleBtn) return;
        
        // Estado inicial: minimizado em mobile
        let isMinimized = true;
        
        // Verificar se está em mobile
        function isMobile() {
            return window.innerWidth <= 768;
        }
        
        // Aplicar estado inicial apenas em mobile
        if (isMobile()) {
            bilhete.classList.add('minimized');
        }
        
        // Toggle do bilhete
        toggleBtn.addEventListener('click', function() {
            if (!isMobile()) return; // Só funciona em mobile
            
            isMinimized = !isMinimized;
            
            if (isMinimized) {
                bilhete.classList.add('minimized');
                toggleBtn.innerHTML = '<i class="fa fa-shopping-cart"></i> <span>CUPOM DE APOSTAS</span> <i class="fa fa-chevron-up"></i>';
            } else {
                bilhete.classList.remove('minimized');
                toggleBtn.innerHTML = '<i class="fa fa-shopping-cart"></i> <span>CUPOM DE APOSTAS</span> <i class="fa fa-chevron-down"></i>';
            }
        });
        
        // Ajustar ao redimensionar
        window.addEventListener('resize', function() {
            if (!isMobile()) {
                // Remover classes mobile em desktop
                bilhete.classList.remove('minimized');
            } else {
                // Aplicar estado minimizado em mobile
                if (isMinimized) {
                    bilhete.classList.add('minimized');
                }
            }
        });
    }
    
    function adjustThemeToggleForMobile() {
        const themeToggle = document.querySelector('.theme-toggle-btn');
        
        if (!themeToggle) return;
        
        // Ajustar posição em mobile se menu estiver aberto
        const menuBtn = document.querySelector('.mobile-menu-btn');
        
        if (menuBtn) {
            menuBtn.addEventListener('click', function() {
                // Theme toggle já está fixo, não precisa ajustar
            });
        }
    }
    
    // Prevenir zoom em double-tap em iOS (mantendo zoom por pinch)
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);
    
    // Otimização de performance para scroll em mobile
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                // Adicione aqui qualquer lógica que precise executar no scroll
                ticking = false;
            });
            ticking = true;
        }
    });
    
    // Log de inicialização
    console.log('✅ LeagueBet Mobile Responsive: Inicializado com sucesso!');
    
})();

