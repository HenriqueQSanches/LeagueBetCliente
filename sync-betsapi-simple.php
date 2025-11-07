<?php
/**
 * Script de Sincronização Simplificado - BetsAPI
 * Versão com output em tempo real
 */

// Desabilita buffer de saída para mostrar progresso em tempo real
ob_implicit_flush(true);
ob_end_flush();

require_once __DIR__ . '/inc.config.php';
require_once __DIR__ . '/app/modules/betsapi/BetsAPIClient.php';

use app\core\crud\Conn;

set_time_limit(120);
ini_set('max_execution_time', 120);
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronização BetsAPI - Simples</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #4caf50;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #4caf50;
            border-radius: 10px;
            padding: 30px;
        }
        h1 {
            color: #ff9800;
            text-align: center;
            margin-bottom: 30px;
        }
        .log {
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 5px;
            padding: 20px;
            min-height: 300px;
            font-size: 14px;
            line-height: 1.8;
        }
        .success { color: #4caf50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        .info { color: #2196f3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Sincronização BetsAPI - Modo Simples</h1>
        <div class="log">
<?php

function logMsg($msg, $type = 'info') {
    $colors = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info'
    ];
    $class = $colors[$type] ?? 'info';
    echo "<div class='{$class}'>" . htmlspecialchars($msg) . "</div>";
    flush();
}

