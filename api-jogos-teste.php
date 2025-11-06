<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Teste API de Jogos - LeagueBet</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #ff6600;
            text-align: center;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            margin-bottom: 30px;
        }
        .section {
            background: rgba(255,102,0,0.1);
            border: 2px solid #ff6600;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .success {
            background: rgba(0,255,0,0.1);
            border-left: 4px solid #00ff00;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .error {
            background: rgba(255,0,0,0.1);
            border-left: 4px solid #ff0000;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .info {
            background: rgba(0,123,255,0.1);
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ff6600;
            font-size: 0.85em;
            max-height: 400px;
            overflow-y: auto;
        }
        .btn {
            display: inline-block;
            background: #ff6600;
            color: #000;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #ff8533;
            transform: scale(1.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: rgba(0,0,0,0.3);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ff6600;
        }
        th {
            background: #ff6600;
            color: #000;
            font-weight: bold;
        }
        tr:hover {
            background: rgba(255,102,0,0.1);
        }
        #loading {
            text-align: center;
            padding: 20px;
            font-size: 1.2em;
        }
        .spinner {
            border: 4px solid rgba(255,102,0,0.3);
            border-top: 4px solid #ff6600;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 TESTE DA API DE JOGOS</h1>
        
        <div class="section">
            <h2 style="color: #ff6600;">📡 Testar Endpoint da API</h2>
            <p>Este teste vai fazer uma requisição AJAX para <code>/apostar/jogos</code> e mostrar o resultado.</p>
            <button class="btn" onclick="testarAPI()">🚀 Testar API Agora</button>
            <button class="btn" onclick="location.reload()">🔄 Recarregar Página</button>
        </div>
        
        <div id="resultado"></div>
    </div>
    
    <script>
        function testarAPI() {
            const resultadoDiv = document.getElementById('resultado');
            
            // Mostrar loading
            resultadoDiv.innerHTML = `
                <div class="section">
                    <div id="loading">
                        <div class="spinner"></div>
                        <p>⏳ Carregando jogos da API...</p>
                    </div>
                </div>
            `;
            
            // Fazer requisição
            fetch('/apostar/jogos')
                .then(response => {
                    console.log('Status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    mostrarResultado(data);
                })
                .catch(error => {
                    console.error('Erro:', error);
                    resultadoDiv.innerHTML = `
                        <div class="section">
                            <div class="error">
                                <h3>❌ Erro ao Carregar API</h3>
                                <p><strong>Mensagem:</strong> ${error.message}</p>
                                <p>Verifique o console do navegador (F12) para mais detalhes.</p>
                            </div>
                        </div>
                    `;
                });
        }
        
        function mostrarResultado(data) {
            const resultadoDiv = document.getElementById('resultado');
            let html = '';
            
            // Verificar cotações
            if (data.cotacoes && data.cotacoes.length > 0) {
                html += `
                    <div class="section">
                        <h2 style="color: #ff6600;">✅ Cotações</h2>
                        <div class="success">
                            <strong>Total:</strong> ${data.cotacoes.length} cotações disponíveis
                        </div>
                    </div>
                `;
            }
            
            // Verificar grupos
            if (data.grupos) {
                html += `
                    <div class="section">
                        <h2 style="color: #ff6600;">✅ Grupos de Cotações</h2>
                        <div class="success">
                            <strong>Total:</strong> ${Object.keys(data.grupos).length} grupos
                        </div>
                    </div>
                `;
            }
            
            // Verificar jogos (vem dentro de paises)
            if (data.paises && data.paises.length > 0) {
                let totalJogos = 0;
                data.paises.forEach(pais => {
                    if (pais.campeonatos) {
                        pais.campeonatos.forEach(camp => {
                            if (camp.jogos) {
                                totalJogos += camp.jogos.length;
                            }
                        });
                    }
                });
                
                html += `
                    <div class="section">
                        <h2 style="color: #00ff00;">🎮 JOGOS DISPONÍVEIS</h2>
                        <div class="success">
                            <strong>Total de Jogos:</strong> ${totalJogos}<br>
                            <strong>Países:</strong> ${data.paises.length}
                        </div>
                `;
                
                if (totalJogos > 0) {
                    html += `<h3 style="color: #ff6600; margin-top: 20px;">🏆 Primeiros 10 Jogos:</h3>`;
                    html += `<table>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Jogo</th>
                            <th>Campeonato</th>
                            <th>País</th>
                            <th>Cotações</th>
                        </tr>
                    `;
                    
                    let contador = 0;
                    for (const pais of data.paises) {
                        if (contador >= 10) break;
                        
                        for (const camp of (pais.campeonatos || [])) {
                            if (contador >= 10) break;
                            
                            for (const jogo of (camp.jogos || [])) {
                                if (contador >= 10) break;
                                
                                const dataHora = new Date(jogo.dateTime).toLocaleString('pt-BR');
                                const casa = jogo.cotacoes && jogo.cotacoes['90'] && jogo.cotacoes['90'].casa ? jogo.cotacoes['90'].casa : 'N/A';
                                const empate = jogo.cotacoes && jogo.cotacoes['90'] && jogo.cotacoes['90'].empate ? jogo.cotacoes['90'].empate : 'N/A';
                                const fora = jogo.cotacoes && jogo.cotacoes['90'] && jogo.cotacoes['90'].fora ? jogo.cotacoes['90'].fora : 'N/A';
                                
                                html += `
                                    <tr>
                                        <td>${dataHora}</td>
                                        <td><strong>${jogo.casa}</strong> x <strong>${jogo.fora}</strong></td>
                                        <td>${camp.title}</td>
                                        <td>${pais.title}</td>
                                        <td>🏠 ${casa} | 🤝 ${empate} | ✈️ ${fora}</td>
                                    </tr>
                                `;
                                contador++;
                            }
                        }
                    }
                    
                    html += `</table>`;
                    
                    html += `
                        <div class="success" style="margin-top: 20px; padding: 20px; text-align: center; font-size: 1.2em;">
                            <h2 style="color: #00ff00;">🎉 SUCESSO!</h2>
                            <p>✅ A API está funcionando perfeitamente!</p>
                            <p>✅ ${totalJogos} jogos disponíveis para apostas!</p>
                            <p><strong>O backend está 100% operacional!</strong></p>
                            <p style="margin-top: 15px; color: #ffa500;">
                                💡 Se os jogos não aparecem no site principal, o problema está no frontend (JavaScript/Vue.js).
                            </p>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="error">
                            <p>⚠️ Estrutura da API está correta, mas nenhum jogo foi encontrado.</p>
                        </div>
                    `;
                }
                
                html += `</div>`;
                
            } else {
                html += `
                    <div class="section">
                        <div class="error">
                            <h3>❌ Nenhum Jogo Encontrado</h3>
                            <p>A API retornou dados, mas não há jogos disponíveis.</p>
                        </div>
                    </div>
                `;
            }
            
            // Verificar datas
            if (data.datas && data.datas.length > 0) {
                html += `
                    <div class="section">
                        <h2 style="color: #ff6600;">📅 Datas Disponíveis</h2>
                        <div class="info">
                            <strong>Total:</strong> ${data.datas.length} datas<br><br>
                            ${data.datas.map(d => `<span style="display: inline-block; margin: 5px; padding: 5px 10px; background: rgba(255,102,0,0.3); border-radius: 5px;">${d.title}</span>`).join('')}
                        </div>
                    </div>
                `;
            }
            
            // Mostrar JSON completo (colapsado)
            html += `
                <div class="section">
                    <h2 style="color: #ff6600;">📄 JSON Completo da Resposta</h2>
                    <details>
                        <summary style="cursor: pointer; padding: 10px; background: rgba(255,102,0,0.2); border-radius: 5px; margin-bottom: 10px;">
                            📋 Clique para ver o JSON completo
                        </summary>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </details>
                </div>
            `;
            
            resultadoDiv.innerHTML = html;
        }
        
        // Testar automaticamente ao carregar
        window.onload = function() {
            console.log('Página carregada. Clique no botão para testar a API.');
        };
    </script>
</body>
</html>

