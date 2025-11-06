<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teste API Direto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1a1a1a;
            color: #fff;
            padding: 20px;
        }
        .box {
            background: #2d2d2d;
            border: 2px solid #ff9800;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        h2 {
            color: #ff9800;
            margin-top: 0;
        }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            color: #00ff00;
            font-size: 12px;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            color: #2196f3;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🔍 Teste Direto da API de Jogos</h1>

    <div class="box">
        <h2>1️⃣ Testando Conexão com Banco de Dados</h2>
        <?php
        try {
            require_once 'conexao.php';
            echo '<p class="success">✅ Conexão com banco OK!</p>';
            
            $stmt = $conexao->query("SELECT COUNT(*) as total FROM sis_jogos WHERE ativo = '1'");
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<p class="info">📊 Total de jogos ativos no banco: ' . $total['total'] . '</p>';
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Erro: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>

    <div class="box">
        <h2>2️⃣ Testando URL da API</h2>
        <?php
        require_once 'inc.config.php';
        $apiUrl = $config['config']['uri'] . '/apostar/jogos';
        echo '<p class="info">🌐 URL configurada: <code>' . $apiUrl . '</code></p>';
        ?>
    </div>

    <div class="box">
        <h2>3️⃣ Chamando a API Diretamente</h2>
        <?php
        try {
            // Simular a chamada do controller
            require_once 'vendor/autoload.php';
            
            $controller = new \app\modules\website\controllers\apostarController();
            $resultado = $controller->jogosAction();
            
            if ($resultado) {
                echo '<p class="success">✅ API retornou dados!</p>';
                
                // Decodificar JSON
                $dados = json_decode($resultado, true);
                
                if ($dados && isset($dados['paises'])) {
                    $totalPaises = count($dados['paises']);
                    echo '<p class="info">🌍 Total de países: ' . $totalPaises . '</p>';
                    
                    $totalJogos = 0;
                    foreach ($dados['paises'] as $pais) {
                        if (isset($pais['campeonatos'])) {
                            foreach ($pais['campeonatos'] as $camp) {
                                if (isset($camp['jogos'])) {
                                    $totalJogos += count($camp['jogos']);
                                }
                            }
                        }
                    }
                    
                    echo '<p class="info">⚽ Total de jogos: ' . $totalJogos . '</p>';
                    
                    if ($totalJogos > 0) {
                        echo '<p class="success">✅ TUDO CERTO! A API está retornando jogos!</p>';
                        echo '<p style="color: #ffeb3b;">⚠️ Se os jogos não aparecem no site, o problema é no Vue.js do frontend.</p>';
                        
                        // Mostrar exemplo de um jogo
                        echo '<h3 style="color: #ff9800;">Exemplo de Jogo Retornado:</h3>';
                        foreach ($dados['paises'] as $pais) {
                            if (isset($pais['campeonatos'])) {
                                foreach ($pais['campeonatos'] as $camp) {
                                    if (isset($camp['jogos']) && count($camp['jogos']) > 0) {
                                        $jogo = $camp['jogos'][0];
                                        echo '<pre>' . json_encode($jogo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                                        break 2;
                                    }
                                }
                            }
                        }
                    } else {
                        echo '<p class="error">❌ API retorna 0 jogos!</p>';
                    }
                } else {
                    echo '<p class="error">❌ Formato de resposta inválido!</p>';
                    echo '<pre>' . substr($resultado, 0, 500) . '</pre>';
                }
            } else {
                echo '<p class="error">❌ API não retornou nada!</p>';
            }
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Erro ao chamar API: ' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
        ?>
    </div>

    <div class="box">
        <h2>4️⃣ Testando Requisição AJAX (JavaScript)</h2>
        <button onclick="testarAjax()" style="background: #ff9800; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">
            🔄 Testar AJAX
        </button>
        <div id="ajax-result" style="margin-top: 15px;"></div>
    </div>

    <script>
        function testarAjax() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.innerHTML = '<p style="color: #2196f3;">⏳ Testando...</p>';
            
            fetch('/Cliente/LeagueBetCliente-main/apostar/jogos')
                .then(response => {
                    resultDiv.innerHTML += '<p style="color: #4caf50;">✅ Status: ' + response.status + '</p>';
                    return response.json();
                })
                .then(data => {
                    resultDiv.innerHTML += '<p style="color: #4caf50;">✅ Resposta recebida!</p>';
                    
                    if (data.paises) {
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
                        
                        resultDiv.innerHTML += '<p style="color: #2196f3;">🌍 Países: ' + data.paises.length + '</p>';
                        resultDiv.innerHTML += '<p style="color: #2196f3;">⚽ Jogos: ' + totalJogos + '</p>';
                        
                        if (totalJogos > 0) {
                            resultDiv.innerHTML += '<p style="color: #4caf50; font-size: 18px;">✅ AJAX FUNCIONANDO! A API retorna ' + totalJogos + ' jogos!</p>';
                            resultDiv.innerHTML += '<p style="color: #ffeb3b;">⚠️ Se não aparecem no site, verifique o Console (F12) para erros do Vue.js</p>';
                        } else {
                            resultDiv.innerHTML += '<p style="color: #f44336;">❌ API retorna 0 jogos via AJAX!</p>';
                        }
                    } else {
                        resultDiv.innerHTML += '<p style="color: #f44336;">❌ Resposta não contém "paises"</p>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML += '<p style="color: #f44336;">❌ Erro: ' + error.message + '</p>';
                });
        }
        
        // Testar automaticamente ao carregar
        window.onload = function() {
            setTimeout(testarAjax, 1000);
        };
    </script>

    <div class="box">
        <h2>📋 Diagnóstico Final</h2>
        <ul style="font-size: 16px; line-height: 1.8;">
            <li>✅ Se a API retorna jogos aqui, mas não aparecem no site = <strong>Problema no Vue.js</strong></li>
            <li>✅ Se AJAX funciona aqui, mas não no site = <strong>Problema de CORS ou configuração</strong></li>
            <li>✅ Se nada funciona = <strong>Problema no backend/banco de dados</strong></li>
        </ul>
        <p style="color: #ffeb3b; font-size: 18px; margin-top: 20px;">
            📌 <strong>PRÓXIMO PASSO:</strong> Abra o Console (F12) no site principal e me envie os erros!
        </p>
    </div>
</body>
</html>