try {
    $startTime = microtime(true);
    
    logMsg("╔════════════════════════════════════════════════════════════╗", 'info');
    logMsg("║   LEAGUEBET - SINCRONIZAÇÃO BETSAPI (SIMPLES)             ║", 'info');
    logMsg("╚════════════════════════════════════════════════════════════╝", 'info');
    logMsg("");
    
    // Conecta ao banco
    logMsg("📊 Conectando ao banco de dados...", 'info');
    $pdo = Conn::getConn();
    logMsg("✅ Conectado ao banco!", 'success');
    logMsg("");
    
    // Conecta à API
    logMsg("🌐 Conectando à BetsAPI...", 'info');
    $api = new BetsAPIClient();
    logMsg("✅ API inicializada!", 'success');
    logMsg("");
    
    // Busca jogos
    logMsg("📅 Buscando jogos futuros (próximos 3 dias)...", 'info');
    $events = $api->getUpcomingEvents('1', 3);
    
    if (!$events || !isset($events['results'])) {
        logMsg("❌ Erro ao buscar jogos da API", 'error');
        throw new Exception("Falha ao buscar jogos");
    }
    
    $total = count($events['results']);
    logMsg("✅ {$total} jogos encontrados!", 'success');
    logMsg("");
    
    // Processa jogos
    logMsg("💾 Salvando jogos no banco de dados...", 'info');
    $inserted = 0;
    $updated = 0;
    $errors = 0;
    
    foreach ($events['results'] as $index => $event) {
        try {
            $progress = $index + 1;
            logMsg("[{$progress}/{$total}] Processando: " . ($event['home']['name'] ?? 'N/A') . " x " . ($event['away']['name'] ?? 'N/A'), 'info');
            
            $apiId = $event['id'];
            $timestamp = $event['time'] ?? time();
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp);
            
            $timeCasa = $event['home']['name'] ?? 'Time Casa';
            $timeFora = $event['away']['name'] ?? 'Time Fora';
            $data = $dateTime->format('Y-m-d');
            $hora = $dateTime->format('H:i:s');
            $campeonato = $event['league']['name'] ?? 'Campeonato';
            
            // Odds padrão
            $odds = [
                '90' => [
                    'casa' => 2.50,
                    'empate' => 3.20,
                    'fora' => 2.80,
                    'mais_2_5' => 1.85,
                    'menos_2_5' => 1.95
                ]
            ];
            $cotacoesJson = json_encode($odds, JSON_UNESCAPED_UNICODE);
            
            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT id FROM sis_jogos WHERE api_id = :api_id");
            $stmt->execute(['api_id' => $apiId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Atualiza
                $sql = "UPDATE sis_jogos SET 
                    timecasa = :timecasa,
                    timefora = :timefora,
                    data = :data,
                    hora = :hora,
                    campeonato = :campeonato,
                    cotacoes = :cotacoes,
                    updated_at = NOW(),
                    ativo = '1',
                    status = 1
                WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'timecasa' => $timeCasa,
                    'timefora' => $timeFora,
                    'data' => $data,
                    'hora' => $hora,
                    'campeonato' => $campeonato,
                    'cotacoes' => $cotacoesJson,
                    'id' => $existing['id']
                ]);
                
                $updated++;
                logMsg("   ✅ Atualizado", 'success');
            } else {
                // Insere
                $sql = "INSERT INTO sis_jogos (
                    api_id, timecasa, timefora, data, hora, campeonato,
                    cotacoes, created_at, updated_at, ativo, status,
                    apostas, maxapostas, valorapostas, limite1, limite2, limite3,
                    timecasaplacar, timeforaplacar,
                    timecasaplacarprimeiro, timeforaplacarprimeiro,
                    timecasaplacarsegundo, timeforaplacarsegundo,
                    totalgols, totalgolsprimeiro, totalgolssegundo,
                    ganhadorprimeiro, ganhadorsegundo, zebra, alteroucotacoes,
                    ao_vivo
                ) VALUES (
                    :api_id, :timecasa, :timefora, :data, :hora, :campeonato,
                    :cotacoes, NOW(), NOW(), '1', 1,
                    0, 0, 0.00, 0.00, 0.00, 0.00,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0
                )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'api_id' => $apiId,
                    'timecasa' => $timeCasa,
                    'timefora' => $timeFora,
                    'data' => $data,
                    'hora' => $hora,
                    'campeonato' => $campeonato,
                    'cotacoes' => $cotacoesJson
                ]);
                
                $inserted++;
                logMsg("   ✅ Inserido", 'success');
            }
            
        } catch (Exception $e) {
            $errors++;
            logMsg("   ❌ Erro: " . $e->getMessage(), 'error');
        }
    }
    
    logMsg("");
    logMsg("╔════════════════════════════════════════════════════════════╗", 'success');
    logMsg("║   SINCRONIZAÇÃO CONCLUÍDA!                                 ║", 'success');
    logMsg("╚════════════════════════════════════════════════════════════╝", 'success');
    logMsg("");
    
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    
    logMsg("📊 ESTATÍSTICAS:", 'info');
    logMsg("   • Total processado: {$total}", 'info');
    logMsg("   • Novos jogos: {$inserted}", 'success');
    logMsg("   • Jogos atualizados: {$updated}", 'warning');
    logMsg("   • Erros: {$errors}", $errors > 0 ? 'error' : 'success');
    logMsg("   • Tempo de execução: {$executionTime}s", 'info');
    logMsg("");
    
    // Estatísticas do banco
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sis_jogos WHERE ativo = '1'");
    $totalJogos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM sis_jogos WHERE data >= CURDATE() AND ativo = '1'");
    $jogosFuturos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    logMsg("📈 BANCO DE DADOS:", 'info');
    logMsg("   • Total de jogos ativos: {$totalJogos}", 'info');
    logMsg("   • Jogos futuros: {$jogosFuturos}", 'info');
    logMsg("");
    
    logMsg("✅ Tudo pronto! Acesse o site para ver os jogos.", 'success');
    
} catch (Exception $e) {
    logMsg("", 'error');
    logMsg("❌ ERRO FATAL: " . $e->getMessage(), 'error');
    logMsg("Stack trace: " . $e->getTraceAsString(), 'error');
}

?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="./" style="display: inline-block; background: #ff9800; color: #000; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                🏠 Ir para o Site
            </a>
            <a href="javascript:location.reload()" style="display: inline-block; background: #4caf50; color: #000; padding: 12px 25px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-left: 10px;">
                🔄 Sincronizar Novamente
            </a>
        </div>
    </div>
</body>
</html>

