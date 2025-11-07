/**
 * Pop-up de "Em Construção" para links do menu
 */

$(document).ready(function() {
    // Função para mostrar pop-up de "Em Construção"
    function mostrarEmConstrucao(e, nomeFuncionalidade) {
        e.preventDefault();
        
        swal({
            title: "Em Construção",
            text: `A funcionalidade "${nomeFuncionalidade}" está em construção e estará disponível em breve!`,
            icon: "info",
            button: "OK",
        });
    }
    
    // Aguardar um pouco para garantir que o sidebar foi criado
    setTimeout(function() {
        // Ao Vivo
        $('.riverbets-sidebar-menu a[href*="Ao Vivo"], .riverbets-sidebar-menu a:contains("Ao Vivo")').on('click', function(e) {
            if (!$(this).attr('href').includes('apostar')) {
                mostrarEmConstrucao(e, 'Ao Vivo');
            }
        });
        
        // Regulamento
        $('.riverbets-sidebar-menu a[href*="regras"]').on('click', function(e) {
            mostrarEmConstrucao(e, 'Regulamento');
        });
        
        // Conferir Bilhete - apenas no sidebar (não no header)
        $('.riverbets-sidebar-menu a[href*="bilhete"]').on('click', function(e) {
            // Não bloquear o link real de consultar bilhete
            if ($(this).closest('.riverbets-sidebar').length) {
                mostrarEmConstrucao(e, 'Conferir Bilhete');
            }
        });
        
        // Mais Esportes
        $('.riverbets-sidebar-menu a:contains("Mais Esportes")').on('click', function(e) {
            mostrarEmConstrucao(e, 'Mais Esportes');
        });
    }, 500);
});

