<?php
/**
 * Script de Teste - BetsAPI Integration
 * 
 * Execute este arquivo para testar a integração com a BetsAPI
 * URL: http://localhost/Cliente/LeagueBetCliente-main/test-betsapi.php
 */

require_once __DIR__ . '/inc.config.php';
require_once __DIR__ . '/app/modules/betsapi/BetsAPIClient.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeagueBet - Teste BetsAPI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 3px solid #ff9800;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2.5em;
            color: #ff9800;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #ccc;
            font-size: 1.1em;
        }
        
        .test-section {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid #ff9800;
        }
        
        .test-section h2 {
            color: #ff9800;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        
        .status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .status.success {
            background: #4caf50;
            color: #fff;
        }
        
        .status.error {
            background: #f44336;
            color: #fff;
        }
        
        .status.info {
            background: #2196f3;
            color: #fff;
        }
        
        .data-box {
            background: #1a1a1a;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            overflow-x: auto;
        }
        
        .data-box pre {
            color: #4caf50;
            font-size: 0.9em;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .event-card {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            border-left: 3px solid #ff9800;
        }
        
        .event-card h3 {
            color: #ff9800;
            margin-bottom: 10px;
        }
        
        .event-card p {
            color: #ccc;
            margin: 5px 0;
        }
        
        .odds {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .odd-btn {
            background: #ff9800;
            color: #000;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            min-width: 60px;
        }
        
        .btn {
            display: inline-block;
            background: #ff9800;
            color: #000;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 5px;
            cursor: pointer;
            border: none;
            font-size: 1em;
        }
        
        .btn:hover {
            background: #f57c00;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #ff9800;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎯 LeagueBet - Teste BetsAPI</h1>
            <p>Verificando integração com a BetsAPI</p>
        </div>

        <?php
        $api = new BetsAPIClient();
        $allTestsPassed = true;
        ?>

        <!-- TESTE 1: Conexão -->
        <div class="test-section">
            <h2>1️⃣ Teste de Conexão</h2>
            <?php
            $connectionTest = $api->testConnection();
            if ($connectionTest) {
                echo '<span class="status success">✅ CONECTADO</span>';
                echo '<p style="color: #4caf50; margin-top: 10px;">A conexão com a BetsAPI foi estabelecida com sucesso!</p>';
            } else {
                echo '<span class="status error">❌ ERRO DE CONEXÃO</span>';
                echo '<p style="color: #f44336; margin-top: 10px;">Não foi possível conectar à BetsAPI. Verifique o token.</p>';
                $allTestsPassed = false;
            }
            ?>
        </div>

        <!-- TESTE 2: Esportes Disponíveis -->
        <div class="test-section">
            <h2>2️⃣ Esportes Disponíveis</h2>
            <?php
            $sports = $api->getSports();
            if ($sports && isset($sports['results'])) {
                echo '<span class="status success">✅ ' . count($sports['results']) . ' ESPORTES ENCONTRADOS</span>';
                echo '<div class="data-box"><pre>';
                foreach ($sports['results'] as $sport) {
                    echo "ID: {$sport['id']} - {$sport['name']}\n";
                }
                echo '</pre></div>';
            } else {
                echo '<span class="status error">❌ ERRO AO BUSCAR ESPORTES</span>';
                $allTestsPassed = false;
            }
            ?>
        </div>

        <!-- TESTE 3: Jogos Futuros -->
        <div class="test-section">
            <h2>3️⃣ Jogos Futuros (Próximos 3 dias)</h2>
            <?php
            $upcomingEvents = $api->getUpcomingEvents('1', 3);
            if ($upcomingEvents && isset($upcomingEvents['results'])) {
                $total = count($upcomingEvents['results']);
                echo '<span class="status success">✅ ' . $total . ' JOGOS ENCONTRADOS</span>';
                
                // Mostra os primeiros 5 jogos
                echo '<p style="color: #ccc; margin: 15px 0;">Exibindo os primeiros 5 jogos:</p>';
                $count = 0;
                foreach ($upcomingEvents['results'] as $event) {
                    if ($count >= 5) break;
                    
                    $timestamp = $event['time'];
                    $dateTime = new DateTime();
                    $dateTime->setTimestamp($timestamp);
                    
                    echo '<div class="event-card">';
                    echo '<h3>' . htmlspecialchars($event['home']['name']) . ' x ' . htmlspecialchars($event['away']['name']) . '</h3>';
                    echo '<p>🏆 ' . htmlspecialchars($event['league']['name']) . '</p>';
                    echo '<p>📅 ' . $dateTime->format('d/m/Y H:i') . '</p>';
                    echo '<p>🆔 Event ID: ' . $event['id'] . '</p>';
                    echo '</div>';
                    
                    $count++;
                }
            } else {
                echo '<span class="status error">❌ ERRO AO BUSCAR JOGOS</span>';
                $allTestsPassed = false;
            }
            ?>
        </div>

        <!-- TESTE 4: Jogos Ao Vivo -->
        <div class="test-section">
            <h2>4️⃣ Jogos Ao Vivo</h2>
            <?php
            $inPlayEvents = $api->getInPlayEvents('1');
            if ($inPlayEvents && isset($inPlayEvents['results'])) {
                $total = count($inPlayEvents['results']);
                if ($total > 0) {
                    echo '<span class="status success">🔴 ' . $total . ' JOGOS AO VIVO</span>';
                    
                    foreach ($inPlayEvents['results'] as $event) {
                        echo '<div class="event-card">';
                        echo '<h3>🔴 ' . htmlspecialchars($event['home']['name']) . ' x ' . htmlspecialchars($event['away']['name']) . '</h3>';
                        echo '<p>🏆 ' . htmlspecialchars($event['league']['name']) . '</p>';
                        if (isset($event['ss'])) {
                            echo '<p><strong>PLACAR: ' . $event['ss'] . '</strong></p>';
                        }
                        if (isset($event['timer'])) {
                            echo '<p>⏱️ ' . $event['timer']['tm'] . '\'</p>';
                        }
                        echo '</div>';
                    }
                } else {
                    echo '<span class="status info">ℹ️ NENHUM JOGO AO VIVO NO MOMENTO</span>';
                }
            } else {
                echo '<span class="status error">❌ ERRO AO BUSCAR JOGOS AO VIVO</span>';
            }
            ?>
        </div>

        <!-- TESTE 5: Odds de um Jogo -->
        <?php if ($upcomingEvents && isset($upcomingEvents['results'][0])): ?>
        <div class="test-section">
            <h2>5️⃣ Teste de Odds</h2>
            <?php
            $testEventId = $upcomingEvents['results'][0]['id'];
            $testEventName = $upcomingEvents['results'][0]['home']['name'] . ' x ' . $upcomingEvents['results'][0]['away']['name'];
            
            echo '<p style="color: #ccc; margin-bottom: 15px;">Buscando odds para: <strong>' . htmlspecialchars($testEventName) . '</strong></p>';
            
            $odds = $api->getEventOddsSummary($testEventId);
            if ($odds && isset($odds['results'])) {
                echo '<span class="status success">✅ ODDS ENCONTRADAS</span>';
                echo '<div class="data-box"><pre>';
                echo json_encode($odds['results'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo '</pre></div>';
            } else {
                echo '<span class="status error">❌ ERRO AO BUSCAR ODDS</span>';
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- RESULTADO FINAL -->
        <div class="test-section" style="border-left-color: <?php echo $allTestsPassed ? '#4caf50' : '#f44336'; ?>">
            <h2>📊 Resultado Final</h2>
            <?php if ($allTestsPassed): ?>
                <span class="status success">✅ TODOS OS TESTES PASSARAM!</span>
                <p style="color: #4caf50; margin: 15px 0; font-size: 1.1em;">
                    A integração com a BetsAPI está funcionando perfeitamente!
                </p>
                <p style="color: #ccc; margin: 15px 0;">
                    <strong>Próximos passos:</strong>
                </p>
                <ol style="color: #ccc; margin-left: 20px; line-height: 1.8;">
                    <li>Execute o script SQL: <code style="background: #1a1a1a; padding: 3px 8px; border-radius: 3px;">database-update-betsapi.sql</code></li>
                    <li>Configure o CRON para executar: <code style="background: #1a1a1a; padding: 3px 8px; border-radius: 3px;">php app/modules/betsapi/SyncJogos.php</code></li>
                    <li>Teste a sincronização manual clicando no botão abaixo</li>
                </ol>
                
                <a href="sync-betsapi.php" class="btn">🔄 Executar Sincronização Manual</a>
            <?php else: ?>
                <span class="status error">❌ ALGUNS TESTES FALHARAM</span>
                <p style="color: #f44336; margin: 15px 0;">
                    Verifique o token da API e tente novamente.
                </p>
            <?php endif; ?>
        </div>

        <div style="text-align: center; padding: 30px 0; color: #666;">
            <p>LeagueBet © 2025 - Desenvolvido por Henrique Sanches</p>
        </div>
    </div>
</body>
</html>

