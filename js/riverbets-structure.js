// RIVERBETS Layout Structure - JavaScript
// Este script cria automaticamente o layout de 3 colunas estilo RIVERBETS

$(document).ready(function() {
    // Remover header e elementos do topo ANTES de montar o layout
    $('header.pt-4.pb-4').remove();
    $('body > header').not('.header-mobile').remove();
    $('#slideshow').remove();
    $('.header-slideshow').remove();
    
    const appMain = document.querySelector('.app-main, #pg-aposta');
    if (!appMain || document.querySelector('.riverbets-layout')) return;
    
    // Criar estrutura principal
    const layout = $('<div class="riverbets-layout"></div>');
    
    // Menu Lateral Esquerdo
    const sidebar = $(`
        <aside class="riverbets-sidebar">
            <div class="riverbets-sidebar-logo">
                <img src="fac.png" alt="Logo" class="sidebar-logo-img">
            </div>
            <nav>
                <ul class="riverbets-sidebar-menu">
                    <li><a href="./"><i class="fa fa-home"></i> Início</a></li>
                    <li><a href="aovivo"><i class="fa fa-play-circle"></i> Ao Vivo</a></li>
                    <li><a href="regras"><i class="fa fa-book"></i> Regulamento</a></li>
                    <li><a href="bilhete"><i class="fa fa-ticket"></i> Conferir Bilhete</a></li>
                </ul>
            </nav>
            <div id="campeonatos-sidebar" class="campeonatos-container">
                <!-- Campeonatos serão inseridos aqui pelo Vue.js -->
            </div>
            <div class="riverbets-sidebar-login">
                <button class="btn" onclick="$('#modal-login').modal('show')">LOGIN</button>
            </div>
        </aside>
    `);
    
    // **IMPORTANTE: PEGAR O BILHETE ANTES DE MOVER QUALQUER COISA**
    let bilhete = $(appMain).find('.bilhete').first();
    console.log('🔍 Procurando bilhete...', bilhete.length > 0 ? '✅ ENCONTRADO!' : '❌ NÃO ENCONTRADO');
    
    // Área Central
    const content = $('<div class="riverbets-content"></div>');
    
    // Banner (sempre adicionar)
    content.append(`
        <div class="riverbets-banner">
            <img src="LEAGUE%20BET%20LOGO%20branco%20horizontal.png" alt="League Bet" class="banner-logo">
        </div>
    `);
    
    // Obter data e dia da semana atual
    const hoje = new Date();
    const diasSemana = ['Domingo', 'Segunda-Feira', 'Terça-Feira', 'Quarta-Feira', 'Quinta-Feira', 'Sexta-Feira', 'Sábado'];
    const diaSemana = diasSemana[hoje.getDay()];
    const dia = String(hoje.getDate()).padStart(2, '0');
    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
    const ano = hoje.getFullYear();
    const dataFormatada = `${dia}/${mes}/${ano}`;
    
    // Data e hora do dia
    content.append(`
        <div class="riverbets-date-header">
            <i class="fa fa-calendar"></i>
            <span class="date-day">${diaSemana}</span>
            <span class="date-separator">-</span>
            <span class="date-number">${dataFormatada}</span>
        </div>
    `);
    
    // Cabeçalho dos jogos
    content.append(`
        <div class="riverbets-games-header">
            <h2><i class="fa fa-futbol-o"></i> Jogos Disponíveis</h2>
            <div class="riverbets-search">
                <div class="search-input-wrapper">
                    <input type="text" placeholder="Buscar partidas" id="search-games">
                    <button onclick="return false;"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
    `);
    
    // Mover conteúdo existente (SEM CLONAR para manter Vue.js)
    // EXCETO O BILHETE que já pegamos antes!
    $(appMain).children().each(function() {
        if (!$(this).hasClass('bilhete') && !$(this).hasClass('riverbets-sidebar-right')) {
            content.append($(this)); // MOVE ao invés de clonar
        }
    });
    
    // Lateral Direita (Cupom) - MOVE o bilhete que pegamos ANTES
    const rightSidebar = $('<aside class="riverbets-sidebar-right"></aside>');
    
    if (bilhete.length) {
        console.log('✅ Bilhete encontrado! Movendo para a direita...');
        rightSidebar.append(bilhete); // MOVE o bilhete para a direita
    } else {
        console.warn('⚠️ Bilhete NÃO encontrado!');
    }
    
    // Montar estrutura NA ORDEM CORRETA: Esquerda > Centro > Direita
    layout.append(sidebar);      // 1. Menu à ESQUERDA
    layout.append(content);      // 2. Jogos no CENTRO
    layout.append(rightSidebar); // 3. Cupom à DIREITA
    
    // Inserir no app-main
    $(appMain).empty().append(layout);
    
    // Ajustar links e estado ativo conforme rota atual
    (function() {
        const path = (window.location.pathname || '').toLowerCase();
        const basePath = path.endsWith('/') ? path : path.replace(/\/[^\/]*$/, '/');
        const isLive = /\/aovivo(\/|$)/i.test(path);
        
        const linkInicio = layout.find('.riverbets-sidebar-menu a:contains("Início")').first();
        const linkAoVivo = layout.find('.riverbets-sidebar-menu a[href$="aovivo"]').first();
        
        // Garantir que 'Início' volte para a raiz do projeto atual
        linkInicio.attr('href', basePath);
        
        // Marcar ativo
        layout.find('.riverbets-sidebar-menu a').removeClass('active');
        (isLive ? linkAoVivo : linkInicio).addClass('active');
    })();
    
    // DEBUG: Verificar ordem
    console.log('Layout montado:', {
        sidebar: layout.find('.riverbets-sidebar').length,
        content: layout.find('.riverbets-content').length,
        cupom: layout.find('.riverbets-sidebar-right').length
    });
    
    // MOVER CAMPEONATOS PARA O SIDEBAR (mantendo Vue.js)
    setTimeout(() => {
        const campeonatosOriginal = $('.campeonatos').first();
        if (campeonatosOriginal.length) {
            console.log('✅ Campeonatos encontrados! Movendo para o sidebar...');
            // MOVE (não clona) para manter os eventos do Vue.js
            $('#campeonatos-sidebar').append(campeonatosOriginal);
        }
    }, 500);
    
    // Função de busca
    $('#search-games').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.riverbets-games-table').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });
});

